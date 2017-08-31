<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 14-4-17
 * Time: 上午11:26
 * Class BaseCloudAction
 * 云盘基类
 */


class BaseCloudAction extends Action{

    //默认文件夹列表
    protected $myDir = array();

    //备课本文件夹
    protected $bkDirId = '';
	
	/**
	 * 初始化网盘信息
	 */
	protected function _initialize() {
		try {
			$this->bkDirId = D ( 'Yunpan', 'yunpan' )->getDirId ( $this->cymid, '0', '我的备课本' );
		} catch ( Exception $exc ) {
			$this->error ( '云盘服务异常,请稍后再试！' );
		}
        $isTeacher = Model('CyUser')->hasRole( UserRoleTypeModel::TEACHER,$this->cymid);  //by frsun 2014.5.7 获取当前用户的角色
		$this->assign("isTeacher",$isTeacher);
		$this->assign("bkDir", $this->bkDirId);
	}
}
