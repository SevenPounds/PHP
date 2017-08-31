<?php
if (!defined('SITE_PATH')) exit();

return array(
	// 应用名称 [必填]
	'NAME'						=> '主题讨论',
	// 应用简介 [必填]
	'DESCRIPTION'				=> '主题讨论',
	// 托管类型 [必填]（0:本地应用，1:远程应用）
	'HOST_TYPE'					=> '0',
	// 前台入口 [必填]（格式：Action/act）
	'APP_ENTRY'					=> 'Index/index',
	// 应用图标 [必填]
	'ICON_URL'					=> SITE_URL . '/apps/research/Appinfo/icon_app.png',
	// 应用图标 [必填]
	'LARGE_ICON_URL'			=> SITE_URL . '/apps/research/Appinfo/icon_app_large.png',
	// 版本号 [必填]
	'VERSION_NUMBER'			=> '1000',
	// 作者名 [必填]
	'AUTHOR_NAME'				=> 'frsun',
	// 作者Email [必填]
	'AUTHOR_EMAIL'				=> 'frsun@iflytek.com',

);