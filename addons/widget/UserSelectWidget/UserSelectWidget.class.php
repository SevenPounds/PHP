<?php
/**
 * 网上评课及主体讨论用户选择
 * Class UserSelectWidget{:W('UserSelect',array('userids'=>**,'appName'=>''))}
 */
class UserSelectWidget extends Widget{
    public function render($data) {
        $var = array();
        !isset($data['appName']) && $data['appName'] = 'pingke';
		$var['app']= D('app')->getAppByName($data['appName']);
        if(!empty($data['userids'])){
            $userIds = array_filter($data['userids']);
            $var['userList'] = $this->getUserInfos($userIds);
            $var['userids'] = implode('|', $userIds);
        }
        $var['provinceOrgList'] = D("CyEduOrg")->listEduorgByParentId("2000000001000000001");
        //获取安徽省的地级市列表
        $var['proviceList'] =  D("CyArea")->listAreaById(1, "province");
        $var['cityList'] = D("CyArea")->listAreaById(2166, "city");
        $var['subjectList'] = D("Node")->subjects;
        $var['currentUser'] = model('User')->getUserInfo($this->mid);
        $var['appName'] = $data['appName'];
		isset($data['style']) && $var['style'] = $data['style'];
        $content = $this->renderFile(dirname(__FILE__).'/userList.html',$var);
        unset($var);
        return $content;
    }

	function getUserInfos($userIds){
		$result= array();
		foreach ($userIds as $userId){
            $user = M('User')->getUserInfo($userId);
            if(!empty($user)){
                array_push($result, $user);
            }
		}
		return $result;		
	}
	
	/**
	 * 获取不同角色的用户信息列表
	 * @param string $roleName 角色id teacher表示教师 researcher表示教研员
	 * @param string $keywords 查询关键字
	 */
	private function getUserList($roleName,$keywords=null){
		$userList = model("User")->getUserListByRoleName($this->mid,$roleName,$keywords);
		return $userList;
	}
	
	/**
	 * 成员列表搜索的功能
	 */
	public function search(){
		
		$keywords = !empty($_POST['keywords']) ? $_POST['keywords'] : "";
		$page = intval($_POST['p']) ? intval($_POST['p']) : 1;
		$pageSize = 24;
		$uList= D("Pingke", "pingke")->searchInstructorAndTeacher($keywords, $page, $pageSize);
		$totalCount = ($page-1) * $pageSize + count($uList);
		$uList = array_slice($uList, 0, $pageSize);
		$p = new AjaxPage(array('total_rows'=>$totalCount,
				'method'=>'ajax',
				'ajax_func_name'=>'userSearch.page',
				'now_page'=>$page,
				'list_rows'=>$pageSize
		));
		$userList = array();
		foreach ($uList AS $u){
            if($u->user && $u->user['uid'] != $this->mid){
                $user = $u->user;
                $user['org'] = $this->_getUserOrgName($user, 'all');
                $userList[] = $user;
            }
		}
		$var['page'] = $p->simpleShow();
		$var['ssList'] = $userList;
		$var['keywords'] = $keywords;
		
		$content = $this->renderFile(dirname(__FILE__).'/ssList.html',$var);
		return $content;
	}
	
	/**
	 * 用于获取教育组织机构信息
	 */
	private function _getEduOrg($orgId){
		$orgList = D("CyEduOrg")->listEduorgByParentId($orgId);
		return $orgList;
	}
	
	/**
	 * 用于教研员列表
	 */
	public function getResearcherList(){
        $type = $_POST['type'] ? $_POST['type'] : 'province';
        $provinceEduOrg = isset($_POST['eduorg_province']) ?  $_POST['eduorg_province'] : "";
        $cityEduOrg = isset($_POST['eduorg_city']) ?  $_POST['eduorg_city'] : "";
        $districtEduOrg = isset($_POST['eduorg_district']) ?  $_POST['eduorg_district'] : "";
        $orgList = array();
        switch($type){
            case 'province':
                if(empty($provinceEduOrg)){
                    //当在省级单位选择“请选择时”时，不再获取下一级的机构列表
                    $orgId = $provinceEduOrg;
                }else{
                    $orgList = D("CyEduOrg")->listEduorgByParentId($provinceEduOrg);
                    $orgId = $provinceEduOrg;
                }
                break;
            case 'city':
                if(empty($cityEduOrg)){
                    //当在市级单位选择“请选择时”时，不再获取下一级的机构列表
                    $orgId = $provinceEduOrg;
                }else{
                    $orgList = D("CyEduOrg")->listEduorgByParentId($cityEduOrg);
                    $orgId = $cityEduOrg;
                }
                break;
            case 'district':
                if(empty($districtEduOrg)){
                    //当在区级单位选择“请选择时”，不再获取下一级的机构列表
                    if(empty($cityEduOrg)){
                        //如果市级的单位选择为空，则使用省级的条件查询用户
                        $orgId = $provinceEduOrg;
                    }else{
                        //如果市级的单位选择为空，则使用省级的条件查询用户
                        $orgId = $cityEduOrg;
                    }
                }else{
                    $orgList = D("CyEduOrg")->listEduorgByParentId($districtEduOrg);
                    $orgId = $districtEduOrg;
                }
                break;
            case 'subject':
                if(!empty($districtEduOrg)){
                    $orgId = $districtEduOrg;
                }elseif(!empty($cityEduOrg)){
                    $orgId = $cityEduOrg;
                }else{
                    $orgId = $provinceEduOrg;
                }
                break;
        }
		$subject = isset($_POST['subject']) ?  $_POST['subject'] : "";
		$keywords = isset($_POST['keywords']) ?  $_POST['keywords'] : "";
		$page = intval($_POST['p']) ?  $_POST['p'] : 1;
		$pageSize = 24;
		$params['eduorg_id'] = $orgId;
		$params['subject'] = $subject;
		$researcherList = D("Pingke", "pingke")->searchInstructorUser($params, $page, $pageSize);
		$totalCount = ($page-1) * $pageSize + count($researcherList);
		$userList = array();
		$researcherList = array_slice($researcherList, 0, $pageSize);
		foreach ($researcherList AS $u){
			if($u->user && $u->user['uid'] != $this->mid){
                $user = $u->user;
                $user['org'] = $this->_getUserOrgName($user,'org');
                $userList[] = $user;
			}
		}
		$p = new AjaxPage(array('total_rows'=>$totalCount,
				'method'=>'ajax',
				'ajax_func_name'=>'researcherList.page',
				'now_page'=>$page,
				'list_rows'=>$pageSize
		));
		$var['userList'] = $userList;
		$var['page'] = $p->simpleShow();
		$result = new stdClass();
		$result->status = 1;
		$result->parentId = $orgId;
		$result->researcherList = fetch(dirname(__FILE__).'/ajax_user_list.html', $var);
		$result->eduOrgList = $orgList;
	
		exit(json_encode($result));
	}
	/**
	 * 用于教师列表
	 */
	public function getTeacherList(){
        $province = isset($_POST['province']) ?  $_POST['province'] : "";
		$city = isset($_POST['city']) ?  $_POST['city'] : "";
		$district = isset($_POST['district']) ?  $_POST['district'] : "";
		$school = isset($_POST['school']) ?  $_POST['school'] : "";
		$subject = isset($_POST['subject']) ?  $_POST['subject'] : "";
		$type = isset($_POST['type']) ?  $_POST['type'] : "";
		$page = intval($_POST['p']) ?  $_POST['p'] : 1;
		$pageSize = 24;
		$orgList = array();
		if($type){
			switch($type){
                case "province":
                    $orgList = D("CyArea")->listAreaById($province,"city");
                    break;
				case "city":
					$orgList = D("CyArea")->listAreaById($city,"county");
					break;
				case "district":
					$orgList = D("CySchool")->list_school_by_area($district,true);
					break;
			}
		}
		$param = array();
        $param['province_id'] = $province;
		$param['city_id'] = $city;
		$param['district_id'] =  $district;
		$param['school_id'] =  $school;
		$param['subject'] = $subject;
		$teacherList = D("Pingke", "pingke")->searchTeacherUser($param, $page, $pageSize);

		$totalCount = ($page-1) * $pageSize + count($teacherList);
		$userList = array();
		$teacherList = array_slice($teacherList, 0, $pageSize);
		foreach ($teacherList AS $u){
			if($u->user && $u->user['uid'] != $this->mid){
                $user = $u->user;
                $user['org'] = $this->_getUserOrgName($user,'school');
				$userList[] = $user;
			}
		}
		$p = new AjaxPage(array('total_rows'=>$totalCount,
				'method'=>'ajax',
				'ajax_func_name'=>'teacherList.page',
				'now_page'=>$page,
				'list_rows'=>$pageSize
		));
		$var['userList'] = $userList;
		$var['page'] = $p->simpleShow();
		$var['userList'] = $userList;
		$result = new stdClass();
		$result->status = 1;
		$result->teacherList = fetch(dirname(__FILE__).'/ajax_user_list.html', $var);
		$result->eduOrgList = $orgList;
	
		exit(json_encode($result));
	}

    /**
     * 用于获取常用用户
     */
    public function getFavoriteUser(){
        $appName = isset($_POST['app_name']) ? $_POST['app_name'] : 'pingke';
        $result = new stdClass();
        $result->status = 0;
        $favoriteUserIds = D('UserFavorite')->getFavoriteUsers($_SESSION['login_userInfo']['uid'], $appName, 0, 9);
        if(empty($favoriteUserIds)){
            $result->msg = "暂无用户！";
            exit(json_encode($result));
        }
        $userIds = array();
        foreach($favoriteUserIds AS $_u){
            $userIds[] = $_u['fuid'];
        }
        $favoriteUsers = D('User')->getUserInfoByUids($userIds);
        $userList = array();
        foreach($favoriteUsers AS $u){
            if($u && $u['uid'] != $this->mid){
                $u['org'] = $this->_getUserOrgName($u,'all');
                $userList[] = $u;
            }
        }
        $var['favoriteUsers'] = $userList;
        $result->status = 1;
        $result->favoriteUserList = fetch(dirname(__FILE__).'/favorite_user_list.html', $var);
        exit(json_encode($result));
    }

    /**
     * 获取用户的组织结构信息
     * @param $user 用户信息
     * @param string $orgType 类型
     * @return string 用户所在的组织结构名,all的话是去查询学校和组织机构两种
     */
    private function _getUserOrgName($user, $orgType = 'school'){
        $cyuid = $user['cyuid'];
        switch(strtolower($orgType)){
            case 'org':
                $orgList = D('CyEduOrg')->listEduorgByUser($cyuid);
                foreach($orgList AS $org){
                    if(isset($org['name'])){
                        return $org['name'];
                    }
                }
                break;
            case 'school':
                $schoolList = D('CySchool')->list_school_by_user($cyuid);
                foreach($schoolList AS $school){
                    if(isset($school['name'])){
                        return $school['name'];
                    }
                }
                break;
            case 'all':
                $roleName = D('UserRole')->where(array('uid'=>$user['uid']))->field('rolename')->find();
                if(is_array($roleName)) $roleName = $roleName['rolename'];
                if($roleName == UserRoleTypeModel::TEACHER){
                    $schoolList = D('CySchool')->list_school_by_user($cyuid);
                    foreach($schoolList AS $school){
                        if(isset($school['name'])){
                            return $school['name'];
                        }
                    }
                }else{
                    $orgList = D('CyEduOrg')->listEduorgByUser($cyuid);
                    foreach($orgList AS $org){
                        if(isset($org['name'])){
                            return $org['name'];
                        }
                    }
                }
                break;
        }
        return "";
    }
}
?>