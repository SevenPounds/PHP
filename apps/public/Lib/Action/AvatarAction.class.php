<?php
class AvatarAction extends Action{
	
	public function index(){
	
		$empty_url = THEME_URL.'/_static/image/noavatar';
		$cyuid =$_GET['cyuid'];
	    
		$map['login'] = $cyuid;
		$user =Model('User')->where($map)->find();
		if($user){
		$avatar = model('Avatar')->init($user['uid'])->getUserPhotoFromCyCore($user['uid']);
		echo $avatar['avatar_middle'];
		}else{
           echo $empty_url.'/middle.jpg';
		}
	}


	
	/**
	 * 头像保存 
	 */
	public function doSaveAvatar(){
		$uid =$_REQUEST['uid']?$_REQUEST['uid']:0;
		$password =$_REQUEST['password']?$_REQUEST['password']:0;  
		if(!$uid||!$password){
			die($this->AjaxReturn(null,"参数错误",-1));
		}
		$user = Model('User')->field('uid,password')->where("uid ='{$uid}'")->find();
		if(!$user){
			die($this->AjaxReturn(null,"用户不存在",-1));
		}
		if($password!=$user['password']){
			die($this->AjaxReturn(null,"密码不正确",-1));
		}
		$dAvatar = model('Avatar');
		$dAvatar->init($uid); 			// 初始化Model用户id
		// 安全过滤
		$result = $dAvatar->upload(true);
		if(!$result['status']){
			die($this->AjaxReturn($result['data'],$result['info'],$result['status']));
		}
		$result = $dAvatar->saveRemoteAvatar(UPLOAD_PATH.'/'.$result['data']['picurl'],$uid);
		Model('User')->cleanCache($uid);
		die($this->AjaxReturn($result['data'],$result['info'],$result['status']));
	}
	
	
}







?>
