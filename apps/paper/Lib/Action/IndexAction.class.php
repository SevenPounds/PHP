<?php
require_once './apps/paper/Common/appInfo.php';
require_once './apps/paper/Common/privacyInfo.php';

/**
 * 控制器类
 * @author hhshi
 * 创建日期：2013.9.11
 * 教学论文、教学反思、我的收获应用控制器
 */
class IndexAction extends AccessAction{
	private $paper;
	private $paperAttach;
	public function __construct() {
		parent::__construct();
		
		$this->paper = D("Paper","paper");
		$this->paperAttach = D("PaperAttach","paper");
		$this->assign('mid', $this->mid);
	}
	
	/**
	 * 教学论文首页
	 */
	public function index(){
		$uid = !empty($_GET['uid'])?$_GET['uid']:$this->uid;
		$condition['private'] = $this->getPrivacyCondition();
		$type = !empty($_GET['type'])?$_GET['type']:appInfo::Paper;
		$condition['type'] = $type;
		switch ($type){
			case 1:
				$icon_url="__APP__/images/jxlw.png";
				break;
			case 2:
				$icon_url="__APP__/images/jxfs.png";
				break;
			case 4:
				$icon_url="__APP__/images/jcjs.png";
				break;
			case 6:
				$icon_url="__APP__/images/kbjd.png";
				break;
			case 7:
				$icon_url="__APP__/images/jxsj.png";
				break;
			case 8:
				$icon_url="__APP__/images/jxlw.png";
				break;
			case 9:
				$icon_url="__APP__/images/jxkj.png";
				break;
			case 10:
				$icon_url="__APP__/images/kszd.png";
				break;
		}
		// 设置分页信息
		$current_page = isset($_GET['p'])?intval($_GET['p']):1;
		$pagesize = 10;
		import("@.ORG.Page");
		
		// 分页查询方式
		$condition['pagesize'] = $pagesize;
		$condition['current_page'] = $current_page;
		$page_conditon = $current_page.",".$pagesize;
		$condition['page'] = $page_conditon;
		$order = empty($_GET['order'])?'dt':$_GET['order'];
		// 把GET的order值放到模板变量上
		$this->order = $order;
		switch($_GET['order']){
			case 'uv':
				$order = "readCount asc";
				break;
			case 'dv':
				$order = "readCount desc";
				break;
			case 'ut':
				$order = "cTime asc";
				break;
			default:
				$order = "cTime desc";
		}
		$show = $this->paper->selectPapersByPage($uid, $condition,$order);
		$total = $this->paper->getPaperCount($uid, $condition);
		// 查询成功设置分页，反之设置为空
		if($show){
			$p = new Page ($total, $pagesize);
			$p->setConfig('prev',"上一页");
			$p->setConfig('next','下一页');
			$page = $p->show();
			$nowPage = $p->nowPage;
		}else{
			$show = null;
		}
		
		// 获取对应文章的用户名
		for($i = 0; $i < count($show); $i++){
			$userid = $show[$i]['uid'];
			$user_info = model ('User')->getUserInfo ($userid);
			$show[$i]['uname'] = $user_info['uname'];
			$show[$i]['content'] = str_replace("&nbsp;","",$show[$i]['content']);
		}
		$isself=$this->uid==$this->mid;//判断是不是自己的空间
		$this->assign("page",$page);
		$this->assign("nav", getAppName($type));
		$this->assign("category", getAppCategory($type));
		$this->assign("papers", $show);
		$this->assign("type", $type);
		$this->assign("icon_url",$icon_url);
		$this->assign("uid", $uid);
		$this->assign("isself",$isself);
		$this->j = $pagesize * ($nowPage - 1);
		$this->display();
	}
	
	/**
	 * 发表新文章页面
	 */
	public function publish(){
		$type = !empty($_GET['type'])?$_GET['type']:appInfo::Paper;

		$this->assign("nav", getAppName($type));
		$this->assign("category", getAppCategory($type));
		$this->assign("type", $type);
		$this->display();
	}

	/**
	 * 论文预览页面
	 */
	public function preview(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		
		// 获取文章信息
		$paper = $this->paper->selectPaperByIdAndUid($id, $uid);
		if(count($paper) <= 0){
			$this->error("文章不存在！");
		}
		$paper = $paper[0];
		$this->getAttachInfo($id, $paper);
		
		// 非本人查看，浏览加一
		if($this->uid != $this->mid){
			$read = $this->paper->viewCountAdd($this->uid, $id);
		}
		
		//记录来访者
        recordVisitor($uid, $this->mid);
		
		// 获取查询条件
		$type = $paper['category'];
		$condition['category'] = $type;
		$condition['cTime'] = $paper['cTime'];
		$condition['private'] = $this->getPrivacyCondition();
		
		// 获取当前文章的上一篇和下一篇
		$nextPaper = $this->paper->selectNextPaper($uid, $condition);
		$prevPaper = $this->paper->selectPrevPaper($uid, $condition);

		// 判断是否是自己空间，不是，则进行查看权限判断
		if($this->uid != $this->mid){
			// 获取相互关注的状态
			$followState = model('Follow')->getFollowState($this->mid, $this->uid);
			if($followState['follower'] != 1){
				$paper == null;
			}
		}
		$this->assign("id", $id);
		$this->assign("uid", $uid);
		$this->assign("type", $type);
		$this->assign("nav", getAppName($type));
		$this->assign("category", getAppCategory($type));
		$this->assign("nextPaper", $nextPaper[0]);
		$this->assign("prevPaper", $prevPaper[0]);
		$this->assign("paper", $paper);
		$this->display();
	}
	
	/**
	 * 编辑论文页面
	 */
	public function edit(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		
		// 获取文章信息
		$paper = $this->paper->selectPaperByIdAndUid($id, $uid);
		$type = $paper[0]['category'];
		if(count($paper) <= 0){
			$this->error("文章不存在！");
		}
		$paper = $paper[0];
		$this->getAttachInfo($id, $paper);
		
		$this->assign("id", $id);
		$this->assign("uid", $uid);
		$this->assign("type", $type);
		$this->assign("paper", $paper);
		$this->assign("nav", getAppName($type));
		$this->assign("category", getAppCategory($type));
		$this->display();
	}
	
	private function getAttachInfo($id,&$paper){
		$attachments = $this->paperAttach->selectAttachByPaperID($id);
		foreach ($attachments as $attachment){
			$id = $attachment["id"];
			$attachtype = $attachment["attach_type"];
			if($attachment["attach_type"] == "0"){
				$attachid = $attachment["attach_id"];
				$attachinfo = D("Attach")->getAttachById($attachid);
				$title = $attachinfo["name"];
				$downloadurl = U("widget/Upload/down", array("attach_id"=>$attachid));
				$extension = $attachinfo['extension'];
			}else if($attachment["attach_type"] == "1"){
				$attachid = $attachment["attach_id"];
				$attachinfo = D("Resource")->where("rid='".$attachid."'")->find();
				$title = $attachinfo["title"].".".$attachinfo["suffix"];
				$downloadurl = U('reslib/Ajax/downloadResource')."&rid=".$attachinfo["rid"]."&filename=".$title;
				$extension = $attachinfo['suffix'];
			}
			$paper["attachment"][] = array("id"=>$id,"attachid"=>$attachid,"title"=>$title,"downloadurl"=>$downloadurl, "attachtype"=>$attachtype,'extension'=>$extension);
		}
	}
	
	/*
	 * 删除文章附件(适用于编辑文章/删除文章)
	 */
	public function deleteAttach(){
		$restype = $_REQUEST["uploadtype"];
		$attachid = $_REQUEST["rid"];
		$return = "0";
		$deletetype = $_REQUEST["deletetype"];
		if(isset($deletetype) && $deletetype == "upload"){
			if($restype == 0){
				$return = D("Attach")->doEditAttach($attachid,"deleteAttach","");
				if($return["status"] == "1"){
					$return =  "1";
				}
			}
			$return = "1";
			echo $return;
			return;
		}
		$id = $_REQUEST["id"];
		//删除paper_attach中的记录
		if(D("PaperAttach","paper")->deleteAttachByID($id)){
			//普通附件，删除attach表中的记录
			if($restype == 0){
				$return = D("Attach")->doEditAttach($attachid,"deleteAttach","");
				if($return["status"] == "1"){
					$return =  "1";
				}
			}
			$return = "1";
		}
		echo $return;
	}

	/**
	 * 提交新增论文
	 */
	public function submitAddPaper(){
		$title = htmlentities($_POST['title']);
		$content = $_POST['content'];
		$private = $_POST['privacyid'];
		$attachments = $_POST['attachments'];
		$paper = array();
		$paper['uid'] = $this->mid;
		$paper['title'] = $title;
		$paper['private'] = $private;
		$paper['cTime'] = time();
		$paper['mTime'] = time();
		$paper['category'] = $_POST['type'];
		$paper['content'] = $content;
		
		
		// 获取论文中的图片信息
		$images = matchImages($paper['content']);
		$images[0] && $paper['cover'] = $images[0];
		
		// 保存新的论文信息并获取生成的id
		$paperId = $this->paper->insertPaper($paper);
		if($paperId){
			//添加到paper_attach表中
			foreach($attachments as $attachment){
				D("PaperAttach","paper")->addAttach($paperId,$attachment[0],$attachment[2]);
			}
			$content = text(html_entity_decode(h(t($_POST['content']))));

			$feed_id = $this->paper->syncToFeed($this->mid, $paperId, $title, $content, $paper['category']);
			$paper['id'] = $paperId;
			$paper['feed_id'] = $feed_id;
			$result = $this->paper->updatePaperById($paper);
			
			// 仅自己可见时，设置不显示动态
			if($paper['private'] == privacyInfo::Oneself){
				model('Feed')->setFeedDeleteState($feed_id, 1);
			}
			
			echo $this->checkResult($paperId, "发表成功！", "发表失败！");
		}
	}
	
	/**
	 * 提交编辑论文
	 */
	public function submitEditPaper(){
		$id = $_POST['id'];
		$uid = $_POST['uid'];
		$title = $_POST['title'];
		$content = $_POST['content'];
		$friendid = $_POST['friendid'];
		$private = $_POST['privacyid'];
		$feed_id = $_POST['feed_id'];
		$attachments = $_POST['attachments'];
		
		$paper = array();
		$paper['id'] = $id;
		$paper['uid'] = $uid;
		$paper['title'] = $title;
		$paper['content'] = $content;
		$paper['private'] = $private;

		// 获取论文中的图片信息
		$images = matchImages($paper['content']);
		$images[0] && $paper['cover'] = $images[0];
		
		if($uid == $this->mid){
			//添加到paper_attach表中
			D("PaperAttach","paper")->where("paper_id='".$id."'")->delete();
			foreach($attachments as $attachment){
				D("PaperAttach","paper")->addAttach($id,$attachment[0],$attachment[2]);
			}
			// 保存新的论文信息
			$result = $this->paper->updatePaperById($paper);
			// 除自己可见外，更改动态为可见状态
			if( $private != privacyInfo::Oneself){
				model('Feed')->setFeedDeleteState($feed_id, 0);
			}else{
				model('Feed')->setFeedDeleteState($feed_id, 1);
			}
			echo $this->checkResult($result, "编辑成功！",  "编辑失败！");
		}else{
			echo "{'statuscode':'400', 'data': '非法操作！'}";
		}
	}
	
	/**
	 * 提交删除论文事件
	 */
	public function submitDeletePaper(){
		$id = $_POST['id'];
		$uid = $_POST['uid'];

		if($uid == $this->mid){
			$result = $this->paper->deletePaperById($id, $uid);
			//获取附件列表并删除
			$deletestatus = 0;
			$attachments = D("PaperAttach","paper")->selectAttachByPaperID($id);
			if(count($attachments) == 0){
				$deletestatus = 1;
			}
			foreach($attachments as $attachment){
				if(D("PaperAttach","paper")->deleteAttachByID($attachment["id"])){
					//普通附件，删除attach表中的记录
					if($attachment["attach_type"] == 0){
						$return = D("Attach")->doEditAttach($attachment["attach_id"],"deleteAttach","");
						if($return["status"] == "1"){
							$deletestatus = 1;
						}
					}
					$deletestatus = 1;
				}
			}
			echo $this->checkResult($result && $deletestatus, "删除成功！", "删除失败！");
		}else{
			echo "{'statuscode':'400', 'data': '非法操作！'}";
		}
	}
	
	/**
	 * 获取隐私设置弹框
	 */
	public function privacysettings(){
		$id = $_GET['id'];
		$uid = $_GET['uid'];
		
		$paper = $this->paper->selectPaperByIdAndUid($id, $uid);
		
		$this->assign("privacy",$paper[0]['private']);
		$this->display();
	}
	
	/**
	 * 提交隐私设置
	 */
	public function submitPrivacySet(){
		$code = $_POST['code'];
		$id = $_POST['id'];
		$uid = $_POST['uid'];
		$feed_id = $_POST['feed_id']; 
		
		// 除自己可见外，更改动态为可见状态
		if( $code != privacyInfo::Oneself){
			model('Feed')->setFeedDeleteState($feed_id, 0);
		}else{
			model('Feed')->setFeedDeleteState($feed_id, 1);
		}
		
		if($uid == $this->mid){
			$result = $this->paper->updatePaperPrivacyById($id, $uid, $code);
			echo $this->checkResult($result, "设置成功！", "设置失败！");
		}else{
			echo "{'statuscode':'400', 'data': '非法操作！'}";
		}
	}
	
	/**
	 * 获取隐私状态查询条件
	 */
	private function getPrivacyCondition(){
		$uid = $_REQUEST['uid'];
		// 在他人空间
		if($this->mid != $this->uid){
			// 获取相互关注的状态
			$followState = model('Follow')->getFollowState($uid, $this->mid);
			if($followState['follower'] == 1){
				$condition = array(privacyInfo::All, privacyInfo::Friend);
			}
		}else{	// 自己空间，获取查询所有的权限
			$condition = array(privacyInfo::All, privacyInfo::Friend, privacyInfo::Oneself);
		}
		
		return $condition;
	}
	
	/**
	 * 检查数据操作返回结果
	 * @param int $result 数据操作影响行数或自动生成的id
	 * @param string $success 操作成功返回的信息
	 * @param string $error 操作失败返回的信息
	 * return string {'statuscode': value, 'data': message}
	 * statucode为返回后台作为判断的编码，data为返回信息
	 */
	private function checkResult($result, $success, $error){
		return "{'statuscode':'200', 'data': '$success' , 'result': '$result'}";
	}
}
?>