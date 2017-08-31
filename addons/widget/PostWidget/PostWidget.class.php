<?php
/**
 * 课题研究和网上评课的发言widget{:W('Post',array('rid'=>$rid,'status'=>$data['status'],'isVisitor'=>$isVisitor))}
 * @author xypan
 * @version TS3.0
 */
class PostWidget extends Widget{
	
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
		$rid = intval($_POST['rid']);
		//$uid = intval($_POST['uid']);
		//主题详细
		$reseach=D('Research','research')->getResearchById($rid);
		
		$nowpage = empty($_POST['p'])?1:$_POST['p'];

		// 查询条件
		$condition = array();
		$condition['research_id'] = $rid;
		$condition['is_del'] = 0;
		if(isset($_POST['uid'])){
			$condition['post_userid'] = intval($_POST['uid']);
		}
		// 分页时每页记录数
		$num = 10;
		
		// 实例化PostModel
		$Post = D('Post','research');
		
		// 查询出公告总数
		$count =  $Post->where($condition)->count();
		
		$p = new AjaxPage(array('total_rows'=>$count,
				'method'=>'ajax',
				'ajax_func_name'=>'getPostByAjax',
				'now_page'=>$nowpage,
				'list_rows'=>$num));
		$page = $p->show();
		
		// 发言列表
		$postList = $Post->getPostByRid($condition,$nowpage,$num);
		$postIdList = array();
		foreach ($postList as $key => $val) {
			array_push($postIdList,$val['id']);
			// 查出附件信息
			$attachs = D('Research','research')->getAttachs("post", $val['id']);
			
			if(!empty($attachs)){
				// 把每个发言自带的附件信息放入发言数组中
				$postList[$key]['attach'] = $attachs;
			}
		}
		$var['agreeList'] = D('AgreeBehaviour','research')->getIsBehaviourUser($postIdList,$this->mid);
		$var['postList'] = $postList;

		$var['page'] = $page;
		
		$var['mid']=$this->mid;
		
		$var['status']=$reseach['status'];
		//渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/postList.html", $var);
		
		echo $content;
	}
}
?>
