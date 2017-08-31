<?php
/**
 * @package msgroup\Lib\Model
 * @author yuliu2@iflytek.com
 * 加入“申请”数据模型
 */
class MSGroupApplicationModel extends Model {
	protected $tableName = 'msgroup_application';
	
	function  _initialize() {
		parent::_initialize();
	}
	
	
	/**
	 * 获取成员申请列表
	 * @param int $gid 工作室ID
	 * @param int $skip
	 * @param int $limit
	 * @param string $order
	 * 
	 * @return array
	 */
	public function getApplicationList($gid, $skip = 0, $limit = 20, $order = 'ctime desc') {
		$dataList = array();
		//	工作室id验证
		if (empty($gid)) return $dataList;
		
		$map = array();
		$map['m.gid'] = intval($gid);
		$map['m.status'] = 0;	//status=0为申请未处理状态
		$joinUser = 'LEFT JOIN '.$this->tablePrefix.user.' u ON m.uid = u.uid';
		$fields = 'm.*, u.uname as real_name, u.login as login_name';
		$dataList = M($this->tableName . ' m')->field($fields)->join($joinUser)->where($map)->order($order)->limit("$skip, $limit")->select();
		//	从网关获取成员扩展信息
		foreach ($dataList as $k=>$member) {
			$info = model('CyUser')->getCyUserInfo($member['login_name']);
			if (!empty($info)) {
				$org = current(current($info['orglist']));
				$dataList[$k]['org_name'] = $org['name'];
				//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个
				$roleEnName = D("UserLoginRole")->getUserCurrentRole($info['user']['login'], $info['rolelist']);
				$dataList[$k]['role_name'] = UserRoleTypeModel::getCNRoleName($roleEnName);
			}
		}
		
		return $dataList;
	}
	
	/**
	 * 忽略用户的申请
	 * 
	 * @param int $id 申请者id
	 * @param int $gid 组id
	 * @param int $handle_uid 处理人uid
	 * 
	 * @return boolean
	 */
	public function applicationDenied($id, $gid, $handle_uid) {
		if (empty($id) || empty($gid) || empty($handle_uid)) return false;
		
		$map = array();
		$map['id'] = intval($id);
		$map['gid'] = intval($gid);
		$map['status'] = 0;
		$data = array();
		$data['status'] = 2;
		$data['mtime'] = time();
		$data['handle_uid'] = $handle_uid;
		$update = M($this->tableName)->where($map)->save($data);
		if ($update) return true;
		return false;
	}
	
	/**
	 * 接受用户的申请
	 * @param int $id 申请者id
	 * @param int $gid 组id
	 * @param int $handle_uid 处理人uid
	 */
	public function applicationApply($id, $gid, $handle_uid) {
		if (empty($id) || empty($gid) || empty($handle_uid)) return false;
		$gid = intval($gid);
		
		//	更新ts_msgroup_application表
		$map = array();
		$map['id'] = intval($id);
		$map['gid'] = $gid;
		$map['status'] = 0;
		$data = array();
		$data['status'] = 1;
		$data['mtime'] = time();
		$data['handle_uid'] = $handle_uid;
		$update = M($this->tableName)->where($map)->save($data);
		if ($update != true) return false;
		
		//	获得申请者uid、角色等信息
		$user = M($this->name)->where(array('id'=>$map['id']))->select();
		if (empty($user[0]['uid'])) return false;
		
		//	添加申请者数据到ts_msgroup_member表
		$map = array();
		$map['gid'] = $gid;
		$map['uid'] = intval($user[0]['uid']);
		$map['level'] = 1;
		$map['ctime'] = time();
		$add = M('msgroup_member')->add($map);
		if ($add) {
			//更新msgroup_data表的统计字段
			$memberData = M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->find();
			if (empty($memberData)) {
				M('msgroup_data')->add(array('gid'=>$gid, 'key'=>'member_count', 'value'=>1, 'mtime'=>time()));
			} else {
				M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->save(array('value' => array('exp', 'value+1'), 'mtime'=>time()));
			}
			return true;
		}
		return false;
	}
}
?>