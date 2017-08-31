<?php
/**
 * 资源遴选基类Action
 * @author yuliu2@iflytek.com
 */
class BaseAction extends Action{
	
	/*
	 * 添加读取三级教研员权限 
	 */
	public function _initialize(){
		if(!empty($GLOBALS['ts']['cyuserdata']['rolelist']) && !isset($GLOBALS['ts']['cyuserdata']['instructor_level'])){
			$rolelist = $GLOBALS['ts']['cyuserdata']['rolelist'];
			foreach ($rolelist as $role){
				if($role['name'] === 'instructor'){
					$cyuser = Model('CyUser')->getUserDetail($this->cymid);
					$GLOBALS['ts']['cyuserdata']['instructor_level'] = $cyuser['instructor_level'];
					break;
				}
			}
		}
	}
}
?>