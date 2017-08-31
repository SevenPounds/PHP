<?php
if (!defined('SITE_PATH')) exit();

$sql = array(
	// vote数据
	"DROP TABLE IF EXISTS `ts_announcement`;",
	"DROP TABLE IF EXISTS `ts_announcement_attach`;",
);

foreach ($sql as $v) {
	$res = M('')->execute($v);
}
