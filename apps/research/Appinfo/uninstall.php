<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// photo数据
	"DROP TABLE IF EXISTS `{$db_prefix}research`;",
	"DROP TABLE IF EXISTS `{$db_prefix}research_attach`;",
	"DROP TABLE IF EXISTS `{$db_prefix}research_post`;",
	"DROP TABLE IF EXISTS `{$db_prefix}research_user`;",
);

foreach ($sql as $v)
	M('')->execute($v);