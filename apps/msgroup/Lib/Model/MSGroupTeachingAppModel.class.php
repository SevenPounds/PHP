<?php
/**
 * @package msgroup\Lib\Model
 * @author yuliu2@iflytek.com
 * 名师工作室Model
 */
class MSGroupTeachingAppModel extends Model {
	
	protected $tableName = 'msgroup_teachingapp';
	protected $research_tableName = 'research';
	protected $pingke_tableName = 'pingke';
	protected $vote_tableName = 'vote';
	protected $online_tableName = 'onlineanswer_question';


	protected $fields =
	 array( 
	 		0=>'id',
	 		1=>'gid',
			2=>'appid',
			3=>'app_type'
		);

	/**
	 * 获取教学研讨信息
	 * @param  int $gid  		名师工作室id
	 * @param  string $appType 	应用类型：'research'://主题讨论
	 * 									  'pingke'://网上评课	
	 * 									  'vote'://网络调研	
	 * 									  'onlineanswer_question'://在线答疑
	 * 
	 */
	public function getTeachingAppInfo($gid,$appType,$limit=5,$order){
		if(!isset($gid) || !isset($appType)){
			return null;
		}
		switch ($appType) {
			case 'research'://主题讨论
				return $this->getResearchInfo($gid,$limit,$order);
				break;
			case 'pingke'://网上评课
				return $this->getPingkeInfo($gid,$limit,$order);
				break;
			case 'vote'://网络调研
				return $this->getVoteInfo($gid,$limit,$order);
				break;
			case 'onlineanswer_question'://在线答疑
				return $this->getOnlineanswerInfo($gid,$limit,$order);
				break;
			default:
				return null;
				break;
		}
	}

	/**
	 * 添加教学研讨信息
	 */
	public function addTeachingAppInfo($gidArr,$appid,$appType){
		if(!isset($gidArr)||!isset($appid)||!isset($appType)){
			return -1;
		}
		else{
			foreach ($gidArr as $gid) {
				if(intval($gid)>0){
					$this->add(array('gid' => $gid,
									'appid' => $appid,
									'app_type' => $appType));
				}
			}
			return 1;
		}
	}

	/**
	 * 更新教学研讨信息
	 */
	public function updateTeachingAppInfo($gidArr,$appid,$appType){
		if(!isset($appid)||!isset($appType)){
			return -1;
		}
		else{
			$map = array('appid' => $appid,'app_type' => $appType);
			$this->where($map)->delete();
			if(!isset($gidArr))	{
				return ;
			}
			return $this->addTeachingAppInfo($gidArr,$appid,$appType);
		}
	}


	/*
	 * 主题讨论
	 */
	private function getResearchInfo($gid,$limit=5,$order='re.`createtime` desc'){
		isset($order) || $order='re.`createtime` desc';
		$map = array('mst.`gid`'=>$gid,'mst.`app_type`' => 'research','re.`is_del`' => 0);
		$join=' LEFT JOIN '.$this->tablePrefix.$this->research_tableName.' re ON mst.`appid` = re.`id`';
		$tables = $this->tablePrefix.$this->tableName.' mst ';
		$fields = array('re.*');
		$res = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->findPage($limit);
		return $res ;
	}

	/*
	 * 网上评课
	 */
	private function getPingkeInfo($gid,$limit=5,$order='pk.`createtime` desc'){
		isset($order) || $order='pk.`createtime` desc';
		$map = array('mst.`gid`'=>$gid,'mst.`app_type`' => 'pingke','pk.`is_del`' => 0);
		$join=' LEFT JOIN '.$this->tablePrefix.$this->pingke_tableName.' pk ON mst.`appid` = pk.`id`';
		$tables = $this->tablePrefix.$this->tableName.' mst ';
		$fields = array('pk.*');
		$res = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->findPage($limit);
		return $res ;
	}
	
	/*
	 * 网络调研
	 */
	private function getVoteInfo($gid,$limit=5,$order='vt.`cTime` desc'){
		isset($order) || $order='vt.`cTime` desc';
		$map = array('mst.`gid`'=>$gid,'mst.`app_type`' => 'vote');
		$join=' LEFT JOIN '.$this->tablePrefix.$this->vote_tableName.' vt ON mst.`appid` = vt.`id`';
		$tables = $this->tablePrefix.$this->tableName.' mst ';
		$fields = array('vt.*');
		$res = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->findPage($limit);
		return $res ;
	}
	
	/*
	 * 在线答疑
	 */
	private function getOnlineanswerInfo($gid,$limit=5,$order='onq.`ctime` desc'){
		isset($order) || $order='onq.`cTime` desc';
		$map = array('mst.`gid`'=>$gid, 'mst.`app_type`' => 'onlineanswer_question','onq.`isDeleted`' => 0);
		$join=' LEFT JOIN '.$this->tablePrefix.$this->online_tableName.' onq ON mst.`appid` = onq.`qid`';
		$tables = $this->tablePrefix.$this->tableName.' mst ';
		$fields = array('onq.*');
		$res = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->findPage($limit);
		return $res ;
	}
}

?>