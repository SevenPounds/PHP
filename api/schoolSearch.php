<?php
/*
* 学校检索服务，供安徽省教育平台，第三方调用
*/


define('SITE_PATH', dirname(dirname(__FILE__)));

require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

require_once SITE_PATH . '/addons/library/Clients/UserCenterClient/ClientFactory.php';
use CyCore\UserCenter\Utils\TEntityHelper;
use CyCore\UserCenter\ClientFactory;

/**
 * 检索学校
 * @param int $skip ,起始位置，用于分页
 * @param int $limit ,每页学校数量，用于分页
 * @param array $school_type ,学校类型
 * @param int $area_id ,学校所属区域编号
 */
function querySchoolByConstions($skip, $limit, $school_type, $area_id){
	$queryParams = array();
	if(!empty($skip)){
		$queryParams['skip'] = intval($skip);
	}	
	if(!empty($limit)){
		$queryParams['limit'] = intval($limit);
	}
	if(!empty($school_type)){
		$queryParams['school_type'] = $school_type;
	}
	if(!empty($area_id)){
		$queryParams['area_id'] = intval($area_id);
	}	

	$school_svc = ClientFactory::createSchoolSvc();
	return json_encode( $school_svc->retrieve_school(TEntityHelper::getTObjectArray($queryParams)));
}




$server = new PHPRPC_Server();
$server->add('querySchoolByConstions');

$server->start();

?>


	