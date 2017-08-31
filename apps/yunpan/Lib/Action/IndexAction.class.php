<?php

/**
 * 云盘
 * @author yuliu2@iflytek
 * @version 0.1
 */
class IndexAction extends BaseCloudAction {

	/**
	 * 云盘首页
	 */
	public function index() {
		header("location: ".C('PAN_SITE_URL'));die;
		$this->display();
	}
}