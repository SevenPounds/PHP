<?php


return array(
	'SECURE_CODE'		=>	'2458451ff61e1c0a16',	// 数据加密密钥
	'COOKIE_PREFIX'		=>	'T3_',					// 数据加密密钥
	
	'TEXT_FILTER_SERVER'	=>	'http://172.16.81.42:8090/filter',		// 敏感词检测服务地址
	'MOBILE_SERVICE'		=> 'http://sms.openspeech.cn/api/send/?',	//短信服务地址
	'MOBILE_API_KEY'		=> 'afdb4a13a9fd3c51c4dcf508aff8446a',		//短信服务apikey
	'MOBILE_PASSWORD'		=> 'jycycp54321',	//短信服务密码
	'MOBILE_TEMPLATE_ID'	=> '10026',			//短信服务模版id
	'DEFAULT_MODULE'        =>  'Index',        // 默认模块名称
	
	// 个人空间相关设置参数配置
	'PRODUCT_CODE'			=>	'CHANGYAN',	// 产品码
	'YUNPAN_CAPACITY'		=>	'2',		// 单位GB,默认每个用户云盘初始容量是2G
	'YUNPAN_UPLOAD_NUM'		=>	5,			// 云盘批量上传最大上传数
	'RES_AUDIT_MODE'		=>	3,			// 资源审核模式，1无需审核，2人工审核，3人工+自动审核
	'UPLOAD_SCORE_LIMIT' 	=>	'5',		// 上传并公开资源增加积分的次数限制
	'HOT_RES_COUNT'			=>	20,			// 个人空间热门资源数量
	'MAJIA_ACCOUNT'			=>	array(),	// 马甲帐号配置
    'RESLIB_VIEW_APP'		=>	'changyan',	// 资源库模块资源在资源门户预览时使用，应与资源门户配置中的“DEFAULT_APP”保持一致
	'RESLIB_FILE_SIZE_LIMIT'		=>	'100MB',				// 资源库模块上传资源大小限制，应有服务器支持
	'RESLIB_USER_TOTAL_CAPACITY'	=>	'3',					// 单位是GB,默认每个用户的初始容量是3G
	
	'TMPL_EXCEPTION_FILE'=> SITE_PATH.'/addons/theme/stv1/exception.html',
     'ENABLE_SECURITY'=>1,//是否启用安全监管统计结果

    'SNS_MODULE'   =>  "个人空间",
    "SNS_ACTION"   =>  array("share"=>"分享文件到资源中心","upload" =>"上传资源"),
    "VOTE_APP_ID"   => "wljy1",      //网络调研
	"VOTE_APP_SECRET" => "a7260d8f089d845f",
    "PINGKE_APP_ID" => "wspk1",     // 网上评课
	"PINGKE_APP_SECRET" => "b6353d31e74f12b2",
    "ONLINEANSWER_APP_ID" => "zxdy1", // 在线答疑
	"ONLINEANSWER_APP_SECRET" => "b8a79852af59d86f",
    "RESEARCH_APP_ID"  => "wk123456",  // 主题讨论
	"RESEARCH_APP_SECRET" => "81bf37ff2f271d5f",
    "SNS_APP_ID"     => "grkj1",    // 个人空间
	"SNS_APP_SECRET" => "a8fef4a854e63930",
	"GROUP_APP_ID" => "xqqz1", // 兴趣圈子
	"GROUP_APP_SECRET" => "9e77c3f7be27e54a"
);