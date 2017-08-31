<?php
/**
 * ProfileAction 个人档案模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class ProfileAction extends Action {
	/**
	 * _initialize 模块初始化
	 * 
	 * @return void
	 */
	protected function _initialize() {
		// 短域名判断
		if (! isset ( $_GET ['uid'] ) || empty ( $_GET ['uid'] )) {
			$this->uid = $this->mid;
		} elseif (is_numeric ( $_GET ['uid'] )) {
			$this->uid = intval ( $_GET ['uid'] );
		} else {
			$map ['domain'] = t ( $_GET ['uid'] );
			$this->uid = model ( 'User' )->where ( $map )->getField ( 'uid' );
		}

		//判断角色 
		$is_Researcher =  D('CyUser')->hasRole(UserRoleTypeModel::RESAERCHER,$this->cymid);//是否为教研员
		$this->assign ( 'uid', $this->uid );
		$this->assign ( 'is_Researcher', $is_Researcher );
	}
	/**
	 * 隐私设置
	 */
	public function privacy($uid) {
		if ($this->mid != $uid) {
			$privacy = model ( 'UserPrivacy' )->getPrivacy ( $this->mid, $uid );
			return $privacy;
		} else {
			return true;
		}
	}
	
	/**
	 * 个人档案展示页面
	 */
	public function index() {
		// 老的链接，直接跳转至新的主页
        $this->uid = $GLOBALS['ts']['uid'];
		if($this->uid != '0'){
			//判断用户是否存在
			$user = model ( 'User' )->getUserInfo ( $this->uid );
			if($user){
				U('public/Workroom/index',array('uid'=>$this->uid),true);
			}
		}else{
			$this->redirect('public/Index/index');
		}
		if(empty($_GET['feed_type'])){
			//教研员有单独主页
		 	if(D('CyUser')->hasRole(UserRoleTypeModel::RESAERCHER,$this->cyuid)||D('CyUser')->hasRole(UserRoleTypeModel::TEACHER,$this->cyuid)){
				U('public/Workroom/index',array('uid'=>$this->uid),true);
			}
		}
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();

		//记录来访者
        recordVisitor($this->uid, $this->mid);
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 加载微博筛选信息
			$d ['feed_type'] = t ( $_REQUEST ['feed_type'] ) ? t ( $_REQUEST ['feed_type'] ) : '';
			$d ['feed_key'] = t ( $_REQUEST ['feed_key'] ) ? t ( $_REQUEST ['feed_key'] ) : '';
			$this->assign ( $d );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		
		// 添加积分
		model ( 'Credit' )->setUserCredit ( $this->uid, 'space_access' );
		
		$this->assign ( 'userPrivacy', $userPrivacy );
		// seo
		$seo = model ( 'Xdata' )->get ( "admin_Config:seo_user_profile" );
		$replace ['uname'] = $user_info ['uname'];
		if ($feed_id = model ( 'Feed' )->where ( 'uid=' . $this->uid )->order ( 'publish_time desc' )->limit ( 1 )->getField ( 'feed_id' )) {
			$replace ['lastFeed'] = D ( 'feed_data' )->where ( 'feed_id=' . $feed_id )->getField ( 'feed_content' );
		}
		$replaces = array_keys ( $replace );
		foreach ( $replaces as &$v ) {
			$v = "{" . $v . "}";
		}
		$seo ['title'] = str_replace ( $replaces, $replace, $seo ['title'] );
		$seo ['keywords'] = str_replace ( $replaces, $replace, $seo ['keywords'] );
		$seo ['des'] = str_replace ( $replaces, $replace, $seo ['des'] );
		! empty ( $seo ['title'] ) && $this->setTitle ( $seo ['title'] );
		! empty ( $seo ['keywords'] ) && $this->setKeywords ( $seo ['keywords'] );
		! empty ( $seo ['des'] ) && $this->setDescription ( $seo ['des'] );
		$this->display ();
	}
	function appList() {
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_assignUserInfo ( $this->uid );
		
		$appArr = $this->_tab_menu();
		$type = t ( $_GET ['type'] );
		if (! isset ( $appArr [$type] )) {
			$this->error ( '参数出错！！' );
		}
		$this->assign('type', $type);
		$className = ucfirst ( $type ) . 'Protocol';
		$content = D ( $className, $type )->profileContent ( $this->uid );
		if(empty($content)){
			$content = '暂无内容';
		}
		$this->assign ( 'content', $content );
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 档案类型
			$ProfileType = model ( 'UserProfile' )->getCategoryList ();
			$this->assign ( 'ProfileType', $ProfileType );
			// 个人资料
			$this->_assignUserProfile ( $this->uid );
			// 获取用户职业信息
			$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->uid );
			if (! empty ( $userCategory )) {
				foreach ( $userCategory as $value ) {
					$user_category .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
				}
			}
			$this->assign ( 'user_category', $user_category );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		$this->setTitle ( $user_info ['uname'] . '的'.L ( 'PUBLIC_APPNAME_' . $type ) );
		$this->setKeywords ( $user_info ['uname'] . '的'.L ( 'PUBLIC_APPNAME_' . $type ) );
		$user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
				$this->uid
		) );
		$this->setDescription ( t ( $user_category . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->uid] ) . ',' . $user_info ['intro'] ) );
		
		
		$this->display ();
	}
	
	/**
	 * 获取指定应用的信息
	 * 
	 * @return void
	 */
	public function appprofile() {
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		
		$d ['widgetName'] = ucfirst ( t ( $_GET ['appname'] ) ) . 'Profile';
		foreach ( $_GET as $k => $v ) {
			$d ['widgetAttr'] [$k] = t ( $v );
		}
		$d ['widgetAttr'] ['widget_appname'] = t ( $_GET ['appname'] );
		$this->assign ( $d );
		
		$this->_assignUserInfo ( array (
				$this->uid 
		) );
		($this->mid != $this->uid) && $this->_assignFollowState ( $this->uid );
		$this->display ();
	}
	
	/**
	 * 获取用户详细资料
	 * 
	 * @return void
	 */
	public function data() {
		if (! CheckPermission ( 'core_normal', 'read_data' ) && $this->uid != $this->mid) {
			$this->error ( '对不起，您没有权限浏览该内容!' );
		}
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		$this->_tab_menu();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();
			// 档案类型
			$ProfileType = model ( 'UserProfile' )->getCategoryList ();
			$this->assign ( 'ProfileType', $ProfileType );
			// 个人资料
			$this->_assignUserProfile ( $this->uid );
			// 获取用户职业信息
			$userCategory = model ( 'UserCategory' )->getRelatedUserInfo ( $this->uid );
			if (! empty ( $userCategory )) {
				foreach ( $userCategory as $value ) {
					$user_category .= '<a href="#" class="link btn-cancel"><span>' . $value ['title'] . '</span></a>&nbsp;&nbsp;';
				}
			}
			$this->assign ( 'user_category', $user_category );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( $user_info ['uname'] . '的资料' );
		$this->setKeywords ( $user_info ['uname'] . '的资料' );
		$user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( array (
				$this->uid 
		) );
		$this->setDescription ( t ( $user_category . $user_info ['location'] . ',' . implode ( ',', $user_tag [$this->uid] ) . ',' . $user_info ['intro'] ) );
		$this->display ();
	}
	
	/**
	 * 获取指定用户的某条动态
	 * 
	 * @return void
	 */
	public function feed() {
		
		$feed_id = intval ( $_GET ['feed_id'] );
		
		if (empty ( $feed_id )) {
			$this->error ( L ( 'PUBLIC_INFO_ALREADY_DELETE_TIPS' ) );
		}
	
		//获取微博信息
		$feedInfo = model ( 'Feed' )->get ( $feed_id );

		if (!$feedInfo){
			$this->error ( '该微博不存在或已被删除' );
			exit();
		}
			
		if ($feedInfo ['is_audit'] == '0' && $feedInfo ['uid'] != $this->mid) {
			$this->error ( '此微博正在审核' );
			exit();
		}

		if ($feedInfo ['is_del'] == '1') {
			$this->error ( L ( 'PUBLIC_NO_RELATE_WEIBO' ) );
			exit();
		}

		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $feedInfo['uid'] );
		
		// 个人空间头部
		$this->_top ();
		
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$this->_sidebar ();		
			$weiboSet = model ( 'Xdata' )->get ( 'admin_Config:feed' );
			$a ['initNums'] = $weiboSet ['weibo_nums'];
			$a ['weibo_type'] = $weiboSet ['weibo_type'];
			$a ['weibo_premission'] = $weiboSet ['weibo_premission'];
			$this->assign ( $a );
			switch ($feedInfo ['app']) {
				case 'weiba' :
					$feedInfo ['from'] = getFromClient ( 0, $feedInfo ['app'], '微吧' );
					break;
				default :
					$feedInfo ['from'] = getFromClient ( $from, $feedInfo ['app'] );
					break;
			}
			$this->assign ( 'feedInfo', $feedInfo );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$feedContent = unserialize ( $feedInfo ['feed_data'] );
		$seo = model ( 'Xdata' )->get ( "admin_Config:seo_feed_detail" );
		$replace ['content'] = $feedContent ['content'];
		$replace ['uname'] = $feedInfo ['user_info'] ['uname'];
		$replaces = array_keys ( $replace );
		foreach ( $replaces as &$v ) {
			$v = "{" . $v . "}";
		}
		$seo ['title'] = str_replace ( $replaces, $replace, $seo ['title'] );
		$seo ['keywords'] = str_replace ( $replaces, $replace, $seo ['keywords'] );
		$seo ['des'] = str_replace ( $replaces, $replace, $seo ['des'] );
		! empty ( $seo ['title'] ) && $this->setTitle ( $seo ['title'] );
		! empty ( $seo ['keywords'] ) && $this->setKeywords ( $seo ['keywords'] );
		! empty ( $seo ['des'] ) && $this->setDescription ( $seo ['des'] );
		$this->assign ( 'userPrivacy', $userPrivacy );
		// 赞功能
		$diggArr = model ( 'FeedDigg' )->checkIsDigg ( $feed_id, $this->mid );
		$this->assign ( 'diggArr', $diggArr );
		
		$this->display ();
	}
	
	/**
	 * 获取用户关注列表
	 * 
	 * @return void
	 */
	public function following() {
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$following_list = model ( 'Follow' )->getFollowingList ( $this->uid, t ( $_GET ['gid'] ), 20 );
			$fids = getSubByKey ( $following_list ['data'], 'fid' ,array('type',0));
			$schoolids =getSubByKey($following_list['data'], 'fid',array('type',1));
			$classids =getSubByKey($following_list['data'], 'fid',array('type',2));
		
			/***关注状态**/
			if($schoolids) {
				$school_follow_state = model('Follow')->getFollowStateByFids($this->mid, $schoolids, 1);
				//获取组织信息
				$schoolListData=S("schoolListData".$schoolids);
				if(!$schoolListData ) {
					$schoolListData = D('CySchool')->get_school_infos_by_ids($schoolids);
					S("schoolListData" . $schoolids, $schoolListData, 360000);
				}
			}
			if($classids) {
				$class_follow_state = model('Follow')->getFollowStateByFids($this->mid, $classids, 2);
				$classListData=S("classListData".$classids);
				if(!$classListData) {
					$classListData = D('CyClass')->get_class_infos_by_ids($classids);
					S("classListData" . $classids, $classListData, 360000);
				}
			}
			
			$orgnization_follow_state = $school_follow_state+$class_follow_state;
			
			if ($fids) {
				$uids = array_merge ( $fids, array (
						$this->uid 
				) );
			} else {
				$uids = array (
						$this->uid 
				);
			}

			$orgListData = $schoolListData + $classListData;
			$this->assign ( 'orgListData', $orgListData );
			// 获取用户组信息
			$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
			
			$this->assign('orgnization_follow_state',$orgnization_follow_state);
			
			$this->assign ( 'userGroupData', $userGroupData );
			$this->_assignFollowState ( $uids );
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			$this->_assignUserCount ( $fids );
			// 关注分组
			($this->mid == $this->uid) && $this->_assignFollowGroup ( $fids );
			$this->assign ( 'following_list', $following_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}

		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( L ( 'PUBLIC_TA_FOLLOWING', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->setKeywords ( L ( 'PUBLIC_TA_FOLLOWING', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->display ();
	}
	
	/**
	 * 获取用户粉丝列表
	 * 
	 * @return void
	 */
	public function follower() {
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		//浏览页面的为本人的粉丝列表页面
		if($this->uid==$this->mid){
			//用户信息表
			$data_model = model('UserData');
			$user_data=$data_model->getUserData($this->uid);
			$this->assign('new_folower_count',$user_data['new_folower_count']);
			//新添加粉丝数不为0,则清空粉丝人数
			if($user_data['new_folower_count']>0){
				$data_model->setKeyValue($this->uid,'new_folower_count',0);
			}
		};
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$follower_list = model ( 'Follow' )->getFollowerList ( $this->uid, 20 );
			$fids = getSubByKey ( $follower_list ['data'], 'fid' );
			if ($fids) {
				$uids = array_merge ( $fids, array (
						$this->uid 
				) );
			} else {
				$uids = array (
						$this->uid 
				);
			}
			
			// 获取用户用户组信息
			$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
			$this->assign ( 'userGroupData', $userGroupData );
			$this->_assignFollowState ( $uids );
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			$this->_assignUserCount ( $fids );
			// 更新查看粉丝时间
			if ($this->uid == $this->mid) {
				$t = time () - intval ( $GLOBALS ['ts'] ['_userData'] ['view_follower_time'] ); // 避免服务器时间不一致
				model ( 'UserData' )->setUid ( $this->mid )->updateKey ( 'view_follower_time', $t, true );
			}
			$this->assign ( 'follower_list', $follower_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		
		$this->setTitle ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->setKeywords ( L ( 'PUBLIC_TA_FOLLWER', array (
				'user' => $GLOBALS ['ts'] ['_user'] ['uname'] 
		) ) );
		$this->display ();
	}
	

	/**
	 * 获取用户关注的学校或班级  
	 *
	 * @return void
	 */
	private function org_following($uid,$type){
		// 获取用户信息
		$user_info = model ( 'User' )->getUserInfo ( $this->uid );
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		// 个人空间头部
		$this->_top ();
		// 判断隐私设置
		$userPrivacy = $this->privacy ( $this->uid );
		if ($userPrivacy ['space'] !== 1) {
			$uids = array (
					$this->uid
			);
			$this->_assignUserInfo ( $uids );
			$this->_assignUserProfile ( $uids );
			$this->_assignUserTag ( $uids );
			// 获取用户用户组信息
			
			$following_list = model ( 'Follow' )->getFollowingsCampusList( $uid, $type, 20 );
			$orgids = getSubByKey ( $following_list ['data'], 'fid' );
			
			$orgnization_follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $orgids,$type );
			
			if($type == 1){
				$orgListData = D('CySchool')->get_school_infos_by_ids($orgids);
			}else{
				$orgListData = D('CyClass')->get_class_infos_by_ids($orgids);
			}
			
			$this->assign ( 'orgListData', $orgListData );
			$this->assign ( 'orgnization_follow_state', $orgnization_follow_state );
			
			// 更新查看粉丝时间
			if ($this->uid == $this->mid) {
				$t = time () - intval ( $GLOBALS ['ts'] ['_userData'] ['view_follower_time'] ); // 避免服务器时间不一致
				model ( 'UserData' )->setUid ( $this->mid )->updateKey ( 'view_follower_time', $t, true );
			}
			$this->assign ( 'following_list', $following_list );
		} else {
			$this->_assignUserInfo ( $this->uid );
		}
		$this->assign ( 'userPrivacy', $userPrivacy );
		$this->display('orgfollowing');
	}
	/**
	 * 获取用户关注的班级
	 *
	 * @return void
	 */
	public function class_following(){
		$uid = $_GET['uid'];
		$type = 2;
		$this->org_following($uid, $type);
	}
	/**
	 * 获取用户关注的学校
	 *
	 * @return void
	 */
	public function campus_following(){
		$uid = $_GET['uid'];
		$type = 1;
		$this->org_following($uid, $type);
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
		$client = new \CyClient();
		foreach($user_info as &$user){
			$userRole = model('UserRole')->getUserRole($user['uid']);
			$user['roleName'] = UserRoleTypeModel::getCNRoleName($userRole['rolename']);
			//判断是否是学生或者教师，如果不是，则查询其机构信息
			if($user['roleName'] == "教师" || $user['roleName'] == "学生"){
			}else{
				$user = model ( 'User' )->getUserInfo ( $user['uid'] );
				if(!empty($user)){
					$eduorg = $client->listEduorgByUser($user['cyuid']);
					$user['eduorgName'] = $eduorg[0]->eduorgName;
				}
			}
			// 获取用户积分信息
			$user['userScore'] = model('Credit')->getUserCredit($user['uid']);
			// 获取相关的统计数目
			$user['userData'] = model('UserData')->getUserData($user['uid']);
		}
		$this->assign ( 'user_info', $user_info );
	}
	
	/**
	 * 获取用户的档案信息和资料配置信息
	 * 
	 * @param
	 *        	mix uids 用户uid
	 * @return void
	 */
	private function _assignUserProfile($uids) {
		$data ['user_profile'] = model ( 'UserProfile' )->getUserProfileByUids ( $uids );
		$data ['user_profile_setting'] = model ( 'UserProfile' )->getUserProfileSetting ( array (
				'visiable' => 1 
		) );
		// 用户选择处理 uid->uname
		foreach ( $data ['user_profile_setting'] as $k => $v ) {
			if ($v ['form_type'] == 'selectUser') {
				$field_ids [] = $v ['field_id'];
			}
			if ($v ['form_type'] == 'selectDepart') {
				$field_departs [] = $v ['field_id'];
			}
		}
		foreach ( $data ['user_profile'] as $ku => &$uprofile ) {
			foreach ( $uprofile as $key => $val ) {
				if (in_array ( $val ['field_id'], $field_ids )) {
					$user_info = model ( 'User' )->getUserInfo ( $val ['field_data'] );
					$uprofile [$key] ['field_data'] = $user_info ['uname'];
				}
				if (in_array ( $val ['field_id'], $field_departs )) {
					$depart_info = model ( 'Department' )->getDepartment ( $val ['field_data'] );
					$uprofile [$key] ['field_data'] = $depart_info ['title'];
				}
			}
		}
		$this->assign ( $data );
	}
	
	/**
	 * 根据指定应用和表获取指定用户的标签
	 * 
	 * @param
	 *        	array uids 用户uid数组
	 * @return void
	 */
	private function _assignUserTag($uids) {
		$user_tag = model ( 'Tag' )->setAppName ( 'User' )->setAppTable ( 'user' )->getAppTags ( $uids );
		$this->assign ( 'user_tag', $user_tag );
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
		$this->assign ( 'user_count', $user_count );
	}
	
	/**
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 * 
	 * @param array $fids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignFollowState($fids = null) {
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = model ( 'Follow' )->getFollowStateByFids ( $this->mid, $fids );
		$this->assign ( 'follow_state', $follow_state );
	}
	
	/**
	 * 获取用户最后一条微博数据
	 * 
	 * @param
	 *        	mix uids 用户uid
	 * @param
	 *        	void
	 */
	private function _assignUserLastFeed($uids) {
		return true; // 目前不需要这个功能
		$last_feed = model ( 'Feed' )->getLastFeed ( $uids );
		$this->assign ( 'last_feed', $last_feed );
	}
	
	/**
	 * 调整分组列表
	 * 
	 * @param array $fids
	 *        	指定用户关注的用户列表
	 * @return void
	 */
	private function _assignFollowGroup($fids) {
		$follow_group_list = model ( 'FollowGroup' )->getGroupList ( $this->mid );
		// 调整分组列表
		if (! empty ( $follow_group_list )) {
			$group_count = count ( $follow_group_list );
			for($i = 0; $i < $group_count; $i ++) {
				if ($follow_group_list [$i] ['follow_group_id'] != $data ['gid']) {
					$follow_group_list [$i] ['title'] = (strlen ( $follow_group_list [$i] ['title'] ) + mb_strlen ( $follow_group_list [$i] ['title'], 'UTF8' )) / 2 > 8 ? getShort ( $follow_group_list [$i] ['title'], 3 ) . '...' : $follow_group_list [$i] ['title'];
				}
				if ($i < 2) {
					$data ['follow_group_list_1'] [] = $follow_group_list [$i];
				} else {
					if ($follow_group_list [$i] ['follow_group_id'] == $data ['gid']) {
						$data ['follow_group_list_1'] [2] = $follow_group_list [$i];
						continue;
					}
					$data ['follow_group_list_2'] [] = $follow_group_list [$i];
				}
			}
			if (empty ( $data ['follow_group_list_1'] [2] ) && ! empty ( $data ['follow_group_list_2'] [0] )) {
				$data ['follow_group_list_1'] [2] = $data ['follow_group_list_2'] [0];
				unset ( $data ['follow_group_list_2'] [0] );
			}
		}
		
		$data ['follow_group_status'] = model ( 'FollowGroup' )->getGroupStatusByFids ( $this->mid, $fids );
		
		$this->assign ( $data );
	}
	
	/**
	 * 个人主页头部数据
	 * 
	 * @return void
	 */
	public function _top() {
		// 获取用户组信息
		$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $this->uid );
		$this->assign ( 'userGroupData', $userGroupData );
		// 获取用户积分信息
		$userCredit = model ( 'Credit' )->getUserCredit ( $this->uid );
		$this->assign ( 'userCredit', $userCredit );
		// 加载用户关注信息
		($this->mid != $this->uid) && $this->_assignFollowState ( $this->uid );
		// 获取用户统计信息
		$userData = model ( 'UserData' )->getUserData ( $this->uid );
		// 获取用户信息
		$cyuserdata = $GLOBALS['ts']['_cyuserdata'];
		$this->assign('location',$cyuserdata['locations']);
		$this->assign('orglist',$cyuserdata['orglist']);
		$this->assign('rolelist',$cyuserdata['rolelist']);
		
		$this->assign ( 'userData', $userData );
	}
	/**
	 * 个人主页标签导航
	 *
	 * @return void
	 */
	public function _tab_menu() {
		// 取全部APP信息
		$appList = model ( 'App' )->where ( 'status=1' )->field ( 'app_name' )->findAll ();
		foreach ( $appList as $app ) {
			$appName = strtolower ( $app ['app_name'] );
			$className = ucfirst ( $appName );
			
			$dao = D ( $className . 'Protocol', strtolower($className), false );
			if (method_exists ( $dao, 'profileContent' )) {
				$appArr [$appName] = L ( 'PUBLIC_APPNAME_' . $appName );
			}
			unset ( $dao );
		}
		$this->assign ( 'appArr', $appArr );
		
		return $appArr;
	}	
	
	/**
	 * 个人主页右侧
	 * 
	 * @return void
	 */
	public function _sidebar() {
		// 判断用户是否已认证
		$isverify = D ( 'user_verified' )->where ( 'verified=1 AND uid=' . $this->uid )->find ();
		if ($isverify) {
			$this->assign ( 'verifyInfo', $isverify ['info'] );
		}
		// 加载用户标签信息
		$this->_assignUserTag ( array (
				$this->uid 
		) );
		// 加载关注列表
		$sidebar_following_list = model ( 'Follow' )->getFollowingList ( $this->uid, null, 12 );
		$this->assign ( 'sidebar_following_list', $sidebar_following_list );
		// 加载粉丝列表
		$sidebar_follower_list = model ( 'Follow' )->getFollowerList ( $this->uid, 12 );
		$this->assign ( 'sidebar_follower_list', $sidebar_follower_list );
		// 加载用户信息
		$uids = array (
				$this->uid 
		);
		
		$followingfids = getSubByKey ( $sidebar_following_list ['data'], 'fid',array('type',0) );
		$followingfids && $uids = array_merge ( $uids, $followingfids );
		

		$followerfids = getSubByKey ( $sidebar_follower_list ['data'], 'fid');
		$followerfids && $uids = array_merge ( $uids, $followerfids );
		
		$schoolids =getSubByKey($sidebar_following_list['data'], 'fid',array('type',1));
		$classids =getSubByKey($sidebar_following_list['data'], 'fid',array('type',2));
		$orgids  =array_merge($schoolids,$classids);
		//获取组织信息
		$schoolListData = D('CySchool')->get_school_infos_by_ids($schoolids);
		$classListData = D('CyClass')->get_class_infos_by_ids($classids);
		
		$orgListData = $schoolListData + $classListData;
		$this->assign ( 'orgListData', $orgListData );
		$this->_assignUserInfo ( $uids );
		
		$cyuserdata = $GLOBALS['ts']['_cyuserdata'];
		$schoolids = getSubByKey($_followOrgList['data'], 'fid',array('type',1));
		$classids = getSubByKey($_followOrgList['data'], 'fid',array('type',2));
		
		$school_follow_state = D ('Follow')->getFollowStateByFids ($this->mid, $schoolids,1);
		$class_follow_state = D ('Follow')->getFollowStateByFids ($this->mid, $classids,2);
		$this->assign ( 'school_follow_state', $school_follow_state );
		$this->assign ( 'class_follow_state', $class_follow_state );
		
		$this->assign( 'orglist',$cyuserdata['orglist'] );
	}
}