<?php
/**
 * @package msgroup\Lib\Model
 * @author cheng
 * 名师工作室成员Model
 */
class MSGroupDataModel extends Model{
	
	protected $tableName = 'msgroup_data';
	protected $fields = array(0=>'id',1=>'gid',2=>'key',3=>'value',4=>'mtime');
	
	/**
	 * 更新某个校园班级的指定Key值的统计数目
	 * Key值：
	 * follower_count：粉丝数
	 * visitor_count：访问量
	 * @param string $key Key值
	 * @param integer $nums 更新的数目
	 * @param boolean $add 是否添加数目，默认为true
	 * @param integer $gid 用户UID
	 * @return array 返回更新后的数据
	 */
	public function updateKey($key, $nums, $add = true, $gid = '') {
		if($nums == 0) {
			$this->error = L('PUBLIC_MODIFY_NO_REQUIRED');			// 不需要修改
			return false;
		}
		// 若更新数目小于0，则默认为减少数目
		$nums < 0 && $add = false;
		$key = t($key);
		// 获取当前设置用户的统计数目
		$data = $this->getDataByGid($gid,$key);
	
		if(empty($data) || !$data) {
			$data = array();
			$data[$key] = $nums;
		} else {
			$data[$key] = $add ? ($data['value'] + abs($nums)) :($data['value'] - abs($nums));
		}
		$data[$key] < 0 && $data[$key] = 0;
		$map['gid'] = $gid;
		$map['key'] = $key;
		$this->where($map)->limit(1)->delete();
		$map['value'] = $data[$key];
		$map['mtime'] = time();
		$this->add($map);
		//设置缓存
		return $data;
	}
	
	
	/**
	 * 设置名师工作室指定Key值的统计数目
	 * @param integer $gid 名师工作室id
	 * @param string $key Key值
	 * @param integer $value 设置的统计数值
	 * @return void
	 */
	public function setKeyValue($gid, $key, $value) {
		$map['gid'] = $gid;
		$map['key'] = $key;
		$this->where($map)->delete();
		$map['value'] = intval($value);
		$this->add($map);
	}
	
	/**
	 * 获取名师工作室的值
	 * @param inetger $gid
	 * @param string $key
	 */
	public function getDataByGid($gid, $key){
		$map['gid'] = $gid;
		if($key){
			$map['key'] = $key;
		}
		return $this->where($map)->find();
	}
	
	
}
