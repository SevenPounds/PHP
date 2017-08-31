<?php
class IndexAction extends Action{
	public function __construct() {
		parent::__construct();
	
		$this->assign('_userid', $this->userid);
	}
	
	/**
	 * 教学反思首页
	 */
	public function Index(){
	
	
		$this->display();
	}
}
?>