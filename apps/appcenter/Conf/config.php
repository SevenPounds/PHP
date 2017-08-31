<?php
return array(
		// 推荐应用配置
		"RECOMMEND_APPS"	=> array(
				/*"tk"	=>	"题库",*/
				"tszy"	=>	"听说作业",
				"zyzx"	=>	"作业中心",
				"xnsys"	=>	"虚拟实验室",
				/*"pjxt"	=>	"评价系统",*/
				"dztsg"	=>	"电子图书馆",
				"szqk"	=>	"数字期刊",
				),
		// 默认应用配置
		"DEFAULT_APPS_BY_ROLE"	=> array(
				"teacher"		=>	array('tk','tszy','zyzx','xnsys','pjxt','dztsg','szqk'),
				"instructor"	=>	array('dztsg','szqk'),
				"student"		=>	array('tszy','zyzx','xnsys','dztsg','szqk'),
				"parent"		=>	array('tszy','xnsys','dztsg','szqk'),
				"eduadmin"      =>  array('jgtj','zttl','wldy','wspk','zxdy'),
				),
		"CLIENT_APPS"	=> array(
				"bkgj"		=> array("app_name"=>"备课工具","download_url"=>C('DOWNLOAD_SITE_URL').'bkgj.rar'),
				"jszs"	=> array("app_name"=>"教师助手","download_url"=>C('DOWNLOAD_SITE_URL').'iFlyBook.rar'),
				),
);
?>