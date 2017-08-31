<?php
/**
 * 用户统计数据模型 - 数据对象模型
 * @author jason <yangjs17@yeah.net> 
 * @version TS3.0
 */
class UserDataModel extends Model {

	protected $tableName = 'user_data';
	protected $fields = array(0=>'id',1=>'uid',2=>'key',3=>'value',4=>'mtime');
	protected $uid = '';
	
	/**
	 * 初始化方法，设置默认用户信息
	 * @return void
	 */
	public function _initialize() {
		$this->uid = $GLOBALS['ts']['mid'];
	}

	/**
	 * 设置用户UID
	 * @param integer $uid 用户UID
	 * @return object 用户统计数据对象
	 */
	public function setUid($uid) {
		$this->uid = $uid;
		return $this;
	}

	/**
	 * 更新某个用户的指定Key值的统计数目
	 * Key值：
	 * feed_count：微博总数
	 * weibo_count：微博数
	 * favorite_count：收藏数
	 * following_count：关注数
	 * follower_count：粉丝数
	 * unread_comment：评论未读数
	 * unread_atme：@Me未读数
	 * @param string $key Key值
	 * @param integer $nums 更新的数目
	 * @param boolean $add 是否添加数目，默认为true
	 * @param integer $uid 用户UID
	 * @return array 返回更新后的数据
	 */
	public function updateKey($key, $nums, $add = true, $uid = '') {
		if($nums == 0) {
			$this->error = L('PUBLIC_MODIFY_NO_REQUIRED');			// 不需要修改
			return false;
		}
		// 若更新数目小于0，则默认为减少数目
		$nums < 0 && $add = false;
		$key = t($key);
		// 获取当前设置用户的统计数目
		$data = $this->getUserData($uid, false);
		if(empty($data) || !$data) {
			$data = array();	
			$data[$key] = $nums;
		} else {
			$data[$key] = $add ? ($data[$key] + abs($nums)) :($data[$key] - abs($nums));
		}

		$data[$key] < 0 && $data[$key] = 0;
		$uid = empty($uid) ? $this->uid : $uid;
		$query = array('uid'=>$uid,'key'=>$key);
		$resu = $this->where($query)->select();
		$map['uid'] = $uid;
		$map['value'] = $data[$key];
		$map['mtime'] = date('Y-m-d H:i:s'); 
		if(count($resu) > 0){
			$this->where($query)->save($map);
		}else{
			$map['key'] = $key;
			$this->add($map);
		}
		model('Cache')->rm('UserData_'.$map['uid']);
		return $data;
	}
	
	/**
	 * 设置指定用户指定Key值的统计数目
	 * @param integer $uid 用户UID
	 * @param string $key Key值
	 * @param integer $value 设置的统计数值
	 * @return void
	 */
	public function setKeyValue($uid, $key, $value) {
		$map['uid'] = $uid;
		$map['key'] = $key;
		$this->where($map)->delete();
		$map['value'] = intval($value);
		$this->add($map);
		// 清掉该用户的缓存
		model('Cache')->rm('UserData_'.$uid);
	}
	
	/**
	 * 获取指定用户的统计数据
	 * @param integer $uid 用户UID
	 * @param boolean fromCache 是否从缓存读，默认为true
	 * @return array 指定用户的统计数据
	 */
	public function getUserData($uid = '', $fromCache=true) {
		// 默认为设置的用户
		if(empty($uid)) {
			$uid = $this->uid;
		}
		if($fromCache){
			if(($data = model('Cache')->get('UserData_'.$uid)) === false || count($data) == 1) {
				$data = $this->getUserDataNoCache($uid);
				model('Cache')->set('UserData_'.$uid, $data, 60);
			}
		} else {
			$data = $this->getUserDataNoCache($uid);
		}
		return $data;
	}
	
	/**
	 * 获取指定用户的统计数据，从数据库读
	 * @param integer $uid 用户UID
	 * @return array 指定用户的统计数据
	 */
	public function getUserDataNoCache($uid){
		$map['uid'] = $uid;
		$data = array();
		$list = $this->where($map)->findAll();
		if(!empty($list)) {
			foreach($list as $v) {
				$data[$v['key']] = (int)$v['value'];
			}
		}
		return $data;
	}
	
	/**
	 * 批量获取多个用户的统计数目
	 * @param array $uids 用户UID数组
	 * @return array 多个用户的统计数目
	 */
	public function getUserDataByUids($uids) {
		$return = $notCache = array();
		$data = model('Cache')->getList('UserData_', $uids);
		// 判断是否存在没有缓存的数据
		foreach($uids as $k => $v) {
			if(!isset($data[$v]) || empty($data[$v])) {
				$notCache[] = $v; 
			}
		}
		// 如果存在没有缓存的数据，获取缓存数据，在进行排序
		if(!empty($notCache)) {
			foreach($notCache as $v) {
				$data[$v] = $this->getUserData($v);
			}
			// 重新排序
			foreach($uids as $v){
				$return[$v] = $data[$v];
			}
			return $return;
		} else {
			return $data;
		}
	}
	public function getUserKeyDataByUids( $key = "weibo_count" , $uids){
		$map['uid'] = array( 'in' , $uids );
		$map['key'] = $key;
		$list = $this->where($map)->field("uid,value")->findAll();
		$rearray = array();
		foreach ( $list as $v ){
			$rearray[$v['uid']][$key] = $v['value'];
		}
		return $rearray;
	}
	/**
	 * 手动统计更新用户数据，微博、关注、粉丝、收藏
	 * @return void
	 */
	public function updateUserData() {
		set_time_limit(0);
		// 总微博数目和未假删除的微博数目
		$sql = 'SELECT uid, count(feed_id) as total, SUM(is_del) as delSum FROM '.C('DB_PREFIX').'feed GROUP BY uid';
		$list = M()->query($sql);
		foreach ($list as $vo){
			$res[$vo['uid']]['feed_count'] = intval($vo['total']);
			$res[$vo['uid']]['weibo_count'] = $res[$vo['uid']]['feed_count'] - intval($vo['delSum']);
		}
		// 收藏数目
		$sql = 'SELECT uid, count(collection_id) as total FROM '.C('DB_PREFIX').'collection GROUP BY uid';
		$list = M()->query($sql);
		foreach ($list as $vo){
			$res[$vo['uid']]['favorite_count'] = intval($vo['total']);
		}
		// 关注数目
		$sql = 'SELECT uid, count(follow_id) as total FROM '.C('DB_PREFIX').'user_follow GROUP BY uid';
		$list = M()->query($sql);
		foreach ($list as $vo){
			$res[$vo['uid']]['following_count'] = intval($vo['total']);
		}
		// 粉丝数目
		$sql = 'SELECT fid, count(follow_id) as total FROM '.C('DB_PREFIX').'user_follow GROUP BY fid';
		$list = M()->query($sql);
		foreach ($list as $vo){
			$res[$vo['fid']]['follower_count'] = intval($vo['total']);
		}
		
		$uids = array_keys($res);
		if(empty($uids)){
			return false;
		}
		
		$map['uid'] = array('in', $uids);
		$map['key'] = array('in', array('feed_count', 'weibo_count', 'favorite_count', 'following_count', 'follower_count'));
		$this->where($map)->delete();
		
		foreach($res as $uid=>$vo){
			$data['uid'] = $uid;
			foreach($vo as $key=>$val){
				$data['key'] = $key;
				$data['value'] = intval($val);
				$this->add($data);
			}
			
			// 清掉该用户的缓存
			model('Cache')->rm('UserData_'.$uid);			
		}
		
	
	}	
	
	
	
	public function getActiveMasterList($limit=5,$startTime,$endTime){
		//开始时间
		if(empty($endTime)){
			$endTime=strtotime('last Sunday');
		}
		//结束时间
		if(empty($startTime)){
			$startTime =mktime(0,0,0,date('m',$endTime),date('d',$endTime)-6,date('Y',$endTime));
		}
		$sql="SELECT t4.uid as uid,COUNT(t4.uid) as counts FROM ( SELECT uid FROM `ts_comment` WHERE ctime BETWEEN ".$startTime." AND ".$endTime
			."	UNION ALl SELECT t3.uid FROM(SELECT post_userid as uid FROM `ts_research_post` WHERE createtime BETWEEN ".$startTime." AND ".$endTime
			."	UNION ALL SELECT t2.uid FROM (SELECT ta.uid FROM `ts_onlineanswer_answer` ta WHERE ta.ctime BETWEEN ".$startTime." AND ".$endTime
			."	UNION ALL SELECT t1.uid FROM (SELECT uid FROM `ts_vote_post` WHERE ctime BETWEEN ".$startTime." and ".$endTime
			."	UNION All SELECT uid FROM `ts_pingke_post` WHERE ctime BETWEEN ".$startTime." and ".$endTime."  ) t1)t2)t3)t4 GROUP BY uid LIMIT 0,".$limit.";";
		
		$list =$this->query($sql);
		$count = count($list);
		//如果活跃达人榜不足5个，则按照总的积分数量排行补足
		if($count<5){
			$addlList=M ( 'credit_user' )->field('uid')->order("`score` DESC ")->limit('0,5')->select();
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
