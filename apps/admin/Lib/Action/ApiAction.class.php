<?php
/**
 * 系统配置API
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class ApiAction extends Action{
	public function getTopNav() {
		$listData = model('Navi')->getTopNav();
		return json_encode($listData);
	}

	public function getBottomNav() {
		$listData = model('Navi')->getBottomNav();
		return json_encode($listData);
	}
}
