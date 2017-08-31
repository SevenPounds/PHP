<?php
class IndexAction extends AccessAction{
	
	/**
	 * 初始化信息
	 */
	public function _initialize(){
		if(in_array(ACTION_NAME,array('center','index','follows'))){
			$newcount =  model('PingkeMember')->getNewDataCount($this->mid);
			$newcount = intval($newcount) >99 ? 99 :$newcount;
			$this->newdiv = $newcount > 0 ?"<div class='tip_circle01'>{$newcount}</div>" :'';
		}
	}
	
	
	/**
	 * 首页
	 */
	public function index(){
		$this->display();
	}
	
	/**
	 * Ajax获取我发起的和参与的评课
	 */
	public function getPingkeAjaxList(){
		$page = isset($_POST['p']) ? $_POST['p']: 1;
		//1:我发起的评课 2：我参与的评课
		$type = isset($_POST['type']) ? $_POST['type']: 1;
		$status = isset($_POST['status']) ? $_POST['status']: -1;
		$keywords = isset($_POST['keywords']) ? $_POST['keywords']: '';
		$status=="" && $status = -1;
	
		!in_array($type, array(1,2)) && $type = 1;
		$pageSize = 5;
		// 查询出我发起的评课
		$pingkeList = D('Pingke')->getMyPingkeList($this->mid, $type, $page, $pageSize, 'id desc',$keywords,intval($status));		$pingkeCount = D('Pingke')->getMyPingkeListCount($this->mid, $type,$keywords,$status);
		$p = new AjaxPage(array('total_rows'=>$pingkeCount,
				'method'=>'ajax',
				'ajax_func_name'=>'loadData',
				'now_page'=>$page,
				'list_rows'=>$pageSize
		));

		// 显示教研应用列表页面分页
		$page = $p->showAppPager();
	
		$this->assign("pk_status", $status);
		$pingkeCount = intval($pingkeCount) > 99 ? '99+':$pingkeCount;
		
		$this->assign("pingkeList", $pingkeList);
		$this->assign("pingkeCount", $pingkeCount);
		$this->assign("page", $page);

		$result = new stdClass();
		$result->status = 1;
		$result->data = $this->fetch("pingke_list");
		exit(json_encode($result));
	}
	
	/**
	 * 我参加的评课
	 */
	public function follows(){
		// 查询出我参加的评课
		$this->display();
	}
	
	/**
	 * 发起评课页面
	 */
	public function add(){
		// 当前登录用户相关的名师工作室
		$loginName = $this->user['login'];
		$MSGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($loginName);
		$this->MSGroups = $MSGroups;
		
		$this->display();
	}
	
	/**
	 * 保存评课信息
	 */
	public function saveInfo(){
		
		//当前登录用户id
		$mid = $this->mid;
		//评课
		$res_name = htmlspecialchars($_REQUEST['res_name']);
		//评课介绍
		$res_des = htmlspecialchars($_REQUEST['res_des']);
		
		//评课视频id
		$video_id = isset($_REQUEST['video_id']) ? $_REQUEST['video_id'] : '';
        $video_id = trim($video_id,'|');
		
		// 评课未转换格式的视频的id
		$context_id = isset($_REQUEST['context_id']) ? $_REQUEST['context_id'] : '';
		$context_id = trim($context_id,'|');

       if(!$video_id){
            exit(json_encode(array("status"=>0,"data"=>'未上传视频！')));
        }
        $toSpace = isset($_REQUEST['to_space']) ? intval($_REQUEST['to_space']) : 0;
		//评课教师
		$teacher_name = t($_REQUEST['teacher_name']);
		
		// 名师工作室id
        $gids = empty($_POST['gids']) ? array() : $_POST['gids'];

		//参与权限类型  chengcheng3
		$accessType = isset($_REQUEST['accessType']) ? intval($_REQUEST['accessType']) : 0;
		$memberAry = array();
		if($accessType==1){
			//评课成员uid数组
			$memberIds = $_REQUEST['userids'];
			$memberAry = explode("|", $memberIds);
			$memberAry = array_filter($memberAry);
			empty($memberAry) && exit(json_encode(array("status"=>0,"data"=>'未指定参与人员！')));
		}
		
	
		//创建一个数组保存评课信息
		$data = array();
		$data['uid'] = $mid;
		$data['title'] = $res_name;
		$data['description'] = $res_des;

        $titleData = $this->sensitiveWord_svc->checkSensitiveWord($res_name);
        $titleData = json_decode($titleData, true);
        if ($titleData["Code"] != 0) {
            return;
        }
        $data['title'] = $titleData["Data"];
        $data['title_origin'] = $res_name;

        $desData = $this->sensitiveWord_svc->checkSensitiveWord($res_des);
        $desData = json_decode($desData, true);
        if ($desData["Code"] != 0) {
            return;
        }
        $data['description'] = $desData["Data"];
        $data['description_origin'] = $res_des;

        $teacherData = $this->sensitiveWord_svc->checkSensitiveWord($teacher_name);
        $teacherData = json_decode($teacherData, true);
        if ($teacherData["Code"] != 0) {
            return;
        }
        $data['teacher'] = $teacherData["Data"];
        $data['teacher_origin'] = $teacher_name;
		$data['teacher'] = $teacher_name;
		$data['video_id'] = $video_id;
		$data['context_id'] = $context_id;
		$cyUserData = $this->cyuserdata;
		$data['province'] = $cyUserData['locations']['province']['id'];//省
		$data['city'] = $cyUserData['locations']['city']['id'];//市
		$data['county'] = $cyUserData['locations']['district']['id'];//区县
		$data['subject'] = $GLOBALS['ts']['user']['subject'];
        $data['to_space'] = $toSpace;
		$data['accessType'] = $accessType==1 ? 1 : 0;
        $data['gid'] = '';

		// 把创建信息保存进数据库
		$res = D('Pingke')->createPingke($data, $memberAry, $gids);
		
		if($res){

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在网上评课发起了“".$res_name."”的网上评课";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["wspk"]["code"],C("opType")["create"]['code'],$res,C("location")["localServer"]["code"],"","",$res_name,$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

			$result = array("status"=>1,"data"=>$res);
		}else{
			$result = array("status"=>0,"data"=>$res);
		}
		exit(json_encode($result));
	}

	/**
	 * 评课详细页面
	 */
	public function show(){
		//请求的评课id
		$id = t($_REQUEST['id']);
		//更新new状态
		model('PingkeMember')->updateNewTag($id, $this->mid);

        //网上评课
        $this->operationLog["actionName"]="pingkeShow";
        $this->operationLog["remark"]="网上评课";
        model("OperationLog")->addOperationLog($this->operationLog);


        //实例化PingkeModel
		$pingkeModel = D('Pingke');
		//评课信息
		$data = $pingkeModel->getPingKeDetails($id);
		//如果查询出错
		if(!$data || $data['is_del']==1){
			$this->error("你要访问的资源已删除!");
		}

        $videoInfo = json_decode($this->apis_client->getConvert($data['video_id'])->results);
        if(!isset($videoInfo)){
            $videoInfo = $this->apis_client->getFile($data['context_id']);
        }

		$basename = basename($videoInfo->url);
		$videoInfo->download_url = $videoInfo->url . "?filename={$basename}";

		//判断当前登录用户是否是评课创建者
		if($data['uid'] == $this->mid){
			$isCreator = TRUE;
		}else{
			$isCreator = FALSE;
		}
        recordVisitor($data['uid'],$this->mid);
	
		// 获取当前登录用户是否是评课的参与者
		$pingke_member = D("PingkeMember")->where(array("pingke_id"=>$id,"uid"=>$this->mid))->find();
		$isParticipator = FALSE;
		if(!empty($pingke_member) || $data['uid'] == $this->mid){
			$isParticipator = TRUE;
		}
		$this->isJoin = $isParticipator;
		
		if(intval($data['accessType'])==0){ //评课公开状态
			$isParticipator = TRUE;
		}
		
		// 判断当前登录用户的对当前应用的访问权限(家长和学生只有浏览的权限)
		$roleId = $GLOBALS['ts']['roleid'];
		if($roleId == UserRoleTypeModel ::PARENTS || $roleId == UserRoleTypeModel::STUDENT || !$isParticipator){
			$isVisitor = TRUE;
			// 创建者名字
			$uname = model("User")->where("uid={$data['uid']}")->field('uname')->find();
			$this->uname = $uname['uname'];
		}else{
			$isVisitor = FALSE;
		}
		
		//创建者信息
		$cyInfo = D("User")->getUserInfo($data['uid']);
		$orgInfo = D("CyUser")->getCyUserInfo($cyInfo['login']);
		if(isset($orgInfo['orglist']['school'])){
			foreach($orgInfo['orglist']['school'] AS $school){
				$cyInfo['orgName'] = $school['name'];
				break;
			}
		}
		
		// 获取评课附件信息
		$video_id = $data['video_id'];
		$video_info = D("Resource", "reslib")->fetchByRid($video_id);
		// 获取存在的总结附件信息
		if($data['summary_attachid'] != 0){
			$sumAttach = D("Attach")->getAttachById($data['summary_attachid']);
			$this->sumAttach = $sumAttach;
		}
		$data['member_count'] += 1;
		// 模板变量
		$this->data = $data;
		$this->videoInfo = $videoInfo;
		$this->isCreator = $isCreator;
		$this->isVisitor = $isVisitor;
		$this->video_info = $video_info;
		$this->cyInfo=$cyInfo;
		$this->rid = $id;
		$this->display();
	}
	/**
	 * 通过Ajax方式获得评课的成员信息
	 */
	public function getMemberList(){
		
		$pingkeId = isset($_POST['pingke_id']) ? intval($_POST['pingke_id']) : "";
		$page = isset($_POST['p']) ? $_POST['p'] : 1;
		$pageSize = 1000;//暂时去掉分页，一次获取一定量用户信息
		// 评课的成员信息
		$userList = D("PingkeMember")->getMemberList($pingkeId, $page, $pageSize);

		$this->assign("userList", $userList);
		
		$result = new stdClass();
		$result->status = 1;
		$result->data = $this->fetch("member_list");
		
		exit(json_encode($result));
		
	}
	
	/**
	 * 发言
	 */
	public function createPost(){
		$attachIds = trim(t($_REQUEST['attach_ids']),'|');
		$attachIds= explode("|", $attachIds);
		$attachIds = array_filter($attachIds);

		$mid  = $this->mid;

		$data = array();
		$data['pingke_id'] = t($_REQUEST['res_id']);
		$data['uid'] = $mid;
        //原始内容
        $data['content_origin'] = $_REQUEST['content'];
        //敏感词检测
        $resultData = $this->sensitiveWord_svc->checkSensitiveWord($_REQUEST['content']);
        $resultData = json_decode($resultData, true);
        if ($resultData["Code"] != 0) {
            return;
        }
        $data['content'] = safe($resultData["Data"]);
		$data['content'] = safe($_REQUEST['content']);
		$data['record_id'] = $_REQUEST['record_id'];
		$data['attach_id'] = $attachIds;
		
		$pingke = M('pingke')->find(intval($data['pingke_id']));
		empty($pingke) && exit(json_encode(array("status"=>"500","info"=>"评课不存在")));	//评课不存在
		$pingke['status'] == '0' &&  exit(json_encode(array("status"=>"500","info"=>"评课已结束")));	//已结束
		
		if ($pingke['uid'] != $mid ) {
			$member = M('pingke_member')->where(array('pingke_id'=>$data['pingke_id'], 'uid'=>$mid))->select();
			if (empty($member) && $pingke['accessType']==1) 
				exit(json_encode(array("status"=>"500","info"=>"对不起，您不在指定成员列表中！")));	//用户不在成员列表
		}
		$res = D('PingkePost')->createPost($data);
		
		if($pingke['uid'] != $mid && $pingke['accessType']==0 && $res){
			//如是新成员发表评论，则将其添加到PingkeMember，同时更新Pingke中member_count数目
			$count = D('PingkeMember')->where(array('pingke_id' => $data['pingke_id'],'uid'=>$mid))->count();
			if($count<=0){
				D('PingkeMember')->add(array('pingke_id' => $data['pingke_id'],'uid'=>$mid,'discuss_count'=>0,'is_new'=>0));
				D('Pingke')->setInc('member_count','id='.intval($data['pingke_id']),1);
			}
		}

        $targetUser = M("User")->getUserInfo($pingke["uid"]);

		if($res){
			$result = array("status"=>"200","data"=>$res);
			if($pingke['accessType']==0 && empty($member) && $pingke['uid'] != $mid){
				$result = array("status" => "250", "data" => $res);
			}

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在网上评课评论了“".$pingke['title_origin']."”的网上评课";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["wspk"]["code"],C("opType")["reply"]['code'],$res,C("location")["localServer"]["code"],"","",$data['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment,$pingke["uid"]);
            Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));

            //网上评课  一级 回复 消息记录
            $conmment = $this->user['uname'].'评论了您发起的“'.$pingke['title'].'”网上评课';
            $map =array(
                "userid" => $targetUser['cyuid'],
                "type" => "4",
                "content" => $conmment,
                "url" => C("SPACE").'index.php?app=pingke&mod=Index&act=show&id='.$data['pingke_id']
            ) ;
            $list = array($map);
            $message = json_encode($list);
            $sendMsg = array(
                "timestamp"=>time(),
                "appId" => C("PINGKE_APP_ID"),
                "message" => $message
            );
            Restful::sendMessage($sendMsg);

		}else{
			$result = array("status"=>"500","data"=>$res);
		}
		
		exit(json_encode($result));
	}

	/**
	 * 编辑评课
	 */
	public function edit(){
		
		// 请求的评课id
		$pingkeId = t($_REQUEST['id']);
		
		// 评课信息
		$data = D('Pingke')->getPingKeDetails($pingkeId);
		
		// 如果查询出错或者访问此页面的不是创建者
		if(!$data || ($data['uid'] != $this->mid)){
			$this->error("你要访问的资源已删除!");
		}
		// 已添加的成员信息
		$userList = D("PingkeMember")->where(array("pingke_id"=>$pingkeId))->Field("uid")->select();
		$userIds = array();
		foreach ($userList AS $_uid){
			array_push($userIds, $_uid['uid']); 
		}
        $gids= D('MSGroupTeachingApp','msgroup')->where(array('app_type'=>'pingke','appid'=>$pingkeId))->field('gid')->findAll();
        $msGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($this->user['login']);

        // 模板变量
        $this->gids = getSubByKey($gids,'gid');
        $this->msGroups = $msGroups;
		$this->data = $data;
		$this->userids = $userIds;
		$this->display();
	}
	
	/**
	 * 更新信息
	 */
	public function updateInfo(){
		
		// 当前登录的用户id
		$mid = $this->mid;
		// 评课id
		$researchId = t($_REQUEST['res_id']);
		// 标题
		$title = t($_REQUEST['res_title']);
		// 介绍
		$description = t($_REQUEST['res_des']);
		// 附件id
		$video_id = t($_REQUEST['video_id']);
        $video_id = trim($video_id,'|');
        if(!$video_id){
            exit(json_encode(array("status"=>0,"data"=>'未上传视频！')));
        }
        $toSpace = isset($_REQUEST['to_space']) ? intval($_REQUEST['to_space']) : 0;
		// 上课教师
		$teacher_name = t($_REQUEST['teacher_name']);
        // 名师工作室id
        $gids = empty($_POST['gids']) ? array() : $_POST['gids'];
		
		//参与权限类型  chengcheng3
		$accessType = isset($_REQUEST['accessType']) ? intval($_REQUEST['accessType']) : 0;
		$memberAry = array();
		if($accessType==1){
			//评课成员uid数组
			$memberIds = $_REQUEST['userids'];
			$memberAry = explode("|", $memberIds);
			$memberAry = array_filter($memberAry);
			empty($memberAry) && exit(json_encode(array("status"=>0,"data"=>'未指定参与人员！')));
		}
		
		// 创建一个数组保存评课信息
		$data = array();
		$data['id'] = $researchId;
		$data['uid'] = $this->mid;

        $titleData = $this->sensitiveWord_svc->checkSensitiveWord($title);
        $titleData = json_decode($titleData, true);
        if ($titleData["Code"] != 0) {
            return;
        }
        $data['title'] = $titleData["Data"];
        $data['title_origin'] = $title;

        $desData = $this->sensitiveWord_svc->checkSensitiveWord($description);
        $desData = json_decode($desData, true);
        if ($desData["Code"] != 0) {
            return;
        }
        $data['description'] = $desData["Data"];
        $data['description_origin'] = $description;

        $teacherData = $this->sensitiveWord_svc->checkSensitiveWord($teacher_name);
        $teacherData = json_decode($teacherData, true);
        if ($teacherData["Code"] != 0) {
            return;
        }
        $data['teacher'] = $teacherData["Data"];
        $data['teacher_origin'] = $teacher_name;
		$data['video_id'] = $video_id;
        $data['to_space'] = $toSpace;
		$data['accessType'] = $accessType==1 ? 1 : 0;
        $pingke = D('Pingke')->getPingKeDetails($researchId);
		// 保存信息
		$res = D('Pingke')->updatePingke($data, $memberAry, $gids);
		
		if($res){
            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在网上评课修改了“".$pingke['title_origin']."”的网上评课";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["wspk"]["code"],C("opType")["update"]['code'],$researchId,C("location")["localServer"]["code"],"","",$pingke['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

			$result = array("status"=>1,"data"=>$res);
		}else{
			$result = array("status"=>0,"data"=>$res);
		}
		exit(json_encode($result));
	}
	
	/**
	 * 结束评课弹出框
	 */
	public function edit_finish_tab(){
		$id = intval($_REQUEST['id']);
		$data = D('Pingke')->getPingKeDetails($id);
        $data['member_count'] += 1;
		$this->data = $data;
		$this->display();
	}
	
	/**
	 * 结束评课
	 */
	public function finish(){
		$pingkeId = t($_REQUEST['res_id']);
		$summaryId = trim(t($_REQUEST['summaryids']),'|');
		$public_status = intval($_REQUEST['public']);
		
		$data = array();
		$data['id'] = $pingkeId;
		$data['uid'] = $this->mid;
		$data['summary_attachid'] = $summaryId;
		$data['public_status'] = $public_status;
		//结束评课
		$data['status'] = 0;
		
		$data = D('Pingke')->updatePingke($data,"","",true);
		if($data>0){
			$result = array("status"=>1,"data"=>$data);
		}else{
			$result = array("status"=>0,"data"=>$data);
		}
		exit(json_encode($result)); 
	}
	/**
	 * 删除评课评论
	 */
	public function delpost(){
		$post_id=t($_REQUEST['post_id']);
		$uid=$this->mid;
		$data=D('PingkePost')->deletePostByPostid($post_id,$uid);
	}
	/**
	 * 删除评课
	 */
	public function deletePost(){
		$pinke_id=t($_REQUEST['pinke_id']);
		$data = D('Pingke')->deletePingkeByUid($this->mid,$pinke_id);
		if($data == 0){
			$result = array('statuscode'=>'400','msg'=>'删除失败！');
		}else{
			$result = array('statuscode'=>'200','msg'=>'删除成功！');
		}
		exit(json_encode($result));
	}
		
	/**
	 * 网上评课中心
	 */
	public function center(){
        //默认获取用户所在省市区和学科的列表
        $subjectList = D("Node")->subjects;
        $provinceList = D("CyArea")->listAreaByCode(1, "province");

        $this->assign("subjectList", $subjectList);
        $this->assign("provineList", $provinceList);

        $this->display("center");
	}
	
	/**
	 * 网上评课中心数据获取（Ajax方式调用）
	 * @param int $nav  选择状态. 0=>最新评课（时间逆序）,1=>最热评课(评论数),2=>精华评课,3=>我关注的评课;
	 */
	public function getDataList(){
		
		$condition = array();
		$nav =isset($_POST['nav'])?$_POST['nav']:0;
		$keywords = $_POST['keywords'];
		$status = isset($_POST['status']) ? $_POST['status'] : -1;
		$pageNum = isset($_POST['p']) ? $_POST['p'] : 1;
		$pageSize = 10;
		//$nav  选择状态. 0=>最新评课（时间逆序）,1=>最热评课(评论数),2=>精华评课,3=>我关注的评课;
		switch($nav){
			case 0:
				$order ="p.createtime DESC,p.discuss_count DESC,p.member_count DESC ";
				$condition['isHot'] = array('neq',2);
				break;
			case 1:
				$order ="p.discuss_count DESC,p.member_count DESC,p.createtime DESC";
				$condition['isHot'] = array('neq',2);
				break;
			case 2:
				$order ="p.discuss_count DESC,p.member_count DESC,p.createtime DESC";
				$condition['isHot'] = 2;
				break;
			case 3:
				$order ="p.createtime DESC";
				$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
				$in_arr = getSubByKey($in_arr,'fid');
				$condition['uid'] =  array('in',$in_arr);
				break;
		}
		$pingkeList = D("Pingke")->searchPingke($condition, $keywords, $status,  $pageNum, $pageSize,$order);
		$pingkeListCount = D("Pingke")->searchPingkeCount($condition, $keywords, $status);
				//ajax分页
		$p = new AjaxPage(array('total_rows'=>$pingkeListCount,
								'method'=>'ajax',
								'ajax_func_name'=>'pingke.page',
								'now_page'=>$pageNum,
								'list_rows'=>$pageSize
								));
		
		// 显示教研应用列表页面分页
		$page = $p->showAppPager();

		$this->assign('page', $page);
		$this->assign("pingkeList", $pingkeList);

		$result = new stdClass();
		$result->status = 1;
		$result->data = $this->fetch("data_list");
		exit(json_encode($result));
	}
	/**
	 * 网上评课中心获取区域信息
	 * 
	 */
	public function getAreaList(){
		$areaId = $_POST['areaId'];
		$type = $_POST['type'];
		$areaList = D("CyArea")->listAreaByCode($areaId,$type);
		$result = new stdClass();
		if($areaList){
			$result->status = 1;
			$result->data = $areaList;
		}else{
			$result->status = 0;
		}
		exit(json_encode($result));
	}
	
	/**
	 *  一级回复赞功能
	 */
	public function addAgree(){
		//检测用户是否登录
		if(empty($this->mid)){
			$return['status'] ='400';
			$return['msg'] ='回复成功';
			exit(json_encode($return));
		}
	
		$post_id =$_POST['post_id'];
		$s =D('AgreeBehaviour','pingke')->addBehaviour($post_id,$this->mid);
		if($s){
			$return['status'] ='200';
			$return['msg'] ='点赞成功';
		}else{
			$return['status'] ='500';
			$return['msg'] ='点赞失败，请稍后再试';
		}
		echo json_encode($return);
	}
	
}
?>
