<?php
if (!defined('SITE_PATH')) exit();

$app_config  = array(
		'EDU_RESOURCE'		=> 'http://127.0.0.1:8090/shenzhen/App.ResourceCloud/',		//最后一个/不可少
		'SPACE'				=> 'http://127.0.0.1:8090/shenzhen/App.EduSNS/',
		'LABORATORY'		=> 'http://127.0.0.1:8090/shenzhen/App.Lab/',		
		'ESCHOOL'			=> 'http://127.0.0.1:8090/shenzhen/App.ESchool',		// E学校地址
		'PAN'				=> 'http://127.0.0.1:8090/shenzhen/App.Pan/',		
		'SEARCH'            => 'http://127.0.0.1:8090/shenzhen/App.Search/',
        'WEB'               => 'http://127.0.0.1:8090/shenzhen/web/',         //web地址
		'JXHD'               => 'http://172.16.91.160:9001/EduPortal/sso/login.aspx/'         //web地址
);

return array(
		// 数据库常用配置
        'DB_DEPLOY_TYPE' => 1, //设置分布式数据库支持
        'DB_RW_SEPARATE' => 1, //设置读写分离
		'DB_TYPE'			=>	'mysql',				// 数据库类型
		'DB_HOST'			=>	'172.16.16.143',		// 数据库服务器地址
		'DB_NAME'			=>	'epsp_sz_sns',			// 数据库名
		'DB_USER'			=>	'root',				// 数据库用户名
		'DB_PWD'			=>	'ifly@2016',			// 数据库密码
		'DB_PORT'			=>	3306,					// 数据库端口
		'DB_PREFIX'			=>	'ts_',					// 数据库表前缀（因为漫游的原因，数据库表前缀必须写在本文件）
		'DB_CHARSET'		=>	'utf8',					// 数据库编码
		'SNS_DB' => '',
		
		// 本机部署相关站点配置
		'RS_SITE_URL' 		=>  $app_config['EDU_RESOURCE'],	// 资源平台地址
		'PAN_SITE_URL'		=>	$app_config['PAN'],				// 文档入口地址
		'LABORATORY'		=>	$app_config['LABORATORY'],		// 拟实验室地址	
		'ESCHOOL'			=>	$app_config['ESCHOOL'],			// E学校地址
		'SEARCH'            =>  $app_config['SEARCH'],			// 资源中心
		'SPACE'				=>	$app_config['SPACE'],			// 个人空间地址
        'WEB'               =>  $app_config['WEB'],             //web 地址
        'JXHD'               =>  $app_config['JXHD'],             //家校互动
        'PUBLIC_HEADER_URL' =>'http://127.0.0.1:8090/shenzhen/web/index.php?app=Home&mod=AuthNav&act=commonnav',//公共头部文件配置

		// #云服务地址
		'YUN_SERVER_URL'		=>	'http://cyservice.szjyy.test.changyan.cn/api?version=1.0&format=json&appkey=KtSNKxk3&access_token=changyanyun', //云盘服务地址
		'USER_SERVER_URL' 		=>	'http://cyservice.szjyy.test.changyan.cn/api?version=1.0&format=json&appkey=KtSNKxk3&access_token=changyanyun', 	// 用户服务地址
		
		// #cycore服务地址
		'RES_SERVICE_URL'		=>	'http://172.16.16.117:50101',			// 资源网关服务地址
		'ATTACH_SERVICE_URL'	=>	'test.cystorage.cycore.cn',			// 资源网关附件上传服务地址
		'YUNPAN_SERVER_URL'		=>	'http://172.16.16.117:50501/iflydisk/common',			//云盘服务地址
		'VOD_URL'				=>	'rtmp://172.16.81.43/iflytekRed5',			//流媒体服务地址
		'USER_SERVICE_URL'		=>	'http://172.16.16.117:50301/usercenter',			//用户服务地址
        'WORKROOM_SITE_URL'     =>  'http://127.0.0.1:8090/shenzhen/App.WorkRoom',

        'LZX_NATIV_URL' =>'http://szdemo.changyan.cn/openapi/site/header.do',
        'LZX_MESSAGE_URL'=>'http://szdemo.changyan.cn:7093/api?method=message.send&version=1.0&format=json',

		'SSO_SERVER' => 'http://open.szjyy.test.changyan.cn/sso/', 	// sso服务器地址
		'SSO_LOGIN_URL' => 'http://127.0.0.1:8090/shenzhen/web/index.php?app=Home&mod=Index&act=login',		
		
		//cycore客户端配置
		'CLIENT_APP_NAME' 			=> 'rrt_shenzhen',
		'CLIENT_APP_SECRET' 		=> '4c1a96f8d95c425dba526ca1ab1863f2',
		'MANAGE_CLIENT_APP_NAME' 	=> 'rrt_manage',
		'MANAGE_CLIENT_APP_SECRET' 	=> '75aa1666bb1bcf23ed096fbf67e30ce9',
		
		//监管统计相关服务
		'RRTTrack_Service'      =>   '',		
				
		'EPSP_SERVER_URL'=>'http://172.16.16.142:8888',
		//安全监管server地址
		'SENSITIVEWORD_SERVER_URL'=>'http://172.31.17.30:9200',
		//空间SNS的应用上架ID
		'CLIENT_SNS_APP_ID'=>'f80237a6a6cc4bd08975b71efef7d74b',
		
		 //redis缓存
		//'DATA_CACHE_TYPE'       => 'File',
        //'REDIS_HOST'            =>'172.16.16.143',
		"DATA_CACHE_TYPE"		=> "Redis", // 数据缓存类型
		"REDIS_HOST"		=> "172.31.7.122", //redis服务地址
		"REDIS_PORT"		=> "6379", //redis端口
);

?>