<?php
/**
 * 云盘文件操作模型
 * yangli4@iflytek.com
 */
class YunpanFileModel	extends	Model {
	private $diskClient = null;

	function __construct() {
		parent::__construct();
		$this->diskClient = IflytekdiskClient::getInstance();
	}

	/**
	 * 文件服务：上传文件到云盘 //大文件会有问题，后期平台组优化，需跟踪
	 * @author yangli4
     * @param  string $uid       文件夹所属用户ID(与用户服务ID一致)
     * @param  string $pDirID    文件夹所属上级父目录ID, 根目录创建,则为0
     * @param  bytes $filepath   文件完整路径
     * @param  string $filename  文件名称(带后缀)
	 * 
	 */
	public function uploadFile($cyuid, $pDirID, $filepath, $filename){
        $result = array();
        //参数信息合法检查
        if( empty($cyuid) ||
            !isset($pDirID) ||
            empty($filepath) ||
            !isset($filename)
            ){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        } else {
        	$condition['method'] = 'pan.file.add';
        	$condition['uid'] = $cyuid;
        	$condition['dirId'] = $pDirID;
        	$condition['fileName'] = $filename;
        	$condition['filePath'] = $filepath;

            $condition['module'] = C("grkj_module");
            $condition['action'] = C("EDUSND_ACTION")["upload"];

        	$obj = Restful::sendPostRequest($condition);
        	if(is_string($obj)){
        		$result = array('statuscode'=>1, 'data'=>$obj);
        	}else{
        		$result = array('statuscode'=>-1, 'data'=>$obj);
        	}
        }
        return $result;
	}
	

  	/**
  	 * 重命名文件
  	 * @param  string $cyuid        文件夹所属用户ID(与用户服务ID一致)
  	 * @param  string $fileID     文件ID
  	 * @param  string $newname    新文件名称
  	 * @return {[type]}          [description]
  	 */
 	public function renameFile($cyuid, $fid, $newname){
		$result = array();
		//参数信息合法检查
		if( empty($cyuid) ||
			empty($fid) ||
			empty($newname) 
			){
			 $result['statuscode'] = 0;
			 $result['data'] = '参数信息错误';
		}else {
			$data = $this->diskClient->renameFile($cyuid, $fid, $newname);

			$result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else {
                $result['data'] = $data;
            }
		}
		return $result;
	}

    /**
     * 复制文件
     * @param  string $cyuid      文件夹所属用户ID(与用户服务ID一致)
     * @param  string $sFileID    待复制文件ID
     * @param  string $pDirID     复制到的目录ID
     * @param  bool   $overwrite  重名文件是否直接覆盖
     * @return {[type]}            [description]
     */
    public function copyFile($cyuid, $sFileID, $pDirID, $overwrite){
    	Log::write("copyFile");
    	$result = array();
		//参数信息合法检查
		if( empty($cyuid) ||
			empty($sFileID) ||
			!isset($pDirID)
			){
			 $result['statuscode'] = 0;
			 $result['data'] = '参数信息错误';
		}else {
			$data = $this->diskClient->copyFile($cyuid, $sFileID, $pDirID, $overwrite);
			$result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else {
                $result['data'] = $data;
                //上传资源数统计
                $prop = $this->diskClient->getFile($cyuid, $sFileID);
                Log::write("prop:".json_encode($prop));
                $fromType = $prop->obj->fileInfoVal->fromtype;              
                if($fromType==0 ){
                	D("UserData")->updateKey("upload_yunpan_count", 1);
                	Log::write($cyuid."复制文件来源于上传，上传资源数+1", Log::DEBUG);
                }
                //云盘已使用容量增加,收藏文件不占容量
                if ($fromType == 0) {
                    $fileSize = $prop->obj->fileInfoVal->length;
                    $user = model("CyUser")->getUserInfo($cyuid);
                    $res= D('Yunpan')->increaseUsed($user["login"] ,$fileSize);
                    Log::write($cyuid."云盘已使用容量+".$fileSize."B", Log::DEBUG);
                }
            }
		}
		return $result;
    }
	

    /**
     * 移动文件
     * @param  string $cyuid        文件夹所属用户ID(与用户服务ID一致)
     * @param  string $sFileID    待移动文件ID
     * @param  string $pDirID     移动到的目录ID
     * @param  bool   $overwrite  重名文件是否直接覆盖
     * @return {[type]}            [description]
     */
	public function moveFile($cyuid, $sFileID, $pDirID, $overwrite){
    	$result = array();
		//参数信息合法检查
		if( empty($cyuid) ||
			empty($sFileID) ||
			!isset($pDirID)
			){
			 $result['statuscode'] = 0;
			 $result['data'] = '参数信息错误';
		}else {
			$data = $this->diskClient->moveFile($cyuid, $sFileID, $pDirID, $overwrite);

			$result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else {
                $result['data'] = $data;
            }
		}
		return $result;
	}

    /**
     * 批量删除文件
     * @param  string $cyuid       文件夹所属用户ID(与用户服务ID一致)
     * @param  array  $fileIDs     待删除文件列表
     * @return {[type]}          [description]
     */
    public function deleteFiles($cyuid, $fids){
    	$result = array();

		//参数信息合法检查
		if (empty($cyuid) || empty($fids)) {
			 $result['statuscode'] = 0;
			 $result['data'] = '参数信息错误';
		} else {
			$data = $this->diskClient->deleteFiles($cyuid, $fids);
			$result['statuscode'] = $data->hasError ? 0 : 1;
            if ($data->hasError) {
                $result['data'] = $data->obj->errorInfo;
                Log::write($cyuid . "删除文件错误信息" . json_encode($result['data']), Log::DEBUG);
            } else {
                $result['data'] = $data;
            }
		}
		return $result;
    }
    
    /**
     * 获取文件树形结构路径信息
     * @param string $cyuid
     * @param string $fid
     * @return array('status'=>xx,'data'=>'xx')
     */
    public function getPathInfo($cyuid, $fid){
    	$data = $this->diskClient->getPathInfo($cyuid,$fid);
    	$result = array();
    	if(!$data->hasError){
    		$path = $data->obj->pathInfoVal;
    		$result['statuscode'] = 1;
    		$result['data'] = $path;
    	}else{
    		$result['statuscode'] = 0;
    		$result['data'] = $data->obj->errorInfo;
    	}
    	return $result;
    }
	
    /**
     * 获取文件属性元数据
     * @param string $uid
     * @param string $fid
     * @return object(grade,subject,book,publisher,course,unit,phase,volumn,type)
     */
    public function getFileProps($cyuid, $fid){
        $result = array();
        //参数信息合法检查
        if (empty($cyuid) || empty($fid)){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        } else {
            $data = $this->diskClient->getFileProps($cyuid, $fid);
            if ($data){
            	$result['statuscode'] = 1;
                $result['data'] = $data;
            } else {
            	$result['statuscode'] = 0;
                $result['data'] = '获取文件属性失败';
            }
        }

    	return  $result;
    }
    
    /**
     * 设置文件/文件夹隐藏
     * @param string $cyuid 文件夹所属用户ID(与用户服务ID一致)
     * @param string $fid 文件/文件夹ID
     * @param boolean $ishidden 文件/文件夹是否隐藏
     */
    public function setHidden($cyuid, $fid, $ishidden=true){
        $result = array();
        //参数信息合法检查
        if( empty($cyuid) ||
            empty($fid) ){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        }else{
            $data = $this->diskClient->setHidden($cyuid,$fid,$ishidden);
            $result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else {
                $result['data'] = $data;
            }
        }
        return $result;
    }
    
    /**
     * 设置文件/文件夹只读
     * @param string $cyuid 文件夹所属用户ID(与用户服务ID一致)
     * @param string $fid 文件/文件夹ID
     * @param boolean $isreadonly 文件/文件夹是否只读
     */
    public function setReadonly($cyuid, $fid, $isreadonly = true){
        $result = array();
        //参数信息合法检查
        if( empty($cyuid) ||
            empty($fid) ){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        }else{
            $data = $this->diskClient->setReadonly($cyuid,$fid,$isreadonly);
            $result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else{
                $result['data'] = $data;
            }
        }
        return $result;
    }

    /** 
     * 设置文件/文件夹公开属性
     * @param string $cyuid 文件夹所属用户ID(与用户服务ID一致)
     * @param string $fid 文件/文件夹ID
     * @param boolean $isopen 文件/文件夹是否公开
     */
    public function setOpen($cyuid, $fid, $isopen = true){
        $result = array();
        //参数信息合法检查
        if( empty($cyuid) ||
            empty($fid) ){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        }else{
            $data = $this->diskClient->setOpen($cyuid, $fid, $isopen);
            $result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            } else {
                $result['data'] = $data;
            }
        }
        return $result;
    }
    
    /**
     * 公开资源时发表动态
     * @param int $uid
     * @param string $fileName 文件名
     * @param string $downloadUrl 文件下载地址
     * @return 成功，返回微博动态，否则返回false
     */
    public function syncToFeed($uid, $filename, $downloadUrl,$type=0){
    	$data = array();
    	$data['content'] = '';
    	if(strlen($filename)>20){
    		$filename=getShort($filename,20,'...');
    	}
    	if($type==0){
    		$data['body'] = '我分享了资源【'.$filename.'】，点击下载：';
    	}else if($type==1){
  			//公开资源同步操作
    		$data['body'] = '我分享了【'.$filename.'】到资源中心，点击查看';
    	}else if($type==2){
    		//公开到个人主页
       		$data['body'] = '我分享了【'.$filename.'】到个人主页，点击查看';
    	} 	
    	$data['source_url'] = $downloadUrl;
    	return model('Feed')->put($uid, 'yunpan', 'post', $data);
    }
    
    /**
     * 公开到个人主页（教学设计、教学课件、教学视频、媒体素材）
     * @param int $uid 用户id
     * @param string $fId 云盘文件id
     * @param string $type 公开到主页栏目(教学设计:0100、教学视频：0200、媒体素材：0300、教学课件:0600)
     * return true/false  成功/失败
     */
    public function syncToHomePage($uid, $fId ,$type){
    	$props = $this->diskClient->getFileProps($uid, $fId);
    	$props["type"] = $type;
    	$resid = $this->diskClient->exportToGateway($uid, $fId, array("rrt"),$this->diskClient->array2Tprops($props));
    	
    	$gateresinfos = D("CyCore")->Resource->Res_GetResIndex($resid);
    	$res = $gateresinfos->data[0];
    	$reslocal = array();
    	$reslocal['rid'] =$res->general->id;
    	if(strrpos($res->general->title, '.')>0){
    		$reslocal['title'] =substr($res->general->title,0,strrpos($res->general->title, '.'));
    	} else {
    		$reslocal['title'] = $res->general->title;
    	}
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
    	//构建本地上传记录
    	$reslocal['product_id'] = "rrt";
    	
    	$cyuserdata = Model('CyUser')->getCyUserInfo($reslocal['creator']);;
    	$reslocal['province'] = $cyuserdata['locations']['province']['id'];//省
    	$reslocal['city'] = $cyuserdata['locations']['city']['id'];//市
    	$reslocal['county'] = $cyuserdata['locations']['district']['id'];//区县
    	//所在学校id
    	$schools = $cyuserdata['orglist']['school'];
    	$school = array_pop($schools);
    	$reslocal['school_id'] = $school['id'];
    	
    	//在资源表中添加记录
    	$result1 = D('Resource','reslib')->increase($reslocal);
    	
    	if($result1){
			//保存上传记录
			$res_opr = array();
			$res_opr['resource_id'] = $result1;
			$res_opr['operationtype'] = ResoperationType::UPLOAD;
			$res_opr['dateline'] = time();
			$res_opr['login_name'] = $reslocal['creator'];
			//上传资源同步到成果展示:教学设计、教学课件、媒体素材,教学视频
			$result2 = D('ResourceOperation','reslib')->saveOrUpdate($res_opr) > 0;
		}
		return $result1 && $result2;
    }

    /**
     * 获取文件的访问链接
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $fileID     文件ID
     * @return {[type]}          [description]
     */
    public function getFileUrl($cyuid, $fid){    	
        $result = array();
        //参数信息合法检查
        if( empty($cyuid) ||
            empty($fid) ){
             $result['statuscode'] = 0;
             $result['data'] = '参数信息错误';
        }else{
            $expires = 3600;
            $data = $this->diskClient->getFileUrl($cyuid, $fid, $expires);
            $result['statuscode'] = $data->hasError ? 0 : 1;
            if($data->hasError){
                $result['data'] = $data->obj->errorInfo;
            }else {
                $result['data'] = $data;
            }
        }
        return $result;
    }

    /**
     * 按照分类获取文件
     * @param  string  $cyuid       用户cyuid
     * @param  string  $category  分类方式: all, audio, video, image, document
     * @param  string  $fid     文件夹ID
     * @param  int     $page      页码, 默认1
     * @param  int     $limit     返回数据条数, 默认0(无限制)
     * @param  int     $ordertype 排序方式, 0: 资源ID, 1:更新时间, 2:名称, 3: 创建时间
     * @param  bool    $isdesc    排序方式, 默认顺排序
     * @return array           [description]
     */
    public function listFilesByCategory($cyuid, $fid, $category, $page=0, $limit=10, $ordertype=3, $isdesc=true){

        //参数信息合法检查
        if(!isset($cyuid) || !isset($category)){
            return array();
        }

        return $this->diskClient->listFilesByCategory($cyuid, $fid, $category, $page, $limit, $ordertype, $isdesc);
    }
    
    
    /**
     * 供班班通查询使用的列表
     * @param unknown_type $uid
     * @param unknown_type $dirId
     * @param unknown_type $type
     * @param unknown_type $page
     * @param unknown_type $limit
     * @param unknown_type $ordertype
     * @param unknown_type $isdesc
     */
    public function listFilesByType($uid, $dirId, $type, $page = 0, $limit = 10, $ordertype = 3, $isdesc = true){
    	return $this->diskClient->listFilesByType($uid, $dirId, $type, $page, $limit, $ordertype, $isdesc);
    }
    
    /**
     * 把云盘资源导入到资源网关
     * @param  string $uid             用户ID eg:1
     * @param  string $fid             资源ID eg:2876
     * @param  array  $props           资源属性 eg:array
     * ("grade"=>"01","subject"=>"02","publisher"=>"xxx","volumn"=>"xxx","book"=>"xxx",
     * "unit"=>"xxx","course"=>"xxx","type"=>"xxx",["keywords"]=>"xxx",["description"]=>"xxx")
     * @return {[type]}                [description]
     */
    public function exportToGateway($uid, $fid, $props,$position='02'){
    	if(empty($uid) || empty($fid) || empty($props)){
    		return null;
    	}
    	$result = $this->diskClient->exportToGateway($uid, $fid, $props);
    	if(!$result->hasError){//添加公开记录
    		$user = D("CyUser")->getUserInfo($uid);
    		$pro=json_decode($props); 
    		Log::write("导入后资源信息：".json_encode($result),Log::DEBUG);
	        $add = D("YunpanPublish","yunpan")->saveOrUpdate(array(
        		"fid"=>$fid,
	        	"rid"=>$result->obj->listVal[0],
        		"login_name"=>$user['login'],
        		"dateline"=>date('Y-m-d H:i:s'),
        		"type"=>$pro->properties->type[0],
        		"open_position"=>$position,
        		"res_title"=>$pro->general->title
	        ));	      
    	}
    	return $result;
    }
    
    /**
     * 云盘资源预览
     * @param string $uid   用户id
     * @param string $fid   文件fid
     */
    public function getPreview($uid, $fid){
    	if(empty($uid) || empty($fid)){
    		return null;
    	}
    	return $this->diskClient->getPreview($uid, $fid);
    }

    /**
     * 获取文件详细信息
     * @param  string $uid        文件夹所属用户ID(与用户服务ID一致)
     * @param  string $fileID     文件ID
     * @return {[type]}         [description]
     */
    public function getFile($uid, $fileID){
        if(empty($uid) || empty($fileID)){
            return null;
        }
        $result = array();
        $data = $this->diskClient->getFile($uid, $fileID);

        $result['statuscode'] = $data->hasError ? 0 : 1;
        if($data->hasError){
            $result['data'] = $data->obj->errorInfo;
        }
        else {
            $result['data'] = $data->obj->fileInfoVal;
        }

        return $result;
    }
    
    
	/**
	* 为文件添加业务属性
	* @param string $uid             文件夹所属用户ID(与用户服务ID一致)
	* @param string $fileID          文件ID
	* @param array $props            属性键值对
	*/
	public function addFileProps($uid, $fileID, $props){
		$result = $this->diskClient->addFileProps($uid, $fileID, $props);
		return $result;
	}

	/**
	* 获取缩略图
	* @param unknown_type $uid
	* @param unknown_type $fid
	* @param unknown_type $width
	* @param unknown_type $height
	*/
	public function getThumbnail($uid, $fid, $width = 120, $height = 90){
		$result = $this->diskClient->getThumbnail($uid, $fid, $width, $height);
		return $result;
	}

	/**
	* @param $uid cyuid
	* @param $fid 文件id
	* @param $properties array("description" => "描述",
	* 						 "aliasname" => "别名", "fromapp"=>"teachermachine");
	* @return mixed
	*/
	public function setFileProperties($uid, $fid, $properties){
		return $this->diskClient->setFileProperties($uid, $fid, $properties);
	}

	/**
	* @param $uid 目标用户 cyuid
	* @param $dirId 目标文件id('0',根目录 )
	* @param $suid 源文件用户id cyuid
	* @param $fids 源文件id;
	*
	*  从一个用户云盘中拷贝资源到另一个用户的某个文件夹中
	*/
	public function copyFromOther($uid,$dirId,$suid,$fids){
		$result = array();
		//参数信息合法检查
		if( empty($uid) ||
		empty($dirId) ||
		empty($suid) ||
		empty($fids)
		){
			$result['statuscode'] = 0;
			$result['data'] = '参数信息错误';
		}else{
			$condition['method'] = 'pan.file.copyfromother';
			$condition['uid'] = $uid;
			$condition['dirId'] = $dirId;
			$condition['suid']=$suid;
			$condition['sfid'] = $fids;
			$obj =Restful::sendGetRequest($condition);
			if(is_string($obj)){
				$result = array('statuscode'=>1, 'data'=>$obj);
			}else{
				$result = array('statuscode'=>-1, 'data'=>$obj->subErrors);
			}
		}
		return $result;
	}
  
}


?>
