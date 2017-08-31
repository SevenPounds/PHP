<?php
/**
 * @package msgroup\Lib\Model
 * @author yuliu2@iflytek.com
 * 名师工作室Model
 */
class MSGroupModel extends Model {
	
	protected $tableName = 'msgroup';
	protected $member_tableName = 'msgroup_member';
	protected $data_tableName = 'msgroup_data';
	protected $user_tableName = 'user';
	
	protected $fields =
	 array( 
	 		0=>'gid',
			1=>'group_name',
			2=>'discription',
			3=>'creator_uid',
			4=>'ctime',
	 		5=>'isdel',
	 		6=>'subject',
	 		'_autoinc' => true,
	 		'_pk'=>'gid'
		);
	
	/**
	 * 获取名师工作室信息，用于展示动态
	 * @param int $gid
	 * @author yxxing
	 */
	public function getMSGroupInfoForFeed($gid){
		$msGroupInfo = S('msgroup_info_feed_'.$gid);
		if(empty($msGroupInfo)){
			$msGroup = $this->getMsGroupById($gid);
			
			$dAvatar = D('MSAvatar','msgroup');
			$params['app'] = 'msgroup';
			$params['rowid'] = intval($gid);
			$dAvatar->init($params);
			$msGroupAvatar = $dAvatar->getAvatar();
			
			$msGroupInfo = array();
			$msGroupInfo['cid'] = $msGroup['id'];
			$msGroupInfo['uname'] = $msGroup['group_name'];
			$msGroupInfo['type'] = 3;
			$msGroupInfo['space_url'] = U("msgroup/Index/index", array("gid"=>$gid));
			$msGroupInfo['space_link'] = "<a href='".$msGroupInfo['space_url']."' class='name' event-node='face_card' orgType='".$msGroupInfo['type']."' cid='".$msGroupInfo['cid']."'>".$msGroupInfo['uname']."</a>";
			$msGroupInfo['avatar_small'] = $msGroupAvatar['avatar_small'];
			S('msgroup_info_feed_'.$gid, $msGroupInfo, 3600);
		}
		return $msGroupInfo;
	}
	/**
	 * 创建名师工作室
	 * @API
	 */
	public function creatMSGoup($msgroup){
		$MSmap=array();
		$MSmap['group_name'] = $msgroup['group_name'];
		$MSmap['subject'] = $msgroup['subject'];
		$MSmap['discription'] = $msgroup['discription'];
		$MSmap['creator_uid'] = $msgroup['creator_uid'];//创建者uid
		$MSmap['ctime'] = time();//创建时间戳
		//msgroup添加
		$gid = $this->add($MSmap);
		if($gid){
			//msgroup_data添加
			$MSData = array();
			$MSData['gid'] = $gid ;
			$MSData['key'] = 'member_count' ;
			$MSData['value'] = 1 ;
			$MSData['mtime'] = $MSmap['ctime'];
			M($this->data_tableName)->add($MSData);

			$MSData['key'] = 'follower_count' ;
			$MSData['value'] = 0 ;
			M($this->data_tableName)->add($MSData);

			$MSData['key'] = 'visitor_count' ;
			$MSData['value'] = 0 ;
			M($this->data_tableName)->add($MSData);

			//msgroup_member添加
			$MsMemberMap = array(
					'gid'=>$gid,
					'uid'=>$MSmap['creator_uid'],
					'level'=>3,
					'ctime'=>$MSmap['ctime']
					);
			$_res = D('MSGroupMember','msgroup')->add($MsMemberMap);
		}
		return $gid;
	}

	/**
	 * 名师工作室资料修改
	 * @author zqxiang@iflytek.com
	 * @API
	 * 
	 * @param int $gid
	 * @param string $group_name
	 * @param string $subject
	 * @param string $discription
	 * @param int $creator_uid
	 */
	public function update($gid, $group_name,$discription,$subject,$creator_uid = ''){
		if(intval($gid) < 1 || empty($group_name) ||  empty($subject) ) {
			return false;
		}
		$MSmap=array();
		$MSmap['gid'] = intval($gid);
		$MSmap['subject'] = $subject;
		$MSmap['group_name'] = $group_name;
		$MSmap['discription'] = $discription;
		if(intval($creator_uid) > 0){
			$MSmap['creator_uid'] = intval($creator_uid);
		}
		return $this->save($MSmap);
	}
	
	/**
	 * 判断用户等级
	 * @param int $gid
	 * @param int $uid 
	 * @param int $level 用户等级 '1：成员，2：管理员，3：创建者'
	 */
	public function hasLevel($gid,$uid,$level){
		if(!$gid ||!$uid ||!$level){
			return false;
		}
		$condition=array();
		$condition['ms.`gid`'] = intval($gid);
		$condition['mem.`uid`'] = intval($uid);
		$condition['mem.`level`'] = intval($level);
		$join = 'LEFT JOIN '.$this->tablePrefix.$this->member_tableName.' mem ON ms.`gid` = mem.`gid`';
		$tables = $this->tablePrefix.$this->tableName.' ms ';
		$res = $this->table($tables)->where($condition)->join($join)->find();
 		if(empty($res)){
 			return false;
 		}
 		return $res;
	}
	
	/**
	 * 获取名师工作室列表
	 * @param array $tempcondition,	$tempcondition['isdel'];
	 * 								$tempcondition['group_name'];
	 * 								$tempcondition['creator_uname'];
	 * @param string $order
	 * @param int $limit
	 */
	public function listMsGroups($tempcondition, $order, $limit){
		if(!$order){
			$order = 'ms.`ctime` DESC';
		}
		$condition=array();
		$condition['ms.`isdel`']=isset($tempcondition['isdel'])?$tempcondition['isdel']:0;
		$condition['md.`key`'] = 'member_count';
		if(isset($tempcondition['group_name'])){
			$condition['ms.`group_name`']=array('like','%'.$tempcondition['group_name'].'%');
		}
		if(isset($tempcondition['creator_uname'])){
			$condition['u.`uname`']=array('like','%'.$tempcondition['creator_uname'].'%');
		}

		$join =  'LEFT JOIN '.$this->tablePrefix.$this->user_tableName.' u ON ms.`creator_uid` = u.`uid`';
		$tables = $this->tablePrefix.$this->tableName.' ms ';
		//名师工作室id，名师工作室名称，创建者uid，创建者真实姓名，创建时间，成员人数
		$fields = 'ms.`gid`,ms.`group_name`,ms.`creator_uid`,u.`uname` creator_uname,ms.`ctime`,md.`value` member_count';
		$joinCount = 'LEFT JOIN '.$this->tablePrefix.$this->data_tableName.' md ON ms.`gid` = md.`gid`';
		$res = $this->field($fields)->table($tables)->where($condition)->join($join)->join($joinCount)->order($order)->limit($limit)->select();
		return $res;
	}
	
	
	/**
	 * 获取单个名师工作室
	 * @param int $gid
	 */
	public function getMsGroup($gid){
		if(!$gid){
			return null;
		}
		$joinUser = 'LEFT JOIN '.$this->tablePrefix.$this->user_tableName.' u ON ms.`creator_uid` = u.`uid`';
		$fields = 'ms.*, u.uname as creator_rname, u.login as creator_uname';
		$result = $this->field($fields)->table($this->tablePrefix.$this->tableName . ' ms')->join($joinUser)->where('ms.isdel = 0 and ms.gid=\''.$gid.'\'')->find();
		return $result;
	}
	
	/**
	 * 获取多个工作室详细信息
	 * 
	 * @param array $gids
	 * 
	 * @return array
	 */
	public function getMsGroupList($gids) {
		$tmp = array();
		if (!is_array($gids) || count($gids) == 0) return $tmp;
		$gids = implode(', ', array_unique($gids));
		
		$sql = 'select m.gid,m.discription, m.creator_uid, m.group_name, fcount.`value` as follower_count, vcount.`value` as visitor_count';
		$sql .= ' from ' . $this->tablePrefix . $this->tableName . ' as m';
		$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' fcount on m.gid = fcount.gid and fcount.`key` = \'follower_count\''; //关注数
		$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' vcount on m.gid = vcount.gid and vcount.`key` = \'visitor_count\''; //访问数
		$sql .= ' where m.isdel = 0 and m.gid in (' . $gids . ')';
		$dataList = $this->query($sql);
		if (!empty($dataList)) {
			$dAvatar = D('MSAvatar','msgroup');
			$params['app'] = 'msgroup';
			foreach ($dataList as $k =>$r){
				$params['rowid'] = intval($r['gid']);
				$dAvatar->init($params);
				$r['image'] = $dAvatar->getAvatar(); //获得工作室头像
				$tmp[$r['gid']] = $r;
			}
		}
		
		return $tmp;
	}
	
	/**
	 * 删除名师工作室
	 * @param array $ids  or int
	 */
	public function delMsGroup($ids){
		if(is_array($ids)){
			$idstr = implode(",",$ids);
		}else{
			$idstr = $ids;
		}
		
		$map['gid']  = array('in',"$idstr");
		return M($this->tableName)->where($map)->save(array('isdel'=>1));
	}

	/**
	 * 获取用户加入的名师工作室
	 * 
	 * @param string $login_name 用户登录名
	 *
	 * @return array
	 */
	function getMsGroupByLoginName($login_name) {
		/**
		 * 此接口不赞成广泛使用
		 * 原因：使用login_name查询效率较低
		 * 推荐：使用getMsGroupByUid接口
		 */
		$m = M("$this->user_tableName u, $this->tablePrefix$this->member_tableName a, $this->tablePrefix$this->tableName m");
		$joinCreator = 'LEFT JOIN '.$this->tablePrefix . $this->user_tableName.' c ON m.`creator_uid` = c.`uid`';
		$fields = 'DISTINCT m.gid, m.*, c.uname as creator_rname, c.login as creator_uname';
		$condition = 'm.isdel = 0 and ((m.gid = a.gid	AND	a.uid = u.uid) OR m.creator_uid = u.uid) AND u.login = \'' . $login_name . '\'';
		$dataList = $m->join($joinCreator)->field($fields)->where($condition)->select();
		if (!empty($dataList)) {
			$dAvatar = D('MSAvatar', 'msgroup');
			$params['app'] = 'msgroup';
			foreach ($dataList as &$r){
				$params['rowid'] = intval($r['gid']);
				$dAvatar->init($params);
				$r['image'] = $dAvatar->getAvatar(); //获得工作室头像
			}
		}
		
		return $dataList;
	}
	
	/**
	 * 获取用户加入的名师工作室
	 * 
	 * @param int $uid 用户uid
	 * 
	 * @return array
	 */
	function getMsGroupByUid($uid) {
		$uid = intval($uid);
		if ($uid == '0') return array();
		
		$tbl = M("{$this->member_tableName} a, {$this->tablePrefix}{$this->tableName} m");
		$joinCreator = 'LEFT JOIN '.$this->tablePrefix . $this->user_tableName.' c ON m.`creator_uid` = c.`uid`';
		$fields = 'DISTINCT m.gid, m.*, c.uname as creator_rname, c.login as creator_uname';
		$condition = 'm.isdel = 0 and ((m.gid = a.gid AND a.uid = \'' . $uid . '\') OR m.creator_uid = \'' . $uid . '\')';
		
		$dataList = $tbl->join($joinCreator)->field($fields)->where($condition)->select();

		if (!empty($dataList)) {
			$dAvatar = D('MSAvatar', 'msgroup');
			$params['app'] = 'msgroup';
			foreach ($dataList as &$r){
				$params['rowid'] = intval($r['gid']);
				$dAvatar->init($params);
				$r['image'] = $dAvatar->getAvatar(); //获得工作室头像
			}
		}
		
		return $dataList;
	}
	
	/**
	 * 获得用户加入的工作室gid
	 * 
	 * @param int $uid 用户uid
	 * 
	 * @return array
	 */
	function getGidByUid($uid) {
		$uid = intval($uid);
		if ($uid == '0') return array();
		$result = $this->table("{$this->tablePrefix}msgroup_member AS mem, {$this->tablePrefix}msgroup AS ms")->where('ms.isdel = 0 and mem.gid = ms.gid AND (ms.creator_uid = \''.$uid.'\' OR mem.uid = \''.$uid.'\')')->Distinct(true)->field('ms.gid')->findAll();
		foreach ($result AS $gid) {
			$gids[] = intval($gid['gid']);
		}
		
		return $gids;
	}
	
	/**
	 * 获取名师工作室详细信息
	 * @param int $id 名师工作室id
	 * 
	 * @return array
	 */
	function getMsGroupById($id) {
		$data = $this->getMsGroup($id);
		if (!empty($data)) {
			//获得成员数、关注者数、访问量
			$data = array_merge($data, array('member_count'=>0, 'follower_count'=>0, 'visitor_count'=>0)); //member_count：成员数 follower_count：关注者数 visitor_count：访问量
			$countData = M($this->data_tableName)->where(array('gid'=>$id))->select();
			foreach ($countData as $c) {
				if ($c['key'] == 'member_count') $data['member_count'] = $c['value'];
				if ($c['key'] == 'follower_count') $data['follower_count'] = $c['value'];
				if ($c['key'] == 'visitor_count') $data['visitor_count'] = $c['value'];
			}
			//获取工作室头像
			$dAvatar = D('MSAvatar', 'msgroup');
			$params['app'] = 'msgroup';
			$params['rowid'] = intval($data['gid']);
			$dAvatar->init($params);
			$data['image'] = $dAvatar->getAvatar(); //获得工作室头像
		}

		return $data;
	}
	
	/**
	 * 名师工作室检索总数（根据学科、工作室名称、创建者名称检索）
	 * @param $conditions 检索条件（仅支持array('subject'=>, 'group_name'=>, 'uname'=>)）
	 * @return int
	 */
	function searchMsGroupCount($conditions) {
		$sql = 'select count(1) as count from ' . $this->tablePrefix . $this->tableName. ' as m';
		$jw = '';
		//如果条件中有学科或用户姓名
		if (!empty($conditions['subject'])) $jw .= ' and u.subject = \''.$conditions['subject'].'\'';
		if (!empty($conditions['uname'])) $jw .= ' and u.uname = \''.$conditions['uname'].'\'';
		$sql .= ' inner join ' . $this->tablePrefix . 'user u on m.creator_uid = u.uid ' . $jw;
		$sql .= ' where m.isdel = 0';
		if (!empty($conditions['group_name'])) $sql .= ' and m.group_name like \'%'.$conditions['group_name'].'%\'';	//如果条件中有工作室名称
		
		$data = $this->query($sql);
		
		return !empty($data[0]['count']) ? $data[0]['count'] : 0;
	}
	
	/**
	 * 名师工作室检索（根据学科、工作室名称、创建者名称检索）
	 * @param $conditions 检索条件（仅支持array('subject'=>, 'group_name'=>, 'uname'=>)）
	 * @param int $page
	 * @param int $limit
	 * @param string $order 关注量、访问量排序字符串(例：$order  = 'visitor_count desc/follower_count desc/member_count desc')
	 * @return mixed
	 */
	function searchMsGroup($conditions, $page = 1, $limit = 10, $order = '') {
		$sql = 'select m.gid, m.creator_uid, m.group_name, ucount.`value` as member_count, fcount.`value` as follower_count, vcount.`value` as visitor_count ,u.uname as creator_uname, m.ctime';
		$sql .= ' from ' . $this->tablePrefix . $this->tableName . ' as m';
		$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' ucount on m.gid = ucount.gid and ucount.`key` = \'member_count\''; //成员数
		$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' fcount on m.gid = fcount.gid and fcount.`key` = \'follower_count\''; //关注数
		$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' vcount on m.gid = vcount.gid and vcount.`key` = \'visitor_count\''; //访问数
		
		$jw = '';
		//如果条件中有学科或用户姓名
		if (!empty($conditions['subject'])) $jw .= ' and u.subject = \''.$conditions['subject'].'\'';
		if (!empty($conditions['uname'])) $jw .= ' and u.uname = \''.$conditions['uname'].'\'';
		$sql .= ' inner join ' . $this->tablePrefix . 'user u on m.creator_uid = u.uid ' . $jw;

		$sql .= ' where m.isdel = 0';
		if (!empty($conditions['group_name'])) $sql .= ' and m.group_name like \'%'.$conditions['group_name'].'%\'';	//如果条件中有工作室名称
		
		if (!empty($order)) {
			$sql .= ' order by ' . $order;
		}else{
			$sql .= ' order by visitor_count desc,member_count desc ';
		}
		
		$sql .= ' limit ' . (($page-1) * $limit) . ',' . $limit;
		
		$dataList = $this->query($sql);
		if (!empty($dataList)) {
			$dAvatar = D('MSAvatar','msgroup');
			$params['app'] = 'msgroup';
		}
		foreach ($dataList as &$r){
			$params['rowid'] = intval($r['gid']);
			$dAvatar->init($params);
			$r['image'] = $dAvatar->getAvatar(); //获得工作室头像
		}
		
		return $dataList;
	}
	
	/**
	 * 获取最活跃、最受欢迎、成员最多的名师工作室
	 * @param string $type (最活跃：visitor；最受欢迎：follower；成员最多：member)
	 * @param int $limit
	 * @return mixed
	 */
	function getHotDataList($type = 'visitor', $limit = 5) {
		if ($type == 'visitor') {
			$sql = 'select m.gid, m.creator_uid, m.group_name, vcount.`value` as count from ' . $this->tablePrefix . $this->tableName . ' as m';
			$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' vcount on m.gid = vcount.gid and vcount.`key` = \'visitor_count\''; //访问数
		} else if ($type == 'follower') {
			$sql = 'select m.gid, m.creator_uid, m.group_name, fcount.`value` as count from ' . $this->tablePrefix . $this->tableName . ' as m';
			$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' fcount on m.gid = fcount.gid and fcount.`key` = \'follower_count\''; //关注数
		} else {
			$sql = 'select m.gid, m.creator_uid, m.group_name, ucount.`value` as count from ' . $this->tablePrefix . $this->tableName . ' as m';
			$sql .= ' left join ' . $this->tablePrefix . $this->data_tableName . ' ucount on m.gid = ucount.gid and ucount.`key` = \'member_count\''; //成员数
		}			
		$sql .= ' where m.isdel = 0 order by count desc limit ' . $limit;
		
		$dataList = $this->query($sql);
		if (!empty($dataList)) {
			$dAvatar = D('MSAvatar', 'msgroup');
			$params['app'] = 'msgroup';
		}
		foreach ($dataList as &$r){
			$params['rowid'] = intval($r['gid']);
			$dAvatar->init($params);
			$r['image'] = $dAvatar->getAvatar(); //获得工作室头像
		}
		
		return $dataList;
	}
	
	
	/**
	 * 获取solr更新名师工作室的全部数据
	 * @param int $gid
	 *
	 * @return array
	 */
	public function getMsgroupOnSolr($gid) {
		$join = 'LEFT JOIN '.$this->tablePrefix.$this->data_tableName.' d ON ms.`gid` = d.`gid` and d.`key` = \'visitor_count\'';
		$group = $this->table($this->tablePrefix.$this->tableName . ' ms')->join($join)->where('ms.isdel = 0 and ms.gid=\''.$gid.'\'')->field('ms.*, d.`value` as vcount')->find();
		$tmp = array();
		if (!empty($group)) {
			$user = M('User')->getUserInfo($group['creator_uid']); //从缓存拿到用户的基本信息
			
			$tmp['zoneid'] = $group['gid'];
			$tmp['zonename'] = $group['group_name'];
			$tmp['username'] = $user['uname'];
			$tmp['subject'] = array($group['subject']);
			$tmp['province'] = empty($user['province']) ? 340000 : $user['province'];
			$tmp['city'] = 0;
			$tmp['county'] = 0;
			$tmp['school'] = 0;
			$tmp['weibocount'] = intval($group['vcount']);
			$tmp['datetime'] = $group['ctime'];
		}
		return $tmp;
	}
}
?>
