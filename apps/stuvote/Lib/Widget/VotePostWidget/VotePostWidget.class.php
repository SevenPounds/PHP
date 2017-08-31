<?php
/**
 * 课题研究和网上评课的发言widget{:W('VotePost',array('rid'=>$rid,'status'=>$data['status'],'isVisitor'=>$isVisitor))}
 * @version TS3.0
 */
class VotePostWidget extends Widget{
	
	/**
	 * 
	 * @param int $rid 课题或评课的id
	 * @param int $status 课题|评课状态
	 * @param boolean $isVisitor 是否只有浏览权限
	 */
	public function render($data){
		$var['rid'] = $data['rid'];
		$var['status'] = $data['status'];
		$var['isVisitor'] = $data['isVisitor'];
		//渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/postWidget.html", $var);
		
		return $content;
	}
	
	/**
	 * 用ajax方式获取发言列表
	 */
	public function getPost(){
		$voteId = intval($_REQUEST['rid']);
		$nowpage = empty($_REQUEST['p']) ? 1 : $_REQUEST['p'];
	    $uid = intval($_REQUEST['uid']);

		// 分页时每页记录数
		$num = 10;
		$voteDetial = D('Vote')->where("`id`= '{$voteId}'")->find();

		// 实例化PostModel
		$commentModel = D('Comment');

		// 查询出公告总数
		$map = array('table' => 'stuvote', 'row_id' => $voteId);
		if (!empty($uid)) {
			$map['uid'] = $uid;
		}

		$result = $commentModel->getCommentList($map,"comment_id DESC");
		$p = new AjaxPage(array('total_rows'=>$result['count'],
				'method'=>'ajax',
				'ajax_func_name'=>'getPostByAjax',
				'now_page'=>$nowpage,
				'list_rows'=>$num));
		$page = $p->show();

		// 发言列表
		$postList = $result['data'];
		$attachInfo = array();
        // 创建一个 存储所有id的数组
        $pids = array();
		foreach ($postList AS $post){
            array_push($pids,$post['comment_id']);
			$attachs = $post['attach'];
			foreach ($attachs AS $attach){
				if($attach['attach_id']){
					$attachInfo[$attach['attach_id']] = D("Attach")->getAttachById($attach['attach_id']);
				}
			}
		}
        $var['agreeArray'] = D('Agree')->checkIsAgreed($pids,$this->mid);
		$var['attachInfo'] = $attachInfo;
		$var['postList'] = $postList;
		$var['page'] = $page;
		$var['mid']=$this->mid;
        $var['voteId'] = $voteId;
        $var['voteUid'] = $voteDetial['uid'];
		$var['status']=1;

		//渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/postList.html", $var);
		
		echo $content;
	}
}
?>
