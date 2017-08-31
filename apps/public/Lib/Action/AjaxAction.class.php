<?php
/**
 * ajax 请求
 * @author cheng
 *
 */
class AjaxAction extends Action{
	
	/**
	 * 
	 */
	public function getUserList(){
		$epspSvc = new \EpspClient();
		$roleid=isset($_POST['roleid'])?$_POST['roleid']:"instructor";
		$grade=isset($_POST['grade'])?$_POST['grade']:'';
		$subject=isset($_POST['subject'])?$_POST['subject']:'';
		$province=isset($_POST['province'])?$_POST['province']:'';
		$city=isset($_POST['city'])?$_POST['city']:'';
		$area=isset($_POST['area'])?$_POST['area']:'';
		$field=isset($_POST['orderfield'])?$_POST['orderfield']:'';
		$order=isset($_POST['order'])?$_POST['order']:'';
		$keywords = isset($_POST['keywords'])?$_POST['keywords']:'';
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$schoolid =  isset($_REQUEST['school']) ? $_REQUEST['school'] : 0;
		$pagesize=10;
		if($field&&$order){
			switch($field){
				case 'uploadCount':
					$orderfield = 'upload_yunpan_count';
					break;
				case 'follower':
					$orderfield = 'follower_count';
					break;
				case 'visitor':
					$orderfield = 'visitor_count';
					$orderby = "ud.`value`*1 $order";
					break;
				case 'member':
					$orderby="member_count $order" ;
                    break;
				default:
					$orderfield = '';
					break;
			}
		}
		if(!$field){
			$isdefault = true;
		}
	    $map=[];
	    if($grade){
	    	$map["grade"] = $grade;
	    }
	    if($subject){
	    	$map["subject"] = $subject;
	    }
		if($province){
			$map["province"] = $province;
			if($city){
				$map["city"] =$city;
				if($area){
					$map["area"] = $area;
				}
			}
		}
		if($keywords){
			$map["uname"] = $keywords;
		}
		if($schoolid){
			$map["schoolId"] = $schoolid;
		}

        $uid = $_REQUEST['uid'] ? $_REQUEST['uid'] :$this->mid;
        $type = 0;
        $value  =$epspSvc->teacherCommunityByCondition($map,$pageindex, $pagesize,$orderfield,$roleid,$uid);
        $value= json_decode($value , true);
        if($value ['Code']==0){
            $resultData=$value['Data'];


        $result['data'] = $resultData['userList']['dataList'];
        $result['count'] = $resultData['tsUserCount'];
        $uids = getSubByKey ( $result['data'], 'uid' );
        foreach ($result['data'] as &$tempTsUser){
            $tempTsUser["image"] = model('Avatar')->init($tempTsUser['uid'])->getUserPhotoFromCyCore($tempTsUser['uid']);
        }

		$p = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'ajax_func_name'=>'PeopleSelector.requestData',
				'now_page'=>$pageindex,
				'list_rows'=>$pagesize));

		// 教师工作室新版UI分页链接
		$page = $p->showStudioPager();

		$this->page =$page;
		$this->pageNum = $pageindex;
		$this->data=$result['data'];
		$this->totalcount =$result['count'];
		$this->field = $field;

		if ($roleid == "famteacher") {
			$this->display('famteacherlist');
		} else {
			$this->display('userlist');
		}	
	 }
	}
	
	
	/**
	 * 批量获取用户的相关信息加载
	 *
	 * @param string|array $uids
	 *        	用户ID
	 */
	private function _assignUserInfo($uids) {
		! is_array ( $uids ) && $uids = explode ( ',', $uids );
		$user_info = model ( 'User' )->getUserInfoByUids ( $uids );
		$this->assign ( 'user_info', $user_info );
	}
	
	
	
	/**
	 * 批量获取多个用户的统计数目
	 *
	 * @param array $uids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignUserCount($uids) {
		$user_count = model ( 'UserData' )->getUserDataByUids ( $uids );
		//获取各用户的积分
		foreach ($user_count as $key=>&$user){
			$userScore = model('Credit')->getUserCredit($key);
			$user["creditScore"] = $userScore['credit']['score']['value'];
		}
		$this->assign ( 'user_count', $user_count );
	}
	
	
	/**
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 *
	 * @param array $fids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignFollowState($fids = null,$type = 0) {
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = model ( 'Follow' )->getFollowStateByFids ( $this->mid, $fids,$type );
		$this->assign ( 'follow_state', $follow_state );
	}
	
	/**
	 * 获取城市列表
	 */
	public function getCityList(){
		$provincecode = isset($_REQUEST['provincecode'])?$_REQUEST['provincecode']:'0';
		if($provincecode == '0'){
			$content['status'] = 1;
			$content['data'] = '';
			exit(json_encode($content));
		}
		$result =Model('CyArea')->listAreaByCode($provincecode,'city',0,100);
		if($result){
			$content['status'] = 1;
			$content['data'] = $result;
			$content['msg'] = '成功获取地区信息';
		}else{
			$content['status'] = 0;
			$content['msg'] = '地区信息获取失败';
		}
		exit(json_encode($content));
	}
	
	/**
	 * 获取区县列表
	 */
	public function getCountyList(){
		$citycode = isset($_REQUEST['citycode'])?$_REQUEST['citycode']:'0';
		if($citycode == '0'){
			$content['status'] = 1;
			$content['data'] = '';
			exit(json_encode($content));
		}
		$result =Model('CyArea')->listAreaByCode($citycode,'county',0,100);
		if($result){
			$content['status'] = 1;
			$content['data'] = $result;
			$content['msg'] = '成功获取地区信息';
		}else{
			$content['status'] = 0;
			$content['msg'] = '地区信息获取失败';
		}
		exit(json_encode($content));
	}
	/**
	 * 教研员教学指导展示页面
	 */
	public function instructionList(){
		require_once './apps/paper/Common/privacyInfo.php';
		require_once './apps/paper/Common/appInfo.php';
			
		//获取教研指导类型， 默认为教材介绍
		$type = $_REQUEST['type'] ? $_REQUEST['type'] : appInfo::MATERIAL_INTRO;
		//用户id
		$uid = $_REQUEST['uid'] ? $_REQUEST['uid'] :$this->mid;
		//获取教研指导
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		
		$nav_app_type = getAppName($type);
		//如果不存在该类型，则默认为教材介绍
		if(!$nav_app_type){
			$type = appInfo::MATERIAL_INTRO;
			$nav_app_type = getAppName($type);
		}
		$page_size = 10;
		$total_count = D("Paper", "paper")->where(array("uid"=>$this->uid,'category'=>$type))->count();
		$instruction_list = D("Paper", "paper")->where(array("uid"=>$this->uid,'category'=>$type,"private"=>privacyInfo::All))->order("cTime DESC")->limit($page_size*($pageindex-1).",".$page_size)->findAll();
		//ajax分页
		$p = new AjaxPage(array('total_rows'=>$total_count,
				'method'=>'ajax',
				'ajax_func_name'=>'page_refresh',
				'now_page'=>$pageindex,
				'list_rows'=>$page_size));
		$page = $p->show ();
		$this->assign('page', $page);
		$this->assign('type', $type);
		$this->assign('nav_app_type', $nav_app_type);
		$this->assign('instruction_list', $instruction_list);
		echo $this->display("instruction_list");
	}
	
	
	/**
	 * 学校信息
	 */
	public function getSchoolList(){
		$areaid = isset($_REQUEST['countyid'])?$_REQUEST['countyid']:0;
		if($areaid == '0'){
			$content['data'] = '';
			$content['status'] = 1;
			exit(json_encode($content));
		}
		$content = array();
		if($areaid){
			$result = D("CySchool")->list_school_by_area($areaid,TRUE);
			$content['data'] = $result;
			$content['status'] = 1;
			$content['msg'] = '成功获取数据';
			exit(json_encode($content));
		}
		$content['status'] = 0;
		$content['msg'] = '请求信息出错了';
		exit(json_encode($content));
	}

	
	/**
	 * 根据学校id、学段、入学年份获取班级列表
	 */
	public function getClasses(){
		$schoolid = $_REQUEST['schoolId'];
		$phase = $_REQUEST['phase'];
		$year = $_REQUEST['year'];
		$conditions = array('skip'=>0,'limit'=>1000,'school_id'=>$schoolid,'year'=>intval($year),'phase'=>$phase);
		$result = D("CyCore")->CyCore->retrieveClassInfo($conditions);
		echo json_encode($result);
	}
	
	
}
