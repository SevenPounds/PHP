<?php
/**
 * 资源操作类模型
 * @author yxxing
 */
class ResourceOperationModel extends Model {

	protected $tableName = 'resource_operation';
	protected $error = '';
	protected $fields = array(
			0 =>'id',
			1=>'resource_id',
			2=>'operationtype',
			3=>'dateline',
			4=>'login_name',
// 			5=>'uid',
			6=>'gid'
	);
	

	/**
	 * 资源操作（上传、下载、收藏）保存、更新
	 * @return 1:成功,0:失败,-1:记录已经存在 ,-2:参数不正确
	 * @param array $res_operation
	 */
	public function saveOrUpdate($res_operation) {
		if(!is_array($res_operation) || empty($res_operation)){
			return -2;
		}
		//清除用户资源数量缓存
		$result = $this->where(array("resource_id"=>$res_operation['resource_id'],"login_name"=>$res_operation['login_name'],"operationtype"=>$res_operation['operationtype']))->find();
		if(!$result){
			$result = $this->add($res_operation);
			if(!empty($result)){
				$this->updateUploadResCount($res_operation['login_name'],intval($res_operation['operationtype']),$res_operation['gid'],1);
			}
			return empty($result) ? 0 : 1;
		}else{
			switch ($res_operation['operationtype']){
				case ResoperationType::DOWNLOAD:
					$this->where(array('id'=>$result['id']))->save(array("dateline"=>$res_operation['dateline']));
                    $result = 1;
                    break;
				case ResoperationType::COLLECTION:
				case ResoperationType::MSGROUP_UPLOAD:
				case ResoperationType::UPLOAD:
                    $result = -1;
					break;
			}
			return $result;
		}
	}

	/**
	 * 更新资源上传数量
	 * @param string 			$login,用户登录名 
	 * @param ResoperationType 	$operationtype,资源操作类型，目前仅有：
	 *																ResoperationType::UPLOAD（用户上传到资源库）
	 *																ResoperationType::MSGROUP_UPLOAD（用户上传到名师工作室）
	 * @param int 				$msgid,上传到的名师工作室id
	 * @param int 				$offset,数量增减偏移量，如数量加一，传1；如减一，传-1；
	 */
	private function updateUploadResCount($login,$operationtype,$msgid,$offset){
		if(!$login){
			return false;
		}	
		if($operationtype ==  ResoperationType::UPLOAD){
			$uid = D('User')->where(array("login"=>$login))->getField("uid");
			
			$res = D('UserData')->where(array('uid'=>$uid,'key'=>'upload_res_count'))->getField('value');
			if(!is_null($res)){				
				$newCount = $res+$offset;
				$newCount = $newCount > 0? $newCount :0;	
				D('UserData')->where(array('uid'=>$uid,'key'=>'upload_res_count'))
							 ->save(array('value' => $newCount,'mtime'=> date('Y-m-d H:i:s')));
			}else{
				$newCount = $offset>0? $offset:0;
				D('UserData')->add(array('uid'=>$uid,'key'=>'upload_res_count','value' => $newCount,'mtime'=> date('Y-m-d H:i:s')));
			}
		}else if($operationtype == ResoperationType::MSGROUP_UPLOAD){
			$res = D('MSGroupData','msgroup')->where(array('gid'=>$msgid,'key'=>'upload_res_count'))->getField('value');
			if(!is_null($res)){
				$newCount = $res+$offset;
				$newCount = $newCount > 0 ? $newCount:0;	
				D('MSGroupData','msgroup')->where(array('gid'=>$msgid,'key'=>'upload_res_count'))
										  ->save(array('value' => $newCount,'mtime'=>time()));
			}else{
				$newCount = $offset > 0 ? $offset:0;
				D('MSGroupData','msgroup')->add(array('gid'=>$msgid,'key'=>'upload_res_count','value' => $newCount,'mtime'=>time()));
			}
		}
		return true;

	}

	
	/**
	 * 删除资源
	 * @param int $rid 资源id
	 * @param string $login 用户登录名 
	 * @param int $operationtype 操作类型1234
	 */
	public function deleteRes($rid, $login, $operationtype){
		if(!$rid || !$login || !$operationtype){
			return false;
		}
		//清除用户资源数量缓存
		$map = array();
		$map['login_name'] = $login;
		$map['resource_id'] = intval($rid);
		$map['operationtype'] = $operationtype;

		$msgid = $this->where($map)->getField("gid");
		$res = $this->where($map)->delete();
		//Log::write(json_encode($map));
		//更新资源上传数量
		if($operationtype == ResoperationType::UPLOAD || $operationtype == ResoperationType::MSGROUP_UPLOAD ){

			$this->updateUploadResCount($login,$operationtype,$msgid,-1);
		}
		return 1;
	}
	
	/**
	 * 获取某个用户的资源数
	 * @param int $uid 用户uid
	 */
	public function getResCount($uid){
		if(!$uid){
			return false;
		}
		$count = D('UserData')->where(array('uid'=>$uid,'key'=>'upload_res_count'))->getField('value');
		return $count;
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
	public function getResByCondition($login, $condition, $pageSize = 10, $sort = "res_opr.dateline DESC", $fields = array()){
		
		$condition = array_merge($condition, array("res_opr.login_name"=>$login));
		empty($fields) && $fields = array(
				"res.id",
				"res.title",
				"res.rid",
				"res.username",
				"res.creator",
				"res_opr.dateline",
				"res.downloadtimes",
				"res.praiserate",
				"res.score",
                "res.source",
				"res.uploaddateline",
				"res.type2",
				"res.suffix",
				"res.audit_status",
				"res.county_level",
				"res.city_level",
				"res.province_level");
		empty($sort) && $sort = "res_opr.dateline DESC";
		if(!empty($condition['keywords'])){
			$where['res.title'] = array('like','%'.$condition['keywords'].'%');
			$where['res.keywords'] = array('like','%'.$condition['keywords'].'%');
			$where['res.description'] = array('like','%'.$condition['keywords'].'%');
			$where['_logic'] = 'OR';
			unset($condition['keywords']);
			$condition['_complex'] = $where;
		}
		
		return $this->table($this->tablePrefix.''.$this->tableName.' res_opr RIGHT JOIN '.$this->tablePrefix.'resource res ON res_opr.resource_id=res.id')
		->where($condition)
		->order($sort)
		->field($fields)
		->findPage($pageSize);
	}

	/**
	 * 查询资源
	 * @param string $login 用户登录名
	 * @param array $condition 查询条件（restype：string/array，keywords：string，optype：int/array，suffix：string/array，resource部分字段）
	 * @param int $pageindex 页码
	 * @param int $pageSize 每页记录数
	 * @param string $orderBy 排序字段（dateline、uploddateline、uploddateline、praiserate、size、dateline、downloadtimes）
	 * @param string $sort 排序方式 DESC/ASC
	 * @param array $fields 返回字段 默认返回部分字段
	 * @return array 返回结果
	 * 
	 */
	public function listResouce($login, $condition, $pageindex = 1, $pageSize = 10, $orderBy = "dateline", $sort = "DESC", $fields = array()){
		if(!$login){
			return FALSE;
		}
		empty($fields) && $fields = array(
				"res.id",
				"res.rid",
				"res.title",
				"res.keywords",
// 				"res.uid",
				"res.username",
				"res.uploaddateline",
				"res.suffix",
				"res.type1",
				"res.type2",
				"res.restype",
				"res.downloadtimes",
				"res.negationtimes",
				"res.praiserate",
				"res.description",
				"res.grade",
				"res.subject",
				"res.size",
				"res.creator",
				"res.product_id",
				"res_opr.dateline",
				"res_opr.operationtype",
				"res_opr.login_name"
		);
		switch(strtoupper($sort)){
			case "DESC" :
				$sort = "DESC";
				break;
			default :
				$sort = "ASC";
				break;
		}
		switch(strtolower($orderBy)){
			case "dateline" :
				$order = "res_opr.dateline ".$sort;
				break;
			case "uploddateline" :
				$order = "res.uploddateline ".$sort;
				break;
			case "downloadtimes" :
				$order = "res.uploddateline ".$sort;
				break;
			case "praiserate" :
				$order = "res.praiserate ".$sort;
				break;
			case "size" :
				$order = "res.size ".$sort;
				break;
			default :
				$order = "res_opr.dateline ".$sort;
				break;
		}
		$where = array();
		foreach($condition AS $k=>$v){
			switch($k){
				case "restype":
					if(is_string($v) && $v){
						$where['res.restype'] = $v;
					}elseif(is_array($v) && $v){
						$tmp = array();
						foreach ($v AS $_k=>$_v){
							$tmp[$_k] = "'".$_v."'";
						}
						$where['res.restype'] = array("IN", $tmp);
					}
					break;
				case "keywords":
					if(!$v){
						break;
					}
					$_where['res.title'] = array('LIKE','%'.$v.'%');
					$_where['res.keywords'] = array('LIKE','%'.$v.'%');
					$_where['res.description'] = array('LIKE','%'.$v.'%');
					$_where['_logic'] = 'OR';
					$where['_complex'] = $_where;
					break;
				case "optype":
					if(is_integer($v) && $v){
						$where['res_opr.operationtype'] = $v;
					}elseif(is_array($v) && $v){
						$where['res_opr.operationtype'] = array("IN", $v);
					}
					break;
				case "suffix":
					if(is_string($v) && $v){
						$where['res.suffix'] = $v;
					}elseif(is_array($v) && $v){
						$tmp = array();
						foreach ($v AS $_k=>$_v){
							$tmp[$_k] = "'".$_v."'";
						}
						$where['res.suffix'] = array("IN", $tmp);
					}
					break;
				case "id":
				case "rid":
// 				case "uid":
				case "username":
				case "type1":
				case "type2":
				case "subject":
				case "grade":
				case "negationtimes":
				case "downloadtimes":
				case "praiserate":
				case "product_id":
				case "size":
				case "creator":
					(is_string($k) || is_integer($k)) && $where["res.$k"] = $v;
                    break;
				default:
					break;
			}
		}
		$where = array_merge($where, array("res_opr.login_name"=>$login, "res.is_del"=>0));
		$pageindex = intval($pageindex);
		if(!$pageSize || $pageindex <= 0){
			$pageindex = 1;
		} 
		if(!$pageSize || $pageSize <=0 || $pageSize>=100){
			$pageSize = 10;
		}
		$start = ($pageindex - 1) * $pageSize;
		$return =  $this->table($this->tablePrefix.''.$this->tableName.' res_opr LEFT JOIN '.$this->tablePrefix.'resource res ON res_opr.resource_id=res.id')
					->where($where)
					->order($order)
					->field($fields)
					->limit("$start,$pageSize")
					->findAll();
		return $return;
	}
	
	/**
	 * 返回符合条件的结果数
	 * @param string $login 用户登录名
	 * @param array $condition 查询条件
	 */
	public function getCount($login, $condition){
		if(!$login){
			return FALSE;
		}
		$where = array();
		foreach($condition AS $k=>$v){
			switch($k){
				case "restype":
					if(is_string($v) && $v){
						$where['res.restype'] = $v;
					}elseif(is_array($v)){
						$tmp = array();
						foreach ($v AS $_k=>$_v){
							$tmp[$_k] = "'".$_v."'";
						}
						$where['res.restype'] = array("IN", $tmp);
					}
					break;
				case "keywords":
					if(!$v){
						break;
					}
					$_where['res.title'] = array('LIKE','%'.$v.'%');
					$_where['res.keywords'] = array('LIKE','%'.$v.'%');
					$_where['res.description'] = array('LIKE','%'.$v.'%');
					$_where['_logic'] = 'OR';
					$where['_complex'] = $_where;
					break;
				case "optype":
					if(is_integer($v) && $v){
						$where['res_opr.operationtype'] = $v;
					}elseif(is_array($v)){
						$where['res_opr.operationtype'] = array("IN", $v);
					}
					break;
				case "suffix":
					if(is_string($v) && $v){
						$where['res.suffix'] = $v;
					}elseif(is_array($v)){
						$tmp = array();
						foreach ($v AS $_k=>$_v){
							$tmp[$_k] = "'".$_v."'";
						}
						$where['res.suffix'] = array("IN", $tmp);
					}
					break;
				case "id":
				case "rid":
// 				case "uid":
				case "username":
				case "type1":
				case "type2":
				case "subject":
				case "grade":
				case "negationtimes":
				case "downloadtimes":
				case "praiserate":
				case "product_id":
				case "size":
				case "creator":
					(is_string($k) || is_integer($k)) && $where["res.$k"] = $v;
                    break;
				default:
					break;
			}
		}
		$where = array_merge($where, array("res_opr.login_name"=>$login, "res.is_del"=>0));
		return $this->table($this->tablePrefix.''.$this->tableName.' res_opr LEFT JOIN '.$this->tablePrefix.'resource res ON res_opr.resource_id=res.id')
					->where($where)
					->count();
	}

	/**
	 * 返回符合条件的名师工作室资源
	 * yangli4
	 * $sql:查询语句
	 */
	public function getMSGroupRes($groupID,$conditions,$orderby, $sort, $start = 0, $limit = 10 ){
		if(!isset($groupID)){
			return false;
		}
		$cs =array();
		$cs2 =null;
		$cs[]="res_opr.`gid` = ".$groupID;
		if($conditions){
			foreach ($conditions as $key =>$value){
				if($value){
					if($key == 'keywords'){
						if(trim($value)){
							$cs2 = array();
							$cs2[]= "res.`keywords` like '%".$value."%'";
							$cs2[]= "res.`title` like '%".$value."%'";
							$cs2[]= "res.`description` like '%".$value."%'";
						}
					}
                    else if($key == 'suffix'){
					   $cs[] = "res.`suffix` in(".implode(',',$value).')';
					}else if($key == 'operationtype'){
					   $cs[]= 'res_opr.'.$key.'='.$value;
					}elseif(is_integer($value)){
						$cs[]= 'res.'.$key.'='.$value;
					}else{
						$cs[]= 'res.'.$key."='".$value."'";
					}
				}
			}
		}
		$res = $this->table($this->tablePrefix.''.$this->tableName.' res_opr,'.$this->tablePrefix.'resource res')
					->where('res.id=res_opr.resource_id AND '.implode(' AND ', $cs).($cs2 ? ' AND ('.implode(' OR ', $cs2).')':''))
					->order($orderby.' '.$sort)
					->limit($start.",".$limit)
					->select();   
		if(empty($res)){
			return false;
		} else {
			return $res;
		}
	}

	/**
	 * 返回符合条件的名师工作室资源数量
	 * yangli4
	 * $sql:查询语句
	 */
	public function getMSGroupResCount($groupID,$conditions){
		if(!isset($groupID)){
			return false;
		}
		$cs =array();
		$cs2 =null;
		$cs[]="res_opr.`gid` = ".$groupID;
		if($conditions){
			foreach ($conditions as $key =>$value){
				if($value){
					if($key == 'keywords'){
						if(trim($value)){
							$cs2 = array();
							$cs2[]= "res.`keywords` like '%".$value."%'";
							$cs2[]= "res.`title` like '%".$value."%'";
							$cs2[]= "res.`description` like '%".$value."%'";
						}
					}else if($key == 'suffix'){
					   $cs[] = "res.`suffix` in(".implode(',',$value).')';
					}					
                    else if($key == 'operationtype'){
					   $cs[]= 'res_opr.'.$key.'='.$value;
					}
					elseif(is_integer($value)){
						$cs[]= 'res.'.$key.'='.$value;
					}else{
						$cs[]= 'res.'.$key."='".$value."'";
					}
				}
			}
		}
		$res = $this->table($this->tablePrefix.''.$this->tableName.' res_opr,'.$this->tablePrefix.'resource res')
					->where('res.id=res_opr.resource_id AND '.implode(' AND ', $cs).($cs2 ? ' AND ('.implode(' OR ', $cs2).')':''))
					->count();        
		return intval($res);
	}

	/**
	 * 返回符合条件的名师工作室资源
	 * yangli4
	 * $sql:查询语句
	 */
	public function getPageMSGroupRes($groupID,$conditions,$orderby, $sort, $pageSize = 10){
		if(!isset($groupID)){
			return false;
		}
		$cs =array();
		$cs2 =null;
		$cs[]="res_opr.`gid` = ".$groupID;
		if($conditions){
			foreach ($conditions as $key =>$value){
				if($value){
					if($key == 'keywords'){
						if(trim($value)){
							$cs2 = array();
							$cs2[]= "res.`keywords` like '%".$value."%'";
							$cs2[]= "res.`title` like '%".$value."%'";
							$cs2[]= "res.`description` like '%".$value."%'";
						}
					}
                    else if($key == 'suffix'){
					   $cs[] = "res.`suffix` in(".implode(',',$value).')';
					}else if($key == 'operationtype'){
					   $cs[]= 'res_opr.'.$key.'='.$value;
					}elseif(is_integer($value)){
						$cs[]= 'res.'.$key.'='.$value;
					}else{
						$cs[]= 'res.'.$key."='".$value."'";
					}
				}
			}
		}
		$res = $this->table($this->tablePrefix.''.$this->tableName.' res_opr,'.$this->tablePrefix.'resource res')
					->where('res.id=res_opr.resource_id AND '.implode(' AND ', $cs).($cs2 ? ' AND ('.implode(' OR ', $cs2).')':''))
					->order($orderby.' '.$sort)
					->findPage($pageSize);   
		if(empty($res)){
			return false;
		} else {
			return $res;
		}
	}



}
