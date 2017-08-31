<?php
/**
 * 用户参与的问题记录
 * @author xypan
 * @version TS3.0
 */
class BehaviorModel extends Model{
	
	// 表名
	protected $tableName = 'onlineanswer_behavior';
	
	/**
	 *记录用户的行为(对于一个问题若有行为则更新,无则新增)
	 */
	public function recordBehavior($data){
		
		// 找出可能存在的行为记录id
		$ids = $this->where($data)->field('id')->find();
		
		$id = $ids['id'];
		
		// 操作的时间
		$data['ctime'] = time();
		
		if(empty($id)){
			$this->add($data);
		}else{
			$res = $this->where("id=$id")->save($data);
		}
	}
	
	/**
	 * 我参与的问题总数
	 * @param int $uid 用户id
	 * @return int 我参与的问题总数
	 */
	public function getMyParticipationCounts($uid){

		return $this->where("uid=$uid")->count();
	}

	/**
	 *	查询某个问题参与者信息
	 */
	public function getQuestionParticipants($qid, $num = 10, $order = 'ctime desc'){
		if(empty($qid)){
			return null;
		}
		$map = array('ob.qid' => $qid );
		$join=' LEFT JOIN '.$this->tablePrefix.'user u ON ob.uid = u.uid';
		$tables = $this->tablePrefix.$this->tableName.' ob ';
		$fields = array('u.*');
		return $this->field($fields)->table($tables)->where($map)->join($join)->order('ob.'.$order)->findPage($num);
	}
}
?>