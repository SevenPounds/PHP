<?php
use Home\Library\ResourceRadarService;
/**
 * 首页控制器
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class IndexAction extends Action {

	
	public function maJiaAccount(){
		// 引入Excel类库
		require './addons/library/PHPExcel/PHPExcel.php';
		
		// 设置马甲账号
		$users = C('MAJIA_ACCOUNT');
		if(empty($users)){
			return;
		}
		
		// 获取说说预置内容
		$feedExcel = './apps/public/_static/MajiaAccount/feeds.xlsx';
		if(file_exists($feedExcel)){
			$feeds = excelIn($feedExcel);
			if(!empty($feeds)){
				$this->setFeedsByUsers($users, $feeds);
			}else{
				Log::write("说说预置excel中无数据", Log::DEBUG);
			}
		}else{
			Log::write("说说预置excel文件不存在", Log::DEBUG);
		}

		// 获取日志预置内容
		$blogExcel = './apps/public/_static/MajiaAccount/blogs.xlsx';
		if(file_exists($blogExcel)){
			$blogs = excelIn($blogExcel);
			if(!empty($blogs)){
				$this->setBlogsByUsers($users, $blogs);
			}else{
				Log::write("日志预置excel中无数据", Log::DEBUG);
			}
		}else{
			Log::write("日志预置excel文件不存在", Log::DEBUG);
		}
	}
	
	/**
	 * 根据配置的用户数组设置马甲账号的日志数据
	 * @param array $users 用户登录名数组
	 * @param array $feeds 预置的日志列表
	 */
	private function setBlogsByUsers($users, $blogs){
		foreach($users as $key=>$uname){
			$userInfo = M("User")->getUserInfoByLogin($uname,array());
			if(!empty($userInfo)){
				$uid = $userInfo['uid'];
				$lastBlog = M("Blog")->where(array("uid"=>$uid,'status'=>1))->order('cTime desc')->field('id,cTime')->limit(1)->select();
				// 获取用户最近的一篇日志
				if($lastBlog){
					$lDate = strtotime(date("Y-m-d",$lastBlog[0]['cTime']));
				}else{
					// 未发表过日志，默认去6天前时间未开始预置时间
					$lDate = strtotime(date("Y-m-d",(time() - 86400 * 6)));
				}
				$today = strtotime(date("Y-m-d",time()));
				while(true){
					$lDate = $lDate + 86400 * 5;
					if($lDate < $today){
						$day = intval(date("d",$lDate));
						$k = floor($day/5) + 1;
						$blogKey = $k + ($key * 7);
						// 获取预置的日志内容
						$blog = $blogs[$blogKey];
						if(!empty($blog)){
							$blogTitle = $blog[0];
							$blogContent = $blog[1];
							$cTime = $lDate + rand(3600 * 8, 3600 * 21);
							$data = array("uid"=>$uid,"content"=>$blogContent,"title"=>$blogTitle,"cTime"=>$cTime,"mTime"=>$cTime,"name"=>null,"class_id"=>null,"category"=>1,"password"=>"","mention"=>null,"private"=>0,"canableComment"=>0,"attach"=>null,"category_title"=>"未分类");
							$add = D('Blog','blog')->add($data);
							$feed_id = D('Blog','blog')->syncToFeed($add,$blogTitle,$blogContent,$uid,$cTime);
							D('Blog','blog')->where('id='.$add)->setField('feed_id',$feed_id);
						}
					}else{
						break;
					}
				}
			}else{
				Log::write("用户" . $uname . "不存在", Log::DEBUG);
			}
		}
	}
	
	/**
	 * 根据配置的用户数组设置马甲账号的说说数据
	 * @param array $users 用户登录名数组
	 * @param array $feeds 预置的说说列表
	 */
	private function setFeedsByUsers($users, $feeds){
		foreach($users as $key=>$uname){
			$userInfo = M("User")->getUserInfoByLogin($uname,array());
			if(!empty($userInfo)){
				$uid = $userInfo['uid'];
				$lastFeed = M("Feed")->where(array("app"=>"public","type"=>"post","is_del"=>0,"uid"=>$uid))->order('publish_time desc')->field('feed_id,publish_time')->limit(1)->select();
				// 获取用户最近的一条说说
				if($lastFeed){
					$lDate = strtotime(date("Y-m-d",$lastFeed[0]['publish_time']));
				}else{
					// 未发表过说说，默认去四天前时间未开始预置时间
					$lDate = strtotime(date("Y-m-d",(time() - 86400 * 4)));
				}
				$today = strtotime(date("Y-m-d",time()));
				while(true){
					$lDate = $lDate + 86400 * 3;
					if($lDate < $today){
						$day = date("d",$lDate);
						$feedKey = floor($day/3);
						// 获取预置的说说内容
						$content = $feeds[$feedKey][$key].' ';
						if(!empty($content)){
							$publishTime = $lDate + rand(3600 * 8, 3600 * 21);
							$data = array("publish_time"=>$publishTime,"content"=>$content,"body"=>$content,"source_url"=>" ","record_id"=>"","feed_title"=>null,"attach_id"=>"","app_row_id"=>0,"app_row_table"=>"feed","from"=>"0","repost_count"=>0,"comment_count"=>0,"is_del"=>0,"is_repost"=>0,"is_audit"=>1);
							$result = M("Feed")->put($uid,'public','post',$data);
						}
					}else{
						break;
					}
				}
			}else{
				Log::write("用户" . $uname . "不存在", Log::DEBUG);
			}
		}
	}
	
	/**
	 * 我的首页 - 微博页面
	 * @return void
	 */
	public function index(){
		// 马甲账号信息检测预置
		$this->maJiaAccount();
		$this->appCssList[] = 'zone.css';
		// 安全过滤
        $type = strFilter(t($_GET['type']));
        $feed_type = strFilter(t($_GET['feed_type']));
        $type_key = strFilter(t($_GET['feed_key']));
		$d['type'] = $type ? $type : 'following';
		$d['feed_type'] = $feed_type ? $feed_type : '';
		$d['feed_key'] = $type_key ? $type_key : '';
		// 关注的人
		if($d['type'] === 'following') {
			$d['groupname'] = L('PUBLIC_ACTIVITY_STREAM');			// 我关注的
			$d['followGroup'] = model('FollowGroup')->getGroupList($this->mid);
			foreach($d['followGroup'] as $v) {
				if($v['follow_group_id'] == t($_REQUEST['fgid'])) {
					$d['groupname'] = $v['title'];
					
					break;
				}
			}
		}
		
		// 判断班级是否开启
		$isClassOpen = model('App')->isAppNameOpen('class');
		//如果是教研员则关闭班级和学校动态
		$isClassOpen && Model('CyUser')->hasRole(UserRoleTypeModel ::RESAERCHER,$this->cymid) && $isClassOpen = false;
		$this->assign('isClassOpen', $isClassOpen);
		if($isClassOpen && $d['type'] === 'class') {
			$d['classname']= '班级';
		}
		$this->assign($d);
		
		// 判断频道是否开启
		$isChannelOpen = model('App')->isAppNameOpen('channel');
		$this->assign('isChannelOpen', $isChannelOpen);
		// 关注的频道
		if($isChannelOpen && $d['type'] === 'channel') {
			$d['channelname'] = '订阅';
			$d['channelGroup'] = D('ChannelFollow', 'channel')->getFollowList($this->mid);
			foreach($d['channelGroup'] as $v) {
				if($v['channel_category_id'] == t($_REQUEST['fgid'])) {
					$d['channelname'] = $v['title'];
					
					break;
				}
			}
		}
		$this->assign($d);
		
		// 获取我的文档fid
		$restParams = array();
		$restParams['method'] = 'pan.dirid.get';
		$restParams['uid'] = $this->cymid;
		$restParams['folderType'] = 'yun_wendang';
		$wendang_fid = Restful::sendGetRequest($restParams);
		Log::write("我的文档fid : ".$wendang_fid,Log::DEBUG);
		$this->wendangFid = $wendang_fid;
		
		// 设置默认话题
		$weiboSet = model('Xdata')->get('admin_Config:feed');
		$initHtml = $weiboSet['weibo_default_topic'];		// 微博框默认话题
		if($initHtml){
			$initHtml = '#'.$initHtml.'#';
		}
		$this->assign('initHtml' , $initHtml);
		
		$title = empty($weiboSet['weibo_send_info']) ? '随时记录' : $weiboSet['weibo_send_info'];
		$this->assign('title', $title);
		// 设置标题与关键字信息
		switch($d['type']) {
			case 'all':
				$this->setTitle('全站动态');
				$this->setKeywords('全站动态');
				break;
			case 'channel':
				$this->setTitle('订阅');
				$this->setKeywords('订阅');
				break;
			case 'class':
				$this->setTitle('班级动态');
				$this->setKeywords('班级动态');
				break;
			default:
				$this->setTitle("个人空间");
				$this->setKeywords("个人空间");
		}


        if(isset($_SESSION['firstLoginTag']) && $_SESSION['firstLoginTag']){
            $this->assign('firstLoginTag',true);
            $_SESSION['firstLoginTag'] = null;
        }
		//add by zhehuang 20150721  添加判断用户是否为机构管理者用户
        $userData = $GLOBALS ['ts'] ['cyuserdata'];
        $cyUserModel = D('CyUser','Model');
        $state = $cyUserModel->isEduManager($userData);
        $this->assign('eduManager',$state);
        //级别判断
        //0，省级 咱不做处理
        
        //1，市级
        if($cyUserModel->isEduManager($userData,ConstantsModel::CITY_TYPE)){
        	$this->assign('managerLevel',ConstantsModel::CITY_TYPE);
        }
        //2，区县级
        if($cyUserModel->isEduManager($userData,ConstantsModel::DISTRICT_TYPE)){
        	$this->assign('managerLevel',ConstantsModel::DISTRICT_TYPE);
        }
        
        $this->_userid = $this->cymid;
        // 获取云盘外网上传地址
        $this->YUNPANUPLOAD = C('YUNPAN_UPLOAD_URL');
        $upload_config = include(CONF_PATH.'/upload.inc.php');
        $exts = implode(';',$upload_config['previewable_exts']);
        $this->fileTypes = $exts;
		$this->display();
	}
	
	/**
	 * 用户说说页面，展示被访问用户说说页面
	 */
	public function userFeed(){
		// 初始化用户说说信息
		$dates = D("Feed")->getFeedMonths($this->uid,true);
		$this->dates = $dates;
		
		//获取资源总数
		$u_info =model ( 'User' )->getUserInfo ( $this->uid );
		$resCount=D ( 'YunpanPublish', 'yunpan' )->getlistPublishCount ( $u_info['login'], 1 );
		$this->resCount=$resCount;
		
		// 获取相册总数
		$map_album['userId'] = $this->uid;
		$map_album['isDel'] = 0;
		$albumCount = D('Album', 'photo')->where($map_album)->count();
		$this->albumCount = $albumCount;
		
		// 获取说说总数
		$map['uid'] = $this->uid;
		$map['type'] = 'post';
		$map['app'] = 'public';
		$map['is_del'] = 0;
		$feedCount = D("Feed")->where($map)->count();
		$this->feedCount = $feedCount;
		
		$this->display();
	}

	public function loginWithoutInit(){
		$this->index();
	}

	/**
	 * 我的微博页面
	 */
	public function myFeed() {
		$this->appCssList[] = 'zone.css';
		// 获取用户统计数目
		$userData = model('UserData')->getUserData($this->mid);
		$this->assign('feedCount', $userData['weibo_count']);
		// 微博过滤内容
		$feedType = t($_GET['feed_type']);
		$this->assign('feedType', $feedType);
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		$this->setTitle('我的微博');	
		$this->setKeywords('我的微博');
		$this->display();
	}

	/**
	 * 我的关注页面
	 */
	public function following() {
		$this->appCssList[] = 'zone.css';
		
		// 获取关组分组ID
		$gid = intval($_GET['gid']);
		$this->assign('gid', $gid);
	
		if($gid==-11||$gid==-12 ||$gid==-13){
			switch($gid){
				case -11://校园
					$followGroupList = model('Follow')->getFollowingsCampusList($this->mid,1);
					$orgids = getSubByKey($followGroupList['data'], 'fid');
					$orgnization_follow_state =  model('Follow')->getFollowStateByFids ( $this->mid, $orgids,1 );
					//获取关注校园信息
					$followOrgList = D('CySchool')->get_school_infos_by_ids($orgids);
					break;
				case -12://班级
					$followGroupList = model('Follow')->getFollowingsCampusList($this->mid,2);
					$orgids = getSubByKey($followGroupList['data'], 'fid');
					$orgnization_follow_state =  model('Follow')->getFollowStateByFids ( $this->mid, $orgids,2 );
					//获取关注班级信息
					$followOrgList = D('CyClass')->get_class_infos_by_ids($orgids);
					break;
				case -13 :
					$followGroupList = model('Follow')->getFollowingsCampusList($this->mid,3);
					$orgids = getSubByKey($followGroupList['data'], 'fid');
					$orgnization_follow_state =  model('Follow')->getFollowStateByFids ( $this->mid, $orgids,3 );
					$followOrgList = D('MSGroup','msgroup')->getMsGroupList($orgids);
					$this->assign('msgroup_follow_state',$orgnization_follow_state);
					break;
			}
			
			foreach($followGroupList['data'] as $key=> $value) {
				$gid==-13 && $followOrgList[$value['fid']]['type'] = 3;
				$followGroupList['data'][$key] = $followOrgList[$value['fid']];
			}

			$this->assign('orgnization_follow_state',$orgnization_follow_state);
			
			$this->assign($followGroupList);
		}else{
		// 获取指定用户的关注分组
		$groupList = model('FollowGroup')->getGroupList($this->mid);
		// 获取用户ID
		switch($gid) {
			case 0:
				$followGroupList = model('Follow')->getFollowingsList($this->mid);
				break;
			case -1:
				$followGroupList = model('Follow')->getFriendsList($this->mid);
				break;
			case -2:
				$followGroupList = model('FollowGroup')->getDefaultGroupByPage($this->mid);
				break;
			default:
				$followGroupList = model('FollowGroup')->getUsersByGroupPage($this->mid, $gid);
		}
		
		$fids = getSubByKey($followGroupList['data'], 'fid',array('type',0));
		$schoolids =getSubByKey($followGroupList['data'], 'fid',array('type',1));
		$classids =getSubByKey($followGroupList['data'], 'fid',array('type',2));
		$msgids = getSubByKey($followGroupList['data'], 'fid',array('type',3));
		
		$school_follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $schoolids,1 );
		$class_follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $classids,2 );
		
		$msgroup_follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $msgids,3 );
		$this->assign('msgroup_follow_state',$msgroup_follow_state);
		
		$orgnization_follow_state = $school_follow_state+$class_follow_state;
		
		//获取组织信息
		$schoolListData = D('CySchool')->get_school_infos_by_ids($schoolids);
		$classListData = D('CyClass')->get_class_infos_by_ids($classids);
	
		$msgroupData = D('MSGroup','msgroup')->getMsGroupList($msgids);
		
		// 获取用户信息
		$followUserInfo = model('User')->getUserInfoByUids($fids);
		// 获取用户的统计数目
		$userData = model('UserData')->getUserDataByUids($fids);
		// 获取用户用户组信息
		$userGroupData = model('UserGroupLink')->getUserGroupData($fids);
		$this->assign('userGroupData',$userGroupData);
		// 获取用户的关注信息状态值
		$followState = model('Follow')->getFollowStateByFids($this->mid, $fids);
		// 获取用户的备注信息
		$remarkInfo = model('Follow')->getRemarkHash($this->mid);
		// 获取用户标签
		$this->_assignUserTag($fids);
		// 关注分组信息
		$followGroupStatus = model('FollowGroup')->getGroupStatusByFids($this->mid, $fids);
		$this->assign('followGroupStatus', $followGroupStatus);
		// 组装数据
		foreach($followGroupList['data'] as $key=> $value) {
			if($value['type']==0){
				$followGroupList['data'][$key] = $followUserInfo[$value['fid']];
				$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], $userData[$value['fid']]);
				$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('feedInfo'=>$lastFeedData[$value['fid']]));
				$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('followState'=>$followState[$value['fid']]));
				$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('remark'=>$remarkInfo[$value['fid']]));
				$followGroupList['data'][$key] = array_merge($followGroupList['data'][$key], array('type'=>$value['type']));
			}else{
				if($value['type'] == 1){
					$followGroupList['data'][$key] = $schoolListData[$value['fid']];
				}elseif($value['type'] == 2){
					$followGroupList['data'][$key] = $classListData[$value['fid']];
				}else{
					$msgroupData[$value['fid']]['type'] = 3;
					$followGroupList['data'][$key] = $msgroupData[$value['fid']];
				}
			}
		}
		$this->assign($followGroupList);
		
		}
		
		// 获取登录用户的所有分组
		$userGroupList = model('FollowGroup')->getGroupList($this->mid);
		$userGroupListFormat = array();
		foreach($userGroupList as $value) {
			$userGroupListFormat[] = array('gid'=>$value['follow_group_id'], 'title'=>$value['title']);
		}
		
		$groupList = array(array('gid'=>0, 'title'=>'全部'), array('gid'=>-1, 'title'=>'相互关注'),array('title'=>'学校','gid'=>'-11'),array('title'=>'班级','gid'=>'-12'),array('title'=>'名师工作室','gid'=>'-13'), array('gid'=>-2, 'title'=>'未分组'));
		//添加校园分类
		
		!empty($userGroupListFormat) && $groupList = array_merge($groupList, $userGroupListFormat);
		$this->assign('groupList', $groupList);
		// 前5个的分组ID
		$this->assign('topGroup', array_slice(getSubByKey($groupList, 'gid'), 0, 5));
		foreach($groupList as $value) {
			if($value['gid'] == $gid) {
				$this->assign('gTitle', $value['title']);
				break;
			}
		}
		// 关注人数
		$midData = model('UserData')->getUserData($this->mid);
		$this->assign('followingCount', $midData['following_count']);
		// 显示的分类个数
		$this->assign('groupNums', 5);
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		
		$this->assign('orgnization_follow_state',$orgnization_follow_state);
		
		$userInfo = model('User')->getUserInfo($this->mid);
		$lastFeed = model('Feed')->getLastFeed(array($fids[0]));
		$this->setTitle('我的关注');
        $this->setKeywords($userInfo['uname'].'的关注');
		$this->display();
	}

	/**
	 * 我的粉丝页面
	 */
	public function follower() {
		$this->appCssList[] = 'zone.css';
		// 清空新粉丝提醒数字
		if($this->uid == $this->mid){
			$udata = model('UserData')->getUserData($this->mid);
			$udata['new_folower_count'] > 0 && model('UserData')->setKeyValue($this->mid,'new_folower_count',0);	
		}
		// 获取用户的粉丝列表
		$followerList = model('Follow')->getFollowerList($this->mid, 20);
		$fids = getSubByKey($followerList['data'], 'fid');
		// 获取用户信息
		$followerUserInfo = model('User')->getUserInfoByUids($fids);
		// 获取用户统计数目
		$userData = model('UserData')->getUserDataByUids($fids);
		// 获取用户标签
		$this->_assignUserTag($fids);
		// 获取用户用户组信息
		$userGroupData = model('UserGroupLink')->getUserGroupData($fids);
		$this->assign('userGroupData',$userGroupData);
		// 获取用户的关注信息状态
		$followState = model('Follow')->getFollowStateByFids($this->mid, $fids);
		// 组装数据
		foreach($followerList['data'] as $key => $value) {
			$followerList['data'][$key] = array_merge($followerList['data'][$key], $followerUserInfo[$value['fid']]);
			$followerList['data'][$key] = array_merge($followerList['data'][$key], $userData[$value['fid']]);
			$followerList['data'][$key] = array_merge($followerList['data'][$key], array('feedInfo'=>$lastFeedData[$value['fid']]));
			$followerList['data'][$key] = array_merge($followerList['data'][$key], array('followState'=>$followState[$value['fid']]));
		}
		$this->assign($followerList);
		// 是否有返回按钮
		$this->assign('isReturn', 1);
		// 粉丝人数
		$midData = model('UserData')->getUserData($this->mid);
		$this->assign('followerCount', $midData['follower_count']);

		$userInfo = model('User')->getUserInfo($this->mid);
		$lastFeed = model('Feed')->getLastFeed(array($fids[0]));
		$this->setTitle('我的粉丝');
        $this->setKeywords($userInfo['uname'].'的粉丝');
		$this->display();
	}
	
	/**
	 * 意见反馈页面
	 */
	public function feedback() {
		$feedbacktype = model('Feedback')->getFeedBackType();
		$this->assign('type', $feedbacktype);
		$this->display();
	}
	
	/**
	 * 获取验证码图片操作
	 */
	public function verify() {
		tsload(ADDON_PATH.'/library/Image.class.php');
		tsload(ADDON_PATH.'/library/String.class.php');
		Image::buildImageVerify();
	}

	/**
	 * 获取指定用户小名片所需要的数据
	 * @return string 指定用户小名片所需要的数据
	 */
	public function showFaceCard() {
		if(empty($_REQUEST['uid'])) {
			exit(L('PUBLIC_WRONG_USER_INFO'));			// 错误的用户信息
		}
		
		$this->assign('follow_group_status', model('FollowGroup')->getGroupStatus($GLOBALS['ts']['mid'], $GLOBALS['ts']['uid']));
		$this->assign('remarkHash', model('Follow')->getRemarkHash($GLOBALS['ts']['mid']));
		
		$uid = intval($_REQUEST['uid']);
		$data['userInfo'] = model('User')->getUserInfo($uid);
		$data['userInfo']['groupData'] = model('UserGroupLink')->getUserGroupData($uid);   //获取用户组信息
		$data['user_tag'] = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($uid);
		$data['user_tag'] = empty($data['user_tag']) ? '' : implode('、',$data['user_tag']);
		$data['follow_state'] = model('Follow')->getFollowState($this->mid, $uid);
		
		$depart = model('Department')->getAllHash();
		$data['department'] = isset($depart[$data['userInfo']['department_id']]) ? $depart[$data['userInfo']['department_id']] : '';
		
		$count = model('UserData')->getUserData($uid);
		if(empty($count)) {
			$count = array('following_count'=>0,'follower_count'=>0,'feed_count'=>0,'favorite_count'=>0,'unread_atme'=>0,'weibo_count'=>0);
		}
		$data['count_info'] = $count;
		// 获取用户积分信息
		$data['userInfo']['userScore'] = model('Credit')->getUserCredit($uid);
		// 获取相关的统计数目
		$data['userInfo']['userData'] = model('UserData')->getUserData($uid);
		// 用户字段信息
		$profileSetting = D('UserProfileSetting')->where('type=2')->getHashList('field_id');
		$profile = model('UserProfile')->getUserProfile($uid);
		$data['profile'] = array();
		foreach($profile as $k=>$v) {
			if(isset($profileSetting[$k])) {
				$data['profile'][$profileSetting[$k]['field_key']] = array('name'=>$profileSetting[$k]['field_name'],'value'=>$v['field_data']);
			}
		}
		//获取用户的角色名称
		$userRole = D('UserRole')->getUserRole($uid);
		$roleName = UserRoleTypeModel::getCNRoleName($userRole['rolename']);
		//判断是否是学生或者教师，如果不是，则查询其机构信息
		if($roleName == "教师" || $roleName == "学生"){
		}else{
			$user = model ( 'User' )->getUserInfo ( $uid );
			if(!empty($user)){
				$client = new \CyClient();
				$eduorg = $client->listEduorgByUser($user['cyuid']);
				$data['userInfo']['eduorgName'] = $eduorg[0]->eduorgName;
			}
		}
		$this->assign('roleName',$roleName);
		// 判断隐私
		if($this->uid != $this->mid) {
			$UserPrivacy = model('UserPrivacy')->getPrivacy($this->mid, $this->uid);
			$this->assign('UserPrivacy', $UserPrivacy);
		}
		//判断用户是否已认证
		$isverify = D('user_verified')->where('verified=1 AND uid='.$uid)->find();
		if($isverify){
			$this->assign('verifyInfo',$isverify['info']);
		}
		$this->assign($data);
		$this->display();
	}

	/**
	 * 公告详细页面
	 */
	public function announcement() {
		$map['type'] = 1;
		$map['id'] = intval($_GET['id']);
		$d['announcement'] = model('Xarticle')->where($map)->find();
		// 组装附件信息
		$attachIds = explode('|', $d['announcement']['attach']);
		$attachInfo = model('Attach')->getAttachByIds($attachIds);
		$d['announcement']['attachInfo'] = $attachInfo;
		$this->assign($d);
		$this->display();
	}

	/**
	 * 公告列表页面
	 */
	public function announcementList() {
		$map['type'] = 1;
		$list = model('Xarticle')->where($map)->findPage(20);
		// 获取附件类型
		$attachIds = array();
		foreach($list['data'] as &$value) {
			$value['hasAttach'] = !empty($value['attach']) ? true : false;
		}

		$this->assign($list);
		$this->display();
	}

	/**
	 * 自动提取标签操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function getTags() {
		$text = t($_REQUEST['text']);
		$format = !empty($_REQUEST['format']) ? t($_REQUEST['format']) : 'string';
		$limit = !empty($_REQUEST['limit']) ? intval($_REQUEST['limit']) : '3';
		$tagX = model("Tag");
		$tagX->setText($text);		// 设置text
		$result = $tagX->getTop($limit,$format);  // 获取前10个标签
		exit($result);
	}

	/**
	 * 根据指定应用和表获取指定用户的标签,同个人空间中用户标签
	 * @param array uids 用户uid数组
	 * @return void
	 */
	private function _assignUserTag($uids) {
		$user_tag = model('Tag')->setAppName('User')->setAppTable('user')->getAppTags($uids);
		$this->assign('user_tag', $user_tag);
	}

	/**
	 * 弹窗发布微博
	 * @return void
	 */
	public function sendFeedBox(){
		$initHtml = t($_REQUEST['initHtml']);
		if(!empty($initHtml)) {
			$data['initHtml'] = $initHtml;
		}
		//投稿数据处理
		$channelID = h($_REQUEST['channelID']);
		if(!empty($channelID)){
			$data['channelID'] = $channelID;
			$data['type'] = 'submission';
		}

		$this->assign($data);
		$this->display();
	}
	public function scoredetail(){
		$list = model('Credit')->getLevel();
		$this->assign( 'list' , $list );
		$this->display();
	}
        
    public function platform(){
            $this->appCssList[] = 'zone.css';
            //家长角色直接进入
            if (Model('CyUser')->hasRole(UserRoleTypeModel ::PARENTS, $this->cyuid)) {
                if (isset($_SESSION['grmhHasLogin']) && $_SESSION['grmhHasLogin']) {
                    $token = M('AppAuth')->getCacheToken('grmh', $GLOBALS['ts']['user']['login']);
                    $this->platUrl = C('PLATFORM_URL') .'token='. $token;
                } else {
                    //获取授权码,记录session
                    $authCode = M('AppAuth')->getAuthcode('grmh', $GLOBALS['ts']['user']['login']);
                    $this->platUrl = C('PLATFORM_URL') .'code='. $authCode;
                    $_SESSION['grmhHasLogin'] = true;
                }
                $this->display();
                return;
            }

            //获取账号对应班级信息
            $cyClient = D('CyCore')->CyCore;
            $classInfo = $cyClient->getClassByUserId($this->cyuid);
            //无班级信息跳转错误页面
            if (empty($classInfo)) {
                $this->display('platform_error');
                return;
            }

            if (isset($_SESSION['grmhHasLogin']) && $_SESSION['grmhHasLogin']) {
                $token = M('AppAuth')->getCacheToken('grmh', $GLOBALS['ts']['user']['login']);
                $this->platUrl = C('PLATFORM_URL') .'token='. $token;
            } else {
                //获取授权码,记录session
                $authCode = M('AppAuth')->getAuthcode('grmh', $GLOBALS['ts']['user']['login']);
                $this->platUrl = C('PLATFORM_URL') .'code='. $authCode;
                $_SESSION['grmhHasLogin'] = true;
            }
            $this->display();
    }
    
    /**
     * 虚拟实验室
     */
	public function virtual(){
		$this->appCssList[] = 'zone.css';
		$this->display();
    }
    
    /**
     * 电子图书馆
     */
    public function library(){
    	$this->appCssList[] = 'zone.css';
    	$user=$GLOBALS['ts']['cyuserdata'];
    	//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
    	$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
    	if($roleEnName=='teacher'||$roleEnName=='instructor'){
    		$librarys=D('ELibrary')->getLibraryForTeacher();
    		$this->assign("librarys",$librarys);
    		$topics=D('ELibrary')->getSpecialTopic();
    		$this->assign("topics",$topics);
    		$refBooks=D('ELibrary')->getRefBookForTeacher();
    		$this->assign("refBooks",$refBooks);
    	}
    	if($roleEnName=='student'||$roleEnName=='parent'){
    		$librarys=D('ELibrary')->getLibraryForStudent();
    		$this->assign("librarys",$librarys);
    		$refBooks=D('ELibrary')->getRefBookForStudent();
    		$this->assign("refBooks",$refBooks);
    	}
    	$mods=D('ELibrary')->getModInfo();
    	$pics=D('ELibrary')->getGallery();
    	$news=D('ELibrary')->getNewspaper();
    	$this->assign("roleName",$roleEnName);
    	$this->assign("mods",$mods);
    	$this->assign("pics",$pics);   	
    	$this->assign("news",$news);
    	$this->display();
    }
    
    /**
     * 数字期刊
     */
    public function journal(){
    	$this->appCssList[] = 'zone.css';
    	$user=$GLOBALS['ts']['cyuserdata'];
    	//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
    	$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
    	if($roleEnName=='teacher'||$roleEnName=='instructor' ||$roleEnName == 'eduadmin'){
     		$journals=D('DJournal')->getJournalForTeacher();
     		$mods=D('DJournal')->getModForTeacher();
    	}
    	if($roleEnName=='student'||$roleEnName=='parent'){
    		$journals=D('DJournal')->getJournalForStudent();
    		$mods=D('DJournal')->getModForStudent();
    	}
    	$this->assign("journals",$journals);
    	$this->assign("mods",$mods);
    	$this->display();
    }
    
    /**
     * 获取首页火热应用展示的数据
     * @app_name  string
     * blog: 热门日志      APP_HOT_BLOG
     * research：主题讨论 APP_HOT_RESEARCH
     * onlineanswer：在线答疑  APP_HOT_ONLINEANSWER
     * vote：网络调研  APP_HOT_VOTE
     * pingke：网上评课  APP_HOT_PINGKE
     * pepole: 本周达人
     *
     *
     */
    function getHotAppInfoList(){
    	$app_name = isset($_POST['app_name'])?$_POST['app_name']:'blog';
    	switch ($app_name){
    		case 'blog':
    			$app_list =S('APP_HOT_BLOG');
    			if(!$app_list){
    				$app_list =D('Blog','blog')->geAppHotBlog();
    				S('APP_HOT_BLOG',$app_list,'7200');
    			}
    			$more_url = U('blog/Index/index');
    			break;
    		case 'research':
    			$app_list =S('APP_HOT_RESEARCH');
    			if(!$app_list){
    				$app_list =D('Research','research')->geAppHotResearch();
    				S('APP_HOT_RESEARCH',$app_list,'3600');
    			}
    			$more_url = U('research/Index/center');
    			break;
    		case 'onlineanswer':
    			$app_list =S('APP_HOT_ONLINEANSWER');
    			if(!$app_list){
    				$app_list =D('Question','onlineanswer')->geAppHotQuestion();
    				S('APP_HOT_ONLINEANSWER',$app_list,'3600');
    			}
    			$more_url = U('onlineanswer/Index/center');
    			break;
    		case 'vote':
    			$app_list =S('APP_HOT_VOTE');
    			if(!$app_list){
    				$app_list =D('Vote','vote')->geAppHotVote();
    				S('APP_HOT_VOTE',$app_list,'3600');
    			}
    			$more_url = U('vote/Index/center');
    			break;
    		case 'pingke':
    			$app_list =S('APP_HOT_PINGKE');
    			if(!$app_list){
    				$app_list =D('Pingke','pingke')->geAppHotPingke();
    				S('APP_HOT_PINGKE',$app_list,'3600');
    			}
    			$more_url = U('pingke/Index/center');
    			break;
    		case 'people':
    			$this->showEredar();
    			break;
    
    	}
    	if ($app_name == 'people'){
    		$result->data = $this->fetch("part/_star_list");
    	}else {
    		$this->assign('app_list',$app_list);
    		$this->assign('more_url',$more_url);
    		$result->data = $this->fetch("part/_hot_app");
    	}
		echo(json_encode($result));
    }
    
    /**
     * 获取本周达人信息方法
     */
    private  function showEredar(){
    	//粉丝达人信息获取
    	$userDataList = S('followerList');
    	if(!$userDataList){
    		$userDataList = D('Follow')->getFollowMasterList(5);
    		S('followerList',$userDataList,3600);
    	}
    	foreach ($userDataList as &$userData){
    		$userData['userInfo'] = model('User')->getUserInfo($userData['uid']);
    		$userData['follow_state'] = model ( 'Follow' )->getFollowState($this->mid,$userData['uid']);
    		$userData['subject'] = model('Node')->getNameByCode('subject',$userData['userInfo']['subject']);
    	}
    	//活跃达人信息获取
    	$eredarList = S('eredarList');
    	if(!$eredarList){
    		$eredarList = D('UserData')->getActiveMasterList(5);
    		S('eredarList',$eredarList,3600);
    	}
    	foreach ($eredarList as &$eradar){
    		$eradar['userInfo'] = model('User')->getUserInfo($eradar['uid']);
    		$eradar['follow_state'] = model ( 'Follow' )->getFollowState($this->mid,$eradar['uid']);
    		$eradar['subject'] = model('Node')->getNameByCode('subject',$eradar['userInfo']['subject']);
    	}
		//积分达人榜信息获取
    	$creditList = S('creditList');
    	if(!$creditList){
    		$creditList = D('CreditRecord')->getCreditMasterList(5);
    		S('creditList',$creditList,3600);
    	}
    	foreach ($creditList as &$credit){
    		$credit['userInfo'] = model('User')->getUserInfo($credit['uid']);
    		$credit['follow_state'] = model ( 'Follow' )->getFollowState($this->mid,$credit['uid']);
    		$credit['subject'] = model('Node')->getNameByCode('subject',$credit['userInfo']['subject']);
    	}
    	$this->assign('followerList',$userDataList);
    	$this->assign('eredarList',$eredarList);
    	$this->assign('creditList',$creditList);
    	$this->assign('mid',$this->mid);
    }
    public function error_page(){
    	$this->assign('msg',$_REQUEST['msg']);
    	$this->display('exception');
    }
    /**************************首页系统监管统计部分开始*************************************/
    public function getCountData() {
    	$type = $_REQUEST ['type'];
    	$userModel = D ( 'CyUser' );
    	switch ($type) {
    		case 'zysy' : // 资源统计
    			// 资源统计接口
    			$resLogic = D ( "ResourceRadar", "Model" );
    			$userData = $GLOBALS ['ts'] ['cyuserdata'];
    			if($userModel->isEduManager($userData,ConstantsModel::CITY_TYPE)){
    				$data = S ( "getCountData_rescount" . ConstantsModel::CITY_TYPE );
    				if (! $data) {
    					$resTotal = $resLogic->getResourceAllNum (); // 获取资源的教材覆盖率 平均每科教材数
    					// 设置缓存
    					$data ['resTotal'] = $resTotal->resourceAllNum;
    					$data ['bookPre'] = $resTotal->bookNum. '%';
    					$data ['subRes'] = $resTotal->avgSubjectNum;
    					$data ['type'] = ConstantsModel::CITY_TYPE;
    					Log::write ( "获取的资源监管统计相关统计数据为：资源总数:[" . $resTotal . '],每科资源数：[' . $bookPre . '],平均每科资源数：[' . $subRes . ']', Log::DEBUG );
    					S ( "getCountData_rescount" . ConstantsModel::CITY_TYPE, $data, 600 );
    				}
    			}
    			//2，区县级
    			if($userModel->isEduManager($userData,ConstantsModel::DISTRICT_TYPE)){
                    $areaCode = $userModel->getManagAreaId ( $userData ['user'] ['cyuid'], ConstantsModel::AREA_TYPE ,ConstantsModel::CODE_TYPE);
                    // 设置缓存
                    $resTotal = $resLogic->getContributionById ($areaCode,ConstantsModel::QY_TYPE_DISTRICT );
                    $data ['resTotal'] = $resTotal->uploadNum;
                    $data ['bookPre'] = $resTotal->uploadAvgNum;
                    $data ['type'] = ConstantsModel::DISTRICT_TYPE;
                    S ( "getCountData_rescount" . ConstantsModel::DISTRICT_TYPE, $data, 600 ); // 重新设置缓存
    			}
    			$this->resData = $data;
    			break;
    		case 'jxjy' : // 教学教研
    			break;
    		case 'kjsy' : // 空间使用
    			break;
    		case 'jszssy':  //教师助手
    			break ;
    		default :
    			exit ( '' );
    	}
    	$this->data = '';
    	$this->display ( './sys_recom/statist/staist_' . $type );
    }
    /**
     * 获取初始化highchar的数据
     */
    public function getInitCharData() {
    	$type = $_REQUEST ['type'];
    	// 判断用户的管理者身份
    	$userData = $GLOBALS ['ts'] ['cyuserdata'];
    	$userModel = D ( 'CyUser' );
    	//1，市级
    	if($userModel->isEduManager($userData,ConstantsModel::CITY_TYPE)){
    		$roleFlg = ConstantsModel::CITY_TYPE;
    	}
    	//2，区县级
    	if($userModel->isEduManager($userData,ConstantsModel::DISTRICT_TYPE)){
    		$roleFlg = ConstantsModel::DISTRICT_TYPE ;
    	}
    	
    	$sysModel = D ( 'SysRecom', 'Model' );
    	switch ($type) {
    		case 'zysy' : // 资源统计
                $areaId = $userModel->getManagAreaId ( $userData ['user'] ['cyuid'], ConstantsModel::AREA_TYPE );
                if($roleFlg == ConstantsModel::CITY_TYPE){
                    $data = $sysModel->getResourceStatistical ( $areaId, ConstantsModel::QY_TYPE_CITY );
                }else if($roleFlg == ConstantsModel::DISTRICT_TYPE){
                    $data = $sysModel->getResourceStatistical ( $areaId, ConstantsModel::QY_TYPE_DISTRICT );
                }
    			echo json_encode ( $data );
    			break;
    		case 'kjsy' : // 空间统计 当前只有市级教研员才有权限
    			if ($roleFlg == ConstantsModel::CITY_TYPE) {
    				// 获取区域下面的所有教研教学统计数据
    				$manageArea = $userModel->getManagAreaId ( $userData ['user'] ['cyuid'], ConstantsModel::AREA_TYPE ,ConstantsModel::CODE_TYPE);
                    // 获取教研员的管辖区域
    				$data = $sysModel->getSpaceStatistical ( $manageArea, ConstantsModel::QY_TYPE_CITY );
    			}
    			echo json_encode ( $data );
    			break;
    		case 'jxjy' :
    				// 获取区域下面的所有教研教学统计数据
    				$manageArea = $userModel->getManagAreaId ( $userData ['user'] ['cyuid'], ConstantsModel::AREA_TYPE ); // 获取教研员的管辖区域
    				$data = $sysModel->getResearchTeachingStatistical ( $manageArea, ConstantsModel::AREA_TYPE,$roleFlg );
    			echo json_encode ( $data );
    			break;
    		default :
    			exit ( '' );
    	}
    }

	/**
	 * 错误页面
	 */
	public function errorPage() {
		$this->display();
	}

}