<?php
/**
 * AgreeModel
 * @author xypan
 */
class AgreeModel extends Model{
	
	// 表名
	protected $tableName = 'onlineanswer_agree';
	
	/**
	 * 增加赞并发送系统消息(根据FeedDiggModel的addDigg进行的修改)
	 * @param int $ansid 回答id
	 * @param int $mid 用户id
	 * @param int 数据库执行插入操作的结果
	 */
	public function addAgree($ansid,$mid){
		
		// 把变量存入数组
		$data = array();
		$data ['ansid'] = $ansid;
		$data ['uid'] = $mid;
		
		// 判断是否已经赞过
		$isExit = $this->where($data)->getField ( 'id' );
		
		if ($isExit) {
			return 0;
		}
		
		// 点赞时间
		$data ['ctime'] = time ();
		
		// 插入数据库
		$res = $this->add ( $data );
		
		if($res){
			
			// 获取提问者信息
			$noticer = $this->table($this->tablePrefix."onlineanswer_answer a,".$this->tablePrefix."onlineanswer_question q")
			->where("a.ansid=$ansid and a.qid = q.qid")->field("a.uid,q.qid,a.content")->find();
			// 发送消息通知提问者,自己赞自己的问题不发消息 sjzhao
			$mid != $noticer['uid'] && addMessage($mid, $noticer['uid'], '',$noticer['content'],$noticer['qid'], 'agreeAnswer');
			
			// 在回答记录中的赞同数上加1
			model('Answer')->where('ansid='.$ansid)->setInc('agree_count');
			
			// 记录行为
			D('Behavior')->recordBehavior(array('qid'=>$noticer['qid'],'uid'=>$mid));
		}
		
		return $res;
	}
	
	/**
	 * 判断对于回答用户是否已经赞同
	 * @param int|array $ansids 所有的回答id
	 * @param int $uid 当前登录用户id
	 * @return array 当前用户是否已经赞过某回答
	 */
	public function checkIsAgreed($ansids,$uid){
		
		// 把非数组转换成数组
		if (! is_array ( $ansids ))
			$ansids = array($ansids);
		
		// 过滤掉错误的值
		$ansids = array_filter($ansids);
		
		// 查询条件
		$map ['ansid'] = array (
				'in',
				$ansids
		);
		$map ['uid'] = $uid;
		
		// 查出结果数组
		$list = $this->where($map)->field ('ansid')->findAll();
		
		// 新建一个数组
		$res = array();
		foreach ( $list as $v ) {
			$res [$v ['ansid']] = 1;
		}
		
		return $res;
	}
}
?>