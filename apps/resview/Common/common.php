<?php
function getPreviewUrl($resid,$type='preview'){
	$preview_url = D('Resource')->Resource->Res_GetResIndex($resid,true);
	$preview_url = $preview_obj->data[0]->file_url;
	return $preview_url;
}

function getShortImg($extension,$isbig=false){
	$extension = strtolower($extension);
	if($isbig){
		$img = 'img_';
	}else{
		$img = 'icon_';
	}
	switch ($extension){
		case "doc":
		case "docx":
			$img.='doc';
			break;
		case "txt":
			$img.='txt';
			break;
		case "xls":
		case "xlsx":
			$img.='xls';
			break;
		case "mp3":
		case "wma":
		case "wav":
		case "ogg":
		case "ape":
		case "mid":
		case "midi":
			$img.='video';
			break;
		case "jpg":
		case "jpeg":
		case "bmp":
		case "png":
		case "gif":
			$img.='img';
			break;
		case "swf":
			$img.='swf';
			break;
		case "asf":
		case "avi":
		case "rmvb":
		case "mp4":
		case "mpeg":
		case "wmv":
		case "flv":
		case "3gp":
			$img.='movie';
			break;
		case "zip":
		case "rar":
			$img.='zip';
			break;
		case "card":
			$img.='card';
			break;
		case "ppt":
		case "pptx":
			$img.='ppt';
			break;
		case "pdf":
			$img.='pdf';
			break;
		case "rtf":
			$img.='rtf';
			break;
		default:
			$img.='default';
			break;
	}
	return ($img.'.png');
}

/**
 * 资源长度剪截
 * @param unknown $length
 * @return string
 */
function getResSize($length){
	if($length<1024){	 	
		$size = $length;
		return $size.'B';		
	}else{
		$size = round($length/1024,2);
		if($size>=1024){
			$size = round($length/(1024*1024),2);
			return $size."MB";
		}
		if($size >= 1024*1024){
			$size = round($length/(1024*1024*1024),2);
			return $size."GB";
		}
		return $size."KB";
	}
}


/**
 * 获取评分对应的描述
 * @param $score
 * @return string
 */
function getStarDescription($score){
	switch($score){
		case 1:
			$description = "很差";break;
		case 2:
			$description = "较差";break;
		case 3:
			$description = "还行";break;
		case 4:
			$description = "推荐";break;
		case 5:
			$description = "力荐";break;
		default:
			$description = "很差";break;
	}
	return $description;
}

/**
 * 获取资源的真实名称
 * @param string $title
 * @param stirng $extension
 * @return Ambigous <string, unknown>
 */
function getResFilename($title,$extension){
	$realTitle = $title;
	$pathinfo = pathinfo($realTitle);
	if(strtolower($pathinfo['extension'])!=$extension){
		$realTitle = $realTitle.'.'.$extension;
	}
	return $realTitle;
}

/**
 * 获取页面分页（学科资源首页特殊处理）
 * @param int $page 当前页码
 * @param int $count 资源总数
 * @param int $prepage 每页资源数目
 * @param string $fun 要调用的js异步方法名
 */
function getPaging($page,$count,$prepage,$fun){
	if($count%$prepage == 0){
		$totalpage = $count/$prepage;
	}else{
		$totalpage = intval($count/$prepage) + 1;
	}
	if($totalpage < 2){
		return "";
	}
	$paging = '';
	if($page > 1){
		$paging = $paging."<a class='pre' onclick='".$fun."(this);' page='".($page - 1)."'>上一页</a>";
	}
	if(($page-3) > 1){
		$paging = $paging."<a onclick='".$fun."(this);' page='1'>1..</a>";
	}else if(($page-3) == 1 && $totalpage == 4){
		$paging = $paging."<a onclick='".$fun."(this);' page='1'>1</a>";
	}
	for($j = ($page - 2); $j < $page; $j++){
		if(($j) > 0){
			$paging = $paging."<a onclick='".$fun."(this);' page='$j'>$j</a>";
		}
	}
	$paging = $paging."<a class='current' href='javascript:void(0);'>$page</a>";
	for($i = ($page + 1); $i < ($page + 3); $i++){
		if(($i) <= $totalpage){
			$paging = $paging."<a onclick='".$fun."(this);' page='$i'>$i</a>";
		}
	}
	if(($page + 3) < $totalpage){
		$paging = $paging."<a onclick='".$fun."(this);' page='$totalpage'>..$totalpage</a>";
	}else if(($page + 3) == $totalpage){
		$paging = $paging."<a onclick='".$fun."(this);' page='$totalpage'>$totalpage</a>";
	}
	if($page < $totalpage){
		$paging = $paging."<a class='next' onclick='".$fun."(this);' page='".($page + 1)."'>下一页</a>";
	}
	return $paging;
}

/**
 * 生成二维码
 * @param  [type] $downloadurl [description]
 * @return [type]              [description]
 */
function getQRcode($downloadurl){
    require_once(ADDON_PATH.'/library/phpqrcode.php');
    return QRcode::png($downloadurl);
}

/**
 * 对下载地址名称进行字符集编码和去除特殊字符；
 */
function encodedowloadUrl($filename){
	$codeArr =array('(',')','{','}','[',']',',');
	$filename =str_replace($codeArr, '', $filename);
	return rawurlencode($filename);
}
?>