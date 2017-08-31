<?php
/**
 * @author yangli2@iflytek.com
 * 常用邀请用户记录表Model
 */
class UserPwdVerifyModel extends Model {
	protected $tableName = 'user_pwd_verify';
	protected $fields = array (
			0 => 'id',
			1 => 'ctime',
			2 => 'loginname',
			3 => 'key'
	);

	/**
	 * 添加一条用户改密验证信息
	 */
	function addUserPwdVerify($data){
		if(empty($data)){
			return -1;
		}
		// 删除用户已有改密验证信息
		$map = array('loginname' => $data['loginname']);
		$this->delUserPwdVerify($map);
		// 添加一条用户改密验证信息
		return $this->add($data);
	}

	/**
	 * 删除用户改密验证信息
	 */
	function delUserPwdVerify($map){
		if(empty($map)){
			return -1;
		}
		return $this->where($map)->delete();
	}

	/**
	 * 获取用户改密验证信息
	 */
	function getUserPwdVerify($loginname){
		if(empty($loginname)){
			return null;
		}
		return $this->where(array('loginname' => $loginname))->select();
	}


}

?>