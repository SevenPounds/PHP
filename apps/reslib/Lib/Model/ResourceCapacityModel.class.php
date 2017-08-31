<?php
/**
 * 资源模型
 * @version 1.0
 */
class ResourceCapacityModel	extends	Model {

	protected $tableName = 'resource_capacity';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login_name',
			2 => 'used_capacity',
			3 => 'total_capacity',
	);
	
	/**
	 * 根据用户id获取用户的容量信息
	 * @param string $loginName 用户登录账号
	 * @return array 用户的容量信息
	 */
	public function getCapacityInfoByLogin($loginName){
		if(!$loginName){
			return false;
		}
		$result = array();
		
		$res = $this->where("login_name='$loginName'")->field('used_capacity,total_capacity')->find();
		
		// 判断当前用户在数据库中是否有容量信息的记录
		if($res){
			$result['usedCapacity'] = $res['used_capacity'];
			$result['totalCapacity'] = $res['total_capacity'];
		}else{
			// 如果当前用户在数据库中的没有容量记录就新建一条记录
			$data = array();
			// 用户资源库初始总容量
			$capacity = C('RESLIB_USER_TOTAL_CAPACITY');
			$capacity = floatval($capacity)*1024*1024*1024;
			$data['login_name'] = $loginName;
			$data['used_capacity'] = 0;
			$data['total_capacity'] = $capacity;
			$r = $this->add($data);
			if(!$r){
				return fasle;
			}			
			$result['usedCapacity'] = 0;
			$result['totalCapacity'] = $capacity;
		}
		return $result;
	}
	
	/**
	 * 用户上传文件成功时，增加已使用容量
	 * @param string $loginName 用户登录账号
	 * @param int $size 上传的文件大小
	 */
	public function addUsedCapacity($loginName, $size){
		if(!$loginName || !$size){
			return false;
		}
		$size = intval($size);
		$size < 0 && $size *= -1;   
		return $this->setInc('used_capacity', array('login_name'=>$loginName), $size);
		
	}
	
	/**
	 * 用户删除文件成功时，减少容量
	 * @param string $loginName 用户登录账号
	 * @param int $size 删除的文件大小
	 */
	public function decUsedCapacity($loginName, $size){
		if(!$loginName || !$size){
			return false;
		}
		$size = intval($size);
		$size < 0 && $size *= -1;
		return $this->setDec('used_capacity', array('login_name'=>$loginName), $size);
		
	}
}