<?php
class UserAction extends Action{
	
	public function index(){
		
	}
	
	public function login($username,$password){
		return model('CyUser')->validateUser($username,$password);		
	}	
	
	/**
	 * 获取头像 by frsun 2013.12.17
	 * @param  $username
	 */
	public function getcyavatar($username){
		$empty_url = THEME_URL.'/_static/image/noavatar';
		$size  = $_GET['size']?$_GET['size']:'small';
		$size = trim($size);
		$map['login'] = $username;
		$user =Model('User')->where($map)->find();
		if($user){
			$avatar = model('User')->getUserInfo($user['uid']);;
			return  str_replace('api/','',$avatar['avatar_big']);
		}
	}
}
?>