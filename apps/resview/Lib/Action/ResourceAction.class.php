<?php
import ( APP_COMMON_PATH . '/resGate_common.php' );
import ( APP_ACTION_PATH . '/baseAction/ResBaseAction.php' );
class ResourceAction extends ResBaseAction {
	/*
	 * 云盘服务
	 */
	private $diskClient = null;
	
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct ();
		$this->diskClient = IflytekdiskClient::getInstance ();
	}
	
	/**
	 * 资源预览首页
	 */
	public function index() {		
		$resid = $_GET ['id'];
		$cyuid = $_GET['uid'];

        //start 作品详细作者信息
        $cyUserInfo  = $this->CyCore->getUser($cyuid);
        if(!empty($cyUserInfo)){
            $createData = M('User')->getUserInfoByLogin($cyUserInfo->loginName);
            $createData['cyuid'] = $cyUserInfo->id;
            $createData['userData']=D("UserData")->getUserDataNoCache($createData['uid']);
            $follows = D ( "Follow" )->getFollowCount ( $createData ['uid'] );
            if(!empty($GLOBALS['ts']['mid'])){
                $createData ['hasFollow'] = $this->getFollowingState ( $GLOBALS['ts']['mid'], $createData ['uid'] );
            }else{
                $createData ['hasFollow']= array('following'=>0,'follower'=>0);
            }
            //用户简介
            if(empty($createData['intro'])){
                $user = model('CyUser')->getCyUserInfo($cyUserInfo->loginName);
                //支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
                $roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
                $createData ['instro'] = $roleEnName;
            }else{
                $createData ['instro'] = $createData['intro'];
            }
            //是否显示关注
            if (empty($GLOBALS['ts']['mid']) || $GLOBALS['ts']['user']['login'] != $createData ['login']) {
                $this->assign ( "showFollowBut", 'show' );
            }

        }else{
            $this->error('该用户不存在！');
        }
        //不安全资源的状态
         $insecurityArr =array("2", "3", "4");
        //资源信息
        $resourceInfo = $this->diskClient->getFile ( $cyuid, $resid );
        //资源的安全状态字段
        $isSecurity=$resourceInfo->obj->fileInfoVal->security;
        //不安全资源
        if(in_array($isSecurity,$insecurityArr)){
            $this->assign('jumpUrl', U('teachingapp/Index/index',array('uid'=>$createData['uid'])));
            $this->error('资源包含敏感信息，已被管理员屏蔽！');
        }
        //单个浏览资源
        if($resourceInfo->obj->fileInfoVal->ishidden || $resourceInfo->hasError){
            $this->assign('jumpUrl', U('teachingapp/Index/index',array('uid'=>$createData['uid'])));
            $this->error('资源已被删除！');
        }
        $resourceDetail['res_title']=$resourceInfo->obj->fileInfoVal->aliasname;
        $resourceDetail['dateline']=date("Y-m-d",$resourceInfo->obj->fileInfoVal->createtime/1000);
        $resourceDetail['fid']=$resourceInfo->obj->fileInfoVal->fid;
        $resourceDetail['length']=$resourceInfo->obj->fileInfoVal->length;
        $resourceDetail['extension']=$resourceInfo->obj->fileInfoVal->extension;
        $resourceDetail['description']=$resourceInfo->obj->fileInfoVal->description;
        $fileExtension = explode('.',$resourceDetail['res_title']);
        if(!($fileExtension[count($fileExtension)-1]==$resourceDetail['extension']))
        {
            $resourceDetail['res_title']=$resourceDetail['res_title'].'.'.$resourceDetail['extension'];
        }
        //资源信息
        $preview=  $this->diskClient->getPreview( $cyuid, $resid );
        $resourceDetail['previewurl'] = $preview->obj->strVal;
        $download =  D('YunpanFile','yunpan')->getFileUrl ( $cyuid, $resid );
        $resourceDetail['downloadurl']  = $download['data']->obj->strVal;


		//是否显示显示用户信息
		$this->assign ( "createData", $createData );
		$this->assign ( "showPersonInfo", 'show' );

		$this->assign ( "resourceDetail", $resourceDetail );

		$audio = array("mp3","wma","wav","ogg","ape","mid","midi");
            if(in_array($resourceDetail['extension'],$audio)){
            //视频地址
            $videoDetail['title'] = $resourceDetail['res_title'];
            $videoDetail['videoUrl'] =  $resourceDetail['previewurl'];
            $videoDetail['originalText'] = "";
            $videoDetail['segments'] = "";
            $videoDetail['description'] ="";
            $videoDetail['keywords'] = $_REQUEST['k']?$_REQUEST['k']:'';
            $videoDetail = json_encode($videoDetail,JSON_HEX_APOS);
            $this->assign('videoDetail',$videoDetail);
		}
		$this->display ();
	}
	
	/**
	 * 获取资源包资源列表
	 * 2014-8-4
	 */
	public function packageResList() {
		$resid = $_POST ['resid'];
		$uids=$_POST['uid'];
		$userInfo =model('User')->getUserInfo($uids);
		$cyuid =$this->CyCore->getUserByUniqueInfoInAll('login_name',$userInfo['login']);
		$dirs=$this->diskClient->getDir($cyuid,$resid);
		$limit = empty ( $_POST ["packageResLimit"] ) ? 10 : $_POST ["packageResLimit"];
		$packageResPageNow = empty ( $_POST ["packageResPageNow"] ) ? 1 : $_POST ["packageResPageNow"];
		$packageRes = $this->diskClient->listFilesAndDirs($this->cyuid,$resid,$packageResPageNow,$limit);
		foreach($packageRes as $key){
			$key->createtime=date("Y-m-d",$key->createtime/1000);
		}
		$packageResPage = getPaging ( $packageResPageNow, $dirs->obj->dirInfoVal->filecounts, $limit, 'queryPackageResByPage' );
		$this->assign ( "packageResPage", $packageResPage );
		$this->assign ( "packageRes", $packageRes);
		$this->assign ( "resid", $resid );
		$this->display ( APP_TPL_PATH . "/Resource/detail/packageResList.html" );
	}
	
	/**
	 * 根据用户登录名，获取用户的详细信息
	 *
	 * @param string $loginname        	
	 * @return 用户信息 2014-8-6
	 */
	private function getUserData($loginname) {
		if (empty ( $loginname )) {
			return false;
		} else {
			$user = D ( "User" )->getUserInfoByLogin ( $loginname );
			$user ['userData'] = D ( "UserData" )->getUserDataNoCache ( $user ['uid'] );
			return $user;
		}
	}
	
	/**
	 * 获取是否关注状态
	 *
	 * @param string $uid
	 *        	登录人uid
	 * @param string $fid
	 *        	关注人uid
	 * @return string 2014-8-6
	 */
	private function getFollowingState($uid, $fid) {
		return D ( "Follow" )->getFollowState ( $uid, $fid );
	}
	
	/**
	 * 用户关注好友或取消关注
	 * 2014-8-6
	 * 关注操作 type=1 取消关注 type=2
	 */
	public function followOrUnfollowUser() {
		$type = $_POST ["type"];
		if (empty ( $this->mid )) {
			$res ["state"] = "500";
			$res ['msg'] = "请登录";
		} else {
			if (empty ( $_POST ['tid'] )) {
				$res ["state"] = "501";
				exit ( json_encode ( $res ) );
			}
			$userInfo = model ( 'User' )->getUserInfo ( $this->mid );
			
			// 关注
			$isSuccess = false;
			if ($type == 1) {
				$isSuccess = $this->spaceFollow ( 1, $_POST ['tid'], $userInfo ['login'] );
				if ($isSuccess) {
					$res ["state"] = "502";
					$res ["msg"] = "关注成功";
				} else {
					$res ["state"] = "501";
					$res ["msg"] = "关注失败";
				}
			} else if ($type == 2) {
				// 取消关注
				$isSuccess = $this->spaceUnfollow ( 1, $_POST ['tid'], $userInfo ['login'] );
				if ($isSuccess) {
					$res ["state"] = "502";
					$res ["msg"] = "取消关注成功";
				} else {
					$res ["state"] = "501";
					$res ["msg"] = "取消关注失败";
				}
			}
		}
		exit ( json_encode ( $res ) );
	}
	
	/**
	 * 关注空间
	 *
	 * @param int $type
	 *        	搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
	 * @param int $tid
	 *        	目标id
	 * @param int $login_name
	 *        	关注者登陆用户名
	 */
	function spaceFollow($type = 1, $tid, $login_name) {
		$uid = null;
		if (! empty ( $login_name )) {
			$uid = D ( "User" )->where ( array (
					'login' => $login_name 
			) )->getField ( "uid" );
		}
		switch (intval ( $type )) {
			// 关注用户
			case ZoneTypeModel::TEACHER :
			case ZoneTypeModel::RESEARCHER :
			case ZoneTypeModel::STUDENT :
			case ZoneTypeModel::PARENTS :
				return D ( 'Follow' )->doFollow ( $uid, $tid, 0 );
				break;
			// 关注名师工作室
			case ZoneTypeModel::STUDIO :
				return D ( 'Follow' )->doFollow ( $uid, $tid, 3 );
				break;
			default :
				return null;
				break;
		}
	}
	/**
	 * 取消关注
	 *
	 * @param int $type
	 *        	搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
	 * @param int $tid
	 *        	目标id
	 * @param int $login_name
	 *        	关注者登陆用户名
	 */
	function spaceUnfollow($type = 1, $tid, $login_name) {
		$uid = null;
		if (! empty ( $login_name )) {
			$uid = D ( "User" )->where ( array (
					'login' => $login_name 
			) )->getField ( "uid" );
		}
		switch ($type) {
			// 关注用户
			case ZoneTypeModel::TEACHER :
			case ZoneTypeModel::RESEARCHER :
			case ZoneTypeModel::STUDENT :
			case ZoneTypeModel::PARENTS :
				return D ( 'Follow' )->unFollow ( $uid, $tid, 0 );
				break;
			// 关注名师工作室
			case ZoneTypeModel::STUDIO :
				return D ( 'Follow' )->unFollow ( $uid, $tid, 3 );
				break;
			default :
				return null;
				break;
		}
	}
}
