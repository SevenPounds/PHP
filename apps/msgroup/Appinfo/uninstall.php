<?php
if (!defined('SITE_PATH')) exit();

$db_prefix = C('DB_PREFIX');

$sql = array(
	// msgroup数据
);

foreach ($sql as $v)
	M('')->execute($v);