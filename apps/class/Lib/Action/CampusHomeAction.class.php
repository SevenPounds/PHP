<?php
class CampusHomeAction extends Action{
	
	private $_orgtype = 1;
	private $_sessionKey = "visit_school";
	private $_db_key = "sid";
	/**
	 * 初始化信息
	 */
	public function  _initialize(){
		$cid = $_GET['cid'];
		if($_GET['act']=='school_manage'||$_GET['act']=='school_intro'){
			$this->_init_Campus($cid,true);
		}else{
			$this->_init_Campus($cid);
		}
		$bmember = $this->_checkmember($this->mid,$cid);
		$this->assign('bmember',$bmember);
	}
	
	
	/**
	 * 初始化学校粉丝
	 * @param  $cid
	 */
	public function campus_follower(){
		$cid = $_GET['cid'];
		$this->_init_Campus_Class($cid);
		$this->_init_person($cid);
		
		$follower_list = D('Follow')->getOrgFollowerList($cid,1,10);
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
	 * 判断是否为该学校成员
	 * @param  $uid
	 * @param  $cid
	 * @return inetger 1:学校成员 -1：   非学校成员
	 */
	private function _checkmember($uid,$cid){
		$user = D('User')->getUserInfo($uid);
		$cyuserdata = D('CyUser')->getCyUserInfo($user['login']);
		$orglist = $cyuserdata['orglist'];
		if(!empty($orglist['school'])){
			foreach($orglist['school'] as $org){
				if($org['id']==$cid){
					return 1;
				}
			}
		}
		return -1;
	}
	
	public function school_manage(){
		$this->_init_tablist();
		$this->display();
	}
	
	public function school_intro(){
		$this->display();
	}
	
	public function school_avatar(){
		$cid = $_GET['cid'];
		$this->_init_tablist();
		$avatarData['url'] = 'widget/MSAvatar/doSaveSchoolAvatar';
		$avatarData['widget_appname'] = 'msgroup';
		$avatarData['rowid'] = $cid;
		$avatarData['defaultImg'] = getCampuseAvatar($cid,1,'avatar_big');
		$this->assign('avatarData',$avatarData);
		$this->display();
	}
	
	
	private function _init_tablist(){
		$tab_title = '学校主页管理';
		$tab_action = 'CampusHome';
		$tab_list[] = array('field_key'=>'school_manage','field_name'=>'基本资料');				// 基本资料
		$tab_list[] = array('field_key'=>'school_avatar','field_name'=>'设置校徽');
		$tab_list[] = array('field_key'=>'indexNotice','field_name'=>'设置学校公告');
// 		$tab_list[] = array('field_key'=>'magiclatern','field_name'=>'设置学校幻灯广告');
		$this->assign('tab_action',$tab_action);
		$this->assign('tab_title',$tab_title);
		$this->assign('tab_list',$tab_list);
	}
	
	
	/**
	 * 校园首页
	 */
	public function schoolIndex(){
		$cid = $_GET['cid'];
		// 校园首页显示4条最新公告
		$num = 4;
		$Notice = D('Notice');
		// 公告列表
		$condition['cid'] = $cid;
		$condition['type'] = 2;
		$condition['isDeleted']  = 0;
		$order = 'ctime desc';
		$list = $Notice->getNoticeLists(1,$condition,$num,$order);
		$this->_init_Campus_Class($cid);
		$this->_init_person($cid);
		$this->_visitor($cid, $this->uid);
		$this->cid = $cid;
		$this->list =$list;
		$this->setTitle("学校主页");
		$this->display();
	}
	/**
	 * 记录校园来访
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
			D('ClassData') ->updateKey("visitor_count", 1, true, $cid, $this->_orgtype);
		}
	}
	/**
	 * 初始化校园信息
	 * @param  $cid
	 */
	private function _init_Campus($cid,$blocal = false){
		
		$org = model('CySchool')->get_school_info_by_id($cid);
		
		if(empty($org['id'])){
			$this->error("您访问的学校不存在 ！");
		}
	
		$org['spaceurl'] = getHomeUrl($org);
		$org['area'] = Model('CyArea')->getFullAreaByOrgnization($org);

		$org['tcount'] = Model('CyStatistics')->count_teacher_by_school($org['id']);
		$org['scount'] = Model('CyStatistics')->count_student_by_school($org['id']);
		$org['ccount'] = Model('CyStatistics')->count_class_by_school($org['id']);
		
		$follow = D('ClassData','class')->getClassDataByFid($org['id'],'follower_count',$org['type']);
		$org['follower'] = $follow['value'];
		
		$follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $cid,1 );
		$org['follow_state'] = $follow_state[$cid];
		
		$this->_orgtype = $org['type'];
		if($this->_orgtype == 2){
			$this->_sessionKey = "visit_class";
			$this->_db_key = "cid";
		}
		if($blocal){
			$_org = D('OrgInfo')->get_orginfo($cid,$org['type']);
			unset($_org['id']);
			$org = empty($_org)?$org:($org + $_org);
		}	
		$this->assign('org', $org);
	}
	
	private function _init_Campus_Class($cid,$start = 0,$perpage = 10){
		$classes = D('CyClass')->list_class_by_school($cid,$start,$perpage);
		$fids = getSubByKey($classes,'id');
		$follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $fids,2);
		
		$this->assign('orgnization_follow_state', $follow_state);
		$this->assign('classes', $classes);
	}
	
	private function _init_person($cid){
		$teachers = D('CyUser')->listUserBySchool($cid,'teacher',0,20);
		$this->assign('teachers', $teachers);
	}
	
	
	/**
	 * 师资力量
	 * @author ylzhao
	 */
	public function school_faculty(){
		$cid = $_GET['cid'];
		// 每页显示记录数
		$num = 10;
		$count = count(D('CyUser')->listUserBySchool($cid,'teacher'));
		$p = new Page ( $count, $num );
		$page = $p->show ();
		$nowPage=$p->nowPage;
		$teachers = D('CyUser')->listUserBySchool($cid,'teacher',$nowPage,$num);
		foreach($teachers as $k=>$v){
			$class =D('CyClass')->list_class_by_user($teachers[$k]['cyuid'],true);
			
			$teachers[$k]['class'] = $class;
			unset($class);
			//本地数据库用户角色统一设为老师
			$teachers[$k]['rolename'][9] = '老师';
			//针对本地数据库用户没有定义个性签名和班级的用户的设置
			if(is_null($teachers[$k]['intro'])){
				$teachers[$k]['intro'] = '暂无个性签名';
			}
		}
		$this->assign('count',$count);
		$this->assign('teachers',$teachers);
		$this->assign( "page", $page );
		$this->display();
	}
	
	/**
	 * 班级园地
	 * @author ylzhao
	 */
	public function class_garden(){
		$cid = $_GET['cid'];
		// 每页显示记录数
		$num = 10;
		$count = D('CyStatistics')->count_class_by_school($cid);
		$p = new Page($count, $num);
		$nowPage = $p->nowPage;
		$classes = D('CyClass')->list_class_by_school($cid,$nowPage,$num);
		$page = $p->show ();
		$fids = getSubByKey($classes,'id');
		$follow_state = model('Follow')->getFollowStateByFids ( $this->mid, $fids,2);
		$this->assign('orgnization_follow_state', $follow_state);
		$this->assign('count',$count);
		$this->assign('classes', $classes);
		$this->assign( "page", $page );
		$this->display();
	}
	
	/**
	 * 公告首页
	 */
	public function indexNotice() {
		//获取flag标记，flag为1没有管理权限
		$flag = intval($_GET['flag']);
		
		// 获取学校编号
		$cid = $_GET['cid'];
		
		// 初始化导航栏
		$this->_init_tablist();
		
		// 当前登录用户的id
		$mid = $this->mid;
	
		// 查询条件
		$condition = array();
		$condition['cid'] = $cid;
		
		// 类型为2标识是学校公告
		$condition['type'] = 2;
		$condition['isDeleted']  = 0;
	
		// 获取排序字段
		$order = empty($_GET['order'])?'dt':$_GET['order'];
	
		// 把GET的order值放到模板变量上
		$this->order = $order;
		
		switch($_GET['order']){
			case 'uv':$order = "viewcount asc";break;
			case 'dv':$order = "viewcount desc";break;
			case 'ut':$order = "ctime asc";break;
			default:$order = "ctime desc";
		}
	
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
		$list = $Notice->getNoticeLists($nowPage,$condition,$num,$order);
	
		// 模板变量
		$this->nowpage = $nowPage;
		$this->j = $num * ($nowPage - 1);
		$this->page = $page;
		$this->list = $list;
		$this->cid = $cid;
		
		$this->setTitle('学校公告');
		if($flag){
			$this->display('noticelist');
		}else{
			$this->display();
		}
		
	}
	
	/**
	 * 创建公告
	 */
	public function createNotice() {
		
		// 获取学校编号
		$cid = $_GET['cid'];
		
		$this->cid = $cid;
		$this->display("create");
	}
	
	/**
	 * 把创建的公告存入数据库
	 */
	public function addNotice() {
	
		// 当前登录的用户
		$user = $this->user;
	
		// 把标题和内容等信息存入$data数组
		$data = array();
		$cid = $_POST['cid'];
		$data['title'] = trim(htmlspecialchars($_POST['title']));
		$data['content'] = safe($_POST['content']);
		$data['attach_ids'] = empty($_POST['attach_ids']) ? '' :     $_POST['attach_ids'];
		$data['type'] = 2;
		$data['uid'] = $user['uid'];
		$data['uname'] = $user['uname'];
		$data['cid'] = $cid;
	
		// 实例化NoticeModel
		$Notice = D('Notice');
	
		// 插入数据库
		$res = $Notice->addSchoolNotice($data);
		
		if($res){
			$d = array();
			$d['content'] = '';
			$d['body'] = "@".$user['uname']." 发布了学校公告【".$data['title']."】 &nbsp;";
			$d['class_id'] = $cid;
			$d['source_url'] = U('class/CampusHome/detailNotice', array("id"=>$res, "cid"=>$cid));
			$feed = model('Feed')->put($data['uid'], 'school', 'post', $d);
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
	 * 公告详细页面
	 */
	public function detailNotice() {
	
		$condition = array();
		$condition['id'] = intval($_GET['id']);
		$condition['isDeleted'] = 0;
	
		// 实例化NoticeModel
		$Notice = D('Notice');
		$detail = $Notice->getNoticeDetail($condition);
	
		// 查询出错
		if(!$detail){
			$this->error("公告已删除!");
		}
	
		$attachIds = D('notice_attach')->where("noticeId = {$detail['id']}")->field("attachId")->select();
	
		$ids = array();
	
		// 转换成id的一维数组
		foreach ($attachIds as $attachId){
			foreach($attachId as $value){
				array_push($ids,$value);
			}
		}
	
		// 附件 信息
		$attachs = D("Attach")->getAttachByIds($ids,"attach_id,name");
	
		$mid = $this->mid;
	
		// 如果当前登录的用户不是公告发布人
		if($mid != $detail['uid']){
			$data = array();
			$data['viewcount'] = intval($detail['viewcount']) + 1;
				
			// 浏览次数加1
			$res = $Notice->updateNotice($condition['id'],$data);
				
			if($res){
				$detail['viewcount'] = intval($detail['viewcount']) + 1;
			}
		}
	
		// 模板变量
		$this->detail = $detail;
		$this->attachs = $attachs;
	
		$this->setTitle('教研公告');
		$this->display();
	}
	
	/**
	 * 编辑页面
	 */
	public function editNotice() {
	
		$condition = array();
		$condition['id'] = intval($_GET['id']);
		$condition['isDeleted'] = 0;
	
		// 实例化NoticeModel
		$Notice = D('Notice');
		$detail = $Notice->getNoticeDetail($condition);
	
		// 查询出错
		if(!$detail){
			$this->error("公告已删除!");
		}
	
		$attachIds = D('notice_attach')->where("noticeId = {$detail['id']}")->field("attachId")->select();
	
		$ids = array();
	
		// 转换成id的一维数组
		foreach ($attachIds as $attachId){
			foreach($attachId as $value){
				array_push($ids,$value);
			}
		}
	
		// 把数组变成以逗号做间隔的字符串
		$ids = implode(",",$ids);
	
		// 模板变量
		$this->detail = $detail;
		$this->ids = $ids;
	
		$this->display("edit");
	}
	
	/**
	 * 把编辑后的信息存入数据库
	 */
	public function alterNotice() {
	
		$id = intval($_POST['id']);
	
		// 把标题和内容等信息存入$data数组
		$data = array();
		$data['title'] = trim(htmlspecialchars($_POST['title']));
		$data['content'] = safe($_POST['content']);
		$data['attach_ids'] = empty($_POST['attach_ids']) ? '' : $_POST['attach_ids'];
	
		// 实例化NoticeModel
		$Notice = D('Notice');
	
		// 插入数据库
		$res = $Notice->updateNotice($id,$data);
	
		if($res){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
	 * 删除公告
	 */
	public function deleteNotice() {
		$id = intval($_POST['id']);
	
		// 实例化NoticeModel
		$Notice = D('Notice');
	
		// 插入数据库
		$res = $Notice->deleteNotice($id);
	
		if($res){
			echo 1;
		}else{
			echo 0;
		}
	}
}
?>
