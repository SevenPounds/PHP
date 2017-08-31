<?php
/**
 * 公开课控制器
 * @author "trwang"
 *
 */
class LessonAction extends Action {
	// 公开课服务
	private $lesson_service;
	
	/**
	 * 构造方法
	 */
	public function __construct() {
		parent::__construct ();
		$this->lesson_service = new lessonApp_Client ( C ( 'LESSON' ) . "index.php?m=Api&c=Server" );
	}
	
	/**
	 * 我的公开课
	 */
	public function index() {
		$this->appCssList [] = 'zone.css';
		if (empty ( $_SESSION ['mid'] )) {
			redirect ( C ( 'LOGIN_URL' ) );
		}
		$this->assign("types",getRecordTypes());
		$lessonUrl=C('LESSON');
		$this->assign ( "lessonUrl", $lessonUrl );
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
		$pre_url=C('LESSON');
		$this->assign("pre_url",$pre_url);
		if ($roleEnName == 'teacher') {
			$condition=array("host"=>$cyuid,"page"=>1,"limit"=>9);
			$record = $this->lesson_service->getCreatedRecordByUser ( $condition );
			$count = $this->lesson_service->getCreatedRecordByUserCount ( $condition );
			$paging = getPaging($condition['page'],$count,$condition['limit'],'queryByPageCreate');
			$this->assign('paging',$paging);
			$this->assign ( "record", $record );
			$this->display ( 'tea_lesson' );
		}
		if ($roleEnName == 'student'||$roleEnName=='parent'||$roleEnName=='instructor' ||$roleEnName=='eduadmin') {
			$condition=array("host"=>$cyuid,"page"=>1,"limit"=>9);
			$live = $this->lesson_service->getNotOverLiveSigned ( $cyuid );
			$record = $this->lesson_service->getSignedUpRecordByUser ( $condition );
			$count = $this->lesson_service->getSignedUpRecordByUserCount ( $condition );
			$paging = getPaging($condition['page'],$count,$condition['limit'],'queryByPageSign');
			$this->assign('paging',$paging);
			$this->assign ( "live", $live );
			$this->assign ( "record", $record );
			$this->display ( 'stu_lesson' );
		}
	}
	
	/**
	 * 开课记录列表
	 */
	public function recordListCreate(){
		if (empty ( $_SESSION ['mid'] )) {
			redirect ( C ( 'LOGIN_URL' ) );
		}		
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		$condition['host']=$cyuid;
		//分页
		$condition["page"] = $_POST['page'];
		$condition["limit"] = 9;
		if(empty($condition["page"])){
			$condition["page"] = 1;
		}
		$course_type = $_REQUEST['type'];
		$condition['course_type'] = $course_type;
		$record = $this->lesson_service->getCreatedRecordByUser ( $condition );
		$count = $this->lesson_service->getCreatedRecordByUserCount ( $condition );
		$paging = getPaging($condition['page'],$count,$condition['limit'],'queryByPageCreate');
		$this->assign('record',$record);
		$this->assign('paging',$paging);
		$pre_url=C('LESSON');
		$this->assign("pre_url",$pre_url);
		$this->assign("type","create");
		$this->display("recordList");
	}
	
	/**
	 * 听课记录列表
	 */
	public function recordListSign(){
		if (empty ( $_SESSION ['mid'] )) {
			redirect ( C ( 'LOGIN_URL' ) );
		}
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		$condition['user_id']=$cyuid;
		//分页
		$condition["page"] = $_POST['page'];
		$condition["limit"] = 9;
		if(empty($condition["page"])){
			$condition["page"] = 1;
		}
		$course_type = $_REQUEST['type'];
		$condition['course_type'] = $course_type;
		$record = $this->lesson_service->getSignedUpRecordByUser ( $condition );
		$count = $this->lesson_service->getSignedUpRecordByUserCount ( $condition );
		$paging = getPaging($condition['page'],$count,$condition['limit'],'queryByPageSign');
		
		$this->assign('record',$record);
		$this->assign('paging',$paging);
		$pre_url=C('LESSON');
		$this->assign("pre_url",$pre_url);
		$this->assign("type","sign");
		$this->display("recordList");
	}
	
	/**
	 * 我要开课弹出层
	 */
	public function newRecordPop() {
		// 获取学段
		$this->assign ( 'phases', $this->lesson_service->getPhaseInfo () );
		// 获取课程类型
		$this->assign ( 'coursetype', $this->lesson_service->getRecordTypes () );

		//获取关注的好友列表
		$users = $this->listUsers();
		 
		$this->assign('users',$users);
		 
		$this->display ( 'liveAdd' );
	}
	
	/**
	 * 列表所有的人员
	 */
	public function listUsers() {
		$uid = $GLOBALS ['ts'] ['mid'];
		if (! isset ( $uid ) && ! empty ( $uid )) {
			return array ();
		}
		$list = model ( 'Follow' )->getFollowingList ( $uid, null, 100 );
		if (empty ( $list ) || empty ( $list ['data'] )) {
			return array();
		} else {
			$new_list = array();
			for($i = 0; $i < count ( $list ['data'] ); $i ++) {
				$user = model ( 'User' )->getUserInfo ( $list ['data'] [$i] ['fid'] );
				$cyId = $user ['cyuid'];
				$list ['data'] [$i] ['fname'] = $user ['uname'];
				$list ['data'] [$i] ['fid'] = $cyId;
				array_push($new_list,$list['data'][$i]);
			}
			return $new_list;
		}
	
	}
	
	/**
	 * 根据学段获取年级和学科信息
	 */
	public function getGradesAndSubjectByPhase() {
		$phase = $_REQUEST ['phase'];
		if (empty ( $phase )) {
			echo '';
		}
		echo $this->lesson_service->getGradesAndSubjectByPhase ( $phase );
	}
	
	/**
	 * 根据学段和学科获取出版社信息
	 */
	public function getPublisherBySubjectAndPhase() {
		$phase = $_REQUEST ['phase'];
		$subject = $_REQUEST ['subject'];
		if (empty ( $phase ) || empty ( $subject )) {
			echo '';
		}
		echo $this->lesson_service->getPublisherBySubjectAndPhase ( $phase, $subject );
	}
	
	/**
	 * 创建直播课
	 */
	public function createLive() {
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		
		if (empty ( $_SESSION ['login_name'] )) {
			Log::write ( '当前登录用户信息失效' . time (), Log::ERR );
			$this->ajaxReturn ( array (
					'status' => 300,
					'msg' => '当前登录用户失效',
					'data' => '' 
			), 'json' );
		}
		
		$imgParams = $this->getImageParams();
		$picPath=$this->lesson_service->uploadImage($imgParams);
		$params = $this->getLiveParams ( $picPath );
		// 对听课人数上限的类型进行判断
		if (! is_numeric ( $params ['listener_count'] )) {
			Log::write ( '输入的人数类型不正确' . time (), Log::ERR );
			$this->ajaxReturn ( array (
					'status' => 400,
					'msg' => '请输入有效的上课人数',
					'data' => '' 
			), 'json' );
		}
		//判断设置的人数是否满足配置的人数上限
		if($params['listener_count'] > 50){
			Log::write('输入的人数上限过大，请重新输入'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'您输入的上课人数过多，请输入少于50的上课人数','data'=>''),'json');
		}
		//对于时间的合法性进行判断
		$frist_ = explode(' ',$params['st_time']) ;
		$day_frist =  explode('-',$frist_[0]) ;
		$second_ = explode(' ',$params['end_time']) ;
		$day_second =  explode('-',$second_[0]) ;
		 
		if($day_frist[2] != $day_second[2]){
			Log::write('开始时间和结束时间必须是同一天'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'开始时间与结束时间必须是同一天','data'=>''),'json');
		}
		
		//对输入的时间进行有效性判断
		$st_time = $params['st_time'] ;
		$end_time = $params['end_time'] ;
		 
		//时间有效性判断
		if($st_time < date('Y-m-d H:i:s') ){
			Log::write('输入的时间非法，公开课时间小当前的时间:'.json_encode($params).',时间是:'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'时间不合法，开始时间应该大于当前时间','data'=>''),'json');
		}
		//时间正确性判断
		if($end_time <= $st_time){
			Log::write('输入的时间非法，不能正确创建公开课参数为:'.json_encode($params).',时间是:'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'时间不合法，结束时间应该大于开始时间','data'=>''),'json');
		}
		$state = $this->lesson_service->creatLive ( $params );
		
		// 创建听课人的信息
		$ids = $_REQUEST ['ids'];
		$idArr = explode(',',$ids);
		 
		if( $params['listener_count'] < count($idArr) ){
			Log::write('您输入的人数上限不合法，人数少于选择的人数:'.json_encode($_REQUEST).',时间是:'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'听课人数不合法，少于选择的听课人数','data'=>''),'json');
		}
		 
		$userCount = 0;
		if (! empty ( $ids )) {
			Log::write ( 'liveID:' . $state, Log::INFO );
			$userParams ['live_id'] = $state;
			$userParams ['join_time'] = date ( 'Y-m-d H:i:s' );
			$userParams ['join_status'] = 1;
			$userParams ['join_type'] = 1;
			$userParams ['cid'] = $cyuid;
			$userParams ['ctime'] = date ( 'Y-m-d H:i:s' );
			$userParams ['is_del'] = 0;
			
			foreach ( $idArr as $val => $key ) {
				if (! empty ( $key )) {
					$userParams ['user_id'] = $key;
					$tmpState = $this->lesson_service->createViewer ( $userParams );
					$userCount ++;
					Log::write ( '创建听课人的返回值:' . $tmpState, Log::INFO );
					//发送信息
					$cyUserModel=new CyUserModel();					
					$inviteUserInfo = $cyUserModel->getUserDetail($key);
					Log::write('获取待发送的人的信息为:'.json_encode($inviteUserInfo).'获取的时间为:'.time(),Log::INFO);
					if(!empty($inviteUserInfo)){
						$sytemMessage=$user ['user'] ['uname'].'邀请您参加直播课程《'.$_REQUEST['liveName'].'》';
						$this->systemMessage($inviteUserInfo['cyuid'],$sytemMessage);
						if(!empty($inviteUserInfo['mobile'])){
							$messageParams['invite_id'] = $key ;
							$messageParams['invite_phone'] = $inviteUserInfo['mobile'];
							$messageParams['class_name'] = $params['name'] ;
							$messageParams['class_host'] = $user ['user'] ['uname'];
							$messageParams['class_time'] = $params['st_time'] ;
 							$sendState=$this->lesson_service->sendMessage($messageParams,'mobile');
 							Log::write('发送mobile信息的结果是:'.$sendState.',参数为:'.json_encode($messageParams).'发送的时间为:'.time(),Log::INFO);
						}
						if(!empty($inviteUserInfo['phone'])){
							$messageParams['invite_id'] = $key ;
							$messageParams['invite_phone'] = $inviteUserInfo['phone'];
							$messageParams['class_name'] = $params['name'] ;
							$messageParams['class_host'] = $user ['user'] ['uname'];
							$messageParams['class_time'] = $params['st_time'] ;
							$sendState=$this->lesson_service->sendMessage($messageParams,'mobile');
							Log::write('发送phone信息的结果是:'.$sendState.',参数为:'.json_encode($messageParams).'发送的时间为:'.time(),Log::INFO);
						}
						if(!empty($inviteUserInfo['email'])){
							//发送邮件
							$mailParams['email'] = $inviteUserInfo['email'];
							$mailParams['loginName'] = $inviteUserInfo['uname'];
							$mailParams['content'] = $user ['user'] ['uname'].'邀请您于'.$params['st_time'].'参加【'.$params['name'].'】公开课的学习，欢迎您的参与!';
 							$mailState=$this->lesson_service->sendMessage($mailParams,'email');
 							Log::write('发送邮件的结果是:'.$mailState.',参数为:'.json_encode($mailParams).'发送的时间为:'.time(),Log::INFO);
						}
					}					
				}
			}
		}
		Log::write ( '创建直播课的参数为：' . json_encode ( $_REQUEST ), Log::INFO );
		Log::write ( '部分听课人主键信息：' . json_encode ( $ids ) . '长度为:' . count ( $ids ), Log::INFO );
		// 更新直播课的邀请人数信息
		if (! empty ( $ids )) {
			$this->lesson_service->updateLive ( $userCount,$state );
		}
		if ($state > 0) {
			Log::write ( '创建直播课成功,创建成功时间' . time (), Log::ERR );
			$this->ajaxReturn ( array (
					'status' => 200,
					'msg' => '创建成功',
					'data' => '' 
			), 'json' );
		}
		Log::write ( '创建直播课失败，时间：' . time (), Log::ERR );
		$this->ajaxReturn ( array (
				'status' => 400,
				'msg' => '创建失败',
				'data' => '' 
		), 'json' );
	}
	
	/**
	 * 邀请听课
	 */
	public function inviteUsers(){
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		
		$state = $_REQUEST['liveId'];
		
		//获取已参加直播课的人数
		$live=$this->lesson_service->getLiveInfoById($state);
		$live=$live[0];
		$users=$this->lesson_service->listUsersByLiveId($state);
		$uid = $GLOBALS ['ts'] ['mid'];
		if (! isset ( $uid ) && ! empty ( $uid )) {
			$len=0;
		}
		$list = model ( 'Follow' )->getFollowingList ( $uid, null, 100 );
		if (empty ( $list ) || empty ( $list ['data'] )) {
			$this->assign ( 'len',0);
		} else {
			$dataList=array();
			$userList=array();
			for($i = 0; $i < count ( $list ['data'] ); $i ++) {
				$flag=1;
				$user = model ( 'User' )->getUserInfo ( $list ['data'] [$i] ['fid'] );
				$cyId = $user ['cyuid'];
				$list ['data'] [$i] ['fname'] = $user ['uname'];
				$list ['data'] [$i] ['fid'] = $cyId;
				if(!empty($users)){
					foreach ($users as $u){
						if($user['cyuid']==$u['user_id']){
							array_push($userList, $list ['data'] [$i]);
							$flag=0;break;
						}
					}
				}
				if($flag==0){
					continue;
				}
				$cyId = $user ['cyuid'];
				$list ['data'] [$i] ['fname'] = $user ['uname'];
				$list ['data'] [$i] ['fid'] = $cyId;
				array_push($dataList, $list ['data'] [$i]);
			}
			$len=count($userList);
		}
		
		// 创建听课人的信息
		$ids = $_REQUEST ['ids'];
		$idArr = explode(',',$ids);
		
		if( ($live['listener_count']-$len) < count($idArr) ){
			Log::write('您输入的人数上限不合法，人数少于选择的人数:'.json_encode($_REQUEST).',时间是:'.time(),Log::ERR);
			$this->ajaxReturn(array('status'=>400,'msg'=>'听课人数不合法，少于选择的听课人数','data'=>''),'json');
		}
			
		$userCount = $len;
		if (! empty ( $ids )) {
			Log::write ( 'liveID:' . $state, Log::INFO );
			$userParams ['live_id'] = $state;
			$userParams ['join_time'] = date ( 'Y-m-d H:i:s' );
			$userParams ['join_status'] = 1;
			$userParams ['join_type'] = 1;
			$userParams ['cid'] = $cyuid;
			$userParams ['ctime'] = date ( 'Y-m-d H:i:s' );
			$userParams ['is_del'] = 0;
			$loginuser = $GLOBALS ['ts'] ['cyuserdata'];
			foreach ( $idArr as $val => $key ) {
				if (! empty ( $key )) {
					$userParams ['user_id'] = $key;
					$tmpState = $this->lesson_service->createViewer ( $userParams );
					$userCount ++;
					Log::write ( '创建听课人的返回值:' . $tmpState, Log::INFO );
					//发送信息
					$cyUserModel=new CyUserModel();
					$inviteUserInfo = $cyUserModel->getUserDetail($key);
					Log::write('获取待发送的人的信息为:'.json_encode($inviteUserInfo).'获取的时间为:'.time(),Log::INFO);
					if(!empty($inviteUserInfo)){						
						$sytemMessage=$loginuser['user']['uname'].'邀请您参加直播课程《'.$live['name'].'》';
						$this->systemMessage($inviteUserInfo['cyuid'],$sytemMessage);
						if(!empty($inviteUserInfo['mobile'])){
							$messageParams['invite_id'] = $key ;
							$messageParams['invite_phone'] = $inviteUserInfo['mobile'];
							$messageParams['class_name'] = $live['name'] ;
							$messageParams['class_host'] = $loginuser['user']['uname'];
							$messageParams['class_time'] = $live['st_time'] ;
							$sendState=$this->lesson_service->sendMessage($messageParams,'mobile');
							Log::write('发送mobile信息的结果是:'.$sendState.',参数为:'.json_encode($messageParams).'发送的时间为:'.time(),Log::INFO);
						}
						if(!empty($inviteUserInfo['phone'])){
							$messageParams['invite_id'] = $key ;
							$messageParams['invite_phone'] = $inviteUserInfo['phone'];
							$messageParams['class_name'] = $live['name'] ;
							$messageParams['class_host'] = $loginuser['user']['uname'];
							$messageParams['class_time'] = $live['st_time'] ;
							$sendState=$this->lesson_service->sendMessage($messageParams,'mobile');
							Log::write('发送phone信息的结果是:'.$sendState.',参数为:'.json_encode($messageParams).'发送的时间为:'.time(),Log::INFO);
						}
						if(!empty($inviteUserInfo['email'])){
							//发送邮件
							$mailParams['email'] = $inviteUserInfo['email'];
							$mailParams['loginName'] = $inviteUserInfo['uname'];
							$mailParams['content'] = $loginuser['user']['uname'].'邀请您于'.$live['st_time'].'参加【'.$live['name'].'】公开课的学习，欢迎您的参与!';
							$mailState=$this->lesson_service->sendMessage($mailParams,'email');
							Log::write('发送邮件的结果是:'.$mailState.',参数为:'.json_encode($mailParams).'发送的时间为:'.time(),Log::INFO);
						}
					}
				}
			}
		}
		Log::write ( '部分听课人主键信息：' . json_encode ( $ids ) . '长度为:' . count ( $ids ), Log::INFO );
		// 更新直播课的邀请人数信息
		if (! empty ( $ids )) {
			$result=$this->lesson_service->updateLive ( $userCount,$state );
			if($result>=0){
				$this->ajaxReturn ( array (
						'status' => 200,
						'msg' => '邀请成功',
						'data' => ''
				), 'json' );
			}else{
				$this->ajaxReturn ( array (
						'status' => 400,
						'msg' => '邀请失败',
						'data' => ''
				), 'json' );
			}
		}
		
	}
	/**
	 * 获取待裁剪的图片的信息
	 */
	private  function getImageParams(){
		$data['picW'] = $_REQUEST ['picW'];
		$data['picH'] = $_REQUEST ['picH'];
		$data['picX'] = $_REQUEST ['picX'];
		$data['picY'] = $_REQUEST ['picY'];
		$imgPathArr = explode('.',$_REQUEST ['pathImg']);
		$imgPathArr_1 = explode('/',$_REQUEST ['pathImg']);
		$data['extension'] = $imgPathArr[2];
		$data['pathImg']=C('SPACE'). $imgPathArr_1[1].'/'.$imgPathArr_1[2];
		return $data;
	}
	/**
	 * 获取创建公开课的参数
	 */
	private function getLiveParams($picPath) {
		
		// 获取登录用户的信息
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$sch = $user ['orglist'] ['school'];
		foreach ( $sch as $val ) {
			$schoolId = $val ['id'];
			break;
		}
		$cyuid = $user ['user'] ['cyuid'];
		
		$data ['name'] = $_REQUEST ['liveName'];
		// 主持人id
		$data ['host'] = $cyuid;
		
		$data ['school_id'] = $schoolId;
		$data ['phase_cd'] = $_REQUEST ['phase'];
		$data ['subject_cd'] = $_REQUEST ['subject'];
		$data ['publisher_cd'] = $_REQUEST ['publish'];
		$data ['grade_cd'] = $_REQUEST ['grade'];
		$data ['private'] = empty ( $_REQUEST ['ids'] ) ? 1 : 0;
		$data ['st_time'] = $_REQUEST ['timePikerStart'];
		$data ['end_time'] = $_REQUEST ['timePikerEnd'];
		$data ['course_type'] = $_REQUEST ['courseType'];
		$data ['cover_url'] = $picPath;
		$data ['intro'] = $_REQUEST ['remark'];
		$data ['listener_count'] = empty ( $_REQUEST ['members'] ) ? 20 : intval ( $_REQUEST ['members'] );
		$data ['audit_status'] = 1;
		// 报名人数
		$data ['apply_count'] = 0;
		// FIX ME 邀请人数
		$data ['invite_count'] = 0;
		$data ['cid'] = $cyuid;
		$data ['ctime'] = date ( 'Y-m-d H:i:s' );
		$data ['is_del'] = 0;
		$data ['key_words'] = $_REQUEST ['keywords'];
		$data['host_name']=$user ['user']['uname'];
		return $data;
	}
	
	/**
	 * 可被邀请的好友列表
	 */
	public function listAddUsers() {
		$liveId=$_REQUEST['liveId'];
		$live=$this->lesson_service->getLiveInfoById($liveId);
		$users=$this->lesson_service->listUsersByLiveId($liveId);
		$uid = $GLOBALS ['ts'] ['mid'];
		if (! isset ( $uid ) && ! empty ( $uid )) {
			return array ();
		}
		$list = model ( 'Follow' )->getFollowingList ( $uid, null, 100 );
		if (empty ( $list ) || empty ( $list ['data'] )) {
			$this->assign ( 'users', array () );
			$this->assign ( 'len',0);
		} else {
			$dataList=array();
			$userList=array();
			for($i = 0; $i < count ( $list ['data'] ); $i ++) {
				$flag=1;
				$user = model ( 'User' )->getUserInfo ( $list ['data'] [$i] ['fid'] );
				$cyId = $user ['cyuid'];
				$list ['data'] [$i] ['fname'] = $user ['uname'];
				$list ['data'] [$i] ['fid'] = $cyId;
				if(!empty($users)){
					foreach ($users as $u){
						if($user['cyuid']==$u['user_id']){
							array_push($userList, $list ['data'] [$i]);
							$flag=0;break;
						}
					}
				}
				if($flag==0){
					continue;
				}
				$cyId = $user ['cyuid'];
				$list ['data'] [$i] ['fname'] = $user ['uname'];
				$list ['data'] [$i] ['fid'] = $cyId;
				array_push($dataList, $list ['data'] [$i]);
			}
			$this->assign ( 'users',$dataList);
			$this->assign ( 'addusers',$userList);
			$len=count($userList);
			$this->assign ( 'len',$len);
		}
		$this->assign ( 'live',$live[0]);
		$this->display ( 'addUserList' );
	}
	
	/**
     * 文件长传
     */
    public function uploadify(){
    	$targetFolder = './UPLOAD_PATH'; //设置上传目录
    	if (!empty($_FILES)) {
    		$tempFile = $_FILES['Filedata']['tmp_name'];
    			
    		$extend = explode("." , $_FILES['Filedata']['name']);
    		$va=count($extend)-1;
    			
    		$fileName = 'mang_'.time().mt_rand(10000,99999).".".$extend[$va];
    		$filePath =$targetFolder. '/' . $fileName;
    	
    		$fileTypes = array('jpg','jpeg','png'); //允许的文件后缀
    		$fileParts = pathinfo($_FILES['Filedata']['name']);
    			
    		if (in_array(strtolower($fileParts['extension']),$fileTypes)) {
    			move_uploaded_file($tempFile,iconv("UTF-8","gb2312", $filePath));
    			$this->ajaxReturn(array(status=>200, msg=>'success', data=>$filePath));//上传成功后返回给前端的数据
    		} else {
    			echo '不支持的文件类型';
    		}
    	}
	}
	
	/**
	 * 老师创建的直播课
	 */
	public function teaCreated(){
		$this->appCssList [] = 'zone.css';
		if (empty ( $_SESSION ['login_name'] )) {
			redirect ( C ( 'LOGIN_URL' ) );
		}
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
		if ($roleEnName == 'teacher') {
			$live = $this->lesson_service->getNotOverLiveCreated ( $cyuid );
			$this->assign ( "live", $live );
		}else{
			$this->assign ( "live", array() );
		}
		$this->display('teaCreated');
	}
	
	/**
	 * 老师报名的直播课
	 */
	public function teaSigned(){
		$this->appCssList [] = 'zone.css';
		if (empty ( $_SESSION ['login_name'] )) {
			redirect ( C ( 'LOGIN_URL' ) );
		}
		$user = $GLOBALS ['ts'] ['cyuserdata'];
		$cyuid = $user ['user'] ['cyuid'];
		//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
		$roleEnName = D("UserLoginRole")->getUserCurrentRole($user['user']['login'], $user['rolelist']);
		if ($roleEnName == 'teacher') {
			$live = $this->lesson_service->getNotOverLiveSigned ( $cyuid );
			$this->assign ( "live", $live );
		}else{
			$this->assign ( "live", array() );
		}
		$this->display('teaSigned');
	}
	
	/**
	 * 发送系统消息
	 */
	private function systemMessage($uid,$message){
		
		if($uid = $this->user_exist($uid)){
			$params['uid'] = $uid;
			$params['title'] = '公开课';
			$params['body'] = $message;
			$params['appname'] = 'lesson';
			$params['node']='';
			if( model('Notify')->sendMessage($params)){
				$data['return'] = true;
				return json_encode($data);
			}else{
				$data['return'] = false;
				return json_encode($data);
			}
		}else{
			$data['return'] = false;
			return json_encode($data);
		}
	}
	
	/**
	 * 获取用户SNS UID
	 * @param unknown $cyuid
	 * @return boolean
	 */
	private function user_exist($cyuid){
		$u_sql = "select uid from ts_user where cyuid=".$cyuid;
		$list = M()->query($u_sql);
		if(empty($list[0]["uid"])){
			return false;
		}
		return $list[0]["uid"];
	}	
}