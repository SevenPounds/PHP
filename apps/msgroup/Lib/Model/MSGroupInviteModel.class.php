<?php
/**
 * 名师工作室邀请model
 * @package msgroup\Lib\Model
 * @author zqxiang@iflytek.com
 * @version 创建时间：2013-12-6 下午5:02:36
 */
class MSGroupInviteModel extends Model {
	protected $tableName = 'msgroup_invite';
	
	
	/**
	 * 查询教研员/教师用户
	 * 
	 * @param int $mid 当前用户的id
	 * @param string|array $roleName	角色名(teacher表示教师 researcher表示教研员)
	 * @param string $keywords 检索关键字
	 * @param string $order
	 * @param int $skip
	 * @param int $limit
	 * 
	 * @return array
	 */
	function getUserList($mid, $roleName, $keywords, $order = '', $skip = 0, $limit = 20) {
		// 判断传入的$role是否是数组
		if(is_array($roleName)){
			$map['ur.rolename'] = array('in', $roleName);
		}else{
			$map['ur.rolename'] = $roleName;
		}
		
		if(isset($keywords)){
			$map['u.uname'] = array('like', "%$keywords%");
		}
		
		$map['u.uid'] = array('neq', $mid);
		$field = 'u.uid, u.uname';
		$userlist = $this->table('`' . $this->tablePrefix.'user` AS u LEFT JOIN `' . $this->tablePrefix.'user_role` AS ur ON u.uid = ur.uid')->where($map)->field($field)->select();
		foreach ($userlist as &$user){
			$user = array_merge($user,model ( 'Avatar' )->init ( $user ['uid'] )->getUserPhotoFromCyCore ($user ['uid']));
		}
		
		return $userlist;
	}
	
	
	/**
	 * 发送邀请
	 * @param array $receiveUid	一维数组保存被邀请人uid
	 * @param int $sendUid 邀请发起人uid
	 * @param int $gid 组id
	 *
	 * @return boolean
	 */
	function sendInvitation($receiveUid, $sendUid, $gid) {
		$joinUser = 'LEFT JOIN '.$this->tablePrefix.user.' u ON m.`creator_uid` = u.`uid`';
		$fields = 'm.gid, m.group_name, u.uname as creator_rname';
		$groupDetail = M('msgroup m')->field($fields)->join($joinUser)->where(array('m.creator_uid'=>$sendUid))->find();
		if (empty($groupDetail)) return false;
		
		// 增加邀请记录
		!is_array($receiveUid) && $receiveUid = explode(',', $receiveUid);
		$config = array();
		$config ['user'] = $groupDetail['creator_rname'];
		$config ['group_title'] = $groupDetail['group_name'];
		foreach($receiveUid as $uid) {
			$map = array('gid' => $groupDetail['gid'], 'receive_uid' => $uid, 'send_uid' => $sendUid, 'ctime'=>time());
			$insertId = M($this->tableName)->add($map);
			if ($insertId) {
				// 增加通知::  {user} 邀请您加入{group_title}。<a href="{sourceurl}" target='_blank'>去看看>></a>
				$config['sourceurl'] = U ( 'msgroup/index/invite', array('id' => $insertId));
				model('Notify')->sendNotify ($uid, 'msgroup_invite', $config);
			}
		}
	
		return true;
	}
	
	/**
	 * 接受/拒绝加入工作室的邀请
	 * 
	 * @param int $id 邀请id
	 * @param int $mid 用户uid
	 * @param int $status	 1：接受  2：拒绝
	 * 
	 * @return boolean
	 */
	function invitationStatusChange($id, $mid, $status) {
		$status != 1 && $status = 2;
		
		//改变邀请状态
		$map = array();
		$map['id'] = $id;
		$map['receive_uid'] = $mid;
		$update = M($this->tableName)->where($map)->save(array('status'=>$status, 'mtime'=>time()));
		if ($update == false) return false;
		
		// 接受处理
		if ($status == 1) {
			$groupDetail = M($this->tableName)->where($map)->select();
			if (empty($groupDetail[0])) return false;
			$gid = intval($groupDetail[0]['gid']);
			
			//	获得申请者用户信息
			$user = M('user')->where(array('uid'=>$mid))->select();
			if (empty($user[0]['uid'])) return false;
			
			//	添加用户数据到ts_msgroup_member表
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
		
		return true;
		
	}
}