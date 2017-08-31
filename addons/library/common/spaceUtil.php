<?php
/**
* 个人空间 共用小方法库
* @author yangli4
*/


/**
* 上册文件转储后
* @author yangli4
*
* @return $targetFile string 文件转储后的完整路径
*/
function moveUploadFile(){
	set_time_limit(0);
	$targetFile = '';
	if (!empty($_FILES)) {
		$file = $_FILES['Filedata']['tmp_name'];
		$fileParts = pathinfo($_FILES['Filedata']['name']);
		
		$targetFolder = UPLOAD_PATH;
		if(!is_dir($targetFolder)){  //如果不存在该文件夹
			mkdir($targetFolder, 0777);  //创建文件夹
		}
		chmod($targetFolder, 0777);  //改变文件模式
		//拷贝临时资源文件
		$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
		move_uploaded_file($file, $targetFile);
	}

	return $targetFile;
}
/**
* 获取请求资源的相关参数数组
* @author yangli4
*/
function getRequestResParams(){
	$params =array(
		//关键词
		'keywords' => $_REQUEST['keywords'],
		//标题
		'title' => $_REQUEST['title'],
		//描述
		'description' => $_REQUEST['description'],
		//资源类型
		'type' => isset($_REQUEST['type']) ? $_REQUEST['type'] : "0000",
		//资源来源
		'source' => isset($_REQUEST['source']) ? $_REQUEST['source'] : "UGC",
        //上传者
        'creator' => $_REQUEST["creator"],
		//科目
		'subject' => $_REQUEST["subject"],
		//出版社
		'publisher' => $_REQUEST["publisher"],
		//年级
		'grade' => $_REQUEST["grade"],
		//册别
		'volumn' => $_REQUEST["volumn"],
		//课本
		'book' => $_REQUEST["book"],
		//单元
		'unit' =>$_REQUEST["unit"],
		//课
		'course' => $_REQUEST["course"]
	);
	
	$info = array();
	foreach ($params as $key => $value) {
		if(!empty($value)){
			$info[$key]=$value;
		}
	}

	return $info;

}

?>