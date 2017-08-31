<?php
/**
 * 用户角色映射关系数据库模型
 * @author hhshi
 * 2014.6.24
 */
class UserRoleMapModel extends Model {
	protected $tableName = 'user_role_map';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'cloud_role',
			2 => 'role_name',
			3 => 'en_name',
			4 => 'app_name',
			5 => 'creator',
			6 => 'role_type',
			7 => 'create_time'
	);
	
	/**
	 * 根据条件获取用户角色映射表
	 * @param array $conditions 查询的条件
	 * @return array() 用户映射表数组
	 */
	public function listRoleMap($conditions = array()){
		$list = $this->where(array())->select();
		return $list;
	}
}
?>