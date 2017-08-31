<?php
/**
 * 用户帐号绑定对应关系数据库模型
 * @author hhshi
 * 2014.6.19
 */
class UserBindingModel extends Model {
	protected $tableName = 'user_binding';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login_cloud',
			2 => 'login_other',
			3 => 'app_name',
			4 => 'create_time'
	);
	
	/**
	 * 新增绑定记录
	 * @param array $bindingRecord 绑定记录信息模型
	 * ('login_cloud'=>云平台登录名,'login_other'=>其他平台登录名,
	 * 'app_type'=>应用类型，如：qxpt,'create_time'=>当前时间，格式'Y-m-d H:i:s'，非必填)
	 * @return 1:插入成功，0:插入失败，2:已绑定
	 */
	public function addUserBindingRecord($bindingRecord){
		if(!is_array($bindingRecord) || empty($bindingRecord)){
			return -1;
		}
		// 检查创建时间是否为空
		if(empty($bindingRecord['create_time'])){
			$bindingRecord['create_time'] = date('Y-m-d H:i:s',time());
		}
		$cloud['login_cloud'] = $bindingRecord['login_cloud'];
		$checkCloud = $this->where($cloud)->find();
		$other['login_other'] = $bindingRecord['login_other'];
		$checkOther = $this->where($other)->find();
		if(empty($checkCloud) && empty($checkOther)){
			// 插入记录
			$result = $this->add($bindingRecord);
			
			$result = $result ? 1 : 0;
		}else{
			$result = 2;
		}
		
		return $result;
	}
	
	/**
	 * 根据登录名和登录名类型，获取帐号绑定记录信息
	 * @param string $login 登录名
	 * @param string $login_type 登录名类型 cloud:云平台帐号，other:其他平台帐号
	 * @return array(); 帐号绑定信息数组
	 */
	public function getBindingRecord($login,$login_type){
		if(empty($login) || empty($login_type)){
			return array();
		}
		$map = array();
		if($login_type == 'cloud'){
			$map['login_cloud'] = $login;
		}else{
			$map['login_other'] = $login;
		}
		$record = $this->where($map)->find();
		return $record;
	}
}
?>