<?php

/**
* 获取资源分类
* @author yxxing
*/
function getRestype($toSelect = false){

    $restype = array();
	if($toSelect)
		$restype['0000'] = array("code"=>"0000", "name"=>"请选择");
	$restype["1400"] = array("code"=>"1400", "name"=>"电子教材");
	$restype['0100'] = array("code"=>"0100", "name"=>"教学设计");
	$restype["0600"] = array("code"=>"0600", "name"=>"教学课件");
	$restype["0300"] = array("code"=>"0300", "name"=>"媒体素材");
	$restype["0400"] = array("code"=>"0400", "name"=>"习题");
	$restype["0500"] = array("code"=>"0500", "name"=>"试卷");
	$restype["0200"] = array("code"=>"0200", "name"=>"教学视频");
	$restype["1600"] = array("code"=>"1600", "name"=>"实验设计");
	$restype["1700"] = array("code"=>"1700", "name"=>"拓展视野");
	
	return $restype;
}

/**
 * 返回类型名称
 * @param string $code
 * @return string
 */
function getNameByCode($code){
	switch($code){
		case "0302":
		case "0602":
			return "文档";
			break;
		case "0303":
			return "图片";
			break;
		case "0304":
			return "音频";
			break;
		case "0305":
			return "视频";
			break;
		case "0306":
			return "动画";
			break;
		case "1205":
		case "1206":
			return "卡包";
			break;
		default:
			return "其他";
			break;
	}
}

/**
 * 返回用户名及其链接
 * @param string $var
 * @param string $type 传入参数类型，cyuid OR login
 * @param string $resource 资源来源（UGC、网台等）
 * @return bool|string 用户的链接
 */
function getUserURL($var, $type="login", $resource = "UGC"){
    if(empty($resource)) $resource = "UGC";
	$result = S("user_url_".$var."_".$type.'_'.$resource);
	if($result)
		return $result;
	switch($type){
		case "uid":
			$user = D("User")->where(array("uid"=>$var))->field("uid,uname,login")->find();
			break;
		case "login":
			$user = D("User")->where(array("login"=>$var))->field("uid,uname,login")->find();
			break;
		default:
			$user = array();
			break;
	}
	if(empty($user) || $user['login'] == 'admin' || strtoupper($resource) != 'UGC'){
		$result = "网台";
		S("user_url_".$var."_".$type.'_'.$resource, $result, 3600);
	} else{
		$space_url = U("public/Profile/index",array("uid"=>$user['uid']));
		$result = "<a target='_blank' event-node='face_card' class='blue' uid='".$user['uid']."' show='yes' href='".$space_url."' title='".$user['uname']."'>".mStr($user['uname'],10,"UTF-8")."</a>";
		S("user_url_".$var."_".$type.'_'.$resource, $result, 3600);
	}
	echo $result;
}