<?php
/**
 * 用户云盘信息Model
 * yuliu2@iflytek.com
 */

class YunpanModel extends Model{
	
	protected $tableName = 'yunpan';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login_name',
			2 => 'used_capacity',
			3 => 'total_capacity',
			4 => 'ctime'
	);
	
	private $diskClient = null;

	function __construct() {
		parent::__construct();
		$this->diskClient = IflytekdiskClient::getInstance();
	}
	
	/**
	 * 用户是否已初始化默认文件夹
	 * @param string $loginName 登录名
	 * @return bool true/false(已初始化/未初始化)
	 * @deprecated
	 */
	public function isInit($loginName){
		if(empty($loginName)){
			return false;
		}
		$result = $this->where(array('login_name'=>$loginName))->find();
		return !empty($result);
	}

	/**
	 * 根据login_name获取用户的容量信息
	 * 容量单位:byte
	 * @param string $loginName 用户登录账号
	 * @return array() array('usedCapacity'=>xx,'totalCapacity'=>xx) 用户的容量信息
	 */
	public function getCapacityInfoByLogin($loginName){
		if(empty($loginName)){
			return array();
		}
		$result = array();
	
		$res = $this->where(array('login_name'=>$loginName))->field('used_capacity,total_capacity')->find();
	
		// 判断当前用户在数据库中是否有容量信息的记录
		if(!empty($res)){
			$result['usedCapacity'] = $res['used_capacity'];
			$result['totalCapacity'] = $res['total_capacity'];
		}
		return $result;
	}
	
	/**
	 * 增加已使用容量
	 * @param string $loginName 用户登录账号
	 * @param int $size 上传的文件大小,单位byte
	 * @return array('status'=>xx,'message'=>'xx'),成功:status=true,失败:status=false
	 */
	public function increaseUsed($loginName, $size){
		if(empty($loginName) || intval($size) < 1){
			return array('status'=>false,'message'=>'参数错误');
		}
		$capacity = $this->where(array('login_name'=>$loginName))->field('used_capacity,total_capacity')->find();
	
		//容量判断,用BCMath处理big integer
		$new_capacity = bcadd($capacity['used_capacity'], $size);
		if(bccomp($capacity['total_capacity'],$new_capacity) >= 0){
			if($this->setField('used_capacity', $new_capacity,array('login_name'=>$loginName))){
				return array('status'=>true,'message'=>'已使用容量增加成功');
			}else{
				return array('status'=>false,'message'=>'数据库操作未生效');
			}
		}else{
			return array('status'=>false,'message'=>'容量不足');
		}
	}
	
	/**
	 * 减少已使用容量
	 * @param string $loginName 用户登录账号
	 * @param int $size 删除的文件大小,单位byte
	 * @return array('status'=>xx,'message'=>'xx'),成功:status=true,失败:status=false
	 */
	public function decreaseUsed($loginName, $size){
		if(empty($loginName) || intval($size) < 1){
			return array('status'=>false,'message'=>'参数错误');
		}
		$capacity = $this->where(array('login_name'=>$loginName))->field('used_capacity,total_capacity')->find();
	
		//容量判断,用BCMath处理big integer
		$new_capacity = bcsub($capacity['used_capacity'], $size);
		//防止已使用容量 < 0
		$new_capacity = bccomp($new_capacity,0) >= 0 ? $new_capacity : 0;
	
		$result = $this->setField('used_capacity',$new_capacity,array('login_name'=>$loginName));
	
		if($result){
			return array('status'=>true,'message'=>'已使用容量减少成功');
		}else{
			return array('status'=>false,'message'=>'数据库操作未生效');
		}
	}
	
	/**
	 * 获取用户上传资源数排名信息
	 * @param int $uid 用户uid
	 * @return array() array('rank'=>xx,'total'=>xx) 返回排名信息
	 */
	public function getRank($uid){
		if(intval($uid) < 1){
			return array();
		} //上传排名
		$sql = "SELECT rank FROM (".
				"	SELECT @rownum:=@rownum+1 AS rank,uid FROM ".C('DB_PREFIX')."user_data ,(SELECT @rownum:=0) rn WHERE `key` = 'upload_yunpan_count' AND `value` > 0 ORDER BY CONVERT(`value`, SIGNED) DESC".
				") AS tmp WHERE uid = ".$uid;
		$result = $this->db->query($sql);
		if(!empty($result)){
			$rank = $result[0]['rank'];
		}else{
			$rank = 0;
		}

		//用户数
		$sql = "SELECT count(uid) AS total FROM ".C('DB_PREFIX')."user WHERE is_del = 0";
		$result = $this->db->query($sql);
		$total = $result[0]['total'];

		return array('rank'=>$rank,'total'=>$total);
	}
	

	/**
	 * 云盘初始化，为首次使用云盘用户初始化云盘
	 * @param string $cyuid 用户cyuid(与用户服务ID一致)
	 * @param string $loginname 用户登录名
	 * @return array('statuscode'=>0|1,'data'=>xx)
	 * @deprecated
	 */
	public function init($cyuid,$loginname){
		$result = array();
		if (!$this->isInit($loginname)) {//用户没有初始化文件夹
			//APP_PATH 在RPC调用会出错
			$docArr = include (dirname(dirname(dirname(__FILE__))).'/Conf/config.php');
			$capacity = C('YUNPAN_CAPACITY');
			$capacity = intval($capacity) * 1024 * 1024;//byte->K->M
			$capacity = bcmul($capacity,1024);//M->G
			//SQL 事务BEGIN
			$this->startTrans();
			Log::write($loginname.'...init yunpan...');
			$data = array('login_name'=>$loginname,'used_capacity'=>0,'total_capacity'=>$capacity,'ctime'=>date('Y-m-d H:i:m'));
			$this->add($data);//初始化云盘信息
			
			$pDocArr = $docArr["DOC_LIST"];
			Log::write($loginname.' pDocArr size is '.count($pDocArr));
			//初始化文件夹
			foreach ($pDocArr as $pDoc){
                if($pDoc["name"] == '我的备课本'){
                    $data = $this->diskClient->mkdirAndSetProps($cyuid, '0', $pDoc["name"], FolderTypeModel::BEI_KE_BEN, array(), true);
                }else if($pDoc["name"] == '我的收藏'){
                    $data = $this->diskClient->mkdirAndSetProps($cyuid, '0', $pDoc["name"], FolderTypeModel::FAVORITE, array(), true);
                }

				if ($data->hasError) {
					$this->rollback();
					$this->deleteYunpanDir($cyuid, '0', true);
					$result['statuscode'] = 0;
					Log::write($loginname.' rollback msg is '.$data->obj->errorInfo->msg);
					$result['data'] = $data->obj->errorInfo->msg;
					return $result;
				}else{
                    // 默认文件夹设置成置顶的
                    $fid = $data->obj->dirInfoVal->fid;
                    $this->diskClient->setOnTop($cyuid, $fid, true);
                }
			}
			Log::write($loginname.' commit');
			$result['statuscode'] = 1;
			$result['data'] = '1云盘初始化成功';
			
			$this->commit();
			//SQL 事务END
		}else{
			$result['statuscode'] = 1;
			$result['data'] = '2云盘不需要初始化';
		}
		return $result;
	}

	/**
	 * 获取目录下文件（含文件夹）列表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  int $orderType     排序方式
     * @param  bool $isOrder      正序还是倒序
     * @return array      [description]
     */
  	public function getYunpanFileAndDirs($cyuid, $pDirID, $page = 0, $limit = 10,$orderType = 3, $isOrder = true){
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return array();
		}
		$list = $this->diskClient->listFilesAndDirs($cyuid, $pDirID, $page, $limit, $orderType, $isOrder);//按创建时间降序排

		return $list;
  	}

	/**
	 * 获取目录下文件夹列表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  string $ordertype  记录排序方式
     * @return {[type]}         [description]
     */
  	public function getYunpanDirs($cyuid, $pDirID, $page = 0, $limit = 10, $ordertype = 3){
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return null;
		}
		$res = $this->diskClient->getDirs($cyuid, $pDirID, $page, $limit, $ordertype, false);
		return $res;
  	}

	/**
	 * 获取目录下文件列表信息
     * @param  string $cyuid      文件夹所属用户cyuid(与用户服务ID一致)
     * @param  string $pDirID     父目录ID, 只获取此目录下的一级子目录列表
     * @param  int $page          第几页
     * @param  int $limit         每页显示记录数
     * @param  string $ordertype  记录排序方式
     * @return {[type]}         [description]
     */
  	public function getYunpanFiles($cyuid, $pDirID, $page = 0, $limit = 10, $ordertype = 3){
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return null;
		}
		$res = $this->diskClient->getFiles($cyuid, $pDirID, $page, $limit, $ordertype, false);
		return $res;
  	}
  	
	/**
	 * 删除目录
	 * @param  string $cyuid      文件夹所属cyuid
	 * @param  string $dirID      待删除的目录ID
	 * @param  bool   $isDirectly 是否直接删除(默认删除到回收站)
	 * @return {[type]}             [description]
	 */
	public function deleteYunpanDir($cyuid, $dirID, $isDirectly){
		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($dirID)|| !isset($dirID)){
			return null;
		}
		//TODO 获取文件夹size
		$dirSize = 0;
		$dirsize_result = $this->getDirSize($cyuid, $dirID);
		if($dirsize_result['statuscode'] == 1){
			$dirSize = $dirsize_result['data'];
		}
		else{
			return $dirsize_result;
		}
		$count = $this->getDirResourceCount($cyuid, $dirID);
		$data = $this->diskClient->deleteDir($cyuid, $dirID, $isDirectly);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
            //上传资源数统计
            D("UserData")->updateKey("upload_yunpan_count", -$count);
            Log::write($cyuid."删除单个文件夹，删除资源数-".$count, Log::DEBUG);
            //修改云盘已使用容量
            $user = model("CyUser")->getUserInfo($cyuid);
            $res= D('Yunpan')->decreaseUsed($user["login"] , $dirSize);
            Log::write($cyuid."云盘已使用容量-".$dirSize."B", Log::DEBUG);
        }
		return $result;
	}
	
	/**
	 * 批量删除目录
	 * @author kangzhang2
	 * @param  string $cyuiduid   文件夹所属cyuid
	 * @param  array  $dirIDs     待删除的目录ID数组
	 * @param  bool   $isDirectly 是否直接删除(默认删除到回收站)
	 * @return {[type]}             [description]
	 */
	public function deleteYunpanDirs($cyuid, $dirIDs, $isDirectly){
		Log::write("deleteDir");
		$res = null;
		//参数信息合法检查
		if(!isset($cyuid) || !is_array($dirIDs)|| !isset($isDirectly)){
			return $res;
		}
		//TODO 获取文件夹size
		$dirSize = 0;
		foreach($dirIDs as $k=>$dirID){
			$dirsize_result = $this->getDirSize($cyuid, $dirID);
			if($dirsize_result['statuscode'] == 1){
				$dirSize += $dirsize_result['data'];
			}
			else{
				return $dirsize_result;
			}
		}
		$count = $this->getDirsResourceCount($cyuid, $dirIDs);
		$data = $this->diskClient->deleteDirs($cyuid, $dirIDs, $isDirectly);
		Log::write("dirIDs:".json_encode($dirIDs));
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
            //上传资源数统计
            D("UserData")->updateKey("upload_yunpan_count", -$count);
            Log::write($cyuid."批量删除文件夹，删除资源数".$count, Log::DEBUG);
            //修改云盘已使用容量
            $user = model("CyUser")->getUserInfo($cyuid);
            $res= D('Yunpan')->decreaseUsed($user["login"] , $dirSize);
            Log::write($cyuid."删除文件夹，云盘已使用容量-".$dirSize."B", Log::DEBUG);
        }
		return $result;
	}

    /**
     * 创建云盘文件夹
     * @param  {[type]} $cyuid     文件夹所属用户cyuid(与用户服务ID一致)
     * @param  {[type]} $pDirID  文件夹所属上级父目录ID, 根目录创建,则为0
     * @param  {[type]} $dirname 文件夹名称
     * @return {[type]}          [description]
     */
  	public function mkYunpanDir($cyuid, $pDirID, $dirname,$type = FolderTypeModel::NORMAL){
  		//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)|| !isset($dirname)){
			return null;
		}
 		$data = $this->diskClient->mkdir($cyuid, $pDirID, $dirname,$type);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }
 		return $result;
  	}
  	
  	/**
  	 * 批量创建文件夹
  	 * @author kangzhang2
  	 * @param  string $uid      文件夹所属用户ID(与用户服务ID一致)
  	 * @param  string $pDirID   文件夹所属上级父目录ID, 根目录创建,则为0
  	 * @param  array  $dirsname 文件夹名称数组
  	 * @return {[type]}           [description]
  	 */
  	public function mkYunpanDirs($cyuid, $pDirID, $dirsname,$type = FolderTypeModel::NORMAL){
  		$res = null;
  		//参数信息合法检查
  		if(!isset($cyuid) || !isset($pDirID)|| !isset($dirsname)){
  			return $res;
  		}
  		$data = $this->diskClient->mkdirs($cyuid, $pDirID, $dirsname,$type);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }
  		return $result;
  	}

  	/**
  	 * 重命名文件夹
  	 * @author kangzhang2
  	 * @param string $cyuid
  	 * @param int $dirID
  	 * @param string $newname
  	 * @return NULL|{[type]}
  	 */
  	public function renameYunpanDir($cyuid, $dirID, $newname){
  		//参数信息合法检查
  		if(!isset($cyuid) || !isset($dirID)|| empty($newname)){
  			return null;
  		}
  		$data =  $this->diskClient->renameDir($cyuid, $dirID, $newname);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }
        return $result;
  	}


    /**
     * 获取单个文件夹信息
     * @param  string $cyuid      文件夹所属用户ID(与用户服务ID一致)
     * @param  stirng $dirID      目录ID
     * @return {[type]}        	  [description]
     */
    public function getYunpanDirDetail($cyuid, $dirID){
    	//参数信息合法检查  		
		if(!isset($cyuid) || !isset($dirID)){
			return null;
		}

		$data = $this->diskClient->getDir($cyuid, $dirID);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }

        return $result;
	}

    /**
     * 移动目录
     * @param  string $uid        文件夹所属用户ID(与用户服务ID一致)
     * @param  string $sDirID     待移动目录ID
     * @param  string $tDirID     移动到所属目录ID
     * @param  bool   $overwrite  如果重名, 是否直接合并文件夹
     * @return {[type]}            [description]
     */
    public function moveYunpanDir($cyuid, $sDirID, $tDirID, $overwrite){
    	//参数信息合法检查  		
		if(empty($cyuid) || empty($sDirID) || !isset($tDirID)){
			return null;
		}

		$data = $this->diskClient->moveDir($cyuid, $sDirID, $tDirID, $overwrite);
        if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }

        return $result;
  	}

  	/**
     * 复制目录
     * @param  string $uid        文件夹所属用户ID(与用户服务ID一致)
     * @param  string $sDirID     待复制目录ID
     * @param  string $tDirID     复制到所属目录ID
     * @param  bool   $overwrite  如果重名, 是否直接合并文件夹
     * @return {[type]}            [description]
     */
    public function copyYunpanDir($cyuid, $sDirID, $tDirID, $overwrite){
    	//参数信息合法检查  		
		if(!isset($cyuid) || !isset($sDirID) || !isset($tDirID)){
			return null;
		}
		//TODO
    	$dirSize = 0;
		$dirsize_result = $this->getDirSize($cyuid, $sDirID);

		if($dirsize_result['statuscode'] == 1){
			$dirSize = $dirsize_result['data'];
		}
		else{
			return $dirsize_result;
		}
		$data = $this->diskClient->copyDir($cyuid, $sDirID, $tDirID, $overwrite);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
            //复制文件夹，上传资源数统计
            $count = $this->getDirResourceCount($cyuid, $sDirID);
            D("UserData")->updateKey("upload_yunpan_count", $count);
            Log::write($cyuid."复制文件夹，统计资源数+".$count, Log::DEBUG);
            //修改云盘已使用容量
            $user = model("CyUser")->getUserInfo($cyuid);
            $res= D('Yunpan')->increaseUsed($user["login"] , $dirSize);
            Log::write($cyuid."复制文件夹，云盘已使用容量+".$dirSize."B", Log::DEBUG);
        }
        return $result;
    }
    /**
     * 递归统计多个文件夹下上传和收藏的资源数目
     * @param unknown $cyuId
     * @param unknown $dirIds
     * @return number
     */
    private function getDirsResourceCount($cyuId, $dirIds){
    	if(is_array($dirIds)){
    		$count = 0;
    		foreach ($dirIds as $key=>$dirId){
    			$count += $this->getDirResourceCount($cyuId, $dirId);
    		}
    		return $count;
    	} else{
    		return $this->getDirResourceCount($cyuId, $dirId);
    	}
    }
    
    /**
     * 递归统计文件夹下上传和收藏的资源数目
     * @param string $cyuId
     * @param string $dirId
     * @return number 文件夹下上传和收藏的资源数目
     */
    private function getDirResourceCount($cyuId, $dirId){
    	$count = 0;
    	//TODO 我不要分页!!!
    	$arr = $this->diskClient->listFilesAndDirs($cyuId, $dirId, 0, 9999);
    	foreach ($arr as $key=>$value){
    		if($value->isdir==true){
    			$count += $this->getDirResourceCount($cyuId, $value->fid);
    		} else {
    			$prop = $this->diskClient->getFile($cyuId, $value->fid);
                $fromType = $prop->obj->fileInfoVal->fromtype;
                //是上传类型或收藏类型 
                if( $fromType==0 || $fromType==1){
                	$count++;
                }
    		}
    	}
    	return $count;
    }

    /**
     * 获取云盘文件夹下文件和文件夹总数
     * @param  string $uid         文件夹所属用户ID(与用户服务ID一致)
     * @param  string $pDirID      文件夹ID
     * @param  bool $allowHidden   是否包含隐藏文件夹
     * @return {[type]}              [description]
     */
    public function getYunpanFileAndDirsTotal($cyuid, $pDirID, $allowHidden){
    	//参数信息合法检查  		
		if(!isset($cyuid) || !isset($pDirID)){
			return null;
		}
		$data = $this->diskClient->getTotal($cyuid, $pDirID, $allowHidden);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }

        return $result;
    }

    /**
     * 判断云盘文件夹是否为空
     * @param  string $uid        文件夹所属用户ID(与用户服务ID一致)
     * @param  stirng $dirID      目录ID
     * @return {[type]}         [description]
     */
    public function checkYunpanDirIsEmpty($cyuid, $dirID){
    	//参数信息合法检查  		
		if(!isset($cyuid) || !isset($dirID)){
			return null;
		}
		$data = $this->diskClient->checkDirIsEmpty($cyuid, $dirID);
  		if ($data->hasError){
        	$result['statuscode'] = 0;
            $result['data'] = $data->obj->errorInfo;
        } else {
        	$result['statuscode'] = 1;
            $result['data'] = $data;
        }

        return $result;
    }
    
    /**
     * 添加资源到指定的文件夹
     * @param string $cyuid 用户cyuid
     * @param string $rID 资源id
     * @param string $dirId 要存放资源的文件夹id
     * @param string $need_feed 是否开启动态
     */
    public function addResourceToDir($cyuid, $rID, $dirId, $need_feed = false,$fromtype = 0){
    	$isPublic = false;
    	Log::write("cyuid : ".$cyuid." rid : ".$rID." dirId : ".$dirId,Log::DEBUG);
    	$result = $this->diskClient->importGatewayFiles($cyuid, array($rID), $dirId, true, $fromtype, $isPublic);
    	Log::write("上传后的资源信息：".json_encode($result),Log::DEBUG);
    	if($result->hasError==false && !$fromtype){
    		$res = ResourceClient::getInstance()->Res_GetResIndex($rID);
    		$file_name = $res->data[0]->general->title;
    		$fid = $result->obj->listVal[0];
    		Log::write("上传后的资源信息fid：".json_encode($fid),Log::DEBUG);
    		$r = $this->diskClient->setFileProperties($cyuid,$fid,array("aliasname"=>$file_name));
    		Log::write("设置别名结果：".json_encode($r),Log::DEBUG);
    		
    		// 处理其他上传至云盘后相关设置
    		$this->addResourceRelated($cyuid, $rID, $need_feed, $isPublic);
    	}else{
    		$errorInfo = $result->obj->errorInfo;
    		Log::write('addResourceToDir importGatewayFiles result ' . $errorInfo->errorcode . ',' . $errorInfo->msg);
    	}
    	return $result;
    }
    /**
     * 添加上传的资源到云盘根目录
     * @param string $cyuid
     * @param string $rID
     * @param bool $need_feed 是否需要发动态
     */
    public function addResourceToYunpan($cyuid, $rID, $need_feed = false){
    	$isPublic = true;
		Log::write("cyuid : ".$cyuid." rid : ".$rID,Log::DEBUG);
    	$result = $this->diskClient->importGatewayFiles($cyuid, array($rID), '0', true, 0, $isPublic);
    	if($result->hasError==false){
    		// 处理其他上传至云盘后相关设置
    		$this->addResourceRelated($cyuid, $rID, $need_feed, $isPublic);
    	}else{
    		$errorInfo = $result->obj->errorInfo;
    		Log::write('addResourceToYunpan importGatewayFiles result ' . $errorInfo->errorcode . ',' . $errorInfo->msg);
    	}
    	return $result; 
    }
    
    /**
     * 王云盘添加资源后相关事项处理
     * @param string $cyuid 用户cyuid
     * @param string $rID 资源id
     * @param string $need_feed 是否开启动态
     */
    private function addResourceRelated($cyuid, $rID, $need_feed, $isPublic){
    	Log::write("addResourceRelated start ****",Log::DEBUG);
    	//修改云盘已使用容量，上传资源数
    	$user = model("CyUser")->getUserInfo($cyuid);
    	Log::write("addResourceRelated cyuser : ".json_encode($user),Log::DEBUG);
    	$loginName = $user["login"];
    	$user = model("User")->getUserInfoByLogin($user['login']);
    	Log::write("addResourceRelated user : ".json_encode($user),Log::DEBUG);
    	$uid = $user["uid"];
    	$res = ResourceClient::getInstance()->Res_GetResIndex($rID);
    	Log::write("addResourceRelated res : ".json_encode($res),Log::DEBUG);
    	$file_name = $res->data[0]->general->title;
    	
    	// 设置资源别名
    	if($need_feed){
    		$data = array(
    				"content"=>"",
    				"body"=>'我上传了资源【'.$file_name.'】',
    				"source_url"=>"[PREVIEW_SITE_URL]&id=".$rID
    		);
    		D('Feed')->put($uid, $app = 'yunpan', 'post', $data);
    	}
    	D('UserData')->updateKey('upload_yunpan_count', 1);
    	Log::write("上传资源数+1", Log::DEBUG);

		// 增加上传资源积分
		if ($res->statuscode == 200) {
			// 获取上传用户数据
			if ($user) {
                $totalCount = C('UPLOAD_SCORE_LIMIT');
                $currentCount = D('UploadRecord')->getCuttentCounts($loginName);
                Log::write($loginName."当天已经上传的资源数:".$currentCount, Log::DEBUG);
                if($currentCount !== false && $currentCount < $totalCount){
                	if($isPublic){
	                    // 增加下载资源积分
	                    $credit_result = M('Credit')->setUserCredit($user['uid'], 'upload_resource');
	                    if ($credit_result !== false) {
	                        // 增加积分成功，添加积分日志
	                        $data = array(
	                            'content' => '上传资源:' . $res->data[0]->general->title,
	                            'url' => '',
	                            'rule' => $credit_result
	                        );
	                        M('CreditRecord')->addCreditRecord($user['uid'], $user['login'], 'upload_resource', $data);
	                    }
                	}
                }

                $map['login'] = $loginName;
                $map['count'] = $currentCount + 1;
                D('UploadRecord')->updateRecord($map);
      		}
		}
    }

    /**
     * 搜索资源
     * @param  string  $cyuid     用户cyuid
     * @param  string  $q         查询内容
     * @param  int     $page      页码, 默认1
     * @param  int     $limit     返回数据条数, 默认0(无限制)
     * @param  int     $ordertype 排序方式, 0: 资源ID, 1:更新时间, 2:名称, 3: 创建时间
     * @param  bool    $isdesc    排序方式, 默认倒排序
     * @return array            [description]
     */
    public function search($cyuid, $q, $page=0, $limit=10, $ordertype=3, $isdesc=true){
        //参数信息合法检查
        if(!isset($cyuid) || !isset($q)){
            return array();
        }

        return $this->diskClient->search($cyuid, $q, $page, $limit, $ordertype, $isdesc);
    }
    
      /**
       * 获取目录属性元数据
       * @param string $uid
       * @param string $dirID
       */
    public function getDirProps($uid,$dirID){
        return $this->diskClient->getDirProps($uid,$dirID);
    }
    
    /**
     * 获取文件夹字节大小
     * @param string $uid
     * @param string $dirID
     * @return float
     */
    public function getDirSize($uid, $dirID, $ignoreCollect = true){
    	$result = $this->diskClient->getDirSize($uid, $dirID, $ignoreCollect);
    	if($result->hasError==false){
    		return array('statuscode'=>1, 'data'=>$result->obj->i64Val);
    	}
    	else{
    		return array('statuscode'=>0, 'data'=>$result->obj->errorInfo);
    	}
    }

    /**
     * 获取文件夹ID
     * @param string $uid
     * @param string $pDirId
     * @param string $name
     * @return float
     */
    public function getDirId($uid, $pDirId, $name){
		$panKey = $uid.$pDirId.$name;
		$result = S($panKey);
		if(empty($result)) {
			$result = $this->diskClient->getDirId($uid, $pDirId, $name);
			S($panKey,$result,360000);
		}
        if ($result->hasError) {
            return false;
        }
        return $result->obj->dirInfoVal->fid;
    }

    /**
     *
     * @$ids $idList array example(
     *	array(
     *         array("uid" => "2134000016000000817", "fid" => "1ffe6df8-5e2c-4c0c-93f4-0e5c04f0051c"),
     *         array("uid" => "2134000017000003364", "fid" => "e1898e89-3f39-405a-b512-a73a50ca8eac"),
     *     );)
     */
    public function getDirsByIds($ids){
        if(!is_array($ids)){
            return array();
        }
        return $this->diskClient->getDirsByIds($ids);
    }
    
    /**
     * 获取我的文档文件夹fid
     * @param $cyuid 用户cyuid
     * 注：查到时返回该文件夹fid，否则返回根目录Id
     */
    public function getDocFid($cyuid){
    	$dirInfo = $this->diskClient->getDirId($cyuid,0,'我的文档');
    	if(!$dirInfo->hasError){
    		$wdId = $dirInfo->obj->dirInfoVal->fid;
    	}else{
    		$wdId = 0;
    	}
    	return $wdId;
    }
}