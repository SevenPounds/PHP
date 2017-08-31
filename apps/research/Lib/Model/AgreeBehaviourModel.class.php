<?php
class AgreeBehaviourModel extends Model{
    // 表名
    protected $tableName = 'research_agree';
    
    protected $pk ='id';

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 添加网络调研一级回复赞行为记录
     *
     * @param unknown $post_id  回复id
     * @param unknown $uid  回复用户uid
     * @param unknown $ctime  赞行为时间
     */
    public function addBehaviour($post_id,$uid){
    
    	//数据检测
    	if(empty($post_id)||empty($uid))
    		return false;
    	$postModel =D('Post','reserach');
    	$post=$postModel->field('post_userid,content,id,research_id')->where(array('id'=>$post_id,'is_del'=>0))->find();
    	if(empty($post['id'])){
    		return false;
    	}
    	$data['post_id'] =$post_id;
    	$data['uid'] =$uid;
    	$data['ctime'] =time();
    	$s =$this->add($data);
    	if($s){
    		//增加赞数
    		$pk= $postModel->getPk();
    		$postModel->setInc('agree_count',"`{$pk}`={$post_id}", 1 );
    		//如果该评论不是本人，则发送系统消息
    		if(($uid!=$post['post_userid'])){
    			addResearchMessage($uid,$post['post_userid'],'',$post['content'],$post['research_id'],'research_comment_digg');
    		}
    	}
    	return $s;
    }
    
    /**
     * 根据用户uid获取一组回复的是否已赞
     * @param unknown $postArr
     * @param unknown $uid
     */
    public function getIsBehaviourUser($postArr,$uid){
    
    	if(count($postArr)==0||empty($postArr))
    		return array();
    	$retrun =array();
    	foreach ($postArr as $value){
    		$return[$value] =0;
    	}
    	if(empty($uid)){
    		return $return;
    	}
    	$map['post_id'] = array('IN',$postArr);
    	$map['uid'] =$uid;
    	$list = $this->field('post_id')->where($map)->order('post_id')->select();
    	foreach($list as $val){
    		$return[$val['post_id']] =  1;
    	}
    	return $return;
    }
   
}
?>
