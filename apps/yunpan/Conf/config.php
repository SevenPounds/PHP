<?php
return array(
	// 集成区县平台新增教研平台默认文件夹
	'DOC_LIST' => array(
			array('code'=>1001,'name'=>'我的备课本'),
			array('code'=>1002,'name'=>'我的收藏')
			),
	'RES_TYPE' =>array(
			array('code'=>'0100','name'=>'教案'),
			array('code'=>'0600','name'=>'课件'),
			array('code'=>'0300','name'=>'素材'),
			array('code'=>'0400','name'=>'习题'),
			array('code'=>'1901','name'=>'微课')
	),

    'EDUSNS_MODULE' => "个人空间",
    'EDUSND_ACTION' => array("upload" =>"传资源","import"=>"保存到我的文档")
);