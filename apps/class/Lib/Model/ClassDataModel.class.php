<?php
class ClassDataModel extends Model{ 
	protected $tableName = 'class_data';
	protected $fields = array(0=>'id',1=>'type',2=>'fid',3=>'key',4=>'value',5=>'mtime');

	
	/**
	 * 更新某个校园班级的指定Key值的统计数目
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
	public function updateKey($key, $nums, $add = true, $fid = '',$type) {
		if($nums == 0) {
			$this->error = L('PUBLIC_MODIFY_NO_REQUIRED');			// 不需要修改
			return false;
		}
		// 若更新数目小于0，则默认为减少数目
		$nums < 0 && $add = false;
		$key = t($key);
		// 获取当前设置用户的统计数目
		$data = $this->getClassDataByFid($fid,$key,$type);
		
		if(empty($data) || !$data) {
			$data = array();
			$data[$key] = $nums;
		} else {
			$data[$key] = $add ? ($data['value'] + abs($nums)) :($data['value'] - abs($nums));
		}
		$data[$key] < 0 && $data[$key] = 0;
		$map['fid'] = $fid;
		$map['key'] = $key;
		$this->where($map)->limit(1)->delete();
		$map['type'] = $type;
		$map['value'] = $data[$key];
		$map['mtime'] = date('Y-m-d H:i:s');
		$this->add($map);
		//设置缓存 	
		return $data;
	}
	
	/**
	 * 设置指定校园班级指定Key值的统计数目
	 * @param integer $fid 校园班级id 
	 * @param string $key Key值
	 * @param integer $value 设置的统计数值
	 * @return void
	 */
	public function setKeyValue($fid, $key, $value) {
		$map['fid'] = $fid;
		$map['key'] = $key;
		$this->where($map)->delete();
		$map['value'] = intval($value);
		$this->add($map);
	}
	
	public function getClassDataByFid($fid, $key,$type=1){
		$map['fid'] = $fid;
		if($key){
			$map['key'] = $key;
			$map['type'] = $type;
		}
		return $this->where($map)->find();
	}
	
	/**
	 * 
	 * @param  $type
	 * @param  $key
	 * @param  $order ASC DESC
	 * @param  $limit
	 */
	public function getClassList($type,$key,$order,$limit){
		$type = $type?$type:1;
		$key = empty($key)?'follower_count':$key;
		$limit = intval($limit) > 0 ? $limit : 10;
		$order = empty($order)?'`value`*1 DESC':'`value`*1 '.$order;
		$map['key'] = $key;
		$map['type'] = $type;
		// 粉丝列表
		$list = $this->where($map)->order($order)->findPage($limit);
		return $list;
	}
	
	public function getVisitorCount($fids){
		$_vcount = array();
		$key = 'visitor_count';
		foreach($fids as $fid){
			$_vcount[$fid] = $this->getClassDataByFid($fid, $key);
		}
		return $_vcount;
	}
}
?>