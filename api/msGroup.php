<?php
/**
* 为名师工作室功能服务
*/



define('SITE_PATH', dirname(dirname(__FILE__)));

require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

/**
* 创建名师工作室
* @param array $msgroup $msgroup['group_name'],
* 										  $msgroup['discription'],
* 										  $msgroup['subject'],	工作室学科
* 										  $msgroup['creator_uid'],  创建者/领头人
* 										  $msgroup['members'],	成员列表（不包括creator_uid）
* 										  $msgroup['imageUrl'],
* @return int 名师工作室gid
*/
function createMsGroup($msgroup){
	$gid = D('MSGroup','msgroup')->creatMSGoup($msgroup);
	if ($gid) {
		D('MSGroupMember','msgroup')->addMembers($msgroup['members'], $gid);
		
		//保存头像
		if (file_get_contents($msgroup['imageUrl'])) doSaveAvatar($msgroup['imageUrl'], $gid);
		
		//	同步到solr
		model('SolrZone')->update(ZoneTypeModel::STUDIO, $gid);
	}
	return $gid;
}

/**
 * 查询教研员、教师用户， 模糊查询
 * 
 * @param string $type 查询类别（1=教研员，2=教师，3=模糊查询教研员和教师）
 * @param array $param 各类别查询时传递的条件（具体参数可参考model中注释）
 * @param int $page
 * @param int $limit
 * 
 * @return mixed
 */
function searchUser($type = 3, $param = array(), $page = 1, $limit = 15) {
	if ($type == 1) {
		return D("Pingke", "pingke")->searchInstructorUser($param, $page, $limit);
	} else if ($type == 2) {
		return D("Pingke", "pingke")->searchTeacherUser($param, $page, $limit);
	} else {
		return D("Pingke", "pingke")->searchInstructorAndTeacher($param['keywords'], $page, $limit);
	}
}

/**
 * 更新名师工作室
 * 
 * @param array $param（具体参数可参考model中注释）
 * 							$param['gid'], $param['group_name'], $param['discription'], $param['new_members']
 * 							$param['old_members']  旧的成员uid一维数组(不包含创建者)
 * 							$param['new_members']  新的成员uid一维数组(不包含创建者)
* 							$param['imageUrl'],
 * 
 * @return 
 */
function updateMsGroup($param) {
	$gid = intval($param['gid']);
	//更新基本信息
	$cid = $param['creator_uid'];
	$subject = $param['subject'];
	if (in_array($param['creator_uid'], $param['new_members']) || in_array($param['creator_uid'], $param['old_members'])) $param['creator_uid'] = ''; //如果创建者uid在新或旧的成员uid数组中， 则不更新
	D('MSGroup','msgroup')->update($gid, $param['group_name'], $param['discription'],$subject, $param['creator_uid']);
	
	//更新创建者
	if (intval($param['creator_uid']) > 0) {
		M('msgroup_member')->where(array('gid'=>$gid, 'level'=>array('neq', 3), 'uid'=>intval($param['creator_uid'])))->delete(); // 从当前组成员提升为创建者时移除成员用户
		M('msgroup_member')->where(array('gid'=>$gid, 'level'=>3))->save(array('uid' => intval($param['creator_uid']))); 
	}
	// 发送消息、移除/新增用户
	$remove_uid = array_diff($param['old_members'], $param['new_members']);
	$add_uid = array_diff($param['new_members'], $param['old_members']);
	
	foreach ($add_uid as $uid) {
		//增加新成员
		D('MSGroupMember','msgroup')->add(array('gid'=>$gid, 'uid'=>intval($uid), 'level'=>1, 'ctime'=>time())); 
	}
	if(!empty($remove_uid)){
		// 移除删除的成员
		M('msgroup_member')->where(array('gid'=>intval($gid), 'level'=>array('neq', 3), 'uid'=>array('in', implode(',', $remove_uid))))->delete();
	}
	
	//重置成员数
	$member_count = count($param['new_members']) + 1;
	M('msgroup_data')->where(array('gid'=>$gid, 'key'=>'member_count'))->save(array('value' => $member_count));
	
	
	//保存头像
	if (file_get_contents($param['imageUrl'])) doSaveAvatar($param['imageUrl'], $gid);
	
	//	同步到solr
	model('SolrZone')->update(ZoneTypeModel::STUDIO, $gid);
	
	return true;
}

/**
 * 查询名师工作室详细信息
 * @param string $gid 名师工作室id
 * 
 * @return mixed
 */
function getMsGroup($gid){
	$result = D('MSGroup', 'msgroup')->getMsGroupById($gid);
	
	//获取成员
	$result['member'] = D('MSGroupMember', 'msgroup')->getMemberByGid($gid, true);
	
	//获取头像
	$dAvatar = D('MSAvatar', 'msgroup');
	$params['app'] = 'msgroup';
	$params['rowid'] = intval($gid);
	$dAvatar->init($params); // 初始化Model用户id
	$result['image'] = $dAvatar->getAvatar();
	
	return $result;
}


/**
 * 删除名师工作室
* @param array $ids  or int
*/
function delMsGroup($ids){
	model('SolrZone')->delete(ZoneTypeModel::STUDIO, $ids);
	return D('MSGroup','msgroup')->delMsGroup($ids);
}


/**
 * 查询名师工作室数量
 * @param array $param
 * 
 * @return int
 */
function searchMsGroupCount($param){
	return D('MSGroup','msgroup')->searchMsGroupCount($param);
}

/**
* 查询名师工作室列表
* @param string $param 
* 							$param['group_name'],
*							$param['uname']
*
*@return array
*/
function searchMsGroup($param, $page = 1, $limit = 10, $order = '') {
	return D('MSGroup','msgroup')->searchMsGroup($param, $page, $limit, $order);
}

/**
 * 保存远程图片
 * @param string $url 图片地址
 * @param int $gid 名师工作室gid
 * @return boolean|string
 */
function doSaveAvatar($url, $gid) {
	$dAvatar = D('MSAvatar', 'msgroup');
	$params['app'] = 'msgroup';
	$params['rowid'] = intval($gid);
	$dAvatar->init($params); // 初始化Model用户id
	$file_path = '/avatar/'.$params['app'].$dAvatar->convertRowIdToPath().'/';
	
	//删除缩略图
	$dir = UPLOAD_PATH.$file_path;
	$dh=opendir($dir);
	while ($file=readdir($dh)) {
		if($file!="." && $file!="..") {
			$fullpath=$dir."/".$file;
			if(!is_dir($fullpath)) {
				@unlink($fullpath);
			}
		}
	}
	closedir($dh);

	if(strncasecmp($url,'http',4)!=0){
		return false;
	}
	$opts = array(
			'http'=>array(
					'method' => "GET",
					'timeout' => 30, //超时30秒
					'user_agent'=>"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)"
			)
	);
	$context = stream_context_create($opts);
	$file_content = file_get_contents($url, false, $context);
	@mkdir($dir,0777,true);
	$i = pathinfo($url);
	if(!in_array($i['extension'],array('jpg','jpeg','gif','png'))){
		$i['extension'] = 'jpg';
	}
	//$file_name = 'original.'.$i['extension'];
	$file_name = 'original.jpg';
	
	//本地存储
	$res = file_put_contents($dir.$file_name, $file_content);

	if($res){
		return $file_path.$file_name;
	}else{
		return false;
	}
}


/**
 * 编辑用户
 * @param string $login_name 用户登录名
 *
 * @return boolean
 */
function updateUser($login_name) {
	if (empty($login_name)) return false;
	
	//验证用户是否开通空间
	$user = model('User')->getUserInfoByLogin($login_name);
	if (empty($user)) return false;
	
	//获取cycore数据
	$cyuser = model('CyUser')->getUserByLoginName($user['login']);
	if (empty($cyuser)) return false;
	
	//cycore数据更新到本地
	Model('CyUser')->cyLogin($cyuser);
	
	//同步数据到solr
	return Model('CyUser')->updateSolrUser($user['uid']);
	
}


/**
 * 删除用户
 * @param array $param 一维数组保存用户登录名
 *
 * @return boolean
 */
function deleteUser($param) {
	if (empty($param)) return false;
	
	//获取uid
	$login = "'" . implode("','", $param) . "'";
	$userList = model('User')->getUserListByCondition(array('login'=>array('in', $login)));
	if (empty($userList['data'])) return false;
	$uidList = array();
	foreach ($userList['data'] as $v) {
		$uidList[] = $v['uid'];
	}
	if (empty($uidList)) return false;
	
	//删除本地用户
	model('User')->deleteUsers($uidList);
	
	//删除solr
	foreach ($uidList as $uid) {
		$userrole = model('UserRole')->getUserRole($uid);
		$type = $userrole['rolename'];
		in_array($type,array(UserRoleTypeModel::PROVINCE_RESAERCHER,UserRoleTypeModel::CITY_RESAERCHER,UserRoleTypeModel::COUNTY_RESAERCHER,UserRoleTypeModel::RESAERCHER))  && $type = 'researcher';
		switch ($type){
			case 'teacher':
				$type = ZoneTypeModel::TEACHER;
				break;
			case 'researcher':
				$type = ZoneTypeModel::RESEARCHER;
				break;
		}
		model('SolrZone')->delete($type,$uid);
	}
	
	return true;
}



$server = new PHPRPC_Server();
$server->add('createMsGroup');
$server->add('searchUser');
$server->add('updateMsGroup');
$server->add('getMsGroup');
$server->add('delMsGroup');
$server->add('searchMsGroupCount');
$server->add('searchMsGroup');
$server->add('updateUser');
$server->add('deleteUser');
$server->start();
