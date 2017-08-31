<?php

/**
 * 2015/3/6
 * @author tkwang
 * 创建网络调研一级回复Model
 *
 */
class VotePostModel extends BaseModel{
	// 表名
	protected $tableName = 'vote_post';
	//主键
	protected $pk ='id';

	public function _initialize(){
		parent::_initialize();
	}
	
	/**
	 * 根据已唯一条件查询数据
	 * @param unknown $key
	 * @param unknown $value
	 */
	public function getPost($condition,$field=""){
		if(empty($condition)||count($condition)==0){
			return array();
		}
		return $this->field($field)->where($condition)->find();
	}
	/**
	 *  查询回复列表
	 * @param unknown $vote_id 网络调研id
	 */
	public function getPostList($condition,$page=0,$num=10,$order=""){
		if(!isset($condition['is_del'])){
			$condition['is_del'] = 0;
		}
		$result['count']=$this->where($condition)->count('',$this->pk);
		$data=$this->where($condition)->page("$page,$num")->order($order)->select();
		foreach($data as &$v){
			$v['user_info'] =model('User')->getUserInfo($v['uid']);
		}
		$result['data']=$data;
		return $result;
	}
	
	/**
	 * 插入回复
	 * @param unknown $data
	 */
	public function addPost($data){
		$_data['uid'] =$data['uid'];
		$_data['content'] =$data['content'];
        $_data['content_origin'] =$data['content_origin'];
		$_data['vote_id'] =$data['vote_id'];
		$_data['ctime'] = time();
		$id =$this->add($_data);
		//修改评论数和发送系统消息
 		if($id){
 			D('Vote','vote')->setInc('commentCount',array('id'=>$_data['vote_id']),1);
 			$vote=D('Vote','vote')->field('title,uid')->where(array('id'=>$_data['vote_id']))->find();
 			//如果不是本人回复本网络调研则发送系统消息
 			if($_data['uid']!=$vote['uid']){
 				addVoteMessage($_data['uid'],$vote['uid'],$vote['title'],$_data['content'],$_data['vote_id'],'vote_comment');
 			}
 		}
		unset($_data);
		return $id;
	}
	
	
	/**
	 * 删除回复
	 * 逻辑删除
	 * @param unknown $comment_id 评论id
	 */
	public function delPost($comment_id){
		$map['id']=$comment_id;
		$data['is_del'] =1;
		$r= $this->where($map)->save($data);
		if($r){
			$post=$this->getPost(array('id'=>$comment_id),'vote_id');
			D('Vote','vote')->setDec('commentCount',array('id'=>$post['vote_id']),1);
		}
		return $r;
	}
	
	/**
	 * 增加后者减少赞和评论数
	 * @param unknown $post_id   评论id
	 * @param unknown $key       操作字段(agree_count :赞数, comment_count:评论数)
	 * @param number $vlaue      数值
	 * @param string $isInc		  增减(true:增加,false:减少)
	 * @return boolean
	 */
	public function updateValue($post_id ,$key ,$vlaue= 1 ,$isInc= true ){
		if($isInc){
			$this->setInc($key,array('id'=>$post_id),$vlaue);
		}else{
			$this->setDec($key,array('id'=>$post_id),$vlaue);
		}
		return true;
	}
	
	/**
	 * 获取精彩回复.
	 * @param unknown $condition 查询条件
	 * @param string $order 排序顺序
	 * @param number $limit 限制数量
	 * @author tkwang
	 * 
	 */
	public function getExcellentCommentList($condition,$limit =5,$order =' agree_count DESC,ctime ASC '){
		$condition['agree_count'] = array('gt',0);
		$condition['is_del'] =0;
		$result= $this->where($condition)->order($order)->limit("0,$limit")->select();
		foreach ($result as &$r){
			$r['user_info'] = model ( 'User' )->getUserInfoForSearch ( $r ['uid'],'uid,uname');
			$r['content'] = parse_html($r['content']);
		}
		return $result;
	}
}

?>