<?php
/**
 * 区域平台服务模型
 * @author yuliu2
 */
class RegionPlatFormModel extends Model {

	protected $tableName = 'yunpan_region_upload';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login_name',
			2 => 'yunpan_fid',
			3 => 'resource_id',
			4 => 'ctime'
	);
	
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 上传资源到云盘
	 * @param string $login
	 * @param string $resid
	 * @return 0|1
	 */
	function addUploadRecord($login, $resid){
		$cyuser = model('CyUser')->getUserByLoginName($login);
		Log::write('上传资源 ' . $login . ',' . $cyuser['cyuid'] . ',' . $resid);
		
		// 获取教研平台文件夹fid
		$jyptFid = $this->getJyptFid($cyuser['cyuid']);
		
		// 向教研平台文件夹添加文件
		$result = D('Yunpan','yunpan')->addResourceToDir($cyuser['cyuid'], $resid, $jyptFid);
		
		$data = array(
				'login_name' => $login,
				'yunpan_fid' => $result->obj->listVal[0],
				'resource_id' => $resid,
				'ctime' => date('Y-m-d H:i:m')
				);
		$this->add($data);
		return $result->hasError ? 0 : 1;
	}
	
	/**
	 * 根据cyuid检测用户是否有教研平台默认文件夹
	 * @param string $cyuid 用户cyuid
	 */
	private function getJyptFid($cyuid){
		$restParams = array();
		$restParams['method'] = 'pan.dirid.get';
		$restParams['uid'] = $cyuid;
		$restParams['folderType'] = 'yun_wendang';
		$wendang_fid = Restful::sendGetRequest($restParams);
		Log::write("我的文档fid : ".$wendang_fid,Log::DEBUG);
		
		$diskClient = IflytekdiskClient::getInstance();
		$dirInfo = $diskClient->getDirId($cyuid,$wendang_fid,'教研平台');
		if(!$dirInfo->hasError){
			$jyptFid = $dirInfo->obj->dirInfoVal->fid;
		}else{
			$jyptFid = '';
		}
		Log::write("教研平台fid : ".$jyptFid,Log::DEBUG);

		// 未找到教研平台默认文件夹
		if($jyptFid == ''){
			// 创建教研平台默认文件夹
			$createDir = $diskClient->mkdirAndSetProps($cyuid,$wendang_fid,'教研平台', '1001', array(), true);
			Log::write('创建教研平台文件夹结果：'.json_encode($createDir),Log::DEBUG);
			if(!$createDir->hasError){
				// 获取新创建的教研平台文件夹fid，并设置成置顶
				$jyptFid = $createDir->obj->dirInfoVal->fid;
				$result = $diskClient->setOnTop($cyuid, $jyptFid, true);
				Log::write('设置置顶结果：'.json_encode($result),Log::DEBUG);
			}
		}
		return $jyptFid;
	}
	
	/**
	 * 删除云盘资源
	 * @param string $login
	 * @param string $resid
	 * @return 0|1
	 */
	function delUploadRecord($login, $resid){
		Log::write('删除记录参数： login:' . $login.'__resid:'.$resid,Log::DEBUG);
		$cyuser = model('CyUser')->getUserByLoginName($login);
		$upload = $this->where(array('login_name' => $login, 'resource_id' => $resid))->find();
		Log::write('查询结果 ' . json_encode($upload),Log::DEBUG);
		$this->where(array('id' => $upload['id']))->delete();
		Log::write('删除资源 ' . $login . ', cyuid ' . $cyuser['cyuid'] . ',resid ' . $resid . ',fid ' . $upload['yunpan_fid'],Log::INFO);
		$result = D('YunpanFile','yunpan')->deleteFiles($cyuser['cyuid'], array($upload['yunpan_fid']));
		Log::write('删除结果 ' . json_encode($result),Log::DEBUG);
		return $result['statuscode'];
	}
}
?>