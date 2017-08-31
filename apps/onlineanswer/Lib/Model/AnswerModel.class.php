<?php
class AnswerModel extends Model{
	
	// 表名
	protected $tableName = 'onlineanswer_answer';
	
	/**
	 * 查询出问题的回答列表
	 * @param int $qid 问题编号
	 * @return array 回答列表
	 */
	public function answers($qid){
		
		return $this->where("qid=$qid")->order('ctime desc')->findPage(10);
	}

	/**
	 * 查询某用户对某问题的回答列表
	 * yangli4
	 * @param int $qid 问题编号
	 * @param int $uid 用户ID
	 *
	 * @return array 回答列表
	 */
	public function getUserAnswers($qid, $uid, $page = 1, $limit = 20, $order = 'ctime desc'){
		$data = array();
		$user = model('User')->getUserInfo($uid);
		$map = array('uid' => $uid,'qid' => $qid);
		return $this->where($map)->order($order)->findPage($limit);
	}

	/**
	 *	查询某个问题所有回答者信息
	 */
	public function getQuestionAnswerUsers($qid, $num = 10, $order = 'ctime desc'){
		if(empty($qid)){
			return null;
		}
		else{
			return $this->where(array('qid' => $qid))->order($order)->findpage($num);
		}
	}

	/*
	 * 删除用户回答
	 * yangli4
	 * @param int $ansid 回答的id
	 */
	public function deleteAnswer($ansid){
		if(isset($ansid)){
			$comment_map = array('table' => 'onlineanswer_answer',	'row_id'=>$ansid);
			//删除评论
			D('Comment')->where($comment_map)->delete();

			//问题回答数减一
			$qid = D('Answer')->where(array('ansid' => $ansid))->getField('qid');
			D('Question')->where(array('qid'=>$qid))->setDec('answer_count');
			return $this->where(array('ansid' =>$ansid))->delete();
		}
		else{
			return -1;
		}
	}
	
	/**
	 * 把问题的答案存入数据库
	 * @param array $data 答案信息
	 * @return int|boolean 如果数据非法或者查询错误则返回false 如果是自增主键 则返回主键值，否则返回1
	 */
	public function insertAnswer($data){
		
		// 创建时间
		$data['ctime'] = time();
		
		//评论次数初始化为0
		$data['comment_count'] = 0;
		
		// 答案初始化未被采纳
		$data['is_best'] = 0;
		
		// 赞同数初始化为0
		$data['agree_count'] = 0;
		
		// 存入数据库
		$res =  $this->add($data);
		
		if($res){
				
			// 获取提问者id
			$noticedid = model('Question')->where($data['qid']."= qid")->field("uid,title")->find();
				
			// 发送消息通知提问者
			$noticedid['uid']!=$data['uid']&&addMessage($data['uid'], $noticedid['uid'],$noticedid['title'], $data['content'],$data['qid'],'answer');
				
			// 实例化QuestionModel
			$Question = D('Question');
			
			// 回答人数加1
			$Question->upadteQuestionAnswerCount($data['qid']);
			
			// 记录行为
			D('Behavior')->recordBehavior(array('qid'=>$data['qid'],'uid'=>$data['uid']));
		}
		
		return $res;
	}
	
	/**
	 * 修改回答内容
	 * @param array $data 要修改的回答信息
	 * @return int|boolean 如果查询错误返回false 如果更新成功返回影响的记录数
	 */
	public function alterAnswerContent($data){
		
		$ansid = $data['ansid'];
		
		$uid = $data['uid'];
		
		$res = $this->where("ansid=$ansid and uid = $uid")->setField('content',$data['content']);
		
		if($res !== false){
			// 记录行为
			D('Behavior')->recordBehavior(array('qid'=>$data['qid'],'uid'=>$uid));
		}
		
		return $res;
	}
	
	/**
	 * 修改回答的is_best(1:表示已采纳)
	 * @param int $ansid 回答id
	 * @return int|boolean 如果查询错误返回false 如果更新成功返回影响的记录数
	 */
	public function alterAnswerBset($ansid){
		return $this->where("ansid=$ansid")->setField('is_best',1);
	}
	
	/**
	 * 得到评论的数量并将评论数插入到数据库中
	 */
	public function getCommentCount(){
		$sql = "SELECT a.row_id,COUNT(a.comment_id) as comment_count FROM ts_comment a WHERE `table`='onlineanswer_answer' AND `is_del`=0 GROUP BY a.row_id";
		$commentcount = $this->query($sql);
		for($i=0;$i<count($commentcount);$i++){
			$data['ansid'] = $commentcount[$i]['row_id'];
			$data['comment_count'] = $commentcount[$i]['comment_count'];
			$answer = $this->save($data);
		}
	}
	
	/**
	 * 获取答疑明星
	 */
	public function getAnswerStar($limit = 5){
		//获取用户的采纳数
		$sql1 = "SELECT uid,COUNT(*) AS best_count 
				FROM `".$this->tablePrefix.$this->tableName."` 
				WHERE is_best=1 
				GROUP BY uid 
				ORDER BY best_count DESC
				LIMIT 0,".$limit;
		$users = $this->query($sql1);
		foreach($users as $key=>$value){
			if($key == 0){
				$user_ids = $value['uid'];
			}else{
				$user_ids .= ",".$value['uid'];
			}
			$best[$key] = $value['best_count'];
		}
		//获取用户的回答数
		$sql2 = "SELECT uid,COUNT(*) AS ans_count 
				FROM `".$this->tablePrefix.$this->tableName."` 
				WHERE uid IN( ".$user_ids." )
				GROUP BY uid 
				LIMIT 0,".$limit;
		$users_ans = $this->query($sql2);
		foreach($users as $key=>&$value){
			foreach($users_ans as $v){
				if($v['uid'] == $value['uid']){
					$value['ans_count'] = $v['ans_count'];
				}
			}
			$answer[$key] = $value['ans_count'];
		}
		//获取用户的赞数
		$sql3 = "SELECT uid,SUM(agree_count) AS agree_count
				FROM `".$this->tablePrefix.$this->tableName."`
				WHERE uid IN( ".$user_ids." )
				GROUP BY uid
				LIMIT 0,".$limit;
		$users_agree = $this->query($sql3);
		foreach($users as $key=>&$value){
			foreach($users_agree as $v){
				if($v['uid'] == $value['uid']){
					$value['agree_count'] = $v['agree_count'];
				}
			}
			$agree[$key] = $value['agree_count'];
		}
		array_multisort($best, SORT_DESC, $answer, SORT_DESC, $agree, SORT_DESC, $users);
		return $users;
	}
	
	/**
	 * 统计回答数、赞数、采纳数
	 * @param unknown $uid
	 * @return Ambigous <mixed, boolean, multitype:, multitype:multitype: >
	 */
	public function getAnsCount($uid){
		$userCount = array();
		$sql1 = "SELECT COUNT(*) AS best_count
				FROM `".$this->tablePrefix.$this->tableName."`
				WHERE is_best=1 AND uid=".$uid;
		$userCount['best_count'] = $this->query($sql1)[0]['best_count'];
		$sql2 = "SELECT COUNT(*) AS ans_count 
				FROM `".$this->tablePrefix.$this->tableName."`
				WHERE uid=".$uid;
		$userCount['ans_count'] = $this->query($sql2)[0]['ans_count'];
		$sql3 = "SELECT SUM(agree_count) AS agree_count
				FROM `".$this->tablePrefix.$this->tableName."`
				WHERE uid=".$uid;
		$userCount['agree_count'] = $this->query($sql3)[0]['agree_count'];
		return $userCount;
	}
	
	/**
	 * 获取最后一条回答
	 * @param int $uid
	 */
	public function getLastAnswer($uid){
		$sql = "SELECT a.*,q.title FROM `".$this->tablePrefix.$this->tableName."` a
				LEFT JOIN `".$this->tablePrefix."onlineanswer_question` q ON q.qid=a.qid AND q.isDeleted=0
				WHERE a.uid=".$uid."
				LIMIT 0,1" ;
		$answer = $this->query($sql);
		return $answer[0];
	}
	
	/**
	 * 获取精品回答的列表
	 * @param array $condition 查询条件
	 * @param int $num 查询数量
	 * @return array 查询结果集
	 * @author ylzhao
	 */
	public function getExcellentAnswerList($condition, $num, $order = "agree_count DESC,ctime DESC"){
		$condition['agree_count'] = array('gt',0);
		$result = $this->where($condition)->order($order)->limit($num)->select();
		foreach ($result as &$r){
			$r['user_info'] = model ( 'User' )->getUserInfoForSearch ( $r ['uid'],'uid,uname');
			$r['id'] = $r['ansid'];
			$r['content'] = parse_html($r['content']);
		}
		return $result;
	}
}
?>