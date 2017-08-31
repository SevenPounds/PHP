<?php
/**
* 网关资源相关共用方法库
* @author yangli4
*/
 import(ADDON_PATH . '/library/Clients/ResourceClient.php');


/**
 * 通过网关资源信息初始化本地资源信息
 * @param string $rid,必须提供， 资源id
 *
 */
function initResinfoByGateinfo($rid){
	$resClient = new ResourceClient();
	$res='';
	$gateresinfos =$resClient->Res_GetResIndex($rid);
	if($gateresinfos && $gateresinfos->statuscode == 200 && $gateresinfos->data){
		$res = getInitLocalResourceArray($gateresinfos->data[0]);
	}else{
		return array();
	}
	return $res;
}

/**
 * 成功返回本地资源资源信息数组对象
 * @param array $res 从网关获取的资源信息
 * @param int $uid  用户UID
 */
function getInitLocalResourceArray($res){
	//上传信息记录到本地
	$reslocal=array();
	if(!$res){
		return $reslocal;
	}
	else{
		$reslocal['rid'] =$res->general->id;
		if(strrpos($res->general->title, '.')>0){
			$reslocal['title'] =substr($res->general->title,0,strrpos($res->general->title, '.'));
		}
		else {
			$reslocal['title'] = $res->general->title;
		}
		if(is_array($res->tags)){
			$reslocal['keywords'] =$res->tags[0];
		}
		$reslocal['description'] =$res->general->description;
		$reslocal['username'] = $res->general->creator;
		$reslocal['creator'] = $res->general->creator;
		$reslocal['size'] = $res->general->length;
		$reslocal['uploaddateline'] = strtotime($res->date->uploadtime);
		$reslocal['suffix'] = strtolower($res->general->extension);
		$reslocal['type1'] = !empty($res->properties->type)?$res->properties->type[0]:'';
		$reslocal['downloadtimes'] = !empty($res->statistics->downloadcount)?$res->statistics->downloadcount:0;
		$reslocal['praisetimes'] =  !empty($res->statistics->up)?$res->statistics->up:0;
		$reslocal['negationtimes'] =  !empty($res->statistics->down)?$res->statistics->down:0;
		$reslocal['praiserate'] = round($res->statistics->reputablerate ? $res->statistics->reputablerate : 0);
		//将平分转换为5分制
		$reslocal['score'] = round(($res->statistics->score ? $res->statistics->score : 0)*1.0/20, 1);
		$reslocal['grade'] =  !empty($res->properties->grade)?$res->properties->grade[0]:'';
		$reslocal['subject'] =  !empty($res->properties->subject)?$res->properties->subject[0]:'';
		$reslocal['restype'] =  !empty($res->properties->type)?$res->properties->type[0]:'';
		return $reslocal;
	}
}

?>
