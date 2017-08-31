<?php
/**
 * @package msgroup\Lib\Model
 * @author yuliu2@iflytek.com
 * 名师工作室成员Model
 */
class MSGroupMemberModel extends Model {
	protected $tableName = 'msgroup_member';
	protected $tableDataName = 'msgroup_data';
	protected $userTableName = 'user';
	
	function  _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 工作室成员查询结果总数
	 *
	 * @param int $gid 工作室ID
	 * @param string $login_name	账号
	 * @param string $real_name	姓名
	 * @param boolean $getAll 为false时则不抽取创建者
	 *
	 * @return int
	 */
	public function getMemberListCount($gid, $login_name = null, $real_name = null, $getAll = true) {
		//	工作室id验证
		if (empty($gid)) return 0;
		
		$map = array();
		$map['m.gid'] = intval($gid);
		if ($getAll === false) {
			$map['m.level'] = array('neq', 3);
		}
		$joinUser = "inner join $this->tablePrefix$this->userTableName u on m.uid = u.uid";
		if (!empty($login_name)) $joinUser .= " and u.login like '%$login_name%'";
		if (!empty($real_name)) $joinUser .= " and u.uname like '%$real_name%'";
		return M($this->tableName . ' m')->join($joinUser)->where($map)->count();
	}
	
	/**
	 * 工作室成员查询
	 * 
	 * @param int $gid 工作室ID
	 * @param string $login_name	账号
	 * @param string $real_name	姓名
	 * @param int $skip
	 * @param int $limit
	 * @param string $order
	 * @param boolean $sync 是否从cycore取用户信息， 默认不取
	 * @param boolean $getAll 为false时则不抽取创建者
	 * 
	 * @return array
	 */
	public function getMemberList($gid, $login_name = null, $real_name = null, $order = null, $skip = 0, $limit = 20, $sync = false, $getAll = true) {
		$dataList = array();
		//	工作室id验证
		if (empty($gid)) return $dataList;
		
		$map = array();
		$map['m.gid'] = intval($gid);
		if ($getAll === false) {
			$map['m.level'] = array('neq', 3);
		}
		
		$joinUser = "inner join $this->tablePrefix$this->userTableName u on m.uid = u.uid";
		if (!empty($login_name)) $joinUser .= " and u.login like '%$login_name%'";
		if (!empty($real_name)) $joinUser .= " and u.uname like '%$real_name%'";
		$fields = 'm.*, u.uname as real_name, u.login as login_name';
		$dataList = M($this->tableName . ' m')->field($fields)->join($joinUser)->where($map)->order($order)->limit("$skip, $limit")->select();
		
		if ($sync == false) return $dataList;
		
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
			$dataList[$k]['level_name'] = getMsGroupLevel($dataList[$k]['level']);
		}
		
		return $dataList;
	}
	
	/**
	 * 成员升级为管理员
	 * @param int $id	成员id
	 * @param int $gid 组id
	 * 
	 * @return boolean
	 */
	public function memberSetToAdmin($id, $gid) {
		if (empty($id) || empty($gid)) return false;
		
		//	升级为管理员
		$map = array();
		$map['id'] = intval($id);
		$map['gid'] = intval($gid);
		$map['level'] = 1;
		$update = M($this->tableName)->where($map)->save(array('level'=>2, 'mtime'=>time()));
		
		if ($update) return true;
		return false;
	}
	
	/**
	 * 管理员降级为成员
	 * @param int $id	成员id
	 * @param int $gid 组id
	 *
	 * @return boolean
	 */
	public function memberSetToDefault($id, $gid) {
		if (empty($id) || empty($gid)) return false;
		
		//	降级为成员
		$map = array();
		$map['id'] = intval($id);
		$map['gid'] = intval($gid);
		$map['level'] = 2;
		$update = M($this->tableName)->where($map)->save(array('level'=>1, 'mtime'=>time()));
	
		if ($update) return true;
		return false;
	}
	
	/**
	 * 删除工作室成员
	 * @param int $id	成员id
	 * @param int $gid 组id
	 * 
	 * @return boolean
	 */
	public function memberDelete($id, $gid) {
		if (empty($id) || empty($gid)) return false;
		
		//	删除成员
		$map = array();
		$map['uid'] = intval($id);
		$map['gid'] = intval($gid);
		$map['level'] = array('neq', 3);
		$delete = M($this->tableName)->where($map)->delete();
		if ($delete) {
			M($this->tableDataName)->where(array('gid'=>$gid, 'key'=>'member_count'))->save(array('value' => array('exp', 'value-1'), 'mtime'=>time()));
			return true;
		}
		return false;
	}


	/**
	 * 依据排名字段，返回名师工作室成员
	 * @author yangli4
	 *
	 * @param int    $start	起始位置
	 * @param int 	 $limit 最大数量
	 * @param string $order 排序字段
	 * @param string $orderDir 排序方向
	 *
	 * @return array
	 */
	public function getMemberRankingList($start,$limit,$order,$orderDir) {
		$sql = 'SELECT u.* FROM ts_user u  
				LEFT JOIN  ' . $this->tablePrefix . 'msgroup_member mm ON u.uid = mm.uid ,ts_user_data ud  
				WHERE ud.uid=mm.uid  AND ud.key="'.$order.'"  
				ORDER BY ud.value*1 '.$orderDir.'  
				LIMIT '.$start.','.$limit;
		$members = M('user')->query($sql);

		foreach($members as &$member){
			$member = array_merge ( $member, model ( 'Avatar' )->init ( $member ['uid'] )->getUserPhotoFromCyCore($member ['uid']));
		}
		return $members; 
	}
	
	/**
	 * 通过用户工作室id与Uid获取级别
	 * 
	 * @param int $gid
	 * @param int $uid
	 * 
	 * @return int
	 */
	public function getLevel($gid = 0, $uid = 0) {
		if (intval($gid) == 0 || intval($uid) == 0) return 0; 
		$level = M($this->tableName)->where(array('gid'=>intval($gid), 'uid'=>intval($uid)))->getField('level');
		return empty($level) ? 0 : $level;
	}
	
	/**
	 * 添加名师工作室成员
	 *@API
	 *
	 * @param array $memberList 一维数组传递被添加人的uid
	 * @param int $gid 工作室gid
	 * 
	 * @return boolean
	 */
	public function addMembers($memberList, $gid) {
		$memberList = array_unique($memberList);
		$gid = intval($gid);
		if (empty($memberList) || !is_array($memberList) || empty($gid)) return false;
		
		$num = 0;
		foreach ($memberList as $uid) {
			$r = $this->add(array('gid'=>$gid, 'uid'=>intval($uid), 'level'=>1, 'ctime'=>time()));
			if ($r) $num++;
		}
		
		//更新msgroup_data表的统计字段
		if ($num > 0) {
			$memberData = M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->find();
			if (empty($memberData)) {
				M('msgroup_data')->add(array('gid'=>$gid, 'key'=>'member_count', 'value'=>$num, 'mtime'=>time()));
			} else {
				M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->save(array('value' => array('exp', 'value+'.$num), 'mtime'=>time()));
			}
			return true;
		}
		return false;
	}
	
	/**
	 * 更新名师工作室成员
	 *@API
	 *
	 * @param array $memberList 一维数组传递被添加人的uid
	 * @param int $gid 工作室gid
	 *
	 * @return boolean
	 */
	public function updateMembers($memberList, $gid) {
		$memberList = array_unique($memberList);
		$gid = intval($gid);
		if (empty($memberList) || !is_array($memberList) || empty($gid)) return false;
	
		$users = M($this->tableName)->where(array('gid'=>$gid))->field('uid, level')->findAll();
		$old_members = array();
		foreach ($users as $u) {
			$old_members[] = $u['uid'];
			//过滤前台传过来的创建者
			if ($u['level'] == 3) {
				$key = array_search($u['uid'], $memberList);
				if (!empty($key)) unset($memberList[$key]); 
			}
		}
	
		//移除/新增用户
		$remove_uid = array_diff($old_members, $memberList);
		$add_uid = array_diff($memberList, $old_members);
		foreach ($add_uid as $uid) {
			//增加新成员
			D('MSGroupMember','msgroup')->add(array('gid'=>$gid, 'uid'=>intval($uid), 'level'=>1, 'ctime'=>time())); 
		}
		// 移除删除的成员
		M('msgroup_member')->where(array('gid'=>$gid, 'level'=>array('neq', 3), 'uid'=>array('in', implode(',', $remove_uid))))->delete();
		
		//重置成员数
		$member_count = count($memberList) + 1;
		$save = M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->save(array('value' => $member_count));
		if ($save == false) {
			$MSData = array();
			$MSData['gid'] = $gid ;
			$MSData['key'] = 'member_count' ;
			$MSData['value'] = $member_count ;
			$MSData['mtime'] = time();
			M($this->tableDataName)->add($MSData);
		}
		return true;
	}
	
	/**
	 * 获取成员信息
	 * @API
	 * 
	 * @param int $gid
	 * @param boolean $getUser 是否获取用户昵称(后台接口)
	 */
	public function getMemberByGid($gid, $getUser = false) {
		$members = M($this->tableName)->where(array('gid'=>intval($gid)))->select();
		foreach($members as &$member){
			$member = array_merge($member, model('Avatar')->init($member ['uid'])->getUserPhotoFromCyCore($member ['uid']));
			if ($getUser) {
				$member = array_merge($member, model('User')->getUserInfo($member ['uid']));
			}
		}
		return $members;
	}
	
	/**
	 * 获取创建者信息(类别，学科，学段，头像)
	 * @API
	 *
	 * @param int $gid
	 */
	public function getCreatorByGid($gid) {
		$joinUser = "inner join $this->tablePrefix$this->userTableName u on m.uid = u.uid";
		$fields = 'm.uid, u.grade, u.subject, u.login, u.uname';
		$creator = M($this->tableName . ' m')->field($fields)->join($joinUser)->where(array('gid'=>intval($gid), 'level'=>3))->find();
		if (empty($creator)) return null;
		
		$info = model('CyUser')->getCyUserInfo($creator['login']);
		$roleEnName = null;
		if (!empty($info)) {
			//支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
			$roleEnName = D("UserLoginRole")->getUserCurrentRole($creator['login'], $info['rolelist']);
			$creator['role_name'] = UserRoleTypeModel::getCNRoleName($roleEnName);
		}
	
		switch($roleEnName){
			case UserRoleTypeModel::PROVINCE_RESAERCHER:
			case UserRoleTypeModel::CITY_RESAERCHER:
			case UserRoleTypeModel::COUNTY_RESAERCHER:
			case UserRoleTypeModel::RESAERCHER:
				$creator['grade'] = D('Node')->getXueduanCNName($creator['grade']);
				break;
			case UserRoleTypeModel::TEACHER:
			case UserRoleTypeModel::STUDENT:
			case UserRoleTypeModel::PARENTS:
				$getGradeInfo = D('Node')->grades;
				$grade = $creator['grade'];
				unset($creator['grade']);
				
				foreach ($getGradeInfo as $s) {
					if ($s['code'] == $grade) {
						$creator['grade'] = $s['name'];
						break;
					}
				}
				break;
			default:
				unset($creator['grade']);
				break;
		}
		$subjects = D('Node')->subjects;
		$subject = $creator['subject'];
		unset($creator['subject']);
		
		foreach ($subjects as $s) {
			if ($s['code'] == $subject) {
				$creator['subject'] = $s['name'];
				break;
			}
		}
		
		$creator['avatar'] = model('Avatar')->init($creator['uid'])->getUserPhotoFromCyCore($creator['uid']);
		return $creator;
	}
}
?>