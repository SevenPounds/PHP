<?php
/**
 * 用户角色
 * @author cheng
 *
 */
class UserRoleModel extends Model{
	
	protected $tableName = 'user_role';
	protected $fields = array(0=>'id',1=>'uid',2=>'roleid',3=>'rolename','_autoinc'=>true,'_pk'=>'id');

	/**
	 * 根据角色判断用户的权限的问题      by frsun 20130918
	 * @param int $mid    eg:1
	 * @param array $roles  eg:array(9,15)
	 */
	public function IsAuthority($mid,$roles){
		$map['uid'] = $mid;
		$map['roleid'] = array('in',implode(',',$roles));		
		$result = $this->where($map)->find();
		return !empty($result);
	}
	
	/**
	 * 根据用户获取角色信息 
	 * @param integer $uid
	 *  @param bool $bReturn  返回多角色 
	 */
	public function getUserRole($uid,$bReturn = false){
		$map['uid'] = $uid;
		if($bReturn){
			$result = $this->where($map)->select();
		}else{
			$result = $this->where($map)->find();
		}
		return $result;
	}
	
}
?>