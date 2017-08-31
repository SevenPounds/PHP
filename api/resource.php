<?php
/**
 * 提供给iflybook、在线教研使用的服务接口
 * @author yuliu2
 */ 

define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'yunpan';

require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

date_default_timezone_set('PRC');

/**
 * 根据用户cyuid获取用户角色类型
 * @param string $cyuid 用户cyuid
 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
 */
function getCloudUserRole($cyuid){
	$result = D("CyUser")->getCloudUserRole($cyuid);
	return json_encode($result);
}

/**
 * 区域平台根据应用名和授权码获取Token
 * @param string $appName 应用名称
 * @param string $authcode 授权码
 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
 * 注：status : 200 获取成功， 400 获取失败
 */
function getToken($appName, $authcode){
	$token = D("AppAuth")->getToken($appName, $authcode);
	if(empty($token)){
		$result['status'] = '400';
		$result['message'] = '令牌获取失败';
		$result['data'] = '';
	}else{
		$result['status'] = '200';
		$result['message'] = '令牌获取成功';
		$result['data'] = $token;
	}
	return $result;
}

/**
 * 根据用户令牌获取授权用户登录名
 * @param string $appName 应用名称
 * @param string $token 用户令牌
 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
 * 注：status : 200 获取成功， 400 获取失败
 */
function getLogin($appName,$token){
	$flag = D("AppAuth")->validateToken($appName, $token);
	if(!$flag){
		$result['status'] = '400';
		$result['message'] = '令牌已失效';
		$result['data'] = '';
	}else{
		$user = D("AppAuth")->getUser($appName, $token);
		if(!empty($user)){
			$result['status'] = '200';
			$result['message'] = '获取用户名成功';
			$result['data'] = $user['user']['login'];
		}else{
			$result['status'] = '400';
			$result['message'] = '获取用户名失败';
			$result['data'] = '';
		}
	}
	return $result;
}

/**
 * 区域平台上传资源记录
 */
function addUploadRecord($resid, $login, $restype, $product_id="bbt") {
	return D('RegionPlatForm','yunpan')->addUploadRecord($login, $resid);
}

function addResourceToYunpan($cyuid, $rID, $need_feed = false){
	return D('Yunpan','yunpan')->addResourceToYunpan($cyuid, $rID, $need_feed);
}

/**
 * 区域平台删除已上传资源
 * @param  $resid 	资源id
 * @param  $login	资源上传者登录名
 */
function delUploadRecord($resid, $login) {
	return D('RegionPlatForm','yunpan')->delUploadRecord($login, $resid);
}

/**
 * iflybook下载资源
 * @param string $resid
 */
function getresource($resid){
	$file = D('IFlyBook','yunpan')->getresource($resid);
	return json_encode($file);
}

/**
 * iflybook
 * 获取资源列表
 * @param  $username 用户登录名
 * @param  $restype	资源类型
 * @param  $reskeyword	资源关键字
 * @param  $suffixAry   扩展名数组
 * @param  $pageindex	页码
 * @param  $pagesize	页大小
 * @return string 资源列表json字符串
 */
function list_resource($username, $restype, $reskeyword, $suffixAry, $pageindex, $pagesize){
	//按上传时间降序排
	$result = D('IFlyBook','yunpan')->list_resource($username, $pageindex, $pagesize);
	return json_encode($result);
// 	$action = A('Api', 'reslib');
// 	$conditions = array();
// 	$conditions ['keywords'] = $reskeyword;
// 	$conditions ['restype'] = $restype;
// 	$conditions ['suffix'] = $suffixAry;
// 	//1：收藏:3：下载 4：上传
// 	$conditions ['optype'] = array(1, 4);
// 	$result = $action->list_resource($username, $conditions, $pageindex, $pagesize, "dateline", "DESC", array());
// 	return $result;
// 	return json_encode($result);
}

$server = new PHPRPC_Server();
$server->add('getCloudUserRole');
$server->add('getToken');
$server->add('getLogin');
$server->add('addUploadRecord');
$server->add('addResourceToYunpan');
$server->add('delUploadRecord');
$server->add('list_resource');
$server->add('getresource');
$server->start();
?>
