<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// photo数据
	"DROP TABLE IF EXISTS `{$db_prefix}pingke`;",
	"DROP TABLE IF EXISTS `{$db_prefix}pingke_attach`;",
	"DROP TABLE IF EXISTS `{$db_prefix}pingke_member`;",
	"DROP TABLE IF EXISTS `{$db_prefix}pingke_post`;",
);

foreach ($sql as $v)
	M('')->execute($v);