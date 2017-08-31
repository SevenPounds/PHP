<?php
/**
 * 名师工作室基础信息管理Action
 * @author sjzhao
 *
 */
class ConfigAction extends BaseAction{
	public function _initialize() {
		parent::_initialize();

		// 权限判断
		if ($this->level != 3) {
			$this->error("当前用户没有权限");
		}
	}


	/**
	 * 基础信息管理首页
	 */
	public function index(){
		$app = t($_GET['app']);

		$dAvatar = D('MSAvatar');
		$params['app'] = 'msgroup';
		$params['rowid'] = intval($this->gid);
		$dAvatar->init($params); 			// 初始化Model用户id
		$avatar = $dAvatar->getAvatar();

		$avatarData = array();
		$avatarData['url'] = 'widget/MSAvatar/doSaveMsGroupAvatar';
		$avatarData['widget_appname'] = 'msgroup';
		$avatarData['rowid'] = $this->gid;
		$avatarData['defaultImg'] = $avatar['avatar_original'];

		// 学科列表
		$this->assign('subjects', D('Node')->subjects);

		$this->avatarData = $avatarData;
		$this->display();
	}
	/**
	 * 保存工作室的头像设置操作
	 * @return json 返回操作后的JSON信息数据
	 */
	public function doSaveAvatar() {
		$app = t($_GET['app']);
		$dAvatar = model('Avatar',array('app'=>$app,'rowid'=>$this->gid));
		// 安全过滤
		$step = t($_GET['step']);
		if('upload' == $step) {
			$result = $dAvatar->upload();
		} else if('save' == $step) {
			$result = $dAvatar->dosave();
		}
		$this->ajaxReturn($result['data'], $result['info'], $result['status']);
	}

}
?>
