<?php
/**
 * @author yuliu2@iflytek.com
 * 名师工作室首页Action
 */
class IndexAction extends BaseAction {
	/**
	 * 名师工作室首页
	 */
	public function index()	{
		// 增加访问数
		$res = D('MSGroupData')->updateKey('visitor_count', 1, true, $this->gid);

		//通知公告
		$Notice = $this->getAnnouncesList($this->gid, 1, 5);
		$this->assign('Notice', $Notice);

		//工作动态
		$Dynamic_work = $this->getAnnouncesList($this->gid,2,5);
		$this->assign('Dynamic_work',$Dynamic_work);

		//研究成果
		$Research_findings = $this->getAnnouncesList($this->gid, 3, 5);
		$this->assign('Research_findings', $Research_findings);
        if(strtolower(C('PRODUCT_CODE'))!='anhui'){//安徽项目去掉教学论文和教学日志
        	//教学论文
        	$Teaching_paper = $this->getAnnouncesList($this->gid, 4, 5);
        	$this->assign('Teaching_paper', $Teaching_paper);
        	
        	//教学日志
        	$Teaching_journal=$this->getAnnouncesList($this->gid,5,5);
        	$this->assign('Teaching_journal',$Teaching_journal);
        }
		//在线答疑
		$Onlineanswer=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'onlineanswer_question');
		$this->assign('Onlineanswer',$Onlineanswer['data']);

		//主题讨论
		$Research=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'research');
		$this->assign('Research',$Research['data']);

		//网上评课
		$Pingke = D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'pingke');
		$this->assign('Pingke',$Pingke['data']);

		//网络调研
		$Vote= D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'vote');
		$this->assign('Vote',$Vote['data']);

		//最新动态
		$this->_getLaseFeeds($this->gid);

		//荣誉室图片总数
		$photoCount = D('MSGroupPhoto')->where("`gid` = '{$this->gid}' and `is_deleted`= '0' ")->count();
		$this->assign('photoCount',$photoCount);

		//荣誉室数据
		$Photo= D('MSGroupPhoto')->where("`gid` = '{$this->gid}'  and `is_deleted`= '0' ")->order("upload_time DESC")->limit("0,4")->select();
		$this->assign('photo',$Photo);

		//教学设计
		$TeaDesign = $this->getPageMSGroupRes($this->gid,'','0100','dateline','desc',5);
		$this->assign("TeaDesign",$TeaDesign['data']);

		//教学课件
		$TeaWare = $this->getPageMSGroupRes($this->gid,'','0600','dateline','desc',5);
		$this->assign("TeaWare",$TeaWare['data']);

		//教学素材
		$TeaMea = $this->getPageMSGroupRes($this->gid,'','0300','dateline','desc',5);
		$this->assign("TeaMea",$TeaMea['data']);

		$this->display();
	}
	/**
	 * 获取名师工作室最新动态
	 */
	private function _getLaseFeeds($gid){
		$lastFeeds = D("Feed")->getFeedByGid($gid);
		$lastFeeds = $lastFeeds['data'];
		//将url前的文字截取，然后转换为html方式展现
		foreach($lastFeeds AS &$feed){
			$feed['feed_content'] = str_replace('&nbsp;', " ", $feed['feed_content']);
			$feed['feed_content'] = str_replace('[SITE_URL]', SITE_URL, htmlspecialchars_decode($feed['feed_content']));
			$feed['feed_content'] = str_replace('[PREVIEW_SITE_URL]', PREVIEW_SITE_URL, htmlspecialchars_decode($feed['feed_content']));
			$feed['feed_content'] = str_replace('@'.$feed['user']['uname'].' ', '', $feed['feed_content']);
			$feed_content = $feed['feed_content'];
			$str_len = mb_strlen($feed_content, "UTF-8");
			$position = mb_strpos($feed_content, "http", null, "UTF-8");
			if($position){
				$feed_txt_content = mb_substr($feed_content, 0, $position, "UTF-8");
				$feed_url_content = mb_substr($feed_content, $position, $str_len - $position, "UTF-8");
			} else{
				$feed_txt_content = $feed_content;
				$feed_url_content = "";
			}
			$feed['feed_content'] = parse_html(replaceUrl(mStr($feed_txt_content, 30, 'utf-8', true).$feed_url_content));
		}
		$this->assign("lastFeeds", $lastFeeds);
	}
	/**
	 * 获取通知公告及资讯文章列表
	 */
	private function getAnnouncesList($gid, $type, $limit, $orderBy = 'ctime desc', $page=1){
		$announceList = D('MSGroupAnnounce')->GetNoticesByGID($gid, $type, $limit, $orderBy, $page);
		return $announceList;
	}
	
	/**
	 * 获取通知公告详情
	 * 
	 */
	public function announcelist(){
		$gid = $_GET["gid"];
		$type = 1;
		$retval = $this->_get_ms_affairs($gid, $type);
		$this->assign("notices",$retval["affairs"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function subjectresearch(){
		$gid = $_GET["gid"];
		$this->affairs = D('MSGroupAnnounce')->GetNoticesByGID($gid, 2, 5);
		$this->achievements = D('MSGroupAnnounce')->GetNoticesByGID($gid, 3, 5);
		$this->display();
	}
	
	public function affairlist(){
		$gid = $_GET["gid"];
		$type = 2;
		$retval = $this->_get_ms_affairs($gid, $type);
		$this->assign("notices",$retval["affairs"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function achievementlist(){
		$gid = $_GET["gid"];
		$type = 3;
		$retval = $this->_get_ms_affairs($gid, $type);
		$this->assign("notices",$retval["affairs"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function teachingresearch(){
		$gid = $_GET["gid"];
		if(strtolower(C('PRODUCT_CODE'))!='anhui'){//安徽项目去掉教学论文和教学日志
			//教学论文
			$Teaching_paper=$this->getAnnouncesList($this->gid,4,5);
			$this->assign('Teaching_paper',$Teaching_paper);
			//教学日志
			$Teaching_journal=$this->getAnnouncesList($this->gid,5,5);
			$this->assign('Teaching_journal',$Teaching_journal);
		}
		//在线答疑
		$Onlineanswer=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'onlineanswer_question');
		$this->assign('Onlineanswer',$Onlineanswer['data']);
		//主题讨论
		$Research=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'research');
		$this->assign('Research',$Research['data']);
		//网上评课
		$Pingke = D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'pingke');
		$this->assign('Pingke',$Pingke['data']);
		//网络调研
		$Vote= D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'vote');
		$this->assign('Vote',$Vote['data']);
		$this->display();
	}
	
	public function essaylist(){
		$gid = $_GET["gid"];
		$type = 4;
		$retval = $this->_get_ms_affairs($gid, $type);
		$this->assign("notices",$retval["affairs"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function journallist(){
		$gid = $_GET["gid"];
		$type = 5;
		$retval = $this->_get_ms_affairs($gid, $type);
		$this->assign("notices",$retval["affairs"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function researchlist(){
		$gid = $_GET["gid"];
		$limit = 10;
		$Research=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'research',$limit);
		$count  = $Research["count"];
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		$this->assign("notices",$Research['data']);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function onlinelist(){
		$gid = $_GET["gid"];
		$limit = 10;
		$Onlineanswer=D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'onlineanswer_question',$limit);
		$count  = $Onlineanswer["count"];
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		$this->assign("notices",$Onlineanswer['data']);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function pingkelist(){
		$gid = $_GET["gid"];
		$limit = 10;
		$Pingke = D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'pingke',$limit);
		$count  = $Pingke["count"];
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		$this->assign("notices",$Pingke['data']);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function votelist(){
		$gid = $_GET["gid"];
		$limit = 10;
		$Vote= D('MSGroupTeachingApp')->getTeachingAppInfo($this->gid,'vote',$limit);
		$count  = $Vote["count"];
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		$this->assign("notices",$Vote['data']);
		$this->assign("page",$page);
		$this->display();
	}
	
	public function teachingresource(){
		$gid = $_GET["gid"];
		$designretval = $this->_get_ms_resources($gid, "0100", 5);
		$this->assign("design_resources",$designretval["resources"]);
		$wareretval = $this->_get_ms_resources($gid, "0600", 5);
		$this->assign("ware_resources",$wareretval["resources"]);
		$materialretval = $this->_get_ms_resources($gid, "0300", 5);
		$this->assign("material_resources",$materialretval["resources"]);
		$this->display();
	}
	
	public function designlist(){
		$gid = $_GET["gid"];
		$retval = $this->_get_ms_resources($gid, "0100");
		$this->assign("resources",$retval["resources"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function warelist(){
		$gid = $_GET["gid"];
		$retval = $this->_get_ms_resources($gid, "0600");
		$this->assign("resources",$retval["resources"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	public function materiallist(){
		$gid = $_GET["gid"];
		$retval = $this->_get_ms_resources($gid, "0300");
		$this->assign("resources",$retval["resources"]);
		$this->assign("page",$retval["page"]);
		$this->display();
	}
	
	private function _get_ms_affairs($gid, $type){
		$limit = 10;
		$page = isset($_GET["p"])?$_GET["p"]:"1";
		$affairs = D('MSGroupAnnounce')->GetNoticesByGID($gid, $type, $limit, 'ctime desc', $page);
		$count = D('MSGroupAnnounce')->GetCountByGID($gid, $type);
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		return array("affairs"=>$affairs, "page"=>$page);
	}
	
	private function _get_ms_resources($gid, $type,$limit = 10){
		$page = isset($_GET["p"])?$_GET["p"]:"1";
		$skip = $limit * (intval($page) - 1);
		$conditions = array(
				'operationtype' => 10,
				'keywords'=>"",
				'restype'=>$type
		);
		$resources = D('ResourceOperation','reslib')->getMSGroupRes($gid,$conditions,'dateline', 'desc', $skip, $limit);
		$count = D('ResourceOperation','reslib')->getMSGroupResCount($gid,$conditions);
		$p = new Page ( $count, $limit );
		$page = $p->show ();
		return array("resources"=>$resources, "page"=>$page);
	}

	/**
	 * 获取工作室成员
	 */
	public function getMsGroupMembers(){
		$gid = $_REQUEST['gid'];
		$pagenum= isset($_REQUEST['p'])?$_REQUEST['p']:1;
		$pagesize = 6;
		if(empty($gid)){
			$this->ajaxReturn('','参数异常',0);
		}
		
		$start =  ($pagenum-1)*$pagesize;
		$limit = "{$start},{$pagesize}";

		$count = D('MSGroupMember')->getMemberListCount($gid, null, null,false);
		$res = D('MSGroupMember')->getMemberList($gid, null, null,null,($pagenum-1)*$pagesize,$pagesize,'',false);
		
		$uids = getSubByKey($res,"uid");
		$user_info = model ( 'User' )->getUserInfoByUids ( $uids );
		
		$data ['total_rows'] = $count;
		$data ['list_rows'] = $pagesize;
		$data ['ajax_func_name'] = "getMsgroupMembers";
		$data ['method'] = "ajax";
		$data ['now_page'] = $pagenum;
		$data ['parameter'] = $gid;
		
		$ajaxpage = new AjaxPage($data);
		$page = $ajaxpage->simpleShow();
		$totalpage = ceil ( $count / $pagesize );
		$pagenum == 1 ? $pre_page = false : $pre_page = true;
		$pagenum < $totalpage ? $next_page = true : $next_page = false;
		
		$members['html'] =$page;
		$members['data'] = $res;
		$members['page'] = $pagenum;
		$members['gid'] = $gid;
		$members['user_info'] = $user_info;
		
		$members['pre_page'] = $pre_page;
		$members['next_page'] = $next_page;
		
		$members['creator'] = D('MSGroupMember')->getCreatorByGid($gid);
		
		$content = fetch('_memberlist',$members);
		unset($members);
		$this->ajaxReturn($content,'信息获取成功',1);
	}
	
	/**
	 * 通知公告、工作动态、研究成果、教学论文和教学日志详细预览页面
	 */
	public function announceDetail(){
		$announceId = $_REQUEST['announce_id'];
		$announce = D("MSGroupAnnounce")->GetNotice($announceId);
		if(empty($announceId) || empty($announce['announce'])){
			$this->error("您访问的文章不存在!");
		}

		D("MSGroupAnnounce")->AddViewCount($announceId);
		$this->assign('announce', $announce['announce']);
		$this->assign('announceType', $announce['announce']['type']);
		$this->assign('attachIds', $announce['attachments']);
		$this->display("announce_detail");
	}

	/**
	 * 荣誉图片详细预览页面
	 */
	public function photoPreview() {
		$gid = $_REQUEST['gid'];
		$photoId = $_REQUEST['pid'];

		if (empty($gid) || empty($photoId)) {
			$this->error("参数异常");
		}

		$map['gid'] = $gid;
		$map['id'] = $photoId;

		$photo = D('MSGroupPhoto')->where($map)->find();
		if(empty($photo) || $photo['is_deleted']==1){
			$this->error("该荣誉已被删除或不存在");
		}

		// 更新浏览量
		D('MSGroupPhoto')->updateMSGPhotoViewCount($photoId,1);

		$this->assign('photo',$photo);
		$this->display('photo_preview');
	}

	/**
	 * @api 查询名师工作室资源
	 * @author yangli4
	 *
	 * @param string $groupID   工作室id
	 * @param string $resTitle  资源名称
	 * @param string $resType   资源类型编号
	 * @param string $orderby   排序字段
	 * @param string $sort      排序方向
	 * @param int    $pageSize  每页显示数量
	 * @return array 返回mysql数据库资源记录数组
	 */
	private function getPageMSGroupRes($groupID,$resTitle,$resType,$orderby = 'dateline', $sort = 'desc', $pageSize = 10 ){
		$conditions = array(
				'operationtype' => 10,
				'keywords'=>$resTitle,
				'restype'=>$resType
		);
		return D('ResourceOperation','reslib')->getPageMSGroupRes($groupID,$conditions,$orderby, $sort, $pageSize);
	}
}

?>
