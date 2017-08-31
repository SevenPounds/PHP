<?php
/*
* 资源平台资源汇聚页 相关服务
*/

define('SITE_PATH', dirname(dirname(__FILE__)));
$_GET['app'] = 'api';
$_GET['mod'] = 'spaceApp';
$_GET['act'] = 'index';


require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

require_once SITE_PATH . '/addons/model/ZoneTypeModel.class.php';
require_once SITE_PATH . '/apps/yunpan/Lib/Model/FolderTypeModel.class.php';

date_default_timezone_set('PRC');

/**
 * 根据起始位置，查询最大数量，以及排序字段，查询 “在线答疑”
 */
function getOnlineAnswers($start = 0,$limit = 10,$order="answer_count desc"){
	$questionD = D('Question','onlineanswer');
	return $questionD->getQuestions($start,$limit,$order);
}

/**
 * 根据起始位置，查询最大数量，以及排序字段，查询 “课题研究（主题讨论）”
 */
function getResearchInfos($start = 0,$limit = 10,$order = "discuss_count desc , closedtime desc"){
	$researchD = D('Research','research');
	return $researchD->getResearchInfos($start,$limit,$order);
}


/**
 * 根据起始位置，查询最大数量，以及排序字段，查询 “优秀空间”
 * $roleName 角色名称：	PROVINCE_RESAERCHER			//省级教研员
 * 						CITY_RESAERCHER				//市级教研员
 * 						COUNTY_RESAERCHER			//区县级教研员
 * 						RESAERCHER					//教研员(传入这个值，按需求将查询 RESAERCHER，COUNTY_RESAERCHER，CITY_RESAERCHER，PROVINCE_RESAERCHER)

 * 						TEACHER						//教师
 * 						STUDENT 					//学生
 * 						PARENTS 					//家长
 */
function getExcellentSpaces($start = 0,$limit = 10,$orderkey = 'follower_count',$orderDir='desc',$roleName='TEACHER'){
	$userD = M('User');
	switch ($roleName) {
		case 'RESAERCHER':
			$roleNames = array();
			$roleNames[] = UserRoleTypeModel::RESAERCHER;
			$roleNames[] = UserRoleTypeModel::PROVINCE_RESAERCHER;
			$roleNames[] = UserRoleTypeModel::CITY_RESAERCHER;
			$roleNames[] = UserRoleTypeModel::COUNTY_RESAERCHER;
			return $userD->getRolePersons($roleNames,$limit,$orderkey,$orderDir,$start);
			// return $userD->getRolePersons(UserRoleTypeModel::RESAERCHER,$limit,$orderkey,$orderDir,$start);
			break;			
		case 'TEACHER':
			return $userD->getRolePersons(UserRoleTypeModel::TEACHER,$limit,$orderkey,$orderDir,$start);
			break;
		case 'PROVINCE_RESAERCHER':
			return $userD->getRolePersons(UserRoleTypeModel::PROVINCE_RESAERCHER,$limit,$orderkey,$orderDir,$start);
			break;
		case 'CITY_RESAERCHER':
			return $userD->getRolePersons(UserRoleTypeModel::CITY_RESAERCHER,$limit,$orderkey,$orderDir,$start);
			break;
		case 'COUNTY_RESAERCHER':
			return $userD->getRolePersons(UserRoleTypeModel::COUNTY_RESAERCHER,$limit,$orderkey,$orderDir,$start);
			break;
		case 'STUDENT':
			return $userD->getRolePersons(UserRoleTypeModel::STUDENT,$limit,$orderkey,$orderDir,$start);
			break;
		case 'PARENTS':
			return $userD->getRolePersons(UserRoleTypeModel::PARENTS,$limit,$orderkey,$orderDir,$start);
			break;
		default:
			return null;
			break;
	}
}

/**
 * 根据起始位置，查询最大数量，以及排序字段，查询 “名师工作室”
 */
function getMingshiSpaces($start = 0,$limit = 10,$orderkey = 'follower_count',$orderDir='desc'){
	$MSGroupMemberD = D('MSGroupMember','msgroup');
	return $MSGroupMemberD->getMemberRankingList($start,$limit,$orderkey,$orderDir);
}

/**
 * 获取最热门的网络调研
 * @author zhaoliang <zhaoliang@iflytek.com>
 * @param int $limit <网络调研数量，默认为5条>
 */
function getHotVotes($limit = 5){
	$votes = D("Vote","vote")->order("vote_num desc")->limit($limit)->select();
	$votes_new = array();
	foreach ($votes as $val){
		$uid = $val['uid'];
		$user = model('User')->getUserInfo($uid);
		$val["avatar"] = $user["avatar_middle"];
		$votes_new[] = $val;
	}
	return $votes_new;
}

/**
 * 获取最新的在线答疑
 * @author zhaoliang <zhaoliang@iflytek.com>
 * @param int $limit <获取的数量，默认为10条>
 */
function getNewOnlineAnswers($limit = 10){
	return D('Question','onlineanswer')->where("isDeleted=0")->order("ctime desc")->limit($limit)->select();
}

/**
 * 获取成员数最多的名师工作室
 * @author zhaoliang <zhaoliang@iflytek.com>
 * @param int $limit <获取数量，默认为2个>
 */
function getHotMsGroup($limit){
	$sql = "select g.gid,g.group_name,g.discription,g.creator_uid,g.ctime,m.value from ts_msgroup as g inner join(SELECT gid,value FROM `ts_msgroup_data` where `key`='follower_count') as m where g.isdel = 0 and g.gid = m.gid ORDER BY m.`value` desc limit 0,".$limit;
	$msgroups =  D('MSGroup','msgroup')->query($sql);
	$msgroups_new = array();
	foreach ($msgroups as $val){
		$gid = $val['gid'];
		D('MSAvatar','msgroup')->init(array("app"=>"msgroup","rowid"=>$gid));
		$val["avatar"] = D('MSAvatar','msgroup')->getAvatar();
		$sql = 'SELECT res.id,res.rid,res.restype,res.title,oper.operationtype,oper.gid from ts_resource_operation oper '
				.' LEFT JOIN ts_resource res on oper.resource_id = res.id'
				.' WHERE oper.gid = '. $gid .' and oper.operationtype = ' . ResoperationType::MSGROUP_UPLOAD . ' ORDER BY res.id desc limit 0,' . $limit;
		$res_list = D('MSGroup','msgroup')->query($sql);
		$val['res_list'] = $res_list;
		$msgroups_new[] = $val;
	}
	return $msgroups_new;
}


/**
 * 通过id，获取个人空间或名师工作室信息
 * 
 * @param array $sids 编号数组(类型+编号)
 * @param int $login_name 查询者登陆用户名，有此条件则查询是否关注过返回的空间
 */
function getSpaceDetailBySids($sids,$login_name){
	if(empty($sids)){
		return null;
	}
	$hostUid=null;
	if(!empty($login_name)){
		$hostUid = D("User")->where(array('login' => $login_name))->getField("uid");
	}

	$teacherIDs=array();
	$researcherIDs=array();
	$studentIDs=array();
	$parentIDs=array();
	$msgroupIDs=array();

	foreach ($sids as $value) {
		$type = substr($value, 0,1);
		$id = substr($value, 1);
		switch (intval($type)) {
			case ZoneTypeModel::TEACHER:
			$teacherIDs[]=$id;
			break;			
			case ZoneTypeModel::RESEARCHER:
			$researcherIDs[]=$id;
			break;			
			case ZoneTypeModel::STUDENT:
			$studentIDs[]=$id;
			break;			
			case ZoneTypeModel::PARENTS:
			$parentIDs[]=$id;
			break;			
			case ZoneTypeModel::STUDIO:
			$msgroupIDs[]=$id;
			break;	

			default:
				break;
		}
	}

	$resTea = getSpaceById(ZoneTypeModel::TEACHER,$teacherIDs,$hostUid);
	$resResc = getSpaceById(ZoneTypeModel::RESEARCHER,$researcherIDs,$hostUid);
	$resStu = getSpaceById(ZoneTypeModel::STUDENT,$studentIDs,$hostUid);
	$resPar = getSpaceById(ZoneTypeModel::PARENTS,$parentIDs,$hostUid);
	$resMsg = getSpaceById(ZoneTypeModel::STUDIO,$msgroupIDs,$hostUid);

	$res = array();
	$res = array_merge($res,$resTea);
	$res = array_merge($res,$resResc);
	$res = array_merge($res,$resStu);
	$res = array_merge($res,$resPar);
	$res = array_merge($res,$resMsg);

	return $res;
}


/**
 * 通过id，获取个人空间或名师工作室信息
 * 
 * @param int $type 搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
 * @param array $ids 编号数组
 * @param int $hostUid 查询者uid，有此条件则查询是否关注过返回的空间
 */
function getSpaceById($type=1,$ids,$hostUid){
	if(empty($ids)|| empty($type)){
		return array();
	}
	$result = array();
	switch ($type) {
		case ZoneTypeModel::TEACHER:
		case ZoneTypeModel::RESEARCHER:
		case ZoneTypeModel::STUDENT:
		case ZoneTypeModel::PARENTS:
			$fields=array('uid','sex','location','school');

			$map = array();
			$map['uid']=array('in',$ids);
			$res = D('User')->field($fields)->where($map)->findAll();
			//查询用户头像和相关统计数量
			$tempM = array();
			$tempM['key'] = array('in',array("'upload_res_count'","'follower_count'","'visitor_count'"));
			$tempM['uid']=array('in',$ids);
			$statistics = D('UserData')->where($tempM)->field(array('uid as id','key','value'))->select();
			foreach ($res as $user) {
				$tempM['uid']=$user['uid'];
				//相关统计数量
				$user['statistics'] = arrayConvert($user['uid'],$statistics);
				//头像
				$user['avatar'] = model('Avatar')->init($user['uid'])->getUserPhotoFromCyCore($user['uid']);

				//是否被关注
				if (!empty($hostUid)) {
					//被关注者是不是自己
					$user['is_host'] = ($hostUid == intval($user['uid']))?1:0;
					if($user['is_host'] == 0){
						$fCout = D('user_follow')->where(array('uid'=>$hostUid,'fid'=>$user['uid'],'type'=>0))->count();
						$user['is_followed']= $fCout>0?1:0;
					}
				}

				$user['type']=$type;				
				$user['key']=$type.$user['uid'];
				$result[]=$user;
			}
			return  $result;
			break;
		case ZoneTypeModel::STUDIO:
			$map=array('key' => array('in',array("'member_count'","'follower_count'","'visitor_count'")));
			$map['gid']=array('in',$ids);
			$statistics = D('MSGroupData','msgroup')->where($map)->field(array('gid as id','key','value'))->select();
			//查询者所创建的名师工作室
			if (!empty($hostUid)) {
				$hostMSG_Arr = D('MSGroup','msgroup')->where(array('creator_uid'=>$hostUid))->getField('gid');
				if(!is_array($hostMSG_Arr)){
					$hostMSG_Arr = array($hostMSG_Arr);
				}
			}

			foreach ($ids as $value) {
				$temp = array();
				$temp['msgid'] = $value;
				$temp['statistics'] = arrayConvert($value,$statistics);
				D('MSAvatar','msgroup')->init(array("app"=>"msgroup","rowid"=>$value));
				$temp["avatar"] = D('MSAvatar','msgroup')->getAvatar();
				//是否被关注
				if (!empty($hostUid)) {
					if(!empty($hostMSG_Arr) && in_array($value, $hostMSG_Arr)) {
						$temp['host_is_creator']=1;
					}else{
						$temp['host_is_creator']=0;
						$fCout = D('user_follow')->where(array('uid'=>$hostUid,'fid'=>$temp['msgid'],'type'=>3))->count();
						$temp['is_followed']= $fCout>0?1:0;
					}
				}
				$temp['type']=$type;						
				$temp['key']=$type.$temp['msgid'];
				$result[] = $temp;
			}
			return $result;
			break;
		default:
			return null;
			break;
	}	
}

function arrayConvert($id,$srcArr){
	$res=array();
	foreach ($srcArr as $value) {
		if($value['id'] == $id){
			$res[$value['key']]=$value['value'];
		}
	}
	return $res;
}


/**
 * 关注空间
 * 
 * @param int $type 搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
 * @param int $tid 目标id
 * @param int $login_name 关注者登陆用户名
 */
function spaceFollow($type=1,$tid,$login_name){
	$uid=null;
	if(!empty($login_name)){
		$uid = D("User")->where(array('login' => $login_name))->getField("uid");
	}
	switch (intval($type)) {
		//关注用户
		case ZoneTypeModel::TEACHER:
		case ZoneTypeModel::RESEARCHER:
		case ZoneTypeModel::STUDENT:
		case ZoneTypeModel::PARENTS:
			return D('Follow')->doFollow($uid,$tid,0);
			break;	
		//关注名师工作室	
		case ZoneTypeModel::STUDIO:
			return D('Follow')->doFollow($uid,$tid,3);
			break;
		default:
			return null;
			break;
	}
}

/**
 * 取消关注
 * 
 * @param int $type 搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
 * @param int $tid 目标id
 * @param int $login_name 关注者登陆用户名
 */
function spaceUnfollow($type=1,$tid,$login_name){
	$uid=null;
	if(!empty($login_name)){
		$uid = D("User")->where(array('login' => $login_name))->getField("uid");
	}
	switch ($type) {
		//关注用户
		case ZoneTypeModel::TEACHER:
		case ZoneTypeModel::RESEARCHER:
		case ZoneTypeModel::STUDENT:
		case ZoneTypeModel::PARENTS:
			return D('Follow')->unFollow($uid,$tid,0);
			break;	
		//关注名师工作室	
		case ZoneTypeModel::STUDIO:
			return D('Follow')->unFollow($uid,$tid,3);
			break;
		default:
			return null;
			break;
	}
}



/**
 * 添加一条用户改密验证信息
 * @param array $data 验证信息数组，
 *				$data['ctime'],提交时间；
 *				$data['loginname'],用户登录名；
 *				$data['key'],验证信息；
 */
function addUserPwdVerify($data){
	return D('UserPwdVerify')->addUserPwdVerify($data);
}

/**
 * 删除户改密验证信息
 * @param array $map 条件数组
 */
function delUserPwdVerify($map){
	return D('UserPwdVerify')->delUserPwdVerify($map);
}


/**
 * 获取用户改密验证信息
 * @param string $loginname 用户登录名
 */
function getUserPwdVerify($loginname){
	return D('UserPwdVerify')->getUserPwdVerify($loginname);
}

/**
 * 资源收藏
 * @param string $login_name 用户名
 * @param string $rid 资源id
 * @param boolean $is_package 是否是资源包
 * @return array('status'=>true|false,'message'=>'xx')
 */
function collectResource($login_name,$rid,$is_package){
	return D('YunpanFavorite','yunpan')->collectResource($login_name,$rid,$is_package);
}
/**
 * 修改云盘用户上传资源数
 * @param string $login_name
 * @param int $resCount
 */
function modifyUploadYunpanCount($login_name, $resCount){
	if($resCount>0) {
		$userMap = D("User")->getUserInfoByLogin($login_name, null);
		D('UserData')->updateKey('upload_yunpan_count', $resCount, true, $userMap['uid']);
		Log::write("uid: ".$userMap['uid']." upload_yunpan_count +".$resCount." Resource!");
	}
}
/**
 * 修改云盘已使用容量
 * @param string $login_name
 * @param int $size
 */
function modifyYunpanUsedSize($login_name, $size){
	if($size > 0){
		D("Yunpan", "yunpan")->increaseUsed($login_name, $size);
	} else if($size < 0){
		D("Yunpan", "yunpan")->decreaseUsed($login_name, $size);
	}
}

/**
 * 获取云盘容量
 * @param string $login
 */
function getCapacityInfoByLogin($login){
	return D("Yunpan", "yunpan")->getCapacityInfoByLogin($login);
}

/**
 * 资源下载，保存下载记录方法
 * @param array $res_download
 * ("fid"=>资源id，"dateline"=>当前时间格式'Y-m-d H:i:s'，"login_name"=>登录用户名,
 * "type"=>资源类型,"download_source"=>下载来源:01 资源网关02 其他)
 */
function saveDownloadRecord($res_download){
	return D("YunpanDownload", "yunpan")->saveOrUpdate($res_download);
}

/**
 * 云盘公开记录保存、更新
 * @return 1:成功,0:失败,-1:参数不正确
 * @param array $res_publish
 * ("fid"=>资源id，"dateline"=>当前时间格式'Y-m-d H:i:s'，"login_name"=>登录用户名,
 * "type"=>资源类型,"open_position"=>公开位置：01 个人主页 02 资源网关)
 */
function savePublishRecord($res_publish){
	return D("YunpanPublish","yunpan")->saveOrUpdate($res_publish);
}

/**
 * 向用户发送消息
 * @param string $loginName
 * @param string $node
 * @param string $data
 * @reutrn array('status'=>true|false,'msg'=>'xx')
 */
function sendNotify($loginName, $node, $data){
	$result = array();
	if(empty($loginName) || empty($node) || empty($data)){
		$result['status'] = false;
		$result['msg'] = '参数不能为空';
		return $result;
	}
	addLang('yunpan');
	$user = D('User')->getUserInfoByLogin($loginName);
	if(empty($user)){
		$result['status'] = false;
		$result['msg'] = '找不到该用户';
		return $result;
	}
	D('Notify')->sendNotify($user['uid'], $node, $data);
	$result['status'] = true;
	$result['msg'] = '发送消息成功';
	return $result;
}

/**
 * 根据条件获取用户角色映射表
 * @param array $conditions 查询的条件
 * @return array() 用户映射表数组
 */
function listRoleMap($conditions = array()){
	return D('UserRoleMap')->listRoleMap($conditions);
}

/**
 * 判断当天增加积分的上传是否已经达到上限
 * @param $login string 登录名
 * @return array 返回结果数组
 */
function isAddScore($login){
    $totalCount = C('UPLOAD_SCORE_LIMIT');
    $currentCount = D('UploadRecord')->getCuttentCounts($login);
    if($currentCount > $totalCount){
        $result['status'] = '500';
        $result['msg'] = '当天的上传已达到上限数!';
    }else{
        $result['status'] = '200';
        $result['msg'] = '积分增加';
    }

    return $result;
}

/**
 * 根据用户登录名和资源id发布动态
 * @param string $login 用户登录名
 * @param string $app 应用名称，如：yunpan
 * @param string $content 动态内容
 * @param string $body 主题部分，例如：'我上传了资源【'.$file_name.'】'
 * @param string $source_url 资源访问链接
 * @return 返回调用结果
 */
function addAuditFeed($login, $app, $content, $body, $source_url){
	$user = D("User")->getUserInfoByLogin($login, array());
	$uid = $user['uid'];
	if($uid){
		$data = array(
				"content" => $content,
				"body" => $body,
				"source_url" => $source_url
		);
		
		$addFeed = D('Feed')->put($uid, $app, 'post', $data);
		if($addFeed){
			$result['status'] = true;
			$result['msg'] = '发表动态成功';
		}else{
			$result['status'] = false;
			$result['msg'] = '发表动态失败';
		}
	}else{
		$result['status'] = false;
		$result['msg'] = '无此用户！';
	}
	return $result;
}

/**
 * 根据用户登录名初始化用户空间
 * @param string $login 用户登录名
 * @return 用户空间uid
 */
function initUserSpace($login){
	return D("CyUser")->initUserSpace($login);
}

/**
 * 根据用户登录名，获取用户的详细信息
 * @param string $loginname
 * @return 用户信息
  *2014-8-6
 */
function getUserData($loginname){
	if(empty($loginname)){
		return false;
	}else{
		$user=D("User")->getUserInfoByLogin($loginname);
		$user['userData']=D("UserData")->getUserDataNoCache($user['uid']);
	 return $user;
	}
}

/**
 * 获取是否关注状态
 * @param string $uid 登录人uid
 * @param string $fid 关注人uid
 * @return string
  *2014-8-6
 */
function getFollowingState($username, $fid){
	$user=D("User")->getUserInfoByLogin($username);
	return D("Follow")->getFollowState($user['uid'], $fid);
}


/**
 * 同步资源发动态
 */
function shareFeed($login, $body,$rid,$content,$app,$source_url){
    $user = D("User")->getUserInfoByLogin($login, array());
    $uid = $user['uid'];
    if($uid){
    	if(empty($source_url)){
    		$source_url = C('RS_SITE_URL').'/index.php?app='.$app.'&mod=Rescenter&act=detail&id='.$rid;
    	}        
        $data = array(
            "content" => $content,
            "body" => $body,
            "source_url" => $source_url
        );

        $addFeed = D('Feed')->put($uid, $app, 'post', $data);
        if($addFeed){
            $result['status'] = '200';
            $result['message'] = '发表动态成功';
        }else{
            $result['status'] = '500';
            $result['message'] = '发表动态失败';
        }
    }else{
        $result['status'] = '504';
        $result['message'] = '此用户未开通个人空间！';
    }
    return $result;
}

/**
 * 判断用户是否开通了个人空间
 */
function hasUserSpace($login){
	$user = D("User")->getUserInfoByLogin($login, array());
	if(isset($user) && $user['uid']){
		$result['uid'] = $user['uid'];
		$result['status'] = '200';
		$result['message'] = '开通了个人空间';
	}else{
		$result['status'] = '504';
        $result['message'] = '此用户未开通个人空间！';
	}
	return $result;
}

/**
 * 同步资源发动态（完成资源分享发动态操作）
 */
function shareFeedComplete($data){
    if(empty($data['source_url'])){
        $data['source_url'] = C('RS_SITE_URL').'/index.php?app='.$data['app'].'&mod=Rescenter&act=detail&id='.$data['rid'];
    }

    $addFeed = D('Feed')->put($data['uid'], $data['app'], 'post', $data);
    if($addFeed){
        $result['status'] = '200';
        $result['message'] = '发表动态成功';
    }else{
        $result['status'] = '500';
        $result['message'] = '发表动态失败';
    }
    return $result;
}

/**
 * 分享资源到个人主页 
 *
 * $condition=array("cyuid"=>"用户cyuid",
	 * 				"uid"=>"用户uid",
	 *              "fid"=>"分享文件fid",
	 *              "filename"=>"分享文件名",
	 *               "desc"=>"分享描述",
	 *               "login"=>"用户登录名");
	 *retrun status:200：分享成功 500：分享失败  501：参数错误               
 */
function shareResToHome($condition){
	if(empty($condition['cyuid'])||empty($condition['fid'])||
	   empty($condition['filename'])){
		return array('status'=>'501','msg'=>'参数错误');
	  }
	  if(empty($condition['uid'])){
	  	$userInfo=D("User")->getUserInfoByLogin($condition['login']);	  	
	  }
	  $condition['uid']=$userInfo['uid'];
	  return D("ShareRes")->shareResToHome($condition);
	  
}


/**
 * 检测资源是否已被分享 
 * @param string $fid
 * @param string $login
 * result['sys_res']=1 同步资源已分享
 * result['per_page']=1 个人主页已分享
 * @return
  *2014-9-12
 */
function checkResHasShare($fid,$login){
	if(empty($fid)||empty($login)){
		return array();
	}
	$result=D("YunpanPublish","yunpan")->checkSharePosition($fid,$login);
	return $result;	
}

/**
 * 添加资源分享记录
 * @param  $condition=array(
 *        "fid"=>"文件id",
 *        "login"=>'登录用户名',
 *        "pos" =>"公开位置"，
 *        "filename"=>""
 *         );
  *2014-9-12
 */
function addShareRecord($condition){
	$pro = array (
			"fid" => $condition['fid'],
			"login_name" => $condition["login"],
			"dateline" => date ('Y-m-d H:i:s'),
			"open_position" =>$condition["pos"],
			"res_title" => $condition["filename"],
			"type" => $condition["type"],
			"rid" => $condition["rid"],
	);
	$pub_res=D("YunpanPublish","yunpan")->saveOrUpdate($pro);
}

/**
  * 更新某个用户的指定Key值的统计数目
  * Key值：
  * share_count:分享数
  * feed_count：微博总数
  * weibo_count：微博数
  * favorite_count：收藏数
  * following_count：关注数
  * follower_count：粉丝数
  * unread_comment：评论未读数
  * unread_atme：@Me未读数
  * @param string $key Key值
  * @param integer $nums 更新的数目
  * @param boolean $add 是否添加数目，默认为true
  * @param $login 登录名
  * @return array 返回更新后的数据
  *2014-9-15
 */
function updateKey($key, $nums, $add = true,$login){
	$user=D("User")->getUserInfoByLogin($login);
	$result= D("UserData")->updateKey($key,$nums,$add,$user['uid']);
	return $result;
}

/**
 * 删除资源公开到个人空间的记录;
 * @param 文件id  $fid
 * @param 公开用户英文名  $login
 */
function deletePublishRecord($fid,$login){
	if(!isset($fid)||empty($login)){
		return array();
	}
	$result = D("YunpanPublish","yunpan")->deleteShare($fid,$login);
	return $result;

}
/**
 * 获取关注的好友列表
 * @param unknown $uid
 * @param unknown $size
 * @return multitype:|unknown
 */
function getFollowingList($uid,$size){
	if(!isset($uid)){
		return array();
	}
	$list = model ( 'Follow' )->getFollowingListAllForOpen ( $uid  );
	 if (empty ( $list ) || empty ( $list  )) {
		return array();
	} else {
		/* $new_list = array();
		for($i = 0; $i < count ( $list ); $i ++) {
			$user = model ( 'User' )->getUserInfo ( $list  [$i] ['fid'] );
			$cyId = $user ['cyuid'];
			$list [$i] ['fname'] = $user ['uname'];
			$list [$i] ['fid'] = $cyId;
			array_push($new_list,$list[$i]);
		}
		return $new_list; */
		return $list ;
	} 
//	return $new_list;
}

/**
 * 获取分享资源到学科资源排前几名的用户相关信息
 * @param int $top 分享排前几名的
 * @param int $limit 查询指定数量用户分享的记录
 * @return array(array('login_name'=>'zhangtest','count'=>'120','list'=>array()));
 */
function getPublishTop($top, $limit){
	$result = D("YunpanPublish","yunpan")->getPublishTop($top,$limit);
	foreach($result as &$value){
		$u = Model('User')->getUserInfoByLogin($value['login_name']);
		$value['uid'] = $u['uid'];
		$value['name'] = $u['uname'];
		$value['avatar_small'] = $u['avatar_small'];
		$value['subject'] = $u['subject'];
	}
	return $result;
}

/**
 * 获取最新用户信息
 * $param int $limit 查询最大数量
 */
function getLatestUserInfo($limit){
	$user = Model('User')->getUserList($limit,array(),'ctime desc');
	return $user['data'];
}

/**
 * 获取名师工作室详情
 * @param int $gid
 */
function getMsGroupById($gid){
	return D('MSGroup','msgroup')->getMsGroupById($gid);
}

/**
 * 获取工作室的资讯文章类基本属性(不含附件)(适用于 列表展示 )
 * @api
 * @param int $gid <工作室ID>
 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
 * @param int $limit <每页显示数量>
 * @param string $order <排序方式(字符串格式：字段 升降序)，默认desc，升序使用asc>
 * @param int $page <页数，默认显示首页>
 * @return array <出现异常返回false，无查询结果返回null，否则返回array>
 */
function getNoticesByGid($gid, $type, $limit, $order, $page){
	return D('MSGroupAnnounce','msgroup')->GetNoticesByGID($gid, $type, $limit, $order, $page);
}

/**
 * 获取用户主页信息
 * @param string $login_name <用户账号名>
 * @param int $uid <用户id>
 * @param int $type <类型 1：资源，2：说说，3：日志，4：相册>
 * @param int start <起始位>
 * @param int limit <查询数量>
 */
function getUserProfile($login_name, $uid, $type, $start, $limit){
	$result = array();
	switch ($type){
		case 1:
			$map ['login_name'] = $login_name;
			$map ['open_position'] = 1;
			$map ['is_del'] = 0;
			$result = D ( 'YunpanPublish', 'yunpan' )->listPublish($login_name, "01", $start, $limit);
			break;
		case 3:
			$map['uid'] = $uid;
			$map['status'] = 1;
			$result = D ( 'Blog', 'blog' )->getBlogList($map,'*','cTime desc',4,$uid);
			break;
	}
	return $result;
}

/**
 * 获取答疑明星
 * @param int $limit
 */

function getAnswerStar($limit){
	$users = D('Answer','onlineanswer')->getAnswerStar($limit);
	//获取回答数
	foreach($users as &$v){
		$count = D('Answer','onlineanswer')->getAnsCount($v['uid']);
		$user = D('User')->getUserInfo($v['uid']);
		$v['name'] = $user['uname'];
		$v['school'] = $user['school'];
		$v['subject'] = $user['subject'];
		$v['avatar_small'] = $user['avatar_small'];
		$v['adoptCount'] = $count['best_count'];
		$v['answerCount'] = $count['ans_count'];
		$v['agreeCount'] = $count['agree_count'];
	}
	return $users;
}

/**
 * 获取最后一条回答
 * @param int $uid
 */
function getLastAnswer($uid){
	$answer = D('Answer','onlineanswer')->getLastAnswer($uid);
	return $answer;
}

/**
 * 获取热评信息
 * @param int $uid
 */
function getHotPingke($limit){
	return D('Pingke','pingke')->getHotPingke($limit);
}

/**
 * 检查每天增加的分享和上传积分次数
 * @param  $login 用户登录名
 */
function checkAddScore($login){
	return D('CreditRecord')->getCuttentCounts($login);
}

/**
 * 增加积分和积分纪录
 * 
 * array $data 
 *   type :'upload_resource'(资源公开) 积分添加类型
 *   url  : 分享资源地址
 *   content:  添加积分的内容
 *   login  :  用户登录名
 */
function addScoreRecord($data){
	return  D('CreditRecord')->addRecordAndScore($data);
}

/**
 * 添加云盘属性
 * @param array $con  
 * cyuid  用户cyuid
 * fid 文件fid
 * properties 属性数组
 * 
 */
function setFileProperties($con){
	D("YunpanFile","yunpan")->setFileProperties($con["cyuid"],$con["fid"],$con['properties']);
}

/**
 * 获取日志列表
 * @param array $conditions
 * @param int $page
 * @param int $limit
 * @param string $order
 */
function getBlogList($conditions, $page, $limit, $order){
	return D('Blog', 'blog')->getBlogs($conditions, $page, $limit, $order);
}

/**
 * 获取主题讨论列表
 * @param array $conditions
 * @param int $page
 * @param int $limit
 * @param string $order
 */
function getResearchList($conditions, $page, $limit, $order){
	return D('Research', 'research')->getResearches($conditions, $page, $limit, $order);
}

/**
 * 获取网上评课列表
 * @param array $conditions
 * @param int $page
 * @param int $limit
 * @param string $order
 */
function getPingkeList($conditions, $page, $limit, $order){
	return D('Pingke', 'pingke')->getPingkes($conditions, $page, $limit, $order);
}

/**
 * 获取网络调研列表
 * @param array $conditions
 * @param int $page
 * @param int $limit
 * @param string $order
 */
function getVoteList($conditions, $page, $limit, $order){
	return D('Vote', 'vote')->getVotes($conditions, $page, $limit, $order);
}

/**
 * 获取在线答疑列表
 * @param array $conditions
 * @param int $page
 * @param int $limit
 * @param string $order
 */
function getQuestionList($conditions, $page, $limit, $order){
	return D('Question', 'onlineanswer')->getQuestionsByCondition($conditions, $page, $limit, $order);
}

/**
 * 设置应用推荐
 * @param string app <blog:日志, research:主题讨论, pingke:网上评课, vote:网络调研, onlineanswer:在线答疑>
 * @param int $id
 * @param string $act <recommend,togreat,cancel>
 */
function doHotApp($app, $id, $act){
	$map = array();
	switch($app){
		case "blog":
			$map['id'] = $id;
			return D('Blog', 'blog')->doIsHot($map, $act);
			break;
		case "research":
			$map['id'] = $id;
			return D('Research', 'research')->doIsHot($map, $act);
			break;
		case "pingke":
			$map['id'] = $id;
			return D('Pingke', 'pingke')->doIsHot($map, $act);
			break;
		case "vote":
			$map['id'] = $id;
			return D('Vote', 'vote')->doIsHot($map, $act);
			break;
		case "onlineanswer":
			$map['qid'] = $id;
			return D('Question', 'onlineanswer')->doIsHot($map, $act);
			break;
	}
}

/**
 * 获取名师工作室头像
 * @param array  $msGroupArray 数组
 */
function getMsGroupAvatar($msGroupArray){
	foreach ($msGroupArray as &$val){
		$gid = $val['gid'];
		D('MSAvatar','msgroup')->init(array("app"=>"msgroup","rowid"=>$gid));
		$val["image"] = D('MSAvatar','msgroup')->getAvatar();
	}
	return $msGroupArray;
}


/**
 * 获取用户头像
 * @param array  $userArray 数组
 */
function getUserAvatar($userArray){
	foreach ($userArray as &$val){
		if(isset($val['uid'])){
			$val["image"] = model('Avatar')->init($val['uid'])->getUserPhotoFromCyCore($val['uid'],'uid',$val['appKey']);
		}else{
			$val["image"] = model('Avatar')->init($val['id'])->getUserPhotoFromCyCore($val['id'],"cyUid",$val['appKey']);
		}
	}
	return $userArray;
}

/**
 * @param $operationLog 添加平台操作记录日志
 * @return mixed
 */
function addOperationLog($operationLog){
   return model("OperationLog")->addOperationLog($operationLog);
}

$server = new PHPRPC_Server();
$server->add('getOnlineAnswers');
$server->add('getResearchInfos');
$server->add('getExcellentSpaces');
$server->add('getMingshiSpaces');
$server->add('getHotVotes');
$server->add('getNewOnlineAnswers');
$server->add('getHotMsGroup');
$server->add('getSpaceDetailBySids');
$server->add('spaceFollow');
$server->add('spaceUnfollow');
$server->add('addUserPwdVerify');
$server->add('delUserPwdVerify');
$server->add('getUserPwdVerify');
//云盘资源收藏接口
$server->add('collectResource');
//云盘上传资源数统计
$server->add('modifyUploadYunpanCount');
//修改云盘已使用容量
$server->add('modifyYunpanUsedSize');
//获取用户的云盘容量
$server->add('getCapacityInfoByLogin');
//在云盘保存资源下载记录
$server->add('saveDownloadRecord');
//保存资源公开记录
$server->add('savePublishRecord');
$server->add('sendNotify');
//获取其他平台在云平台用户角色映射表
$server->add('listRoleMap');
//判断当天增加积分的上传是否已经达到上限
$server->add('isAddScore');
//根据用户登录名和资源id发布动态
$server->add('addAuditFeed');
//根据用户登录名初始化用户空间信息
$server->add('initUserSpace');
//添加用户信息获取
$server->add('getUserData');
//添加用户关注判断
$server->add('getFollowingState');
// 同步资源分享发动态
$server->add('shareFeed');
// 判断用户是否开通了个人空间
$server->add('hasUserSpace');
// 同步资源分享发动态(性能优化)
$server->add('shareFeedComplete');
//添加资源分享到个人主页
$server->add('shareResToHome');
//资源是否被分享检测
$server->add('checkResHasShare');
//记录资源分享记录
$server->add('addShareRecord');
//更新统计接口
$server->add('updateKey');
//删除资源公开到个人空间的记录接口
$server->add('deletePublishRecord');
//获取关注的好友列表
$server->add('getFollowingList');
//获取分享到学科资源排前几名用户信息
$server->add('getPublishTop');
//获取最新用户信息
$server->add('getLatestUserInfo');
//获取名师工作室详情
$server->add('getMsGroupById');
//获取工作室的资讯文章类基本属性
$server->add('getNoticesByGid');
//获取用户主页信息
$server->add('getUserProfile');
//获取答疑明星
$server->add('getAnswerStar');
//获取最后一条回答
$server->add('getLastAnswer');
//获取热评信息
$server->add('getHotPingke');
//获取积分纪录信息
$server->add('checkAddScore');
//添加积分纪录
$server->add('addScoreRecord');
//添加云盘文件属性
$server->add('setFileProperties');
//日志列表获取
$server->add('getBlogList');
//主题讨论列表获取
$server->add('getResearchList');
//网上评课列表获取
$server->add('getPingkeList');
//网络调研列表获取
$server->add('getVoteList');
//在线答疑列表获取
$server->add('getQuestionList');
//设置热门和精华
$server->add('doHotApp');
//获取名师工作室头像
$server->add('getMsGroupAvatar');
//获取用户头像
$server->add('getUserAvatar');
//添加操作日志
$server->add('addOperationLog');
// 启动服务
$server->start();
?>