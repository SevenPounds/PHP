<?php
/**
 * 班级相册应用控制器
 */
class ClassAlbumAction extends Action {
	/**
	 * 获取应用配置参数
	 * @param string $key 指定的配置KEY值
	 * @return mixed(array|string) 应用配置参数
	 */
	function photo_getConfig ($key = null) {
		$config = model('Xdata')->lget('photo');
		$config['album_raws'] || $config['album_raws'] = 6;
		$config['photo_raws'] || $config['photo_raws'] = 8;
		$config['photo_preview'] == 0 || $config['photo_preview'] = 1;
		($config['photo_max_size'] = floatval($config['photo_max_size']) * 1024 * 1024) || $config['photo_max_size'] = -1;
		$config['photo_file_ext'] || $config['photo_file_ext'] = 'jpeg,gif,jpg,png';
		$config['max_flash_upload_num'] || $config['max_flash_upload_num'] = 10;
		$config['open_watermark']==0 || $config['open_watermark'] = 1;
		$config['watermark_file'] || $config['watermark_file'] = 'public/images/watermark.png';
		if ($key == null) {
			return $config;
		} else {
			return $config[$key];
		}
	}
	/**
	 * 班级全部专辑
	 * @return void
	 */
	public function classalbums () {
		// 获取相册数据
		$map['classId'] = $_GET['cid'];
		$map['isDel'] = 0;
		// 相册信息
		$data = D('Album', 'photo')->order("mTime DESC")->where($map)->findPage(20);
		// 所有的图片数目
		$count = 10;
		// 最后更新时间
		$lastUpdateTime = D('Album', 'photo')->where("classId=".$map['classId'])->order('mTime DESC')->limit(1)->getField('mTime');
	
		$this->assign('classId',$map['classId']);
		$this->assign('data', $data);
		$this->assign('lastUpdateTime', $lastUpdateTime);
		$this->setTitle("班级相册");
		
		$this->display();
	}
	/**
	 * 显示一个班级的图片专辑
	 * @return void
	 */
	public function classalbum () {
		$id = intval($_REQUEST['id']);
		$classId = $_REQUEST['cid'];
		// 获取相册信息
		$albumDao = D('Album','photo');
		$album = $albumDao->where("id={$id}")->find();
	
		if (!$album) {
			$this->assign('jumpUrl', U('class/ClassAlbum/classalbums',array('cid'=>$classId)));
			$this->error('相册不存在或已被删除！');
		}
	
		// 获取图片数据
		$order = '`order` DESC, `id` DESC';
		$map['albumId'] = $id;
		$map['is_del'] = 0;
	
		$config = photo_getConfig();
		$photos	= D('Photo', 'photo')->order($order)->where($map)->findPage(20);
		$this->assign('photos', $photos);
	
		// 点击率加1
		$res = $albumDao->where("id={$id} AND classId=$classId")->setInc('readCount');

		$this->setTitle("班级相册".'：'.$album['name']);
	
		$this->assign('photo_preview', $config['photo_preview']);
		$this->assign('classId',$classId);
		$this->assign('album', $album);
		$this->display();
	}
	/**
	 * 显示班级相册的一张图片
	 * @return void
	 */
	public function classphoto() {
		$uid = intval($_REQUEST['uid']);
		$aid = intval($_REQUEST['aid']);
		$id = intval($_REQUEST['id']);
		$classId = $_REQUEST['cid'];
		$type = t($_REQUEST['type']);	// 图片来源类型，来自某相册，还是其它的
	
		// 判断来源类型
		if (!empty($type) && $type != 'mAll') {
			$this->error('错误的链接！');
		}
		$this->assign('type', $type);
	
		// 获取所在相册信息
		$albumDao = D('Album','photo');
		$album = $albumDao->find($aid);
		if (!$album) {
			$this->assign('jumpUrl', U('class/ClassAlbum/classalbums',array('cid'=>$classId)));
			$this->error('相册不存在或已被删除！');
		}
	
		// 获取图片信息
		$photoDao = D('Photo','photo');
		$photo = $photoDao->where(" albumId={$aid} AND `id`={$id} AND userId={$uid} ")->find();
		$this->assign('photo', $photo);
	
		// 验证图片信息是否正确
		if (!$photo) {
			$this->assign('jumpUrl', U('class/ClassAlbum/classalbum', array('uid'=>$this->uid,'id'=>$aid,'cid'=>$classId)));
			$this->error('图片不存在或已被删除！');
		}
	
		// 隐私控制
		if ($this->mid != $album['userId']) {
			$relationship = getFollowState($this->mid, $this->uid);
			if ($album['privacy'] == 3) {
				$this->error('这个'.$this->appName.'的图片，只有主人自己可见。');
			} else if ($album['privacy'] == 2 && $relationship == 'unfollow') {
				$this->error('这个'.$this->appName.'的图片，只有主人的粉丝可见。');
			} else if ($album['privacy'] == 4) {
				;
				$cookie_password = cookie('album_password_'.$album['id']);
				// 如果密码不正确，则需要输入密码
				if ($cookie_password != md5($album['privacy_data'].'_'.$album['id'].'_'.$album['userId'].'_'.$this->mid)) {
					$this->need_password($album, $id);
					exit;
				}
			}
		}
		
		$this->assign('classId',$classId);
		$this->assign('album', $album);
		$this->assign('albumId', $album['id']);
		$this->assign('photoId', $id);
	
		// 获取所有图片数据
		$photos = $albumDao->getPhotos($this->uid, $aid, '', '`order` DESC, `id` DESC', 0);
	
		// 获取上一页 下一页 和 预览图
		if ($photos) {
			foreach ($photos as $v) {
				$photoIds[] = intval($v['id']);
			}
			$photoCount = count($photoIds);
			// 颠倒数组，取索引
			$pindex = array_flip($photoIds);
			// 当前位置索引
			$now_index = $pindex[$id];
			// 上一张
			$pre_index = $now_index - 1;
			if ($now_index <= 0) {
				$pre_index = $photoCount - 1;
			}
			$pre_photo = $photos[$pre_index];
			// 下一张
			$next_index = $now_index + 1;
			if ($now_index >= $photoCount - 1) {
				$next_index = 0;
			}
			$next_photo = $photos[$next_index];
			// 预览图的位置索引
			$start_index = $now_index - 2;
			if ($photoCount - $start_index < 5) {
				$start_index = ($photoCount - 5);
			}
			if ($start_index < 0) {
				$start_index = 0;
			}
			// 取出预览图列表 最多5个
			$preview_photos = array_slice($photos, $start_index, 5);
		} else {
			$this->error('图片列表数据错误！');
		}
		// 点击率加1
		$res = $photoDao->where("id={$id} AND albumId={$aid} AND userId={$this->uid}")->setInc('readCount');

		$this->assign('photoCount', $photoCount);
		$this->assign('now', $now_index + 1);
		$this->assign('pre', $pre_photo);
		$this->assign('next', $next_photo);
		$this->assign('previews', $preview_photos);
	
		unset($pindex);
		unset($photos);
		unset($album);
		unset($preview_photos);
		$user_name = getUserName($this->uid);
		$this->assign('user_name', $user_name);
		$this->setTitle('班级图片：'.$photo['name']);
	
		$this->display();
	}
	/**
	 * 班级flash上传
	 * @return void
	 */
	public function classflash () {
		// 获取相册配置信息
		$config = $this->photo_getConfig();
		$classId = $_GET['cid'];
		$this->assign('classId',$classId);
		$this->assign($config);
		$this->setTitle('批量上传');
		$this->display();
	}
	/**
	 * 上传后执行编辑操作
	 * @return void
	 */
	public function muti_edit_classphotos () {
		$upnum = intval($_REQUEST['upnum']);
		if ($upnum == 0) {
			$this->error('请至少上传一张图片！');
		}
		$albumId = intval($_REQUEST['albumId']);
		$classId = $_REQUEST['cid'];
		$albumDao = D('Album', 'photo');
		$albumInfo = $albumDao->find($albumId);
	
		if (!$albumInfo) {
			$this->error('请上传到指定的相册！');
		}
		// 公开的相册发布微薄
		if ($albumInfo['privacy'] <= 2) {
			$this->assign('publish_weibo', 1);
		}
		if ($upnum > 0) {
			$photos = D('Photo', 'photo')->limit($upnum)->order("id DESC")->where("albumId=$albumId")->findAll();
			$this->assign('photos', $photos);
			$this->assign('album', $albumInfo);
			$this->assign('upnum', $upnum);
			$albumlist = $albumDao->where("classId=$classId")->findAll();
			$this->assign('albumlist', $albumlist);
			$this->assign('classId',$classId);
			$this->display();
		} else {
			$this->error('上传出错：没有上传任何图片！');
		}
	}
	/**
	 * 保存上传的图片
	 * @return void
	 */
	public function save_upload_photos () {
		// 相册信息
		$albumId = intval($_POST['albumId']);
		$classId = $_POST['cid'];//班级ID
		$album_cover = intval($_POST['album_cover']);
		$upnum = intval($_POST['upnum']);
		$albumDao = D('Album', 'photo');
		$albumInfo = $albumDao->find($albumId);
	
		if (!$albumInfo) {
			$this->error('请先正确选择相册，再上传图片！');
		}
		// 处理图片信息
		$photoDao = D('Photo', 'photo');
		// 解析图片数据
		foreach ($_POST['name'] as $k => $v) {
			$new_photos[$k]['name'] = t($v);
			$new_photoids[] = t($k);
		}
		foreach ($_POST['move_to'] as $k => $v) {
			$new_photos[$k]['albumId'] = t($v);
		}
		// 对比原始数据，筛选出需要更新的图片
		$photo_ids['id'] = array('IN', $new_photoids);
		$old_photos = $photoDao ->where($photo_ids)->findAll();
		foreach ($old_photos as $k => $v) {
			//如果相册ID和名称都没变化，不需要保存
			$photoid = $v['id'];
			if ($v['albumId'] == $new_photos[$photoid]['albumId'] && $v['name'] == $new_photos[$photoid]['name']) {
				unset($new_photos[$photoid]);
			}
		}
		// 保存图片信息并统计新图片数
		foreach ($new_photos as $k => $v) {
			unset($map);
			$map['userId'] = $this->mid;
			$map['albumId'] = $v['albumId'];
			$map['name'] = $v['name'];
			$map['privacy'] = $album_privacy;
			// 相册信息更新
			$photoDao->limit(1)->where("id='$k'")->save($map);
			// 重置相册图片数
			$albumDao->updateAlbumPhotoCount($map['albumId']);
		}
		// 重置相册图片数
		$albumDao->updateAlbumPhotoCount($albumId);
		// 如果相册封
		if ($album_cover) {
			$album['coverImageId'] = $album_cover;
			if ($coverInfo = $photoDao->field('id,savepath')->find($album_cover)) {
				$album['coverImagePath'] = $coverInfo['savepath'];
				$albumDao->where("id='$albumId'")->save($album);
			}
		}
	
		$newphotoCount = count($new_photoids);
		$photo_ids['albumId'] = $albumId;
		$photoInfo = $photoDao->where($photo_ids)->order('id ASC')->find();
		if (!$photoInfo) {
			$photoInfo = $photoDao->where(array('id'=>array('IN',$new_photoids)))->order('id ASC')->find();
		}
		$attach_ids = $photoInfo['attachId'];
		if(empty($classId)){
			$app = 'public';
			$content = '我上传了'.$newphotoCount.'张新图片:【'.$photoInfo['name'].'】... ';
			$source_url = U('photo/Index/photo',array('id'=>$photoInfo['id'],'uid'=>$photoInfo['userId'],'aid'=>$photoInfo['albumId']));
		} else {
			$app = 'class';
			$content = "@".$GLOBALS['ts']['user']['uname'].' 上传了'.$newphotoCount.'张新图片:【'.$photoInfo['name'].'】到班级相册...';
			$source_url = U('class/ClassAlbum/classphoto',array('id'=>$photoInfo['id'],'uid'=>$photoInfo['userId'],'aid'=>$photoInfo['albumId'],cid=>$classId));
		}
		D('Photo', 'photo')->syncToFeed($app,$content,$source_url,$attach_ids,$this->mid,$classId);
		if(empty($classId)){
			$this->assign('jumpUrl',U('/Index/album',array(id=>$albumId,uid=>$this->mid)));
		}else{
			$this->assign('jumpUrl',U('/ClassAlbum/classalbum',array(id=>$albumId,cid=>$classId)));
		}
		$this->success('图片上传保存成功！');
	}
	/**
	 * 编辑图片
	 * @return void
	 */
	public function edit_photo_tab () {
		$map['id'] = intval($_REQUEST['pid']);
		$map['albumId'] = intval($_REQUEST['aid']);
		$map['userId'] = $this->mid;
		$map['is_del'] = 0;
		$photo = D('Photo', 'photo')->where($map)->find();
		if (!$photo) {
			echo "错误的相册信息！";
		}
		$this->assign('cId', intval($_REQUEST['cid']));
		$this->assign('nextId', intval($_REQUEST['nextid']));
		$this->assign('photo', $photo);
		$this->display();
	}	
	/**
	 * 创建班级相册专辑
	 * @return void
	 */
	public function create_classalbum_tab () {
		$isRefresh = intval($_GET['isRefresh']);
		$classId = intval($_GET['cid']);
		$this->assign('isRefresh', $isRefresh);
		$this->assign('classId',$classId);
		$this->display();
	}
	/**
	 * 创建班级相册
	 */
	public function do_create_classalbum(){
		$name = t(mStr(trim($_POST['name']),12,'utf-8',false));
		$classId = intval($_POST['cid']);
		if (strlen($name) == 0) {
			$this->error('相册名称不能为空');
		}
		$album = D('Album', 'photo');
		// 检测相册是否已存在
		$albumId = $album->getField('id', "classId={$classId} AND name='{$name}'");
		if($albumId) {
			$res['status'] = -1;
			$res['info'] = '相册已存在';
			exit(json_encode($res));
		}
	
		$album->cTime = time();
		$album->mTime = time();
		$album->classId = $classId;
		$album->name = $name;
		$album->privacy = 1;
	
		$result	= $album->add();
	
		if ($result) {
			$res['status'] = 1;
			$res['info'] = '创建成功';
			$res['data']['albumId'] = $result;
			$res['data']['albumName'] = $name;
			exit(json_encode($res));
		} else {
			$res['status'] = 0;
			$res['info'] = '创建失败';
			exit(json_encode($res));
		}
	}
	/**
	 * 删除班级相册
	 * @return void
	 */
	public function delete_class_album () {
		$map['id'] = intval($_REQUEST['id']);
		$map['classId'] =  intval($_REQUEST['cid']);
	
		$result	= D('Album', 'photo')->deleteAlbum($map['id'],$map['classId'],1);
		if ($result) {
			// 删除成功
			$this->assign('jumpUrl', U('/ClassAlbum/classalbums',array('cid'=>$map['classId'])));
			$this->success('删除相册成功~');
		} else {
			// 删除失败
			$this->error('删除失败~！');
		}
	}



}
