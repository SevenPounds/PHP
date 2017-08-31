<?php
class IndexAction extends AccessAction{
	
	/**
	 * 首页
	 */
	public function index(){
		$this->assign("modname",APP_NAME);
		$newcount = D("research")->countNewResearch($this->mid);
		$newcountdiv = '';
		$newcount <= 99 ? ($newcount > 0 ? $newcountdiv = '<div class="tip_circle01">'.$newcount.'</div>': $newcountdiv = '') : $newcountdiv = '<div class="tip_circle01">99+</div>';
		$this->newcountdiv = $newcountdiv;
		$this->display();
	}
	
	/**
	 * 我参加的课题
	 */
	public function follows(){
		//为查看主题的数量
		$newcount = D("research")->countNewResearch($this->mid);
		$newcountdiv = '';
		$newcount <= 99 ? ($newcount > 0 ? $newcountdiv = '<div class="tip_circle01">'.$newcount.'</div>': $newcountdiv = '') : $newcountdiv = '<div class="tip_circle01">99+</div>';
		$this->newcountdiv = $newcountdiv;
		$this->display();
	}
	
	/**
	 * 发起课题页面
	 */
	public function add(){
		
		// 当前登录用户相关的名师工作室
		$loginName = $this->user['login'];
		$MSGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($loginName);
		$this->MSGroups = $MSGroups;
		$this->assign('tag_num',5);
		$this->assign("modname",APP_NAME);
		$this->display();
	}
	
	/**
	 * 保存课题信息
	 */
	public function saveInfo(){
		
		// 当前登录用户id
		$mid = $this->mid;
		
		// 课题
		$res_name = t($_REQUEST['res_name']);
		// 课题介绍
		$res_des = safe($_REQUEST['res_des']);
		// 课题附件ids
		$attachIds = trim(t($_REQUEST['attachids']),'|');
		//权限设置参与人员 
		$accessType = isset($_REQUEST['accessType'])? intval($_REQUEST['accessType']):0;
		if(isset($_REQUEST['userids']) && $accessType){
			if(empty($_REQUEST['userids'])){
				$this->ajaxReturn('','还未选择研讨人员!',0);
			}
			$memberIds = trim(t($_REQUEST['userids']),'|');
		}
        // 是否同步到我的工作室
        $toSpace = isset($_REQUEST['to_space']) ? intval($_REQUEST['to_space']) : 0;
        // 名师工作室id
        $gids = empty($_POST['gids']) ? array() : $_POST['gids'];
        // 标签
        $tagIds = empty($_POST['tag_ids']) ? array() : explode(',',$_POST['tag_ids']);

		// 创建一个数组保存课题信息
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

		$data['type'] = "research";
		$data['accessType'] = $accessType == 1? 1 : 0;
        $data['to_space'] = $toSpace;
		// 把创建信息保存进数据库
		$res = D('Research')->createNewData($data,$attachIds,$memberIds,$gids,$tagIds);

        $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在主题讨论创建了“".$data['title_origin']."”的主题讨论";
        $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zttl"]["code"],C("opType")["create"]['code'],$res,C("location")["panServer"]['code'],"","",$data['title_origin'],$this->user["mid"],$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
        Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));

		if($res){
			$result = array("status"=>1,"data"=>$res);
		}else{
			$result = array("status"=>0,"data"=>$res);
		}
		
		exit(json_encode($result));
	}
	
	/**
	 * 课题详细页面
	 */
	public function show(){
		
		// 请求的课题id
		$id = t($_REQUEST['id']);
		
		// 实例化ResearchModel
		$Research = D('Research');
		
		// 课题信息
		$data = $Research->getResearchById($id,'*');
		
	
		//更新New状态
		$Research->updateNewStatus($data["id"],$this->mid);
		
		// 如果查询出错
		if(!$data || $data['is_del']==1){
			$this->error("你要访问的资源已删除!");
		}

		// 判断当前登录用户是否是课题创建者
		if($data['uid'] == $this->mid){
			$isCreator = TRUE;
		}else{
			$isCreator = FALSE;
		}
		
		//记录来访者
        recordVisitor($data['uid'], $this->mid);
	
		
		
		// 获取当前登录用户是否是课题的参与者
		$isParticipator = $Research->isExistsJoin($id,$this->mid);
		$this->isJoin = $isParticipator;
		
		if(intval($data['accessType'])==0){ //评课公开状态
			$isParticipator = TRUE;
		}
		
		
		// 判断当前登录用户的对当前应用的访问权限(家长和学生只有浏览的权限)
		$roleId = $GLOBALS['ts']['roleid'];
		if($roleId == UserRoleTypeModel ::PARENTS || $roleId == UserRoleTypeModel::STUDENT || !$isParticipator){
			
			$isVisitor = true;
			
		}else{
			$isVisitor = false;
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
		// 创建者名字
		$uname = model("User")->where("uid={$data['uid']}")->field('uname')->find();
		$this->uname = $uname['uname'];
		
		// 课题的成员信息
		$userList = D('Post')->getUserListByRid($id);
		
		// 获取课题附件信息
        $attachIdList = $Research->getAttachs('research',$id,true);
		$attachIdList = $Research->getAttachs('research',$id,false);
        $attachIdList = explode(",",$attachIdList);
        $attachList =array();
        foreach($attachIdList as $v){
            if(empty($v)){
                continue;
            }
            $file = $this->apis_client->getFile($v);
            $name = basename($file->url);
            $attach = ["attach_id" => $file->contextId,"name"=>$name];
           array_push($attachList,$attach);
        }

		// 获取存在的总结附件信息
		if($data['summary_attachid'] != 0){
			$sumAttach = $Research->getSumAttachs($data['summary_attachid']);
			$this->sumAttach = $sumAttach;
		}
		
		// 模板变量
		$this->data = $data;
		$this->isCreator = $isCreator;
		$this->isVisitor = $isVisitor;
		$this->userList = $userList;
		$this->attachList = $attachList;
		$this->cyInfo=$cyInfo;
		$this->rid = $id;

        //主题讨论
        $this->operationLog["actionName"]="researchShow";
        $this->operationLog["remark"]="主题讨论查看";
        model("OperationLog")->addOperationLog($this->operationLog);

		$this->display();
	}
	
	/**
	 * 发言
	 */
	public function createPost(){
		$data = array();
		$researchId = t($_REQUEST['res_id']);
        //原始内容
        $contentOrigin = $_REQUEST['content'];
        //敏感词检测
        $resultData = $this->sensitiveWord_svc->checkSensitiveWord($_REQUEST['content']);
        $resultData = json_decode($resultData, true);
        if ($resultData["Code"] != 0) {
            return;
        }
		$content = safe($_REQUEST['content']);
        $content = safe($resultData["Data"]);
		$attachIds = trim(t($_REQUEST['attach_ids']),'|');
		$recordId = $_REQUEST['record_id'];
		
		$mid  = $this->mid;
		
		// 获取当前登录用户是否是课题的参与者
		$isParticipator = D('Research')->isExistsJoin($researchId, $mid);
		// 课题信息
		$data = D('Research')->getResearchById($researchId,'*');
		// 如果用户已经被删除
		if (!$isParticipator&&$data['accessType']==1) {
			$result = array("status" => "400", "data" => "用户已被移出课题成员组!");
			exit(json_encode($result));
		}
        $targetUser = M("User")->getUserInfo($data['uid']);
		$res = D('Post')->createPostData($researchId, $mid, $content, $contentOrigin, $attachIds, $recordId);
		if ($res) {
			$result = array("status" => "200", "data" => $res);
			if($data['accessType']==0 && !$isParticipator){
				$result = array("status" => "250", "data" => $res);
			}

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在主题讨论评论了".$data["title_origin"]."”的主题讨论";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zttl"]["code"],C("opType")["reply"]['code'],$res,C("location")["localServer"]["code"],"","",$data["title_origin"],$this->user["uid"],$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment,$targetUser['uid']);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

            //主题讨论一级回复
            $conmment = $this->user['uname'].'评论了您发起的“'.$data["title"].'”主题讨论';
            $map =array(
                "userid" => $targetUser['cyuid'],
                "type" => "4",
                "content" => $conmment,
                "url" => C("SPACE").'index.php?app=research&mod=Index&act=show&id='.$researchId
            ) ;
            $list = array($map);
            $message = json_encode($list);
            $sendMsg = array(
                "timestamp"=>time(),
                "appId" => C("RESEARCH_APP_ID"),
                "message" => $message
            );

            Restful::sendMessage($sendMsg);

		} else {
			$result = array("status" => "500", "data" => $res);
		}
		
		exit(json_encode($result));
	}

	/**
	 * 编辑课题
	 */
	public function edit(){
		
		// 请求的课题id
		$id = t($_REQUEST['id']);
		
		// 课题信息
		$data = D('Research')->getResearchById($id,'*');
		// 如果查询出错或者访问此页面的不是创建者
		if(!$data || ($data['uid'] != $this->mid)){
			$this->error("你要访问的资源已删除!");
		}
		
		// 实例化ResearchModel
		$Research = D('Research');
		
		// 获取课题附件信息
		$attachIds = $Research->getAttachs('research',$id,false);
		// 已添加的成员信息
		$userIds = $Research->getUserListIds($id,$data['uid']);
		$userList = explode(",", $userIds);
        $gids= D('MSGroupTeachingApp','msgroup')->where(array('app_type'=>'research','appid'=>$id))->field('gid')->findAll();
        $msGroups = D("MSGroup","msgroup")->getMsGroupByLoginName($this->user['login']);
        // 模板变量
        $this->gids = getSubByKey($gids,'gid');
        $this->msGroups = $msGroups;
		$this->data = $data;
		$this->attachIds = $attachIds;
		$this->userList = $userList;
		$this->userids = $userIds;
		$this->accessType = $data['accessType'];
		$this->display();
	}
	
	/**
	 * 更新信息
	 */
	public function updateInfo(){
		
		// 当前登录的用户id
		$mid = $this->mid;

		// 课题id
		$researchId = t($_REQUEST['res_id']);
		// 标题
		$title = t($_REQUEST['res_title']);
		// 介绍
		$description = t($_REQUEST['res_des']);
		// 附件ids
		$attachIds = trim(t($_REQUEST['attachids']),'|');
		//评课成员uid数组
		$memberIds = trim(t($_REQUEST['userids']),'|');
		// 以前的成员ids
		$oldMemberIds = trim($_REQUEST['oldMemberIds']);
        // 是否同步到我的工作室
        $toSpace = isset($_REQUEST['to_space']) ? intval($_REQUEST['to_space']) : 0;
        // 名师工作室id
        $gids = empty($_POST['gids']) ? array() : $_POST['gids'];
        // 标签
        $tagIds = empty($_POST['tag_ids']) ? array() : explode(',',$_POST['tag_ids']);

		// 创建一个数组保存课题信息
		$data = array();
		$data['id'] = $researchId;
		$data['uid'] = $mid;

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

		$data['type'] = "research";
		$data['to_space'] = $toSpace;
        $researchInfo = D('Research')->getResearchById($researchId,'*');
		// 保存信息
		$res = D('Research')->updateRecordData($data,$attachIds,$memberIds,$oldMemberIds,$gids,$tagIds);
		
		if($res){

            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在主题讨论修改了“".$researchInfo['title_origin']."”的主题讨论";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zttl"]["code"],C("opType")["update"]['code'],$researchId,C("location")["localServer"]["code"],"","",$researchInfo['title_origin'],$this->user["mid"],$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

			$result = array("status"=>1,"data"=>$res);
		}else{
			$result = array("status"=>0,"data"=>$res);
		}
		exit(json_encode($result));
	}
	
	/**
	 * 结束课题弹出框
	 */
	public function edit_finish_tab(){
		$id = intval($_REQUEST['id']);
		$data = D('Research')->getResearchById($id,'*');
		$this->data = $data;
		$this->display();
	}
	
	/**
	 * 结束课题
	 */
	public function finish(){
		$research_id = t($_REQUEST['res_id']);
		$summaryids = trim(t($_REQUEST['summaryids']),'|');
		if(!empty($summaryids)){
			$summaryids = explode('|', $summaryids);
			array_map('intval', $summaryids);
		}
		$uid = $this->mid;
		$public_status = intval($_REQUEST['public']);
		$data = D('Research')->finishResearch($research_id,$summaryids[0],$public_status,$uid);
		if($data>0){
			$result = array("status"=>1,"data"=>$data);
		}else{
			$result = array("status"=>0,"data"=>$data);
		}
		exit(json_encode($result)); 
	}
	/**
	 * 主题中心
	 */
	public function center(){
		//查询主题中心数据
		$centerData = D('Research')->getResearchList('',-1,'','research');
		$this->centerData = $centerData;
		
		$cyuserdata = $this->cyuserdata;
		$province = $cyuserdata['locations']['province']['code'];//省
		$city = $cyuserdata['locations']['city']['code'];//市
		$district = $cyuserdata['locations']['district']['code'];//区县
		$subject = $GLOBALS['ts']['user']['subject'] ? $GLOBALS['ts']['user']['subject'] : "";
		
		//默认获取用户所在省市区和学科的列表
		$subjectList = D("Node")->subjects;
		$provinceList = D("CyArea")->listAreaByCode(1, "province");
		$cityList = D("CyArea")->listAreaByCode($province, "city");
		$districtList = D("CyArea")->listAreaByCode($city, "county");
		$this->assign("subjectList", $subjectList);
		$this->assign("provineList", $provinceList);
		$this->assign("cityList", $cityList);
		$this->assign("districtList", $districtList);
		//我参与的主题数量
		$newcount = D("research")->countNewResearch($this->mid);
		$newcountdiv = '';
		$newcount <= 99 ? ($newcount > 0 ? $newcountdiv = '<div class="tip_circle01">'.$newcount.'</div>': $newcountdiv = '') : $newcountdiv = '<div class="tip_circle01">99+</div>';
		$this->newcountdiv = $newcountdiv;
		
		$this->display();
	}
	/**
	 * ajax获取主题中心列表
	 */
	public function getResearchList(){
		$data = $_REQUEST['data'];
		$page = isset($_POST['p']) ? $_POST['p']: 1;
		$nav =$data['nav'];
		$tag = $data['tag'];
		$status = $data['status'];
		$keyword = $data['keyword'];
		$limit = 10;
		$centerData = D('Research')->getResearchList($tag,$status,$keyword,'research',$limit,$nav,$this->mid);
		$p = new AjaxPage(array('total_rows'=>$centerData['totalRows'],
				'method'=>'ajax',
				'ajax_func_name'=>'topic.loadData',
				'now_page'=>$page,
				'list_rows'=>$limit
		));

		// 显示教研应用列表页面分页
		$page = $p->showAppPager();

		$this->assign('page', $page);
		$this->assign("topiclist",$centerData['data']);

		$result = new stdClass();
		$result->status = 1;
		$result->data = $this->fetch("topic_list");
		exit(json_encode($result));
	}
	/**
	 * ajax获取参与和发起主题
	 */
	public function getCreateFollowTopic(){
		$uid = $this->mid;//当前登录用户uid
		$page = $_REQUEST['p']?$_REQUEST['p']:1;//页码
		$data = $_REQUEST['data'];//发起还是参与
		$type = $data['type'];//发起还是参与
		$keyword = $data['keyword'];//关键字
		$status = $data['status'];//状态
		$limit = 5;

		switch ($type){
			case 'C':
				$CAndFdata = D('Research')->getResearchByCuid($uid, 'research', $status, $keyword);
				$jsmethod = 'create';
				break;
			case 'F':
				$CAndFdata = D('Research')->getResearchByJuid($uid, 'research', $status, $keyword);
				$jsmethod = 'Follows';
				break;
		}
		$p = new AjaxPage(array('total_rows'=>$CAndFdata['totalRows'],
				'method'=>'ajax',
				'ajax_func_name'=>$jsmethod.'.loadData',
				'now_page'=>$page,
				'list_rows'=>$limit
		));

		// 显示教研应用列表页面分页
		$page = $p->showAppPager();
		$this->assign('page', $page);

		$this->totalcount = $CAndFdata['totalRows'];//记录总数
		$this->assign("CAndFdata",$CAndFdata['data']);
		$result = new stdClass();
		$result->status = 1;
		$result->sourcedata = $CAndFdata['data'];
		$result->data = $this->fetch("cf_list");
		exit(json_encode($result));
	}
	/**
	 * ajax获取区域信息
	 *
	 */
	public function getAreaList(){
		$areaCode = $_POST['areaCode'];
		$type = $_POST['type'];
		$areaList = D("CyArea")->listAreaByCode($areaCode,$type);
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
	 * 删除主题评论
	 */
	public function delpost(){
		$post_id=t($_REQUEST['post_id']);
		$uid=$this->mid;
		$data=D('Post')->deletePostById($post_id,$uid);
	}
	/**
	 * 删除主题
	 */
	public function deleteResearch(){
		$rid = t($_REQUEST['rid']);
		$uid = $this->mid;
		$data = D('Research')->deleteResearchByUid($rid, $uid);
		if($data == 0){
			$result = array('statuscode'=>'400','msg'=>'删除失败！');
		}else{
			$result = array('statuscode'=>'200','msg'=>'删除成功！');
		}
		exit(json_encode($result));
	}
	
	/**
	 * 通过Ajax方式获得评课的成员信息
	 */
	public function getMemberList(){
	
		$rid = isset($_POST['rid']) ? intval($_POST['rid']) : "";
		$ruid= isset($_POST['ruid']) ? intval($_POST['ruid']) : "";
		$page = isset($_POST['p']) ? $_POST['p'] : 1;
		$pageSize = 1000;//暂时去掉分页，一次获取一定量用户信息

		$userList = D("Post")->getUserListByRid($rid, $page, $pageSize);
		$this->assign("userList", $userList);
		$this->assign("mid",$this->mid);
		$this->assign("ruid",$ruid);
		$result = new stdClass();
		$result->status = 1;
		$result->data = $this->fetch("member_list");
	
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
		$s =D('AgreeBehaviour','research')->addBehaviour($post_id,$this->mid);
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
