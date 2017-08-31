<?php
/**
 * 资讯文章管理Action
 * @author sjzhao
 *
 */
class ArticleAction extends BaseAction {
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
	 * 工作动态
	 */
	public function dynamic() {
		$this->display();
	}

	/**
	 * 研究成果
	 */
	public function research() {
		$this->display();
	}

	/**
	 * 教学论文
	 */
	public function thesis() {
		$this->display();
	}

	/**
	 * 教学日志
	 */
	public function blog() {
		$this->display();
	}
	/**
	 * 资讯文章预览页面
	 */
	public function detail(){
		$id = $_REQUEST['id'];
		$detail = model('MSGroupAnnounce')->GetNotice($id);
        if(empty($detail['announce'])){
            $this->error("您访问的文章不存在！");
        }

		$type = $_REQUEST['type'];
		switch($type){
			case 2:
				$actionname="工作动态";
				$actionurl =U('msgroup/Article/dynamic', array('gid' => $this->gid));
				break;
			case 3:
				$actionname="研究成果";
				$actionurl =U('msgroup/Article/research', array('gid' => $this->gid));
				break;
			case 4:
				$actionname="教学论文";
				$actionurl =U('msgroup/Article/thesis', array('gid' => $this->gid));
				break;
			case 5:
				$actionname="教学日志";
				$actionurl =U('msgroup/Article/blog', array('gid' => $this->gid));
				break;
		}
		$this->assign("actionname",$actionname);
		$this->assign("actionurl",$actionurl);
		$this->assign("detail",$detail);
		$this->display();
	}
	/**
	 * 资讯文章编辑页面
	 */
	public function edit(){
		$id = $_REQUEST['id'];
		$type = $_REQUEST['type'];
		switch ($type){
			case 2:
				$action_name='dynamic';
				break;
			case 3:
				$action_name='research';
				break;
			case 4:
				$action_name='thesis';
				break;
			case 5:
				$action_name='blog';
				break;
		}
		$cancel_url=U('msgroup/Article/'.$action_name,array('gid'=>$this->gid,'type'=>$type));
		$detail = model('MSGroupAnnounce')->GetNotice($id);
		$attachmentIds = array();
		foreach ($detail['attachments'] AS $attachment){
			if(!empty($attachment) && $attachment['attach_id']){
				$attachmentIds[] = $attachment['attach_id'];
			}
		}
		$attachmentIds = implode(array_filter($attachmentIds), ",");
		$this->assign("detail", $detail);
		$this->assign("type", $type);
		$this->assign("cancel_url",$cancel_url);
		$this->assign("attachmentIds", $attachmentIds);
		$this->display();
	}
	/**
	 * 添加资讯文章
	 */
	public function add(){
		$type = $_REQUEST['type'];
		switch($type){
			case 2:
				$actionname="发布动态";
				break;
			case 3:
				$actionname="发布成果";
				break;
			case 4:
				$actionname="发表论文";
				break;
			case 5:
				$actionname="发表日志";
				break;
		}
		$this->assign("actionname",$actionname);
		$this->assign("type",$type);
		$this->display();
	}
}
?>
