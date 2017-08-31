<?php
/**
 * IFlyBook服务模型
 * @author yuliu2
 */
class IFlyBookModel extends Model {

	//bbt、iflybook专用cyuid
	const CY_UID = 'a0a3136e4082fb8396e2787b38c5f6d6';
	
	function __construct() {
		parent::__construct();
	}

	/**
	 * 获取资源下载地址
	 * @param string $resid
	 * @return stdClass
	 */
	function getresource($resid){
		$result = D('YunpanFile','yunpan')->getFileUrl(self::CY_UID, $resid);
		$fileInfo = D('YunpanFile','yunpan')->getFile(self::CY_UID, $resid);
		$file = new stdClass();
		if($result['statuscode'] && $fileInfo['statuscode']){
			$resource = new stdClass();
			$resource->general = new stdClass();
			$resource->general->id = $resid;
			$resource->general->title = $fileInfo['data']->name;
			$resource->general->extension = $fileInfo['data']->extension;
			//获取文件缩略图
			$thumbnail = D('YunpanFile','yunpan')->getThumbnail(self::CY_UID, $resid , 128, 96);
			if(!$thumbnail->hasError){
				$resource->thumbnail_url = $thumbnail->obj->strVal;
			}else{
				$resource->thumbnail_url = '';
				$errorInfo = $thumbnail->obj->errorInfo;
				Log::write('获取缩略图失败：' . $errorInfo->errorcode . ' ' . $errorInfo->msg);
			}
			
			$resource->properties = new stdClass();
			$fileProps = D('YunpanFile','yunpan')->getFileProps(self::CY_UID, $resid);
			if($fileProps['statuscode'] && !empty($fileProps['data'])){
				//学科书本年级信息
				$resource->properties = $fileProps['data'];
				Log::write('getFileProps： ' . json_encode($fileProps['data']));
			}
			
			$resource->file_url = $result['data']->obj->strVal;
			$file->data = array($resource);
		}else{
			Log::write('getFileUrl： ' . json_encode($result['data']));
			Log::write('getFile： ' . json_encode($fileInfo['data']));
		}
		return $file;
	}
	
	/**
	 * 查询用户云盘根目录下所有文件
	 * @param string $username
	 * @param int $page
	 * @param int $limit
	 */
	function list_resource($username, $page, $limit){
		$cyuser = model('CyUser')->getUserByLoginName($username);
		$restParams = array();
		$restParams['method'] = 'pan.dirid.get';
		$restParams['uid'] = $cyuser['cyuid'];
		$restParams['folderType'] = 'yun_wendang';
		$wendang_fid = Restful::sendGetRequest($restParams);
		$list = D('YunpanFile','yunpan')->listFilesByCategory($cyuser['cyuid'], $wendang_fid, 'all', $page, $limit);
		$result = array();
		$result['count'] = $list->total;
		$array_list = array();
		foreach($list->data as $resource){
			$tmp['id'] = $resource->fid;
			$tmp['rid'] = $resource->fid;
			$tmp['title'] = $resource->name;
			$tmp['suffix'] = $resource->extension;
			//获取文件缩略图
			$thumbnail = D('YunpanFile','yunpan')->getThumbnail(self::CY_UID, $resource->fid , 128, 96);
			if(!$thumbnail->hasError){
				$tmp['thumbnail_url'] = $thumbnail->obj->strVal;
			}else{
				$tmp['thumbnail_url'] = '';
				$errorInfo = $thumbnail->obj->errorInfo;
				Log::write('获取缩略图失败：' . $errorInfo->errorcode . ' ' . $errorInfo->msg);
			}
			//thumbnail,sourcetype,extension
			$array_list[] = $tmp;
		}
		$result['data'] = $array_list;
		return $result;
	}
	
}

?>