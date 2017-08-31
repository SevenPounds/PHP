<?php
class UserPraiseModel extends Model{
	protected $tableName = 'user_praise';
	protected $tablePraise = 'praise';
	
	protected $fields = array(
			0=>'id',
			1=>'uid',
			2=>'praise_id',
			3=>'grant_uid',
			4=>'classId',
			5=>'message',
			6=>'ctime',
			7=>'is_del',
			'_autoinc' => true,
			'_pk' => 'id'
			);
	
	/**
	 * 添加或修改用户表扬信息
	 * @param array $d 相关用户组信息
	 * @return integer 相关用户组ID
	 */
	public function addUserPraise($d) {
		$data['ctime'] = time();
		isset($d['uid']) && $data['uid'] = intval($d['uid']);
		isset($d['praise_id']) && $data['praise_id'] =intval($d['praise_id']);
		isset($d['grant_uid']) && $data['grant_uid'] =intval($d['grant_uid']);
		isset($d['classId']) && $data['classId'] =intval($d['classId']);

        if(!empty($d['id'])) {
        	$amap['id'] = $d['id'];
        	$res = $this->where($amap)->save($data);
        } else {
        	$res = $this->add($data);
        }
        // 清除相关缓存
        $this->cleanCache();
        return $res;
	}
	
	/**
	 * 查询表扬列表
	 * @param array or string $condition 
	 * @param integer $start
	 * @param integer $limit
	 */
	public function list_praise($condition,$field,$order='up.`ctime` DESC',$limit=10){
		if(!$field){
			$field = 'up.`uid` ,up.`grant_uid` ,up.`message`,up.`ctime`,p.`praise_id` ,p.`praise_name`,p.`icon`,p.`logo` ';
		}
		$tables = $this->tablePrefix.$this->tableName.' up ,'.$this->tablePrefix.$this->tablePraise.' p ';
		return $this->field($field)->table($tables)->where($condition)->order($order)->findpage($limit);
	}
	
	
	
}