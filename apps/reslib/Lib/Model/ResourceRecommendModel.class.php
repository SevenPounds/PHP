<?php
/**
 * 资源操作类模型
 * @author yxxing
 */
class ResourceRecommendModel extends Model {

	protected $tableName = 'resource_recommend';
	protected $error = '';
	protected $fields = array(
			0 =>'id',
			1=>'resource_id',
			2=>'login_name',
			3=>'uid',
			4=>'dateline'
	);	
	
	/**
	 * 添加资源操作信息
	 * @param array $res_operation
	 */
	public function increaseRes($res_operation) {
		if(!is_array($res_operation) || empty($res_operation)){
			return false;
		}
		$result = $this->where(array("resource_id"=>$res_operation['resource_id'],"uid"=>$res_operation['uid'],"login_name"=>$res_operation['login_name']))->save(array("dateline"=>$res_operation['dateline']));
		if($result){
			return $result;
		}
		return $this->add($res_operation);
	}
	
	/**
	 * 根据查询条件，返回符合条件的资源
	 * 
	 * @param string $login_name 用户的login名
	 * @param array $condition 查询条件
	 * @param int $pageSize 分页大小
	 * @param string $sort 排序条件
	 * @param array $fields 返回内容
	 * 
	 * @return array 根据传入的$fields返回值
	 */
	public function getResByCondition($login, $condition, $pageSize = 10, $sort = "res_cmd.dateline DESC", $fields = array()){
		
		!is_array($condition) && $condition = array();
		$condition = array_merge($condition, array("res_cmd.login_name"=>$login, "res.is_del"=>0));
		empty($fields) && $fields = array(
				"res.id", 
				"res.title", 
				"res.rid", 
				"res.username", 
				"res.creator", 
				"res_cmd.dateline",
				"res.downloadtimes",
				"res.praiserate",
				"res.score",
				"res.uploaddateline",
				"res.type2",
				"res_cmd.uid",
				"res.suffix");
		empty($sort) && $sort = "res_cmd.dateline DESC";
		if(!empty($condition['keywords'])){
			$where['res.title'] = array('like','%'.$condition['keywords'].'%');
			$where['res.keywords'] = array('like','%'.$condition['keywords'].'%');
			$where['res.description'] = array('like','%'.$condition['keywords'].'%');
			$where['_logic'] = 'OR';
			unset($condition['keywords']);
			$condition['_complex'] = $where;
		}
		return $this->table($this->tablePrefix.''.$this->tableName.' res_cmd LEFT JOIN '.$this->tablePrefix.'resource res ON res_cmd.resource_id=res.id')
			->where($condition)
			->order($sort)
			->field($fields)
			->findPage($pageSize);
	}
}