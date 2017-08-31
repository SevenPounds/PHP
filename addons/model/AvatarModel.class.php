<?php 
/**
 * 头像模型 - 业务逻辑模型
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class AvatarModel {

	protected $_uid;			// 用户UID字段

	/**
	 * 初始化模型，加载相应的文件
	 * @param integer $uid 用户UID
	 * @return object 头像模型对象
	 */
	public function __construct($uid) {
		if(!$uid)
			$uid = intval($_SESSION['mid']);
		$this->_uid = intval($uid);
		return $this;
	}

	/**
	 * 初始化模型，加载相应的文件
	 * @param integer $uid 用户UID
	 * @return object 头像模型对象
	 */
	public function init($uid) {
		$this->_uid = intval($uid);
		return $this;
	}

	/**
	 * 判断用户是否上传头像
	 * @return array 
	 */
	public function hasAvatar() {
		$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';
	
		//头像云存储
		$cloud = model('CloudImage');
		if($cloud->isOpen()){
			$original_file_info = $cloud->getFileInfo($original_file_name);
			if($original_file_info){
				$filemtime = @intval($original_file_info['date']);
				$avatar = getImageUrl($original_file_name).'!small.avatar.jpg?v'.$filemtime;
			}
			 
			//头像本地存储
		}elseif(file_exists(UPLOAD_PATH.$original_file_name)){
			$filemtime = @filemtime(UPLOAD_PATH.$original_file_name);
			$avatar = getImageUrl($original_file_name,50,50).'?v'.$filemtime;
		}
		return ($avatar)?true:false;
	}

	/**
	 * 获取当前登录用户头像
	 * @return array 用户的头像链接
	 */
	public function getUserAvatar() {
		$empty_url = THEME_URL.'/_static/image/noavatar';
		$avatar_url = array(
			'avatar_original' 	=> $empty_url.'/big.jpg',
			'avatar_big' 		=> $empty_url.'/big.jpg',
			'avatar_middle' 	=> $empty_url.'/middle.jpg',
			'avatar_small' 		=> $empty_url.'/small.jpg',
			'avatar_tiny' 		=> $empty_url.'/tiny.jpg'
		); 

		$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';

		//头像云存储
		$cloud = model('CloudImage');
        if($cloud->isOpen()){
        	$original_file_info = $cloud->getFileInfo($original_file_name);
        	if($original_file_info){
	        	$filemtime = @intval($original_file_info['date']);
	        	$avatar_url['avatar_original'] = getImageUrl($original_file_name);
	        	$avatar_url['avatar_big'] = getImageUrl($original_file_name).'!big.avatar.jpg?v'.$filemtime;
	        	$avatar_url['avatar_middle'] = getImageUrl($original_file_name).'!middle.avatar.jpg?v'.$filemtime;
	        	$avatar_url['avatar_small'] = getImageUrl($original_file_name).'!small.avatar.jpg?v'.$filemtime;
	        	$avatar_url['avatar_tiny'] = getImageUrl($original_file_name).'!tiny.avatar.jpg?v'.$filemtime;
        	}
        	
        //头像本地存储
        }elseif(file_exists(UPLOAD_PATH.$original_file_name)){
    		$filemtime = @filemtime(UPLOAD_PATH.$original_file_name);
    		$avatar_url['avatar_original'] = getImageUrl($original_file_name);
			$avatar_url['avatar_big'] = getImageUrl($original_file_name,200,200).'?v'.$filemtime;
	    	$avatar_url['avatar_middle'] = getImageUrl($original_file_name,100,100).'?v'.$filemtime;
	    	$avatar_url['avatar_small'] = getImageUrl($original_file_name,50,50).'?v'.$filemtime;
	    	$avatar_url['avatar_tiny'] = getImageUrl($original_file_name,30,30).'?v'.$filemtime;
    	}

		return $avatar_url;
	}

	/**
	 * 保存Flash提交的数据 - flash上传
	 * @param array $data 用户头像的相关信息
	 * @param array $oldUserInfo 貌似无用字段，与此flash组件有关
	 * @return boolean 是否保存成功
	 */
	public function saveUploadAvatar($data, $oldUserInfo) {
		$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';
		// Log::write(var_export($data,true));
		//如果是又拍上传
        $cloud = model('CloudImage');
        if($cloud->isOpen()){
        	@$cloud->deleteFile($original_file_name);
        	//重新上传新头像原图
        	$imageAsString = $data['big'];
        	$res = $cloud->writeFile($original_file_name,$imageAsString,true);
        }else{
        	$res = file_put_contents(UPLOAD_PATH.$original_file_name, $data['big']);
			getThumbImage($original_file_name,200,200,true,true);
			getThumbImage($original_file_name,100,100,true,true);
			getThumbImage($original_file_name,50,50,true,true);
			getThumbImage($original_file_name,30,30,true,true);
        }

        if(!$res){
        	return false;
        }else{
		    // 清理用户缓存
		    model('User')->cleanCache($this->_uid);
			return true;
        }
	}

    /**
     * 上传头像
     * @return array 上传头像操作信息
     */
    public function upload($fromApi=false) {
		$data['attach_type'] = 'avatar';
        $data['upload_type'] = 'image';
        $info = model('Attach')->upload($data);
        //Log::write(var_export($info,true));
    	if($info['status']){
    		$data = $info['info'][0];
    		$image_url = getImageUrl($data['save_path'].$data['save_name']);
    		$image_info = getimagesize($image_url);
    		//如果不支持获取远程图片信息，使用如下方法
    		if(!$image_info){
		  		$cloud = model('CloudImage');
		        if($cloud->isOpen()){
		        	$cinfo = $cloud->getFileInfo($data['save_path'].$data['save_name']);
		        	if($cinfo){
			        	$cinfo = json_decode($cinfo);
			        	$image_info[0] = $cinfo['width'];
			        	$image_info[1] = $cinfo['height'];
		        	}
		        }else{
		        	$image_info = getimagesize(UPLOAD_PATH.'/'.$data['save_path'].$data['save_name']);
		        }
		    }
    		if($image_info){
    			unset($return);
    			$return['data']['picwidth'] = $image_info[0];
    			$return['data']['picheight'] = $image_info[1];
    			$return['data']['picurl'] 	 = $data['save_path'].$data['save_name'];
    			$return['data']['fullpicurl'] = getImageUrl($data['save_path'].$data['save_name']);
    			$return['status'] = '1';
    			// if($image_info[0] < 300){
    			// 	die(json_encode(array('status'=>0,'info'=>'请选择一个较大的照片作为头像，宽度不小于300px')));
    			// }else{
    				if($fromApi){
    					return $return;
					}else{
						die(json_encode($return));
					}
    			//}
    		}else{
    			die(json_encode(array('status'=>0,'info'=>'不是有效的图片格式，请重新选择照片上传')));
    		}
    	}else{
    		die(json_encode(array('status'=>0,'info'=>$info['info'])));
    	}
    }

    /**
     * 将系统推荐头像复制到指定文件夹下
     */
    public function moveImg($imgName){
    	// 载入默认规则
    	$default_options = array();
    	$default_options['custom_path']	= date('Y/md/H/');					// 应用定义的上传目录规则：'Y/md/H/'
    	$default_options['allow_exts'] = 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf'; 					// 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf'
    	$default_options['save_path'] =	UPLOAD_PATH.'/'.$default_options['custom_path'];
    	$default_options['save_name'] =	''; //指定保存的附件名.默认系统自动生成
    	$default_options['save_to_db'] = true;
    	//原图片地址
    	$fileName = ADDON_PATH."/theme/stv1/_static/headimg/".$imgName.".jpg";
    	
    	// 创建目录
    	mkdir($default_options['save_path'], 0777, true);
    	$saveName = uniqid().".jpg";
    	//新地址及名称
    	$fileSaveName = auto_charset($default_options['save_path'].$saveName,'utf-8','gbk');
    	if(copy($fileName, $fileSaveName)){
    		$imgSizeInfo = getimagesize($fileName);
    		$upload_info = array();
    		$upload_info['name'] = $imgName.".jpg";
    		$upload_info['type'] = $imgSizeInfo['mime'];
    		$upload_info['width'] = $imgSizeInfo[0];
    		$upload_info['height'] = $imgSizeInfo[1];
    		$upload_info['size'] = filesize($fileName);
    		$upload_info['key'] = "Filedata";
    		$upload_info['extension'] = "jpg";
    		$upload_info['save_path'] = $default_options['save_path'];
    		$upload_info['save_name'] = $saveName;
    		$hashType = 'md5_file';
    		$upload_info['hash'] = $hashType($fileSaveName);
    		// 保存信息到附件表
    		$status = model('Attach')->saveDefaultImg($upload_info, $default_options);
    		if("success" == $status){
    			//保存成功，则切割头像,并保存到指定路径下
    			$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';
    			$filemtime = microtime(true);
    			if(!file_exists(UPLOAD_PATH.$original_file_name)) {
    				$this->_createFolder(UPLOAD_PATH.'/avatar'.$this->convertUidToPath($this->_uid));
    			}
    			if(copy($fileSaveName, UPLOAD_PATH.$original_file_name)){
    				$return['data']['big'] 		= getImageUrl($original_file_name,200,200,false,true).'?v'.$filemtime;
    				$return['data']['middle'] 	= getImageUrl($original_file_name,100,100,false,true).'?v'.$filemtime;
    				$return['data']['small'] 	= getImageUrl($original_file_name,50,50,false,true).'?v'.$filemtime;
    				$return['data']['tiny'] 	= getImageUrl($original_file_name,30,30,false,true).'?v'.$filemtime;
    				return  "success";
    			}else{
    				return "fail";
    			}
    		}
    	} 
    }
    /**
     * 将图片上传到本地
     */
    public function uploadLocalImg(){
    	// 载入默认规则
    	$default_options = array();
    	// 应用定义的上传目录规则：'Y/md/H/'
    	$default_options['custom_path']	= date('Y/md/H/');					
    	$default_options['allow_exts'] = 'jpg,png,jpeg'; 					// 'jpg,gif,png,jpeg,bmp,zip,rar,doc,xls,ppt,docx,xlsx,pptx,pdf'
    	$default_options['save_path'] =	UPLOAD_PATH.'/'.$default_options['custom_path'];
    	//指定保存的附件名.默认系统自动生成
    	$default_options['save_name'] =	''; 
    	$default_options['save_to_db'] = true;
    	$default_options['size'] = "original";
    	$system_default = model('Xdata')->get('admin_Config:attach');
    	if(empty($system_default['attach_path_rule'])){
    		$default_options['max_size'] = 2 * 1024 * 1024;		// 单位: 兆
    	}else{
    		$default_options['max_size'] = floatval($system_default['attach_max_size']) * 1024 * 1024;		// 单位: 兆
    	}
    	
    	$result = model('Attach')->localUpload($default_options);
    	return $result;
    }
    /**
     * 切割头像
     */
    public function cutImg($savePath,$saveName){
    	//保存成功，则切割头像,并保存到指定路径下
    	$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';
    	$fileSaveName = auto_charset(UPLOAD_PATH.'/'.$savePath.$saveName,'utf-8','gbk');
    	$filemtime = microtime(true);
    	if(!file_exists(UPLOAD_PATH.$original_file_name)) {
    		$this->_createFolder(UPLOAD_PATH.'/avatar'.$this->convertUidToPath($this->_uid));
    	}
    	if(copy($fileSaveName, UPLOAD_PATH.$original_file_name)){
    		$return['data']['big'] 		= getImageUrl($original_file_name,200,200,false,true).'?v'.$filemtime;
    		$return['data']['middle'] 	= getImageUrl($original_file_name,100,100,false,true).'?v'.$filemtime;
    		$return['data']['small'] 	= getImageUrl($original_file_name,50,50,false,true).'?v'.$filemtime;
    		$return['data']['tiny'] 	= getImageUrl($original_file_name,30,30,false,true).'?v'.$filemtime;
    		return  "success";
    	}else{
    		return "fail";
    	}
    	
    }
    
    /**
     * 保存用户头像图片 - 本地上传
     * @return array 头像图片信息
     */
    public function dosave($facedata=false) {
    	//Log::write(var_export($facedata,true));
    	if(!$facedata){
    		$facedata = $_POST;
    	}
    	//header("Content-type: image/jpeg"); 
    	//Log::write(var_export($facedata,true));
		$picWidth = intval($facedata['picwidth']); //原图的宽度
		$scale = $picWidth/300;	//缩放比例
		$x1 = intval($facedata['x1'])*$scale;		// 选择区域左上角x轴坐标
		$y1 = intval($facedata['y1'])*$scale;		// 选择区域左上角y轴坐标
		$x2 = intval($facedata['x2'])*$scale;		// 选择区域右下角x轴坐标
		$y2 = intval($facedata['y2'])*$scale;		// 选择区域右下角x轴坐标
		$w  = intval($facedata['w'])*$scale;		// 选择区的宽度
		$h  = intval($facedata['h'])*$scale;		// 选择区的高度

		$src = getImageUrl($facedata['picurl']);	// 图片的路径

		//原图存储地址
		$original_file_name = '/avatar'.$this->convertUidToPath($this->_uid).'/original.jpg';

		$filemtime = microtime(true);

		//如果是又拍上传
        $cloud = model('CloudImage');
        if($cloud->isOpen()){
			//切割原图
			require_once SITE_PATH.'/addons/library/phpthumb/ThumbLib.inc.php';
			$thumb = PhpThumbFactory::create($src);
			$res = $thumb->crop($x1, $y1, $w, $h);

			//获取获取缩图后的数据
			if(!$res){
				die(json_encode(array('status'=>0,'info'=>'头像切割失败')));
			}
        	@$cloud->deleteFile($original_file_name);
        	//重新上传新头像原图
        	$imageAsString = $thumb->getImageAsString();
        	$res = $cloud->writeFile($original_file_name,$imageAsString,true);
        	if($res){
	 			unset($return);
	 			$return['data']['big'] 		= getImageUrl($original_file_name).'!big.avatar.jpg?v'.$filemtime;
				$return['data']['middle'] 	= getImageUrl($original_file_name).'!middle.avatar.jpg?v'.$filemtime;
				$return['data']['small'] 	= getImageUrl($original_file_name).'!small.avatar.jpg?v'.$filemtime;
				$return['data']['tiny'] 	= getImageUrl($original_file_name).'!tiny.avatar.jpg?v'.$filemtime;
				$return['status'] = 1;
			    // 清理用户缓存
	    		model('User')->cleanCache($this->_uid);
        	}else{
        		$return['status'] = '0';
        		$return['info']	  = '切割头像失败';
        	}
        }else{

			//切割原图
			require_once SITE_PATH.'/addons/library/phpthumb/ThumbLib.inc.php';
			$thumb = PhpThumbFactory::create(UPLOAD_PATH.'/'.$facedata['picurl']);
			$res = $thumb->crop($x1, $y1, $w, $h);

			//获取获取缩图后的数据
			if(!$res){
				die(json_encode(array('status'=>0,'info'=>'头像切割失败')));
			}

        	if(!file_exists(UPLOAD_PATH.$original_file_name)) {
        		$this->_createFolder(UPLOAD_PATH.'/avatar'.$this->convertUidToPath($this->_uid));
        	}
			$thumb->save(UPLOAD_PATH.$original_file_name);
			unset($return);
			$return['data']['big'] 		= getImageUrl($original_file_name,200,200,false,true).'?v'.$filemtime;
			$return['data']['middle'] 	= getImageUrl($original_file_name,100,100,false,true).'?v'.$filemtime;
			$return['data']['small'] 	= getImageUrl($original_file_name,50,50,false,true).'?v'.$filemtime;
			$return['data']['tiny'] 	= getImageUrl($original_file_name,30,30,false,true).'?v'.$filemtime;
		    $return['status'] = 1;
        }
        die(json_encode($return));
    }

    /**
     * 保存用户头像图片 - 本地上传
     * @return array 头像图片信息
     */
    public function saveRemoteAvatar($src,$uid) {

		//原图存储地址
		$original_file_name = '/avatar'.$this->convertUidToPath($uid).'/original.jpg';

		//保存图片到原图
		$opts = array(
		'http'=>array(
		  'method' => "GET",
		  'timeout' => 3, //超时30秒
		  'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
		 )
		);
		$context = stream_context_create($opts);
		$imageData = file_get_contents($src, false, $context);

		$filemtime = microtime(true);

		//如果是又拍上传
        $cloud = model('CloudImage');
        if($cloud->isOpen()){
        	@$cloud->deleteFile($original_file_name);
        	//重新上传新头像原图
        	$imageAsString = $imageData;
        	$res = $cloud->writeFile($original_file_name,$imageAsString,true);
        	if($res){
	 			unset($return);
	 			$return['data']['big'] 		= getImageUrl($original_file_name).'!big.avatar.jpg?v'.$filemtime;
				$return['data']['middle'] 	= getImageUrl($original_file_name).'!middle.avatar.jpg?v'.$filemtime;
				$return['data']['small'] 	= getImageUrl($original_file_name).'!small.avatar.jpg?v'.$filemtime;
				$return['data']['tiny'] 	= getImageUrl($original_file_name).'!tiny.avatar.jpg?v'.$filemtime;
				$return['status'] = 1;
			    // 清理用户缓存
	    		model('User')->cleanCache($this->_uid);
        	}else{
        		$return['status'] = '0';
        		$return['info']	  = '上传头像失败';
        		return $return;
        	}
        }else{

        	if(!file_exists(UPLOAD_PATH.$original_file_name)) {
        		$this->_createFolder(UPLOAD_PATH.'/avatar'.$this->convertUidToPath($uid));
        	}

        	if(!file_put_contents(UPLOAD_PATH.$original_file_name, $imageData)){
	    		$return['status'] = '0';
	    		$return['info']	  = '切割保存失败';
				return $return;
			}

			$return['data']['big'] 		= getImageUrl($original_file_name,200,200,true,true).'?v'.$filemtime;
			$return['data']['middle'] 	= getImageUrl($original_file_name,100,100,true,true).'?v'.$filemtime;
			$return['data']['small'] 	= getImageUrl($original_file_name,50,50,true,true).'?v'.$filemtime;
			$return['data']['tiny'] 	= getImageUrl($original_file_name,30,30,true,true).'?v'.$filemtime;
		    $return['status'] = 1;
        }
        return $return;
    }

	/**
	 * 将用户的UID转换为三级路径
	 * @param integer $uid 用户UID
	 * @return string 用户路径
	 */
	public function convertUidToPath($uid) {
		// 静态缓存
		$sc = static_cache('avatar_uidpath_'.$uid);
		if(!empty($sc)) {
			return $sc;
		}
		$md5 = md5($uid);
		$sc = '/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4, 2);
		static_cache('avatar_uidpath_'.$uid, $sc);
		return $sc;
	}

	/**
	 * 创建多级文件目录
	 * @param string $path 路径名称
	 * @return void
	 */
	private function _createFolder($path)
	{
		if(!is_dir($path)) {
			$this->_createFolder(dirname($path));
			mkdir($path, 0777, true);
		}
	}

	/**
	 * 从cyCore获取当前用户的头像
	 */
	public function getUserPhotoFromCyCore($uid,$key="uid",$appKey){

		if($key=='uid'){
			$userName = D("User")->getUser($uid)["login"];
		}elseif($key=='cyUid'){
			$userName = D('CyUser')->getUserDetail($uid)['login'];
		}elseif($key=='loginName'){
			$userName = $uid;
		}
		if(empty($appKey)){
			$appKey = C("SNS_APP_ID");
		}
		$SuserAvatar = S("UserAvatar_".$userName);
		if(empty($SuserAvatar)){
			$epspClient= new \EpspClient();
			// 获取头像--通过appkey获取
			$photoFromOpen = $epspClient->getUserPhoto('loginName', $userName, $appKey);
			$photo = json_decode($photoFromOpen,true);

			if($photo["Code"]==0){
				$userPhotoData = json_decode($photo["Data"],true);
				if($userPhotoData["code"]==0){
					$userPhotoDataAvatar=$userPhotoData["data"]["extInfo"];
					if(!empty($userPhotoDataAvatar['avatar'])&&count($userPhotoDataAvatar['avatarThumbnails'])>0){
						$userPhoto = array(
							'avatar_original' 	=> $userPhotoDataAvatar["avatar"],
							'avatar_big' 		=> $userPhotoDataAvatar["avatarThumbnails"]["200*200"],
							'avatar_middle' 	=> $userPhotoDataAvatar["avatarThumbnails"]["100*100"],
							'avatar_small' 		=> $userPhotoDataAvatar["avatarThumbnails"]["50*50"],
							'avatar_tiny' 		=> $userPhotoDataAvatar["avatarThumbnails"]["30*30"]
						);
					}else{ // 查不到显示默认的头像
						$userPhoto = $this->defaultAvatar();
					}
				}else{
					$userPhoto = $this->defaultAvatar();
				}
				$expire = 600; // 10分钟
				S("UserAvatar_".$userName,$userPhoto,$expire);
			}else{
				$userPhoto = $this->defaultAvatar();
			}
		}else{
			$userPhoto = $SuserAvatar;
		}

		return $userPhoto;
	}

	/**
	 * 获取默认头像
	 * @return array
	 */
	private function defaultAvatar(){
		$empty_url = THEME_URL.'/_static/image/test';
		$userPhoto = array(
			'avatar_original' 	=> $empty_url.'/test.jpg',
			'avatar_big' 		=> $empty_url.'/test.jpg',
			'avatar_middle' 	=> $empty_url.'/test.jpg',
			'avatar_small' 		=> $empty_url.'/test.jpg',
			'avatar_tiny' 		=> $empty_url.'/test.jpg'
		);
		return $userPhoto;
	}
}