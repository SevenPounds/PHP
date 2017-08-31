<?php
class CampusFollowAction extends Action{
	
	private $_follow_model = null;         // 关注模型对象字段
	
	/**
	 * 初始化控制器，实例化关注模型对象
	 */
	protected function _initialize() {
		$this->_follow_model = model('CampusFollow');
	}
	
	/**
	 * 添加关注操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doFollow() {
	
		// 安全过滤
		$fid = t($_POST['fid']);
		$type = t($_GET['ftype']);
		$res = $this->_follow_model->doFollow($this->mid, $fid,intval($type));
		$this->ajaxReturn($res, $this->_follow_model->getError(), false !== $res);
	}
	
	/**
	 * 取消关注操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function unFollow() {
		// 安全过滤
		$fid = t($_POST['fid']);
		$type = t($_GET['ftype']);
		$res = $this->_follow_model->unFollow($this->mid, $fid,intval($type));
		$this->ajaxReturn($res, $this->_follow_model->getError(), false !== $res);
	}
	
}

?>