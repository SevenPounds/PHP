<?php
/**
 * 登录日志模型 - 数据对象模型
 */
class LoginModel extends Model
{
	protected $tableName = 'sns_loginlogs';
	protected	$fields		=	array
	(
			0 => 'sns_login_id',
			1 => 'uid',
			2 => 'ctime',
			3 => 'login_name',
            '_pk' => 'sns_login_id',
            '_autoinc'=> true
	);
	public function Insert($uid, $uname){
		$logData['login_name'] = $uname;
		$logData['ctime'] = time();
		$logData['uid'] = $uid;
		M($this->tableName)->add($logData);
	}
}