<?php
/**
 * 用户访问者模型 - 数据对象模型
 * @author yxxing
 * @version 
 */
class UserVisitorModel extends Model {
	protected $tableName = 'user_visitor';
	protected $error = '';
	protected $fields = array (
			0 => 'id',
			1 => 'uid',
			2 => 'cid',
			3 => 'sid',
			4 => 'vuid',
			5 => 'vtime',
			'_pk' => 'id'
	);
	/**
	 * 更新用户访问记录
	 * @param int $uid 被访问用户的UID
	 * @param int $vuid 访问用户的ID，即系统当前登录用户
	 */
	public function updateVisitor($uid, $vuid){
		$user = model('User')->getUserInfo($uid);
		if(!$user)
			return;
		$result = $this->where('uid='.$uid.' AND vuid='.$vuid)->select();
		if($result){
			return $this->where('uid='.$uid.' AND vuid='.$vuid)->save(array('vtime'=>time()));
		} else {
			$data = array('uid'=>$uid, 'vuid'=>$vuid, 'vtime'=>time());
			return $this->add($data);
		}
	}
	/**
	 * 获取某一用户的最近来访记录
	 * @param int $uid
	 */
	public function getLastVisitors($uid ){
		$list = $this->where('uid='.$uid)->order('vtime DESC')->limit("0, 6")->select();
		return $list;
	}
}