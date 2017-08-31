<?php
/**
 * 用于控制各app的访问权限
 * @author zhaoliang
 *2014/2/26
 */
class AccessAction extends Action{
	public function __construct(){
		parent::__construct();
		if(!parent::checkAppAccess()){
			$this->error('您没有权限访问，即将跳转...');
		}
	}
}
?>