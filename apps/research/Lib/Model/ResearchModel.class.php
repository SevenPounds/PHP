<?php
/**
 * ResearchModel(课题|评课)
 * @author frsun|xypan
 * @version TS3.0
 */
class ResearchModel extends Model{
	
	/**
	 * 表名
	 * @var string
	 */
	protected $tableName = 'research';
	
	/**
	 * 获取发起的课题|评课
	 * @param int $cuid 创建者id
	 * @param string $type <标志位，'research'-课题 'onlineEval'-在线评课>
	 * @param int $status <课题状态 -1全部 1正在进行 0结束>--by zhaoliang 2014/1/13
	 * @param string $keyword 搜索词，默认为空字符，查询所有--by zhaoliang 2014/1/13
	 * @param string $field 查询字段
	 * @param string $order  排序字段
	 * @param int $limit 分页大小
	 */
	public function getResearchByCuid($cuid,$type='research',$status = -1, $keyword="",$field=null,$order="createtime desc",$limit=5){
		$condition['is_del'] = 0;
		$condition['uid'] = $cuid;
		$condition['type'] = $type;
		if($keyword != ""){
			$condition['title'] = array('like', '%'.$keyword.'%');
		}
		if($status != -1){
			$condition['status'] = $status;
		}
		$result = $this->where($condition)->field($field)->order($order)->findPage($limit);
		return $result;
	}
	
	/**
	 * 获取发起的课题数量
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @api
	 * @param int $cuid <发起人id>
	 * @param int $status <课题状态 -1:全部 0结束 1进行中>
	 * @param string $type <标志位，'research'-课题 'onlineEval'-在线评课>
	 */
	public function getResearchCountByStatus($cuid, $status = -1, $type = 'research'){
		$condition['is_del'] = 0;
		$condition['uid'] = $cuid;
		$condition['type'] = $type;
		if($status != -1){
			$condition['status'] = $status;
		}
		return $this->where($condition)->count();
	}
	
	/**
	 * 按条件查询课题列表
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @api
	 * @param string $subjectId <学科code，若查询全部学科，则传参数"">
	 * @param int $provinceId <省份code,如查询全部，则传参数0>
	 * @param int $cityId <市code,如查询全部，则传参数0>
	 * @param int $areaId <区县code,如查询全部，则传参数0>
	 * @param string $tags <tagId>
	 * @param int $status <课题状态 -1全部 1正在进行 0结束>
	 * @param string $keyword <搜索词，默认为空字符，查询所有>
	 * @param string $type <标志位，'research'-课题 'onlineEval'-在线评课>
	 * @param int $choose <搜索类型, 0=>最新讨论;1=>最热讨论;0=>精华讨论;0=>我关注的人;>
	 * @param int $limit <每页显示数量>
	 */
	public function getResearchList( $tags = "", $status = -1, $keyword = "", $type='research', $limit = 10,$nav=0,$mid){
		$condition = " and r.is_del = 0 and r.type='$type'";
		switch($nav){
			case 0:
				$condition = $condition." and r.isHot != 2 ";
				$order ='r.createtime DESC , r.discuss_count DESC';
				break;
			case 1:
				$condition = $condition." and r.isHot != 2 ";
				$order='r.discuss_count DESC, r.createtime DESC';
				break;
			case 2:
				$condition =$condition." and r.isHot =2 " ;
				$order='r.discuss_count DESC, r.createtime DESC';
				break;
			case 3:
				$in_arr = M('user_follow')->field('fid')->where("uid={$mid}")->findAll();
				$in_arr = $this->_getInArr($in_arr);
				$condition = $condition." and u.uid IN  " .$in_arr.' ';
				$order='r.createtime DESC';
				break;
		
		}
		if($status!=-1){
			$condition = $condition." and r.status = $status";
		}
		if($keyword != ""){
			$condition = $condition." and r.title like '%$keyword%'";
		}
		$tablestr = $this->tablePrefix."user u,".$this->tablePrefix."research r";
		if($tags != ""){
			$tablestr = $tablestr.",".$this->tablePrefix."app_tag t";
			$condition = $condition." and r.id = t.row_id and t.tag_id in (".$tags.") and t.app='$type' and t.table='$type'";
		}
		return $this->table($tablestr)->where("u.uid = r.uid".$condition)->order($order)
		->field('r.id,r.title,r.description,r.status,r.createtime,r.discuss_count,r.member_count,u.uname,u.subject,u.province,u.city,u.area')->findpage($limit);
	}
	
	/**
	 * 删除主题讨论（支持删除多个）
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @api
	 * @param int/array $rids <主题Id>
	 */
	public function deleteResearch($rids){
		if(is_array($rids)){
			foreach ($rids as $rid) {
				$this->where("id=".$rid)->setField('is_del',1);
			}
		}
		else{
			return $this->where("id=".$rids)->setField('is_del',1);
		}
	}
	
	/**
	 * 根据用户uid删除主题讨论
	 * @param int $rid 主题讨论id
	 * @param int $uid 用户uid
	 */
	public function deleteResearchByUid($rid,$uid){
		$map = array('id'=>$rid, 'uid'=>$uid);
		$ret = $this->where($map)->setField('is_del',1);
		return $ret;
	}
	
	/**
	 * 获取参加者参加的主题讨论
	 * @param int $muid  参加者id
	 * @param string $type <标志位，'research'-课题 'onlineEval'-在线评课>
	 * @param int $status 讨论状态 -1全部  1正在进行  0结束--by zhaoliang 2014/1/14
	 * @param string $keyword 搜索词，默认为空字符，查询所有--by zhaoliang 2014/1/13
	 * @param string $order  排序字段
	 * @param int $limit  分页大小
	 * @return unknown
	 */
	public function  getResearchByJuid($muid,$type = 'research',$status = -1,$keyword="",$order="createtime desc",$limit=5){
		$condition = " and r.is_del=0 and ru.member_id=$muid and r.type='$type'";
		if($keyword != ""){
			$condition = $condition." and r.title like '%$keyword%'";
		}
		if($status != -1){
			$condition = $condition." and r.status=$status";
		}
		$condition = $condition." and r.uid!=$muid";
		$field = 'r.*,ru.member_id,ru.is_new';
		$order ='r.'.$order;
		$result = $this->table($this->tablePrefix.'research r,'.$this->tablePrefix.'research_user ru')->where("r.id=ru.research_id".$condition)->field($field)->order($order)->findPage($limit);
		return $result;
	}
	
	/**
	 * 创建课程研究
	 * @param  int $cuid  创建者id
	 * @param  string $title 课程名称
	 * @param string $description 简介
	 * @param string $type 类型      research:课题研究  onlineEval:网上评课
	 * @param int $status  课程状态  1：进行中  0：已完成
	 * @param int $public_status  课程公开状态  1：公开  0：不公开
	 * @param string $attachIds 附件ids
	 * @param string $memberIds 成员ids	 
	 * @param array $gids 发布到名师工作室的id数组
	 * @param array $tagIds 标签IDs --by zhaoliang 2014/1/6
	 */
	public function createNewData($data,$attachIds,$memberIds,$gids,$tagIds){

		if(!empty($memberIds)){
			$memberIds = explode('|', $memberIds);
			array_map('intval', $memberIds);
		}
		if(!is_array($memberIds)){
			$memberIds = array();
		}
		$memberIds = array_merge($memberIds,array($data['uid']));
		
		// 数据信息
		$data['createtime'] = time();
		$data['modifiedtime'] = time();
		$data['discuss_count'] = 0;
		$data['member_count'] = count($memberIds);
		$data['summary_attachid'] = 0;
		$data['status'] = 1;
		$data['public_status'] = 1;
		$data['is_del'] = 0;
		$res = $this->add($data);
		
		if($res){
			//增加标签信息
			$tagobj = M("Tag");
			$tagobj->setAppName("research");
			$tagobj->setAppTable("research");
			$tagobj->setAppTags($res,array(),9,$tagIds);
			// 增加成员信息
			if(!empty($memberIds)){
				$this->createResearchUser($res,$memberIds,$data['uid'],$data['title']);
			}
            //添加用户的常用用户
            D('UserFavorite')->inviteUser($data['uid'], $memberIds, 'research');
			// 增加附件信息
			if(!empty($attachIds)){
				$attachIds = explode('|', $attachIds);
				array_map('intval', $attachIds);
				$this->createResearchAttach('research',$res,$attachIds);
			}
			//同步到名师工作室
			D('MSGroupTeachingApp','msgroup')->addTeachingAppInfo($gids,$res,'research');
			// 增加动态
			$this->_syncToFeed($data['uid'], $res, $data['title'], $data['description'], $gids, $data['to_space']);
		}
		
		return $res;
	}
	
	/**
	 * 根据id获取数据
	 * @param int $researchId id
	 * @param string $field 字段
	 * @return array 详细信息
	 */
	public function getResearchById($researchId,$field=null){
		$condition['id'] = $researchId;
		$rs_obj = $this->where($condition)->field($field)->find();
		//获取AppTags---by zhaoliang 2014/1/16
		$tagobj = M("Tag");
		$tagobj->setAppName("research");
		$tagobj->setAppTable("research");
		$tagList = $tagobj->getAppTags(array($researchId));
		$rs_obj["tags"] = $tagList[$researchId];
		return $rs_obj;
	}
	
	/**
	 * 判断用户是否是参与者
	 * @param int $researchId id
	 * @param int $uid  参与者id
	 */
	public function isExistsJoin($researchId,$uid){
		$map['research_id'] = $researchId;
		$map['member_id'] = $uid;
		$res  = D('research_user')->where($map)->count();
		
		if($res > 0){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取附件信息
	 * @param string $appType 应用名称
	 * @param int $appId 应用id
	 * @param boolean $flag true则返回附件信息,false则返回附件ids
	 */
	public function getAttachs($appType,$appId,$flag = true){
		$map['app_type'] = $appType;
		$map['app_id'] = $appId;
		$attachids = D('research_attach')->where($map)->field('attach_id')->select();
		$attachids_ = array();
		foreach ($attachids as $key=>$val){
			$attachids_ = array_merge($attachids_,array($val['attach_id']));
		}
		$attachids_ = implode(',', $attachids_);
		
		if(!$flag){
			return $attachids_;
		}

		// 附件 信息
		$attachs = D("Attach")->getAttachByIds($attachids_,"attach_id,name");
		
		return $attachs;
	}
	
	/**
	 * 获取总结附件的信息
	 * @param int $attachId 附件id
	 */
	public function getSumAttachs($attachId){
		$attachids_ = array($attachId);
		
		// 附件 信息
		$attachs = D("Attach")->getAttachByIds($attachids_,"attach_id,name");
		
		return $attachs;
	}
	
	/**
	 * 获取成员信息
	 * @param int $researchId id
	 * @param int $uid 创建者id
	 */
	public function getUserListIds($research_id,$uid){
		$map['research_id'] = $research_id;
		$map['member_id'] = array('neq',$uid);
		$userList = D('research_user')->where($map)->field('member_id')->select();
		$userIds = array();
		foreach ($userList as $key=>$val){
			$userIds = array_merge($userIds,array($val['member_id']));
		}
		return implode(',', $userIds);
	}
	
	/**
	 * 更新课程研究
	 * @param  int $cuid  创建者id
	 * @param  string $title 课程名称
	 * @param string $description 简介
	 * @param string $type 类型      research:课题研究  onlineEval:网上评课
	 * @param string $attachIds 附件ids
	 * @param string $memberIds 成员ids
	 * @param array $gids 发布到名师工作室的id数组
	 * @param array $tagIds 标签Ids
	 */
	public function updateRecordData($data,$attachIds,$memberIds,$oldMemberIds,$gids,$tagIds){
		
			// 验证
		$research = M($this->tableName)->find($data['id']);
		if (empty($research)) return false;	//该主题不存在
		
		if(!empty($memberIds)){
			$memberIds = explode('|', $memberIds);
			array_map('intval', $memberIds);
		}
		if(!is_array($memberIds)){
			$memberIds = array();
		}
		$memberIds = array_merge($memberIds,array($data['uid']));
		
		// 数据信息
		$data['modifiedtime'] = time();
		$data['member_count'] = count($memberIds);

		$res = $this->where("id={$data['id']} and uid={$data['uid']}")->save($data);
		
		if($res){
			$tagobj = M("Tag");
			$tagobj->setAppName("research");
			$tagobj->setAppTable("research");
			$row_id = $data['id'];
			$tagList = $tagobj->getAppTags(array($row_id));
			if($tagList){
				foreach($tagList[$row_id] as $k=>$v){
					$tagobj->deleteAppTag($row_id, $k);
				}
			}
			$tagobj->setAppTags($row_id,array(),9,$tagIds);
			
			if(intval($research['accessType'])==1){
				if(!empty($oldMemberIds)){
				$oldMemberIds = explode(',', $oldMemberIds);
				array_map('intval', $oldMemberIds);
				}
			
				// 被删除的参与者ids
				$deletedMemberIds = array();
				foreach($oldMemberIds as $value){
					if(!in_array($value,$memberIds)){
						array_push($deletedMemberIds,$value);
					}
				}	
			
				// 新增的参与者ids
				$addmemberIds = array();
				foreach($memberIds as $value){
					if(!in_array($value,$oldMemberIds) && $value != $data['uid']){
						array_push($addmemberIds,$value);
					}
				}
				//添加用户的常用用户
				D('UserFavorite')->inviteUser($data['uid'], $addmemberIds, 'research');
				// 删除被删除成员信息
				if(!empty($deletedMemberIds)){
					$this->deleteResearchUser($data['id'],$deletedMemberIds,$data['uid'],$data['title']);
				}
			
				// 增加成员信息
				if(!empty($addmemberIds)){
					$this->createResearchUser($data['id'],$addmemberIds,$data['uid'],$data['title']);
				}
				
			}
			// 增加附件信息
			if(!empty($attachIds)){
				$attachIds=str_replace(',', '|', $attachIds);
				$attachIds = explode('|', $attachIds);
				array_map('intval', $attachIds);
				$this->createResearchAttach('research',$data['id'],$attachIds);
			}
			
			//同步到名师工作室
			D('MSGroupTeachingApp','msgroup')->updateTeachingAppInfo($gids,$data['id'],'research');
		}
		
		return $res;
	}
	
	/**
	 * 结束课题研究
	 * @param int $researchid  课题id
	 * @param int $summary_attachid  附件id
	 * @param int $public_status  成果是否公开  1：公开  0：不公开
	 * @param int $uid 创建者id
	 */
	public function finishResearch($researchid,$summary_attachid,$public_status,$uid){
		$map['id'] = $researchid;
		$map['uid'] = $uid;
		if($summary_attachid>0){
			$data['summary_attachid'] = $summary_attachid;
			$data['public_status'] = $public_status;
			$data['status'] = 0;
			$data['closedtime'] = time();
		}
		$result = $this->where($map)->save($data);
		return $result;
	}


	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “课题研究（主题讨论）”
	 * 筛选已结束的主题，按照讨论次数降序排序，次数相同按结束时间降序。
	 */
	public function getResearchInfos($start,$limit,$order){
		$map = array();
		$map['status'] = 0;
		if(empty($order)){
			$order ='discuss_count desc , closedtime desc';
		}
		return $this->order($order)->where($map)->limit($start.",".$limit)->select();
	}
	
	/**
	 * 获取用户未参与的课题数量
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @api
	 * @param int $uid <用户id>
	 */
	public function countNewResearch($uid){
		/*$condition = array("member_id"=>$uid, "is_new"=>1);
		return D('research_user')->where($condition)->count();*/
		return $this->table($this->tablePrefix.'research r,'.$this->tablePrefix.'research_user u')->where("r.id=u.research_id and r.uid!=$uid and u.member_id=$uid and u.is_new=1 and r.is_del=0")->count();
	}
	/*
	public function updateNewStatus($ruId){
		$condition = array("id"=>$ruId);
		return D('research_user')->where($condition)->setField("is_new",0);
	}
	*/
	/**
	 * 更新主题对于某参与用户而言是否是新主题的标志
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @api
	 * @param int $researchId <主题Id>
	 * @param int $uid <用户Id>
	 */
	public function updateNewStatus($researchId, $uid){
		$condition = array("research_id"=>$researchId, "member_id"=>$uid);
		return D('research_user')->where($condition)->setField("is_new",0);
	}
	
	/**
	 * 删除参加成员关系
	 * @param int $researchId id
	 * @param array $memberIds 成员id数组
	 * @param int $uid 创建者id
	 * @param string $title 标题
	 */
	private function deleteResearchUser($researchId,$memberIds,$uid,$title){
		foreach ($memberIds as $value){
			$ru['research_id'] = $researchId;
			$ru['member_id'] = $value;
			$res = D('research_user')->where($ru)->delete();
				
			// 增加消息通知
			if($res){
				addResearchMessage($uid, $value,'', $title,$researchId,'delMember');
			}
		}
	}
	
	/**
	 * 增加参加成员关系
	 * @param int $researchId id
	 * @param array $memberIds 成员id数组
	 * @param int $uid 创建者id 
	 * @param string $title 标题
	 */
	private function createResearchUser($researchId,$memberIds,$uid,$title){
		foreach ($memberIds as $value){
			$ru['research_id'] = $researchId;
			$ru['member_id'] = $value;
			$ru['is_new'] = 1;
			$res = D('research_user')->add($ru);
			
			// 增加消息通知
			if($res && $value != $uid){
				addResearchMessage($uid, $value,'' ,$title,$researchId,'research');
			}
		}
	}
	
	/**
	 * 添加或修改附件材料信息的关系
	 * @param string $appType 应用类型
	 * @param int $appId 应用id
	 * @param array $attachIds 附件ids
	 */
	private function createResearchAttach($appType,$appId,$attachIds){
		$condition['app_type'] = $appType;
		$condition['app_id'] = $appId;
		$attach_obj =  D('research_attach');
		$attach_obj->where($condition)->delete();
		foreach ($attachIds as $val){
			$data['app_type'] = $appType;
			$data['app_id'] = $appId;
			$data['attach_id'] = $val;
			$attach_obj->add($data);
		}
	}
	
	/**
	 * 发表课题研究动态
	 * @param int $uid 用户id
	 * @param int $appId 应用id
	 * @param int $title 标题
	 * @param int $content 内容
	 * @param array $gids 名师工作室ID
     * @param int $toSpace 是否同步到我的工作室
     *
	 * @author yxxing |modify by xypan 0927
	 */
	private function _syncToFeed($uid, $appId, $title, $content, $gids, $toSpace) {
		
        $d['content'] = '';
        $d['source_url'] = U('research/Index/show', array("id"=>$appId));
            $d['body'] = '我发起了主题讨论【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
            $feed = model('Feed')->put($uid, 'research', 'research', $d);
        if(!empty($gids)){
            foreach ($gids as $gid) {
                if(!empty($gid)){
                    $d['gid'] = $gid;
                    $d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 我发起了主题讨论【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
                    $feed = model('Feed')->put($uid, 'msgroup', "msgroup", $d);
                }
            }
        }
    }
    

    /**
     * 根据isHot,评论数，成员数，时间排序最火主题讨论
     * return array
     */
    public function geAppHotResearch(){
    	//主题讨论列表
    	$hot_list =$this->field('id,uid,title,description,discuss_count')->where(array('isHot'=>1,'is_del'=>0,'accessType'=>0))->order('discuss_count DESC, member_count DESC, createtime DESC ')->limit("0,5")->findAll();
    	$result =array();
    	foreach ($hot_list as $key){
    		$map=array();
    		$map['research_id']=$key['id'];
    		$map['is_del']=0;
    		//主题讨论中根据赞，评论数，时间排序的评论内容
    		$comment =D('Post','research')->field('post_userid,content,agree_count')->where($map)->order('agree_count DESC, comment_count DESC, createtime DESC')->find();
    		//主题讨论发表者的信息
    		$blogUser =D('User')->getUserInfo($key['uid']);
    		if(!empty($comment)){
    			//主题讨论下评论发表者的信息
    			$commentUser =D('User')->getUserInfo($comment['post_userid']);
    		}
    		$blogInfo['hotUser']=$blogUser;
    		$blogInfo['commentUser']=$commentUser;
    		$blogInfo['id']=$key['id'];
    		$blogInfo['content'] =htmlentities($key['description']);
    		$blogInfo['title']=htmlentities($key['title']);
    		$blogInfo['discuss_count']=$key['discuss_count'];
    		$blogInfo['comment_content']=parse_html(htmlentities($comment['content']));
    		$blogInfo['comment_count']=$comment['agree_count'];
    		$blogInfo['source_url']=U('research/Index/show',array('id'=>$key['id']));
    		unset($commentUser);
    		unset($comment);
    		array_push($result,$blogInfo);
    	}
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
    
    /**
     * 根据条件获取主题讨论列表
     * 注：供后台使用
     * @author ylzhao
     */
    public function getResearches($conditions, $page=1, $limit=10, $order){
    	$map = array();
    	if(isset($conditions['isHot'])){
    		$map['isHot'] = intval($conditions['isHot']);
    	}
    	if(isset($conditions['accessType'])){
    		$map['accessType'] = $conditions['accessType'];
    	}
    	if(isset($conditions['title'])){
    		$map['title'] = array('like', "%".$conditions['title']."%");
    	}
    	$fields = 'r.*,u.`uname`';
    	$tables = $this->tablePrefix.$this->tableName.' r ';
    	$join = 'LEFT JOIN '.$this->tablePrefix.'user u ON r.`uid` = u.`uid`';
    	$order = empty($order) ? "createtime DESC" : $order;
    	$map['r.`is_del`'] = 0;
    	$start = ($page - 1) * $limit;
    	$result['data'] = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->limit("$start, $limit")->select();
    	$result['count'] = $total = $this->table($tables)->where($map)->join($join)->count();
    	return $result;
    }
    
    
    /**
     * 数组截取组成字符串.
     * @param unknown $in_arr
     * @return string
     */
    private function _getInArr($in_arr) {
    
    	$in_str = "(";
    	foreach($in_arr as $key=>$v) {
    		$in_str .= $v['fid'].",";
    	}
    	$in_str = rtrim($in_str,",");
    	$in_str .= ")";
    	return $in_str;
    
    }
    
    
}
?>
