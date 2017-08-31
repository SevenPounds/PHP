<?php
/**
 * 相册应用 - 公共基础控制器
 */
class BaseAction extends Action {

	var $appName;			// 应用名称

	/**
	 * 执行应用初始化
	 * @return void
	 */
	public function _initialize() {
		global $ts;
		$this->appName = $ts['app']['app_alias'];
		if ($this->mid == $this->uid) {
			$userName = '我';
		} else {
			if(D('User')->where("uid={$this->uid} AND is_Del=0")->count()==0){
				$this->assign('jumpUrl', U('photo/Index/albums'));
				$this->error('用户不存在或已被删除！');
			}
			$userName = getUserName($this->uid);
		}
		$this->assign('userName', $userName);
		$this->setTitle($userName.'的'.$this->appName);
	}
}