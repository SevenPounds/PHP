<?php
if (!defined('SITE_PATH')) exit();

$app_config  = array(
		'EDU_RESOURCE'		=> '{$website_resource}/',		//最后一个/不可少
		'SPACE'				=> '{$website_sns}/',
		'LABORATORY'		=> '{$website_lab}/',		
		'ESCHOOL'			=> '{$website_eschool}',		// E学校地址
		'PAN'				=> '{$website_pan}/',		
		'SEARCH'            => '{$website_search}/',
        'WEB'               => '{$website_web}/' ,        //web地址
		'JXHD'              => '{$website_jxhd}/',        //家校互动
        'YUNRES'            => '{$website_search1}/'      //YUNRES地址

);

return array(
		// 数据库常用配置
        'DB_DEPLOY_TYPE' => {$sns_db_deploy_type}, //设置分布式数据库支持
        'DB_RW_SEPARATE' => {$sns_db_rw_separate}, //设置读写分离
		'DB_TYPE'			=>	'mysql',				// 数据库类型
		'DB_HOST'			=>	'{$sns_db_host}',		// 数据库服务器地址
		'DB_NAME'			=>	'{$sns_db_name}',			// 数据库名
		'DB_USER'			=>	'{$sns_db_user}',				// 数据库用户名
		'DB_PWD'			=>	'{$sns_db_pwd}',			// 数据库密码
		'DB_PORT'			=>	{$sns_db_port},					// 数据库端口
		'DB_PREFIX'			=>	'ts_',					// 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
		'DB_CHARSET'		=>	'utf8',					// 数据库编码
		'SNS_DB' => '{$sns_db}',
		
		// 本机部署相关站点配置
		'RS_SITE_URL' 		=>  $app_config['YUNRES'],	// 资源平台地址
		'PAN_SITE_URL'		=>	$app_config['PAN'],				// 文档入口地址
		'LABORATORY'		=>	$app_config['LABORATORY'],		// 拟实验室地址	
		'ESCHOOL'			=>	$app_config['ESCHOOL'],			// E学校地址
		'SEARCH'            =>  $app_config['SEARCH'],			// 资源中心
		'SPACE'				=>	$app_config['SPACE'],			// 个人空间地址
        'WEB'               =>  $app_config['WEB'],             //web 地址
        'JXHD'              =>  $app_config['JXHD'],             //家校互动
	    'YUNRES'            =>  $app_config['YUNRES'],
        'PUBLIC_HEADER_URL' =>'{$website_public_header}',//公共头部文件配置

		// #云服务地址
		'YUN_SERVER_URL'		=>	'{$cloud_service_pan}', //云盘服务地址
		'USER_SERVER_URL' 		=>	'{$cloud_service_core}', 	// 用户服务地址
		
		// #cycore服务地址
		'RES_SERVICE_URL'		=>	'{$cycore_service_res}',			// 资源网关服务地址
		'ATTACH_SERVICE_URL'	=>	'{$cycore_service_upload}',			// 资源网关附件上传服务地址
		'YUNPAN_SERVER_URL'		=>	'{$cycore_service_disk}',			//云盘服务地址
		'VOD_URL'				=>	'{$cycore_service_vod}',			//流媒体服务地址
		'USER_SERVICE_URL'		=>	'{$cycore_service_user}',			//用户服务地址
        'WORKROOM_SITE_URL'     =>  '{$website_workroom}',

        'LZX_NATIV_URL' =>'{$lzx_nativ_url}',
        'LZX_MESSAGE_URL'=>'{$lzx_message_url}',

		'SSO_SERVER' => '{$sso_service_url}', 	// sso服务器地址
		'SSO_LOGIN_URL' => '{$sso_login_url}',		
		
		//cycore客户端配置
		'CLIENT_APP_NAME' 			=> '{$cycore_appkey}',
		'CLIENT_APP_SECRET' 		=> '{$cycore_secret}',
		'MANAGE_CLIENT_APP_NAME' 	=> '{$cycore_manage_appkey}',
		'MANAGE_CLIENT_APP_SECRET' 	=> '{$cycore_manage_secret}',
		
		//监管统计相关服务
		'RRTTrack_Service'      =>   '{$rrttrack_service}',		
				
		'EPSP_SERVER_URL'=>'{$epsp_service_url}',
		'SENSITIVEWORD_SERVER_URL'=>'{$sensitivword_service_url}',
		'CLIENT_SNS_APP_ID' => '{$CLIENT_SNS_APP_ID}',
		
		 //redis缓存
		'DATA_CACHE_TYPE'       => '{$cache_type}',
        'REDIS_HOST'            =>'{$cache_redis_host}',

        //白名单配置
		'LEGAL_URL' => array(
							'{$website_local}',
							'{$cycore_service_user}',
							'{$cycore_service_vod}',
							'{$cycore_service_disk}',
							'{$cycore_service_diskupload}',
							'{$cache_redis_host}',
							'{$epsp_service_url}',
							'{$sensitivword_service_url}',
							'{$website_keyword}',
		),
);

?>