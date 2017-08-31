<?php
/**
 * @package msgroup\Lib\Model
 * @author yangli4@iflytek.com
 * 名师工作室 荣誉信息Model
 */
class MSGroupPhotoModel extends Model {
	protected $tableName = 'msgroup_photo';

	protected $fields = array(1=>'id',
			2=>'uid',
			3=>'attachId',
			4=>'gid',
			5=>'photo_name',
			6=>'viewcount',
			7=>'is_deleted',
			8=>'upload_time',
			9=>'mtime',
			10=>'savepath');

	function  _initialize() {
		parent::_initialize();
	}

	/**
	* 获取名师工作室荣誉图片
	* @param int $gid 名师工作室id
	*
	* @return array 师工作室荣誉图片记录数组
	*/
	public function getMSGPhoto($gid,$limit='1,10'){
		if(empty($gid)){
			return false;
		}
		$map = array('gid' => $gid,'is_deleted'=>0);
		$result =$this->order('upload_time desc')->where($map)->limit($limit)->findAll();
		return $result;
	}

	/**
	* 删除名师工作室荣誉图片
	* @param array $photoids 图片id数组
	*
	* @return bool 删除是否成功
	*/
	public function deleteMSGPhoto($photoids){
		$phCount = count($photoids);
		if(empty($photoids) || $phCount == 0){
			return false;
		}
		else{
			$data = array('is_deleted' =>1);
			foreach ($photoids as $id) {
				$this->where(array('id'=>$id))->save($data);
			}
			return true;
		}
	}
	
	/**
	* 更新名师工作室荣誉图片信息
	* @param int $photoid 图片id
	* @param array $updateInfo 更新信息键值对数组
	*
	* @return bool 删除是否成功
	*/
	public function updateMSGPhoto($photoid,$updateInfo){
		if(empty($photoid)){
			return false;
		}
		if(empty($updateInfo) || count($updateInfo) == 0){
			return false;
		}
		$updateInfo['mtime'] = time();
		$this->where(array('id'=>$photoid))->save($updateInfo);
		return true;
	}
	
	/**
	* 更新名师工作室荣誉图片浏览次数
	* @param int $photoid 图片id
	* @param array $countOffset 浏览次数增加量
	*
	* @return bool 删除是否成功
	*/
	public function updateMSGPhotoViewCount($photoid,$countOffset=1){
		if(empty($photoid)){
			return false;
		}
		$result = $this->where(array('id' => $photoid))->findAll();

		if(empty($result) || count($result) == 0){
			return false;
		}
		$this->where(array('id'=>$photoid))->save($updateInfo);
		$updateInfo = array('viewcount'=> $result[0]['viewcount']+$countOffset);
		return $this->updateMSGPhoto($photoid,$updateInfo);
	}


}
?>