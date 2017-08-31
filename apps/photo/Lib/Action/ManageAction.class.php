<?php
/**
 * 相册应用 - ManageAction 编辑、维护 专辑和图片
 */
class ManageAction extends BaseAction {

	/**
	 * 初始化
	 * @return void
	 */
	public function _initialize () {
		$IsHotList = photo_IsHotList();
		$this->assign('IsHotList', $IsHotList);

		parent::_initialize();
	}
	
	/**
	 * 删除图片
	 * @return integer 是否删除成功
	 */
	public function delete_photo () {
		$map['id'] = intval($_REQUEST['id']);
		$map['albumId'] = intval($_REQUEST['albumId']);
		$map['userId'] = $this->mid;

		$result = D('Album', 'photo')->deletePhoto($map['id'], $this->mid);
		if ($result) {
			// 删除成功
			model('Credit')->setUserCredit($this->mid, 'delete_photo');
			echo 1;exit;
		} else {
			// 删除失败
			echo 0;exit;
		}
	}

	/**
	 * 相册管理
	 * @return void
	 */
    public function index () {
		$this->display();
    }

	/**
	 * 创建专辑
	 * @return void
	 */
	public function create_album_tab () {
		$isRefresh = intval($_GET['isRefresh']);
		$this->assign('isRefresh', $isRefresh);

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

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在相册创建了“".$_POST['name']."”的班级相册";
            $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["xc"]["code"],C("opType")["create"]['code'],$result,C("location")["localServer"]["code"],"","",$_POST['name'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

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
	 * 创建相册操作
	 * @return json 返回创建相册后的JSON数据
	 */
	public function do_create_album() {
		$name = t(mStr(trim($_POST['name']),12,'utf-8',false));

		$nameOrigin = $name;
		$sensitiveWord = $this->sensitiveWord_svc;
		$nameReplace = $sensitiveWord->checkSensitiveWord($name);
		$nameReplace = json_decode($nameReplace,true);
		if($nameReplace["Code"]!=0){
			return;
		}
		$name = $nameReplace['Data'];

		if (strlen($name) == 0) {
			$this->error('相册名称不能为空');
		}
		$album = D('Album', 'photo');
		// 检测相册是否已存在
		$albumId = $album->getField('id', "userId={$this->mid} AND name='{$name}'");
		if($albumId) {
			$res['status'] = -1;
			$res['info'] = '相册已存在';
			exit(json_encode($res));
		}

		$album->cTime = time();
		$album->mTime = time();
		$album->userId = $this->mid;
		$album->name = $name;
		$album->name_origin = $nameOrigin;
		$album->privacy = intval($_POST['privacy']);

		// 设置相册密码
		if (intval($_POST['privacy']) === 4) {
			$album->privacy_data = t($_POST['privacy_data']);
		}

		$result	= $album->add();

		if ($result) {

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在相册创建了“".$_POST['name']."”的相册";
            $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["xc"]["code"],C("opType")["create"]['code'],$result,C("location")["localServer"]["code"],"","",$_POST['name'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

			model('Credit')->setUserCredit($this->mid, 'add_album');
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
	 * 编辑专辑
	 * @return void
	 */
	public function album_edit () {
		$id = intval($_REQUEST['id']);
		$uid = $this->mid;
		// 获取相册信息
		$album = D('Album', 'photo')->where(" id='$id' AND userId='$uid' ")->find();
		$this->assign('album', $album);

		$albumlist = D('Album')->where(" userId='$uid' ")->findAll();
		$this->assign('albumlist', $albumlist);

		if (!$album) {
			$this->error('专辑不存在或已被删除！');
		} else {
			// 获取图片数据
			$map['albumId'] = $id;
			$photos = D('Photo')->where($map)->findAll();
			$this->assign('photos', $photos);
		}

		$this->display();
	}

	/**
	 * 保存相册编辑信息
	 * @return void
	 */
	public function do_update_album() {
		// 相册信息
		$albumId = intval($_POST['albumId']);
		$album_name = t($_POST['album_name']);
		$album_cover = intval($_POST['album_cover']);
		$album_privacy = intval($_POST['album_privacy']);
		$album_privacy_data = t($_POST['album_privacy_data']);

		$albumDao = D('Album', 'photo');
		$photoDao = D('Photo', 'photo');

		// 获取相册信息
		$albumInfo = $albumDao->where("id='$albumId' AND userId='$this->mid' ")->find();
		if (!$albumInfo) {
			$this->error('你没有权限编辑该相册！');
		}

		//解析图片数据
		foreach ($_POST['name'] as $k => $v) {
			$new_photos[$k]['name']	= t($v);
			$new_photoids[]	= t($k);
		}
		foreach ($_POST['move_to'] as $k => $v) {
			$new_photos[$k]['albumId'] = t($v);
		}

		// 对比原始数据，筛选出需要更新的图片
		$photo_ids['id'] = array('IN', $new_photoids);
		$old_photos = $photoDao->where($photo_ids)->findAll();
		foreach ($old_photos as $k => $v) {
			// 如果相册ID和名称都没变化，不需要保存
			$photoid = $v['id'];
			if ($v['albumId'] == $new_photos[$photoid]['albumId'] && $v['name'] == $new_photos[$photoid]['name']) {
				unset($new_photos[$photoid]);
			}
		}

		// 保存图片信息
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

		// 如果相册封面发生变化
		if ($albumInfo['coverImageId'] != $album_cover) {
			$album['coverImageId'] = $album_cover;
			if ($coverInfo = $photoDao->field('id, savepath')->find($album_cover)) {
				$album['coverImagePath'] = $coverInfo['savepath'];
			}
		}

		// 如果相册隐私发生变化
		if ($albumInfo['privacy'] != $album_privacy) {
			$album['privacy'] = $album_privacy;
			// 更新该相册下所有图片的隐私
			unset($map);
			$map['privacy'] = $album_privacy;
			$photoDao->where("albumId='$albumId'")->save($map);
		}

		// 如果相册隐私数据发生变化
		if ($albumInfo['privacy_data'] != $album_privacy_data) {
			$album['privacy_data'] = h(t($album_privacy_data));
		}

		$album['name'] = h(t(mStr($album_name,14,'utf-8',false)));

		$result = $albumDao->where("id='$albumId'")->save($album);

		// 跳转到相册页面
        $this->assign('jumpUrl', U('/Index/album',array(id=>$albumId,uid=>$this->mid)));
		$this->success('相册编辑成功！');
	}

	/**
	 * 图片排序
	 * @return void
	 */
	public function reorder_photos () {
		$this->display();
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
	 * 执行图片修改操作
	 * @return josn 返回修改后的JSON数据
	 */
	public function do_update_photo () {
		$id = intval($_REQUEST['id']);
		$map['albumId'] = intval($_REQUEST['albumId']);
		$map['name'] = h(t($_REQUEST['name']));

		$map['name_origin'] = $map["name"];
		// 过滤敏感词
		$sensitiveWord = $this->sensitiveWord_svc;
		$nameReplace = $sensitiveWord->checkSensitiveWord($map['name']);
		$nameReplace = json_decode($nameReplace,true);
		if($nameReplace["Code"]!=0){
			return;
		}
		$map['name'] = $nameReplace['Data'];

		$nextId = intval($_REQUEST['nextId']);
		$photoDao = D('Photo', 'photo');
		$albumDao = D('Album', 'photo');
		// 图片原信息
		$oldInfo = $photoDao->where("id={$id} AND userId={$this->mid}")->field('albumId')->find();
		// 更新信息
		$result = $photoDao->where("id={$id} AND userId={$this->mid}")->save($map);
		// 移动图片则重置相册图片数
		if ($map['albumId'] != $oldInfo['albumId']) {
			$albumDao->updateAlbumPhotoCount($map['albumId']);
			$albumDao->updateAlbumPhotoCount($oldInfo['albumId']);
		}
		// 返回
		if ($result) {
			$data['result'] = 1;
			$data['message'] = keyWordFilter($map['name']);
			exit(json_encode($data));
		} else {
			$data['result'] = 0;
			exit(json_encode($data));
		}
	}

	/**
	 * 设置封面
	 * @return integer 设置封面后的状态值
	 */
	public function set_cover () {
		$albumId = intval($_POST['albumId']);
		$photoId = intval($_POST['photoId']);

        $owner = D("Album")->checkOwner($this->mid,$albumId);
        if(!$owner){
            exit(json_encode(array("status"=>false,"info"=>"您没有该相册的设置权限！")));
        }
		$photo = D('Photo', 'photo')->where("id='$photoId' AND albumId='$albumId' ")->find();
		if ($photo) {
			$map['coverImageId'] = $photoId;
			$map['coverImagePath'] = $photo['savepath'];
			$result = D('Album', 'photo')->where(" id='$albumId' ")->save($map);
			if ($result) {
				// 设置成功
				echo "1";
			} else {
				// 设置失败 如果封面已是该图片 则也返回该值
				echo "0";
			}
		} else {
			// 该图片不存在
			echo "-1";
		}
	}

	/**
	 * 删除专辑
	 * @return void
	 */
	public function delete_album () {
		$map['id'] = intval($_REQUEST['id']);
		$map['userId'] = $this->mid;

		$result	= D('Album', 'photo')->deleteAlbum($map['id'],$this->mid);
		if ($result) {
			// 删除成功
			model('Credit')->setUserCredit($this->mid, 'delete_album');
			$this->assign('jumpUrl', U('/Index/albums'));
			$this->success('删除相册成功~');
		} else {
			// 删除失败
			$this->error('删除失败~！');
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
			$this->assign('jumpUrl', U('/Index/classalbums',array('cid'=>$map['classId'])));
			$this->success('删除相册成功~');
		} else {
			// 删除失败
			$this->error('删除失败~！');
		}
	}

	/**
	 * 图片排序
	 * @return void
	 */
	public function album_order () {
        $id = intval($_REQUEST['id']);
        $uid = $this->mid;

        //获取相册信息
        $album = D('Album', 'photo')->where(" id='$id' AND userId='$uid' ")->find();
        if (!$album) {
            $this->error('专辑不存在或已被删除！');
        }

        $map['albumId'] = $id;
        $map['userId'] = $uid;
        $map['is_del'] = 0;

        $photos = D('Photo', 'photo')->order('`order` DESC,id DESC')->where($map)->findAll();
        $this->assign('photos', $photos);

        $this->setTitle(getUserName($this->uid).'的相册：'.$album['name']);
        $this->assign('aid', $id);
        $this->assign('album', $album);
		$this->display();
	}

	/**
	 * 保存图片排序
	 * @return integer 返回保存后的状态值
	 */
	public function save_order () {
		$order = explode(',', $_POST['order']);
		$order = array_reverse($order);
		$id = intval($_POST['id']);
		$dao = D('Photo', 'photo');

		foreach ($order as $key => $value) {
			$condition['id'] = $value;
            $map['order'] = intval($key);
            $result = $dao->where($condition)->save($map);
		}
		echo 1;
	}
	
	/**
	 * by tkwang
	 * 相册名称进行验证;
	 */
	public function checkAlbumName(){
		$album = $_POST['name'];
		$id =$_POST['id'];
		if(empty($this->mid)){
			$result['status'] =501;
			$result['msg'] ='对不起,请您登陆';
		}
		if(empty($album)){
			$result['status'] =404;
			$result['msg'] ='请输入相册名称';
		}
		$count =D('Album', 'photo')->where(array('name'=>$album,'userId'=>$this->mid,'id'=>array('neq',$id)))->count();
		if($count>0){
			$result['status'] =500;
			$result['msg'] ='该相册名称您已经使用';
		}else{
            $album = D('Album', 'photo')->where(" id='$id' AND userId='$this->mid' ")->find();

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在相册修改了“".$album['name_origin']."”的相册";
            $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["xc"]["code"],C("opType")["update"]['code'],$result,C("location")["localServer"]["code"],"","",$album['name_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

			$result['status'] =200;
		}
		echo  json_encode($result);
	}
}