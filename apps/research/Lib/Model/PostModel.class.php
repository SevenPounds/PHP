<?php
/**
 * 课题|评课的发言Model
 * @author frsun|xypan
 * @version TS3.0
 */
class PostModel extends  Model{
	/**
	 * 表名
	 * @var string 
	 */
	protected  $tableName = 'research_post';
	
	protected $fields =array('id','research_id','post_userid','content','record_id','createtime','is_del','comment_count','agree_count');
	
	protected $pk = 'id';
	
	/**
	 * 对课题研究发言
	 * @param int $research_id
	 * @param int $post_userid
	 * @param string $content
	 * @return Ambigous <mixed, boolean, unknown, string>
	 */
	public function createPostData($research_id, $post_userid, $content,$contentOrigin, $attachIds, $record_id) {
		$data['research_id'] = $research_id;
		$data['post_userid'] = $post_userid;
		$data['content'] = $content;
        $data['content_origin'] = $contentOrigin;
		$data['record_id'] = $record_id;
		$data['createtime'] = time();
		$res = $this->add($data);
// 		//如是新成员发表评论，则将其添加到research_user，同时更新Research中member_count数目
		$research = D('Research')->field('uid,title,id')->where(array('id' => $research_id))->find();
		if(intval($research['uid'])!=$post_userid){
			$count = D('research_user')->where(array('research_id' => $research_id,'member_id'=>$post_userid))->count();
			if($count<=0){
				D('research_user')->add(array('research_id' => $research_id,'member_id'=>$post_userid,'is_new'=>0));
				D('Research')->setInc('member_count','id='.intval($research_id),1);
			}
		}
		
		if($res){
// 			研讨次数加1
			D('Research')->where("id='$research_id'")->setInc('discuss_count');
			
// 			把发言时添加的附件加到数据库的research_attach
			if(!empty($attachIds)){
				$ids = explode('|', $attachIds);
			
				foreach ($ids as $attachId) {
					$data = array();
					$data['app_type'] = 'post';
					$data['app_id'] = $res;
					$data['attach_id'] = $attachId;
					D('research_attach')->add($data);
				}
			}
			if($post_userid !=intval($research['uid'])){
				addResearchMessage($post_userid,$research['uid'],$research['title'],$data['content'],$research['id'],'research_comment');
			}
		
		}
		return $res;
	}
	
	/**
	 * 获取课题研究的所有符合条件的发言信息
	 * @param array $condition 查询条件
	 * @param int $page 请求页
	 * @param int $num 每页记录数
	 * @return array 查询结果集
	 */
	public function getPostByRid($condition,$page,$num){
		$result = $this->where($condition)->order('createtime DESC')->page("$page,$num")->select();
		foreach ($result as &$r){
			$r = array_merge($r,model ( 'User' )->getUserInfoForSearch ( $r ['post_userid'],'uid,uname'));
		}
	    return $result;
	}
	
	/**
	 * 获取用户列表根据课题研究的id
	 * @param int $research_id
	 * @return array
	 */
	public function getUserListByRid($research_id, $page = 1, $limit = 20, $order = 'id desc'){
		$map['research_id'] = $research_id;		
		$members = D('research_user')->where($map)->order($order)->page("$page, $limit")->select();
		foreach ($members as &$m){
			$map['post_userid'] = $m['member_id'];
			$count = D('research_post')->where($map)->field('count(1) as pcount')->find();
		    $m = array_merge($m,$count,model ( 'User' )->getUserInfoForSearch ( $m ['member_id'],'uid,uname'));
		}
		return $members;
	}
	
	/**
	 * 删除评论
	 * @param int $post_id <评论ID>
	 */
	public function deletePostById($post_id, $uid){
		$data = array("is_del"=>1);
		//获取research_id
		$research_post = $this->where(array("id"=>$post_id))->field("research_id")->select();
		$research_id = intval($research_post[0]["research_id"]);
		$ret = $this->where(array("id"=>$post_id, "post_userid"=>$uid))->save($data);
		if($ret){
			$model = new Model();
			$model->execute("update ts_research set discuss_count = discuss_count-1 where id=".intval($research_id));
		}
	}
	
	/**
	 * 获取精彩回复列表
	 * @param array $condition 查询条件
	 * @param int $num 查询数量
	 * @return array 查询结果集
	 * @author tkwang
	 */
	public function getExcellentPostList($condition, $num, $order = "agree_count DESC,createtime ASC"){
		$condition['agree_count'] = array('gt',0);
		!isset($condition['is_del']) && ($condition['is_del'] = 0);
		$result = $this->where($condition)->order($order)->limit($num)->select();
		foreach ($result as &$r){
			$r['user_info'] = model ( 'User' )->getUserInfoForSearch ( $r ['post_userid'],'uid,uname');
			$r['content'] = parse_html($r['content']);
		}
		return $result;
	}
}
?>
