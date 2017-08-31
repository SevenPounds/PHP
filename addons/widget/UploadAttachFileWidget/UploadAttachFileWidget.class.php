<?php
/**
 * @since 2014/1/9
 * @author cheng
 * @example {:W('UploadAttachFile',array('uploadType'=>'file','urlquery'=>'attach_type=research_file&upload_type=file','attachIds'=>'','preview'=>1,'cloudsave'=>0))}
 * uploadType: file ,video
 * urlquery： 请求url ( upload_type=video&preview=1：  视频文件带预览 jquery media; upload_type=file：  普通文件)
 * attachIds： 附件id 
 * cloudsave: 是否来自附件服务器附件  1 是，0 否
 * message :按钮提示消息
 * total : 上传文件数量限制 ，默认：3
 * fileSizeLimit : 上传文件大小限制  ，默认视频110M，文件：10M
 * uploader: 文件上传url ,默认本地 U('widget/UploadAttachFile/save')  ,附件服务器保存url :U('widget/UploadAttachFile/cloudsave')
 */
class UploadAttachFileWidget extends Widget{
	
	public function render($data){
		$var = array();
		$var['attach_type']  = isset($data['attach_type'])?$data['attach_type']:'feed_file';
		$var['inputname']   = 'attach';
		$var['attachIds']   = $data["attachIds"];
		$var['total'] = isset($data['total'])?$data['total']:3; //当前默认上传个数为3
		$var['used'] = 0;//已上传文件数
		$uploader = isset($data['uploader'])?$data['uploader'] : U('widget/UploadAttachFile/save');
		unset($data['uploader']);
		$var['uploader'] = $uploader.'&'.$data['urlquery'];
		//判断是否生成预览
		$var['uploader'] = isset($data['preview'])? $var['uploader'].'&preview='.$data['preview'] :$var['uploader'];
		
		$var['unid'] = substr(strtoupper(md5(uniqid(mt_rand(), true))), 0, 8);
		$var['message'] = isset($data['message'])?$data['message']:'选择上传文件';
		// 设置渲染变量
		$uploadType = in_array($data['uploadType'],array('file','video'))?t($data['uploadType']):'file';
		if($uploadType === 'video'){
			$ext = array('avi','wmv','asf');
			foreach ($ext as $value) {
				$var['fileTypeExts'] .= '*.'.strtolower($value).'; ';
			}
			$var['fileSizeLimit']  = isset($data['fileSizeLimit'])?$data['fileSizeLimit']:'10MB';
		}else{
			$var['fileTypeExts'] .= '*.*';
			$var['fileSizeLimit']  = isset($data['fileSizeLimit'])?$data['fileSizeLimit']:'110MB';
		}
		$defualt_total = $data['uploadType'] === 'video' ? 1 : 3;
		$var['total'] = isset($data['total'])?$data['total']:$defualt_total; //当前默认上传个数为1
		is_array($data) && $var = array_merge($var,$data);
		
		$uploadTemplate = $var['preview']==1? $uploadType.'upload.html' :'fileupload.html';
		if(!empty($var['attachIds'])){
			
			//!is_array($var['attachIds']) && $var['attachIds'] = explode('|',$var['attachIds']);
             $attachList = explode(",",$var['attachIds']);
			//判断是否来自附件服务器
			//if(isset($var['cloudsave']) && $var['cloudsave'] == 1){
				foreach ($attachList as $attach_id){
                    if(isset($data["cloudsave"])&&$data["cloudsave"] == 1 ){
                        $attachInfo = $this->apis_client->getConvert($attach_id);
                        if($attachInfo){
                            $attachInfo = json_decode($attachInfo->results);
                        }else{
                            $attachInfo = $this->apis_client->getFile($attach_id);
                        }
                    }else{
                        $attachInfo = $this->apis_client->getFile($attach_id);
                    }
					//$attachInfo = AttachServer::getInstance()->getFileInfo($attach_id);
					
					if($attachInfo->status == 2){
						$v = array();
						$paramArray = explode("/",$attachInfo->url);
						$c = count($paramArray);
						$v['name'] = rawurldecode($paramArray[$c - 1]);
						$v['shortname'] = getShort($v['name'],6,'...');
						$v['size'] =  $attachInfo->length;
						$v['downoLoadUrl'] = $attachInfo->url;
						$v['extension'] = substr(strrchr($v['name'], '.'), 1);
						$v['attach_id'] = $attach_id;
						if($var['uploadType'] === 'video' && $var['preview']==1){
							$v['src']   = $attachInfo->url;
						}else{
							$v['src'] = getExtImageUrl(strtolower($v['extension']));
						}
						$var['attachInfo'][] = $v;
						$var['used'] ++;
						unset($v);
					}elseif($attachInfo['errorCode'] == 404){
						$v = array();
						$v['name'] = "该文件不存在或已被删除！";
						$v['shortname'] = getShort($v['name'],6,'...');
						$v['src'] = getExtImageUrl("");
						$v['attach_id'] = $attach_id;
						$var['attachInfo'][] = $v;
						$var['used'] ++;
					}
				}
			/*}else{
				$attachInfo = model('Attach')->getAttachByIds($var['attachIds']);
				foreach($attachInfo as $v){
					if($var['uploadType'] === 'file'){
						$data = pathinfo($v['save_name']);
						$v['src']   = getExtImageUrl($data['extension']);
					}else{
						$v['src']   = UPLOAD_URL.'/'.$v['save_path'].$v['save_name'];
					}
					$v['extension']  = strtolower($v['extension']);
					$var['attachInfo'][] = $v;
					$var['used'] ++;
				}
			}*/
			$var['attachIds'] = implode('|',$attachList);
		}
		
		//渲染模版
		$content = $this->renderFile(dirname(__FILE__)."/".$uploadTemplate,$var);
		
		unset($var,$data);
		
		//输出数据
		return $content;
	}
	
	
	/**
	 * 附件上传
	 * @return array 上传的附件的信息
	 */
	public function save(){
	
		$data['attach_type'] = t($_REQUEST['attach_type']);
		$data['upload_type'] = $_REQUEST['upload_type']?t($_REQUEST['upload_type']):'file';
		
		$preview  = intval($_REQUEST['preview']);
		/* 图片预览剪切
		$thumb  = intval($_REQUEST['thumb']);
		$width  = intval($_REQUEST['width']);
		$height = intval($_REQUEST['height']);
		$cut    = intval($_REQUEST['cut']);
		 */
		//Addons::hook('widget_upload_before_save', &$data);
		$option['attach_type'] = $data['attach_type'];
		isset($_REQUEST['cid']) && $option['cid']=$_REQUEST['cid'];
		$option['uname']=$_REQUEST['uname'];

       /* $params = array(
            "filePath" => $filePath,
            "callbackMethod" => "GET",
            "callbackUrl" => $this->host,
            "module" => "网上评课",
            "action" => "上传评课"
        );*/
       // $before_info = $this->apis_client->uploadFileOnBefore($params);
       // $file_info = $this->apis_client->uploadFile($before_info->contextId,$targetFile);
        $info = $this->attchSave();
		//$info = model('Attach')->upload($data, $option);
		//Addons::hook('widget_upload_after_save', &$info);
		//
		if($info['result']){
			$data = $info;
			//$data['extension']  = strtolower($data['extension']);
            $data['extension'] = $info["extension"];
			if($preview == 1 && t($_REQUEST['upload_type']) === 'video' ){
				$data['src'] = $info["url"];
			}else{
				$data['src'] = getExtImageUrl($data['extension']);
			} 
			$data['shortname'] = getShort($info['name'],6,'...');
            $data["attach_id"] = $info["result"]->contextId;
			$return = array('status'=>1,'data'=>$data);
		}else{
			$return = array('status'=>0,'data'=>$info['info']);
		}
		echo json_encode($return);exit();
	}


    private  function  attchSave(){
        if (!empty($_FILES)) {
            try {
                $_login = $GLOBALS['ts']['user']['login'];
                if (!$_login) {
                    exit('{"status":0,"data":"终止非法操作！"}');
                }
                $file = $_FILES['Filedata']['tmp_name'];
                $fileParts = pathinfo($_FILES['Filedata']['name']);
                $name = $fileParts['basename'];
                $attachInfo = array();
                $attachInfo['save_path'] = date('Y-m-d');
                $attachInfo['extension'] = $fileParts['extension'];
                $attachInfo['save_name'] = uniqid() . '.' . $fileParts['extension'];

                $dirName = UPLOAD_PATH . '/' . $attachInfo['save_path'];
                //	$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
                $targetFile = $dirName . '/' . $attachInfo['save_name'];
                @mkdir($dirName, 0777, true);
                $m = move_uploaded_file($file, $targetFile);

                $res_info->general->title = $fileParts['filename'] . '.' . $fileParts['extension'];

                Log::write($_login . "开始上传附件" . $fileParts['filename'] . '.' . $fileParts['extension'], Log::DEBUG);
//本地地址
//				$savaPath =realpath(UPLOAD_URL).'/'.$attachInfo['save_path'].'/'.$info["Filedata"]["savename"];
                $filePath = md5($_login . time() . rand(1, 1000)) . "/" . $_FILES['Filedata']["size"] . "/" . $fileParts['filename'] . '.' . $fileParts['extension'];
//				$convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array(  "format"=>"flv"))]);
                $filePath = $this->appkey . "/" . $fileParts['filename'] . '.' . $fileParts['extension'];
                try {
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath);
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath,false,$convertParams);

                    $params = array(
                        "filePath" => $filePath,
                        "callbackMethod" => "GET",
                        "callbackUrl" => $this->host,
                        "module" => C("zttl_module"),
                        "action" => "上传主题材料",
                        "userId" => $GLOBALS['ts']['user']['cyuid']
                        /*                        "callbackParams" => "k1=v1&k2=v2"*/
                    );
                    $before_info = $this->apis_client->uploadFileOnBefore($params);
                    $file_info = $this->apis_client->uploadFile($before_info->contextId, $targetFile);
                    $previewId = $file_info->contextId;

					//上传至附加网关
					if($_REQUEST['attach_type']=="feed_file") {
						/*$data['data']['preview_id'] ="";
						$flvname =substr($attachInfo['save_name'], 0,strripos($attachInfo['save_name'], '.')).'.flv';
						$filePath = md5(time().rand(1,1000)) . "/" .$_FILES['Filedata']["size"]."/". $flvname;
						//转换类型;系统默认
						$convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array("format"=>"flv"))]);*/
						//上传至附加网关

						$convertParams = array(
							"contextId" => $before_info->contextId,
							"convType" => 16,
							"convParams" => "format=flv"
							/* "convCallbackUrl" => $this->host,
                             "convCallbackMethod" => "GET",
                             "convCallbackProtocol" => "HTTP"*/
						);
						$result =  $this->apis_client->startConvert($convertParams);
						$previewId = $result->converts["0"]->convId;
						//$result = \AttachServer::getInstance()->upload($targetFile,$filePath,false,$convertParams);
						if(isset($result->contextId)){
							$data['data']['preview_id'] = $result->contextId;
						}
					}

                    $resId = isset($result) ? $result->contextId :$file_info->contextId;
                    $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在主题讨论上传了“".$fileParts['filename']."”的附件";
                    $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["zttl"]["code"],C("opType")["upload"]['code'],$resId,C("location")["attachServer"]["code"],"","",$fileParts['filename'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,$file_info->url,$conmment);
                    Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

					Log::write("result:" . json_encode($file_info));
					if (file_exists($targetFile)) {
						@unlink($targetFile);
					}
                    return ["result" =>$file_info,"name"=>$fileParts['filename']. $fileParts['extension'],"extension"=>$fileParts['extension']];
                    exit;
                } catch (Exception $e) {
                    Log::write($e->getMessage());
                }
            }catch (Exception $e){
                Log::write($e->getMessage());
            }
        }
        return null;
    }
	
	/**
	 * 附件服务器保存附件
	 */
	public function cloudsave(){
		set_time_limit(0);
		Log::write("执行上传方法", Log::DEBUG);
		if (!empty($_FILES)) {
			try{
				$_login = $GLOBALS['ts']['user']['login'];
				if(!$_login){
					exit('{"status":0,"data":"终止非法操作！"}');
				}
				$file = $_FILES['Filedata']['tmp_name'];
				$fileParts = pathinfo($_FILES['Filedata']['name']);
				$name = $fileParts['basename'];
				$attachInfo = array();
				$attachInfo['save_path'] = date('Y-m-d');
				$attachInfo['extension'] = $fileParts['extension'];
				$attachInfo['save_name'] = uniqid().'.'.$fileParts['extension'];
				
				$dirName = UPLOAD_PATH.'/'.$attachInfo['save_path'];
			//	$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
				$targetFile = $dirName.'/'.$attachInfo['save_name'];
				@mkdir($dirName,0777,true);
				move_uploaded_file($file, $targetFile);
				
				$res_info->general->title = $fileParts['filename'].'.'.$fileParts['extension'];
	
				Log::write($_login."开始上传附件".$fileParts['filename'].'.'.$fileParts['extension'], Log::DEBUG);
//本地地址
//				$savaPath =realpath(UPLOAD_URL).'/'.$attachInfo['save_path'].'/'.$info["Filedata"]["savename"];
				$filePath = md5($_login.time().rand(1,1000)) . "/" .$_FILES['Filedata']["size"]."/".$fileParts['filename'].'.'.$fileParts['extension'];
//				$convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array(  "format"=>"flv"))]);
                $filePath = $this->appkey."/".$fileParts['filename'].'.'.$fileParts['extension'];
				try{
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath);
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath,false,$convertParams);
					$cyUid = $GLOBALS['ts']['user']["cyuid"];
                    $params = array(
                        "filePath" => $filePath,
                        "callbackMethod" => "GET",
                        "callbackUrl" => $this->host,
                        "module" => C("wspk_module"),
                        "action" => "上传评课",
                        "userId" => $cyUid
/*                        "callbackParams" => "k1=v1&k2=v2"*/
                    );
                    $before_info = $this->apis_client->uploadFileOnBefore($params);
                    $file_info = $this->apis_client->uploadFile($before_info->contextId,$targetFile);
                    $previewId = $file_info->contextId;
					//上传至附加网关
					if($_REQUEST['attach_type']=="feed_file") {
						/*$data['data']['preview_id'] ="";
						$flvname =substr($attachInfo['save_name'], 0,strripos($attachInfo['save_name'], '.')).'.flv';
						$filePath = md5(time().rand(1,1000)) . "/" .$_FILES['Filedata']["size"]."/". $flvname;
						//转换类型;系统默认
						$convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array("format"=>"flv"))]);*/
						//上传至附加网关

                        $convertParams = array(
                            "contextId" => $before_info->contextId,
                            "convType" => 16,
                            "convParams" => "format=flv"
                           /* "convCallbackUrl" => $this->host,
                            "convCallbackMethod" => "GET",
                            "convCallbackProtocol" => "HTTP"*/
                        );
                        $result =  $this->apis_client->startConvert($convertParams);
						$contextId = $before_info->contextId;
                        $previewId = $result->converts["0"]->convId;
						//$result = \AttachServer::getInstance()->upload($targetFile,$filePath,false,$convertParams);
						if(isset($result->contextId)){
							$data['data']['preview_id'] = $result->contextId;
						}
					}

					Log::write("result:".json_encode($file_info));
				}catch (Exception $e){
					Log::write($e->getMessage());
				}
				if(file_exists($targetFile)){
					@unlink($targetFile);
				}
				Log::write("上传资源到附件网关的结果：".json_encode($file_info));
				if($file_info->status == 2){
					Log::write($_login."上传".$res_info->general->title."到附件网关成功，ID为：'".$file_info->contextId."'", Log::DEBUG);

                    $resId = isset($result) ? $result->contextId :$file_info->contextId;
                    $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在网上评课上传了“".$name."”的附件";
                    $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["wspk"]["code"],C("opType")["upload"]['code'],$resId,C("location")["attachServer"]["code"],"","",$name,$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,$file_info->url,$conmment);
                    Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

//					$data = array();
					//if($file_info->status == 2){
						$paramArray = explode("/",$file_info->url);
						$c = count($paramArray);
						$data['name'] = rawurldecode($paramArray[$c - 1]);
						$data['shortname'] = getShort($data['name'],6,'...');
						$data['size'] =  $paramArray[$c - 2];
						$data['downoLoadUrl'] = $file_info->url;
						$data['extension'] = substr(strrchr($data['name'], '.'), 1);
						$data['attach_id'] = $previewId;
						$data['context_id'] = $contextId;
						if($_REQUEST['preview'] == 1 && t($_REQUEST['upload_type']) === 'video' ){
							$data['src'] = $data['downoLoadUrl'];
						}else{
							$data['src'] = getExtImageUrl($data['extension']);
						}
					//}
					$return = array('status'=>1,'data'=>$data);
					
				}else{
					Log::write($_login."上传".$res_info->general->title."失败，".$file_info, Log::ERR);
					$return = array('status'=>0,'data'=>"上传服务器失败，请稍后重试！");
					//exit('{"status":0,"msg":"'.json_encode($result).'"}');
				}
			}catch(Exception $e){
				$return = array('status'=>0,'data'=>'上传服务器出现异常，请稍后重试！');
				//exit('{"status":0,"msg":"'.$e->getMessage().'"}');
			}
			exit(json_encode($return));
		}
		$return = array('status'=>0,'data'=>"上传文件不能为空！");
		exit(json_encode($return));
		Log::write("上传文件失败，未获取file");
	}

}




function getExtImageUrl($extension){
	$extImageUrl =  THEME_URL.'/_static/image/icon/word.gif';
	$extension ='.'.$extension;

	switch (strtolower($extension))
	{
		case ".jpg":
		case ".jpeg":
		case ".bmp":
		case ".png":
		case ".gif":
			$extImageUrl =  THEME_URL.'/_static/image/icon/img.gif';
			break;
		case ".asf":
		case ".avi":
		case ".rmvb":
		case ".mp4":
		case ".mpeg":
		case ".wmv":
		case ".3gp":
		case ".rm":
		case ".mpg":
		case ".mpeg4":
		case ".mov":
		case ".flv":
		case ".vob":
		case ".mkv":
			$extImageUrl =  THEME_URL.'/_static/image/icon/video.gif';
			break;
		case ".doc":
		case ".docx":
			$extImageUrl =  THEME_URL.'/_static/image/icon/word.gif';
			break;
		case ".xls":
		case ".xlsx":
			$extImageUrl =  THEME_URL.'/_static/image/icon/xls.gif';
			break;
		case ".txt":
			$extImageUrl =  THEME_URL.'/_static/image/icon/txt.gif';
			break;
		case ".pdf":
			$extImageUrl =  THEME_URL.'/_static/image/icon/pdf.gif';
			break;
		case ".rtf":
		case ".chm":
	/* 	case ".html":
		case ".htm":
		case ".mht": */
			$extImageUrl =  THEME_URL.'/_static/image/icon/rtf.gif';
			break;
		case ".swf":
			$extImageUrl =  THEME_URL.'/_static/image/icon/rtf.gif';
			break;
		case ".mp3":
		case ".wma":
		case ".wav":
		case ".ogg":
		case ".ape":
		case ".mid":
		case ".midi":
			$extImageUrl =  THEME_URL.'/_static/image/icon/video.gif';
			break;
		case ".zip":
		case ".rar":
		case ".iso":
		case ".gz":
		case ".7z":
			$extImageUrl =  THEME_URL.'/_static/image/icon/zip.gif';
			break;
		case ".ppt":
		case ".pptx":
		case ".pps":
		case ".ppsx":
			$extImageUrl =  THEME_URL.'/_static/image/icon/ppt.gif';
			break;
		default:
			$extImageUrl =  THEME_URL.'/_static/image/icon/mr.gif';
			break;
	}
	return $extImageUrl;
}


