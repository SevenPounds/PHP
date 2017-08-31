<?php
/*----------------将服务路径XX/sns/apiXX改为网站路径XX/sns/XX by yxxing 10.10-------*/
if(!defined('IS_CGI')){
	define('IS_CGI',substr(PHP_SAPI, 0, 3)=='cgi' ? 1 : 0 );
}

// 当前文件名
if(!defined('_PHP_FILE_')) {
	if(IS_CGI) {
		// CGI/FASTCGI模式下
		$_temp  = explode('.php',$_SERVER["PHP_SELF"]);
		define('_PHP_FILE_', rtrim(str_replace($_SERVER["HTTP_HOST"],'',$_temp[0].'.php'),'/'));
	}else {
		define('_PHP_FILE_', rtrim($_SERVER["SCRIPT_NAME"],'/'));
	}
}
// 网站URL根目录
if(!defined('__ROOT__')) {
	$_root = dirname(_PHP_FILE_);
	define('__ROOT__',  dirname(($_root=='/' || $_root=='\\')?'':rtrim($_root,'/')));
}
/*----------------end---------------*/
?>