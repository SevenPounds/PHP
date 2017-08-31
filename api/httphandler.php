<?php
/**
 * 提供给BBT电子白板使用的服务接口
 */
//载入核心文件
define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'yunpan';
$_GET['mod'] = 'BBT';
$_GET['act'] = 'Index';
require_once SITE_PATH . '/api/change_url.php';
require(dirname(dirname(__FILE__)).'/core/core.php');

$method = $_REQUEST['method'];
if(!isset($method) || empty($method)){
	echo 'not method';
	exit();
}
$action = A('BBT','yunpan');
//$action = A('WhiteboardApi','reslib');
switch ($method){
	case 'getnetdiskresources':		
		$result = $action->getnetdiskresources();
	    echo $result;
		break;
	case 'uploadpageresource':
		$result = $action->uploadpageresource();
		echo  $result;
		break;
	case 'uploadsizelimit':
		$result = $action->uploadsizelimit();
		echo $result;
		break;
	case 'getresourcedescriptor':
		$result = $action->getresourcedescriptor();
		echo $result;
		break;
	case 'downloadresource':
		$action->downloadresource();
		break;
	case 'uploadebookresource':
		$result = $action->uploadebookresource();
		echo $result;
		break;
	case 'getresourceurl':
		$result = $action->getresourceurl();
		echo $result;
		break;
	case 'index':
		$result = $action->index();
		echo $result;
		break;
	default:		
		echo 'method not match';
		break;
}

?>