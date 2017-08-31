<?php
/**
 *	提供给BBT登录使用 
 */
define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'api';
$_GET['mod'] = 'User';
$_GET['act'] = 'index';
require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';
$action = A('User', 'api');
$apiAction = $_GET['apiaction'];
//班班通登录接口
if($apiAction=='login'){
	$username = $_GET['username'];
	$password = $_GET['password'];	
	echo $action->login($username, $password);
}else if($apiAction=='getcyavatar'){
	$login = $_REQUEST['username'];
	echo $action->getcyavatar($login);
}



?>