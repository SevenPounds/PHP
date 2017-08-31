<?php
/**
 * @author yuliu2@iflytek.com
 * 名师工作室BaseAction
 */
class BaseAction extends Action {
	// 工作室id
	var $gid = null;

	// 当前用户在工作室里的角色
	// 0:非成员 1:普通成员 2:管理员 3:创建者
	var $level = 0;

	/**
	 * 初始化工作
	 */
	public function _initialize() {
		// 获取gid
		if (empty($_REQUEST['gid'])) {
			$this->error('您还未加入任何工作室，请联系管理员！');
		}
		
		// 检查资讯id是否都为数字，非法参数跳回列表页
		if(!preg_match("/^\d*$/", $_REQUEST['gid'])){
			$gs = D('MSGroup', 'msgroup')->getMsGroupByUid($this->mid);
			$this->redirect('/Index/index',array('gid'=>$gs[0]['gid']));
		}

		$this->gid = $_REQUEST['gid'];
		$msgroup_data = D('MSGroup')->getMsGroupById($this->gid);
		$this->level = D('MSGroupMember')->getLevel($this->gid, $this->mid);
		
		$follow_state = D('Follow')->getFollowState($this->mid,$this->gid,3);
		
		$this->assign('follow_state', $follow_state['following']);
		
		$this->assign('mid', intval($this->mid));
		$this->assign('gid', intval($this->gid));
		$this->assign('level', intval($this->level));
		$this->assign('msgroup_data', $msgroup_data);
	}
}
?>
