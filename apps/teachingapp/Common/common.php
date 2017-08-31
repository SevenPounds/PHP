<?php
/**
 * 计算资源长度
 * @param unknown $length 资源长度
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
 * 根据资源类型显示图片
 * @param unknown $extension
 * @param string $isbig
 * @return string
 */
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
 * 生成二维码
 * @param  [type] $downloadurl [description]
 * @return [type]              [description]
 */
function getQRcode($downloadurl){
	require_once(ADDON_PATH.'/library/phpqrcode.php');
	return QRcode::png($downloadurl);
}
?>