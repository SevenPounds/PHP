<?php
/**
 * Created by PhpStorm.
 * User: cheng
 * Date: 14-4-12
 * Time: 下午1:39
 * Description: 云盘中的Ajax操作
 */

class AjaxAction extends BaseCloudAction{	

    /**
     * 获取网盘中的列表
     */
    public function getList(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $fid = empty($_REQUEST['fid'])?'0':$_REQUEST['fid'];
        $page = empty($_REQUEST['p'])?'0':$_REQUEST['p'];
        $limit = 10;

        $parents = D('YunpanFile')->getPathInfo($cyuid,$fid);

        if($parents['statuscode'] == 0){
            $parentArray = array();
        }else{
            $parentArray = array();
            foreach($parents['data']->parents as $obj){
                array_push($parentArray,$obj->fid);
            }
            array_push($parentArray,$fid);
        }
        switch(count($parentArray)){
            case 2:
                $parentDetail = D('Yunpan', 'yunpan')->getYunpanDirDetail($cyuid,$parentArray[1]);
                $bookFolderType = $parentDetail['data']->obj->dirInfoVal->foldertype;
                $bookFid = $parentDetail['data']->obj->dirInfoVal->fid;
                break;
            case 1:
                $parentDetail = D('Yunpan', 'yunpan')->getYunpanDirDetail($cyuid,$parentArray[0]);
                $bookFolderType = $parentDetail['data']->obj->dirInfoVal->foldertype;
                $bookFid = $parentDetail['data']->obj->dirInfoVal->fid;
                break;
            case 0:
                $bookFolderType = '1000';
                $bookFid = '';
                break;
            default:
                $parentDetail = D('Yunpan', 'yunpan')->getYunpanDirDetail($cyuid,$parentArray[2]);
                $bookFolderType = $parentDetail['data']->obj->dirInfoVal->foldertype;
                $bookFid = $parentDetail['data']->obj->dirInfoVal->fid;
                $parentId = $parentArray[1];
        }

        if(empty($fid)){
            $folderType = FolderTypeModel::NORMAL;
        }else{
            $dirDetail = D('Yunpan', 'yunpan')->getYunpanDirDetail($cyuid,$fid);

            $folderType = $dirDetail['data']->obj->dirInfoVal->foldertype;
        }

        if(FolderTypeModel::UNIT == $folderType){
            $limit = 6;
        }
        $type = trim($_REQUEST['type']);

        $keyword = trim(urldecode($_REQUEST['keyword']));
        if(!empty($keyword)){
            $folderType = '';
            $result = D('Yunpan')->search($cyuid, $keyword, $page, $limit);
            $count = $result->total;
            $result = $result->data;

            $parentfolders = array();
            foreach($result as $value){
                array_push($parentfolders,array('uid'=>$cyuid,'fid'=>$value->parentfolder));
            }

            $parentfolderresult = D('Yunpan')->getDirsByIds($parentfolders);

            foreach($parentfolderresult->data as $value){
                $value->shortName = getShort($value->name,5,'...');
                $value->name = htmlspecialchars($value->name,ENT_QUOTES);
            }

            $return['parentfolders'] = $parentfolderresult->data;

        }else if(empty($type) || $type == 'all'){

            if(FolderTypeModel::BEI_KE_BEN == $folderType){
                $result = D('Beikeben')->getBeikebens($cyuid, $fid, $page, $limit);
            }else if(FolderTypeModel::BOOK == $folderType){
                $result = D('Yunpan')->getYunpanFileAndDirs($cyuid, $fid, $page, $limit,3,false);
            }else{
                $result = D('Yunpan')->getYunpanFileAndDirs($cyuid, $fid, $page, $limit);
            }
            $countResult = D('Yunpan')->getYunpanFileAndDirsTotal($cyuid,$fid,false);
            $count = $countResult['data'];
            $return['parentfolders'] = '';
        }else if(!empty($type)){
            $folderType = FolderTypeModel::RESOUCECATEGORY;
            $result = D('YunpanFile')->listFilesByCategory($cyuid,$fid, $type, $page, $limit);
            $count = $result->total;
            $result = $result->data;

            $parentfolders = array();
            foreach($result as $value){
                array_push($parentfolders,array('uid'=>$cyuid,'fid'=>$value->parentfolder));
            }
            $parentfolderresult = D('Yunpan')->getDirsByIds($parentfolders);

            foreach($parentfolderresult->data as $value){
                $value->shortName = getShort($value->name,5,'...');
                $value->name = htmlspecialchars($value->name,ENT_QUOTES);
            }

            $return['parentfolders'] = $parentfolderresult->data;
        }

        foreach($result as $value){
            $value->shortName = getShort($value->name,10,'...');
            $value->name = htmlspecialchars($value->name,ENT_QUOTES);
            $temp = strval($value->createtime);
            $temp = substr($temp,0,(strlen($temp)-3));
            $value->createtime = date('Y/n/j H:i',$temp);
        }

        $p = new Page($count, $limit);
        $p->setConfig('prev', '上一页');
        $p->setConfig('next', '下一页');
        $page = $p->show();

        $return['list'] = $result;
        $return['page'] = $page;
        $return['totalPages'] = $p->totalPages;
        $return['folderType'] = $folderType;
        $return['fid'] = $fid;
        $return['bookFolderType'] = $bookFolderType;
        $return['bookFid'] =$bookFid;
        $return['parentId'] = $parentId;
        echo json_encode($return);
    }

    /**
     * 获取网盘中的文件夹列表
     */
    public function getDirs(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $fid = empty($_REQUEST['fid'])?'0':$_REQUEST['fid'];

        $result = D('Yunpan')->getYunpanDirs($cyuid, $fid, 0, 0);

        $result = $result->data;

        foreach($result as $value){
            $value->shortName = getShort($value->name,15,'...');
            $value->name = htmlspecialchars($value->name,ENT_QUOTES);
        }

        echo json_encode($result);
    }
    
    
    /**
     * 获取网盘容量,以及资源容量排名
     */
    public function getCapacity(){
    	//定义字节向G转换
   
    	$rate=1024*1024*1024;
        $temp = 1048576;
    	$longName=$this->user['login'];
    	$result=array();
    	$yunpan=D('Yunpan');
  
    	//获取云盘的容量   
    	$capacity=$yunpan->getCapacityInfoByLogin($longName);   
   
     	//如果取得值是空
     	if(empty($capacity)){
     		$capacity['usedCapacity']=0;
     		$capacity['totalCapacity']=C('YUNPAN_CAPACITY')*$rate;
     	}     	   
          //小于1MB，以KB作为单位
     	if($capacity['usedCapacity']<$temp){
     		$result['usedCapacity']=number_format($capacity['usedCapacity']/1024,1);
     		$result['usedCapacity'] .='KB';
     	  //小于1G，以MB作为单位
     	}else if($capacity['usedCapacity']< $rate){
     		$result['usedCapacity']=number_format($capacity['usedCapacity']/$temp,1);
     		$result['usedCapacity'] .='MB';
     	}else{
     		//将字节转换成G,并且存入result结果中,以G作为单位
     		$result['usedCapacity']=number_format($capacity['usedCapacity']/$rate,1);
     		$result['usedCapacity'] .='GB';
     	}       	
     	$result['totalCapacity']=number_format($capacity['totalCapacity']/$rate,1);  
     	//获取当前我的云盘资源的排名
     	$rank=$yunpan->getRank($this->uid);     
     	if($rank['rank']==0){
     	    $per='0';
     	}else{
     		$per=number_format(($rank['total']-$rank['rank'])/$rank['total'],3)*100;
     	}
     	$per.='%';
        $result['per']=$per;
        echo json_encode($result);
    }
      
    /**
     * 获取推荐资源包
     */
	public function getRecomBag(){
        $fid = $_REQUEST['fid'];
        //当前登录用户的cyuid
        $cyuid = $this->cymid;
        $parents = D('YunpanFile')->getPathInfo($cyuid,$fid);
        $bookDetail =D('Yunpan')->getDirProps($this->cyuid,$fid);
        Log::write("Book detail : ".json_encode($bookDetail),Log::DEBUG);
		$bookid = $parents['data']->parents[1]->fid;
		$bookcode = $bookDetail->book[0];
		$unitcode = $bookDetail->unit[0];
		$coursecode = $bookDetail->course[0];
		$qUnits = $this->getUnitsByCourse($coursecode);
		$res = new ResourceClient();
		$query = array('properties.book'=>$bookcode,'properties.type'=>'1801');
		$condition = array_merge($qUnits,$query);
		Log::write("condition : ".json_encode($condition),Log::DEBUG);
		if(C('ENABLE_SECURITY')==1){//是否启用安全监管监测的结果
			$conditions['lifecycle.securitystatus']='-(2 or 3 or 4)';
		}
		$bundles = $res->Res_GetResources($condition,array('general','properties', 'statistics'));
		$bag = array();
		if($bundles->statuscode == '200' && $bundles->total >= 1){
			$bag['bagInfo'] = $bundles->data[0];
			
			// 根据资源包id，获取资源包内资源列表
			$resList = $res->Get_Resources_In_Package($bundles->data[0]->general->id,array(),0,6);
			if($resList->statuscode == '200'){
				$total = $resList->total;
				if($total > 6){
					$bag['showPage'] = true;
				}
				$bag['bagRes'] = $resList->data;
			}
			$bag['viewUrl'] = C('RS_SITE_URL').'/index.php?app=changyan&mod=Rescenter&act=detail&id=';
			$bag['bookid'] = $bookid;
			$bag['fid'] = $fid;
		}else{
			exit('');
		}
 		echo json_encode($bag);
	}
	
	private function getUnitsByCourse($course){
		$units = explode('-',$course);
		$query = array();
		foreach($units as $key=>$val){
			$query['properties.unit'.($key+1)] = $val;
		}
		return $query;
	}
	
	/**
	 * 根据分页条件获取资源包当前页资源
	 */
	public function getBagResByPage(){
		$page = $_REQUEST['page'];
		$bagId = $_REQUEST['bagId'];
		$limit = 6;
		$skip = ($page - 1) * $limit;
		$res = new ResourceClient();
		$result = array();
		
		$resList = $res->Get_Resources_In_Package($bagId,array(),$skip,$limit);
		if($resList->statuscode == '200'){
			$result['bagRes'] = $resList->data;
		}else{
			$result['bagRes'] = array();
		}
		if($resList->total > $limit * $page){
			$result['showNext'] = true;
		}else{
			$result['showNext'] = false;
		}
		if($page > 1){
			$result['showPre'] = true;
		}else{
			$result['showPre'] = false;
		}
		$result['viewUrl'] = C('RS_SITE_URL').'/index.php?app=changyan&mod=Rescenter&act=detail&id=';
		echo json_encode($result);
	}

	/**
	 * @author yuliu2
	 * 收藏云盘收藏夹中的资源包
	 */
	public function collectResoucePackage(){
		$fid = $_REQUEST['fid'];
		$result = D("YunpanFavorite")->collectResourcePackage($this->cymid, $fid);
		echo json_encode($result);
	}
	
    /**
     * 收藏资源平台推荐资源包
     */
    public function collectBag(){
    	$fid = $_REQUEST['fid'];
    	$bagid = $_REQUEST['id'];
    	
    	$result = D("YunpanFavorite")->addToBook($this->cymid,$fid,$bagid);
    	echo json_encode($result);
    }
    
    /**
     * 云盘中文件夹的创建
     */
    public function createFolder(){

        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $pDirID = empty($_REQUEST['fid'])?'0':$_REQUEST['fid'];
        $dirname = trim($_REQUEST['name']);
        $result = D('Yunpan')->mkYunpanDir($cyuid, $pDirID, $dirname);

        if($result['statuscode'] == 0){
            $msg = $result['data']->msg;
            echo json_encode("{\"status\":\"500\",\"msg\":\"$msg!\"}");
        }else{
            echo json_encode("{\"status\":\"200\",\"msg\":\"创建成功!\"}");
        }
    }
	
    /**
     * 删除备课本
     * 
     */
	public function deleteBeikeben(){
		//当前登录用户的cyuid
		$cyuid = $this->cymid;
		
		$dirIDs = $_REQUEST['fid'];
		$dirIDs = explode(',',$dirIDs);
		$result=D('Beikeben')->deleteBeikeben($cyuid,$dirIDs,true);
		if($result==0){
			echo json_encode("{\"status\":\"500\",\"msg\":\"删除的备课本失败!\"}");
			return;
		}
		echo json_encode("{\"status\":\"200\",\"msg\":\"删除成功!\"}");
	}

	
    /**
     * 云盘中的文件夹删除
     */
    public function deleteFolder(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;
        $pDirIDs = $_REQUEST['fid'];
        $isdirs = $_REQUEST['isdir'];
        $parentfolder=$_REQUEST['parentfolder'];

      $pDirIDs = explode(',',$pDirIDs);
      $isdirs = explode(',',$isdirs);
      $parentfolder = explode(',', $parentfolder);
        $folderIds = array();
        $fileIds = array();

        for($i = 0;$i < count($pDirIDs);$i++){

            $fid = $pDirIDs[$i];
            $isdir = $isdirs[$i];
          /** start 修改资源收藏策略 add by xmsheng 2014/7/4**/
	        $parentId=$parentfolder[$i];	  
	        if(!empty($parentId)){
	        	$folder=D('Yunpan')->getYunpanDirDetail($cyuid,$parentId);
	        	$foldertype=$folder['data']->obj->dirInfoVal->foldertype;
	        	if($foldertype==FolderTypeModel::FAVORITE){
	        		D("YunpanFavorite")->deleteFavorite($this->user['login'],$fid);
	        	}
	        }
	      /** end 修改资源收藏策略 add by xmsheng 2014/7/4**/
            if($isdir == 'true'){
                array_push($folderIds,$fid);
                $result1 =  D('Yunpan')->getYunpanDirDetail($cyuid,$fid);
                if(empty($result1) || $result1->hasError){
                    echo json_encode("{\"status\":\"500\",\"msg\":\"删除的资源不存在!\"}");
                    return;
                }
            }else{
            	// 删除公开记录表中数据
            	Log::write("fid ".$fid." login:".$this->user['login'],Log::DEBUG);
            	D("YunpanPublish","yunpan")->deleteShare($fid,$this->user['login']);
                array_push($fileIds,$fid);
                $result2 =  D('YunpanFile')->getFileProps($cyuid,$fid);
                if(empty($result2) || $result2['statuscode'] == 0){
                    echo json_encode("{\"status\":\"500\",\"msg\":\"删除的资源不存在!\"}");
                    return;
                }
            }
        }
        $folderResultFlag = false;
        if(!empty($folderIds)){
            $folderResult = D('Yunpan')->deleteYunpanDirs($cyuid, $pDirIDs,true);

            if($folderResult->hasError){
                $folderResultFlag = true;
            }
        }

        $fileResultFlag = false;
        if(!empty($fileIds)){
            $fileResult =  D('YunpanFile')->deleteFiles($cyuid, $fileIds);

            if($fileResult['statuscode'] == 0){
                $fileResultFlag = true;
            }
        }

        if( $folderResultFlag || $fileResultFlag){
            echo json_encode("{\"status\":\"500\",\"msg\":\"删除失败!\"}");
        }else{
            echo json_encode("{\"status\":\"200\",\"msg\":\"删除成功!\"}");
        }
    }

    /**
     * 云盘中的文件夹重命名
     */
    public function renameFolder(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $pDirID = $_REQUEST['fid'];
        $name = trim($_REQUEST['name']);
        $isdir = trim($_REQUEST['isdir']);

        if($isdir == 'true'){
            $result = D('Yunpan')->renameYunpanDir($cyuid, $pDirID,$name);
        }else{
            $result = D('YunpanFile')->renameFile($cyuid, $pDirID, $name);
        }

        if($result['statuscode'] == 0){
            $msg = $result['data']->msg;
            echo json_encode("{\"status\":\"500\",\"msg\":\"$msg!\"}");
        }else{
            echo json_encode("{\"status\":\"200\",\"msg\":\"重命名成功!\"}");
        }
    }

    /**
     * 移动文件夹及文件
     */
    public function moveFolder(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $tDirID = trim($_REQUEST['tDirID']);
        $sDirID = trim($_REQUEST['sDirID']);
        $isdir = trim($_REQUEST['isdir']);
        $parentId= trim($_POST['parentfolder']);

        if($isdir == 'true'){
            $result = D('Yunpan')->moveYunpanDir($cyuid, $sDirID, $tDirID, true);
        }else{
            $result = D('YunpanFile')-> moveFile($cyuid, $sDirID, $tDirID, true);
        }

        if($result['statuscode'] == 0){
            echo json_encode("{\"status\":\"500\",\"msg\":\"移动失败!\"}");
        }else{
            // 从我的收藏中移动出去时删除收藏记录
            if(isset($parentId) && ($parentId != $tDirID)){
                $folder=D('Yunpan')->getYunpanDirDetail($cyuid,$parentId);
                $foldertype=$folder['data']->obj->dirInfoVal->foldertype;
                if($foldertype==FolderTypeModel::FAVORITE){
                    D("YunpanFavorite")->deleteFavorite($this->user['login'],$sDirID);
                }
            }
            echo json_encode("{\"status\":\"200\",\"msg\":\"移动成功!\"}");
        }
    }

    /**
     * 复制文件夹及文件
     */
    public function copyFolder(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        $tDirID = trim($_REQUEST['tDirID']);
        $sDirID = trim($_REQUEST['sDirID']);
        $isdir = trim($_REQUEST['isdir']);

        if($isdir == 'true'){
            $result = D('Yunpan')->copyYunpanDir($cyuid, $sDirID, $tDirID, true);
        }else{
            $result = D('YunpanFile')->copyFile($cyuid, $sDirID, $tDirID, true);
        }

        if($result['statuscode'] == 0){
            echo json_encode("{\"status\":\"500\",\"msg\":\"复制失败!\"}");
        }else{
            echo json_encode("{\"status\":\"200\",\"msg\":\"复制成功!\"}");
        }
    }

    /**
     * 显示资源上传
     */
    public function showUpload(){
    	// 获取云盘容量
    	$loginName = $this->user['login'];
		$result = D("Yunpan", "yunpan")->getCapacityInfoByLogin($loginName);
		$this->usedCapacity = $result['usedCapacity'];
		$this->totalCapacity = $result['totalCapacity'];
		
        $this->folder = $_REQUEST['folder'] ?  trim($_REQUEST['folder']) : '0' ;//一次性上传最大文件数
    	$this->total = C('YUNPAN_UPLOAD_NUM');//一次性上传最大文件数
    	$this->uploader = U('yunpan/Ajax/save');
    	$this->fileSizeLimit = C('RESLIB_FILE_SIZE_LIMIT');
    	$this->unid = substr(strtoupper(md5(uniqid(mt_rand(), true))), 0, 8);
    	$upload_config = include(CONF_PATH.'/upload.inc.php');
    	$exts = implode(';',$upload_config['previewable_exts']);
    	$this->fileTypeExts = $exts;
    	$this->display();
    }
    
    /**
     * 资源上传保存
     */
    public function save(){
    	set_time_limit(0);
        if(empty($_FILES))
            exit(json_encode(array('status'=>0,'info'=>'上传文件不空!')));
       	if(!isset($_REQUEST['folder'])){
            exit(json_encode(array('status'=>0,'info'=>'请选择上传目标文件夹！')));
        }
        
        Log::write("开始上传",Log::DEBUG);
        $filesize = $_FILES["Filedata"]["size"];
        $tmpFile = $_FILES['Filedata']['tmp_name'];
        $fileinfo = pathInfo($_FILES['Filedata']['name']);

		// 替换上传资源文件名中的&符号
        $fileinfo['filename'] = str_replace("&","",$fileinfo['filename']);
        $fileName = $fileinfo['filename'];

        // 处理Linux环境下后缀名大写问题
        if (isset($fileinfo['extension'])) {
            $fileinfo['extension'] = strtolower($fileinfo['extension']);
            $fileName = $fileinfo['filename'];
        }

        $upload_config = include(CONF_PATH.'/upload.inc.php');
        $file_extensions = $upload_config['previewable_exts'];
        if(!in_array("*.".$fileinfo['extension'],$file_extensions)){
            echo json_encode(array("status"=>"0","info"=>"上传格式不支持"));
            return;
        }

        $targetFolder = UPLOAD_PATH;
        if(!is_dir($targetFolder)){  //如果不存在该文件夹
            mkdir($targetFolder, 0777);  //创建文件夹
        }
        chmod($targetFolder, 0777);  //改变文件模式
        $targetFile = rtrim($targetFolder,'/') . '/' . md5($fileinfo['filename'].$this->user['login'].mt_rand()) . '.' . $fileinfo['extension'];

        $flag = move_uploaded_file($tmpFile, $targetFile);
        Log::write("移动文件的结果：".$flag,Log::DEBUG);
        if(file_exists($targetFile)){
			Log::write("目标文件存在",Log::DEBUG);
		}else{
			Log::write("目标文件不存在",Log::DEBUG);
		}
        
        try{
        	// 获取我的文档fid
        	$wdId = D("Yunpan")->getDocFid($this->cymid);
            $fName = $fileName;
            $ret = D('YunpanFile')->uploadFile($this->cymid,$wdId,$targetFile,$fName);


            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在个人空间上传了“".$fName."”资源";
            $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["grkj"]["code"],C("opType")["upload"]['code'],$ret["data"],C("location")["panServer"]["code"],"","",$fName,$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
            Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

            Log::write("上传到云盘返回的结果：".json_encode($ret),Log::DEBUG);
        }catch (Exception $e){
        	Log::write("上传到云盘异常信息：".json_encode($e),Log::DEBUG);
        }
        @unlink($targetFile);

        if($ret['statuscode']==1){
            $return['data'] = $ret['data'];
            $return['status'] = 1;
            $return['info'] = "上传成功！";
        }else{
            $return['status'] = 0;
            $return['info'] = "上传失败！";
        }
        Log::write("结束上传",Log::DEBUG);
        exit(json_encode($return));
    }

    /**
     * 获取书本
     */
    public function getBook(){
    	$pagenow=empty($_REQUEST['pagenow']) ? 0 : $_REQUEST['pagenow'];
    	$limt=empty($_REQUEST['limit']) ? 0 : $_REQUEST['limit']; 

    	try{
    		$books =  D('Beikeben','yunpan')->getBeikebens($GLOBALS['ts']['cyuserdata']['user']['cyuid'],$this->bkDirId,$pagenow,$limt );    
    		//查询下一页记录，判断下一iyeshifou有记录
    		$hasMoreBooks= D('Beikeben','yunpan')->getBeikebens($GLOBALS['ts']['cyuserdata']['user']['cyuid'],$this->bkDirId,($pagenow+1), $limt);   		
    		if(count($hasMoreBooks)>0){
    			$var['hasnext']=1;
    		}else{
    			$var['hasnext']=0;
    		}   	
    		$var['books'] = $books;     	
    	}catch(Exception $e){
    		$var['books'] = array();
    	}
    	echo json_encode($var);
    }

    /**
     * 获取文件路径
     */
    public function getPath(){
        $return = array();
        $data = array();
        $fid = $_REQUEST['fid'];
        if(!isset($fid)){
            $return['status']==0;
            $return['data'] = array();
            exit(json_encode($return));
        }

        $res=D('YunpanFile')->getPathInfo($this->cymid,$fid);
        if($res['statuscode']==1){
            $names = explode('/',$res['data']->path);
            $name = $names[count($names)-1];
            $return['status']=1;
            foreach($res['data']->parents as $obj){
                array_push($data,array('fid'=>$obj->fid,'name'=>$obj->name,'shortName'=>getShort($obj->name,5,'...')));
            }
            array_push($data,array('fid'=>$fid,'name'=>htmlspecialchars($name,ENT_QUOTES),'shortName'=>getShort($name,5,'...')));
            $return['data'] = $data;
        }else{
            $return['status']=1;
            $return['data'] = array();
        }
        unset($names);
        unset($name);
        exit(json_encode($return));
    }

    /**
     * 文件下载
     */
    public function download(){
        $return  =array();
        $fileId = $_REQUEST['fileId']? trim($_REQUEST['fileId']):'';
        if(empty($fileId)){
            $return['status']==0;
            $return['info'] = '文件不存在！';
            exit(json_encode($return));
        }

        $res = D('YunpanFile')->getFileUrl($this->cymid,$fileId);       
        if($res['statuscode']==1){
            $return['status']= 1;
            $return['data'] = $res['data']->obj->strVal;
        }else{
            $return['status']= 0;
            $return['info'] = $res['data']->msg;
        }
        exit(json_encode($return));
    }

    /**
     * 公开文件
     */
    public function openFile(){
        $fid= $_REQUEST['fid'];

        $fileName = trim($_REQUEST['name']);

        //当前登录用户的cyuid
        $cyuid = $this->cymid;

        // 当前登录的用户的uid
        $uid = $this->uid;

        $result = D('YunpanFile')->setOpen($cyuid, $fid);

        if($result['statuscode'] == 0){
            $msg = $result['data']->msg;
            echo json_encode("{\"status\":\"500\",\"msg\":\"$msg!\"}");
        }else{

            $res = D('YunpanFile')->getFileUrl($this->cymid,$fid);
            $downloadUrl = $res['data']->obj->strVal;

            D('YunpanFile')->syncToFeed($uid, $fileName, $downloadUrl);

            echo json_encode("{\"status\":\"200\",\"msg\":\"公开成功!\"}");
        }
    }

    /**
     * 云盘中的文件夹删除合法性的验证
     */
    public function checkDeleteLegal(){
        //当前登录用户的cyuid
        $cyuid = $this->cymid;
        $pDirIDs = $_REQUEST['fid'];
        $isdirs = $_REQUEST['isdir'];
        $pDirIDs = explode(',',$pDirIDs);
        $isdirs = explode(',',$isdirs);

        for($i = 0;$i < count($pDirIDs);$i++){

            $fid = $pDirIDs[$i];
            $isdir = $isdirs[$i];

            if($isdir == 'true'){
                // 判断文件夹是否为空
                $emptyResult = D('Yunpan')->checkYunpanDirIsEmpty($cyuid, $fid);
                if($emptyResult['statuscode'] == 1){
                    if(!$emptyResult['data']){
                        echo json_encode("{\"status\":\"200\",\"msg\":\"正在删除非空文件夹!\"}");
                        return;
                    }else{
                        echo json_encode("{\"status\":\"300\",\"msg\":\"可以删除!\"}");
                        return;
                    }
                }else{
                    echo json_encode("{\"status\":\"500\",\"msg\":\"网络连接出错!\"}");
                    return;
                }
            }
        }

        echo json_encode("{\"status\":\"300\",\"msg\":\"可以删除!\"}");
    }
    
    /**
     * 获取年级、学科、出版社等列表信息
     */
    public  function getTreeNodes(){
    	$node = $_REQUEST['node'];
    	$phase =$_REQUEST['phase'];
    	$subject = $_REQUEST['subject'];
    	$edition = $_REQUEST['edition'];
    	$stage = $_REQUEST['stage'];   	
    	$condition = array();
    	if (!empty($phase)) {
    		$condition = array_merge($condition, array('phase' => $phase));
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
    	$order = '';
    	if (!empty($order)) {
    		$condition = array_merge($condition, array('order' => $order));
    	}    	
    	$return  = array();     
    	$return['status'] = 1;
    	$return['data'] = getBookTreenodes($condition , $node);    	
    	$return['rstype']=C("RES_TYPE");  	
    	exit(json_encode($return));
    }
    
    /**
     * 获取书本下的单元等课程列表信息
     */
    public function getBookIndex(){ 
    	$id = $_REQUEST['id'];
    	$bookinfo = S('s_book_'.$id);
    	if(empty($bookinfo)){
    		$_treeclient = D('CyCore')->Tree;
    		$obj = $_treeclient->Tree_getBookindex($id);  		
    		$bookinfo = $obj->statuscode == 200 ? $obj->data : array();
    		S('s_book_'.$id,$bookinfo,3600);
    	}
    	$return  = array();
    	$return['status'] = 1;
    	$return['data'] = $bookinfo;
    	exit(json_encode($return));
    }
    
    /**
     * 通过bookid,unit获取课程
     */
    public function getCource(){
    	$bookid=$_REQUEST['id'];
        $node=$_REQUEST['node'];
        $unit2 = $_REQUEST['unit2'];
    	$unitCode=empty($_REQUEST['unit'])?'':$_REQUEST['unit'];
    	$obj=D('CyCore')->Tree->Tree_getBookindex($bookid);
    	$return=array();
    	if($obj->statuscode==200){
    		$units=$obj->data->general->resourcedescriptor->units;
            switch($node){
                case 'unit2' :
                    foreach($units as $unit){
                        if($unit->Code == $unitCode){
                            $return = $unit->Courses;
                            break;
                        }
                    }
                    break;
                case 'unit3' :
                    foreach($units as $unit){
                        if($unit->Code == $unitCode){
                            $units2 = $unit->Courses;
                            foreach($units2 as $value){
                                if($value->Code == $unit2){
                                    $return = $value->Courses;
                                }
                            }
                            break;
                        }
                    }
                    break;
            }
    	}
    	exit(json_encode($return));
    }
    
    /**
     * 把云盘资源导入到资源网关
     * @return resid  资源id
     */
    public function exportToGateway(){
    	$bookid=$_REQUEST['bookid'];    	
    	$fid = $_REQUEST['fid'];
    	$unit = $_REQUEST['unit'];
    	$unit2 = $_REQUEST['unit2'];
    	$unit3 = $_REQUEST['unit3'];

    	$type = $_REQUEST['type'];
    	$keywords = $_REQUEST['keywords'];
    	$description = $_REQUEST['description'];
    	$extension=$_REQUEST['extension'];
    	$uid = $this->cymid;
    	$filename=$_REQUEST['filename'];  	
        // 固定为学科资源
    	$result  = array();
        $word=$filename.$description.$keywords;
    	$filter_rs=json_decode(wordFilter($word),true);    
    	if($filter_rs['status']==1){
    		$result['status'] = 2;
    		$result['data']='文件名或关键字或资源描述中含有敏感词汇，请修改之后重新公开';
    		exit(json_encode($result));
    	}
    	if(empty($uid) || empty($fid)){
    		$result['status'] = 0;
    		$result['data'] = array();
    		exit(json_encode($result));
    	}
    	//获取书本的基本信息
    	$obj = D('CyCore')->Tree->Tree_getBookindex($bookid);	
    	$properties=$obj->data->properties;	 
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
        
        $props['general'] = array(
            'productid' => 'rrt',
            'creator' => $this->user['login'],
            'description'=>$description,
        	'extension'=>$extension,
        	'title'=>$filename
        );      
        $props['properties'] = array(
            'subject' => array($properties->subject[0]),
            'publisher' => array($properties->publisher[0]),
            'grade' => array($properties->grade[0]),
            'edition' => array($properties->edition[0]),
            'book' => array($properties->book[0]),
            'unit' => array($unit),
            'type' => array($type),
        	'course'=>array($unit2),
            'unit1' => array($unit),
        	'unit2'=>array($unit2),
        	'unit3'=>array($unit3),
        	'stage'=> array($properties->stage[0]),
        	'phase'=>array($properties->phase[0]),
            'rrtlevel1' => array('08'),
        );        
        $props['tags'] = array($keywords);
    	$yunPanFile=D('YunpanFile');      
    	
    	//文件导入资源网关
    	Log::write("exportToGateway called", Log::DEBUG);
    	Log::write("exportToGateway called, UID:" . $uid, Log::DEBUG);
    	Log::write("exportToGateway called, FID:" . $fid, Log::DEBUG);
    	Log::write("exportToGateway called, PROPS:" . json_encode($props), Log::DEBUG);   	
    	$res = $yunPanFile->exportToGateway($uid, $fid, json_encode($props));
    	Log::write("exportToGateway called, RESULT:" . json_encode($res), Log::DEBUG);
    	if(!$res->hasError){
    		// 新增自动审核
    		if(C('RES_AUDIT_MODE') == 3){
    			$resReturn = D('CyCore')->Resource->Res_GetResIndex($res->obj->listVal[0]);
    			$fileUrl = $resReturn->data[0]->file_url;
    			Log::write("自动审核链接：".$fileUrl,Log::DEBUG);
    			$fileUrl = C('TEXT_FILTER_SERVER').'?id='.rawurlencode($res->obj->listVal[0]).'&url='.rawurlencode($fileUrl);
    			Log::write("请求地址：".$fileUrl,Log::DEBUG);
    			$setAuto = file_get_contents($fileUrl);
    			Log::write("添加至自动审核结果：".json_encode($setAuto),Log::DEBUG);
    		}
    		
    		$result['status'] = 1;
    		$result['data'] = $res->obj->listVal;    	
    		//文件公开
    		$op_rs = $yunPanFile->setOpen($uid, $fid);  
    		
    		if($op_rs['statuscode']!=0){   			
    			//同步动态到个人中心
    			$resUrl = C("RS_SITE_URL").'/index.php?app=changyan&mod=Rescenter&act=detail&id='.$res->obj->listVal[0];
 
    			if($auditMode == 1){
	    			$shar_res = $yunPanFile-> syncToFeed($this->uid, $filename, $resUrl,1);
    			}
                $totalCount = C('UPLOAD_SCORE_LIMIT');
                $currentCount = D('UploadRecord')->getCuttentCounts($this->user['login']);

                if($currentCount !== false && $currentCount < $totalCount){

                    /*********** 增加积分 begin by xypan 2014/5/28 *****************/
                    $result['creditResult'] = D('Credit')->setUserCredit($this->mid,'upload_resource');
                    $data = array();
                    $data['content'] = '上传并公开了资源【'.$filename.'】';
                    $data['url'] = $resUrl;              
                    $data['rule'] = array(
                        'alias' => $result['creditResult']['alias'],
                        'score' => $result['creditResult']['score']
                    );
                    
                    M('CreditRecord')->addCreditRecord($this->mid, $this->user['login'], 'upload_resource', $data);
                    /*********** 增加积分 end by xypan 2014/5/28 *****************/
                }
             $map['login'] = $this->user['login'];
                $map['count'] = $currentCount + 1;
                D('UploadRecord')->updateRecord($map);
    		}else{
    			$result['status'] = 3;
    			exit(json_encode($result));
    		}	
    	}else{
    		$result['status'] = 4;
    		exit(json_encode($result));
    	}	
    	exit(json_encode($result));
    }
    
    //获取课本元信息
    public function getBookDetial(){
        $fid=$_REQUEST['fid'];
        $result=D('Yunpan')->getDirProps($this->cyuid,$fid);
        $course = $result->course;
        $units = explode('-',$course[0]);
        $result->unit = $units[0];
        $result->unit2 = empty($units[1])?'':$units[1];
        $result->unit3 = empty($units[2])?'':$units[2];
        exit(json_encode($result));
    }

    /**
     * 判断容量满不满足需要
     */
    public function judgeCapacity(){
        // 当前登录名
        $loginName = $this->user['login'];
        //当前登录用户的cyuid
        $cyuid = $this->cymid;
        $fid  = trim($_REQUEST['fid']);
        $idDir = trim($_REQUEST['isdir']);
        $capacityInfo = D("Yunpan")->getCapacityInfoByLogin($loginName);
        $totalCapacity = $capacityInfo['totalCapacity'];
        $usedCapacity =  $capacityInfo['usedCapacity'];

        if($idDir){
            $result = D("Yunpan")->getDirSize($cyuid,$fid);
        }else{
            $result =  D("YunpanFile")->getFile($cyuid,$fid);
            $result['data'] = $result['data']->length;
        }

        if($result['statuscode'] == 1){
            if($usedCapacity + floatval($result['data']) > $totalCapacity){
                echo json_encode("{\"status\":\"500\",\"msg\":\"云盘容量不足!\"}");
            }else{
                echo json_encode("{\"status\":\"200\",\"msg\":\"云盘还有充足容量!\"}");
            }
        }else{
            echo json_encode("{\"status\":\"500\",\"msg\":\"网络连接不稳定,请重试!\"}");
        }
    }
    
    /**
     * 获取我的公开记录
     */
    public function getPublic(){
    	$pageindex=empty($_REQUEST['p'])?1:$_REQUEST['p'];
    	//'01'个人主页  '02'学科资源
    	$open_position=empty($_REQUEST['open_position'])?'01':$_REQUEST['open_position'];
    	$login=$this->user['login'];
    	$limit=10;
    	$count=D('YunpanPublish')->getlistPublishCount($login, $open_position);   	   	
    	$notes=D('YunpanPublish')->listPublish($login, $open_position, $pageindex ,$limit);  		
    	$resType=C('RES_TYPE');
    	$publics=array();
    	//通过资源类型code获取资源名
    	foreach($notes as $note){
    		$note['resTypeName']='其它';
    		 foreach ($resType as $res){   		
    		 	if($res['code']==$note['type']){
					$note['resTypeName']=$res['name'];	
    		 	}	 		 	
    		 }
    		 array_push($publics,$note);
    	}   
    	$p = new Page($count, $limit);   	
    	$page = $p->show();
    	$result['page']=$page;
    	$result['notes']=$publics;
    	echo json_encode($result);
    }  

    /**
     * 获取我的下载记录
     */
    public function getDownload(){
     $pageIndex=empty($_REQUEST['p'])?1:$_REQUEST['p'];
     $login=$this->user['login'];
     $limit=10;
     $count=D('YunpanDownload')->getlistDownloadCount($login,array('01','02'));
     $notes=D('YunpanDownload')->listDownload($login, array('01','02'), $pageIndex, $limit);
     $downloadNotes=array();
     $user=D('User');
     foreach($notes as $note){
     	$star=get_resStart($note['score']);
     	$note['star']=$star;
     	$createor=$user->getUserInfoByLogin($note['creator']);
     	if(!empty($createor['uid'])){
     		$note['prerson_sns_url']=SITE_URL."/index.php?app=public&mod=Profile&act=index&uid=".$createor['uid'];
     	}
    	array_push($downloadNotes,$note);
     }
     $res['notes']=$downloadNotes;
	 $page=new Page($count,$limit);
     $p=$page->show();
     $res['page']=$p;
     echo json_encode($res); 
    }
    
    /**
     * 根据fid删除用户上传至云盘的资源
     */
    public function deleteByFids(){
    	$fids = $_REQUEST['fids'];
    	$result = D('YunpanFile')->deleteFiles($this->cymid,$fids);
    	echo json_encode($result);
    }
}