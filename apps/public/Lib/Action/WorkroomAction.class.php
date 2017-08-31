<?php
import (APPS_PATH."/teachingapp/Lib/Library/util.php");
import (ADDON_PATH."/library/Clients/ResourceClient.php");

use epdcloud\epsp\api\EpspSvcClientFactory;
include_once

include_once  APPS_PATH.'/paper/Common/appInfo.php';
/**
 * ProfileAction 个人档案模块
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
class WorkroomAction extends Action {
    private $epspSvc;
    public function __construct() {
        parent::__construct ();
        $this->epspSvc=new \EpspClient(); 
    }
	/**
	 * _initialize 模块初始化
	 * 
	 * @return void
	 */
	protected function _initialize() {

		$user = $GLOBALS ['ts'] ['cyuserdata'];
		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);

		// 短域名判断
		if (! isset ( $_GET ['uid'] ) || empty ( $_GET ['uid'] )) {
            if(isset ( $_GET ['cyuid'] ) || !empty ( $_GET ['cyuid'] )){
                $cyuid = t($_GET['cyuid']);
                $this->uid = model ( 'User' )->getUidByCyuid($cyuid);
            }else{
                $this->uid = $this->mid;
            }
		} elseif (is_numeric ( $_GET ['uid'] )) {
			$this->uid = intval ( $_GET ['uid'] );

		} else {
			$map ['domain'] = t ( $_GET ['uid'] );
			$this->uid = model ( 'User' )->where ( $map )->getField ( 'uid' );
		}

		$result = Model('UserRole')->getUserRole($this->uid,true);
		$roleids=getSubByKey($result,'roleid');
		if(in_array(UserRoleTypeModel::RESAERCHER,$roleids)){
				
		}else if(in_array(UserRoleTypeModel::TEACHER,$roleids)){
				
		}else if(!in_array(MODULE_NAME,array('Workroom'))){
			U('public/Profile/index',array('uid'=>$this->uid),true);
		}
		
		$isResearch = Model('CyUser')->hasRole(UserRoleTypeModel ::RESAERCHER,$this->cyuid);

		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		$node = model('Node');
		
		$garde=$node->getNameByCode('grade',$user_info['grade']);
		if($isResearch){
			$garde ='';
		}
		$subject=$node->getNameByCode('subject',$user_info['subject']);
		$this->assign('garde',$garde);
		$this->assign('subject',$subject);
		
		//记录来访者
        recordVisitor($this->uid, $this->mid);
		//判断角色 
		$this->assign ( 'uid', $this->uid );
		$cyuserdata = $GLOBALS['ts']['_cyuserdata'];

		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($cyuserdata['user']['login'], $cyuserdata['rolelist']);
		
		//三级教研员信息
		if($roleEnName=='instructor'){
			$instructor=D("CyUser")->getUserDetail($this->cyuid);
			$leveltype=$instructor['instructor_level'];
			if(empty($leveltype)){
				$levelName="教研员";
			}else{
				$levelName=UserRoleTypeModel::getCNRoleName($leveltype);
			}
			$this->assign('levelName',$levelName);
			
		}else{
			$levelName=UserRoleTypeModel::getCNRoleName($roleEnName);
			$this->assign('levelName',$levelName);
		}
		
		$roleCNname = UserRoleTypeModel::getCNRoleName($roleEnName);
		$location['country'] = $cyuserdata['locations']['country'];
		$location['province'] = $cyuserdata['locations']['province'];
		$location['city'] = $cyuserdata['locations']['city'];
		$location['district'] = $cyuserdata['locations']['district'];
		
		//空间名称
		if(empty($user_info['space_name'])){
			$space_name=$user_info['uname'].'工作室'.'('.$subject.$roleCNname.')';
		}else{
			$space_name=$user_info['space_name'];
		}
		
		$this->assign('space_name',$space_name);
		$this->assign('location',$location);
		$this->assign('orglist', $cyuserdata['orglist']);
		$this->assign('roleCNname',$roleCNname);
		$this->assign('index_url', U('public/Workroom/index', array('uid'=>$this->uid)));
		$this->assign('isResearch',$isResearch);
		
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
	 * 初始化导航数据 
	 * @param string $teachType
	 * @param string $resType
	 * @param string $resDetail
	 */
	private function assignNavgation($teachTypeHtml,$resTypeHtml ='',$resDetailHtml=''){
		$teachTypeHtml = $teachTypeHtml?$teachTypeHtml:'<span class="sgray">首页</span>';
		if($resTypeHtml){
			$resTypeHtml = '&gt;&nbsp;'.$resTypeHtml;
			$this->assign('resourceType',$resTypeHtml);
		}
		if($resDetailHtml){
			$resDetailHtml ='&gt;&nbsp;'.$resDetailHtml;
			$this->assign('resDetail',$resDetailHtml);
		}
		//导航数据
		$this->assign('teachType',$teachTypeHtml);
	}
	
	/**
	 * 教研员或教师工作室展示页面
	 */
	public function index() {
		//判断用户是否存在
		$user = model ( 'User' )->getUserInfo ( $this->uid );
		if(!$user){
			U('public/Profile/index',array('uid'=>$this->uid),true);
		}
		
		$userPrivacy = $this->privacy ( $this->uid );
		if($userPrivacy ['space'] == 1){
			$this->hidePerHomePage = true;
			$this->error("根据用户设置的权限，您不可访问TA的主页！");
		}
		
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}

		//当前用户为本人时
		if($this->uid==$this->mid){
			 $this->assign("mark","owner");
		}
		
		$this->_sidebar();
		
		// 初始化用户详细信息 
		$this->initUserDetailByLogin($user_info['login']);
		// 添加积分
		model ( 'Credit' )->setUserCredit ( $this->uid, 'space_access' );
        $this->assign('_title',$user_info['uname'].'的主页');
		$this->display();
		return;
	}
	
	/**
	 * 根据用户登录名初始化详细信息
	 * @param string $login 用户登录名
	 */
	private function initUserDetailByLogin($login){
        $userId = $this->uid;


        $user = S($userId."user");
        if(!$user){
            // 初始化用户简介
            $cyClient = new CyClient();
            $user = $cyClient->getUserByUniqueInfo('login_name',$login);
            S($userId."user",$user,10);
        }
		$this->remark = $user->remark;


        $list = S($userId."list");
        if(!$list){
            // 初始化最近访问用户信息
            $list = model('UserVisitor')->getLastVisitors($this->uid);
            S($userId."list",$list,10);
        }
		$new_list = array();
		foreach($list AS $visitor){
			$visitor['time'] = date('n月j日', $visitor['vtime']);
			$user_info = model ( 'User' )->getUserInfo ( $visitor['vuid'] );
			$visitor['user_info'] = $user_info;
			$new_list[] = $visitor;
		}
		$this->visitors = $new_list;
		
		//获取资源总数
        $obj = S($userId."resCount");
        if(!$obj){
            $params['method'] = 'pan.file.share.list';
            $params['uid'] = $this->cyuid;
            $params['to'] = 'homepage';
            $params['page'] = 1;
            $params['limit'] = 0;
            //是否启用安全监管监测的结果
            if(C('ENABLE_SECURITY')==1){
                $params['security']='0,1';
            }
            $obj = Restful::sendGetRequest($params);
            S($userId."resCount",$obj,10);
        }
		$this->resCount=$obj->total;
		
		// 获取相册总数
        $albumCount = S($userId."albumCount");
        if(!$albumCount){
            $map_album['userId'] = $this->uid;
            $map_album['isDel'] = 0;
            $albumCount = D('Album', 'photo')->where($map_album)->count();
            S($userId."albumCount",$albumCount,10);
        }

        $this->albumCount = $albumCount;
		
		// 获取说说总数
        $feedCount = S($userId."feedCount");
        if(!$feedCount){
            $map['uid'] = $this->uid;
            $map['type'] = 'post';
            $map['app'] = 'public';
            $map['is_del'] = 0;
            $feedCount = D("Feed")->where($map)->count();
            S($userId."feedCount",$feedCount,10);
        }
        $this->feedCount = $feedCount;
		
		// 初始化用户说说信息
        $dates = S($userId."feedMonths");
        if(!$dates){
            $dates = D("Feed")->getFeedMonths($this->uid);
            S($userId."feedMonths",$dates,10);
        }
		$this->dates = $dates;
	}
		
	/**
	 * 教师工作室 
	 */
	public function teaching(){
			$user_info = $GLOBALS['ts']['_user'];
			$ttype = $_REQUEST['ttype'];
			//导航数据
			$teachTypeHtml = '<span class="sgray">教学资源</span>';
			$this->assignNavgation($teachTypeHtml); 
			$this->setTitle('教学资源');
			$this->assign_resource($user_info);
			$this->assign('user_info', $user_info);
			$this->assign('mainnav', "teaching");
			$this->display();
		}
		
	public function teaching_more(){
			$appcode=$_GET['appcode']?$_GET['appcode']:'';
			$login = $GLOBALS['ts']['_user']['login'];
			$user_info = $GLOBALS['ts']['_user'];
			$this->assign('appcode',$appcode);
			$this->assign('mainnav', "teaching");
			$type=$_REQUEST['type'];//1:教学设计 2:教学视频  3:媒体素材  4:教学课件
			$pagesize = 10;
			switch ($type){
				case '1':
					$current_page = isset($_GET['p'])?intval($_GET['p']):1;
					$result =getResListByType($login,"0100",$current_page,10);
					$this->assign('teaching_more',$result);
						
					$page = getPager($result['totalrecords'],$pagesize);
					$this->assign("page",$page);
					
					$title = "教学设计";
					
					break;
				case '2':
					$current_page = isset($_GET['p'])?intval($_GET['p']):1;	
					$result =getResListByType($login,"0200",$current_page,10);
					$this->assign('teaching_more',$result);
						
					$page = getPager($result['totalrecords'],$pagesize);
					$this->assign("page",$page);
					
					$title = "教学视频";
					
					break;
				case '3':
					$current_page = isset($_GET['p'])?intval($_GET['p']):1;	
					$result =getResListByType($login,"0300",$current_page,10);
					$this->assign('teaching_more',$result);
						
					$page = getPager($result['totalrecords'],$pagesize);
					$this->assign("page",$page);
					
					$title = "媒体素材";
					
					break;
				case '4':
					$current_page = isset($_GET['p'])?intval($_GET['p']):1;	
					$result =getResListByType($login,"0600",$current_page,10);
					$page = getPager($result['totalrecords'],$pagesize);
						
					$this->assign('teaching_more',$result);
					$this->assign("page",$page);
					
					$title = "教学课件";
					
					break;
			}
			//导航数据
			$this->assign("title",$title);
			$teachTypeHtml = '<a href="'.U('public/Workroom/teaching',array('uid'=>$this->uid)).'" class="blue">教学资源</a>';
			$resTypeHtml = '<span>'.$title.'</span>';
			$this->assignNavgation($teachTypeHtml, $resTypeHtml);
			$this->assign('user_info', $user_info);
			$this->setTitle($title);
			$this->display();
		}
		
	private function assign_resource($userinfo){
			$login = $userinfo['login'];
			$uid = $userinfo['uid'];
			//"0100","教学设计"
			$teachdesign = getResListByType($login,"0100",1,5);
			$teachdesign['title'] = '教学设计';
			$teachdesign['url'] = U('public/Workroom/teaching_more', array('uid'=>$uid,'type'=>1,'appcode'=>'0100'));
			$teachdesign['preview_url'] = U('public/Workroom/preview', array('uid'=>$uid,'appcode'=>'0100'));
			
			//"0200","教学视频"
			$teachvideo = getResListByType($login,"0200",1,5);
			$teachvideo['title'] = '教学视频';
			$teachvideo['url'] = U('public/Workroom/teaching_more', array('uid'=>$uid,'type'=>2,'appcode'=>'0200'));
			$teachvideo['preview_url'] = U('public/Workroom/preview', array('uid'=>$uid,'appcode'=>'0200'));
			
			//"0300","媒体素材"
			$mediamaterial = getResListByType($login,"0300",1,5);
			$mediamaterial['title'] = '媒体素材';
			$mediamaterial['url'] = U('public/Workroom/teaching_more', array('uid'=>$uid,'type'=>3,'appcode'=>'0300'));
			$mediamaterial['preview_url'] = U('public/Workroom/preview', array('uid'=>$uid,'appcode'=>'0300'));
			
			//"0600","教学课件"
			$teachware = getResListByType($login,"0600",1,5);
			$teachware['title'] = '教学课件';
			$teachware['url'] = U('public/Workroom/teaching_more', array('uid'=>$uid,'type'=>4,'appcode'=>'0600'));
			$teachware['preview_url'] = U('public/Workroom/preview', array('uid'=>$uid,'appcode'=>'0600'));
			
			
			$teachinglist =  array($teachdesign,$teachware,$teachvideo,$mediamaterial);
			unset($teachdesign);
			unset($teachvideo);
			unset($mediamaterial);
			unset($teachware);
			$this->teachData = $teachinglist;
		}
		
	/**
	 * 教研员教学资料展示页面
	 */
	public function teachMaterial(){
			// 获取用户信息
			$user_info = $GLOBALS['ts']['_user'];
			
			// 用户为空，则跳转用户不存在
			if (empty ( $user_info )) {
				$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
			}
			//获取教研指导
			$this->_getResearcherInstruction($this->uid, array(4, 5, 6), 5);
			$title = "教研指导";
			$this->assign('user_info', $user_info);
			$this->assign('mainnav', "material");
			$teachTypeHtml = '<span class="sgray">教学资料</span>';
			$this->assignNavgation($teachTypeHtml);
			$this->display("teach_material");
		}
	/**
	 * 教研员教学指导展示页面
	 */
	public function teachInstruction(){
			// 获取用户信息
			$user_info = $GLOBALS['ts']['_user'];
			// 用户为空，则跳转用户不存在
			if (empty ( $user_info )) {
				$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
			}
			//获取教研指导
			$this->_getResearcherInstruction($this->uid, array(7, 8, 9, 10), 5);
			$this->assign('user_info', $user_info);
			$this->assign('mainnav', "instruction");
			$teachTypeHtml = '<span class="sgray">教学指导</span>';
			$this->assignNavgation($teachTypeHtml);
			$this->display("teach_instruction");
		}
	/**
	 * 教研员教学指导展示页面
	 */
	public function instructionList(){
			// 获取用户信息
			$user_info = $GLOBALS['ts']['_user'];
			
			// 用户为空，则跳转用户不存在
			if (empty ( $user_info )) {
				$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
			}
		
			//获取教研指导类型， 默认为教材介绍
			$type = $_REQUEST['type'] ? $_REQUEST['type'] : appInfo::MATERIAL_INTRO;
			$nav_app_type = getAppName($type);
			//如果不存在该类型，则默认为教材介绍
			if(!$nav_app_type){
				$type = appInfo::MATERIAL_INTRO;
				$nav_app_type = getAppName($type);
			}
			if(in_array($type, array(appInfo::TEACH_DESIGN, appInfo::TEACH_PAPER, appInfo::TEACH_WARE, appInfo::EXAM_INSTRUCTION))){
				$nav_type = "教学指导";
				$this->assign('mainnav', "instruction");
				$this->assign('mainnav_url', U('public/Workroom/teachInstruction', array('uid'=>$this->uid)));
				$mainnav_url= U('public/Workroom/teachInstruction', array('uid'=>$this->uid));
			} elseif(in_array($type, array(appInfo::MATERIAL_INTRO, appInfo::MATERIAL_TRAIN, appInfo::STAND_EXPLAIN))){
				$nav_type = "教学资料";
				$this->assign('mainnav', "material");
				$this->assign('mainnav_url', U('public/Workroom/teachMaterial', array('uid'=>$this->uid)));
				$mainnav_url= U('public/Workroom/teachMaterial', array('uid'=>$this->uid));
			}elseif(in_array($type, array(appInfo::FILE_NOTICE,appInfo::EDU_NEWNOTICE))){
				$nav_type = "工作动态";
				$this->assign('mainnav', "teacher_list");
				$this->assign('mainnav_url', U('public/Workroom/teacher_list', array('uid'=>$this->uid)));
				$mainnav_url= U('public/Workroom/teacher_list', array('uid'=>$this->uid));
			}
			$this->assign('type', $type);
			$this->assign('user_info', $user_info);
			$teachTypeHtml = '<a href="'.$mainnav_url.'" class="blue">'.$nav_type.'</a>';
			$this->assignNavgation($teachTypeHtml,$nav_app_type);
			$this->display("instruction_list");
		}
	/**
	 * 教研员教学指导展示页面
	 */
	public function instructionDetail(){
			require_once './apps/paper/Common/appInfo.php';
			// 获取用户信息
			$user_info = $GLOBALS['ts']['_user'];
		
			// 用户为空，则跳转用户不存在
			if (empty ( $user_info )) {
				$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
			}
			
			//获取教研指导类型， 默认为教材介绍
			$type = $_REQUEST['type'] ? $_REQUEST['type'] : appInfo::MATERIAL_INTRO;
			$paperId = $_REQUEST['paper_id'];
			if(!$paperId){
				return;
			}
			$nav_app_type = getAppName($type);
			//如果不存在该类型，则默认为教材介绍
			if(!$nav_app_type){
				$type = appInfo::MATERIAL_INTRO;
				$nav_app_type = getAppName($type);
				$mainnav_url= U('public/Workroom/teachMaterial', array('uid'=>$this->uid));
			}
			if(in_array($type, array(appInfo::TEACH_DESIGN, appInfo::TEACH_PAPER, appInfo::TEACH_WARE, appInfo::EXAM_INSTRUCTION))){
				$nav_type = "教学指导";
				$this->assign('mainnav', "instruction");
				$this->assign('mainnav_url', U('public/Workroom/teachInstruction', array('uid'=>$this->uid)));
				$mainnav_url= U('public/Workroom/teachInstruction', array('uid'=>$this->uid));
			} elseif(in_array($type, array(appInfo::MATERIAL_INTRO, appInfo::MATERIAL_TRAIN, appInfo::STAND_EXPLAIN))){
				$nav_type = "教学资料";
				$this->assign('mainnav', "material");
				$this->assign('mainnav_url', U('public/Workroom/teachMaterial', array('uid'=>$this->uid)));
				$mainnav_url= U('public/Workroom/teachMaterial', array('uid'=>$this->uid));
			}
			$paper = D('paper', 'paper')->where(array('id'=>$paperId))->find();
			if($paper && $this->mid != $this->uid){
				D('paper', 'paper')->where(array('id'=>$paperId))->setInc("readCount");
			}elseif (!$paper){
				$this->error("您访问的文章已删除");
			}
			//获取文章附件
			$attachIds = D('paper_attach')->where(array("paper_id" =>$paperId))->field(array("attach_id","attach_type"))->select();
			
			$this->assign('paper', $paper);
			$this->assign('attachIds', $attachIds);
			$this->assign('type', $type);
			$this->assign('user_info', $user_info);
			$teachTypeHtml = '<a href="'.$mainnav_url.'" class="blue">'.$nav_type.'</a>';
			$teachappHtml  = '<a href="'.U('public/Workroom/instructionList',array('uid'=>$this->uid,'type'=>$type)).'" class="blue">'.$nav_app_type.'</a>';
			$this->assignNavgation($teachTypeHtml,$teachappHtml,$paper['title']);
			$this->display("instruction_detail");
		}
	/**
	 * 教研员工作动态展示页面
	 */
	public function teacher_list(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		$this->_assignDynamicWork($this->uid,"",10);
		//导航信息
		$teachTypeHtml = '教研动态';
		$this->assignNavgation($teachTypeHtml);
		$this->display();
	}

	/**
	 * 获取用户关注的学校或班级  
	 *
	 * @return void
	 */
	private function org_following($uid,$type){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		
		$node = model('Node');
		$this->grade = $node->getNameByCode('grade',$user_info['grade']);
		$this->subject = $node->getNameByCode('subject',$user_info['subject']);
		
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
			if($type == 1){
				$orgListData = D('CySchool')->get_school_infos_by_ids($orgids);
			}else{
				$orgListData = D('CyClass')->get_class_infos_by_ids($orgids);
			}
			$this->assign ( 'orgListData', $orgListData );
			
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
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 *
	 * @param array $fids
	 *        	用户uid数组
	 * @return void
	 */
	private function _assignOrgFollowState($fids = null) {
		// 批量获取与当前登录用户之间的关注状态
		$follow_state = D ( 'CampusFollow','class' )->getFollowStateByFids ( $this->mid, $fids );
		$this->assign ( 'org_follow_state', $follow_state );
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
		$this->assign ( 'follow_info', $user_info );
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
	 * 获取工作动态
	 */
	private function _assignDynamicWork($uid,$order,$limit=5) {
	
		// 排序字段
		switch($order){
			case 'uv':$order = "viewcount asc";break;
			case 'dv':$order = "viewcount desc";break;
			case 'ut':$order = "ctime asc";break;
			default:$order = "ctime desc";
		}
		// 查询条件
		$FileCondition = array();
		$FileCondition['uid'] = $uid;
		// 类型为3标识是教研指南
		$FileCondition['type'] = 3;
		$FileCondition['isDeleted']  = 0;

		// 实例化NoticeModel
		$Notice = D('Notice');
		// 公告列表
		$FileList = $Notice->getNoticeLists(1,$FileCondition,$limit,$order);

		$this->assign ( 'FileData', $FileList );
	}
	/**
	 * 公告首页
	 */
	private function _init_notice($type,$uid) {
	
		//导入分页类
		import("@.ORG.Page");
	
		// 查询条件
		$condition = array();
		$condition['uid'] = $uid;
		// 类型为1标识是教研公告
		$condition['type'] = $type;
		$condition['isDeleted']  = 0;

		// 分页时每页记录数
		$num = 10;
	
		// 实例化NoticeModel
		$Notice = D('Notice');
	
		// 查询出公告总数
		$count =  $Notice->getNoticeCount($condition);
	
		$p = new Page($count, $num);
		$nowPage = $p->nowPage;
		$p->setConfig('prev', '上一页');
		$p->setConfig('next', '下一页');
		$page = $p->show();
	
		// 公告列表
		$list = $Notice->getNoticeLists($nowPage,$condition,$num,"ctime desc");
	
		// 模板变量
		$this->nowpage = $nowPage;
		$this->j = $num * ($nowPage - 1);
		$this->page = $page;
		$this->list = $list;
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
	 * 教研员工作室头部数据
	 * 
	 * @return void
	 */
	public function _top() {
		// 不再使用
	}
	/**
	 * 主页左侧
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
		$sidebar_following_list = model ( 'Follow' )->getFollowingList ( $this->uid, null, 9 );
		$this->assign ( 'sidebar_following_list', $sidebar_following_list );
		// 加载粉丝列表
		$sidebar_follower_list = model ( 'Follow' )->getFollowerList ( $this->uid, 9);
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
		
		$msgroupids =getSubByKey($sidebar_following_list['data'], 'fid',array('type',3));
		$msgroupData = D('MSGroup','msgroup')->getMsGroupList($msgroupids);
		
		//获取组织信息
		$schoolListData = D('CySchool')->get_school_infos_by_ids($schoolids);
		$classListData = D('CyClass')->get_class_infos_by_ids($classids);
		
		$this->assign ( 'schoolListData', $schoolListData );
		$this->assign ( 'classListData', $classListData );
		$this->assign ( 'msgroupData', $msgroupData );
		$this->_assignUserInfo ( $uids );
	}
	/**
	 * 获取教研指导数据
	 * @param int $uid
	 * @param array $types 需要查询的类型
	 */
	private function _getResearcherInstruction($uid, $types, $limit = 5){
		$list = array();
		if(empty($types)){
			$types = array(4, 6, 7, 8, 9, 10);
		}
		for($i = 4; $i <= 12; $i++){
			if(in_array($i, $types)){
				$list[$i] = D("Paper", "paper")->getLastPapers($uid, $i, $limit);
			}
		}
		$this->assign("instruction_list", $list);
	}
	
	public function teacher_studio(){
		$subjectcode = $_REQUEST['subjectcode']?$_REQUEST['subjectcode']:'';
		$eduRole = $_REQUEST['eduRole']?$_REQUEST['eduRole']:'researcher';

		/*----------------最受欢迎的教研员和最活跃的教研员--------------------*/
        $value = json_decode($this->epspSvc->teacherCommunityMore($this->mid,$eduRole),true);
		if($value['Code']==0){
			$result=$value['Data'];
			// 最受欢迎的教研员

            $popularResearchers = $this->userAvatar($result['popularResearchers']);

            $popularTeachers = $this->userAvatar($result['popularTeachers']);

			// 活跃的教研员
            $activeResearchers = $this->userAvatar($result['activeResearchers']);

            $activeTeachers = $this->userAvatar($result['activeTeachers']);

			//最受欢迎的名师工作室

            $popularFamteachers = $this->msAvatar($result['popularFamteachers']);

			// 活跃的名师工作室
            $activeFamteachers = $this->msAvatar($result['activeFamteachers']);

			$this->eduRole = $eduRole;
			$this->currentUid = $this->mid;
			$this->popularResearchers = $popularResearchers;
			$this->activeResearchers = $activeResearchers;
			$this->popularTeachers = $popularTeachers;
			$this->activeTeachers = $activeTeachers;
			$this->popularFamteachers = $popularFamteachers;
			$this->activeFamteachers = $activeFamteachers;
			
			$this->msgroup_follow_state = $result['msGroupFollowState'];

			$this->assign("provinces", $result['provinces']);
			$this->assign('subjects', $result['subjects']);
			$this->assign('grades', $result['grades']);
			$this->assign('roles', $result['roles']);

			// 获取当前用户所属名师工作室
			// 汇聚页判断用户是否属于工作室
			// 如果属于工作室则不显示关注按钮
			$this->assign('user_groups', $result['userGroups']);
			$this->assign('subjectcode',$subjectcode);
			$this->display();
		}
	}

    /**
     * 获取用户头像路径
     * @param $users
     * @return array
     */
    private function userAvatar($users){
        foreach($users as &$user){
            $user = array_merge ( $user, model ( 'Avatar' )->init ( $user ['uid'] )->getUserPhotoFromCyCore ($user ['uid']));
        }
        return $users;
    }

    /**
     * 获取名师工作室头像
     * @param $msgroups
     * @return array
     */
    private function msAvatar($msgroups){
        $params['app']='msgroup';
        $msAvatar = D( 'MSAvatar','msgroup' );
        foreach($msgroups as &$msgroup){
            $params['rowid']=intval($msgroup['gid']);
            $msAvatar->init($params);
            $msgroup['image'] = $msAvatar->getAvatar ();
        }
        return $msgroups;
    }
	public function teacher_list_more(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		$type=$_REQUEST['type'];//1 通知2、资讯
		switch ($type){
			case '1':
				$this->assign("title","教研动态");
				$title="教研指南";
				$this->_init_notice(3,$this->uid);
				break;
			case '2':
				$title="教研日志";
				$this->assign("title","教研日志");
				$this->_init_notice(4,$this->uid);
				break;
		}

		$this->assignNavgation("教研动态");
		$this->display();
	}
	public function teacher_preview(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		$detail=$this->_getPreview($_GET ['id']);
		switch($detail['type']){
			case 3:
				$title="教研动态";
				$type=1;
				break;
			case 4:
				$title="教研日志";
				$type=2;
				break;
		}
		//导航信息
		$teachTypeHtml = '<a href="'.U('public/Workroom/teacher_list_more',array('uid'=>$this->uid,'type'=>1)).'" class="blue">教研动态</a>';
		$resTypeHtml = '';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml,$detail['title']);
		$this->display();
	}

	/**
	 * 教学研讨
	 * @author xypan 10.15
	 * @version TS3.0
	 */
	public function teachingResearch(){
		
		$type  = empty($_GET['type']) ? '0':$_GET['type'];
		
		$user_info = model ( 'User' )->getUserInfo ($this->uid );
		$this->_top();
	
		// 主题讨论
		$researchs = D('Research','research')->where(array('uid'=>$this->uid,'to_space'=>1,'is_del'=>0))->order('id DESC')->page("1, 5")->select();
		
		// 在线答疑
		$questions = D('Question','onlineanswer')->getQuestionList($this->uid,5,0);
		
		//网络调研
		$votes=D('Vote','vote')->where(array('uid'=>$this->uid,'to_space'=>1))->order('id DESC')->findPage(5);
		
		//网络评课
		$pingkes=D('Pingke','pingke')->where(array('uid'=>$this->uid,'to_space'=>1,'is_del'=>0))->order('id DESC')->page("1, 5")->select();
		
		$this->pingkes = $pingkes;
		$this->votes = $votes['data'];
		
		$this->researchs = $researchs;
		$this->questions = $questions['data'];
		$title = '教学研讨';
		$teachTypeHtml = '<span class="sgray">'.$title.'</span>';
		$this->assignNavgation($teachTypeHtml);
		$this->setTitle($title);
		$this->assign("user_info",$user_info);
		$this->assign('mainnav', "teachingResearch");
		$this->setTitle ("教学研讨");
		$this->display();
	
	}
	
	/**
	 * 更多的主题讨论
	 */
	public function moreResearch(){
		$researchs = D('Research','research')->where(array('uid'=>$this->uid,'to_space'=>1,'is_del'=>0))->order('id DESC')->page("1, 5")->select();
		$user_info = $GLOBALS['ts']['_user'];
		$this->researchs = $researchs;
		$this->assign("user_info",$user_info);
		$teachTypeHtml = '<a href="'.U('public/Workroom/teachingResearch',array('uid'=>$this->uid)).'" class="blue">教学研讨</a>';
		$resTypeHtml = '<span>主题讨论</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml);
		$this->title = "主题讨论";
		$this->assign('mainnav', "teachingResearch");
		$this->display('teachingResearch_more');
	}
	
	public function moreQuestion(){
		$uid = $this->uid;
		$user_info = model ( 'User' )->getUserInfo ($uid);
		$this->_top();
		
		// 每页显示记录数
		$num = 10;
		// 实例化QuestionModel
		$Question = D('Question','onlineanswer');
		$questions = $Question->getQuestionList($uid,$num,0);
		$p = new Page ( $questions['count'], $num );
		$page = $p->show ();
		
		$this->page = $page;
		$this->questions = $questions['data'];
		$teachTypeHtml = '<a href="'.U('public/Workroom/teachingResearch',array('uid'=>$this->uid)).'" class="blue">教学研讨</a>';
		$resTypeHtml = '<span>在线答疑</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml);
		$this->setTitle( '在线答疑' );
		$this->assign("user_info",$user_info);
		$this->assign('mainnav', "teachingResearch");
		$this->display ();
	
	}
	
	public function moreVote(){
		///网络调研
		$votes=D('Vote','vote')->where(array('uid'=>$this->uid,'to_space'=>1))->order('id DESC')->findPage(10);
		
		$this->votes = $votes['data'];
		
		$teachTypeHtml = '<a href="'.U('public/Workroom/teachingResearch',array('uid'=>$this->uid)).'" class="blue">教学研讨</a>';
		$resTypeHtml = '<span>网络调研</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml);
		$this->setTitle( '网络调研' );
		$this->assign('mainnav', "teachingResearch");
		$this->display ();
	}
	public function morePingke(){
		//网络评课
		$pingkes=D('Pingke','pingke')->where(array('uid'=>$this->uid,'to_space'=>1,'is_del'=>0))->order('id DESC')->page("1, 10")->select();
		
		$this->pingkes = $pingkes;
		
		$teachTypeHtml = '<a href="'.U('public/Workroom/teachingResearch',array('uid'=>$this->uid)).'" class="blue">教学研讨</a>';
		$resTypeHtml = '<span>网上评课</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml);
		$this->setTitle( '网上评课' );
		$this->assign('mainnav', "teachingResearch");
		$this->display ();
	}
	/**
	 * 课后总结
	 */
	public function lesson_summary(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		
		$papermodel = D("Paper","paper");
		//教学日志  TODO
		$condition['type'] = appInfo::Paper;
		$condition['pagesize'] = 5;
		$condition['current_page'] = 1;
		$page_conditon = $condition['current_page'].",".$condition['pagesize'];
		$condition['page'] = $page_conditon;
		$condition['private'] = array(1, 2);
		$this->teach_diaries = $papermodel->selectPapersByPage($this->uid, $condition);
			
		//教学反思 TODO
		$condition['type'] =appInfo::Reflection;
		$this->teach_reflec = $papermodel->selectPapersByPage($this->uid, $condition);
		
		$this->assign('mainnav', "lesson_summary");
		
		$title = '课后总结';
		$teachTypeHtml = '<span class="sgray">'.$title.'</span>';
		$this->assignNavgation($teachTypeHtml);
		$this->setTitle($title);
		$this->display();
	}
	
	/**
	 * 课后总结更多
	 */
	public function more_summary(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		// 每页显示记录数
		$num = 10;
		// 实例化QuestionModel
		$papermodel = D("Paper","paper");
		
		$type=$_REQUEST['type'];//1 日志2、反思
		
		switch ($type){
			case '1':
				$title = "教学日志";
				//查询条件
				$condition['private'] = array(1, 2);
				$condition['type'] =appInfo::Paper;
				//结果集
				break;
			case '2':
				$title = "课后反思";
				//查询条件
				$condition['private'] = array(1, 2);
				$condition['type'] =appInfo::Reflection;
				//结果集
				break;
				
		}
		$summary_content = $papermodel->getPaperList($this->uid,$num,$condition);
		$p = new Page ( $summary_content['count'], $num );
		$page = $p->show ();
		$this->page = $page;
		$this->assign('type',$type);
		$this->assign('summary_content',$summary_content);
		
		//导航数据
		$this->assign("title",$title);
		$teachTypeHtml = '<a href="'.U('public/Workroom/lesson_summary',array('uid'=>$this->uid)).'" class="blue">课后总结</a>';
		$resTypeHtml = '<span>'.$title.'</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml);
		$this->setTitle($title);	
		
		$this->assign('mainnav', "lesson_summary");
		
		$this->display('lessonSummary_more');
	}
	
	
	/**
	 * 课后总结预览
	 */
	public function summary_preview(){
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		$id=$_REQUEST['pid'];
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
		
		$papermodel = D("Paper","paper");
		// 非本人查看，浏览加一
		
		if($this->uid != $this->mid){
			$read = $papermodel->viewCountAdd($this->uid, $id);
		}
		
		$type=$_REQUEST['type'];//1 日志2、反思
		$paperId=$_REQUEST['pid'];
		switch ($type){
			case '1':
				$title= "教学日志";
				$summary_content = $papermodel->selectPaperById($paperId);
			
				break;
			case '2':
				$title = "课后反思";
				$summary_content = $papermodel->selectPaperById($paperId);
				break;
		
		}
		if(!$summary_content){
			
			$this->error("您所查看的文章不存在");
		}
		$this->assign('type',$type);
		$this->assign("title",$title);
		$this->setTitle($title);
		$this->summary_content  = $summary_content;
		//导航数据
		$this->assign("title",$title);
		$teachTypeHtml = '<a href="'.U('public/Workroom/lesson_summary',array('uid'=>$this->uid)).'" class="blue">课后总结</a>';
		$resTypeHtml = '<a href="'.U('public/Workroom/more_summary',array('uid'=>$this->uid,'type'=>$type)).'" class="blue">'.$title.'</a>';
		$resDetailHtml =  '<span title="'.$summary_content['title'].'">'.getShort($summary_content['title'],30,'...').'</span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml, $resDetailHtml);
			
		//获取文章附件
		$attachIds = D('paper_attach')->where(array("paper_id" =>$paperId))->field(array("attach_id","attach_type"))->select();
		$this->assign('attachIds',$attachIds);
		$this->assign('mainnav', "lesson_summary");
		
		$this->display('lessonSummary_preview');
	}
	/**
	 * 教学资源预览
	 */
	public function preview(){
		$resclient = new  ResourceClient();
		// 获取用户信息
		$user_info = $GLOBALS['ts']['_user'];
		
		// 用户为空，则跳转用户不存在
		if (empty ( $user_info )) {
			$this->error ( L ( 'PUBLIC_USER_NOEXIST' ) );
		}
		$this->assign('user_info', $user_info);
        //获取资源信息
		$resid = $_REQUEST['id'];
		$appCode = $_REQUEST['appcode'];
		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->mid;
		
		if (empty($resid)) {
			$this->error("当前资源不存在！");
		} else {
			$obj = $resclient->Res_GetResIndex($resid,true);

			$resourceInfo = $obj->data[0];
			if (empty($resourceInfo) || $resourceInfo->lifecycle->curstatus != "1" || $resourceInfo->lifecycle->auditstatus != "1") {
				$this->error("当前资源不存在或未被审核！");
			}

			//非本人预览，浏览量+1
			if ($uid != $this->mid) {
				$viewcount = $resourceInfo->statistics->viewcount;
				$viewcount = empty($viewcount) ? 0 : $viewcount;
				$viewcount = intval($viewcount) + 1;
				$resclient->Res_UpdateStatistics($resid, 'viewcount', $viewcount);
			}
		}

		$app= $this->getAppByCode($appCode,$uid);
		$this->assign("appname",$app['appname']);
		$this->assign("appurl",$app['url']);
		$this->assign("resourceInfo",$resourceInfo);
		$user_data=D('user','User')->getUserInfoByLogin($resourceInfo->general->creator);
		$resourceInfo->general->creatorName=$user_data['uname'];
		$resdetail = $resourceInfo->general;
		$date = $resourceInfo->date;
		$uploadtime = new DateTime($date->uploadtime);
		$date->uploadtime = date('Y-m-d H:i:s', strtotime($date->uploadtime));
		$extension =$resdetail->extension;
		$audio = array("mp3","wma","wav","ogg","ape","mid","midi");
		$preview_url = in_array($extension, $audio)?$resourceInfo->file_url:$resourceInfo->preview_url;
		$realTitle = $resdetail->title;
		$pathinfo = pathinfo($realTitle);
		if(strtolower($pathinfo['extension'])!=$extension){
			$realTitle = $realTitle.'.'.$extension;
		}
		
		$r = D("Resource", "reslib")->where("rid='".$resourceInfo->general->id."'")->find();
		$this->assign("ts_resource_id",$r['id']);
		
		//导航信息
		$teachTypeHtml = '<a href="'.U('public/Workroom/teaching',array('uid'=>$this->uid)).'" class="blue">教学资源</a>';
		$resTypeHtml = '<span><a href="'.$app['url'].'" class="blue">'.$app['appname'].'</a></span>';
		$this->assignNavgation($teachTypeHtml, $resTypeHtml,$realTitle);
		
		$this->extension = $extension;
		$this->assign("resourceInfo",$resourceInfo);
		$this->assign("realTitle",$realTitle);
		$this->assign("preview_url",$preview_url);
		$this->assign("isUploadLimit",false);
		
		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->mid;
		$this->assign("uid",$uid);
		$this->assign("mid",$this->mid);
		$this->assign("cyuid",$this->cymid);
		$this->assign('mainnav', "teaching");
		$this->display('teacher_res_preview');
	}
	/**
	 * 查看查出的用户和当前登录用户之前的关注关系和用户的详细信息
	* @param int $users 用户列表
	*/
	private function getUserFlowerState($users){
		$result = array();
	
		foreach ($users as $value){
				
			$value = array_merge($value,model("Follow")->getFollowState($this->mid, $value['uid']));
				
			array_push($result,$value);
		}
	
		return $result;
	}
	/**
	 * 获取文章信息
	 */
	private function _getPreview($id){
		$condition = array();
		$condition['id'] =$id;
		$condition['isDeleted'] = 0;
		// 实例化NoticeModel
		$Notice = D('Notice');
		$detail = $Notice->getNoticeDetail($condition);
		// 查询出错
		if(!$detail){
			$this->error("文章已删除!");
		}
		
		$attachIds = D('notice_attach')->where("noticeId = {$detail['id']}")->field(array("attachId","attach_type"))->select();
		$ids = array();

		// 转换成id的一维数组
		foreach ($attachIds as $k=>&$attachId){
			$attachId['attach_id'] = $attachId['attachId'];
		}
		$mid = $this->mid;
		
		// 如果当前登录的用户不是通知发布人
		if($mid != $detail['uid']){
			$data = array();
			$data['viewcount'] = intval($detail['viewcount']) + 1;
				
			// 浏览次数加1
			$res = $Notice->updateNotice($condition['id'],$data);
				
			if($res){
				$detail['viewcount'] = intval($detail['viewcount']) + 1;
			}
		}
		$this->assign("detail",$detail);
		$this->assign("attachIds", $attachIds);
		return $detail;
        //返回值供MODULE使用sjzhao
	}

	/**
	 *  初始化节点信息
	 * @param  $node
	 * @param  $assignName
	 * @param  $condition
	 */
	private function _init_nodes($node='subject',$assignName,$condition=array()){
	 	$nodeModel = Model('Node');
		if($node =='subject'){
			$categoroys = $nodeModel->subjects;
		}else if($node == 'grade'){
			$categoroys = $nodeModel->grades;
		}
		$this->assign($assignName,$categoroys);
	}
	
	/**
	 * 初始化教研员角色
	 */
	private function _init_roles(){
		$roles = array(array('roleid'=>UserRoleTypeModel::COUNTY_RESAERCHER,'name'=>'区县教研员'),
						array('roleid'=>UserRoleTypeModel::CITY_RESAERCHER,'name'=>'市级教研员'),
						array('roleid'=>UserRoleTypeModel::PROVINCE_RESAERCHER,'name'=>'省级教研员'));
						var_dump($roles);exit();		
		$this->assign('roles',$roles);
		
	}
	
	/**
	 * 初始化城市信息
	 * 
	 */
	private function _init_city($area_code,$assignName){
		$result = D("CyArea")->listAreaByCode($area_code,"city",0,100);
		if($assignName=="citys"){
			foreach ($result as $key=>$value){//删除巢湖市sjzhao
				if($value['code']=="341400"){
					unset($result[$key]);
				}
			}
		}
		$this->assign($assignName,$result);
		return $result;
	}
	
	/**
	 * 初始化省信息
	 */
	private function _init_province($area_code,$assignName){
		$result = D("CyArea")->listAreaByCode($area_code,'province',0,100);
		$this->assign($assignName,$result);
	}
	/**
	 * 
	 * @param string $appCode
	 * @param  int $uid
	 */
	private function getAppByCode($appCode,$uid){
		$app=array();
		switch ($appCode){
			case '0100':
				$app['appname']='教学设计';
				$app['url']='index.php?app=public&mod=Workroom&act=teaching_more&uid='.$uid.'&type=1&appcode=0100';
				break;
			case '0200':
				$app['appname']='教学视频';
				$app['url']='index.php?app=public&mod=Workroom&act=teaching_more&uid='.$uid.'&type=2&appcode=0200';
				break;
			case '0300':
				$app['appname']='媒体素材';
				$app['url']='index.php?app=public&mod=Workroom&act=teaching_more&uid='.$uid.'&type=3&appcode=0300';
				break;
			case '0600':
				$app['appname']='教学课件';
				$app['url']='index.php?app=public&mod=Workroom&act=teaching_more&uid='.$uid.'&type=4&appcode=0600';
				break;
		}
	
		return $app;
	}
	/**
	 * 根据用户帐号检测激活状态，是否是未激活的用户
	 */
	public function checkActState(){
		$acc = $_REQUEST['account'];
        $result_json = $this->epspSvc->checkActState($acc);
        $result = json_decode($result_json,ture)['data'];
        if($result['status']=='0'){
            echo '0';
        }
	}
}
