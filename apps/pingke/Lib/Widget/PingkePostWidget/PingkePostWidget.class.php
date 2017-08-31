<?php
/**
 * 课题研究和网上评课的发言widget{:W('Post',array('rid'=>$rid,'status'=>$data['status'],'isVisitor'=>$isVisitor))}
 * @author xypan
 * @version TS3.0
 */
class PingkePostWidget extends Widget{
	
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
		
		$pingkeId = intval($_POST['rid']);
		
		//评课详细
		$pinkeDetails=D('Pingke')->getPingKeDetails($pingkeId);
		$nowpage = empty($_POST['p']) ? 1 : $_POST['p'];
	    $uid =intval($_POST['uid']);
		// 分页时每页记录数
		$num = 10;
		
		// 实例化PostModel
		$pingkePost = D('PingkePost');
		// 查询出公告总数
		$count =  $pingkePost->getPostListCount($pingkeId,$uid);
		$p = new AjaxPage(array('total_rows'=>$count,
				'method'=>'ajax',
				'ajax_func_name'=>'getPostByAjax',
				'now_page'=>$nowpage,
				'list_rows'=>$num));
		$page = $p->show();
		
		// 发言列表
		$postList = $pingkePost->getPostList($pingkeId, $nowpage, $num,'id desc',$uid);
		$attachInfo = array();
		foreach ($postList AS $post){
			$attachs = $post['attach'];
			foreach ($attachs AS $attach){
				if($attach['attach_id']){
					$attachInfo[$attach['attach_id']] = D("Attach")->getAttachById($attach['attach_id']);
				}
			}
		}
		/*-获取用户对于回复列表是否赞  start-*/
		$postArr =getSubByKey($postList,'id');
		$AgreeList =D('AgreeBehaviour','pingke')->getIsBehaviourUser($postArr,$this->mid);
		/*--------获取end---------*/
		$var['agreeArray'] =$AgreeList;
		$var['attachInfo'] = $attachInfo;
		$var['postList'] = $postList;
		$var['page'] = $page;
		$var['mid']=$this->mid;
		$var['status']=$pinkeDetails['status'];
		
		//渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/postList.html", $var);
		echo $content;
	}
}
?>
