<?php
class ClassHomeAction extends Action{
	
	/**
	 * 首页
	 * @return void
	 */
	private $_classType = 2;
	private $_sessionKey = "visit_class";
	private $_db_key = "cid";
	private $cid ; 
	
	/***
	 * 初始化班级信息 
	 */
	public function  _initialize(){
		$this->cid = $_GET['cid'];
		$this->_init_class($this->cid,true);
		$this->_init_person($this->cid);
		$bmember = $this->_checkmember($this->mid,$this->cid);
		$this->assign('bclassmember',$bmember);
		$this->assign('classId', $this->cid);
		$this->assign('empty','<div class="center" style="padding:10px 0px;"><p class="green">暂时没有数据</p></div>');
			
			//日期
		$arr=array("天","一","二","三","四","五","六");
		$dateTime=array('week'=>"星期".$arr[date("w")],'date'=>date("n")."月".date("j")."日");
		$this->assign('dateTime', $dateTime);
	}
	
	/**
	 * 判断是否为该班级成员
	 * @param  $uid
	 * @param  $cid
	 * @return inetger 1:班级成员  -1：   非班级成员 
	 */
	private function _checkmember($uid,$cid){
		$user = D('User')->getUserInfo($uid);
		$cyuserdata = D('CyUser')->getCyUserInfo($user['login']);
		$orglist = $cyuserdata['orglist'];
		if(!empty($orglist['class'])){
			foreach($orglist['class'] as $org){
				if($org['id']==$cid){
					return 1;
				}
			}
		}
		return -1;
	}

	
	public function index()	{
		$cid = $this->cid;
	    $this->_init_list($cid);
	  	$this->_init_album($cid);
	  	$homework=model('ClassHomework')->getHomeworkByCid(825,1);//作业
	  	$homeworklength=count($homework);//作业数量
	  	$newhomework=$homework[0]['content'];
	  	//访问次数记录
	  	$this->_visitor($cid, $this->uid);
	  	//当日课表
	  	$this->_init_schedule($cid);
		
	  	//照片数量
	  	$photo_album = D("Album", "photo")->where("classId=".$cid)->getField('id,photoCount');
	  	$photo_count = 0;
	  	foreach($photo_album AS $count){
	  		$photo_count += intval($count);
	  	}
	  	$this->assign('photo_count', $photo_count);
        $this->assign('cid',$cid);
        $this->assign('newhomework',$newhomework);
        $this->assign('homeworklength',$homeworklength);
        $this->setTitle("班级主页");
		$this->display();
		
	}

	public function blog(){

        $cid = $this->cid;
        $this->_init_list($cid);
       
	  	//访问次数记录
	  	$this->_visitor($cid, $this->uid);
       //获得分类的计数
       	$Categorylist =  D('BlogCategory','blog')->getClassCategory($cid);
        $category = D('Blog','blog')->getBlogCount($cid,$Categorylist,true);
		
        $this->assign('category',$category);
        $this->setTitle("班级的{$this->app['app_alias']}");
        $this->display();
	}
	
	public function album(){
		
		$cid = $this->cid;
	  	//访问次数记录
	  	$this->_visitor($cid, $this->uid);

		//照片数据
		$photo_album = D("Album", "photo")->where("classId=".$cid)->getField('id,photoCount');
		$photo_count = 0;
		foreach($photo_album AS $count){
			$photo_count += intval($count);
		}
		$this->assign('photo_count', $photo_count);
		$list = D('Photo', 'photo')->getDataWidthClassId("classNew", "", 12,$cid);
		$this->assign('photo_list', $list);
		
		//相册数据
		$map['classId'] = $cid;
		$map['isDel'] = 0;
		$photo_data = D('Album', 'photo')->where($map)->order("mTime DESC")->limit("0,3")->select();
		$this->assign('photo_data', $photo_data);
		$album_count=D('Album', 'photo')->where($map)->count();
		$this->assign('album_count', $album_count);
		//暂无数据信息
		$this->assign('empty','<div class="center" style="padding:10px 0px;"><p class="green">暂时没有相册</p></div>');
		$this->setTitle("照片墙");
		$this->display();
	}
	/*
	 * 百宝箱
	 */
	public function treasurebox(){
		$cid = $this->cid;
		$this->_init_person($cid);
		$this->assign('classId',$cid);
		$this->display();
	}
	/**
	 * 班级作业
	 */
	public function class_work(){
		$cid = $this->cid;
		$this->_init_person($cid);
		$this->assign('classId',$cid);
		$this->display();
	}
	/**
	 * 记录班级来访
	 */
	private function _visitor($cid, $vuid){
		//SESSION是否记录了当前用户访问任意班级的信息
		if(isset($_SESSION[$this->_sessionKey])){
			$visit_class =  $_SESSION[$this->_sessionKey];
		} else{
			$visit_class = array();
		}
		//当前时间
		$now_time = time();
		//是否更新数据库，添加来访次数
		$will_update = false;
		//是否记录了当前用户访问当前班级的信息
		if(array_key_exists($cid, $visit_class)){
			$last_visit_time = $visit_class[$cid];
			// 如果SESSION记录时间超过一小时，则添加访问次数
			($now_time > ($last_visit_time + 60*60)) &&  $will_update = true;
		//如果没有记录，则取查询数据库，记录时间是否在1小时内
		} else {
			$vtime = D('UserVisitor')->where($this->_db_key.'='.$cid.' AND vuid='.$vuid)->getField('vtime');
			($now_time > $vtime + 60*60) &&  $will_update = true;
			//将数据库中记录的用户访问时间添加到session
			$visit_class[$cid] = $vtime;
			$_SESSION[$this->_sessionKey] = $visit_class;
		}
		//添加来访次数
		if($will_update){
			//更新SESSION
			$visit_class[$cid] = $now_time;
			$_SESSION[$this->_sessionKey] = $visit_class;
			$data[$this->_db_key] = $cid;
			$data['vuid'] = $vuid;
			$result = D('UserVisitor')->where($data)->save(array('vtime'=>$now_time));
			if(!$result){
				$data['vtime'] = $now_time;
 				D('UserVisitor')->add($data);
			}
			D('ClassData') ->updateKey("visitor_count", 1, true, $cid, $this->_classType);
		}
	}
	
	/**
	 * 初始化课程表
	 * @param  $cid
	 */
	private function _init_schedule($cid,$today=true){
		$sch =D('ClassSchedule')->get_schinfo($cid);
		$sch['course'] = json_decode($sch['course'],true);
		if($today){
			$_sch[]= $sch['course'];
			for ($i = 0; $i <count($_sch[0][strtolower(date("l"))]); $i++) {
				//去除午休
				if($i!=4){
					if($i==count($_sch[0][strtolower(date("l"))])-1){
						$t_sch=$t_sch.$_sch[0][strtolower(date("l"))][$i];
					}else{
						if($_sch[0][strtolower(date("l"))][$i]){
							$t_sch=$t_sch.$_sch[0][strtolower(date("l"))][$i].","." ";
						}
						
					}
					
				};
			}
			$this->assign('sch',$t_sch);	
		}else{
			$this->assign('sch',$sch['course']);
			$this->assign('sch_id',$sch['id']);
		}
	}
	
	/**
	 * 初始化日志
	 * @param  $cid
	 */
	private function _init_list($cid){
		$blog_map['class_id'] = $cid;
		$list =  D('Blog','blog')->getClassBlogList( $blog_map,'*','cTime desc',6 );
		/*日志分类*/
		foreach($list['data'] as $k => $v) {
			$list['data'][$k]['app'] = 'classblog';
			$list['data'][$k]['content'] = t($list['data'][$k]['content']);
			if ( empty($v['category_title']) && !empty($v['category']) ){
				$list['data'][$k]['category_title'] = M('blog_category')->where('id='.$v['category'])->getField('name');
			}
		 	if($v['feed_id']){
				$_map = array();
				$_map['feed_id'] = $v['feed_id'];
				$_map['is_del'] = 0;
				$feed = model('Feed')->getFeeds($_map);
				/* 
				$feed = model('feed')->where($_map)->find(); */
				$list['data'][$k]['feed_data']=$feed['feed_id'];
				unset($_map);
			} 
		}
		$uids = array_unique(getSubByKey($list['data'],'uid'));
		$this->assign('user_info',model('User')->getUserInfoByUids($uids));
		$blogcount = D('Blog','blog')->getTotalCount($cid,true);
		$config= D('AppConfig','blog')->getConfig();
		$this->assign('blogcount',$blogcount);
		$this->assign($config);
		$this->assign( $list );
	}

	/**
	 * 初始化班级信息
	 * @param  $cid
	 */
	private function _init_class($cid,$blocal = false){
		$org = model('CyClass')->get_class_info_by_id($cid);
		
		if(empty($org['id'])){
			$this->error("您访问的班级不存在 ！");
		}
		$org['spaceurl'] = getHomeUrl($org);
		$org['area'] =Model("CyArea")->getFullAreaByOrgnization($org);
		
		$org['tcount'] = Model('CyStatistics')->count_teacher_by_class($org['id']);
		$org['scount'] = Model('CyStatistics')->count_student_by_class($org['id']);
		$org['pcount'] = Model('CyStatistics')->count_parents_by_class($org['id']);
	
		
		$follow = D('ClassData','class')->getClassDataByFid($org['id'],'follower_count');
		$org['follower'] = $follow['value'];
		
		$follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $cid,2 );
		$org['follow_state'] = $follow_state[$cid];

		$this->_classType = $org['type'];
		if($this->_classType == 1){
			$this->_sessionKey = "visit_school";
			$this->_db_key = "sid";
		}
		if($blocal){
			$_org = D('OrgInfo')->get_orginfo($cid,$org['type']);
			unset($_org['id']);
			$org = empty($_org)?$org:array_merge($org,$_org);
		}
		if($cid){
			$org['parent'] = D("CySchool")->get_school_info_by_id($org['schoolId']);
		}
	
		$this->assign('org', $org);
		
	}
	
	/**
	 * 初始化成员信息
	 * @param  $cid
	 */
	private function _init_person($cid){
		$teachers = D('CyUser')->listUserByClass($cid,UserRoleTypeModel::TEACHER,0,10);
		$students = D('CyUser')->listUserByClass($cid,UserRoleTypeModel::STUDENT,0,10);
		$parents = D('CyUser')->listUserByClass($cid,UserRoleTypeModel::PARENTS,0,10);
		$this->assign('teachers', $teachers);
		$this->assign('students', $students);
		$this->assign('parents', $parents);
	}
	
	/**
	 * 初始化粉丝信息
	 * @param  $cid
	 */
	private function _init_follower($cid){

	}
	
	/**
	 * 初始化相册
	 * @param  $cid
	 */
	private function _init_album($cid){
		$map['classId'] = $cid;
		$map['isDel'] = 0;
		$photo_data = D('Album', 'photo')->order("mTime DESC")->where($map)->findPage(20);
		// 所有的图片数目
		$count = 10;
		$this->assign('photo_data', $photo_data);
	}
	
	public function class_manage(){
		$cid = $_GET['cid'];
		$this->_init_tablist();
		
		//班级管理基本资料中班级信息
		$year_list=array('2012','2011','2010','2009','2008');
		$classNumber_list=array('一班','二班','三班','四班','五班');
		$schoolYear_list=array('3','4','5','6','9');
		$section_list=array('幼儿园','小学','初中','高中','职高');
		$classInfo_list=array('year'=>$year_list,'classNum'=>$classNumber_list,'schoolYear'=>$schoolYear_list,'section'=>$section_list);
		
		$this->assign('classInfo',$classInfo_list);
		$this->display();
	}
	
	public function class_avatar(){
		$cid = $_GET['cid'];
		$this->_init_tablist();
		$avatarData['url'] = 'widget/MSAvatar/doSaveClassAvatar';
		$avatarData['widget_appname'] = 'msgroup';
		$avatarData['rowid'] = $cid;
		$avatarData['defaultImg'] = getCampuseAvatar($cid,2,'avatar_big');
		$this->assign('avatarData',$avatarData);
		$this->display();
	}
	
	/**
	 * 班级小明星
	 */
	public function class_star(){
		$cid = $_GET['cid'];
		$this->_init_tablist();
		$this->display('class_manage');
	}
	
	/**
	 * 班级课程表
	 */
	public function class_schedule(){
		$cid = $_GET['cid'];
		$this->_init_schedule($cid,false);
		$this->_init_tablist();
		$this->display();
	}
	
	
	private function _init_tablist(){
		$tab_title = '班级管理';
		$tab_action = 'ClassHome';
		$tab_list[] = array('field_key'=>'class_manage','field_name'=>'基本资料');	// 基本资料
		$tab_list[] = array('field_key'=>'class_avatar','field_name'=>'设置班徽');			// 头像设置
		$tab_list[] = array('field_key'=>'class_star','field_name'=>'设置班级小明星');			// 头像设置
		$tab_list[] = array('field_key'=>'class_schedule','field_name'=>'班级课表');	// 基本资料
		$this->assign('tab_action',$tab_action);
		$this->assign('tab_title',$tab_title);
		$this->assign('tab_list',$tab_list);
	}

	public function showUpload(){
		$restype = isset($_REQUEST['restype'])?$_REQUEST['restype'] : "";
		if(empty($restype)){
			$restype_list = $this->getRestype();
		} else{
			$restype_list = array();
			switch ($restype){
				case '0100':
					$restype_list[] = array("code"=>"0100", "name"=>"教学设计");
					break;
				case '0600':
					$restype_list[] = array("code"=>"0600", "name"=>"教学课件");
					break;
				case '0200':
					$restype_list[] = array("code"=>"0200", "name"=>"课堂实录");
					break;
				case '1300':
					$restype_list[] = array("code"=>"1300", "name"=>"难点解析");
					break;
				case '0300':
					$restype_list[] = array("code"=>"0300", "name"=>"媒体素材");
					break;
				case '0400':
					$restype_list[] = array("code"=>"0400", "name"=>"习题解析");
					break;
				default:
					$restype_list = $this->getRestype();
			}
		}
		$this->assign("cyuid", $GLOBALS['ts']['cyuserdata']['user']['cyuid']);
		$this->assign("res_list", $restype_list);
		$uid = $GLOBALS['ts']['user']['uid'];
		$uname = $GLOBALS['ts']['user']['uname'];
		$cid=$_GET['cid'];
		$this->assign('classid',$cid);
		$this->assign("uid", $uid);
		$this->assign("uname", $uname);
		$this->display();
	}
	/**
	 * 获取资源分类
	 * @author yxxing
	 */
	private function getRestype(){
		$restype = array();
		$restype[] = array("code"=>"0000", "name"=>"请选择");
		$restype[] = array("code"=>"0100", "name"=>"教学设计");
		$restype[] = array("code"=>"0600", "name"=>"教学课件");
		$restype[] = array("code"=>"0200", "name"=>"课堂实录");
		$restype[] = array("code"=>"1300", "name"=>"难点解析");
		$restype[] = array("code"=>"0300", "name"=>"媒体素材");
		$restype[] = array("code"=>"0400", "name"=>"习题解析");
		return $restype;
	}
	

	
	/**
	 * 初始化班级成员信息
	 * @param  $cid
	 */
	public function class_people(){
		$cid = $_GET['cid'];
		$this->_init_person($cid);
		$this->display();
	}
	
	/**
	 * 初始化班级粉丝信息
	 * @param  $cid
	 */
	public function class_follower(){
		$cid = $_GET['cid'];
		$this->_init_person($cid);
		$num = 10;
		$follower_list = D('Follow')->getOrgFollowerList($cid,2,$num);
		
		//分页
		$count= $follower_list['count'];
		$p = new Page ( $count, $num );
		$nowPage=$p->nowPage;
		$p->setConfig('prev',"上一页");
		$p->setConfig('next','下一页');
		$page = $p->show ();
		$this->assign( "page", $page );
		
		$fids = getSubByKey ( $follower_list ['data'], 'fid' );
		if ($fids) {
			$uids = array_merge ( $fids, array ($cid) );
		} else {
			$uids = array ($cid);
		}
		$userGroupData = model ( 'UserGroupLink' )->getUserGroupData ( $uids );
		$this->assign ( 'userGroupData', $userGroupData );
		$user_info = model ( 'User' )->getUserInfoByUids ( $uids );
		$this->assign ( 'user_info', $user_info );
		$user_count = model ( 'UserData' )->getUserDataByUids ( $uids );
		$this->assign ( 'user_count', $user_count );
		$follow_state = model ( 'Follow' )->getFollowStateByFids ( $this->mid, $fids );
		$this->assign ( 'follow_state', $follow_state );
		if ($this->uid == $this->mid) {
			$t = time () - intval ( $GLOBALS ['ts'] ['_userData'] ['view_follower_time'] ); // 避免服务器时间不一致
			model ( 'UserData' )->setUid ( $this->mid )->updateKey ( 'view_follower_time', $t, true );
		}
		$this->assign ( 'follower_list', $follower_list );
		$this->display();
	}
	
	
	/**
	 * 万年历
	 */
	public function calendar(){
		$this->display();
	}
	
	
	public function class_praise(){
		$cid = $_GET['cid'];
		$students = D('CyUser')->listUserByClass($cid,UserRoleTypeModel::STUDENT,0,50);
		$this->assign('studentData',$students);
		$this->display();
	}
	
	/**
	 * 历史上的今天
	 */
	public function today(){
		$date = date( "m"."d");
		$Today = D('Today');
		$today = $Today->getTodayContent($date);
		$soundurl = "http://c.changyan.com/AudioService";
		$this->assign("soundurl",$soundurl);
		$this->assign("today",$today);
		$this->display();
	}
	
	/**
	 * 表扬栏页面
	 */
	public function praise(){
		$condition = 'up.praise_id = p.praise_id and up.classId = 825 ';
		$prasie_list =D('UserPraise','class')->list_praise($condition,$field,$order='up.`ctime` DESC',2);
		$uids = getSubByKey ( $prasie_list ['data'], 'uid' );
		$user_info = model ( 'User' )->getUserInfoByUids ( $uids );
		$this->assign ( 'user_info', $user_info );
		$follow_state = model ( 'Follow' )->getFollowStateByFids ( $this->mid, $uids );
		$this->assign ( 'follow_state', $follow_state );
		$this->assign ( 'prasie_list', $prasie_list );
		$this->display();
	}
}
?>