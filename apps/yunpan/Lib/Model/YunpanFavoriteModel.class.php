<?php
/**
 * 云盘收藏夹模型
 * yuliu2@iflytek.com
 */


use iflydisk\IFlyDisk;

class YunpanFavoriteModel	extends	Model {

    protected $tableName = 'yunpan_favorite';

	protected $error = '';

	private $diskClient = null;

	private $resourceClient = null;
	
	//我的备课本
	const BEI_KE_BEN = 1001;

	//我的收藏
	const COLLECTION = 1002;	
	
	function __construct() {
		parent::__construct();
		$this->diskClient = IflytekdiskClient::getInstance();
		$this->resourceClient = ResourceClient::getInstance();
	}
	
	/**
	 * 收录到备课本
	 * @author yuliu2@iflytek.com
	 * @param string $cyuid 用户cyuid
	 * @param string $bkbDirId 备课本目录id
	 * @param string $rid 资源包id
	 * @return array('statuscode'=>0|1,'data'=>'xx')
	 */
	function addToBook($cyuid, $bkbDirId, $rid){
		$return_result = array();
		
		if(empty($cyuid) || empty($bkbDirId) || empty($rid)){
			$return_result['statuscode'] = 0;
			$return_result['data'] = '参数不正确';
			return $return_result;
		}

        // 查询资源包
        $packageDetail = $this->resourceClient->Res_GetResIndex($rid);
        if($packageDetail->statuscode == 200 && !empty($packageDetail->data)){
            $packageDetail = $packageDetail->data[0];
        }else{
            $return_result['statuscode'] = 0;
            $return_result['data'] = '查询资源包信息失败';
            return $return_result;
        }
       	
        $metadata['book'] = $packageDetail->properties->book[0];
        $metadata['course'] = self::getCoursePath($packageDetail->properties);
        Log::write('book: '. $metadata['book'] .' course: '. $metadata['course'], Log::DEBUG);
        //查询课	
        $course_result = $this->diskClient->listDirsByProperties($cyuid, $bkbDirId, $metadata, 0, 1, 3, true);
        if($course_result->hasError){
        	$errorInfo = $course_result->obj->errorInfo;
        	Log::write($cyuid . ', bkbDirId '. $bkbDirId .' listDirsByProperties ' . $errorInfo->errorcode . ',' . $errorInfo->msg);
        	$return_result['statuscode'] = 0;
        	$return_result['message'] = $errorInfo->msg;
        	return $return_result;
        }
        if($course_result->obj->mapVal['total'] == 0){
        	$return_result['statuscode'] = 0;
        	$return_result['message'] = '备课本缺少相应课，无法收录';
        	return $return_result;
        }else{
        	$course_data = json_decode($course_result->obj->mapVal['data']);
        	$course = $course_data[0];
        }
		//查询资源包里面的资源
		$result = $this->resourceClient->Get_Resources_In_Package($rid, array('general,properties'));

		if($result->statuscode == 200 && !empty($result->data)){
			$resource_list = $result->data;
		}else{
			$return_result['statuscode'] = 0;
			$return_result['data'] = '查询资源包的资源信息失败';
			return $return_result;
		}

        // 资源包里面的资源id数组
        $resList = array();
        foreach ($resource_list as $resource){
            $resList[] = $resource->general->id;
        }
		Log::write('course fid is ' . $course->fid . ', resList size is ' . count($resList), Log::INFO);
        //资源包有资源、且备课本课目录已经创建
        if(!empty($resList)){
        	$data = $this->diskClient->importGatewayFiles($cyuid, $resList, $course->fid, false);
        	if($data->hasError){
        		$return_result['data'] = $data->obj->errorInfo->errorcode == 40006 ? '已经收录过该资源包' : $data->obj->errorInfo->msg;
        		$return_result['statuscode'] = 0;
        		return $return_result;
        	}
        }

		$return_result['statuscode'] = 1;
		$return_result['data'] = '收录成功';
		return $return_result;
	}
	
	/**
	 * 资源收藏RPC服务调用
	 * @param string $login_name 用户名
	 * @param string $rid 资源id
	 * @param boolean $is_package 是否是资源包
	 * @return array('status'=>true|false,'message'=>'xx')
	 */
	function collectResource($login_name,$rid,$is_package){
		$resCount = 0;
		$return_result = array();

		if(empty($login_name) || empty($rid)){
			$return_result['status'] = false;
			$return_result['message'] = '参数错误';
			return $return_result;			
		}

		$cyuser = model('CyUser')->getUserByLoginName($login_name);
        // 新生成的文件夹fid或者是文件fid
		$rids = explode(',',$rid);
		foreach($rids as $value){
			$restParams = array();
			$restParams['method'] = 'pan.file.import';
			$restParams['uid'] = $cyuser['cyuid'];
			$restParams['fileIds'] = $value;

            $restParams['module'] = C("grkj_module");
            $restParams['action'] = C("EDUSND_ACTION")["import"];

			$rest_result = Restful::sendGetRequest($restParams);
			if(!$rest_result->return){
				$return_result['status'] = false;
				$return_result['message'] = '资源保存失败';
				return $return_result;
			}
		}
		$return_result['status'] = true;
		$return_result['message'] = '资源保存成功';
		return $return_result;
	}
	/**
	 * 收录云盘收藏夹中的资源包
	 * @author yuliu2
	 * @param $cyuid 用户id
	 * @param $fid 收藏夹中资源包目录id
	 * @return array('status' => true|false, 'message' => 'xx') 返回信息
	 */
	public function collectResourcePackage($cyuid, $fid){
		$result = array('status' => false);
		$files = model('Yunpan')->getYunpanFiles($cyuid, $fid, 0, 0);
		$fileArray = array();
		if($files->total == 0){
			$result['message'] = '资源包为空';
			return $result;
		}
		foreach($files->data as $file){
			$fileArray[] = $file->fid;
		}
		//获取所有备课本文件夹
		$folderId = model('Yunpan')->getDirId($cyuid, 0, '我的备课本');
		if(empty($folderId)){
			$result['message'] = '请先创建相应备课本';
			return $result;
		}
		$packageDir = model('Yunpan')->getDirProps($cyuid, $fid);
		if(empty($packageDir->book)){
			$result['message'] = '查询资源包书本信息失败';
			return $result;
		}
		$metadata['book'] = $packageDir->book[0];
		$metadata['course'] = $packageDir->course[0];
		Log::write('book: '. $metadata['book'] .' course: '. $metadata['course'], Log::DEBUG);
		//递归查询其子目录
		$units = $this->diskClient->listDirsByProperties($cyuid, $folderId, $metadata, 0, 1, 3, true);
		if($units->hasError){
			$errorInfo = $units->obj->errorInfo;
			Log::write($cyuid . ', fid '. $fid .' listDirsByProperties ' . $errorInfo->errorcode . ',' . $errorInfo->msg);
			$result['message'] = $errorInfo->msg;
			return $result;
		}
		$list = $units->obj->mapVal;
		if($list['total'] == 0){
			$result['message'] = '未找到相应备课本或课时';
			return $result;
		}
		$data = json_decode($list['data']);
		$unit = $data[0]->fid;
		//批量拷贝文件到单元目录下
		$cpResult = $this->diskClient->copyFiles($cyuid, $fileArray, $unit, false);
		if($cpResult->hasError){
			$errorInfo = $cpResult->obj->errorInfo;
			Log::write($cyuid . ', unit_dir '. $unit .' copyFiles ' . $errorInfo->errorcode . ',' . $errorInfo->msg);
			$result['message'] = $errorInfo->msg;
			return $result;
		}
		$result['status'] = true;
		$result['message'] = '收录成功';
		return $result;
	}

    /**
     * 判断是否已经收藏了资源
     * @param $login
     * @param $rid
     * @return bool
     */
    public function isFavorite($login,$rid){
        $map['login'] = $login;
        $map['rid'] = $rid;
        $result = $this->where($map)->find();
        if($result){
            if($result['is_deleted'] == 0){
                return true;
            }else{
                return false;
            }
        }else if($result !== false){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 判断是否收藏过该资源
     * @param $login
     * @param $rid
     * @return bool
     */
    public function isNotExistRecord($login,$rid){
        $map['login'] = $login;
        $map['rid'] = $rid;
        $result = $this->where($map)->find();

        if($result){
            return false;
        }else if($result !== false){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 增加收藏记录
     * @param $data array 收藏记录
     * @return mixed 新增的id|失败返回false
     */
    public function addFavorite($data){
        $map['login'] = $data['login'];
        $map['rid'] = $data['rid'];
        $map['fid'] = $data['fid'];
        $nowTime = new DateTime();
        $map['ctime'] = $nowTime->format("Y-m-d H:i:s");
        $map['mtime'] = $nowTime->format("Y-m-d H:i:s");
        $map['is_deleted'] = 0;
        return $this->add($map);
    }

    /**
     * 更新收藏的资源
     * @param $login
     * @param $rid
     * @param $fid
     * @param $isDeleted int 删除还是更新记录(1表示删除,0表示更新)
     * @return mixed 数据库更新操作影响的行数|操作失败返回false
     */
    public function updateFavorite($login,$rid,$fid,$isDeleted = 0){
        $map['login'] = $login;
        $map['rid'] = $rid;
        $data['is_deleted'] = $isDeleted;
        $data['fid'] = $fid;
        $nowTime = new DateTime();
        $data['mtime'] = $nowTime->format("Y-m-d H:i:s");

        return $this->where($map)->save($data);
    }

    /**
     * 删除收藏的资源
     * @param $login
     * @param $fid
     */
    public function deleteFavorite($login,$fid){
        $map['login'] = $login;
        $map['fid'] = $fid;
        $data['is_deleted'] = 1;
        $nowTime = new DateTime();
        $data['mtime'] = $nowTime->format("Y-m-d H:i:s");

        return $this->where($map)->save($data);
    }
    /**
     * @author yuliu2
     * 获取到课目录唯一Code路径
     * @param object $properties
     * @return string $path 'unit1-unit2-unit3' 多级目录合并，拼凑唯一到课path
     */
    public static function getCoursePath($properties){
    	//支持三级目录，拼凑到课路径
    	$unit = $properties->unit1[0];
    	$unit2 = $properties->unit2;
    	$unit3 = $properties->unit3;
    	if($unit2){
    		$unit = $unit . '-' . $unit2[0];
    	}
    	if($unit3){
    		$unit = $unit . '-' . $unit3[0];
    	}
    	return $unit;
    }
}
