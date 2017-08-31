<?php
/**
 * 名师工作室客户端服务
 * 
 *	测试例子：
 *	$client = new MsGroup_Client('http://192.168.63.120:81/thinkSNS/api/msGroup.php');
 *	echo $client->createMsGroup(array('group_name'=>'Test Group', 'discription'=>'testing...', 'creator_uid'=>607, 'members'=>array(615)));
 */


class MsGroup_Client{
	private $client = null;
	/**
	 * 
	 * @param String $url 服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}
	
	
	/**
	 * 创建名师工作室
	 * @param array $msgroup $msgroup['group_name'],
	 * 										  $msgroup['discription'],
	 * 										  $msgroup['creator_uid'],  创建者/领头人
	 * 										  $msgroup['members'],	成员列表（不包括creator_uid）
	 * 
	 * @return int 名师工作室gid
	 */
	function createMsGroup($msgroup) {
		return $this->client->createMsGroup($msgroup);
	}
	
	
	/**
	 * 查询教研员、教师用户， 模糊查询
	 * 
	 * @param string $type 查询类别（1=教研员，2=教师，3=模糊查询教研员和教师）
	 * @param array $param 各类别查询时传递的条件（具体参数可参考model中注释）
	 * @param int $page
	 * @param int $limit
	 * 
	 * @return mixed	返回$limit+1条数据，用于翻页
	 */
	function searchUser($type, $param, $page, $limit) {
		return $this->client->searchUser($type, $param, $page, $limit);
	}
	
	
	/**
	 * 更新名师工作室
	 * 
	 * @param array $param（具体参数可参考model中注释）
	 * 							$param['gid'], $param['group_name'], $param['discription'], $param['new_members']
	 * 							$param['old_members']  旧的成员uid一维数组(不包含创建者)
	 * 							$param['new_members']  新的成员uid一维数组(不包含创建者)
	 * 
	 * @return 
	 */
	function updateMsGroup($param) {
		return $this->client->updateMsGroup($param);
	}
	
	/**
	 * 查询名师工作室详细信息
	 * @param int $gid
	 * 
	 * @return array
	 */
	function getMsGroup($gid) {
		return $this->client->getMsGroup($gid);
	}
	
	/**
	 * 删除名师工作室
	 * @param array $gids 一维数组保存
	 *
	 * @return boolean
	 */
	function delMsGroup($gids) {
		return $this->client->delMsGroup($gids);
	}
	
	/**
	 * 查询名师工作室数量
	 * @param array $param
	 *
	 * @return int
	 */
	function searchMsGroupCount($param){
		return $this->client->searchMsGroupCount($param);
	}
	
	/**
	 * 查询名师工作室列表
	 * @param array $param
	 *
	 * @return int
	 */
	function searchMsGroup($param, $page = 1, $limit = 10, $order = '') {
		return $this->client->searchMsGroup($param, $page, $limit, $order);
	}
	
	/**
	 * 编辑用户
	 * @param string $login_name 用户登录名
	 * 
	 * @return boolean
	 */
	function updateUser($login_name) {
		return $this->client->updateUser($login_name);
	}
	
	
	/**
	 * 删除用户
	 * @param array $param 一维数组保存用户登录名
	 *
	 * @return boolean
	 */
	function deleteUser($param) {
		return $this->client->deleteUser($param);
	}
}
?>
