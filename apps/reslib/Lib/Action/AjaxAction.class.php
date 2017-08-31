<?php
import (SITE_PATH . '/vendor/rrt/resourceclient/src/gatewayInterface/commonFunction/function_util.php');

/**
 * Ajax调用控制器
 * @author yxxing
 * @version TS3.0
 */
class AjaxAction extends Action {
	
	//用户登录名
	private $login = "";
	private $_resclient = null;
	private $_treeclient = null;
	
	/**
	 * _initialize 模块初始化
	 *
	 * @return void
	 */
	protected function _initialize() {
		//关闭session写入，避免堵塞
		session_write_close();
		//避免非登录用户的非法操作
		$this->login = $this->user['login'];
		if(!$this->login){
			exit('{"status":0,"data":"非法调用！"}');
		}
		$this->_resclient = D('CyCore')->Resource;
		$this->_treeclient = D('CyCore')->Tree;
	}
	
	/**
	 * 入口
	 * @return void
	 */
	public function index() {
		$this->ajaxReturn('');
	}
	
	/**
	 * 检测上传标题敏感词方法
	 */
	public function textFilter(){
		$content = $_REQUEST['content'];
		$url = C('TEXT_FILTER_SERVER').'?content='.rawurlencode($content);
		$html = file_get_contents($url);
		echo $html;
	}
	
	/**
	 * 获取学科、年级、出版社、册别等信息
	 */
	public function nodes(){
		$node = $_REQUEST['node'];
		$subject = $_REQUEST['subject'];
		$publisher = $_REQUEST['publisher'];
		$grade = $_REQUEST['grade'];
		$volumn = $_REQUEST['volumn'];
		$condition = array();
		if(!empty($subject)){
			$condition = array_merge($condition,array('subject'=>$subject));
		}
		if(!empty($publisher)){
			$condition = array_merge($condition,array('publisher'=>$publisher));
		}
		if(!empty($grade)){
			$condition = array_merge($condition,array('grade'=>$grade));
		}
		if(!empty($volumn)){
			$condition = array_merge($condition,array('volumn'=>$volumn));
		}
		$order = '';
		if(!empty($order)){
			$condition = array_merge($condition,array('order'=>$order));
		}
		//添加缓存
		$categoroys = S("res_service_nodes_".$subject."_".$publisher."_".$grade."_".$volumn."_".$order);
		if(empty($categoroys)){

			$obj = $this->_treeclient->Tree_GetTreeNodes('booklibrary', $node, $condition);

			if($obj->statuscode == 200){
				$categoroys = $obj->data;
			} else{
				$categoroys = array();
				Log::write("从网关获取NODE失败：".$obj->data, Log::ERR);

			}
			S("res_service_nodes_".$subject."_".$publisher."_".$grade."_".$volumn."_".$order, $categoroys, 36000);
		}
		echo json_encode($categoroys);
	}
	
	/**
	 * 获取书本信息
	 */
	public function book(){
		$id = $_REQUEST['id'];
		//添加缓存
		$bookinfo = S("book_id_".$id);
		if(empty($bookinfo)){
			
			$obj = $this->_treeclient->Tree_getBookindex($id);
			
			if($obj->statuscode == 200 && isset($obj->data->general->resourcedescriptor->units)){
				$bookinfo = $obj->data->general->resourcedescriptor->units;
			} else{
				$bookinfo = array();

				Log::write("从网关获取BOOK失败：".$obj->data, Log::ERR);

			}
			S("book_id_".$id, $bookinfo, 36000);
		}
		echo json_encode($bookinfo);
	}
	
	/**
	 * 获取书本信息
	 */
	public function subject(){
        // 获取学科和年级
        $subjects = model('Node')->subjects;
        $subjects = array_slice($subjects, 1);
		echo json_encode($subjects);
	}
	
	/**
	 * 快速上传通道
	 */
	public function upload(){
		set_time_limit(0);

		Log::write("执行上传方法", Log::DEBUG);

		if (!empty($_FILES)) {
			try{				
				$_login = isset($_POST['login']) ? $_POST['login'] : "";
				if(!$_login){
					exit('{"status":0,"msg":"参数空"}');
				}
				$file = $_FILES['Filedata']['tmp_name'];
				$fileParts = pathinfo($_FILES['Filedata']['name']);
				
				// 替换上传资源文件名中的&符号
				$fileParts['filename'] = str_replace("&","",$fileParts['filename']);
				// 处理Linux环境下后缀名大写问题
				if (isset($fileParts['extension'])) {
					$fileParts['extension'] = strtolower($fileParts['extension']);
				}

                $upload_config = include(CONF_PATH.'/upload.inc.php');
                $file_extensions = $upload_config['previewable_exts'];
                if(!in_array("*.".$fileParts['extension'],$file_extensions)){
                    echo json_encode(array("statuscode"=>"500","msg"=>"上传格式不支持"));
                    return;
                }

				$targetFolder = UPLOAD_PATH;
				if(!is_dir($targetFolder)){  //如果不存在该文件夹
					mkdir($targetFolder, 0777);  //创建文件夹
				}
				chmod($targetFolder, 0777);  //改变文件模式
				$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename'].$_login.mt_rand()) . '.' . $fileParts['extension'];
				move_uploaded_file($file, $targetFile); 
				$res_info = array();
				$res_info['title'] = $fileParts['filename'];
				
				// 获取资源审核模式配置
				$auditMode = C('RES_AUDIT_MODE');
				if($auditMode == '1'){
					$res_info['auditstatus'] = "1";
				}else{
					$res_info['auditstatus'] = "0";
				}
				$res_info['productid'] = 'rrt';
				$res_info['creator'] = $_login;
				$res_info['extension'] = $fileParts['extension'];
				$res_info['province']=$GLOBALS['ts']['_cyuserdata']['locations']['province']['id'];
				$res_info['city']=($GLOBALS['ts']['_cyuserdata']['locations']['city']['id']);
				$res_info['district']=array($GLOBALS['ts']['_cyuserdata']['locations']['district']['id']);
				$res_info['school']=array(array_keys($GLOBALS['ts']['_cyuserdata']['orglist']['school'])[0]);

				Log::write($_login."开始上传资源".$fileParts['filename'].'.'.$fileParts['extension'], Log::DEBUG);

				try{

					$result = $this->_resclient->Res_UploadRes($targetFile,$res_info);
				}catch (Exception $e){
					Log::write($e->getMessage());
				}
				@unlink($targetFile);
				Log::write("上传资源到网关的结果：".json_encode($result));
				if($result->statuscode == 200){
					Log::write($_login."上传".$res_info->general->title."到网关成功，ID为：'".$result->data."'", Log::DEBUG);
					if($auditMode == '3'){
						$resReturn = $this->_resclient->Res_GetResIndex($result->data);
						$fileUrl = $resReturn->data[0]->file_url;
						Log::write("自动审核链接：".$fileUrl,Log::DEBUG);
						$url = C('TEXT_FILTER_SERVER').'?id='.rawurlencode($result->data).'&url='.rawurlencode($fileUrl);
						Log::write("请求地址：".$url,Log::DEBUG);
						$setAuto = file_get_contents($url);
						Log::write("添加至自动审核结果：".json_encode($setAuto),Log::DEBUG);
					}
					exit('{"status":1,"id":"'.$result->data.'"}');
				}else{

					Log::write($_login."上传".$res_info->general->title."失败，".$result->data, Log::ERR);

					exit('{"status":0,"msg":"'.$result->data.'"}');
				}
			}catch(Exception $e){
				exit('{"status":0,"msg":"'.$e->getMessage().'"}');
			}
		}
		Log::write("上传文件失败，未获取file");
	}
	
	/**
	 * 提交信息
	 */
	public function complete_upload(){
		Log::write($this->login."提交上传文件信息", Log::DEBUG);
		$msgid = isset($_REQUEST["msgid"]) ? $_REQUEST["msgid"] : "";;//名师工作室id		
		$cyuid = $this->cymid;
		$rid = $_REQUEST['rid'];
		
		if(empty($rid) || empty($cyuid)){
			exit('{"status":0,"msg":"非法调用"}');
		}
		
		// 同步资源到云盘根目录	2014.5.8
		$result = D('Yunpan','yunpan')->addResourceToYunpan($cyuid, $rid);
		Log::write("教学应用同步到云盘结果：".json_encode($result), Log::DEBUG);
		
		$bookId = isset($_REQUEST["book"]) ? $_REQUEST["book"] : "";
		
		$tree = new TreeClient();
		$book = $tree->Tree_getBookindex($bookId);
		$bookProperties=$book->data->properties;
		$unit = isset($_REQUEST["unit"]) ? $_REQUEST["unit"] : "";
		$course = isset($_REQUEST["course"]) ? $_REQUEST["course"] : "";
		$keywords = $_REQUEST['keywords'];
		$description = $_REQUEST['description'];
		$restype = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : "0000";
		
		$info = array(
					'source'=>'UGC',
					'subject'=>$bookProperties->subject[0],
					'publisher'=>$bookProperties->publisher[0],
					'grade'=>$bookProperties->grade[0],
					'volumn'=>$bookProperties->volumn[0],
					'book'=>$bookProperties->book[0],
					'unit'=>$unit,
					'type'=>$restype,
					'edition'=>$bookProperties->edition[0],
					'stage'=>$bookProperties->stage[0],
					'phase'=>$bookProperties->phase[0],
					'rrtlevel1'=>'08'
				);
		if(!empty($keywords)){
			$info['keyword'] = $keywords; 
		}
		if(!empty($description)){
			$info['description'] = $description;
		}
		if(!empty($course)){
			$info['course'] = $course;
		}
		$result =$this->_resclient->Res_UpdateResource($rid,$info);
		$_data = $this->_resclient->Res_GetResIndex($rid);
		// 增加公开记录
		if($_data->statuscode == '200'){
			$resourceInfo = $_data->data[0];
			$type = $resourceInfo->properties->type[0];
			$res_title = $resourceInfo->general->title;
			$publishRecord = array(
					"fid" => $rid,
					"dateline" => date('Y-m-d H:i:s',time()),
					"login_name" => $this->login,
					"type" => $type,
					"open_position" => "01",
					"res_title" => $res_title
			);
			$record = D("YunpanPublish","yunpan")->saveOrUpdate($publishRecord);
			Log::write("添加公开记录结果：".json_encode($record),Log::DEBUG);
		}
		if($result->statuscode == 200){
			$return = $this->_uploadResource($rid,$info['type'],$msgid);
			if($return){

                // 获取上传资源的上限数
                $totalCount = C('UPLOAD_SCORE_LIMIT');
                $currentCount = D('UploadRecord')->getCuttentCounts($this->login);
				if($currentCount > $totalCount){
                    $currentStatus = '500';
                }else{
                    $currentStatus = '200';
                }
				echo '{"status":1,"id":"'.$rid.'","currentStatus":"'.$currentStatus.'"}';

				//名师工作室上传的资源不计入资源库容量
				if(empty($msgid)){
					/* 资源上传成功,增加资源库容量 by xypan 10.9*/
					$size = intval($_REQUEST['size']);
					$loginName = $this->user['login'];
					D('ResourceCapacity')->addUsedCapacity($loginName, $size);
					/*-------------------end-----------------------------*/
				}
				return;
			} else{

				Log::write("上传文件信息到数据库失败", Log::ERR);

				$this->_resclient->Res_DeleteResource($rid);
				echo '{"status":0,"msg":"保存文件信息到数据库失败"}';
			}
		}else{

			Log::write("上传文件信息到网关失败".json_encode($result), Log::ERR);

			$this->_resclient->Res_DeleteResource($rid);
			echo '{"status":0,"msg":"上传文件信息失败"}';
		}
	}

	/**
	 * 下载资源
	 * @param string $rid,必须提供， 资源id
	 * 
	 * 成功返回1，失败返回0
	 */
	function downloadResource(){
		$rid = $_REQUEST['rid'];
        $isMarket = empty($_REQUEST['isMarket'])? false:true;
		$cyuid = $this->cymid;
		if(!$rid || !$cyuid){
			return;
		}
		$_filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : "未命名";
		$rescreator = D('Resource')->where(array("rid"=>$rid))->getField("creator");//获取资源作者sjzhao
		if($rescreator != $this->user['login']){//下载非本人资源扣除相应的积分,被下载的人增加相应积分
			$usercreditmodel=D('UserCredit','api');
			$usercreditmodel->addCreditByUname($rescreator,1);//被下载者增加1分
		}
		$down_result = $this->_downloadFile($rid, $_filename,$isMarket);
		if(false == $down_result){
			//下载失败
			$this->error("资源已被删除，或未被审核！");

			Log::write($this->login."下载资源".$rid."失败", Log::ERR);

		} else {
			//更新下载次数
			$res = $this->_initResinfoByGateinfo($rid);
			//保存下载记录
			$res_download = array(
					"fid" => $rid,
					"dateline" => date('Y-m-d H:i:s',time()),
					"login_name" => $this->login,
					"type" => $res['restype'],
					"download_source" => "02",
			);
			$downResult = D("YunpanDownload", "yunpan")->saveOrUpdate($res_download);
			Log::write("下载资源结果:".json_encode($downResult), Log::DEBUG);
			$resoucr_id = D('Resource')->where(array("rid"=>$rid))->getField("id");
			if(!$resoucr_id){
				//如果资源信息表中没有这条资源的信息，则重新插入记录
				$resoucr_id = D('Resource')->increase($res);
				if(!$resoucr_id){
					exit('{"status":0,"msg":"资源下载数据库插入失败"}');
				}
			} else {
				//更新数据库信息
				$info = array();
				$info['downloadtimes'] = $res['downloadtimes'];
				$info['praisetimes'] = $res['praisetimes'];
				$info['negationtimes'] = $res['negationtimes'];
				$info['praiserate'] =$res['praiserate'];
				$info['score'] =$res['score'];
				$res_update = D('Resource')->updateRes($rid, $info);
				if(!$res_update){
					exit('{"status":0,"msg":"资源下载数据库更新失败"}');
				}
			}
		}
	}
	
	/**
	 * 获取缩略图
	 */
	public function getResThumbnail(){
		$size =  isset($_REQUEST['size'])?$_REQUEST['size']:"164_123";
		$id = $_REQUEST["id"];
		$obj = $this->_resclient->Res_GetResIndex($id,true,$size);
		$thumbnailurl = $obj->statuscode == 200 && $obj->data->thumbnail_url!="" ? $obj->data->thumbnail_url:"";
		$hasthumb = "1";
		if(empty($thumbnailurl)){
			$hasthumb = "0";
			if($size=="120_90"){
				$thumbnailurl = 'default_h.jpg';
			}
			if($size=="120_160"){
				$thumbnailurl = 'default_s.jpg';
			}
			if($size=="164_123"){
				$thumbnailurl = 'default_h2.jpg';
			}
			if($size=="272_207"){
				$thumbnailurl = 'default_h3.jpg';
			}
		}
		$thumbnailobj=array();
		$thumbnailobj["id"]=$id;
		$thumbnailobj["thumbnail"]=$thumbnailurl;
		$thumbnailobj["hasthumb"]=$hasthumb;
		echo json_encode($thumbnailobj);
	}
	
	/**
	 * 修改资源信息
	 * @param string $rid,必须提供,资源id
	 * @param array $info,必须提供,资源信息
	 *
	 * 返回 {"status":1/0,"msg":""}
	 */
	function updateRes(){
		$rid =  isset($_REQUEST['rid'])?$_REQUEST['rid'] : "";
		if(!$rid){
			exit('{"status":0,"msg":"传入参数错误，rid为空！"}');
		}	
		$res_updatable = D('Resource')->where(array("rid"=>$rid, "creator"=>$this->login))->find();
		if(!$res_updatable){
			exit('{"status":0,"msg":"该资源不属于您，无权修改！"}');
		}
		$info = array();
		$info['title'] = isset($_REQUEST['title']) ? $_REQUEST['title'] : "";
		$info['restype'] = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : 0 ;
		$info['description'] = isset($_REQUEST['description']) ? $_REQUEST['description'] : "";
		
		$gateinfo = array();
		foreach ($info as $key => $value){
			if($key == "restype"){
				$gateinfo['type']=  array($value);
			}else{
				$gateinfo[$key]=  $value;
			}
		}
		//修改网关信息
		$updateres = $this->_resclient->Res_UpdateResource($rid, $gateinfo);
		
		if(!$updateres || $updateres->statuscode != 200){
			//网关信息修改失败
			echo '{"status":0,"msg":"网关更新失败！"}';

			Log::write($this->login."更新网关资源失败:".$rid.",原因为：".$updateres->data, Log::ERR);

		}else{
			//修改数据库信息
			$update_result = D('Resource')->where(array("rid"=>$rid, "creator"=>$this->login))->save($info);
			if($update_result || $update_result !== false){
				echo '{"status":1,"msg":"资源更新成功！"}';
			}else{
				echo '{"status":0,"msg":"资源数据库更新失败！"}';
			}
		}
	}
	
	/**
	 * 1.获取系统推荐资源
	 * 2.获取好友推荐资源
	 * 3.根据排序、关键字获取收藏资源列表
	 * 4.根据排序、关键字获取下载资源列表
	 *:5.根据学科、年级、资源类型和排序条件获取同步资源列表
	 * 
	 */
	public function getResList(){
		$cyuid = $this->cymid;
		$login = $this->login;
		
		if(!$cyuid || !$login){
			exit('{"status":0,"msg":"非法操作"}');
		}
		
		$sort_type = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "";
		$pageindex =  isset($_REQUEST['p']) ? $_REQUEST['p'] : 1;
		$operation =  isset($_REQUEST['operation']) ? $_REQUEST['operation'] : 1;
		$keywords = isset($_REQUEST['keywords']) ? $_REQUEST['keywords'] : '';
		
		switch ($sort_type){
			case "uploaddown":
				if($operation == 3 || $operation == 4){
					$sort = "res_opr.dateline desc";
				} else {
					$sort = "res.uploaddateline desc";
				}
				break;
			case "uploadup":
				if($operation == 3 || $operation == 4){
					$sort = "res_opr.dateline asc";
				} else {
					$sort = "res.uploaddateline asc";
				}
				break;
			case "downloadtimeup":
				$sort = "res.downloadtimes asc";
				break;	
			case "downloadtimedown":
				$sort = "res.downloadtimes desc";
				break;
			case "praiseratedown":
				$sort = "res.score desc";
				break;					
			case "praiserateup":
				$sort = "res.score asc";//按排序排序
				break;	
			default:
				$sort_type = "uploaddown";
				if($operation == 3 || $operation == 4){
					$sort = "res_opr.dateline desc";
				}else{
					$sort = "";	
				}			
		}
		
		$conditions  = array();
		if($keywords != "")
			$conditions['keywords'] = $keywords ;
		
		$pagesize = 10;
		//操作类型
		switch($operation){
			case 2:
				//好友推荐
				$operationtype = "friend_recommend";
				$condition = array_merge($conditions, array("res_cmd.login_name"=>$login));
				$result = D("ResourceRecommend")->getResByCondition($login, $condition, $pagesize, $sort, $fields=array());
				$this->results = $result['data'];
				break;
			case 3:
				//我的收藏
				$grade = isset($_REQUEST['g']) ? $_REQUEST['g'] : '';
				$subject = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
				$restype = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';
				
				$operationtype = "collection";
				if($grade != '')
					$conditions['grade'] = $grade;
				if($subject != '')
					$conditions['subject'] = $subject;
				if($restype != '')
					$conditions['restype'] = $restype;
				
				// 获取学科和年级
				$node = model('Node');
				$subjects = $node->subjects;
				$grades = $node->grades;
				
				//获取资源类型
				$restype_list = getRestype();
				$this->subjects = $subjects;
				$this->s = $subject;
				$this->grades = $grades;
				$this->g = $grade;
				$this->res = $restype_list;
				$this->t = $restype;
				$condition = array_merge($conditions, array("res_opr.operationtype"=>ResoperationType::COLLECTION));
				$result = D("ResourceOperation")->getResByCondition($login, $condition, $pagesize, $sort, $fields=array());
				$this->results = $result['data'];
				break;
			case 4:
				//我的下载
				$operationtype = "download";
				$condition = array_merge($conditions, array("res_opr.operationtype"=>ResoperationType::DOWNLOAD));
				$result = D("ResourceOperation")->getResByCondition($login, $condition, $pagesize, $sort, $fields=array());
				$this->results = $result['data'];
				break;
			case 5:
				//同步资源
				$grade = isset($_REQUEST['g']) ? $_REQUEST['g'] : '';
				$subject = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';
				$restype = isset($_REQUEST['t']) ? $_REQUEST['t'] : '';
				
				unset($conditions['keywords']);
				$operationtype = "upload";
				if($grade != '')
					$conditions['grade'] = $grade;
				if($subject != '')
					$conditions['subject'] = $subject;
				if($restype != '')
					$conditions['restype'] = $restype;
				
				// 获取学科和年级
				$node = model('Node');
				$subjects = $node->subjects;
				$grades = $node->grades;

				//获取资源类型
				$restype_list = getRestype();
				$this->subjects = $subjects;
				$this->s = $subject;
				$this->grades = $grades;
				$this->g = $grade;
				$this->res = $restype_list;
				$this->t = $restype;
				$condition = array_merge($conditions, array("res_opr.operationtype"=>ResoperationType::UPLOAD,"res.is_del"=>0));
				$result = D("ResourceOperation")->getResByCondition($login, $condition, $pagesize, $sort, $fields=array());
				$this->results = $result['data'];
                $this->results_subject = addslashes(json_encode($result['data']));
                
				break;
			default:
				//1系统推荐
				$user_subject = $GLOBALS['ts']['user']['subject'];
				$user_grade = $GLOBALS['ts']['user']['grade'];
				$list = $this->_getrecommendedresources($user_grade, $user_subject, 10);
				$this->results = $list['data'];
				$operationtype = "sys_recommend";
		}
		//ajax分页
		$p = new AjaxPage(array('total_rows'=>$result['totalRows'],
								'method'=>'ajax',
								'parameter'=>$operation,
								'ajax_func_name'=>'MyResLib.resPageList',
								'now_page'=>$pageindex,
								'list_rows'=>$pagesize
								));
		
		$page = $p->show ();
		$this->page =$page;
		$this->pageNum = $pageindex;
		$this->operation = $operation;
		$this->sort = $sort_type;
		$this->RS_SITE_URL = C('RS_SITE_URL');
		
		$this->setTitle('资源库首页');
		$this->setKeywords( $this->user['uname'].'的资源库' );
		$this->display($operationtype);
	}
	
	/**
	 * 删除同步资源时，查询该资源是否属于某一文章的附件
	 */
	function confirmRes(){
		$operationids =  isset($_REQUEST['opertionids']) ? $_REQUEST['opertionids'] : "";
		$_to_confirm = array();
		for ($i = 0; $i < count($operationids); $i++){
			$_rid = $operationids[$i];
			$_paper_attach = D("PaperAttach", "paper")->where(array("attach_id"=>$_rid,"attach_type"=>1))->getField("id");
			$_notice_attach = D("NoticeAttach")->where(array("attachId"=>$_rid,"attach_type"=>1))->getField("id");
			if($_paper_attach || $_notice_attach){
				$_res = D("Resource", "reslib")->where(array("rid"=>$_rid, "is_del"=>0))->field(array("title","suffix"))->find();
				$_res && $_to_confirm[] = $_res['title'].".".$_res['suffix'];
			}
		}
		$result = new stdClass();
		$result->status = 0;
		if(!empty($_to_confirm)){
			$result->status = 1;
			$result->data = $_to_confirm;
			$msg = "“".implode("，", $_to_confirm)."”是某一文章附件";
			$result->msg = $msg;
		}
		exit(json_encode($result));
	}
	
	/**
	 * 删除网关资源时，同时删除资源类教研应用里的附件数据
	 */
	private function deleteAttachRes($rid) {
		if (empty($rid)) {
			return false;
		}

		$paper_del_result = D("PaperAttach", "paper")->where(array("attach_id" => $rid, "attach_type" => 1))->delete();
		$notice_del_result = D("NoticeAttach")->where(array("attachId" => $rid, "attach_type" => 1))->delete();

		Log::write("删除同步至网关的资源{$rid}：r1:" . $paper_del_result . "r2:" . $notice_del_result, Log::DEBUG);

		return true;
	}


	/**
	 * 删除资源
	 */
	function deleteRes()
	{
		if(!$this->login){
			exit('{"status":0,"msg":"非法操作!"}');
		}
		
		$operationids =  isset($_REQUEST['opertionids']) ? $_REQUEST['opertionids'] : "";
		$operationtype = isset($_REQUEST['operationtype']) ? $_REQUEST['operationtype'] : "";
		
		switch ($operationtype){
			case 5:
				$operationtype = "upload";
				break;
			case 4:
				$operationtype = "download";
				break;
			case 3:
				$operationtype = "collection";
				break;
			default:
				$operationtype = "";
		}
		if(empty($operationids) || !$operationtype){
			exit('{"status":0,"msg":"传入参数错误！"}');
		}
		for ($i = 0; $i < count($operationids); $i++){
			$opertionid = $operationids[$i];
			$result = $this->_deleteResource($opertionid, $this->login, $operationtype);
			if(!$result){
				exit('{"status":0,"msg":"删除‘'.$opertionid.'’失败！"}');
			}
		}
		exit('{"status":1,"msg":"删除成功！"}');
	}
	
	/**
	 * 上传资源完成后，提交资源信息失败时，将上传的资源删除
	 */
	public function deleteRid(){
		$rid =  isset($_REQUEST['rid']) ? $_REQUEST['rid'] : "";
		if(!$rid){
			echo '{"status":0,"data":"参数rid为空"}';
		}
		$obj = $this->_resclient->Res_DeleteResource($rid);
		if($obj->statuscode == 200){
			echo '{"status":1,"data":"删除成功"}';
		} else{
			echo '{"status":0,"data":"'.$obj->data.'"}';
		}
	}
	
	/**
	 * 资源推荐
	 * @author sjzhao
	 */
	function getResRecommend(){
		$user=$GLOBALS['ts']['user'];
		$grade=$user['grade']?$user['grade']:'';//年级
		$subject=$user['subject']?$user['subject']:'';//学科
		$result =  $this->_getrecommendedresources($grade,$subject);
		foreach ($result['data'] as &$value){
			$value['shortTitle']=getShort($value['title'],10,'...');
		}
		exit(json_encode($result));
	}
	/**
	 * 获取好友推荐资源
	 * @author sjzhao
	 */
    function getFriendRecommendRes(){
    	$login=$GLOBALS['ts']['user']['login'];//登录名
    	$result = D("ResourceRecommend")->getResByCondition($login, array(), 5);
    	if(empty($result['data'])){
    		$result['status']=0;
    	}else{
    		$data=$result['data'];
    		foreach ($data as &$value){
    			$value['subjectTitle']=$value['title'];
    			$value['shortTitle']=getShort($value['title'],10,'...');
    			$value['url']=C('RS_SITE_URL');
    			$value['id']=$value['rid'];
    		}
    		$result['data']=$data;
    		$result['status']=1;
    	}
    	exit(json_encode($result));
    }
	/**
	 * 推荐资源给好友
	 */
	public function friendRecommend(){
		$friendUid = isset($_REQUEST['friend_uids']) ? $_REQUEST['friend_uids'] : array();
		$rid = isset($_REQUEST['rid']) ? $_REQUEST['rid'] : "";
		if(!$rid || empty($rid)){
			exit('{"status":0,"msg":"参数错误！"}');
		}
		if(!is_array($friendUid)){
			$friendUid = array($friendUid);
		}
		$resource_id = D('Resource')->where(array("rid"=>$rid))->getField("id");
		if(!$resource_id){
			$res = $this->_initResinfoByGateinfo($rid);
			//如果资源信息表中没有这条资源的信息，则重新插入记录
			$resource_id = D('Resource')->increase($res);
			if(!$resource_id){
				exit('{"status":0,"msg":"插入资源到数据库失败"}');
			}
		}
		foreach($friendUid AS $_fuid){
			$user_login = D("User")->where(array("uid"=>$_fuid))->getField("login");
			$res_opr = array();
			$res_opr['resource_id'] = $resource_id;
			$res_opr['uid'] = $this->user['uid'];
			$res_opr['login_name'] = $user_login;
			$res_opr['dateline'] = time();
			$r = D('ResourceRecommend')->increaseRes($res_opr);
			if(!$r){
				exit('{"status":0,"msg":"推荐资源给好友“'.$user_login.'”失败"}');
			}
		}
		exit('{"status":1,"msg":"推荐资源给好友成功！"}');
	}
	
	/**
	 * 通过网关资源信息初始化本地资源信息
	 * @param string $rid,必须提供， 资源id
	 *
	 */
	private  function _initResinfoByGateinfo($rid){
		$gateresinfos =$this->_resclient->Res_GetResIndex($rid);
		if($gateresinfos && $gateresinfos->statuscode == 200 && count($gateresinfos->data)>0){
			$data = $gateresinfos->data[0];
			$res = $this->_getInitLocalResourceArray($data);
		}else{
			return array();
		}
		return $res;
	}
	
	/**
	 * 删除资源信息
	 * @param string $rid,必须提供,资源id
	 * @param int $uid,必须提供,用户id
	 * @param string $opertiontype,必须提供,操作类型
	 *
	 * 成功返回1，失败返回false
	 */
	private function _deleteResource($rid, $login, $operationtype){
		if(!$rid || !$login || !$operationtype){
			return false;
		}
		$operationtype = strtolower($operationtype);
	
		switch ($operationtype){
			case 'collection':
				$optype = ResoperationType::COLLECTION;
				break;
			case 'upload':
				$optype = ResoperationType::UPLOAD;
				break;
			case 'download':
				$optype = ResoperationType::DOWNLOAD;
				break;
			case 'deliver':
				$optype = ResoperationType::DELIVER;
				break;
			default:
				return false;
		}
		if($optype == ResoperationType::UPLOAD){
			/**------查询出资源的大小 by xypan 10.9---------*/
			$fields = array("general");
			$resourceInfo=$this->_resclient->Res_GetResources(array("id"=>$rid),$fields);
			$fileSize = $resourceInfo->data[0]->general->length;
			$fileSize = intval($fileSize);
			/*------------------end-------------------*/
				
			$data =array();
			$data['is_del'] = 1;
			$delete_result = D('Resource')->where(array("rid"=>$rid, "creator"=>$login))->save($data);
			//删除网关资源
			$deleteres = $this->_resclient->Res_DeleteResource($rid);
			if($deleteres->statuscode != 200){

				Log::write($login."删除网关资源".$rid."失败！", Log::ERR);

			}
			
			/**判断当前登录用户的资源库容量是否已经有记录,修复使用资源库容量时资源库已有资源的BUG by xypan 10.12**/
			$loginName = $this->user['login'];
			$result = D("ResourceCapacity")->getCapacityInfoByLogin($loginName);
			if(empty($result))
				return false;
			if($result['usedCapacity'] < $fileSize){
				D('ResourceCapacity')->where("login_name='$loginName'")->setField('used_capacity',0);
			}else{
				D('ResourceCapacity')->decUsedCapacity($loginName, $fileSize);
			}
			/*----------end--------------*/
		}
		$resource_id = D('Resource')->where(array("rid"=>$rid))->getField('id');
		$login = $GLOBALS['ts']['cyuserdata']['user']['login'];
		$res = D('ResourceOperation')->deleteRes($resource_id, $login, $optype);

		// 删除同步至网关的资源
		$this->deleteAttachRes($rid);

		return $res;
	}

	/**
	 * 获取推荐资源
	 * 策略：优先推荐老师所在年级学科，并按下载量由高到底，每个类型推荐5个资源
	 * @param int $uid,用户uid
	 * @param string $type,资源类型：教案、试题、媒体素材...
	 *
	 */
	private function _getrecommendedresources($grade, $subject, $limit=5){

		//下载次数降序
		$order = "-statistics.downloadcount";
		$skip = 0;
		$condition = array(
				'order'=>$order,
				'skip'=>$skip,
				'limit'=>$limit
		);
		$fields = array(
				"id",
				"date",
				"general",
				"lifecycle",
				"properties",
				"statistics",
				"tags"
				);
		$_map = array();
		//若果年级和学科为空时，则不根据年级和学科查询
		$grade &&  $_map['grade'] = $grade;
		$subject &&  $_map['subject'] = $subject;
		$conditions = array_merge($condition, $_map);
		if(C('ENABLE_SECURITY')==1){//是否启用安全监管监测的结果
			$conditions['lifecycle.securitystatus']='-(2 or 3 or 4)';
		}
		$result = S(md5(json_encode($conditions)));
		if(!$result){
			$obj = $this->_resclient->Res_GetResources($conditions, $fields);
			$resList = $obj->statuscode == 200?$obj->data:array();
			//如果为空时，则不再根据学科和年级的资源进行推荐，重新获取全站推荐资源
			if(empty($resList) && $grade && $subject){
				$obj = $this->_resclient->Res_GetResources($conditions, $fields);
				$resList = $obj->statuscode == 200?$obj->data:array();
			}
			$result=array();
			$result['operationtype']='upload';//查询资源类型
			$result['currentpage']=1;//当前页码
			$result['totalRows'] = count($obj->data);//总记录数
			$result['totalpage']=1;//总页数
			$result['data']= array();
			$resType = array();
			$resType['0302'] ='文档' ;
			$resType['0303'] ='图片' ;
			$resType['0304'] ='音频' ;
			$resType['0305'] ='视频' ;
			$resType['0306'] ='动画' ;
			$resType['1205'] ='卡包' ;
			foreach ($obj->data as $aRes){
				$localres =	$this->_getInitLocalResourceArray($aRes);
				$localres['id'] = $localres['rid'] ;
				$localres['subjectTitle'] = $localres['title'];
				$localres['realname'] = '讯飞资源';
				$result['data'][]=$localres;
			}
			if(!empty($result['data'])){
				$result['status']=1;
				$html='';
				$data=$result['data'];
				foreach ($data as $key=>$value){
					$data[$key]['url']=C('RS_SITE_URL');
				}
				$result['data']=$data;
			}else{
				$result['status']=0;
			}
			S(md5(json_encode($conditions)),$result,3600);
		}
		
		return $result;
	}
	/**
	 * 上传资源
	 * $rid  	资源id
	 * $restype  资源类型
	 * 
	 */
	private  function _uploadResource($rid,$restype,$msgid=""){
		if(!$rid){
			return false;
		}
		//初始化资源信息
		$res = $this->_initResinfoByGateinfo($rid);
		if(empty($res)){
			return false;
		}
		
		//构建本地上传记录
		$res['username'] = $res['creator'];
		$res['restype'] = $restype;
		$res['product_id'] = "rrt";
	
		$uid = $this->mid;
		$cyuserdata = $this->cyuserdata;
		$res['province'] = $cyuserdata['locations']['province']['id'];//省
		$res['city'] = $cyuserdata['locations']['city']['id'];//市
		$res['county'] = $cyuserdata['locations']['district']['id'];//区县
		//所在学校id
		$schools = $cyuserdata['orglist']['school'];
		$school = array_pop($schools);
		$res['school_id'] = $school['id'];
		
		//在资源表中添加记录
		$result1 = D('Resource')->increase($res);
		if($result1){
			//保存上传记录
			$res_opr = array();
			$res_opr['resource_id'] = $result1;
			//依据名师工作室id是否为空，判断此次上传是普通上传还是名师工作室上传
			$res_opr['operationtype'] = empty($msgid)? ResoperationType::UPLOAD:ResoperationType::MSGROUP_UPLOAD;
			$res_opr['dateline'] = $res['uploaddateline'];
			$res_opr['login_name'] = $res['creator'];
			!empty($msgid) && $res_opr['gid'] = $msgid; 
			//添加资源操作信息（上传、下载、收藏等）
			$result2 = D('ResourceOperation')->saveOrUpdate($res_opr) > 0;
			//名师工作室上传资源同步到成果展示:教学设计、教学课件、媒体素材
			if(!empty($msgid)){
				unset($res_opr['gid']);
				$res_opr['operationtype'] = ResoperationType::UPLOAD;
				$result2 = D('ResourceOperation')->saveOrUpdate($res_opr) > 0;
			}
		}
		
		return $result1 && $result2;
	}
	
	/**
	 * 下载资源的私有方法
	 * @param string $rid
	 * @param string $_filename
	 */
	private function _downloadFile($rid, $filename,$isMarket = false){
		// 	下载网关资源
		$downloadres = $this->_resclient->Res_GetResIndex($rid);

		//如果下载失败
		if(!$downloadres || $downloadres->statuscode != 200 || $downloadres->data[0]->file_url == ""){
			return false;
		}

        if($isMarket){
            $filename = $downloadres->data[0]->general->title.".".$downloadres->data[0]->general->extension;
        }
		
		$downloadncount = $downloadres->data[0]->statistics->downloadcount;
		$downloadncount = empty($downloadncount)?0:intval($downloadncount);
		$downloadncount++;
		$this->_resclient->Res_UpdateStatistics($rid,'downloadcount',$downloadncount);
		unset($downloadncount);

		$_file = $downloadres->data[0]->file_url;
		$filename =  str_replace(',',' ',$filename);
		$filename =  rawurlencode($filename);
		$flag = strpos($_file,"?");
	    $location = '';
	    if($flag){
	      	$location = $_file.'&filename='.$filename;
	    }else{
	      	$location = $_file.'?filename='.$filename;
	    }
	    header("Location: $location");

		return true;
	}
	
	/**
	 * 将文件切割
	 * @param string $filename
	 * @param boolean $retbytes
	 * @return boolean|number
	 */
	private function _readfile_chunked($filename, $retbytes=true) {
		$chunksize = 512 * 1024; // how many bytes per chunk
		$buffer = '';
		$cnt =0;
		$handle = fopen($filename, 'rb');
		if ($handle === false) {
			return false;
		}
		while (!feof($handle)) {
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if ($retbytes) {
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status) {
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	
	}
	
	/**
	 * 成功返回本地资源资源信息数组对象
	 * @param array $res 从网关获取的资源信息
	 */
	private  function _getInitLocalResourceArray($res){
		//上传信息记录到本地
		$reslocal=array();
		if(!$res){
			return $reslocal;
		}
		else{
			$reslocal['rid'] =$res->general->id;
			if(is_array($res->tags)){
				$reslocal['keywords'] =$res->tags[0];
			}
			$reslocal['description'] =$res->general->description;
			$reslocal['username'] = $res->general->creator;
			$reslocal['creator'] = $res->general->creator;
			$reslocal['size'] = $res->general->length;
			$reslocal['uploaddateline'] = strtotime($res->date->uploadtime);
			$reslocal['suffix'] = strtolower($res->general->extension);
			$reslocal['type1'] = !empty($res->properties->type)?$res->properties->type[0]:'0000';
			$reslocal['type2'] = GetResType_Level2('.'.strtolower($res->general->extension));
			$reslocal['downloadtimes'] = !empty($res->statistics->downloadcount)?$res->statistics->downloadcount:0;
			$reslocal['praisetimes'] =  !empty($res->statistics->up)?$res->statistics->up:0;
			$reslocal['negationtimes'] =  !empty($res->statistics->down)?$res->statistics->down:0;
			$reslocal['praiserate'] = round($res->statistics->reputablerate ? $res->statistics->reputablerate : 0);
			$reslocal['source'] =  $res->general->source;
			//将平分转换为5分制
			$reslocal['score'] = round(($res->statistics->score ? $res->statistics->score : 0)*1.0/20, 1);
			$reslocal['grade'] =  !empty($res->properties->grade)?$res->properties->grade[0]:'';
			$reslocal['subject'] =  !empty($res->properties->subject)?$res->properties->subject[0]:'';
			$reslocal['restype'] =  $reslocal['type1'];
			return $reslocal;
		}
	}
	
	/**
	 * 快速上传通道
	 */
	public function uploadToServer(){
		set_time_limit(0);

		Log::write("执行上传方法", Log::DEBUG);

		if (!empty($_FILES)) {
			try{
				$_login = isset($_POST['login']) ? $_POST['login'] : "";
				if(!$_login){
					exit('{"status":0,"msg":"参数空"}');
				}
				$file = $_FILES['Filedata']['tmp_name'];
				$fileParts = pathinfo($_FILES['Filedata']['name']);

				// 处理Linux环境下后缀名大写问题
				if (isset($fileParts['extension'])) {
					$fileParts['extension'] = strtolower($fileParts['extension']);
				}

				$file_path = date('/Y/md/H/');
				@mkdir(UPLOAD_PATH.$file_path,0777,true);
				$file_name = uniqid().'.'.$fileParts['extension'];
				move_uploaded_file($file, UPLOAD_PATH.$file_path.$file_name);
				if(file_exists(UPLOAD_PATH.$file_path.$file_name)){
					exit('{"status":1,"savepath":"'.$file_path.'","savename":"'.$file_name.'"}');
					Log::write($this->login."上传文件到服务器");
				}else{
					exit('{"status":0,"msg":"上传失败"}');
				}
			}catch(Exception $e){
				exit('{"status":0,"msg":"'.$e->getMessage().'"}');
			}
		}
		Log::write("上传文件失败，未获取file");
	}
	
	/**
	 * 提交信息
	 */
	public function uploadToGateWay(){
		
		$type = isset($_POST['type']) ? intval($_POST['type']) : 0;
		if($type !== 1 && $type !== 0){
			exit('{"status":0,"msg":"非法调用"}');
		}
		
		$savepath = isset($_POST['savepath']) ? $_POST['savepath'] : "";
		$savename = isset($_POST['savename']) ? $_POST['savename'] : "";
		$title = isset($_REQUEST['title']) ? $_REQUEST['title'] : "";
		$size = intval($_REQUEST['size']);
		
		//查询容量
		$loginName = $this->user['login'];
		$result = D("ResourceCapacity")->getCapacityInfoByLogin($loginName);
		if(empty($result)){
			exit('{"status":0,"msg":"查询容量错误"}');
		}
		$capacity_exceed = false;
		if(($result['usedCapacity'] + intval($size)) > $result['totalCapacity']){
			//如果超出容量，则将资源以附件形式上传
			$type = 0;
			$capacity_exceed = true;
		}
		
		if($type == 1){
			//将上传附件同步到网关
			$subject  = isset($_REQUEST["subject"]) ? $_REQUEST["subject"] : "00";
			$publisher = isset($_REQUEST["publisher"]) ? $_REQUEST["publisher"] : "";
			$grade = isset($_REQUEST["grade"]) ? $_REQUEST["grade"] : "00";
			$volumn = isset($_REQUEST["volumn"]) ? $_REQUEST["volumn"] : "";
			$book = isset($_REQUEST["book"]) ? $_REQUEST["book"] : "";
			$unit = isset($_REQUEST["unit"]) ? $_REQUEST["unit"] : "";
			$course = isset($_REQUEST["course"]) ? $_REQUEST["course"] : "";
			$restype = isset($_REQUEST['restype']) ? $_REQUEST['restype'] : "0000";
			$res_source = $_REQUEST['ressource'];
			$keywords = $_REQUEST['keywords'];
			$description = $_REQUEST['description'];
			
			$res_info = array();
			$res_info['title'] = $title;
			$res_info['auditstatus'] = "1";
			$res_info['productid'] = 'rrt';
			$res_info['creator'] = $this->login;
			$res_info['extension'] = substr($title, strrpos($title, "."));
			$res_info['source'] = $res_source;
			$res_info['subject'] = $subject;
			$res_info['grade'] = $grade;
			$res_info['publisher'] = $publisher;
			$res_info['volumn'] = $volumn;
			$res_info['book'] = $book;
			$res_info['unit'] = $unit;
			$res_info['type'] = $restype;
			$res_info['rrtlevel1'] = "08";
			$res_info['province']=$GLOBALS['ts']['_cyuserdata']['locations']['province']['id'];
			$res_info['city']=($GLOBALS['ts']['_cyuserdata']['locations']['city']['id']);
			$res_info['district']=array($GLOBALS['ts']['_cyuserdata']['locations']['district']['id']);
			$res_info['school']=array(array_keys($GLOBALS['ts']['_cyuserdata']['orglist']['school'])[0]);
			
			if(!empty($keywords)){
				$res_info['keyword'] = $keywords;
			}
			if(!empty($description)){
				$res_info['description'] = $description;
			}
			
			if(!empty($course)){
				$res_info['course'] = $course;
			}
			$upload_file = UPLOAD_PATH . $savepath . $savename;
			try{

				Log::write($this->login."开始上传资源".$res_info->general->title, Log::DEBUG);

				$result =$this->_resclient->Res_UploadRes($upload_file, $res_info);
			}catch (Exception $e){
				Log::write($e->getMessage());
			}
			//删除保存在本地服务器的文件
			if(file_exists($upload_file)){
				@unlink($upload_file);
			}
			if($result->statuscode == 200){
				//上传到网关的资源ID
				$rid = $result->data;

				$return = $this->_uploadResource($rid,$restype);
				if($return){

					/* 资源上传成功,增加资源库容量 by xypan 10.9*/
					$loginName = $this->user['login'];
					D('resource_capacity')->setInc('used_capacity',array('login_name'=>$loginName),$size);
					/*-------------------end-----------------------------*/
					
					/* 上传资源增加积分 by sjzhao  12.13*/
					D('UserCredit','api')->addCreditByUid($this->user['uid'],1);
					/* 增加积分结束 */
					Log::write($this->login."上传文件成功:".$rid, Log::DEBUG);

					exit('{"status":1,"id":"'.$rid.'","type":'.$type.'}');
				} else{

					Log::write("上传文件信息失败", Log::ERR);

					exit('{"status":0,"msg":"保存文件信息到数据库失败","type":'.$type.'}');
				}
			}else{

				Log::write($this->login."上传".$res_info->general->title."到网关失败，原因：".$result['data'], Log::ERR);

				exit('{"status":0,"msg":"'.$result->data.'","type":'.$type.'}');
			}
		}else{
			$data = array();
			$data['save_path'] = substr($savepath, 1);
			$data['appname'] = 'reslib';
			$data['attach_type'] = 'attach_file';
			$data['from'] = 0;
			$data['uid'] = $this->mid;
			$data['ctime'] = time();
			$data['name'] = $title;
			$data['size'] = $size;
			$data['save_name'] = $savename;
			$data['extension'] = substr($title, strrpos($title, ".") + 1);
			$data['hash'] = hash(UPLOAD_PATH.$savepath.$savename);
			//将上传附件存储到本地
			$attach_id = D("Attach")->add($data);
			if($attach_id){
				if($capacity_exceed == true){
					exit('{"status":201,"id":"'.$attach_id.'","type":'.$type.'}');
				}else{
					exit('{"status":1,"id":"'.$attach_id.'","type":'.$type.'}');
				}
			}else{
				exit('{"status":0,"id":"保存附件到数据库失败","type":'.$type.'}');
			}
		}
	}
	/**
	 * 附件上传
	 */
	public function attachUpload(){
		set_time_limit(0);
	
		Log::write("执行上传方法", Log::DEBUG);
	
		if (!empty($_FILES)) {
			try{
				$_login = isset($_POST['login']) ? $_POST['login'] : "";
				if(!$_login){
					exit('{"status":0,"msg":"参数空"}');
				}
				$file = $_FILES['Filedata']['tmp_name'];
				$fileParts = pathinfo($_FILES['Filedata']['name']);

				// 处理Linux环境下后缀名大写问题
				if (isset($fileParts['extension'])) {
					$fileParts['extension'] = strtolower($fileParts['extension']);
				}
	
				$targetFolder = UPLOAD_PATH;
				if(!is_dir($targetFolder)){  //如果不存在该文件夹
					mkdir($targetFolder, 0777);  //创建文件夹
				}
				chmod($targetFolder, 0777);  //改变文件模式
				$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
				move_uploaded_file($file, $targetFile);
				$res_info->general->title = $fileParts['filename'].'.'.$fileParts['extension'];
	
				Log::write($_login."开始上传附件".$fileParts['filename'].'.'.$fileParts['extension'], Log::DEBUG);
				
				$filePath = md5($_login.time().rand(1,1000)) . "/" .$_FILES['Filedata']["size"]."/".$fileParts['filename'].'.'.$fileParts['extension'];
				try{
					$result = AttachServer::getInstance()->upload($targetFile, $filePath);
				}catch (Exception $e){
					Log::write($e->getMessage());
				}
				if(file_exists($targetFile)){
					@unlink($targetFile);
				}
				Log::write("上传资源到附件网关的结果：".json_encode($result));
				if($result['errorCode'] == 0){
					Log::write($_login."上传".$res_info->general->title."到附件网关成功，ID为：'".$result->data."'", Log::DEBUG);
					exit(json_encode($result));
				}else{
					Log::write($_login."上传".$res_info->general->title."失败，".$result['data'], Log::ERR);
					exit('{"status":0,"msg":"'.json_encode($result).'"}');
				}
			}catch(Exception $e){
				exit('{"status":0,"msg":"'.$e->getMessage().'"}');
			}
		}
		Log::write("上传文件失败，未获取file");
	}
	
	/**
	 * 删除网关附件
	 */
	public function deleteAttach(){
		
		$rid =  isset($_REQUEST['rid']) ? $_REQUEST['rid'] : "";

		$result = AttachServer::getInstance()->deleteFile($rid);
		if($result){
			exit('{"status":1,"msg":"删除成功！"}');
		}else{
			exit('{"status":0,"msg":"删除失败！"}');
		}
		
		
	}
	
	
	/**************************************************************
	 * 收藏资源
	* @param string $rid,必须提供， 资源id
	* @param string $login,必须提供， 收藏者登录名
	* 成功返回1，失败返回0
	*
	*************************************************************/
	public function collectResource(){
		$rid = $_REQUEST['rid'];
		if(!$rid || !$GLOBALS['ts']['user']['login']){
			exit(json_encode(array('status'=>0,'info'=>"操作异常！")));
		}
		//构建本地上传记录
		$res = $this->_initResinfoByGateinfo($rid);
		$res['product_id'] = isset($_REQUEST['productId'])? $_REQUEST['productId']: "other";
		$resoucr_id = D('Resource')->where(array("rid"=>$rid))->getField("id");
		if(!$resoucr_id){
			//如果资源信息表中没有这条资源的信息，则重新插入记录
			$resoucr_id = D('Resource')->increase($res);
			if(!$resoucr_id){
				exit(json_encode(array('status'=>0,'info'=>"收藏的资源不存在或已被删除！")));
			}
		} else {
			//更新数据库信息
			$info = array();
			$info['downloadtimes'] = $res['downloadtimes'];
			$info['praisetimes'] = $res['praisetimes'];
			$info['negationtimes'] = $res['negationtimes'];
			$info['praiserate'] =$res['praiserate'];
			$info['score'] =$res['score'];
			$res_update = D('Resource')->updateRes($rid, $info);
		}

		//保存下载记录
		$res_opr = array();
		$res_opr['resource_id'] = $resoucr_id;
		$res_opr['login_name'] = $GLOBALS['ts']['user']['login'];
		$res_opr['operationtype'] = ResoperationType::COLLECTION;
		$res_opr['dateline'] = time();
		$result = D('ResourceOperation')->saveOrUpdate($res_opr);
		
		switch(intval($result)){
			case 1:
				D('UserCredit','api')->addCreditByUname($GLOBALS['ts']['user']['login'],1);
				exit(json_encode(array('status'=>1,'info'=>"收藏成功！")));
				break;
			case -1:
				exit(json_encode(array('status'=>1,'info'=>"已收藏过该资源！")));
				break;
			case 0:
			case -2:
				exit(json_encode(array('status'=>0,'info'=>"收藏失败！")));
				break;
		}
	}
	
	/**
	 * 收藏资源至云盘
	 */
	public function collectYunpan(){
		$rid = $_REQUEST['rid'];
		if(!$rid || !$GLOBALS['ts']['user']['login']){
			exit(json_encode(array('status'=>0,'info'=>"操作异常！")));
		}
		$result = D('YunpanFavorite','yunpan')->collectResource($this->login,$rid,false);
		Log::write("收藏结果：".json_encode($result),Log::DEBUG);
		if($result['status']){
			$resService = new ResourceClient();
			$res_favtime_info = $resService->Resource_GetStatistics($rid, 'favtimes');
			
			$inc_value = $resService->Res_UpdateStatistics($rid, 'favtimes', $res_favtime_info->data->statistics->favtimes + 1);
			Log::write("收藏加一结果：".json_encode($inc_value),Log::DEBUG);
		}
		echo json_encode($result);
	}
	
	/**
	* 根据资源id检验某资源当前状态，删除与否
	*/
	public function checkResExist(){
		$rid = $_REQUEST['rid'];
		$obj =$this->_resclient->Res_GetResIndex($rid);
		$resourceInfo = $obj->data[0];
		if(empty($resourceInfo) || $resourceInfo->lifecycle->curstatus!="1"){
			exit(json_encode(array("status"=>0,"info"=>"当前资源不存在或未被审核,暂时无法操作！")));
		}else{
			exit(json_encode(array("status"=>1,"info"=>"成功获取资源信息")));
		}
	}
	
	//////////////////////////////////////////////////////////////////////
	public function getNodes(){
		import();
		$node = $_REQUEST['node'];
		$subject = $_REQUEST['subject'];
		$edition = $_REQUEST['edition'];
		$stage = $_REQUEST['stage'];
		$condition = array();
		if (empty($subject)&&empty($edition)&&empty($stage)){
			// 获取学科和年级
	        $subjects = model('Node')->subjects;
	        $subjects = array_slice($subjects, 1);
			echo json_encode($subjects);
			return true;
		}
		if (!empty($subject)) {
			$condition = array_merge($condition, array('subject' => $subject));
		}
		
		if (!empty($edition)) {
			$condition = array_merge($condition, array('edition' => $edition));
		}
		
		if (!empty($stage)) {
			$condition = array_merge($condition, array('stage' => $stage));
		}
		$resourceCommon=D('ResourceCommon');
		$skey = md5(json_encode($condition)).$node;
		$obj = S('s_node_'.$skey);
		if(empty($obj)){
			$obj = $resourceCommon->getNodes($condition,$node);
			S('s_node_'.$skey,$obj,3600);
		}
		$categoroys= $obj;
		echo json_encode($categoroys);
	}
	
	
	public function getUnits(){
		$subject = $_REQUEST['subject'];
		$edition = $_REQUEST['edition'];
		$stage = $_REQUEST['stage'];
		$condition = array();
		$condition['subject']=$subject;
		$condition['edition']=$edition;
		$condition['stage']=$stage;
		$skey = md5(json_encode($condition)).$node;
		$obj = S('s_book_'.$skey);
		if (empty($obj)){
			//获取书本列表
			$resourceCommon=D('ResourceCommon');
			$bookList=$resourceCommon->getNodes($condition,'book');
			$book=$bookList[0];
			$unitList = $resourceCommon->getUnitsByBookId($book->id);
			$obj['book']=$book;
			$obj['data']=$unitList;
			S('s_book_'.$skey,$obj,3600);
		}
		echo json_encode($obj);
	}
	/////////////////////////////////////////////////////////////////////
}
?>