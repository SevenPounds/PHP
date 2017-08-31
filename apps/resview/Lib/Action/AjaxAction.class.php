<?php
import ( APP_ACTION_PATH . '/baseAction/ResBaseAction.php' );
class AjaxAction extends ResBaseAction {
	
	/*
	 * 云盘服务
	 */
	private $diskClient = null;
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	protected function _initialize() {
		$this->diskClient = IflytekdiskClient::getInstance ();
	}
	
	/**
	 * 获取好友分组
	 */
	public function getGrouplist() {
		$usergroupList = model ( 'FollowGroup' )->getGroupList ( $this->mid );
		$grouplist = array ();
		foreach ( $usergroupList as $g ) {
			$group ['gid'] = $g ['follow_group_id'];
			$group ['title'] = $g ['title'];
			$grouplist [] = $group;
		}
		$defaultgroup = array (
				'gid' => - 2,
				'title' => '未分组' 
		);
		$grouplist [] = $defaultgroup;
		$html = "";
		foreach ( $grouplist as $gl ) {
			$html = $html . "<li>";
			$clickevent = 'expandlist(this,' . $gl ["gid"] . ');';
			$html = $html . '<img style="float:right;" src="apps/resview/_static/image/ep_toright.gif"></img>';
			$html = $html . '<i class="ico-at-group mr5" style="cursor:pointer;" onclick="' . $clickevent . '"></i>' . $gl ['title'];
			
			$html = $html . '<ul class="friendlist"></ul>';
			$html = $html . '<input class="hasclicked" type="hidden">';
			$html = $html . "</li>";
		}
		echo $html;
	}
	
	/**
	 * 获取好友列表
	 */
	public function getFriendlist() {
		$gid = intval ( $_GET ['gid'] );
		$groupuser = array ();
		if ($gid == - 1) {
			$kw = $_GET ['kw'];
			$groupinfo = model ( "Follow" )->getFollowingListAll ( $this->mid );
			/*
			 * $user = model('User')->getUserInfoForSearch("7" , 'uid,uname'); echo json_encode($user); return;
			 */
			foreach ( $groupinfo as $gu ) {
				$user = model ( 'User' )->getUserInfoForSearch ( $gu ['fid'], 'uid,uname' );
				if (stristr ( $user ['uname'], $kw )) {
					$groupuser [] = $user;
				}
			}
		} else {
			if ($gid == - 2) {
				$nogroupusers = model ( 'FollowGroup' )->getDefaultGroupByAll ( $this->mid );
				$groupinfo = getSubByKey ( $nogroupusers, 'fid' );
			} else {
				$groupinfo = model ( 'FollowGroup' )->getUsersByGroup ( $this->mid, $gid );
			}
			foreach ( $groupinfo as $gu ) {
				$groupuser [] = model ( 'User' )->getUserInfoForSearch ( $gu, 'uid,uname' );
			}
		}
		foreach ( $groupuser as $u ) {
			if ($u ['uid']) {
				$html = $html . '<li onclick=\'insertUser("' . $u ['uid'] . '","' . $u ['uname'] . '","' . $u ['avatar_small'] . '")\'>';
				$html = $html . '<a class="friendli" href="javascript:void(0);">';
				$html = $html . '<img id="choose_' . $u ['uid'] . '"' . ' style="float:right;margin-right:15px;display:none;" src="apps/resview/_static/image/ep_tick.gif"></img>';
				$html = $html . '<img style="margin-right:5px;" width="15" alt="' . $u ['uname'] . '" src="' . $u ['avatar_small'] . '">';
				$html = $html . $u ['uname'];
				$html = $html . '</a></li>';
			}
		}
		echo $html;
	}
	
	/**
	 * 收藏资源到云盘
	 */
	public function collect() {
		// 当前登录用户的登录账号
		if (empty ( $this->mid )) {
			$result ['status'] = '501';
			$result ['message'] = "请登录";
			echo json_encode ( $result );
			return;
		}
		// 资源id
		$resid = $_POST ['id'];
		$uid = $_POST ['uid'];
		$cyid ['mid'] = $GLOBALS ['ts'] ['user'] ['cyuid'];

		// 当前用户的角色
		$role = $this->CyCore->listRoleByUser ( $this->cymid );
		$isAllows =false;
		foreach ( $role as $keys ) {
			if ( in_array ( $keys->enName, array ('teacher','instructor') )) {
			 $isAllows =true;
			}
		}
		if(!$isAllows){
			$result ['status'] = '502';
			$result ['message'] = "操作无法完成，仅教师、教研员可以收藏资源";
			echo json_encode ( $result );
			return;
		}
		// 云盘是否初始化
		if (D ( "Yunpan", "yunpan" )->isInit ( $GLOBALS ['ts'] ['user']['login'] )) {
			D ( "Yunpan", "yunpan" )->init ( $cyid ['mid'],  $GLOBALS ['ts'] ['user']['login']  );
		}
		// 判断该资源是否正确存在 
		$resourceInfo = $this->diskClient->getFile ( $uid, $resid );
		if ($resourceInfo->hasError) {
			$result ['status'] = '503';
			$result ['message'] = '文件不存在';
			echo json_encode ( $result );
			return;
		}
		//获取我的文档的文件id
		$dir =$this->diskClient->getDirId($cyid ['mid'],0,'我的文档');
		//云盘拷贝资源
		$results=D('YunpanFile','yunpan')->copyFromOther($cyid ['mid'], $dir->obj->dirInfoVal->fid,$uid,$resid);
		//$results 判断保存结果;
		if ($results['statuscode']=='1') {
			$result ['total'] = 1;
			$result ['message'] = '保存成功';
			$result ['status'] = '504';
		} else {
			$result ['total'] = 1;
			$result ['message'] = '保存失败';
			$result ['status'] = '502';
		}
		echo json_encode ( $result );
	}
	
	/**
	 * 下载
	 */
	public function download() {
		set_time_limit ( 0 );
		session_write_close ();
		// 当前登录用户的登录账号
		if (empty ( $this->mid )) {
			$result ['status'] = 501;
			$result ['info'] = "请登录";
            exit (json_encode ( $result ));

		}
		
		$fileId = $_REQUEST ['resid'] ? trim ( $_REQUEST ['resid'] ) : '';
		$uid = $_REQUEST ['uid'] ? trim ( $_REQUEST ['uid'] ) : '';
		$return = array ();
		if (empty ( $fileId ) || empty ( $uid )) {
			$return ['status'] = 0;
			$return ['info'] = "请输入正确数据";
            exit (json_encode ( $return ));
		}
		$res = $this->diskClient->getFile ( $uid, $fileId );
        if(empty($res) || $res->obj->fileInfoVal->ishidden){
            $return ['status'] = 0;
            $return ['info'] = "文件不存在";
            exit (json_encode ( $return ));
        }
		$resurl = $this->diskClient->getFileUrl ( $uid, $fileId );
		$filename=$res->obj->fileInfoVal->aliasname;
		$extension=$res->obj->fileInfoVal->extension;
		//如果没有后缀名，添加后缀名称;
		$fileExtension = explode('.',$filename);
		if(!($fileExtension[count($fileExtension)-1]==$extension))
		{
			$filename=$filename.'.'.$extension;
		}
		if (!$resurl->hasError == 1) {
			$return ['status'] = 1;
			$return ['data'] = $resurl->obj->strVal . "?filename=" .encodedowloadUrl( $filename );
			//保存下载记录
			$resclient = new  ResourceClient();
			$gateresinfos =$resclient->Res_GetResIndex($fileId);
			$res = $gateresinfos->data[0];
			
			$type = !empty($res->properties->type)?$res->properties->type[0]:'0000';
			//保存下载记录
			$res_download = array(
					"fid" => $fileId,
					"dateline" => date('Y-m-d H:i:s',time()),
					"login_name" => $GLOBALS['ts']['user']['login'],
					"type" => $type,
					"download_source" => "02",
			);
			$downResult = D("YunpanDownload", "yunpan")->saveOrUpdate($res_download);
		} else {
			$return ['status'] = 0;
			$return ['info'] = $resurl->obj->errorInfo->msg;
		}
		echo (json_encode ( $return ));
	}
	
	/**
	 * 判断用户是否登录
	 * 2014-8-7
	 */
	public function isLogin() {
		if (empty ( $this->uid )) {
			$result ['status'] = "500";
			$result ['msg'] = "请登录";
			exit ( json_encode ( $result ) );
			return;
		} else {
			$result ['status'] = "200";
			$result ['msg'] = '已登录';
			exit ( json_encode ( $result ) );
			return;
		}
	}
	
	/**
	 * 同步资源分享
	 */
	public function share() {
		$body = trim ( $_POST ['body'] );
		$rid = $_POST ['rid'];
		$id = $_POST ['id'];
		// 当前登录用户的登录账号
		$login = $this->uid;
		
		if (empty ( $this->uid )) {
			$result ['status'] = '501';
			$result ['message'] = "请登录";
			echo json_encode ( $result );
			return;
		}
		if (empty ( $rid )) {
			$result ['status'] = '502';
			$result ['message'] = "请分享资源";
			echo json_encode ( $result );
			return;
		}
		if (empty ( $body )) {
			$result ['status'] = '504';
			$result ['message'] = "请填写动态内容";
			echo json_encode ( $result );
			return;
		}
		//填充分享语句
		$user_info =model("User")->getUserInfo($id);
		$cyuser_info = model("CyUser")->getUserByLoginName($user_info['login']);
		//获取资源名称
		$res = $this->diskClient->getFile ($cyuser_info['cyuid'], $rid );
		$body ='我分享了一个资源【'.$res->obj->fileInfoVal->aliasname.'】,'.$body;
		$result = $this->shareFeed ( $login, $body, $rid, $cyuser_info['cyuid'] );
		echo json_encode ( $result );
	}
	
	/**
	 * 同步资源发动态
	 */
	function shareFeed($login, $body, $rid, $id, $content, $app) {
		$uid = $this->uid;
		if ($uid) {
			$source_url = U ( 'resview/Resource/index', array (
					'id' => $rid,
					'uid' => $id 
			) );
			$data = array (
					"content" => $content,
					"body" => $body,
					"source_url" => $source_url 
			);
			$addFeed = D ( 'Feed' )->put ( $uid, $app, 'post', $data );
			if ($addFeed) {
				$result ['status'] = '200';
				$result ['message'] = '发表动态成功';
			} else {
				$result ['status'] = '500';
				$result ['message'] = '发表动态失败';
			}
		} else {
			$result ['status'] = '404';
			$result ['message'] = '此用户未开通个人空间！';
		}
		return $result;
	}
	
	/**
	 * 获取二维码
	 * 
	 * @return [type] [description]
	 */
	public function qrcode() {
		$dowloadurl = $_REQUEST ['url'];
		echo getQRcode ( $dowloadurl );
	}
	
	/**
	 * 下载资源时，进行记录相应信息
	 */
	public function recordResourceDownload(){
		$fileId = $_GET['resId'];
		//保存下载记录
		$resclient = new  ResourceClient();
		$gateresinfos =$resclient->Res_GetResIndex($fileId);
		$res = $gateresinfos->data[0];
		$type = empty($res->properties->type) ? '' :$res->properties->type[0];
		//保存下载记录
		$res_download = array(
				"fid" => $fileId,
				"dateline" => date('Y-m-d H:i:s',time()),
				"login_name" => $GLOBALS['ts']['user']['login'],
				"type" => $type,
				"download_source" => "01",
		);
		$downResult = D("YunpanDownload", "yunpan")->saveOrUpdate($res_download);
		return true;
	}
}