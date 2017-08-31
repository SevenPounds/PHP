<?php
class QuestionModel extends Model{

	// 表名
	protected $tableName = 'onlineanswer_question';
	
	/**
	 * 把创建的问题存入数据库
	 * @param array $data 问题信息
     * @param array $gids 发布到名师工作室的id数组
	 */
	public function insertQuestion($data,$gids){
		// 创建时间
		$data['ctime'] = time();
		// 浏览次数初始化为0
		$data['viewcount'] = 0;
		// 回答人数初始化为0
		$data['answer_count'] = 0;
		// 问题状态初始化为未解答状态
		$data['status'] = 0;
		//问题删除状态初始化为0
		$data['isDeleted'] = 0;
		// 存入数据库
		$res = $this->add($data);

		if(isset($gids)){
			D('MSGroupTeachingApp','msgroup')->addTeachingAppInfo($gids,$res,'onlineanswer_question');
		}
		return $res;
	}

	/**
	 * 获取指定条数的问题列表
	 * @param int $limit 取前多少条记录
	 * @return array 指定条数的问题列表
	 * 0：获取问题列表
	 * 1：获取我的问题列表
	 * 2：获取我的参与列表
	 * 3：获取特定条件的问题列表
	 */
	public function getQuestionList($uid = null, $num = 5, $type = 0, $condition=array(),$order="ctime DESC"){
		switch($type){
			case 0:// 获取我的工作室在线答疑
				$map['isDeleted'] = "0";
				$map['to_space'] = 1;
				$map['uid'] = $uid;
				return  $this->where($map)->order('ctime desc')->findpage($num);
				break;
			case 1://获取我的问题
				$condition['isDeleted'] = "0";
				$condition['uid'] = $uid;
				$keyword = $condition['keyword'];
				unset($condition['keyword']);
				if(!empty($keyword)){	
						$temp = array();				
						$temp['title'] = array('like','%'.$keyword.'%');
						$temp['content'] = array('like','%'.$keyword.'%');
						$temp['_logic'] = 'OR';
						$condition['_complex'] = $temp;
				}
				return $this->where($condition)->order('ctime desc')->findpage($num);
				break;
			case 2://获取我参与的问题
				$condition['isDeleted'] = "0";
				$map=array();
				foreach ($condition as $key => $value) {
					if($key!='keyword'){
						$map['q.'.$key] = $value;
					}
					else{
						$temp = array();
						$temp['q.title'] = array('like','%'.$value.'%');
						$temp['q.content'] = array('like','%'.$value.'%');
						$temp['_logic'] = 'OR';
						$map['_complex'] = $temp;
					}
				}
				$map['q.uid']=array('neq',$uid);
				$map['b.uid']=$uid;

				$join=' LEFT JOIN '.$this->tablePrefix.'onlineanswer_behavior b ON b.qid = q.qid';
				$tables = $this->tablePrefix.$this->tableName.' q ';
				$fields = array('q.*');
				return $this->field($fields)->table($tables)->where($map)->join($join)->order('b.ctime desc')->findPage($num);

				break;
			case 3://根据条件查询
				$condition['isDeleted'] = "0";
				$keyword = $condition['keyword'];
				unset($condition['keyword']);
				if(empty($condition['tagid'])){//不含标签查询
					if(!empty($keyword)){	
							$temp = array();				
							$temp['title'] = array('like','%'.$keyword.'%');
							$temp['content'] = array('like','%'.$keyword.'%');
							$temp['_logic'] = 'OR';
							$condition['_complex'] = $temp;
					}
					$join=' LEFT JOIN '.$this->tablePrefix.'user us ON q.uid = us.uid';
					$tables = $this->tablePrefix.$this->tableName.' q ';
					$fields = array('q.*','us.uname');
					return $this->field($fields)->table($tables)->where($condition)->join($join)->order($order)->findpage($num);
				}
				else{//含标签查询
					$map = array();
					$map['at.tag_id']=$condition['tagid'];
					unset($condition['tagid']);
					foreach ($condition as $key => $value) {
						if($key!='keword'){
							$map['q.'.$key] = $value;
						}
						else{
							if($value==''){
								break;
							}
							$temp = array();				
							$temp['q.title'] = array('like','%'.$value.'%');
							$temp['q.content'] = array('like','%'.$value.'%');
							$temp['_logic'] = 'OR';
							$map['_complex'] = $temp;
						}
					}
					$map['at.app']='onlineanswer';
					$map['at.table']='onlineanswer';

					$join=' LEFT JOIN '.$this->tablePrefix.'app_tag at ON q.qid = at.row_id';
					$join .= ' LEFT JOIN '.$this->tablePrefix.'user us ON q.uid = us.uid';
					$tables = $this->tablePrefix.$this->tableName.' q ';
					$fields = array('q.*','us.uname');
					return $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->findPage($num);
				}
				break;
		}
	}

	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “在线答疑”
	 */
	public function getQuestions($start,$limit,$order){
		return $this->order($order)->limit($start.",".$limit)->select();		
	}


	/**
	 * 根据条件查询问题个数
	 * @param int $uid
	 */
	public function getQuestionCount($uid){
		$number=$this->where("uid='$uid' AND isDeleted=0")->count();
		return $number;
	} 
	
	/**
	 * 查出问题的提出者和当前状态
	 * @param int $qid 问题id
	 * @return array 问题的提出者和当前状态
	 */
	public function questionStatusAndUid($qid){
		return $this->where("qid=$qid")->field("uid,status")->find();
	}
	
	/**
	 * 更新问题的浏览次数
	 * @param int $qid 问题id
	 */
	public function updateQuestionViewcount($qid){
		// 浏览次数加1
		$this->where("qid=$qid")->setInc('viewcount'); 
	}
	
	/**
	 * 更新回答人数
	 * @param int $qid 问题id
	 */
	public function upadteQuestionAnswerCount($qid){
		// 回答人数加1
		$this->where("qid=$qid")->setInc('answer_count');
	}
	
	/**
	 * 修改问题内容
	 * @param array $data 问题信息
     * @param array $gids 发布到名师工作室的id数组
	 */ 
	public function alterQuestionContent($data,$gids){
		$qid = $data['qid'];
		$uid = $data['uid'];

		$update = array('title' => $data['title'], 'content' => $data['content'], 'grade' => $data['grade'], 'subject' => $data['subject'],'to_space' => $data['to_space'],'title_origin'=>$data['title_origin'],'content_origin'=>$data['content_origin']);
		$map['qid'] = $qid;
		$map['uid'] = $uid;
		$res = $this->where($map)->save($update);

		D('MSGroupTeachingApp','msgroup')->updateTeachingAppInfo($gids,$qid,'onlineanswer_question');
		return $res;
	}
	
	/**
	 * 把问题的状态改成1(已解答)
	 * @param int $qid 问题id
	 */
	public function alterQuestionStatus($qid){
		$this->where("qid=$qid")->setField('status',1);
	}
	
	/**
	 * 问题详细
	 * @param int $qid 问题编号
	 * @return array 问题详细
	 */
	public function questionDetail($qid){
		return $this->table($this->tablePrefix."user u,".$this->tablePrefix."onlineanswer_question q")
				->where("q.qid = $qid and u.uid = q.uid")
				->find();
	}
	
	/**
	 * 我参与的问题列表
	 * @param int $page 请求页
	 * @param int $num 每页记录数
	 * @param int $mid 用户id
	 * @return array 我参与的问题列表
	 */
	public function getMyParticipationQuestions($page,$num,$mid){
		return $this->table($this->tablePrefix."onlineanswer_behavior b,".$this->tablePrefix."onlineanswer_question q")
			   ->where("b.uid = $mid and b.qid = q.qid and q.isDeleted=0 and q.uid!=$mid")->order('b.ctime desc')->page("$page,$num")->select();
	}
	
	/**
	 * 删除指定的问题
	 * @param $id 问题id
	 * @return int|boolean 如果查询错误或者数据非法返回false 如果更新成功返回影响的记录数
	 */
	public function deleteQuestion($qid,$uid){
		return $this->where("qid=$qid and uid=$uid")->setField('isDeleted',1);
	}
	
	/**
	 * 发表在线答疑动态
	 * @param int $uid 用户id
	 * @param int $research_id 在线答疑地址
	 * @param varchar $title 在线答疑标题
	 * @param text $content 在线答疑内容
	 * @param array $gids 名师工作室ID
	 * @author yxxing
	 */
	public function syncToFeed($uid, $research_id, $title, $content, $gids) {
		$d['content'] = '';
		$d['source_url'] = " ".U('onlineanswer/Index/detail', array("qid"=>$research_id));
		//发表动态
		$d['body'] = '我发起了答疑话题【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
		$feed = model('Feed')->put($uid, 'onlineanswer', 'onlineanswer', $d);
		if(isset($gids)){
			foreach ($gids as $gid) {
				if(!empty($gid)){
					$d['gid'] = $gid;
					$d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 我发起了答疑话题【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
					$feed = model('Feed')->put($uid, 'msgroup', "msgroup", $d);
				}
			}
		}
		return 1;
	}
	/**
	 * 获取问题删除状态
	 * @param int $qid
	 * @author ylzhao
	 */
	public function getIsDelete($qid){
		return $this->where("qid=$qid")->field('isDeleted')->find();
	}
	
	/**
	 * 根据isHot,评论数，浏览量和时间排序最火在线答疑
	 * return array
	 */
	public function geAppHotQuestion(){
		//在线答疑列表
		$hot_list =$this->field('qid,uid,content,title,answer_count')->where(array('isHot'=>1,'isDeleted'=>0))->order('answer_count DESC, viewcount DESC, ctime DESC ')->limit("0,5")->findAll();
		$result =array();
		foreach ($hot_list as $key){
			$map=array();
			$map['qid']=$key['qid'];
			//在线答疑评论
			$comment =D('Answer','onlineanswer')->field('uid,content,agree_count')->where($map)->order('agree_count DESC, comment_count DESC, ctime DESC')->find();
			//在线答疑发表用户信息
			$blogUser =D('User')->getUserInfo($key['uid']);
			if(!empty($comment)){
				//精彩评论发表用户信息
				$commentUser =D('User')->getUserInfo($comment['uid']);
			}
			$blogInfo['hotUser']=$blogUser;
			$blogInfo['commentUser']=$commentUser;
			$blogInfo['id']=$key['qid'];
			$blogInfo['content'] =h($key['content']);
			$blogInfo['title']=h($key['title'],all);
			$blogInfo['discuss_count']=$key['answer_count'];
			$blogInfo['comment_content']=parse_html($comment['content']);
			$blogInfo['comment_count']=$comment['agree_count'];
			$blogInfo['source_url']=U('onlineanswer/Index/detail',array('qid'=>$key['qid']));
			unset($commentUser);
			unset($comment);
			array_push($result,$blogInfo);
		}
	
		return $result;
	}
	/**
	 * 根据条件获取在线答疑列表
	 * 注：供后台使用
	 * @author ylzhao
	 */
	public function getQuestionsByCondition($conditions, $page=1, $limit=10, $order){
		$map = array();
		if(isset($conditions['isHot'])){
			$map['isHot'] = $conditions['isHot'];
		}
		if(isset($conditions['title'])){
			$map['title'] = array('like', "%".$conditions['title']."%");
		}
		$fields = 'q.*,u.`uname`';
		$tables = $this->tablePrefix.$this->tableName.' q ';
		$join = 'LEFT JOIN '.$this->tablePrefix.'user u ON q.`uid` = u.`uid`';
		$order = empty($order) ? "ctime DESC" : $order;
		$map['q.`isDeleted`'] = 0;
		$start = ($page - 1) * $limit;
		$result['data'] = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->limit("$start, $limit")->select();
		$result['count'] = $total = $this->table($tables)->where($map)->join($join)->count();
		return $result;
	
	}
	/**
	 * doIsHot
	 * 设置推荐
	 * @param array $map
	 * @param string $act
	 * @author ylzhao
	 */
	public function doIsHot( $map,$act ) {
		if( empty($map) ) {
			throw new ThinkException( "不允许空条件操作数据库" );
		}
		switch( $act ) {
			case "recommend":   //推荐
				$result = $this->where($map)->setField("isHot",1);
				break;
			case "togreat":   //精华
				$result = $this->where($map)->setField("isHot",2);
				break;
			case "cancel":   //取消
				$result = $this->where($map)->setField("isHot",0);
				break;
		}
		return $result;
	}
}
?>
