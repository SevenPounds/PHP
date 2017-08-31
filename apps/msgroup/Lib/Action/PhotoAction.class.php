<?php
/**
 * 荣誉信息管理Action
 * @author sjzhao
 *
 */
class PhotoAction extends BaseAction{
	/**
     * 初始化
     */
	public function _initialize() {
		parent::_initialize();

		// 权限判断
		if ($this->level == 0) {
			$this->error("当前用户没有权限");
		}
	}

	/**
	 * 荣誉信息管理首页
	 */
	public function index(){
		$this->display();
	}

	public function uploadReputationPhoto(){
		$msgid = $_REQUEST["gid"];
		if (!empty($_FILES) && !empty($msgid)) {
			$file = $_FILES['Filedata']['tmp_name'];
			$fileParts = pathinfo($_FILES['Filedata']['name']);
			$attachInfo = array();
			$attachInfo['save_path'] = date('Y/md/H/');
			$attachInfo['extension'] = strtolower($fileParts['extension']);
			$attachInfo['save_name'] = uniqid().'.'.strtolower($fileParts['extension']);
	
			$dirName = UPLOAD_PATH.'/'.$attachInfo['save_path'];
			@mkdir($dirName,0777,true);
			move_uploaded_file($file, $dirName.$attachInfo['save_name']);
		
			if(file_exists($dirName.$attachInfo['save_name'])){
				$attachInfo['appname'] = 'msgroup';
				$attachInfo['attach_type'] = 'msgroup_photo';
				$attachInfo['from'] = 0;
				$attachInfo['uid'] = $this->mid;
				$attachInfo['ctime'] = time();
				$attachInfo['name'] = $fileParts['basename'];
				$attachInfo['hash'] = md5_file($dirName.$attachInfo['save_name']);
				$attachInfo['size'] = abs(filesize($dirName.$attachInfo['save_name']));
				
				//附件表Attach记录荣誉信息
				$attach_id = D("Attach")->add($attachInfo);
			
				$data = array();
				$data['uid'] = $attachInfo['uid'];
				$data['attachId'] = $attach_id;
				$data['gid'] = $msgid;
				$data['photo_name'] = isset($_REQUEST["phototitle"]) ? $_REQUEST["phototitle"] : $fileParts['filename'];
				$data['upload_time'] = $attachInfo['ctime'];
				$data['mtime'] = $data['upload_time'];
				$data['savepath'] = $attachInfo['save_path'].$attachInfo['save_name'];
				//工作室荣誉信息表Attach记录荣誉信息
				$res = D('MSGroupPhoto')->add($data);
				
				$d['gid'] = $msgid;
				$d['source_url'] = U('msgroup/Index/photoPreview',array('gid'=>$msgid,'pid'=>$res));
				$d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 上传了一个荣誉【'.$data['photo_name'].'】&nbsp;';
				$feed = model('Feed')->put($this->mid, 'msgroup', "msgroup", $d);
				unset($d);
				$this->ajaxReturn('','上传成功！',1);
			}else{
				$this->ajaxReturn('','上传失败！',0);
			}
		}else{
			$this->ajaxReturn('','上传参数出错！',0);
		}
	}
	
	
	/**
	 * 荣誉详细
	 */
	public function detail(){
		$gid = $_REQUEST['gid'];
		$photoId = $_REQUEST['pid'];
		if(empty($gid) || empty($photoId)){
			$this->error("参数异常");
		}
		$map['gid'] = $gid;
		$map['id'] = $photoId;
		$photo = D('MSGroupPhoto')->where($map)->find();
		if(empty($photo) || $photo['is_deleted']==1){
			$this->error("该荣誉已被删除或不存在");
		}
		D('MSGroupPhoto')->updateMSGPhotoViewCount($photoId,1);
		unset($map);
		$this->assign('photo',$photo);
		$this->display();
	}
	/**
	 * 荣誉预览
	 */
	public function preview(){
		$gid = $_REQUEST['gid'];
		$photoId = $_REQUEST['pid'];
		if(empty($gid) || empty($photoId)){
			$this->error("参数异常");
		}
		$map['gid'] = $gid;
		$map['id'] = $photoId;
		$photo = D('MSGroupPhoto')->where($map)->find();
		if(empty($photo) || $photo['is_deleted']==1){
			$this->error("该荣誉已被删除或不存在");
		}
		D('MSGroupPhoto')->updateMSGPhotoViewCount($photoId,1);
		unset($map);
		$this->assign('photo',$photo);
		$this->display();
	}	
	/**
	 * 荣誉上传dialog
	 */
	public function showUpload(){
		$this->setTitle("上传荣誉图片");
		$this->display();
	}
	
}
?>
