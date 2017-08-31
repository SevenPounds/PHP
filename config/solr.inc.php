<?php
if (!defined('SITE_PATH')) exit();

return array(
	'SOLR_FIELD_UPDATE' => array('ISOPEN'=>true,
													'USER'=>array('0'=>10, '1000'=>50),
													'MSGROUP'=>array('0'=>5, '1000'=>10),
													'NEWS'=>array('0'=>10)
											),	//SOLR Schema的微博数与访问量更新间隔设置
	'SOLR_CONNECT_TIMEOUT' => 1, //超时设置。单位秒
);
