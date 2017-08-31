<?php 
/**
 * 头像模型 - 业务逻辑模型
 * @package msgroup\Lib\Model
 * @author yuliu2@iflytek.com
 * 重构TS的头像Widget
 * 可以根据应用app名称,表记录id来支持多应用头像上传
 */
class MSAvatarModel {

	protected $_app;//应用名称
	protected $_rowid;//记录id
	/**
	 * 初始化模型，加载相应的文件
	 * @params array('app'=>'?','rowid'=>'?') 应用和表记录参数
	 * @return object 头像模型对象
	 */
	public function __construct() {
		
	}
	
	public function init($params = array()){
		$this->_app = $params['app'];
		$this->_rowid = $params['rowid'];
	}

	/**
	 * 判断是否有头像
	 * @return array 
	 */
	public function hasAvatar() {
		$original_file_name = '/avatar/'.$this->_app.$this->convertRowIdToPath().'/original.jpg';
	
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
	 * 获取当前头像
	 * @return array 头像链接
	 */
	public function getAvatar() {
		$empty_url = THEME_URL.'/_static/image/noavatar/'.$this->_app;
		$avatar_url = array(
			'avatar_original' 	=> $empty_url.'/big.jpg',
			'avatar_big' 		=> $empty_url.'/big.jpg',
			'avatar_middle' 	=> $empty_url.'/middle.jpg',
			'avatar_small' 		=> $empty_url.'/small.jpg',
			'avatar_tiny' 		=> $empty_url.'/tiny.jpg'
		); 

		$original_file_name = '/avatar/'.$this->_app.$this->convertRowIdToPath().'/original.jpg';

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
     * 上传头像
     * @return array 上传头像操作信息
     */
    public function upload($fromApi=false) {
		$data['attach_type'] = $this->_app.'_avatar';
        $data['upload_type'] = 'image';
        $info = model('Attach')->upload($data);
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
    				if($fromApi){
    					return $return;
					}else{
						die(json_encode($return));
					}
    		}else{
    			die(json_encode(array('status'=>0,'info'=>'不是有效的图片格式，请重新选择照片上传')));
    		}
    	}else{
    		die(json_encode(array('status'=>0,'info'=>$info['info'])));
    	}
    }

    /**
     * 保存头像图片 - 本地上传
     * @return array 头像图片信息
     */
    public function dosave($facedata=false) {
    	if(!$facedata){
    		$facedata = $_POST;
    	}
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
		$original_file_name = '/avatar/'.$this->_app.$this->convertRowIdToPath().'/original.jpg';

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
        		$this->_createFolder(UPLOAD_PATH.'/avatar/'.$this->_app.$this->convertRowIdToPath());
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
	 * 将rowid转换为三级路径
	 * @return string 头像路径
	 */
	public function convertRowIdToPath() {
		// 静态缓存
		$sc = static_cache($this->_app.'_avatar_path_'.$this->_rowid);
		if(!empty($sc)) {
			return $sc;
		}
		$md5 = md5($this->_rowid);
		$sc = '/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2).'/'.substr($md5, 4, 2);
		static_cache($this->_app.'_avatar_path_'.$this->_rowid, $sc);
		return $sc;
	}

	/**
	 * 创建多级文件目录
	 * @param string $path 路径名称
	 * @return void
	 */
	private function _createFolder($path){
		if(!is_dir($path)) {
			$this->_createFolder(dirname($path));
			mkdir($path, 0777, true);
		}
	}
}