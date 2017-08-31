<?php
/**
 * @author yuliu2@iflytek.com
 * 常用邀请用户记录表Model
 */
class UserFavoriteModel extends Model {
	protected $tableName = 'user_favorite';
	protected $fields = array (
			0 => 'favorite_id',
			1 => 'uid',
			2 => 'fuid',
			3 => 'app_name',
			4 => 'invite_count',
			5 => 'ctime',
			'_autoinc' => true,
			'_pk' => 'favorite_id'
	);
	/**
	 * 获取常用用户列表
	 * @param int $uid 用户uid
	 * @param string $app_name 应用名称
	 * @param int $start 起始位置
	 * @param int $limit 偏移量
	 * @return array(array('fuid'=>'xxx'),...) 返回常用用户列表
	 */
	public function getFavoriteUsers($uid,$app_name,$start = 0,$limit = 20){
		if(intval($uid) <= 0 || empty($app_name) || intval($start) < 0 || intval($limit) <= 0){
			return array();
		}
		$map = array('uid'=>$uid,'app_name'=>$app_name);
		$field = 'fuid';
		$order = 'invite_count DESC';
		$limit = $start.','.$limit;
		$data = $this->where($map)->field ( $field )->order ( $order )->limit($limit)->findAll();
		return $data;
	}
	/**
	 * 获取常用用户记录总数
	 * @param int $uid 用户uid
	 * @param string $app_name 应用名称
	 * @return int 返回记录总数
	 */
	public function getFavoriteUsersCount($uid,$app_name){
		if(intval($uid) <= 0 || empty($app_name)){
			return 0;
		}
		$map = array('uid'=>$uid,'app_name'=>$app_name);
		$field = 'count(1) as cnt';
		$data = $this->where($map)->field ( $field )->find();
		$count = $data['cnt'];
		return $count;
	}
	/**
	 * 记录用户邀请
	 * @param int $uid 邀请用户
	 * @param array $fuids 被邀请用户列表
	 * @param string $app_name 应用名称
	 * @return int 成功返回大于0
	 */
	public function inviteUser($uid,$fuids,$app_name){
		if(intval($uid) <= 0 || empty($fuids) || !is_array($fuids) || empty($app_name)){
			return 0;
		}
		$map = array('uid'=>$uid,'app_name'=>$app_name);
		$result = $this->where($map)->field('uid,fuid,invite_count')->findAll();
		$existed_fuids = array();
		$new_fuids = array();
		foreach ($result as $invite){
			array_push($existed_fuids,$invite['fuid']);
		}
		$new_fuids = array_diff($fuids,$existed_fuids);
        $existed_fuids = array_intersect($fuids,$existed_fuids);
		if(!empty($new_fuids)){
			$map['ctime'] = time();
			$map['invite_count'] = 1;
			foreach($new_fuids as $fuid){
				$map['fuid'] = $fuid;
                intval($fuid) != $uid && $this->add($map);
			}
		}
		if(!empty($existed_fuids)){
			$sql = 'UPDATE __TABLE__ SET invite_count = invite_count + 1 WHERE uid = '.$uid.' AND app_name = "'.$app_name.'" AND fuid IN (';
			foreach($existed_fuids as $fuid){
				$sql .=$fuid.',';
			}
			$sql = trim($sql,',');
			$sql .=')';
			$this->execute($sql);
		}
		return 1;
	}	
}
?>