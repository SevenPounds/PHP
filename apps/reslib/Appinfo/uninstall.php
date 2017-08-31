<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
//资源库不允许被删除
// 	"DROP TABLE IF EXISTS `{$db_prefix}resource`;",
// 	"DROP TABLE IF EXISTS `{$db_prefix}resource_capacity`;",
// 	"DROP TABLE IF EXISTS `{$db_prefix}resource_operation`;",
// 	"DROP TABLE IF EXISTS `{$db_prefix}resource_recommend`;",
);

foreach ($sql as $v)
	M('')->execute($v);