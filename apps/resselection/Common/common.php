<?php
function getPreviewUrl($resid,$type='preview'){
// 	$preview_obj = ResourceServer::getInstance()->download_resource($resid,$type);
    $resClient = D('CyCore')->Resource;
	$preview_url = $resClient->Res_GetResIndex($resid,true);
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
 * 给资源审核不通过的人发送消息
 * @param int $uid 作出行为的用户id
 * @param string $uname 审核者
 * @param int $noticedid 被通知者的id
 * @param string $title 资源的名称
 * @param int $rid 资源Id
 * @param int $node 审核状态 audit_pass已经审核通过，audit_un_pass审核未通过 ，rete_resource遴选结果
 * @param string $rid 资源ID
 * @param string $cause 审核结果
 */
function addMessage($uid, $uname, $noticedId, $title, $node, $rid, $cause=""){
	if(!$noticedId || $uid == $noticedId )
		return false;
	$config = array();
	$config ['resource'] = t($title);
	$config ['sourceurl'] = "[PREVIEW_SITE_URL]&id=".$rid;
	$config ['auditor_url'] = U("public/Profile/index", array('uid'=>$uid));
	$config ['auditor'] = $uname;
	$config ['cause'] = $cause;
	model ( 'Notify' )->sendNotify ($noticedId, $node, $config );
}
?>