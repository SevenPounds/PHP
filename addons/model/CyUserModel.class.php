<?php
include_once (dirname(__FILE__)."/UserRoleTypeModel.class.php");
include_once dirname(__FILE__).'/CyBaseModel.class.php';


/**
 * 用户信息服务
 * @author cheng
 *
 */
class CyUserModel extends CyBaseModel{
	/**
	 * 获取用户管辖的区域或者学校的主键
	 * @param unknown $cyId
	 * @param unknown $type
	 * @return NULL
	 */
	public function getManagAreaId($cyId,$type,$flage = 'id'){
		if(empty($cyId) || empty($type)){
			return null ;
		}
		if($type == ConstantsModel::AREA_TYPE){
			$client = new CyClient();
			$eduList = $client->listAreaByManager($cyId);
			if(!empty($eduList)){
				if($flage == ConstantsModel::CODE_TYPE){
					return $eduList[0]->areaCode;
				}else if($flage == ConstantsModel::ID_TYPE){
					return $eduList[0]->id;
				}else {
					return $eduList[0]->id;
				}
			}else {
				return null ;
			}
			/* dump($eduList);die;
			$eduList = $this->client->listEduorgByUser($cyId);
			$edu = $eduList[0] ;
			if(!empty($edu->districtId) || $edu->districtId != '0'){
				return $edu->districtId ;
			}else if(!empty($edu->cityId) || $edu->cityId != '0'){
				return $edu->cityId ;
			}else if(!empty($edu->provinceId) || $edu->provinceId != '0'){
				return $edu->provinceId ;
			}else{
				return $edu->countryId ;
			} */
		}else if($type == ConstantsModel::SCHOOL_TYPE){
			$schoolist = $this->client->listSchoolByUser($cyId);
			return $schoolist[0]->id;
		}else {
			return null ;
		}
	}
	/**
	 *哦按段用户是否为机构管理者，可携袋级别参数
	 * @param unknown $userInfo
	 * @param unknown $level
	 * @return boolean
	 */
	public function isEduManager($userInfo,$level=''){
		$roleList = $userInfo['rolelist'];
		if(empty($roleList)){
			return false ;
		}
		//$flage = false ;
		foreach ($roleList as $key=>$val){
			if(!empty($val) && $val['name'] == ConstantsModel::EDU_MANAGER){
				if(empty($level)){
					return true ;
					break ;
				}else{
					//$flage = true ;
					break ;
				}
			}
		}
		$client = new CyClient();
		$userDetail =$client->getUserDetail($userInfo['user']['cyuid']) ;
		$userLevel = $userDetail->userExt1->ext_str_01;

		
		if($userLevel === $level){
			return true ;
		}else{
			return false ;
		}
	}
	/**
	 * 用户数据转换
	 * @param object $cyuser
	 * @return array
	 */
	public function cyUserConvert($cyuser){
		$user  = array();
		$user['cyuid'] = $cyuser->id;
		$user['uname'] =  $cyuser->userName;
		$user['login'] =  $cyuser->loginName;
		$user['email'] = $cyuser->email;
		$user['pinyinName'] = $cyuser->pinyinName;
		$user['isdel'] = $cyuser->delFlag;
		$user['sex'] = $cyuser->gender==1 ? $cyuser->gender:2;
		return $user;
	}
	
	/**
	 * 获取cy用户信息
	 * @param integer $cyuid
	 */
	public function getUserInfo($cyuid){
		if(empty($cyuid)) return array();
		$_result = $this->client->getUser($cyuid);
		return  $this->user_convert($_result);
	}
	
	/**
	 * 获取cy用户详细信息
	 * @param string $cyuid
	 */
	public function getUserDetail($cyuid){
		if(empty($cyuid)) return array();
		$_result = $this->client->getUserDetail($cyuid);
		$user = $this->user_convert($_result->user);	
		$user['grade'] = $_result->userExt1->ext_str_02;
		$user['subject'] = $_result->userExt1->ext_str_03;
		$user['instructor_level'] =  $_result->userExt1 ? $_result->userExt1->ext_str_01 : NULL;//三级教研员标志扩展字段
		$user['position'] = $_result->userExt1->position;
		$user['job_title'] = $_result->userExt1->job_title;
		$user['honor'] = $_result->userExt1->honor;
		$user['phase'] = $_result->userExt1->ext_str_02;
		$user['mobile'] = $_result->user->mobile;
		$user['is_can_init'] = $_result->userExt1->ext_int_04 == 2 ? false : true;
		$user['level'] = $_result->userExt1->ext_str_01;
		return $user;
	}
	
	/**
	 * 
	 * @param string $login
	 */
	public function getUserByLoginName($login){
		if(empty($login)) return array();
		$_result = $this->client->getUserByUniqueInfo('login_name',$login);
		return $this->user_convert($_result);
	}

	/**
	 * 获取最后错误信息
	 *
	 * @return string 最后错误信息
	 */
	public function getLastError() {
		return $this->error;
	}
	
	/**
	 * 获取班级成员
	 * @param int $classId
	 * @param string $roleEnName
	 * @param int $skip
	 * @param int $limit
	 */
	public function  listUserByClass($classId, $roleEnName, $skip = 0, $limit = 50){
		if(empty($classId)) return array();
		$_result = $this->client->listUserByClass($classId, $roleEnName,$skip,$limit);
		$cyusers = $this->muti_user_convert($_result);
		return $this->convert_userData($cyusers);
	}
	
	/**
	 * 
	 * @param int $schoolId
	 * @param string $roleEnName
	 * @param int $skip
	 * @param int $limit
	 */
	public function listUserBySchool($schoolId, $roleEnName, $skip = 0, $limit = 50) {
		if(empty($schoolId)) return array();
		$_result = $this->client->listUserBySchool($schoolId, $roleEnName,$skip,$limit);
		$cyusers = $this->muti_user_convert($_result);
		return $this->convert_userData($cyusers);
	}

	private function  convert_userData($cyusers){
		foreach($cyusers as $cyuser){
			$user =  model('User')->getUserInfoByLogin($cyuser['login']);
			if(!empty($user)){
				$user['is_synchronous'] =1;
				$user['follow_state'] = model('Follow')->getFollowState($GLOBALS['ts']['mid'],$user['uid']);
			}else{
				$empty_url = THEME_URL.'/_static/image/noavatar';
				$avatar_url = array(
						'avatar_original' 	=> $empty_url.'/big.jpg',
						'avatar_big' 		=> $empty_url.'/big.jpg',
						'avatar_middle' 	=> $empty_url.'/middle.jpg',
						'avatar_small' 		=> $empty_url.'/small.jpg',
						'avatar_tiny' 		=> $empty_url.'/tiny.jpg'
				);
				$user['is_synchronous'] =0;
				$user = array_merge($cyuser,$avatar_url);
			}
			unset($userdata);
			$users[] = $user;
			unset($user);
		}
		return $users;
	}
	
	/**
	 * 获取cycore用户信息,缓存有效期1小时
	 * @param string $login
	 * @return $cyuserdata
	 */
	public function getCyUserInfo($login){
		if(empty($login))  return array();
		$cyuserdata = S("CyUserInfo_".$login);
		//检测cycore数据是否与本地数据相同,如果不同则清空缓存
		$cyuser = $this->getUserByUniqueInfo('login_name',$login);
		$user = M('User')->getUserInfoByLogin($login);
		if(strtotime($cyuser['updatetime']) > $user['updatetime']){
			$cyuserdata = array();
		}
		if(empty($cyuserdata)){
			if(empty($cyuser['cyuid'])){
				return array();
			}
			$cyuserdata['user'] = $cyuser;
			// 初始化个人信息
			$schools = $this->muti_school_convert($this->client->listSchoolByUser($cyuser['cyuid']));
			$cyuserdata['orglist']['school'] = empty($schools)?$this->muti_eduorg_convert($this->client->listEduorgByUser($cyuser['cyuid'])):$schools;
			$cyuserdata['orglist']['class'] = $this->muti_class_convert($this->client->listClassByUser($cyuser['cyuid']));
			if(!empty($cyuserdata['orglist']['school'])){
				$school =$cyuserdata['orglist']['school'];
				$cyuserdata['locations'] = Model("CyArea")->getFullAreaByOrgnization(array_pop($school));
			}else{
				$cyuserdata['locations'] = null;
			}
			$cyuserdata['rolelist'] = $this->listRoleByUser($cyuser['cyuid']);
			$expire = 3600;//一个小时
			S("CyUserInfo_".$login,$cyuserdata,$expire);
		}
		return $cyuserdata;
	}
	
	/**
	 * 通过用户cyuid获取角色列表
	 * @param int $cyuid
	 */
	public function listRoleByUser($cyuid){
		$rolelist = $this->client->listRoleByUser($cyuid);
		//Log::write("映射前用户角色列表：".json_encode($rolelist),Log::DEBUG);
		$rolelist = $this->getMappingRoles($rolelist);
		//Log::write("映射后用户角色列表：".json_encode($rolelist),Log::DEBUG);
		$result = UserRoleTypeModel::getFirstRole($rolelist);
		return $result;
	}
	
	public function getMappingRoles($roleList){
		// 获取用户角色映射表
		$roleMaps = S('qxptRoleMapList');
		if(!$roleMaps){
			$roleMaps = D('UserRoleMap')->listRoleMap();
			S('qxptRoleMapList',$roleMaps,24 * 3600);
		}
		
		for($i = 0; $i <count($roleList); $i++){
			foreach ($roleMaps as $key=>$val){
				if($roleList[$i]->enName == $val['en_name']){
					$roleList[$i] = $this->client->getRoleByEnName($val['cloud_role']);
					break;
				}
			}
		}
		return $roleList;
	}
	
	/**
	 * 根据用户类型名获取映射后的角色信息
	 * @param string $roleType 用户类型
	 * @return array(); 用户角色信息
	 */
	public function getMappingRole($roleType){
		// 获取用户角色映射表
		$roleMaps = S('qxptRoleMapList');
		if(!$roleMaps){
			$roleMaps = D('UserRoleMap')->listRoleMap();
			S('qxptRoleMapList',$roleMaps,24 * 3600);
		}
	
		// 遍历检查映射
		foreach ($roleMaps as $key=>$val){
			if($roleType == $val['en_name']){
				$roleInfo = $this->client->getRoleByEnName($val['cloud_role']);
				return $roleInfo;
			}
		}
		return null;
	}
	
	/**
	 * 用户角色判断
	 * @param int $uid
	 * @param string $roleEnName
	 */
	private  function IsAuthority($cyuid,$roleEnName){
		if(empty($cyuid)||empty($roleEnName)) return false;
		return $this->client->hasRole($cyuid,$roleEnName);
	}
	
	/**
	 * 用户角色判断
	 * @param string $cyuid
	 * @param string $roleEnName
	 */
	public function hasRole($roleEnName,$cyuid){
		if(empty($roleEnName) || empty($cyuid)){
			return false;
		}
		if(empty($cyuid) || $cyuid == $GLOBALS['ts']['cyuserdata']['user']['cyuid']) {
			$cyuid = $GLOBALS['ts']['cyuserdata']['user']['cyuid'];
			$cyuserData =$GLOBALS['ts']['cyuserdata'];
		}elseif($cyuid == $GLOBALS['ts']['_cyuserdata']['user']['cyuid']){
			$cyuserData =$GLOBALS['ts']['_cyuserdata']; 
		}else{
			$cyuserData = $this->getUserInfo($cyuid);
		}
		if(!empty($cyuserData['rolelist'])){
			foreach($cyuserData['rolelist']  as $role){
				if($role['name']==$roleEnName){
					return true;
				}
			}
		}else{
			//TODO 默认教师角色 yuliu2
			return $roleEnName == UserRoleTypeModel::TEACHER;
		}
		return false;
	}
	
	
	/**
	 * 
	 * @param string $login
	 * @param string $password
	 * return 0: 失败; 1:成功; 2:未激活
	 */
	public function validateUser($login, $password){
		if(empty($login) || empty($password)) return 0;
		$_result =  $this->client->validateUser('login_name',$login, $password);
		return $_result;
	}
	
	
	
	/**
	 * 检查指定字段信息的用户是否存在
	 * @param String $key 指定字段名，如`login_name`, `email`, `phone`
	 * @param String $value 指定字段取值
	 * @return boolean true: 存在; false: 不存在
	 */
	public function existUser($key,$value){
		if(empty($key) || empty($value)) return false;
		$_result =  $this->client->existUser($key,$value);
		return $_result;
	}
	
	
	/**
	 * 更新用户密码
	 * @param String $loginName 用户登录名
	 * @param String $oldPassword 旧密码
	 * @param String $newPassword 新密码
	 * @return boolean true: 成功; false: 失败
	 */
	public function update_password($loginName, $oldPassword, $newPassword){
		if(!$loginName||!$oldPassword||!$newPassword){
			return false;
		}
		$_result = $this->client->updatePassword($loginName, $oldPassword, $newPassword);
		if(empty($_result)){
			return false;
		}
		return $_result;
	}
	
	/**
	 * cycore 用户登录入库
	 * @param array $cyuser
	 */
	private function _cyLogin($cy_user){
		$local_user = model('User')->where(array('login' => $cy_user['login'], 'is_del' => 0))->find();
		static_cache('user_' . $cy_user['login'], $local_user);
		$updatetime = empty($cy_user['updatetime'])?$cy_user['createtime']:$cy_user['updatetime'];
		$updatetime = strtotime($updatetime);
		$userExt = $this->getUserDetail($cy_user['cyuid']);
		//待激活账号，不初始化空间
		if(!$userExt['is_can_init']){
			return false;
		}
		//获取用户学科、学段信息
		$roles = $this->listRoleByUser($cy_user['cyuid']);
		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($cy_user['login'], $roles);
		if(!empty($roles) && $roleEnName == UserRoleTypeModel::RESAERCHER){
			$insTeaching = $this->client->listInstructorTeaching($cy_user['cyuid'], 0, 1);
			if(!empty($insTeaching)){
				$cy_user['grade'] = $insTeaching[0]['phase'];
				$cy_user['subject'] = $insTeaching[0]['subject'];
			}	
		}
		else{
			$cy_user['grade'] = $userExt['grade'] ;
			$cy_user['subject'] = $userExt['subject'] ;
		}
		$cy_user['instructor_level'] = $userExt['instructor_level'] ;
		if(!$local_user){
			//	Log::write('***开始写入cycore用户数据*** ',"INFO");
			//写入本地系统
			$login_salt = rand(11111, 99999);
			$map['cyuid'] = $cy_user['cyuid'];
			$map['uname'] = $cy_user['uname'];
			//本地数据库性别1为男，2为女 
			//由于sex字段为必填字段，sex默认为0 --by ylzhao
			$map['sex'] = empty($cy_user['sex']) ? 0 : $cy_user['sex'];
			$map['grade'] = $cy_user['grade'];
			$map['subject'] = $cy_user['subject'];
			
			$map['login_salt'] = $login_salt;
			$map['password'] = md5(md5(111111).$login_salt);
			$map['email'] = $cy_user['email'];
			$map['login'] = $cy_user['login'];
			$map['reg_ip'] = get_client_ip();
			$map['ctime'] = time();
			$map['updatetime'] = $updatetime;//strtotime($updatetime)
			$map['is_audit'] = 1;
			$map['is_active'] = 1;
			$map['is_init'] = 1; // 用户已经初始化
			$map['first_letter'] = getFirstLetter($cy_user['uname']);
			//如果包含中文将中文翻译成拼音
			if ( preg_match('/[\x7f-\xff]+/', $map['uname'] ) ){
				//昵称和呢称拼音保存到搜索字段
				$map['search_key'] = $map['uname'].' '.model('PinYin')->Pinyin( $map['uname'] );
			} else {
				$map['search_key'] = $map['uname'];
			}
            //更新家长角色的区域信息为子女的区域信息  by tkwang 2015-7-2
            if(($childrens=$this->client->listChildren($map['cyuid']))|| count($childrens)>0){
                krsort($childrens);
                $children = current($childrens);
            }
            //更新区域信息和学校信息by zhaoliang
			$schoolinfo = $this->getSchoolInfo((isset($children)&&!empty($children->id))?$children->id:$map['cyuid']);
			$map['school_id'] = $schoolinfo[0];
			$map['school'] = $schoolinfo[1];
			$map['province'] = $schoolinfo[2] ? $schoolinfo[2] :""; //默认安徽省
				$map['city'] = $schoolinfo[4] ? $schoolinfo[4] :""; //默认合肥市
				$map['area'] = $schoolinfo[6];
				if(isset($schoolinfo[3])){
					$location = $schoolinfo[3];
					if(isset($schoolinfo[5])){
						$location = $schoolinfo[3]." ".$schoolinfo[5];
						if(isset($schoolinfo[7])){
							$location = $schoolinfo[3]." ".$schoolinfo[5]." ".$schoolinfo[7];
						}
					}
				}
				$map['location'] = $location;
			//个人简介
			$map['intro'] = $cy_user['intro'];
			$ts_uid = model('User')->add($map);
			// 添加积分
			model('Credit')->setUserCredit($ts_uid,'init_default');
			// 添加至默认的用户组
			$userGroup = model('Xdata')->get('admin_Config:register');
			$userGroup = empty($userGroup['default_user_group']) ? C('DEFAULT_GROUP_ID') : $userGroup['default_user_group'];
			model('UserGroupLink')->domoveUsergroup($ts_uid, implode(',', $userGroup));
			//	model ( 'Register' )->overUserInit ( $cy_user->id );//用户信息初始化
			if(empty($ts_uid)){
				$this->error = '同步用户信息失败，请联系管理员';
				//Log::write('***写入cycore用户数据失败*** ','ERR');
				return false;
			}
			$this->_saveRole($ts_uid, $cy_user);
			
			//	标记网关上当前用户空间已激活
			$cyMap = array();
			$cyMap['cyuid'] = $cy_user['cyuid'];
			$cyMap['ext1_ext_int_01'] = 1;  //1：用户空间激活
			$this->updateUser($cyMap);
// 			$this->updateSolrUserZone($ts_uid);
		}else{
			//if($cy_user['updatetime'] && $cy_user['modificator'] != $cy_user['login'] && $local_user['updatetime'] != $updatetime  && $local_user['updatetime'] != $local_user['ctime'] || strcmp($local_user['uname'],$cy_user['uname']) != 0){
			if($updatetime > $local_user['updatetime']){
				$map['uid'] = $local_user['uid'];
				$map['uname'] = $cy_user['uname'];
				$map['email'] = $cy_user['email'];
				//由于sex字段为必填字段，sex默认为0 --by ylzhao
				$map['sex'] = empty($cy_user['sex']) ? 0 : $cy_user['sex'];
				$map['updatetime'] = $updatetime;
				$map['cyuid'] = $cy_user['cyuid'];
				$map['grade'] = $cy_user['grade'];
			    $map['subject'] = $cy_user['subject'];
			    $map['first_letter'] = getFirstLetter($cy_user['uname']);
			    //如果包含中文将中文翻译成拼音
			    if ( preg_match('/[\x7f-\xff]+/', $map['uname'] ) ){
			    	//昵称和呢称拼音保存到搜索字段
			    	$map['search_key'] = $map['uname'].' '.model('PinYin')->Pinyin( $map['uname'] );
			    } else {
			    	$map['search_key'] = $map['uname'];
			    }
                //更新家长角色的区域信息为子女的区域信息  by tkwang 2015-7-2
                if(($childrens=$this->client->listChildren($map['cyuid']))|| count($childrens)>0){
                    krsort($childrens);
                    $children = current($childrens);
                }
                //更新区域信息和学校信息by zhaoliang
			    $schoolinfo = $this->getSchoolInfo((isset($children)&&!empty($children->id))?$children->id:$map['cyuid']);
				$map['school_id'] = $schoolinfo[0];
				$map['school'] = $schoolinfo[1];
				$map['province'] = $schoolinfo[2] ? $schoolinfo[2] : ""; //默认安徽省
				$map['city'] = $schoolinfo[4] ? $schoolinfo[4] :""; //默认合肥市
				$map['area'] = $schoolinfo[6];
				if(isset($schoolinfo[3])){
					$location = $schoolinfo[3];
					if(isset($schoolinfo[5])){
						$location = $schoolinfo[3]." ".$schoolinfo[5];
						if(isset($schoolinfo[7])){
							$location = $schoolinfo[3]." ".$schoolinfo[5]." ".$schoolinfo[7];
						}
					}
				}
				$map['location'] = $location;
				//个人简介
				$map['intro'] = $cy_user['intro'];
				$res = model('User')->save($map);
				$this->_saveRole($local_user['uid'], $cy_user);
// 				$this->updateSolrUserZone($map['uid']);
				//bug修复：后台修改用户信息，空间信息因缓存不能及时显示的bug
				static_cache ( 'user_info_' . $local_user['uid'], false );
				model ( 'Cache' )->rm('ui_' . $local_user['uid']);
				model ( 'Cache' )->rm('userData_uid' . $local_user['uid']);
			}
			
			$ts_uid = $local_user['uid'];
		}
		unset($map);
		unset($userExt);
		return $ts_uid;
	}
	
	/**
	 * cycore 用户登录
	 * @param array $cyuser
	 */
	public function cyLogin($cyuser){
		$uid = $this->_cyLogin($cyuser);
		return model('Passport')->loginLocalWhitoutPassword($cyuser['login']);
	}
	
	/**
	 * 根据用户名初始化用户个人空间
	 * @param string $login 用户登录名
	 */
	public function initUserSpace($login){
		$cyuserdata = $this->getCyUserInfo($login);
		$cyuser = $cyuserdata['user'];
		if(!empty($cyuser['cyuid'])){
			$initSpace = $this->_cyLogin($cyuser);
			Log::write("用户[$login]初始化空间结果：".json_encode($initSpace),Log::DEBUG);
			return $initSpace;
		}
	}
	
	/**
	 * 插入、更新SOLR
	 * @param int $ts_uid 用户id
	 */
	private function updateSolrUserZone($ts_uid){
		//提交相关信息到solor服务器 sjzhao
		$userrole = model('UserRole')->getUserRole($ts_uid);
		$type = $userrole['rolename'];
		in_array($type,array(UserRoleTypeModel::PROVINCE_RESAERCHER,UserRoleTypeModel::CITY_RESAERCHER,UserRoleTypeModel::COUNTY_RESAERCHER,UserRoleTypeModel::RESAERCHER))  && $type = 'researcher';
		switch ($type){
			case 'teacher':
				$type = ZoneTypeModel::TEACHER;
				break;
			case 'researcher':
				$type = ZoneTypeModel::RESEARCHER;
				break;
		}
		return model('SolrZone')->update($type,$ts_uid);
	}
	
	/**
	 * 插入、更新SOLR
	 * @param int $ts_uid 用户id
	 */
	public function updateSolrUser($ts_uid) {
		return $this->updateSolrUserZone($ts_uid);
	}
	
	/**
	 * 用户资料更新 
	 * @param array $cyUser
	 */
	public function updateUser($cyUser){
		$cyUserMap['id'] = $cyUser['cyuid'];
		isset($cyUser['uname']) && $cyUserMap['user_name'] = $cyUser['uname'].'';
		isset($cyUser['sex']) && $cyUserMap['gender'] = $cyUser['sex'].'';
		isset($cyUser['intro']) && $cyUserMap['remark'] = $cyUser['intro'] ;
		isset($cyUser['position']) && $cyUserMap['position'] = $cyUser['position'] ;
		isset($cyUser['honor']) && $cyUserMap['honor'] = $cyUser['honor'] ;
		isset($cyUser['job_title']) && $cyUserMap['job_title'] = $cyUser['job_title'] ;
		isset($cyUser['ext1_ext_int_01']) && $cyUserMap['ext1_ext_int_01'] = intval($cyUser['ext1_ext_int_01']);
		isset($cyUser['grade']) && $cyUserMap['ext1_ext_str_02'] = $cyUser['grade'];
		isset($cyUser['subject']) && $cyUserMap['ext1_ext_str_03'] = $cyUser['subject'];
        isset($cyUser['email']) && $cyUserMap['email'] = $cyUser['email'];
		//设置教师多学科
		if(isset($cyUser['classes']) && isset($cyUser['subjects'])){
			foreach($cyUser['classes'] as $key=>$v){
				if(empty($v))  continue;
				$this->client->setTeacherSubject($cyUserMap['id'],$v,$cyUser['subjects'][$key]);
			}
		}

		//设置教研员多学科
		if(isset($cyUser['oldteaching'])){
			foreach($cyUser['oldteaching']  as $key=>$v){
				if(empty($v))  continue;
				$this->client->removeInstructorTeaching($cyUserMap['id'],$v['grade'],$v['subject']);
			}
		}
		
		if(isset($cyUser['grades']) && isset($cyUser['subjects'])){
			foreach($cyUser['grades'] as $key=>$v){
				if(empty($v))  continue;
				$this->client->setInstructorTeaching($cyUserMap['id'],$v,$cyUser['subjects'][$key]);
			}
		} 
		
		//设置学生班级信息
		if($cyUser['class'] && isset($cyUser['oldclass']) && $cyUser['class']!=$cyUser['oldclass'] ){
			//如果有老班级则删除班级，没有则不操作；
			empty($cyUser['oldclass'])?"":$this->client->removeUserFromClass(array($cyUserMap['id']),'student',$cyUser['oldclass']);
			$this->client->setUserToClass(array($cyUserMap['id']),'student',$cyUser['class']);
		}
		S("CyUserInfo_".$GLOBALS['ts']['user']['login'],null);  //更新用户信息
		return $_result =  $this->client->updateUser($cyUserMap);
	}
	
	/**
	 * 保存cycore用户角色
	 * @param integer $uid
	 * @param array $cyuser
	 */
	private function _saveRole($uid,$cyuser){
		$cyRoles = empty($cyuser['rolelist'])?$this->listRoleByUser($cyuser['cyuid']):$cyuser['rolelist'];
		if(empty($cyRoles)){
				return ;
		}
		$roleModel = model('UserRole');
		$userRoles = $roleModel->where("`uid` = '{$uid}'")->findAll();

		
		foreach ($cyRoles as $cyRole ){
			$role_map =array();
			$role_map['uid'] = $uid;
			if($cyRole['name'] == UserRoleTypeModel::RESAERCHER){
				//三级教研员
				if(UserRoleTypeModel::isHasSelectionRight($cyuser['instructor_level'])){
					$role_map['rolename'] = $cyuser['instructor_level'];
				}else{
					$role_map['rolename'] = UserRoleTypeModel::RESAERCHER;
				}
			}else{
				$role_map['rolename'] = $cyRole['name'];
			}
			$userRole = $roleModel->where($role_map)->find();
		
			if(empty($userRole)){
				$role_map['roleid'] = $cyRole['id'];
				$roleModel->add($role_map);
			}
			
			foreach($userRoles as $k=>$v){
				if($role_map['rolename'] === $v['rolename'] ){
					  unset($userRoles[$k]);
				}
			}
			
			unset($userRole);
			unset($role_map);
		}
		if(!empty($userRoles)){
			$_roles = getSubByKey($userRoles,'rolename');
			$condition = "'".implode("','",$_roles)."'";
			$map['rolename'] = array('in',$condition);
			$map['uid'] = $uid ;
			$roleModel->where($map)->delete();
			unset($map);
		}
		unset($_cyEnNames);
		unset($_localEnNames);
	}
	
	
	/**
	 * 获取用户详细信息
	 * @param string $key
	 * @param string $value
	 * return  array
	 */
	public function getUserByUniqueInfo($key, $value){
		if(empty($key) || empty($value)) return array();
		$_result = $this->client->getUserByUniqueInfo($key, $value);
		return $this->user_convert($_result);
	}


	/**
	 * 获取用户下辖区域信息
	 * @param string $cyuid 用户cyuid
	 * return  array
	 */
	public function getAreaByCyuid($cyuid){
		$_result =  $this->client->listAreaByManager($cyuid);
		return $_result;
	}
	
	/**
	 * 获取学校信息
	 */
	public function getSchoolInfo($uid){
		$schools = $this->muti_school_convert($this->client->listSchoolByUser($uid,0,1));
		if($schools){
			foreach($schools as $v){
				$province = $this->client->getArea($v["provinceId"]);
				$v["provinceCode"] = $province->areaCode;
				$v["provinceName"] = $province->areaName;
				$city = $this->client->getArea($v["cityId"]);
				$v["cityCode"] = $city->areaCode;
				$v["cityName"] = $city->areaName;
				$district = $this->client->getArea($v["districtId"]);
				$v["districtCode"] = $district->areaCode;
				$v["districtName"] = $district->areaName;
				return array($v["id"],$v["name"],$v["provinceCode"],$v["provinceName"],$v["cityCode"],$v["cityName"],$v["districtCode"],$v["districtName"]);
			}
		}
		$orgs = $this->muti_eduorg_convert($this->client->listEduorgByUser($uid),0,1);
		if($orgs){
			foreach($orgs as $v){
				$province = $this->client->getArea($v["provinceId"]);
				$v["provinceCode"] = $province->areaCode;
				$v["provinceName"] = $province->areaName;
				$city = $this->client->getArea($v["cityId"]);
				$v["cityCode"] = $city->areaCode;
				$v["cityName"] = $city->areaName;
				$district = $this->client->getArea($v["districtId"]);
				$v["districtCode"] = $district->areaCode;
				$v["districtName"] = $district->areaName;
				return array($v["id"],$v["name"],$v["provinceCode"],$v["provinceName"],$v["cityCode"],$v["cityName"],$v["districtCode"],$v["districtName"]);
			}
		}
		return array(-1,"");
	}
	
	/**
	 * 获取区域信息
	 */
	public function getAreaInfo($code){
		$_result = D('CyArea')->getAreaByCode($code);
		return $_result["name"];
	}
	
	
	
	/**
	 * 查询教研员用户
	 * @param array $params 查询条件(仅支持 array('eduorg_id'=>, 'subject'=>) 需要添加条件请阅读retrieveInstructor接口文档
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchInstructorUser($params, $page = 1, $limit = 20) {
		//查询条件
		$queryParams = array();
		$queryParams['ext1_ext_int_01'] = 1;
		if (!empty($params['eduorg_id'])) $queryParams['eduorg_id'] = $params['eduorg_id'];
		else $queryParams['area_id'] = 1123; //教研员接口不传机构id时默认为省id
		if (!empty($params['subject'])) $queryParams['ext1_ext_str_03'] = $params['subject'];
		$queryParams['skip'] = ($page - 1) * $limit;
		$queryParams['limit'] = $limit + 1;
		$dataList = $this->client->retrieveInstructor($queryParams);
		foreach ($dataList as &$r) {
			$r->user = model('User')->getUserInfoByLogin($r->loginName);
		}
		return $dataList;
	}
	
	/**
	 * 查询教师用户
	 * @param array $params 查询条件(仅支持 array('city_id'=>, 'district_id'=>, 'school_id'=>, 'subject'=>) 需要添加条件请阅读retrieveTeacher接口文档
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchTeacherUser($params, $page = 1, $limit = 20) {
		//查询条件
		$queryParams = array();
		$queryParams['ext1_ext_int_01'] = 1;
        if (!empty($params['province_id']))  $queryParams['area_id'] = $params['province_id'];
		if (!empty($params['city_id']))  $queryParams['area_id'] = $params['city_id'];
		if (!empty($params['district_id'])) $queryParams['area_id'] = $params['district_id'];
		if (!empty($queryParams) && !empty($params['school_id'])) {
			$queryParams['school_id'] = $params['school_id'];
			unset($queryParams['area_id']);
		}
		if (!empty($params['subject'])) $queryParams['ext1_ext_str_03'] = $params['subject'];
		$queryParams['skip'] = ($page - 1) * $limit;
		$queryParams['limit'] = $limit + 1;

		$dataList = $this->client->retrieveTeacherExt($queryParams);
		foreach ($dataList as &$r) {
			$r->user = model('User')->getUserInfoByLogin($r->loginName);
		}
		return $dataList;
	}
	
	/**
	 * 查询教研员/教师用户
	 * @param string $user_name
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchInstructorAndTeacher($user_name, $page = 1, $limit = 20) {
		//查询条件
		$queryParams = array();
		$queryParams['ext1_ext_int_01'] = 1;
		$queryParams['user_name'] = t($user_name);
		$queryParams['skip'] = ($page - 1) * $limit;
		$queryParams['limit'] = $limit + 1;
		$dataList = $this->client->retrieveInstructorAndTeacher($queryParams);
		foreach ($dataList as &$r) {
			$r->user = model('User')->getUserInfoByLogin($r->loginName);
		}
		return $dataList;
	}
	
	
	/**
	 * 获取教研员学科信息
	 * @param string $userId
	 * @param int $skip
	 * @param int $limit
	 * @return mixed array
	 */
	public function listInstructorTeaching($userId, $skip=0, $limit=50){
		$_result =  $this->client->listInstructorTeaching($userId, $skip, $limit);
		$result = array();
		foreach ($_result as $k=>$v){
			array_push($result,array('grade'=>$v['phase'],'subject'=>$v['subject']));
		}
		return $result;
	}
	
	
	/**
	 * 教研员删除学科信息
	 * @param string $userId
	 * @param string $phase
	 * @param string $subject
	 */
	public function removeInstructorTeaching($userId, $phase, $subject){
		$_result =  $this->client->removeInstructorTeaching($userId, $phase, $subject);
		return $_result;
	}
	
	/**
	 * 根据用户cyuid获取用户角色类型
	 * @param string $cyuid 用户cyuid
	 */
	public function getCloudUserRole($cyuid){
		// 之前是获取用户角色
		$roles = $this->client->listRoleByUser($cyuid);
		// 获取用户身份映射到角色
//		$roles = array();
//		$client = new \CyClient();
//		$userTypeInfo = $client->getListUserReviewByUserId($cyuid);
//		$roleTypeArr = array(
//			"001"=>"orger",
//			"002"=>"teacher",
//			"003"=>"student",
//			"004"=>"parent"
//		);
//		// 将身份映射为角色名
//		$userTypeInfoLen = count($userTypeInfo);
//		for($i=0;$i<$userTypeInfoLen;$i++){
//			if($userTypeInfo[$i]->reviewStatus=='110002'||$userTypeInfo[$i]->reviewStatus==null){
//				$everyRole = new \stdClass();
//				$everyRole->enName = $roleTypeArr[$userTypeInfo[$i]->userType];
//				$roles[] = $everyRole;
//			}
//		}

		$result = array();
		if(!empty($roles)){
			$user = $this->client->getUserInfo($cyuid);
			//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
			$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $roles);
			$cloudName = $roleEnName;
			$roleMaps = D("UserRoleMap")->listRoleMap();
			
			// 处理角色映射关系
			foreach($roleMaps as $key=>$val){
				if($roleEnName == $val['en_name']){
					$cloudName = $val['cloud_role'];
					break;
				}
			}
			$result['status'] = '200';
			$result['data'] = $cloudName;
		}else{
			$result['status'] = '400';
			$result['message'] = '获取角色类型失败';
			$result['data'] = '';
		}
		return $result;
	}
    
    //获取教育机构管理者的级别
	public function listLevel(){
		$levelList = $this->client->listLevel();
		return $levelList;
	}

	public function listChildsByParentId($parentId){
		$children = $this->client->listChildren($parentId);
		return $children;
	}
}
?>