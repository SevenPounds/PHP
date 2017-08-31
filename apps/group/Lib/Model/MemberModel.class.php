<?php
class MemberModel extends Model {
	var $tableName = 'group_member';

	function getNewMemberList($gid, $limit = 3)	{
		$gid = intval($gid);
		$new_member_list = $this->field('id,uid,level,ctime')->where("gid={$gid} AND level>1")->order('ctime DESC')->limit($limit)->findAll();
		foreach ( $new_member_list as &$v ){
			$v['userinfo'] = model('User')->getUserInfo($v['uid']);
		}
		return $new_member_list;
	}

	function memberCount($gid){
		return $this->where("gid=".$gid)->count();
	}
	
	/**
	 * 
	 * @param unknown_type $gid 圈子id
	 * @param unknown_type $uid 用户uid
	 * @return boolean
	  *2014-7-24
	 */
	function  hasRole($gid,$uid){
		$res=false;
		if(empty($gid)||empty($uid)){
			return false;
		}
		$uids=$this->where('gid='.$gid)->field('uid')->select();		
		foreach ($uids as $uidTemp){
			if($uid==$uidTemp['uid']){
			  $res=true;
			   break;
			}
		}
		return $res;
	}
}
