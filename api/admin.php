<?php 
/**
 * 提供给资源平台获取导航栏
 */

define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'admin';
$_GET['mod'] = 'Api';
$_GET['act'] = 'Index';
require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

function getTopNav() {
	$action = A('Api', 'admin');
	return $action->getTopNav();
}

function getBottomNav() { 
	$action = A('Api', 'admin');
	return $action->getBottomNav();
}

$server = new PHPRPC_Server();
$server->add(array('getTopNav', 'getBottomNav'));
$server->start();
?>
