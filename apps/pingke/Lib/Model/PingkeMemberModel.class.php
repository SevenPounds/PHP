<?php
/**
 * 网上评课成员model
 * @package pingke\Lib\Model
 * @author zqxiang@iflytek.com
 * @version 创建时间：2013-12-9 下午2:53:43
 */

class PingkeMemberModel extends Model {
	protected $tableName = 'pingke_member';
	
	function  _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 获得评课成员数量
	 * @param int $pingke_id
	 * 
	 * @return int
	 */
	function getMemberListCount($pingke_id) {
		return M('pingke')->where(array("id"=>$pingke_id))->getField('member_count');
	}
	
	/**
	 * 获取评课成员信息
	 * @param int $pingke_id
	 * @param int $page
	 * @param int $limit
	 * @param string $order
	 * 
	 * @return array
	 */
	function getMemberList($pingke_id, $page = 1, $limit = 20, $order = 'id desc') {
		$result = array();
		$id = intval($pingke_id);
		
		//获取评课成员uid、评论次数
		$memberList = M($this->tableName)->where(array('pingke_id'=>$id))->order($order)->page("$page, $limit")->select();
		if (empty($memberList)) return $result;	//成员不存在
		
		foreach ($memberList as $k=>$v) {
			//获取创建者信息
			$user = model('User')->getUserInfo($v['uid']);
			if (empty($user)) continue;
			$user['post_count'] = $v['discuss_count'];	//研讨次数
			
			$info = model('CyUser')->getCyUserInfo($user['login']);
			if (!empty($info)) {
				$org = current(current($info['orglist']));
				$user['org_name'] = $org['name'];
			}
			
			$result[$k] = $user;
		}
		
		return $result;
	}
	
	/**
	 * 获取用户未读取的评课数量
	 * @param int $uid
	 * 
	 * @return int
	 */
	function getNewDataCount($uid) {
		$join = 'inner join ' . $this->tablePrefix . 'pingke p on m.pingke_id = p.id and p.is_del = 0';
		return M($this->tableName . ' m')->join($join)->where('m.is_new = 1 and m.uid = ' . intval($uid))->count();
	}
	
	/**
	 * 更新评课new字段
	 * @param int $pingke_id
	 * @param int $uid
	 * 
	 * @return boolean
	 */
	function updateNewTag($pingke_id, $uid) {
		return M($this->tableName)->where(array('pingke_id' => intval($pingke_id), 'is_new'=>1, 'uid'=>intval($uid)))->save(array('is_new'=>0));
	}
	
}