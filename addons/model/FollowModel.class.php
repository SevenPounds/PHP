<?php
/**
 * 用户关注模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net>
 * @version 1.0
 */
class FollowModel extends Model {

	protected $tableName = 'user_follow';
	protected $fields = array(0=>'follow_id',1=>'uid',2=>'fid',3=>'remark',4=>'ctime',5=>'type','_autoinc'=>true,'_pk'=>'follow_id');

	/**
	 * 获取关注查询SQL语句，具体使用不清楚
	 * @param integer $uid 用户ID
	 * @return string 关注查询SQL语句
	 */
	public function getFollowingSql($uid) {
		return "SELECT `fid` FROM {$this->tablePrefix}user_follow WHERE `uid` = '{$uid}'";
	}
	
	/**
	 * 获取指定用户的备注列表
	 * @param integer $uid 用户ID
	 * @return array 指定用户的备注列表
	 */
	public function getRemarkHash($uid) {
		if(empty($uid)) {
			return false;
		}
		if($list = static_cache('follow_remark_'.$uid)) {
			return $list;
		}
		
		$map['uid'] = $uid;
		$map['remark'] = array('NEQ', '');
		$list = $this->where($map)->getHashList('fid', 'remark');
		static_cache('follow_remark_'.$uid, $list);

		return $list;
	}

	/**
	 * 添加关注 (关注用户)
	 * @example
	 * null：参数错误
	 * 11：已关注
	 * 12：关注成功(且为单向关注) 
	 * 13：关注成功(且为互粉)
	 * @param integer $uid 发起操作的用户ID
	 * @param integer $fid 被关注的用户ID或被关注的话题ID
	 * @return boolean 是否关注成功
	 */
	public function doFollow($uid, $fid,$type=0) {
		if ( intval( $uid ) <= 0 || $fid <= 0 ){
			$this->error= L('PUBLIC_WRONG_DATA');			// 错误的参数
			return false;
		}
		
		if ($type==0&&$uid == $fid){
			$this->error=L('PUBLIC_FOLLOWING_MYSELF_FORBIDDEN');		// 不能关注自己
			return false;
		}

		if ($type==0&&!model('User')->find($fid)){
			$this->error=L('PUBLIC_FOLLOWING_PEOPLE_NOEXIST');			// 被关注的用户不存在
			return false;
		}

		if ($type==0&&model('UserPrivacy')->isInBlackList($uid,$fid)) {
			$this->error='根据对方设置，您无法关注TA';
			return false;
		}else if($type==0&&model('UserPrivacy')->isInBlackList($fid,$uid)){
			$this->error='您已把对方加入黑名单';
			return false;
		}
		//维护感兴趣的人的缓存
		model('Cache')->set('related_user_'.$uid, '' , 24 * 60 * 60);
		// 获取双方的关注关系
		$follow_state = $this->getFollowState($uid, $fid,$type);
 		// 未关注状态
		if(0 == $follow_state['following']) {
		
			// 添加关注
			$map['uid']  = $uid;
			$map['fid']  = $fid;
			$map['type']  = $type;
			$map['ctime'] = time();
			$result = $this->add($map);
			// 通知和微博
/*			model('Notify')->send($fid, 'user_follow', '', $uid);
			model('Feed')->put('user_follow', array('fid'=>$fid), $uid);*/
			if($result) {
				if($type==0){
					$maps['key'] = 'email';
					$maps['uid'] = $fid;
					$isEmail = D('user_privacy')->where($map)->field('value')->find();
					if($isEmail['value'] === 0){
						$userInfo = model('User')->getUserInfo($fid);
						model('Mail')->send_email($userInfo['email'],'您增加了一个新粉丝','content');
					}
				}
				$this->error = L('PUBLIC_ADD_FOLLOW_SUCCESS');			// 关注成功
				if($type==0){
					$this->_updateFollowCount($uid, $fid, true);			// 更新统计
				}else if($type==3){
					$this->_updataFollowMsGroupCount($uid, $fid, true);
				}else{
					$this->_updateFollowCampusCount($uid, $fid, true,$type);
				}
				$follow_state['following'] = 1;
				return $follow_state;
			} else {
				$this->error = L('PUBLIC_ADD_FOLLOW_FAIL');				// 关注失败
				return false;
			}
		} else {
			$this->error = L('PUBLIC_FOLLOW_ING');						// 已关注
			return false;
		}
	}
	
	public function doFollowCampus($uid, $fid,$type){
		return $this->doFollow($uid, $fid,$type);
	}

	public function bulkDoFollow($uid, $fids) {
		$follow_states = $this->getFollowStateByFids($uid, $fids);
		$data  = array();
		$_fids = array();
		foreach ($follow_states as $f_s_k => $f_s_v) {
	 		// 未关注
			if (0 == $f_s_v['following']) {
				// 关注的字段数据
				$data[]  = "({$uid}, {$f_s_k},".time().")";
				$_fids[] = $f_s_k;
				$follow_states[$f_s_k]['following'] = 1;
				// 通知和微博
/*				model('Notify')->send($fid, 'user_follow', '', $uid);
				model('Feed')->put('user_follow', array('fid'=>$fid), $uid);*/
			} else {
				unset($follow_states[$f_s_k]);
			}
		}
		if (!empty($data)) {
			$sql = "INSERT INTO {$this->tablePrefix}{$this->tableName}(`uid`,`fid`,`ctime`) VALUES" . implode(',', $data);
			$res = $this->execute($sql);
			if ($res) {
				// 关注成功
				$this->error=L('PUBLIC_ADD_FOLLOW_SUCCESS');

				// 更新统计
				$this->_updateFollowCount($uid, $_fids, true);

				return $follow_states;
			} else {
				$this->error = L('PUBLIC_ADD_FOLLOW_FAIL');
				return false;
			}
		} else {
			// 全部已关注
			$this->error = L('PUBLIC_FOLLOW_ING');
			return false;
		}
	}

	/**
	 * 双向关注用户操作
	 * @param integer $uid 用户ID
	 * @param array $fids 需关注用户ID数组
	 * @return boolean 是否双向关注成功
	 */
	public function eachDoFollow($uid, $fids)
	{
		// 获取用户关组状态
		$followStates = $this->getFollowStateByFids($uid, $fids);
		$data = array();
		$_following = array();
		$_follower = array();
		
		foreach($followStates as $key => $value) {
			if(0 == $value['following']) {
				$data[] = "({$uid}, {$key}, ".time().")";
				$_following[] = $key;
			}
			if(0 == $value['follower']) {
				$data[] = "({$key}, {$uid}, ".time().")";
				$_follower[] = $key;
			}
		}
		// 处理数据结果
		if(!empty($data)) {
			$sql = "INSERT INTO {$this->tablePrefix}{$this->tableName}(`uid`,`fid`,`ctime`) VALUES ".implode(',', $data);
			$res = $this->execute($sql);
			if($res) {
				// 关注成功
				$this->error = L('PUBLIC_ADD_FOLLOW_SUCCESS');
				
				//被关注人的关注人数+1
				foreach ( $_follower as $fo){
					model('UserData')->setUid($fo)->updateKey('following_count', 1, true);
				}
				// 更新被关注人的粉丝数统计
				$this->_updateFollowCount($uid, $_following, true);
				//更新关注人的粉丝数
				model('UserData')->setUid($uid)->updateKey('follower_count', count($_follower), true);
				return true;
			} else {
				$this->error = L('PUBLIC_ADD_FOLLOW_FAIL');
				return false;
			}
		} else {
			// 已经全部关注
			$this->error = L('PUBLIC_FOLLOW_ING');
			return false;
		}
	}

	/**
	 * 取消关注（关注用户 / 关注话题）
	 * @example
	 * 00：取消失败
	 * 01：取消成功
	 * @param integer $uid 发起操作的用户ID
	 * @param integer $fid 被取消关注的用户ID或被取消关注的话题ID
	 * @return boolean 是否取消关注成功
	 */
	public function unFollow($uid, $fid,$type=0) {
		$map['uid'] = $uid;
		$map['fid'] = $fid;
		$smap = $map;
		$smap['type'] = $type;
		// 获取双方的关注关系
		$follow_state = $this->getFollowState($uid, $fid,$type);
		if(1 == $follow_state['following']) {
			// 已关注
			// 清除对该用户的分组，再删除关注
			if($type==0&&(false !== D('UserFollowGroupLink')->where($map)->delete()) && $this->where($smap)->delete()) {
				//D('UserFollowGroupLink')->where($map)->delete();
				$this->error = L('PUBLIC_ADMIN_OPRETING_SUCCESS');			// 操作成功
				$this->_updateFollowCount($uid, $fid, false);				// 更新统计
				$follow_state['following'] = 0;
				return $follow_state;
			}elseif($type==3 && $this->where($map)->delete()) {
				$this->error = L('PUBLIC_ADMIN_OPRETING_SUCCESS');			// 操作成功
				$this->_updataFollowMsGroupCount($uid, $fid, false);
				$follow_state['following'] = 0;
				return $follow_state;
			} elseif($type!=0 && $this->where($map)->delete()) {
				$this->error = L('PUBLIC_ADMIN_OPRETING_SUCCESS');			// 操作成功
				$this->_updateFollowCampusCount($uid, $fid, false,$type);
				$follow_state['following'] = 0;
				return $follow_state;
			} else {
				$this->error = L('PUBLIC_ADMIN_OPRETING_ERROR');			// 操作失败
				return false;
			}
		} else {
			// 未关注
			$this->error = L('PUBLIC_ADMIN_OPRETING_ERROR');				// 操作失败
			return false;
		}
	}

	public function unFollowCampus($uid, $fid,$type){
		return $this->unFollow($uid, $fid,$type);
	}
	/**
	 * 获取指定用户的关注与粉丝数
	 * @param array $uids 用户ID数组
	 * @return array 指定用户的关注与粉丝数
	 */
	public function getFollowCount($uids) {
		$count = array();
		foreach($uids as $u_v) {
			$count[$u_v] = array('following'=>0,'follower'=>0);
		}

		$following_map['uid'] = $follower_map['fid'] = array('IN', $uids);
		// 关注数
		$following = $this->field('COUNT(1) AS `count`,`uid`')->where($following_map)->group('`uid`')->findAll();
		foreach($following as $v) {
			$count[$v['uid']]['following'] = $v['count'];
		}
		// 粉丝数
		$follower = $this->field('COUNT(1) AS `count`,`fid`')->where($follower_map)->group('`fid`')->findAll();
		foreach ($follower as $v) {
			$count[$v['fid']]['follower'] = $v['count'];
		}

		return $count;
	}

	/**
	 * 获取指定用户的关注列表 分页
	 * @param integer $uid 用户ID
	 * @param integer $gid 关注组ID，默认为空
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户的关注列表
	 */
	public function getFollowingList($uid, $gid = null, $limit = 10) {
		$limit = intval($limit) > 0 ? $limit : 10;
		if(is_numeric($gid)) {
			// 关组分组
			if($gid == 0) {
				$list = $this->table("{$this->tablePrefix}{$this->tableName} AS follow LEFT JOIN {$this->tablePrefix}user_follow_group_link AS link ON link.follow_id = follow.follow_id")
							 ->field('follow.*')
							 ->where("follow.uid={$uid} AND link.follow_id IS NULL")
							 ->order('follow.uid DESC')
							 ->findPage($limit);
			} else {
				$list = $this->field('follow.*')
						 	 ->table("{$this->tablePrefix}user_follow_group_link AS link LEFT JOIN {$this->tablePrefix}{$this->tableName} AS follow ON link.follow_id=follow.follow_id AND link.uid=follow.uid")
						 	 ->where("follow.uid={$uid} AND link.follow_group_id={$gid}")
						 	 ->order('follow.uid DESC')
						 	 ->findPage($limit);
			}
		} else {
			// 没有指定关组分组的列表
			$list = $this->where("`uid`={$uid}")->order('`follow_id` DESC')->findPage($limit);
		}

		return $list;
	}

	/**
	 * 获取指定用户的关注列表  不分页
	 * @param integer $uid 用户ID
	 * @param integer $gid 关注组ID，默认为空
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户的关注列表
	 */
	public function getFollowingListAll($uid, $gid = null) {
		if(is_numeric($gid)) {
			// 关组分组
			if($gid == 0) {
				$list = $this->table("{$this->tablePrefix}{$this->tableName} AS follow LEFT JOIN {$this->tablePrefix}user_follow_group_link AS link ON link.follow_id = follow.follow_id")
							 ->field('follow.*')
							 ->where("follow.uid={$uid} AND link.follow_id IS NULL")
							 ->order('follow.uid DESC')
							 ->findAll();
			} else {
				$list = $this->field('follow.*')
						 	 ->table("{$this->tablePrefix}user_follow_group_link AS link LEFT JOIN {$this->tablePrefix}{$this->tableName} AS follow ON link.follow_id=follow.follow_id AND link.uid=follow.uid")
						 	 ->where("follow.uid={$uid} AND link.follow_group_id={$gid}")
						 	 ->order('follow.uid DESC')
						 	 ->findAll();
			}
		} else {
			// 没有指定关组分组的列表
			$list = $this->where("`uid`={$uid}")->order('`follow_id` DESC')->findAll();
		}

		return $list;
	}
	/**
	 * 获取指定用户的关注列表  不分页
	 * @param integer $uid 用户ID
	 * @param integer $gid 关注组ID，默认为空
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户的关注列表
	 */
	public function getFollowingListAllForOpen($uid) {
		$list = $this->field('usr.fid,fg.title,fg.follow_group_id,tu.uname,tu.cyuid')
		->table("{$this->tablePrefix}user_follow AS usr LEFT JOIN {$this->tablePrefix}user_follow_group_link AS glink ON usr.uid = glink.uid AND usr.fid = glink.fid  LEFT JOIN {$this->tablePrefix}user_follow_group AS fg ON glink.follow_group_id = fg.follow_group_id LEFT JOIN {$this->tablePrefix}user AS tu ON usr.fid = tu.uid")
		->where("usr.uid={$uid} ")
		->order('usr.uid DESC')
		->findAll();
		return $list;
	}

	/**
	 * 获取指定用户的粉丝列表
	 * @param integer $uid 用户ID
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 指定用户的粉丝列表
	 */
	public function getFollowerList($uid, $limit = 10) {
		$limit = intval($limit) > 0 ? $limit : 10;
		// 粉丝列表
		$list = $this->where("`fid`={$uid} AND type=0")->order('`follow_id` DESC')->findPage($limit);
		$fids = getSubByKey($list['data'], 'uid');
		// 格式化数据
		foreach($list['data'] as $key => $value) {
			$uid = $value['uid'];
			$fid = $value['fid'];
			$list['data'][$key]['uid'] = $fid;
			$list['data'][$key]['fid'] = $uid;
		}
		return $list;
	}
	
	/**
	 * 获取不同类型用户的粉丝列表
	 * @param integer $uid 用户ID
	 * @param integer $limit 结果集数目，默认为10
	 * @return array 用户的粉丝列表
	 */
	public function getOrgFollowerList($uid,$type,$limit = 10) {
		$limit = intval($limit) > 0 ? $limit : 10;
		// 粉丝列表
		$list = $this->where("fid={$uid} AND type={$type}")->order('`follow_id` DESC')->findPage($limit);
		
		$fids = getSubByKey($list['data'], 'uid');
		// 格式化数据
		foreach($list['data'] as $key => $value) {
			$uid = $value['uid'];
			$fid = $value['fid'];
			$list['data'][$key]['uid'] = $fid;
			$list['data'][$key]['fid'] = $uid;
		}
		return $list;
	}
	

	/**
	 * 获取用户uid与用户fid的关注状态，已uid为主
	 * @param integer $uid 用户ID
	 * @param integer $fid 用户ID
	 * @return integer 用户关注状态，格式为array('following'=>1,'follower'=>1)
	 */
	public function getFollowState($uid, $fid ,$type=0) {
		$follow_state = $this->getFollowStateByFids($uid, $fid ,$type);
		return $follow_state[$fid];
	}

/* 	public function getFollowCampusStateByFids($uid, $fids ,$type){
		array_map( 'intval' , $fids);
		!is_array($fids) && $fids = explode(',', $fids);
		if(empty($_fids)) {
			return array();
		}
		$follow_data = $this->where("  uid = '{$uid}' AND fid IN({$_fids})   AND type='{ $type}' ")->findAll();
		$follow_states = $this->_formatFollowState($uid, $fids, $follow_data);
		return $follow_states[$uid];
	}
	
	private function _formatFollowCampusState($uid, $fids, $follow_data){
		!is_array($fids) && $fids = explode(',', $fids);
		foreach($fids as $fid) {
			$follow_states[$uid][$fid] = array('following'=>0,'follower'=>0);
		}
		foreach($follow_data as $r_v) {
			if($r_v['uid'] == $uid) {
				$follow_states[$r_v['uid']][$r_v['fid']]['following'] = 1;
			} else if($r_v['fid'] == $uid) {
				$follow_states[$r_v['fid']][$r_v['uid']]['follower'] = 1;
			}
		}
		return $follow_states;
	}
	 */
	/**
	 * 批量获取用户uid与一群人fids的彼此关注状态
	 * @param integer $uid 用户ID
	 * @param array $fids 用户ID数组
	 * @return array 用户uid与一群人fids的彼此关注状态
	 */
	public function getFollowStateByFids($uid, $fids,$type=0) {
		array_map( 'intval' , $fids);
        //$fids = is_array($fids) ? array_unique(array_map( 'intval' , $fids)) : array_unique(array_map( 'intval' , explode(',', $fids)));
        $_fids = is_array($fids) ? implode(',', $fids) : $fids;
//        $fids = array_unique(array_map( 'intval' , explode(',', $fids)));
//        $_fids = is_array($fids) ? implode(',', $fids) : $fids;
		if(empty($_fids)) {
			return array();
		}
		$follow_data = $this->where(" ( uid = '{$uid}' AND fid IN({$_fids})  AND type='{$type}' ) OR ( uid IN({$_fids}) and fid = '{$uid}' and type='{$type}' ) ")->findAll();
		$follow_states = $this->_formatFollowState($uid, $fids, $follow_data);
		return $follow_states[$uid];
	}

	/**
	 * 获取朋友列表数据 - 分页
	 * @param integer $uid 用户ID
	 * @return array 朋友列表数据
	 */
	public function getFriendsList($uid) {
		$data = D()->table('`'.$this->tablePrefix.'user_follow` AS a LEFT JOIN `'.$this->tablePrefix.'user_follow` AS b ON a.uid = b.fid AND b.uid = a.fid')
				   ->field('a.fid')
				   ->where('a.uid = '.$uid.' AND b.uid IS NOT NULL')
				   ->order('a.follow_id DESC')
				   ->findPage();

		return $data;
	}

	/**
	 * 获取朋友列表数据 - 不分页
	 * @param integer $uid 用户ID
	 * @return array 朋友列表数据
	 */
	public function getFriendsData($uid) {
		$data = D()->table('`'.$this->tablePrefix.'user_follow` AS a LEFT JOIN `'.$this->tablePrefix.'user_follow` AS b ON a.uid = b.fid AND b.uid = a.fid AND b.type=0')
				   ->field('a.fid')
				   ->where('a.uid = '.$uid.' AND b.uid IS NOT NULL')
				   ->findAll();

		return $data;
	}

	/**
	 * 获取所有关注用户数据
	 * @param integer $uid 用户ID
	 * @return array 所有关注用户数据
	 */
	public function getFollowingsList($uid) {
		$data = $this->field('fid,type')->where('uid='.$uid)->order('follow_id DESC')->findPage();
		return $data;
	}

	
	/**
	 * 获取所有关注用户数据
	 * @param integer $uid 用户ID
	 * @return array 所有关注用户数据
	 */
	public function getFollowingsCampusList($uid,$type,$limit = 10) {
		$limit = intval($limit) > 0 ? $limit : 10;
		if(!$type || empty($type)){
			$data = $this->field('fid,type')->where('uid='.$uid.' and type<>0 ' )->order('follow_id DESC')->findPage($limit);
		}else{
			$_map=array();
			$_map['uid'] = $uid;
			$_map['type'] = $type;
			$data = $this->field('fid,type')->where($_map)->order('follow_id DESC')->findPage($limit);
			unset($_map);
		}
		return $data;
	}
	
	
	/**
	 * 格式化，用户的关注数据
	 * @param integer $uid 用户ID
	 * @param array $fids 用户ID数组
	 * @param array $follow_data 关注状态数据
	 * @return array 格式化后的用户关注状态数据
	 */
	private function _formatFollowState($uid, $fids, $follow_data) {
		!is_array($fids) && $fids = explode(',', $fids);
		foreach($fids as $fid) {
			$follow_states[$uid][$fid] = array('following'=>0,'follower'=>0);
		}
		foreach($follow_data as $r_v) {
			if($r_v['uid'] == $uid) {
				$follow_states[$r_v['uid']][$r_v['fid']]['following'] = 1;
			} else if($r_v['fid'] == $uid) {
				$follow_states[$r_v['fid']][$r_v['uid']]['follower'] = 1;
			}
		}
		return $follow_states;
	}

	/**
	 * 更新关注数目
	 * @param integer $uid 操作用户ID
	 * @param array $fids 被操作用户ID数组
	 * @param boolean $inc 是否为加数据，默认为true
	 * @return void
	 */
	private function _updateFollowCount($uid, $fids, $inc = true) {
		!is_array($fids) && $fids = explode(',', $fids);
		$data_model = model('UserData');
		// 添加关注数
		$data_model->setUid($uid)->updateKey('following_count', count($fids), $inc);
		foreach($fids as $f_v) {
			// 添加粉丝数
			$data_model->setUid($f_v)->updateKey('follower_count', 1, $inc);
			$data_model->setUid($f_v)->updateKey('new_folower_count', 1, $inc);
		}
	}

	/*** API使用 ***/
	/**
	 * 获取指定用户粉丝列表，API使用
	 * @param integer $mid 当前登录用户ID
	 * @param integer $uid 指定用户ID
	 * @param integer $since_id 主键起始ID，默认为0
	 * @param integer $max_id 主键最大ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 页数ID，默认为1
	 * @return array 指定用户的粉丝列表数据
	 */
	public function getFollowerListForApi($mid, $uid, $since_id = 0, $max_id = 0, $limit = 20, $page = 1) {
		$uid = intval($uid);
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 
		$where = " fid = '{$uid}'";
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND follow_id > {$since_id}";
			!empty($max_id) && $where .= " AND follow_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$list = $this->where($where)->limit("{$start},{$end}")->order('follow_id DESC')->findAll();
		if(empty($list)) {
			return array();
		} else {
			$r = array();
			foreach($list as $key => $value) {
				$uid = $value['uid'];
				$fid = $value['fid'];
				$r[$key] = model('User')->formatForApi($value, $uid, $mid);
				unset($r[$key]['fid']);
			}
			return $r;
		}	
	}

	/**
	 * 获取指定用户关注列表，API使用
	 * @param integer $mid 当前登录用户ID
	 * @param integer $uid 指定用户ID
	 * @param integer $since_id 主键起始ID，默认为0
	 * @param integer $max_id 主键最大ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 页数ID，默认为1
	 * @return array 指定用户的关注列表数据
	 */
	public function getFollowingListForApi($mid, $uid, $since_id = 0, $max_id = 0, $limit = 20, $page = 1) {
		$uid = intval($uid);
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page); 
		$where = " uid = '{$uid}'";
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND follow_id > {$since_id}";
			!empty($max_id) && $where .= " AND follow_id < {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$list = $this->where($where)->limit("{$start},{$end}")->order('follow_id DESC')->findAll();
		if(empty($list)) {
			return array();
		} else {
			$r = array();
			foreach($list as $key => $value) {
				$uid = $value['fid'];
				$value['uid'] = $uid;
				$r[$key] = model('User')->formatForApi($value, $uid, $mid);
				unset($r[$key]['fid']);
			}
			return $r;
		}	
	}

	/**
	 * 获取指定用户的朋友列表，API专用
	 * @param integer $mid 当前登录用户ID
	 * @param integer $uid 指定用户ID
	 * @param integer $since_id 主键起始ID，默认为0
	 * @param integer $max_id 主键最大ID，默认为0
	 * @param integer $limit 结果集数目，默认为20
	 * @param integer $page 页数ID，默认为1
	 * @return array 指定用户的朋友列表
	 */
	public function getFriendsForApi($mid, $uid, $since_id = 0, $max_id = 0, $limit = 20, $page = 1)
	{
		$uid = intval($uid);
		$since_id = intval($since_id);
		$max_id = intval($max_id);
		$limit = intval($limit);
		$page = intval($page);
		$where = " a.uid = '{$uid}' AND b.uid IS NOT NULL";
		if(!empty($since_id) || !empty($max_id)) {
			!empty($since_id) && $where .= " AND a.follow_id > {$since_id}";
			!empty($max_id) && $where .= " AND a.follow_id > {$max_id}";
		}
		$start = ($page - 1) * $limit;
		$end = $limit;
		$list = D()->table('`'.$this->tablePrefix.'user_follow` AS a LEFT JOIN `'.$this->tablePrefix.'user_follow` AS b ON a.uid = b.fid AND b.uid = a.fid')
				   ->field('a.fid, a.follow_id')
				   ->where($where)
				   ->limit("{$start}, {$end}")
				   ->order('a.follow_id DESC')
				   ->findAll();

		if(empty($list)) {
			return array();
		} else {
			$r = array();
			foreach($list as $key => $value) {
				$uid = $value['fid'];
				$value['uid'] = $uid;
				$r[$key] = model('User')->formatForApi($value, $uid, $mid);
				unset($r[$key]['fid']);
			}
			return $r;
		}
	}


// ***************************************************ts2.XX  应用移动新增函数
	function getfollowList($uid){
		$list= $this->field('fid')->where("uid=$uid AND type=0")->findall();
		return $list;
	}
	
	
	/**
	 * 更新关注数目
	 * @param integer $uid 操作用户ID
	 * @param array $fids 被操作用户ID数组
	 * @param boolean $inc 是否为加数据，默认为true
	 * @return void
	 */
	private function _updateFollowCampusCount($uid, $fids, $inc = true,$type) {
		!is_array($fids) && $fids = explode(',', $fids);
		// 添加关注数
		$user_model = model('UserData');
		$data_model = D('ClassData','class');
	
		// 添加关注数
		$user_model->setUid($uid)->updateKey('following_count', count($fids), $inc);
		foreach($fids as $f_v) {
			// 添加粉丝数
			$data_model->updateKey('follower_count', 1, $inc,$f_v,$type);
			$data_model->updateKey('new_folower_count', 1, $inc,$f_v,$type);
		}
	}
	
	/**
	 * 更新名师工作室数据
	 */
	private function _updataFollowMsGroupCount($uid, $fids, $inc = true){
		!is_array($fids) && $fids = explode(',', $fids);
		$user_model = model('UserData');
		$data_model = D('MSGroupData','msgroup');
		// 添加关注数
		$user_model->setUid($uid)->updateKey('following_count', count($fids), $inc);
		foreach($fids as $f_v) {
			// 添加粉丝数
			$data_model->updateKey('follower_count', 1, $inc,$f_v);
			$data_model->updateKey('new_folower_count', 1, $inc,$f_v);
		}
	}
	
	/**
	 * @param $limit 获取数量
	 * 获取上周粉丝达人榜.
	 *   根据上周粉丝增加数逆序排列
	 */
	public function getFollowMasterList($limit=5){
		//上周日时间
		$sunday = strtotime('last Sunday');
		//上周一时间
		$monday = mktime(0,0,0,date('m',$sunday),date('d',$sunday)-6,date('Y',$sunday));
		$sql ="SELECT  tf.fid as uid, count(tf.follow_id) as counts	 FROM  `ts_user_follow` tf"
				." where tf.type =0 and  tf.ctime BETWEEN ".$monday." and ".$sunday." GROUP BY uid "
				."	order by counts DESC limit 0,".$limit.";";
		$list =$this->query($sql);
		$count = count($list);
		//如果粉丝达人榜不足5个，则按照总的粉丝数量排行补足
		if($count<5){
			$addlList=D('UserData')->field('uid')->where(' `key` ="follower_count"')->order("`value` DESC ")->limit('0,5')->select();
			$uidArr =getSubByKey($list,'uid');
			foreach($addlList as $val){
				if(!in_array($val['uid'], $uidArr)){
					array_push($list, array('uid'=>$val['uid']));
					$count++;
					if($count >=5){
						return $list;
					}
				}
			}
		}

		return $list;
	}
	
}
