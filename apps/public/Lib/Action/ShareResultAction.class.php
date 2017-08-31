<?php
/**
 * 分享成果控制器
 * @author xmsheng
 *
 */
class ShareResultAction extends Action {

    protected $cyClient = null;
    protected $treeClient = null;
    protected $categoryClient = null;

    public function __construct(){
        parent::__construct();
        $this->cyClient = new \CyClient();
        $this->treeClient = new \TreeClient();
        $this->categoryClient = new \CategoryClient();
    }

	/**
	 * 获取父目录下的子目录
	 */
	public function getDirs() {
		$cyuid = $this->cyuid;
		if(empty($_REQUEST["fid"])){
			// 获取我的文档fid			
			$restParams = array();
			$restParams['method'] = 'pan.dirid.get';
			$restParams['uid'] = $cyuid;
			$restParams['folderType'] = 'yun_wendang';
			$wendang_fid = Restful::sendGetRequest($restParams);
			Log::write("我的文档fid : ".$wendang_fid,Log::DEBUG);
			
			$cloudDir['fid'] = $wendang_fid; //-1表示云盘根目录
			$cloudDir['shortName'] = "我的文档";
			$cloudDir['title'] = "我的文档";
			$cloudDir['name'] = "我的文档";
			$cloudDir['isParent']=true;
			exit (json_encode($cloudDir));
		}
		$fid =($_GET ["fid"]==-1 ) ? 0 : $_GET['fid'];		
		$results = D ( "Yunpan", "yunpan" )->getYunpanDirs($cyuid, $fid, 0, 0 );
		$res_dirs=array();
		$dirs = $results->data;
		$FolderType=D("FolderType","yunpan");
		foreach ( $dirs as $key=>$value ) {
            $value->shortName = getShort($value->name,6,'...' );
            $value->title = $value->name;
            $value->name = htmlspecialchars($value->name, ENT_QUOTES );
            $value->isParent = true;
            $res_dirs=array_merge($res_dirs,array($value));
		}
		exit(json_encode($res_dirs));
	}
	
	/**
	 * 获取某一文件夹下的文件列表
	 * 2014-8-12
	 */
	public function getFiles() {
		$cyuid = $this->cyuid;
		if($_GET['fid']==-1||empty($_GET['fid'])){
			$fid=0;
		}else{
			$fid=$_GET['fid'];
		}
		$files = D ( "Yunpan", "yunpan" )->getYunpanFiles ( $cyuid, $fid, 0, 10 );
		$files = $files->data;
		foreach ( $files as $value ) {
			$value->shortImgUrl = ADDON_URL . '/theme/stv1/_static/images/' . getShortImg ( $value->extension );
			$value->shortName = getShort ( $value->name, 11, '...' );
			$value->name = htmlspecialchars ( $value->name, ENT_QUOTES );
		}
		exit (json_encode($files));
	}
	
	/**
	 * 分享资源
	 * 2014-8-15
	 */
	public function shareRes() {
		$fid = $_POST ['fid'];
		$desc = $_POST['desc'];
		$filename = $_POST['name'];
		//资源分享次数是否增加标志
		$hasShareFlag = false;
		$msg = array("status"=>"200","msg"=>"资源分享成功","score"=>"0");	
		
		if(empty($fid)){
			$msg ['status'] = '500';
			$msg ['msg'] = '分享资源不能为空';
			exit (json_encode($msg));
		}
		
		//分享到个人主页
		if($_POST['per_page']=='1' && $result["per_page"]==0){
			$res = $this->shareResultToPersonPage ($fid,$filename,$desc);
			if ($res['status'] == "500") {
				$msg ['status'] = "500";
				$msg ['msg'] = "资源分享到个人主页失败";
				exit(json_encode($msg));
			}else{
				if(!empty($res['score'])){
					$msg['score'] += $res['score'];
				}
			}
			//添加资源被分享数
			if(!$hasShareFlag){
				D('UserData')->updateKey('share_count',1);
				$hasShareFlag=true;
			}
		}

		// 分享到班级
		if($_POST['class_share'] == '1'){
			$classIds = $_POST['classids'];
			$params = array();
			$params['classids'] = $classIds;
			$params['filename'] = $filename;
			$params['description'] = $desc;
			$params['subject']=$_POST['classSubject'];
			$classRecord = $this->shareResultToClass($fid, $params);
			if(!empty($classRecord['score'])){
				$msg['score'] += $classRecord['score'];
			}
			//添加资源被分享数
			if(!$hasShareFlag){
				D('UserData')->updateKey('share_count',1);
				$hasShareFlag=true;
			}
		}
		
		//分享到同步资源
		if($_POST['sys_res']=='1'&&$result['sys_res']==0){
			$con['fid'] = $fid;
			$con['bookid'] = $_POST['bookid'];
			$con['unit1'] = $_POST['unit1'];
			$con['unit2'] = $_POST['unit2'];
			$con['unit3'] = $_POST['unit3'];
			$con['type'] = $_POST['type'];
			$con['description'] = $desc;
			$con['extension'] = $_POST['extension'];
			$con['filename'] = $filename;
			$res = $this->shareResultToSysRes($con);
			Log::write("share to syn : ".json_encode($res),Log::DEBUG);
			if($res['status']== "500") {
				$msg ['status'] = "500";
				$msg ['msg'] = "资源分享到同步资源失败";
				exit (json_encode($msg));
			}else{
				if(!empty($res['score'])){
				  $msg['score'] += $res['score'];
				}
			}
			//添加资源被分享数
			if(!$hasShareFlag){
				D('UserData')->updateKey('share_count',1);
				$hasShareFlag = true;
			}
		}
	    if($result["per_page"]==1&&$_POST['per_page'] == '1'){
	    	$msg['msg'] = "该资源在个人主页已被分享";
	    }
	    if($result["sys_res"] == 1 && $_POST['sys_res'] == '1'){
	    	$msg['msg'] = "该资源在同步资源已被分享";
	    }
		if ($result ["per_page"] == 1 && $result ["sys_res"] == 1 && $_POST ['per_page'] == '1' && $_POST ['sys_res'] == '1'){
			$msg ['msg'] = "该资源已被分享";
		}
		exit (json_encode($msg));
	}
	
	/**
	 * 分享到班级
	 * @param unknown_type $params
	 */
	private function shareResultToClass($fid,$parameters){
		// 获取云盘资源详细信息
		$query = array('method'=>'pan.file.get','uid'=>$this->cymid,'fileId'=>$fid);
		$panFile = Restful::sendGetRequest($query);
			
		// 拼装资源参数信息
		$resource = array();
		$resource['id'] = $fid;
		$resource['resName'] = $parameters['filename'];
		$resource['extension'] = $panFile->extension;
		$resource['size'] = $panFile->length;
		$resource['thumbnail'] = $panFile->thumbpath;
		$resource['downloadpath'] = $panFile->downloadpath;
			
		// 拼装接口参数
		$params = array();
		$params['uid'] = $this->cymid;
		$params['resources'] = json_encode(array($resource));
		$params['classIds'] = $parameters['classids'];
		$params['source'] = 'yun_shouye';
		$params['to'] = 'class';
		$params['method'] = 'pan.file.share';
		$shareIds = Restful::sendGetRequest($params);
		Log::write("分享结果：".json_encode($shareIds),Log::DEBUG);
		$shareId = $shareIds[0];
		$body = '我分享了【' . $parameters['filename'] . '】到班级，点击查看';
		$preUrl = C("ESCHOOL") . "index.php?m=Clazz&c=Share&a=preview&resid=" . $fid . "&uid=" . $this->cymid . "&share_id=" . $shareId . '&classId=' . $parameters['classids'];
		$data = array(
            "content" => '',
            "body" => $body,
            "source_url" => $preUrl
        );
        $addFeed = D('Feed')->put($this->mid, 'public', 'post', $data);
        Log::write("分享动态添加结果：".json_encode($addFeed),Log::DEBUG);
			
		// 发动态
		$condition = array();
		$condition['method'] = 'sns.resourceCenter.share';
		$condition['content'] = '';
		$condition['uid'] = $params['uid'];
		$condition['shareId'] = $shareId;
		$arr = Array();
		$arr['resId'] = $fid;
		$arr['resName'] = $parameters['filename'];
		$arr['extension'] = $panFile->extension;
		$arr['type'] = 2;
		$condition['resInfo'] = json_encode(array($arr));
		$feedRes = Restful::sendGetRequest($condition, C("SPACE").'api/snsapi.php?');
		Log::write("分享暗动态结果：".json_encode($feedRes),Log::DEBUG);
		
		// 添加分享记录
		$pro = array (
				"fid" => $fid,
				"login_name" => $GLOBALS['ts']['user']['login'],
				"dateline" => date ('Y-m-d H:i:s'),
				"open_position" => '03',
				"res_title" => $parameters['filename']
		);
		$pub_res=D("YunpanPublish","yunpan")->saveOrUpdate($pro);
		Log::write("分享记录保存态结果：".json_encode($pub_res),Log::DEBUG);
		
		$addRecord = $this->addCreditAndRecord($body, $preUrl);
		return $addRecord;
	}
	
	/**
	 * 分享资源到校本资源
	 * @param unknown_type $login
	 * @param unknown_type $parameters
	 */
	private function shareResultToSchool($login,$parameters){
		$classIds = explode(',',$parameters['classIds']);
		$class_info=D('CyClass')->get_class_info_by_id($classIds[0]);
		Log::write('班级信息:'.json_encode($class_info));
		//班级信息获取不正确或者无学校id返回
		if(empty($class_info['id'])||empty($class_info['schoolId'])){
			return false;
		}
		$e_client = new eschoolApp_Client(C("ESCHOOL")."index.php?m=Home&c=Server" );
		$school_isopen = $e_client->isSchoolOpen($class_info['schoolId']);
		//学校空间未开通
		if($school_isopen == 0){
			return false;
		}
		$fileIndex=array();
		//设置校本资源的数据属性
		$fileIndex['general'] = array(
				'productid' => 'rrt',
				'creator' => $login,
				'uploader' => $login,
				'description' => empty($parameters['description'])?'':$parameters['description'],
				'extension'=>$parameters['extension'],
				'title' => $parameters['filename'],
				'source' => 'yun_shouye',
		);
		$fileIndex['properties'] = array(
				'country'=>array($class_info['countryId']),
				'province'=>array($class_info['provinceId']),
				'city'=>array($class_info['cityId']),
				'district'=>array($class_info['districtId']),
				'school'=>array($class_info['schoolId']),
				'rrtlevel1' => array('13'),
				'subject' => array($parameters['subject']),
				'type' =>array(""),
		);
		//分享到校本资源
		$param['method'] = 'pan.file.export';
		$param['uid'] = $parameters['uid'];
		$param['fileId'] = $parameters['id'];
		$param['fileIndex'] = json_encode($fileIndex);
		$schoolResult = Restful::sendGetRequest($param);
		Log::write("分享到校本资源结果：".json_encode($schoolResult),Log::DEBUG);
	}
	
	/**
	 * 分享到个人主页
	 * 2014-8-18
	 */
	private function shareResultToPersonPage($fid,$filename,$desc) {
		/*
		$condition=array();
		$condition['login']=$this->user['login'];
		$condition['uid']=$this->uid;
		$condition['cyuid']=$this->cyuid;
		$condition['filename']=$filename;
		$condition['desc']=$desc;
		$condition['fid']=$fid;
		$msg = D("ShareRes")->shareResToHome($condition);
		if($msg['status'] == '200'){
			$body = '我分享了【' . $filename . '】到个人主页，点击查看';
			$resUrl = U("resview/Resource/index",array("id"=>$condition["fid"],"uid"=>$condition["uid"]));
			$shareResult = $this->addCreditAndRecord($body, $resUrl);
			return $shareResult;
		}else{
			return $msg;
		}
		*/
	
	    $query = array('method'=>'pan.file.get','uid'=>$this->cymid,'fileId'=>$fid);
		$panFile = Restful::sendGetRequest($query);
			
		// 拼装资源参数信息
		$resource = array();
		$resource['id'] = $fid;
		$resource['resName'] = $filename;
		$resource['extension'] = $panFile->extension;
		$resource['size'] = $panFile->length;
		$resource['thumbnail'] = $panFile->thumbpath;
		$resource['downloadpath'] = $panFile->downloadpath;
			
		// 拼装接口参数
		$params = array();
		$params['uid'] = $this->cymid;
		$params['resources'] = json_encode(array($resource));
		$params['source'] = 'yun_shouye';
		$params['to'] = 'homepage';
		$params['method'] = 'pan.file.share';
		$shareIds = Restful::sendGetRequest($params);
		
		if($shareIds){
			$body = '我分享了【' . $filename . '】到个人主页，点击查看';
			$resUrl = U("resview/Resource/index",array("id"=>$fid,"uid"=>$this->cymid));
			$data = array(
	            "content" => '',
	            "body" => $body,
	            "source_url" => $resUrl
            );
            $addFeed = D('Feed')->put($this->mid, 'public', 'post', $data);
			$shareResult = $this->addCreditAndRecord($body, $resUrl);
			return $shareResult;
		}else{
			$msg['status']="500";
			return $msg;

		}
	}
	
	/**
	 * 分享资源到同步资源
	  *2014-8-18
	 */
	private function shareResultToSysRes($con){
		$result  = array();
		//获取书本的基本信息		
		$obj = D("Tree")->getBookIndex($con['bookid']);	
		$properties=$obj->properties;
		$props = array();
		//通过资源审核模式配置来设置公开后的资源状态
		//1 无需审核
		$auditMode = C('RES_AUDIT_MODE');
		if($auditMode == 1){
			$props['lifecycle'] = array(
					'auditstatus' => '1'
			);
		}else{
			$props['lifecycle'] = array(
					'auditstatus' => '0'
			);
		}
        $userInfo = json_decode($_SESSION["cas_member"]);
		$props['general'] = array(
				'productid' => 'rrt',
				'creator' => $this->user['login'],
				'description'=>$con['description'],
				'extension'=>$con['extension'],
				'title'=>$con['filename'],
                'module' => C("grkj_module"),
                'action' =>  C("SNS_ACTION")["share"]
		);
		
		$props['properties'] = array(
				'subject' => array($properties->subject[0]),
				'publisher' => array($properties->publisher[0]),
				'grade' => array($properties->grade[0]),
				'edition' => array($properties->edition[0]),
				'book' => array($properties->book[0]),
				'unit' => array($con['unit1']),
				'type' => array($con['type']),
				'course'=>array($con['unit2']),
				'unit1' =>array($con['unit1']),
				'unit2'=>array($con['unit2']),
				'unit3'=>array($con['unit3']),
				'stage'=> array($properties->stage[0]),
				'phase'=>array($properties->phase[0]),
				'rrtlevel1' => array('08'),
				'province'=>array($GLOBALS['ts']['_cyuserdata']['locations']['province']['id']),
				'city'=>array($GLOBALS['ts']['_cyuserdata']['locations']['city']['id']),
				'district'=>array($GLOBALS['ts']['_cyuserdata']['locations']['district']['id']),
				'school'=>array(array_keys($GLOBALS['ts']['_cyuserdata']['orglist']['school'])[0]),
		);
		
		//文件导入资源网关
		Log::write("con info : ".json_encode($con),Log::DEBUG);
		Log::write("props : ".json_encode($props),Log::DEBUG);
		$res = D('YunpanFile','yunpan')->exportToGateway($this->cymid, $con['fid'], json_encode($props));
		Log::write("export to getway : ".json_encode($res),Log::DEBUG);	
		if(!$res->hasError){
			$result['data'] = $res->obj->listVal;
			//文件公开
			$op_rs = D ("YunpanFile","yunpan" )->setOpen ($this->cyuid,$con['fid']);	
				//同步动态到个人中心
				$resUrl = C("RS_SITE_URL").'/index.php?app=changyan&mod=Rescenter&act=detail&id='.$res->obj->listVal[0];		
				if($auditMode == 1){
					$shar_res = D('YunpanFile','yunpan')->syncToFeed($this->uid,$con['filename'], $resUrl,1);
				}
				$totalCount = C('UPLOAD_SCORE_LIMIT');
				$currentCount = D('UploadRecord')->getCuttentCounts($this->user['login']);
				//添加积分操作
				if($currentCount !== false && $currentCount < $totalCount){		
					$result['creditResult'] = D('Credit')->setUserCredit($this->mid,'upload_resource');
					$data = array();
					$data['content'] = '我分享了【'.$con['filename'].'】到资源中心，点击查看';
					$data['url'] = $resUrl;
					$data['rule'] = array(
							'alias' => $result['creditResult']['alias'],
							'score' => $result['creditResult']['score']
					);
					M('CreditRecord')->addCreditRecord($this->mid, $this->user['login'], 'upload_resource', $data);
				}
				$map['login'] = $this->user['login'];
				$map['count'] = $currentCount + 1;
				D('UploadRecord')->updateRecord($map);
			return array("status"=>"200","score"=>$data['rule']['score']);
		}else{
			return array("status"=>"500");
		}
	}
	
	private function addCreditAndRecord($record,$resUrl,$creditType = 'upload_resource'){
		$totalCount = C('UPLOAD_SCORE_LIMIT');
		$currentCount = D('UploadRecord')->getCuttentCounts($this->user['login']);
		//添加积分操作
		if($currentCount !== false && $currentCount < $totalCount){
			$result['creditResult'] = D('Credit')->setUserCredit($this->mid,'upload_resource');
			$data = array();
			$data['content'] = $record;
			$data['url'] = $resUrl;
			$data['rule'] = array(
					'alias' => $result['creditResult']['alias'],
					'score' => $result['creditResult']['score']
			);
			M('CreditRecord')->addCreditRecord($this->mid, $this->user['login'], $creditType, $data);
		}
		$map['login'] = $this->user['login'];
		$map['count'] = $currentCount + 1;
		D('UploadRecord')->updateRecord($map);
		return array("status"=>"200","score"=>$data['rule']['score']);
	}
	
	/**
	 * 获取同步资源目录列表
	 * 2014-8-15
	 */
	public function getNodes() {
		$conditon = array ();
		$node = strtolower ( $_REQUEST ['node'] );
		$phase = $_REQUEST ['phase'];
		$subject = $_REQUEST ['subject'];
		$edition = $_REQUEST ['edition'];
		$stage = $_REQUEST ['stage'];
		$data=array();	 
		if (!empty($phase)) {
			$conditon=array_merge ($conditon,array("phase"=>$phase));
		}
		if (!empty($subject)){
			$conditon =array_merge($conditon, array("subject"=>$subject));
		}
		if (!empty($edition)){
			$conditon = array_merge($conditon,array("edition" =>$edition));
		}
		if (!empty($stage)){
			$conditon=array_merge($conditon, array("stage"=>$stage));
		}		
		$obj =D('Tree')->getTreenodes($conditon,$node);			
		if ($node=="book"){
			exit(json_encode($obj[0]));
		}
		exit (json_encode($obj));
	}
	
	/**
	 * 获取书本及书本子目录的详细信息
	 * 2014-8-18
	 */
	public function getBookIndex() {
		$bookId = $_REQUEST ['bookId'];
		if (empty($bookId )) {
			exit (json_encode(Array()));
		}
		$bookInfo =D("Tree")->getBookIndex($bookId);
		exit(json_encode($bookInfo));
	}
	
	/**
	 * 获取课本下的单元下的子目录
	  *2014-8-18
	 */
	public function getUnit(){
		$bookId = $_REQUEST ['bookId'];
		$unit1_code=$_REQUEST['unit1_code'];
		$unit2_code=$_REQUEST['unit2_code'];
		$node=$_REQUEST['node'];
		if (empty ( $bookId )) {
			exit ( json_encode ( Array () ) );
		}
		$bookInfo=D("Tree")->getBookIndex($bookId);
		$units=$bookInfo->general->resourcedescriptor->units;
		$unit_chid=array();
		if(empty($units)){
			exit (json_encode(array()));
		}
		//获取单元下的第第一层目录
		if($node=="unit1"){
			foreach($units as $unit){
				if($unit->Code==$unit1_code){
					$unit_chid=$unit->Courses;
					exit(json_encode($unit_chid));
				}
			}
		}else if($node=="unit2"){
		 //获取单元下的第二层目录
			foreach($units as $unit){
				if($unit->Code==$unit1_code){
					$units2=$unit->Courses;
					foreach ($units2 as $unit2){
					  if($unit2->Code==$unit2_code){
					  	$unit_chid=$unit2->Courses;
					  	exit(json_encode($unit_chid));
					  }		
					}
				}				
			}
		}	
	}
	/**
	 * 获取资源类型列表
	 */
	public function getResourceTypes(){
		$types = array();
		array_push($types,array('code'=>'0100','name'=>'教案'));
		array_push($types,array('code'=>'0600','name'=>'课件'));
		array_push($types,array('code'=>'0300','name'=>'素材'));
		array_push($types,array('code'=>'0400','name'=>'习题'));
		array_push($types,array('code'=>'1901','name'=>'微课'));
		exit(json_encode($types));
	}


    /**
     * 获取学科列表
     * @return mixed
     */
    public function getSubjects($userId){
        $userInfo = $this->cyClient->getUserDetail($userId);
        $phaseCode = $userInfo->userExt1->ext_str_02;
        if(empty($phaseCode)){
            $result = $this->categoryClient->Category_GetCategoryValue('subject');
        }else{
            $result = $this->treeClient->Tree_GetTreeNodes('booklibrary2','subject',array('phase'=>$phaseCode));
        }
        $subjects = $result->statuscode == '200' ? $result->data:array();
        $order = array();
        foreach ($subjects as &$value){
            if(in_array($value->name,array('演示','其他'))){
                $value = null;
            }else{
                array_push($order,strlen($value->name));
            }
        }
        $subjects = array_filter($subjects);
        array_multisort($order,$subjects);

        return $subjects;
    }


    /**
     * 获取老师教授的学科信息
     * @param string $userId 老师id
     * @return string 老师教授的学科code
     */
    public function getTeacherSubject($userId){
        $userInfo = $this->cyClient->getUserDetail($userId);
        $subjectCode = $userInfo->userExt1->ext_str_03;
        if(empty($subjectCode)){
            $classes = $this->cyClient->getClassByUserId($userId);
            foreach($classes as $class){
                $result = $this->cyClient->listTeacherSubjectByClass($userId,$class->id);
                if(!empty($result)){
                    foreach($result as $value){
                        if(empty($value) || $value == '00'){
                            continue;
                        }else{
                            return $value;
                        }
                    }
                }
            }

        }
        return $subjectCode;
    }
	
	/**
	 * 获取当前用户班级列表
	 */
	public function getUserClasses(){
		$param['method'] = 'core.class.list';
		$param['uid'] = $this->cymid;
		$param['page'] = 1;
		$param['limit'] = 20;
		$shareResult = Restful::sendGetRequest($param, C('USER_SERVER_URL'));

        //获取学科列表
        $subjectList = $this->getSubjects($this->cymid);
        //获取该教师所授主学科
        $subject = $this->getTeacherSubject($this->cymid);

		//默认班级数组为空
		$classes = array();
		// 不过滤班级是否开通，直接取得相关信息
		if(!empty($shareResult)){
			foreach($shareResult as $key=>$val){
				$classes[] = $val;
			}
		}

        $result = array(
            'subjects'=>$subjectList,
            'subject'=>$subject,
            'classes'=>$classes
        );
        echo json_encode($result);
	}
}