<?php
/**
 * 资源模型
 * @version 1.0
 */
class ResourceModel	extends	Model {

	protected $tableName = 'resource';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'rid',
			2 => 'title',
			3 => 'keywords',
// 			4 => 'uid',
			5 => 'username',
			6 => 'uploaddateline',
			7 => 'product_id',
			8 => 'suffix',
			9 => 'type1',
			10 => 'type2',
			11 => 'restype',
			12 => 'downloadtimes',
			13 => 'praisetimes',
			14 => 'negationtimes',
			15 => 'praiserate',
			16 => 'is_del',
			17 => 'description',
			18 => 'grade',
			19 => 'subject',
			20 => 'province',
			21 => 'city',
			22 => 'county',
			23 => 'school_id',
			24 => 'audit_uid',
			25 => 'audit_status',
			26 => 'province_level',
			27 => 'city_level',
			28 => 'county_level',
			29 => 'province_auditor',
			30 => 'city_auditor',
			31 => 'county_auditor',
			32 => 'size',
			33 => 'creator',
			34 => 'source',
			35 => 'countyratedate',
			36 => 'cityratedate',
			37 => 'provinceratedate',
			38 => 'score'
	);
	/**
	 * 修改资源
	 * @param string $rid 资源id
	 * @param array $info 资源信息
	 */
	public function updateRes($rid, $info) {
		if(empty($rid) || empty($info)){
			return false;
		}
		if(is_array($rid)){
			foreach($rid as $key=>$value){
				$res[$key]=$this->where(array('rid'=>$value))->save($info);
			}
			$result=!in_array(false, $res);
		}else{
			$result=$this->where(array('rid'=>$rid))->save($info);
		}
		return $result;
	}
	/**
	 * 修改资源信息
	 * @param string $rid 资源id
	 * @param string $restype 资源类型
	 */
	public function updateRestypeById($rid, $restype) {
		if(!$rid || !$restype){
			return false;
		}
		return $this->where(array('rid'=>$rid))->save(array('restype'=>$restype));
	}
	
	/**
	 * 通过资源ID删除资源
	 * @param string $rid 资源ID
	 */
	public function delete($rid){
		if(!$rid){
			return false;
		}
		return $this->where("rid=".rid)->save(array("is_del"=>1));
	}
	
	/**
	 * 添加资源
	 * @param array $res 资源信息
	 */
	public function increase($res){
		if(empty($res) || !is_array($res) || !$res['rid']){
			return false;
		}
		
		$result = $this->add($res); 
		return $result;
	}
	/**
	 * 通过资源id获取资源
	 * @param string $rid 资源ID
	 */
	public function fetchByRid($rid){
		if(is_array($rid)){
			foreach ($rid as $key=>$value){
				$result[$key]=$this->where("rid='".$value."'")->find();
			}
			return $result;
		}else{
			if(!isset($rid)){
				return false;
			}
			return $this->where("rid='".$rid."'")->find();
		}
	}
	
	/**
	 * 获取推荐资源
	 * @param string $sort
	 */
	public function getRecommendRes($sort){
		if(!$sort){
			return false;
		}
		return $this->table($this->tablePrefix."resource AS res")->where("res.is_del=0")->order($sort)->limit("0,10")->findAll();
	}
	
	/**
	 * 通过查询条件返回资源列表
	 * @param int $lcid 地区id
	 * @param char $subid 学校id
	 * @param int $status 审核状态
	 * @param int $prelevel 是否精品
	 * @param int $role 用户教研员角色
	 * @param int $pageindex 页码
	 * @param int $pagesize 每页显示的数量
	 */
	public function getResListByCondition($lcid,$subid,$status,$prelevel,$role,$pageindex,$pagesize,$keyword='',$userlocation,$is_del = 0,$order='uploaddateline ASC'){
		if(!$order){
			$order = 'uploaddateline ASC';
		}
		$map=array();
		switch($role){
			//省级
			case UserRoleTypeModel::PROVINCE_RESAERCHER:
				if($prelevel){
					$map['province_level']=$prelevel;
				}else{
					$map['province_level']=0;
				}
				$map['province']= $userlocation['province']['id'];
				$map['city_level']=1;
				$map['county_level']=1;
				$map['audit_status']= 1;
				if($lcid){
					$map['city']=$lcid;
				}
				break;
			//市级
			case UserRoleTypeModel::CITY_RESAERCHER:
			//	$map['province_level']=0;
				$map['city_level']=$prelevel;
				$map['county_level']=1;
				$map['audit_status']= 1;
				$map['province']= $userlocation['province']['id'];
				$map['city']= $userlocation['city']['id'];
				if($lcid){
					$map['county']=$lcid;
				}
				break;
			//县级
			case UserRoleTypeModel::COUNTY_RESAERCHER:
				$map['audit_status']= $status;
				/* $map['province_level']=0;
				$map['city_level']=0; */
				$map['county_level']= $prelevel;
				$map['province']= $userlocation['province']['id'];
				$map['city']= $userlocation['city']['id'];
				$map['county']= $userlocation['district']['id'];
				if($prelevel){
					$map['audit_status']= 1;
				}
				if($lcid){
					$map['school_id']=$lcid;
				}
				break;
		}
		if(!empty($subid)&&$subid){
			$map['subject']=$subid;
		}
		if(!empty($keyword)){
			$map['title']=array('like','%'.$keyword.'%');
		}
		if(isset($is_del)){
			$map['is_del']=$is_del;
		}
		$map['product_id']=array('eq','rrt');//只取来自资源库和资源凭条的上传资源
		$result=$this->where($map)->order($order)->findPage($pagesize);
		//return $this->getLastSql();
		return $result;
	}
	
	/**
	 * by frsun 2013.10.10 14:22:46
	 * 提供给电子白板查询网盘资源使用的接口
	 * @param array $condition
	 */
	public function getNetdiskResources($condition = array()){		
		if(!empty($condition['keyword'])){
			if(strtolower($condition['match'])=='true'){
				$where['r.title'] = $condition['keyword'];
				$where['r.keywords'] = $condition['keyword'];
				$where['_logic'] = 'OR';
			}else{
				$where['r.title'] = array('like','%'.$condition['keyword'].'%');
				$where['r.keywords'] = array('like','%'.$condition['keyword'].'%');
				$where['_logic'] = 'OR';
			}
			$map['_complex'] = $where;
		}		
		$map['ro.login_name'] =  $condition['author'];
		$map['r.is_del'] = 0;
		$map['ro.operationtype'] = array('in','1,4');
		if(!empty($condition['type'])){
		    $map['r.restype'] = array('in',$condition['type']);
		}
		$field = 'r.*';
		if(!empty($condition['sidx'])){
			$order = 'r.'.$condition['sidx'].' '.$condition['sord'];
		}
		$limit = $condition['size'];
		$start = $condition['page']*$limit-$limit;
				
		$count = $this->table('`'.$this->tablePrefix.$this->tableName.'` AS r LEFT JOIN `'.$this->tablePrefix.'resource_operation` AS ro ON r.id = ro.resource_id')->where($map)->count();
		
		$list = $this->table('`'.$this->tablePrefix.$this->tableName.'` AS r LEFT JOIN `'.$this->tablePrefix.'resource_operation` AS ro ON r.id = ro.resource_id')->where($map)->field($field)->order($order)->limit("$start,$limit")->select();
		$result = array("records"=>intval($count),"rows"=>$list);
		Log::write($this->getLastSql());
		return $result;
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
	public function getRRTResource($login, $condition, $pageSize = 10, $sort = "res.dateline DESC", $fields = array()){
	
		$condition = array_merge($condition, array("res.creator"=>$login, "res.is_del"=>0, "res.product_id"=>"rrt"));
		empty($fields) && $fields = array(
				"res.id",
				"res.title",
				"res.rid",
				"res.username",
				"res.creator",
				"res.downloadtimes",
				"res.praiserate",
				"res.score",
				"res.uploaddateline",
				"res.restype",
				"res.type2",
				"res.description",
				"res.audit_status",
				"res.suffix");
		empty($sort) && $sort = "res.uploaddateline DESC";
	
		return $this->table($this->tablePrefix.$this->tableName.' AS res')
					->where($condition)
					->order($sort)
					->field($fields)
					->findPage($pageSize);
	}
}