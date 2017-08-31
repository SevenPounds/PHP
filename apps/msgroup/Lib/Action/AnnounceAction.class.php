<?php
/**
 * 通知公告Action
 * @author sjzhao
 *
 */
class AnnounceAction extends BaseAction {
	/**
     * 初始化
     */
	public function _initialize() {
		parent::_initialize();

		// 权限判断
		if ($this->level == 0) {
			$this->error("当前用户没有权限");
		}
	}

	/**
	 * 通知公告首页
	 */
	public function index(){
		$this->display();
	}
	/**
	 * 通知公告添加页面
	 */
	public function add(){
		$this->display();
	}
	/**
	 * 资讯文章预览页面
	 */
	public function detail(){
		$gid = $_REQUEST['gid'];
		$id = $_REQUEST['id'];
		$detail = model('MSGroupAnnounce')->GetNotice($id);
        if(empty($detail['announce'])){
            $this->error("您访问的文章不存在！");
        }

		$this->assign("detail",$detail);
		$this->display();
	}
	/**
	 * 资讯文章编辑页面
	 */
	public function edit(){
		$id = $_REQUEST['id'];
		$type = $_REQUEST['type'];
		$detail = model('MSGroupAnnounce')->GetNotice($id);
		$attachmentIds = array();
		foreach ($detail['attachments'] AS $attachment){
			if(!empty($attachment) && $attachment['attach_id']){
				$attachmentIds[] = $attachment['attach_id'];
			}
		}
		$attachmentIds = implode(array_filter($attachmentIds), ",");
		$cancel_url=U('msgroup/Announce/index',array('gid'=>$this->gid,'type'=>$type));
		$this->assign("detail", $detail);
		$this->assign("cancel_url",$cancel_url);
		$this->assign("attachmentIds", $attachmentIds);
		$this->display();
	}
}
?>
