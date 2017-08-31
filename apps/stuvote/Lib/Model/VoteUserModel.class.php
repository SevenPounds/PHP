<?php
class VoteUserModel extends Model{
	var $tableName = "stuvote_user";

	protected $fields	=   array(
        'id','vote_id','uid','login_name','opts','cTime','is_new','is_invited',
		'_autoInc'	=>	true,
		'_pk'		=>	'id',
	);

    public function _initialize(){
        parent::_initialize();
    }


    /**
     * 浏览投票后，置最新为0
     */
    public function setNotNew($vote_id,$uid){
    	if(empty($uid)||empty($vote_id)){
    		return -1;
    	}
    	else{
    		$mapD = array('uid' =>$uid , 'vote_id'=>$vote_id);
    		return $this->where($mapD)->save(array('is_new' => 0));
    	}
    }

    /**
     * 用户投票，置‘is_new’ 字段为零，表明其已经参与投票。
     */    
    public function vote($data){
    	//参数有误
		if(empty($data['uid'])||empty($data['vote_id'])){
    		return -1;
    	}
    	else{
    		$data['is_new'] = 0;
    		$data['cTime'] = time();
    		$mapD = array('uid' =>$data['uid'] , 'vote_id'=>$data['vote_id']);
    		$cc = $this->where($mapD)->count();
    		if($cc>0){
    			unset($data['uid']);
    			unset($data['vote_id']);

				$this->where($mapD)->save($data);
    		}
    		else{
		   		$this->add($data);
    		}
    		//更新投票人数
    		$userCount = $this->where(array('vote_id' => $data['vote_id']))->count(); 
    		D('Vote')->where(array('id' => $data['vote_id']))->save(array('vote_num' => $userCount));

    		return 1;
    	}
    }

    /**
     * 邀请他人，参与投票
     */
    public function inviteUser($invitees,$vote_id){
    	if(empty($invitees)||empty($vote_id)){
    		return -1;
    	}

        foreach ($invitees as $userid) {
            $this->add(array('uid' => $userid ,
                            'vote_id' => $vote_id ,
                            'cTime' => time() ,
                            'is_new' => 1,
                            'is_invited'=>1));
        }
        return 1;
    }

    /**
     * 统计用户被邀请参与且未浏览的投票数量
     */
    public function getNewCount($uid){
    	if(empty($uid)){
    		return -1;//参数有误
    	}
    	$map = array();
		$map['uid']=$uid;
		$map['is_new']=1;
        return $this->where($map)->count(); 
    }


	/**
	 *获取被邀请投票的用户
	 */
    public function getInvitees($vote_id){
    	if(empty($vote_id)){
    		return null;
    	}
    	else{
    		return $this->where(array('vote_id'=>$vote_id,'is_invited'=>1))->select();
    	}
    }
	
	/**
	 * @author chengcheng3
	 * This is method getVoteUsers
	 * 去除自己发起的网络投票
	 * @param mixed $vote_id 投票ID 
	 * @param mixed $limit  分页每页条数
	 * @return mixed 分页用户数据
	 */	
	public function getVoteUsers($vote_id,$order="usr.id DESC",$limit=40){
		if(empty($vote_id)){
			return null;
		}	
		return  D( '' )->table($this->tablePrefix.$this->tableName." usr ,".$this->tablePrefix."vote vote")->DISTINCT(true)->field("usr.uid")->where("usr.vote_id='{$vote_id}' and vote.id=usr.vote_id and usr.uid<>vote.uid ")->order($order)->findPage($limit);
	}

} 
?>
