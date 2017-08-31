<?php
error_reporting(E_ERROR);

/**
 * 好友关系、消息系统
 * User: justplus
 * Date: 14-7-18
 * Time: 下午3:01
 */

define('SITE_PATH', dirname(dirname(__FILE__)));
require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';

function user_exist($cyuid){
    $u_sql = "select uid from ts_user where cyuid=".$cyuid;
    $list = M()->query($u_sql);
    if(empty($list[0]["uid"])){
        return false;
    }
    return $list[0]["uid"];
}

function object_array($array)
{
   if(is_object($array)){
		$array = (array)$array;
   }
   if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key] = object_array($value);
		}
   }
   return $array;
}

function sendShareFeed($resInfos, $description, $uid, $feed_type="yunpan",$cyuid=''){
	
    /*$count = count($resInfos);
	$data = array(
			"content"=>"",
			"body"=>'我分享了'.$count.'个资源到：',
			"description" => $description,
			"other" => $resInfos
	);*/
    $types = $resInfos[0]['type'];
    $shareIds = explode(",",$resInfos[0]['shareId']);
    $classIds = explode(",",$resInfos[0]['classId']);
    switch ($types) {
        case "1":
            $body = '我分享了【'.$resInfos[0]['resName'].'.'.$resInfos[0]['extension'].'】到个人主页，点击查看';
            $resUrl = U("resview/Resource/index",array("id"=>$resInfos[0]['resId'],"uid"=>$cyuid));
            break;
        case "2":
            $body = '我分享了【'.$resInfos[0]['resName'].'.'.$resInfos[0]['extension'].'】到班级，点击查看';
            $resUrl = C('ESCHOOL').'/index.php?m=Clazz&c=Share&a=preview&uid='.$cyuid.'&resid='.$resInfos[0]['resId'].'&classId='.$classIds[0].'&share_id='.$shareIds[0];
            break;
        case "3":
            $body = '我分享了【'.$resInfos[0]['resName'].'.'.$resInfos[0]['extension'].'】到资源中心，点击查看';
            $resUrl =  C('SEARCH').'/index.php?m=Search&c=Resource&a=preview&resId='.$resInfos[0]['resId'];
            break;
        case "4":
            $body = '我发现【'.$resInfos[0]['resName'].'.'.$resInfos[0]['extension'].'】是个很好的资源，点击查看';
            $resUrl = C('SEARCH').'/index.php?m=Search&c=Resource&a=preview&resId='.$resInfos[0]['resId'];
            break;
        default:
            # code...
            break;
    }
    $data = array(
        "content" => '',
        "body" => $body,
        "source_url" => $resUrl
    );

	$result = model('Feed')->put($uid, "public", $feed_type, $data);
    
	return $result;
}

function getComment($map = null, $order = 'comment_id ASC', $page, $limit = 10){
               $result = array();
        	$map['is_del'] = 0;
	$skip = ($page-1)*$limit;
	$data = M('Comment')->where($map)->order($order)->limit($skip.",".$limit)->select();
                $total =  M('Comment')->where($map)->count();
	foreach($data as &$v) {
            if(!empty($v['to_comment_id'])) {
	$v['to_user_info'] = model('User')->getUserInfo($v['to_uid']);
       }
        	$v['user_info'] = model('User')->getUserInfo($v['uid']);
        }
        $result['total'] = $total;
              $result['data'] = $data;
        return $result;
}

/**
 * 根据用户名查找该用户的积分
 * @param string $username
 */
function getCreditByUsername($username){
    $creditD = D("UserCredit","api");
    return $creditD->getCreditByUsername($username);
}

$method = $_REQUEST['method'];
$epspSvc = new EpspClient();
switch($method){
    case 'sns.teach.checkin':  //签到
        $uid = intval($_REQUEST['uid']);
        $callback = $_REQUEST ['callback'];
        $checkinInfo = new TeachCheckinModel();
        $result = $checkinInfo->teach_check_in($uid,$callback);
        echo $result;
        break;
    case 'sns.check.actstate':
        $acc = $_REQUEST['loginName'];
        $result_json = $epspSvc->szcheckActState($acc);
        $result = json_decode($result_json,ture)['data'];
        echo json_encode($result);
        break;
    case 'sns.yunpan.download':
        $info = new YunpanDownloadModel();
        $download = $info->getNewListDownload();
        echo json_encode($download);
        break;
    case 'sns.user.credit.get':
        $usercredit = $this->getCreditByUsername();
        echo json_encode($usercredit);
        break;
	case "sns.user2.get": //获取sns用户信息
	    $login_name = $_REQUEST['login_name'];
	    $sql = "select cyuid, uid from ts_user where login='".$login_name."'";
	    $user_list = M()->query($sql);		
        $uid = !empty($user_list)?$user_list[0]['uid']:0;
		echo $uid;
	   break;   
    case "sns.user.get": //获取用户关注、被关注、资源分享、头像等信息
        $valid = false;//默认用户验证不通过        
        $data = array();
        $cyuid = $_REQUEST["uid"];
        $uid = $_REQUEST['uuid'];
        $login_name = $_REQUEST['login_name'];
		$key = $_REQUEST['key']; //cyuid, uid,login_name;
        if(!empty($cyuid)) {//如果cyid存在，获取uid
        	$uid = user_exist($cyuid);
        	$valid = !empty($uid);
        }else if(!empty($uid)){//如果uid存在，那么获取cyid
        	$sql = "select cyuid from ts_user where uid=".$uid;
        	$user_list = M()->query($sql);
        	if(!empty($user_list)){
        		$cyuid = $user_list[0]['cyuid'];
        	}
        	$valid = !empty($user_list);
        	$valid = !empty($user_list);
        }else if(!empty($login_name)) {
        	$sql = "select cyuid, uid from ts_user where login='".$login_name."'";
        	$user_list = M()->query($sql);
        	if(!empty($user_list)){
        		$cyuid = $user_list[0]['cyuid'];
        		$uid = $user_list[0]['uid'];
        	}
        	$valid = !empty($user_list);
        }
        
        //根据cyuid获取uid
        if($valid){
            //获取粉丝、关注数等信息
            //$follow_sql = "select * from ts_user_data where uid=(select uid from ts_user where cyuid=".$uid.")";
            $follow_sql = "select * from ts_user_data where uid=".$uid;
            $list = M()->query($follow_sql);
            if(!empty($list)) {
                foreach($list as $v) {
                    $data[$v['key']] = (int)$v['value'];
                }
            }
            
            //获取分享数
            $share_sql = "select count(id) as total from yun_file_share where isdel=0 and uid=".$cyuid;
            $list = M()->query($share_sql);
            $data['share'] = $list[0]["total"] == null ? 0 : $list[0]["total"];
            //获取头像
            $appKey = $_REQUEST["appKey"];
            $avatar = new AvatarModel($uid);
            $avatars = $avatar->getUserPhotoFromCyCore($uid,"uid",$appKey);
            $data['avatar'] = $avatars;

            //获取用户积分
            $credit = new CreditModel($uid);
            $usercredit = $credit->getUserCredit($uid);
            $data['credit_count'] = $usercredit['credit']['score']['value'];

            //获取用户uid
            $data['uid'] = $uid;
            $data['cyid'] = $cyuid;
            echo json_encode($data);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
            //throw new Exception("用户不存在");
        }
        break;
    //获取用户之间的关注信息
    case "sns.user.follow.check":
        $cyuid = $_REQUEST["uid"];
        $cyfid = $_REQUEST["fid"];
        if($uid = user_exist($cyuid)){
            if($fid = user_exist($cyfid)){
                echo json_encode(M("Follow")->getFollowState($uid, $fid));
            }
            else{
                $statusCode = -1;
                $message = "用户不存在";
                $data['statusCode'] = $statusCode;
                $data['message'] = $message;
                echo json_encode($data);
            }
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //关注用户or取消关注用户
    case "sns.user.follow":
        $cyuid = $_REQUEST["uid"];
        $cyfid = $_REQUEST["fid"];
        $operation = $_REQUEST["operation"];
        if($uid = user_exist($cyuid)){
            if($fid = user_exist($cyfid)){
                if($operation == "follow"){
                    echo json_encode(M("Follow")->doFollow($uid, $fid));
                }
                elseif($operation == "unfollow"){
                    echo json_encode(M("Follow")->unFollow($uid, $fid));
                }
            }
            else{
                $statusCode = -1;
                $message = "用户不存在";
                $data['statusCode'] = $statusCode;
                $data['message'] = $message;
                echo json_encode($data);
            }
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //分享资源到个人中心
    case "sns.resource.share2index":
        $app='changyan';
        $content='';
        $login = $_REQUEST['login'];
        $body = $_REQUEST['body'];
        $source_url=$_REQUEST['url'];
        $rid = $_REQUEST['rid'];
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
            $result['msg'] = '此用户未开通个人空间！';
        }
        echo json_encode($result);
        break;
    case "sns.resource.share":
    $res_info = $_REQUEST["resInfo"];
    $content = $_REQUEST["content"];
    $cyuid = $_REQUEST["uid"];
    $source = $_REQUEST["source"];
    return $_REQUEST;
    if(empty($source)){
        $source = "yunpan";
    }
    //$_GET['app'] = 'public';
    //$action = A('Index', 'public');
    
    if($uid = user_exist($cyuid)){
        echo json_encode(sendShareFeed(object_array(json_decode($res_info)), $content, $uid, $source,$cyuid));
    }
    else{
        $statusCode = -1;
        $message = "用户不存在";
        $data['statusCode'] = $statusCode;
        $data['message'] = $message;
        echo json_encode($data);
    }
    break;
    //分享资源到资源中心、班级、个人主页以及发布习题之前调用
    case "sns.resourceCenter.share":
        $cyuid = $_REQUEST["uid"];
        //$res_info = $_REQUEST["resInfo"];
        
        //$source = $_REQUEST["source"];
        //if(empty($source)){
        //    $source = "yunpan";
        //}
        if($uid = user_exist($cyuid)){
            $content = "分享到资源中心的微博不予显示！";
            $data = array(
                "content"=>$content,
                "body"=>$content
            );
            $result = model('Feed')->put($uid, "public", $feed_type = 'share', $data);
            $delete = model('Feed')->doEditFeed($result['feed_id'],$type = 'delFeed',$title = 'null',$uid);
            if($_REQUEST['exercise_id']){
                $exercise_id = $_REQUEST['exercise_id'];
                $add = $result['feed_id'];
                $return = M()->execute("UPDATE yun_exercise SET feed_id=".$add." WHERE exercise_id=".$exercise_id);
                //$return = M('yun_exercise',null)->where($exercise_id)->save($add);
            }else{
                $id = $_REQUEST['shareId'];
                $add = $result['feed_id'];
                $return = M()->execute("UPDATE yun_file_share SET feed_id=".$add." WHERE id=".$id);
                //$return = M('yun_file_share',null)->where($id)->save($add);
                //$content = $_REQUEST["content"];
                //echo json_encode(sendShareFeed(object_array(json_decode($res_info)), $content, $uid, $source));

            }
            echo json_encode($result['feed_id']);
	   //$content = $_REQUEST["content"];
            //echo json_encode(sendShareFeed(object_array(json_decode($res_info)), $content, $uid, $source));
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;

    //发送私信
    case "sns.message.post":
        $from = $_REQUEST["from"];
        $to = $_REQUEST["to"];
        $type = $_REQUEST['type'];// 判断是否进行用户验证 ，4表示不需要用户验证
        $content = $_REQUEST["content"];
        $data['to'] = $to;
        $data['content'] = $content;
        $data['type'] = $type;
        if($type = 4){
            $appKey = C("SNS_APP_ID");
            $url = C('WEB')."/index.php?app=Home&mod=PersonSet&act=sz_personSet";
            $result = $epspSvc->sendMessage($to, 'system', $content, $url, $appKey);
            echo json_encode($result);
        }else{
            if($from = user_exist($from)){
                if($to = user_exist($to)){
                    /*  $action = A('Message', 'public');
                     return $action->doPost($from, $to, $content,1, true); */
                    $result = model('Message')->postMessage($data, $from, true,'password');
                    echo json_encode($result);
                }
                else{
                    $statusCode = -1;
                    $message = "用户不存在";
                    $data['statusCode'] = $statusCode;
                    $data['message'] = $message;
                    echo json_encode($data);
                }
            }
            else{
                $statusCode = -1;
                $message = "用户不存在";
                $data['statusCode'] = $statusCode;
                $data['message'] = $message;
                echo json_encode($data);
            }
        }
        break;
    //获取私信列表
    case "sns.message.list":
        $uid = $_REQUEST["uid"];
        if($uid = user_exist($uid)){
            $list = model('Message')->getMessageListByUid($uid, array(MessageModel::ONE_ON_ONE_CHAT));
            echo json_encode($list);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //获取单条私信内容
    case "sns.message.detail.get":
        $uid = $_REQUEST["uid"];
        $mid = $_REQUEST["messageId"];
        if($uid = user_exist($uid)){
            $message = model('Message')->isMember(t($mid), $uid, true);

            // 验证数据
            if(empty($message)) {
                throw new Exception(L('PUBLIC_PRI_MESSAGE_NOEXIST'));
            }
            $message['member'] = model('Message')->getMessageMembers(t($mid), 'member_uid');
            $message['to'] = array();
            // 添加发送用户ID
            foreach($message['member'] as $v) {
                $uid != $v['member_uid'] && $message['to'][] = $v;
            }
            // 设置信息已读(私信列表页去掉new标识)
            model('Message')->setMessageIsRead(t($mid), $uid, 0);
            $message['since_id'] = model('Message')->getSinceMessageId($message['list_id'],$message['message_num']);
            echo json_encode($message);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //删除私信
    case "sns.message.delete":
        $uid = $_REQUEST["uid"];
        if($uid = user_exist($uid)){
            $res = model('Message')->deleteMessageByListId($uid, t($_REQUEST['ids']));
            echo $res?"1":"0";
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //获取动态列表
    case "sns.feed.list":
        $uid = $_REQUEST["uid"];
        $type = $_REQUEST["type"];
        $map = array();
        if($type)
            $map['type'] = $type;
        if($uid = user_exist($uid)){
            $list = model('Feed')->getList(t($map));
            echo json_encode($list);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //评论动态
    case "sns.feed.addcomment":
        $uid = $_REQUEST["uid"];        // 评论者的id,不可为空
        //$app_uid = $_REQUEST["app_uid"];// 评论内容的发表者,不可为空
        // 返回结果集默认值
        $return = array('status'=>0, 'data'=>L('PUBLIC_CONCENT_IS_ERROR') );
        if($uid = user_exist($uid)){
        $GLOBALS['ts']['mid'] = $uid;
        // 获取接收数据
        $data = $_REQUEST;
        // 安全过滤
        foreach ($data as $key => $val) {
            $data [$key] = t($data[$key]);
        }
        // 评论所属与评论内容

        $data['app'] = $data['app_name']?$data['app_name']:'public';
        $data['content'] = h($data['content']);
        //$data['row_id'] = intval($data['feed']);
        $data['source_url'] = $data['source_url'];
        //$data['source_body'] = h($data['source_body']);
        //$data['app_uid'] = $app_uid;
        $data['to_comment_id'] = h($data['to_comment_id']);
        $to_uid = $_REQUEST["to_uid"];  // 被@的人的id,可以为空
        $data['to_uid'] = $to_uid;
        $data['uid'] = $uid;

        //
        if($_REQUEST['exercise_id'])
        {                                           //对发布的习题进行评论
            $find = intval($_REQUEST['exercise_id']);
            $find_data = "SELECT * from yun_exercise where exercise_id=".$find;
            $feed_data = M()->query($find_data);
            //$feed_data = M('yun_exercise')->where($find)->find();
            $feed_id = $feed_data[0]['feed_id'];
            $data['table'] = 'yun_exercise';
            //$data['source_body'] = '我发布了一个题目'.$data['source_url'];
            // 生成题目url,跳转到班级作业与讲解
            $homework_id = M()->query('SELECT * from yun_homework_exercise where exercise_id='.$find);
            $homework_id = $homework_id[0]['homework_id'];
            $class_id = M()->query('SELECT * from yun_homework_class where homework_id='.$homework_id);
            $class_id = $class_id[0]['class_id'];
            $data['source_url'] = C("eschool_site_url").'/index.php?m=Clazz&c=Homework&a=homework&classId='.$class_id;
            $data['source_body'] = '我发布了一个题目<a href="'.$data['source_url'].'" target="_blank" class="ico-url-web"></a>';

        }elseif($_REQUEST['id']){                   //对分享到班级或者个人中心的资源进行评论
            $find = intval($_REQUEST['id']);
            $find_data = "SELECT * from yun_file_share where id=".$find;
            $feed_data = M()->query($find_data);
            //$feed_data = M('yun_file_share')->where($find)->find();
            $feed_id = $feed_data[0]['feed_id'];
            $data['table'] = 'yun_file_share';

            $findData = "SELECT * from yun_file_share where id=".$find;
            $returnData = M()->query($findData);
            if($feed_data[0]['dest'] == 'homepage'){// 生成个人主页的预览地址
                $shareUid = $feed_data[0]['uid'];
                if(json_decode($returnData[0]['resourceinfo'])->resource_id==null){
                    $resId = json_decode($returnData[0]['resourceinfo'])->id;
                }else $resId = json_decode($returnData[0]['resourceinfo'])->resource_id;
                $data['source_url'] = C("space_site_url").'/index.php?app=public&mod=Profile&act=preview&uid='.$shareUid.'&resid='.$resId.'&shareId='.$find;
            }
            else{                                   // 生成班级空间的预览地址
                $shareUid = $feed_data[0]['uid'];
                if(json_decode($returnData[0]['resourceinfo'])->resource_id==null){
                    $resId = json_decode($returnData[0]['resourceinfo'])->id;
                }else $resId = json_decode($returnData[0]['resourceinfo'])->resource_id;
                $findClassData = "SELECT * from yun_file_share_class where share_id=".$find;
                $findClass = M()->query($findClassData);
                $classId = $findClass[0]['class_id'];
                $data['source_url'] = C("eschool_site_url").'/index.php?m=Clazz&c=Share&a=preview&resid='.$resId.'&uid='.$shareUid.'&classId='.$classId.'&share_id='.$find;
            }
            $data['source_body'] = '我发布了一个资源<a href="'.$data['source_url'].'" target="_blank" class="ico-url-web"></a>';
        }else{                                      //对资源中心、校本资源的资源进行评论
            $feedContent = "分享到资源中心的微博不予显示！";
            $feedData = array(
                "content"=>$feedContent,
                "body"=>$feedContent
            );
            if($_REQUEST['resourceId']){            //资源中心预览地址生成
                $resource_id = $_REQUEST['resourceId'];
                $data['source_url'] = C("rs_site_url").'/index.php?m=Search&c=Resource&a=preview&resId='.$resource_id;
            }else{                                  //校本资源预览地址生成
                $resource_id = $_REQUEST['schoolResourceId'];
                $schoolId = $_REQUEST['schoolId'];
                $data['source_url'] = C("eschool_site_url").'/index.php?m=School&c=Resource&a=preview&resid='.$resource_id.'&schoolId='.$schoolId;
            }
            $find_data = "SELECT * from yun_file_share where dest='yun_web' AND resourceinfo='".$resource_id."'";
            $feed_data = M()->query($find_data);
            if($_REQUEST['schoolResourceId'] && $feed_data[0]['feed_id'] == null){
                //更新yun_file_share表
                $id = $feed_data[0]['id'];
                $result = model('Feed')->put(1, "public", $feed_type = 'share', $feedData);
                $delete = model('Feed')->doEditFeed($result['feed_id'],$type = 'delFeed',$title = 'null',1);
                $add = $result['feed_id'];
                $return = M()->execute("UPDATE yun_file_share SET feed_id=".$add." WHERE id=".$id);
                $feed_id = $add;
                $feed_data = M()->query($find_data);
            }
            //对资源中心匿名的资源评论处理
            if(empty($feed_data) || $feed_data[0]['feed_id'] == null)
            {
                if(empty($feed_data)){
                    date_default_timezone_set('prc');
                    $insertData = "INSERT INTO yun_file_share (uid,resourceinfo,sharetime,source,dest,isdel) VALUES (-1, '".$resource_id."', '".date('Y-m-d H:i:s',time())."', 'yun_web','yun_web',1)";
                    $insertResult = M()->execute($insertData);
                }
                $findData = "SELECT * from yun_file_share where dest='yun_web' AND resourceinfo='".$resource_id."'";
                $feedDataFind = M()->query($findData);
                $id = $feedDataFind[0]['id'];
                if($feedDataFind[0]['uid'] == '-1')
                    $result = model('Feed')->put(1, "public", $feed_type = 'share', $feedData);
                else
                    $result = model('Feed')->put(user_exist($feedDataFind[0]['uid']), "public", $feed_type = 'share', $feedData);
                $delete = model('Feed')->doEditFeed($result['feed_id'],$type = 'delFeed',$title = 'null',1);
                $add = $result['feed_id'];
                $return = M()->execute("UPDATE yun_file_share SET feed_id=".$add." WHERE id=".$id);
                $feed_id = $add;
            }else{
                $feed_id = $feed_data[0]['feed_id'];
            }
            $data['table'] = 'yun_file_share';

            //$data['source_body'] = '我分享了一个资源到资源中心:'.$data['source_url'];
            $data['source_body'] = '我发布了一个资源<a href="'.$data['source_url'].'" target="_blank" class="ico-url-web"></a>';
        }
        $data['row_id'] = $feed_id;
        $action = model('Feed')->get($data['row_id']);
        $data['app_uid'] = $action['uid'];
        // 添加评论操作

        $data['comment_id'] = model('Comment')->addComment( $data );
        if ($data ['comment_id']) {
            $return ['status'] = 1;
            // 去掉回复用户@
            $lessUids = array ();
            if (! empty ( $data ['to_uid'] )) {
                $lessUids [] = $data ['to_uid'];
            }

            if ($data ['ifShareFeed'] == 1) {  // 转发到我的微博
                unlockSubmit();
                updateToWeiBo( $data, $sourceInfo = true, $lessUids );
            } else if ($data ['comment_old'] != 0) {  // 是否评论给原来作者
                unlockSubmit();
                updateToComment ( $data, $sourceInfo = true, $lessUids );
            }
            $return['data'] = $data['comment_id'];
            echo json_encode ( $return );
        }else
            echo json_encode ( $return );
    }else{
        $statusCode = -1;
        $message = "用户不存在";
        $return['status'] = $statusCode;
        $return['data'] = $message;
        echo json_encode($return);
    }
        break;
    //资源中心、班级分享、习题获取评论列表
    case "sns.feed.commentlist":
        $limit = $_REQUEST['limit'];
        $page = $_REQUEST['page'];
            if($_REQUEST['exercise_id'])            //获取习题评论列表
            {
                $find = intval($_REQUEST['exercise_id']);
                $find_data = "SELECT * from yun_exercise where exercise_id=".$find;
                $feed_data = M()->query($find_data);
                $feed_id = $feed_data[0]['feed_id'];
	            $map['table'] = 'yun_exercise';
            }elseif($_REQUEST['id']){               //获取班级分享评论列表
                $find = intval($_REQUEST['id']);
                $find_data = "SELECT * from yun_file_share where id=".$find;
                $feed_data = M()->query($find_data);
                $feed_id = $feed_data[0]['feed_id'];
                $map['table'] = 'yun_file_share';
            }else{                                  //获取资源中心评论列表
                $resource_id = $_REQUEST['resourceId'];
                $tempMaps['dest'] = 'yun_web';
                $tempMaps['resourceinfo'] = $resource_id;
                $feed_data = model('YunFileShare')->where($tempMaps)->select();
	           // $find_data = "SELECT * from yun_file_share where dest='yun_web' AND resourceinfo='".$resource_id."'";
               //$feed_data = M()->query($find_data);
                $feed_id = $feed_data[0]['feed_id'];
	            $map['table'] = 'yun_file_share';
            }
            $map['row_id'] = $feed_id;
	        $map['app'] = 'public';
            $action = getComment($map, 'ctime desc', $page, $limit);
            if($_REQUEST['exercise_id']){
                foreach($action['data'] as &$value)
                    $value['exerciseId'] = $_REQUEST['exercise_id'];
            }
            echo json_encode($action);
        break;

    // 获取作业评论数
    case "sns.exercise.commentCount":
        $exerciseId = $_REQUEST['exerciseIds'];
        $sql = "select exercise_id, cnt from yun_exercise inner join".
            "(SELECT row_id, count(*) as cnt FROM `ts_comment` where ".
            "row_id in (select feed_id from yun_exercise where exercise_id in (".$exerciseId.
            ")) group by row_id) s on yun_exercise.feed_id = s.row_id;";
        $data = M()->query($sql);
        echo json_encode($data);
        break;
    //删除评论
    case "sns.feed.deletecomment":
        $uid = $_REQUEST["uid"];
        //$comment_id = $_REQUEST["comment_id"];
        //$ids['comment_id'] = $comment_id;
        if($uid = user_exist($uid)){
            $res = model('Comment')->deleteComment(t($_REQUEST["comment_id"]), $uid);
            echo $res?"1":"0";
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //发布动态
    case "sns.feed.post":
        $uid = $_REQUEST["uid"];
        if($uid = user_exist($uid)){
            // 返回数据格式
            $return = array('status'=>1, 'data'=>'');
            // 用户发送内容
            $d['content'] = isset($_REQUEST['content']) ? filter_keyword(h($_REQUEST['content'])) : '';
            // 原始数据内容
            $d['body'] = filter_keyword($_REQUEST['body']);
            // 安全过滤
            foreach($_REQUEST as $key => $val) {
                $_REQUEST[$key] = t($_REQUEST[$key]);
            }
            $d['source_url'] = " ".urldecode($_REQUEST['source_url']);  //应用分享到微博，原资源链接
            // 滤掉话题两端的空白
            $d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);
            //录音ID
            $d['record_id'] = $_REQUEST['record_id'];
            //标题
            $d['feed_title'] = $_REQUEST['feed_title'];
            // 附件信息
            $d['attach_id'] = trim(t($_REQUEST['attach_id']), "|");
            if ( !empty($d['attach_id']) ){
                $d['attach_id'] = explode('|', $d['attach_id']);
                array_map( 'intval' , $d['attach_id'] );
            }
            // 发送微博的类型
            $type = isset($_REQUEST['type']) ? t($_REQUEST['type']) : 'post';
            // 所属应用名称
            $app = isset($_REQUEST['app_name']) ? t($_REQUEST['app_name']) : 'public';          // 当前动态产生所属的应用
            if(!$data = model('Feed')->put($uid, $app, $type, $d)) {
                $return = array('status'=>0,'data'=>model('Feed')->getError());
                echo json_encode($return);
            }
                //是否是日志转发
                //$blog_id = isset($_REQUEST['blog_id']) ? t($_REQUEST['blog_id']) : "";
                //日志分享次数+1
                //if("" != $blog_id){
                //    D("Blog", "blog")->changeShareCount($blog_id);
                //}
            //$action = A('Feed', 'public');
            // 发布邮件之后添加积分
            model ( 'Credit' )->setUserCredit ( $uid, 'add_weibo' );
            // 微博来源设置
            //$data ['from'] = getFromClient ( $data ['from'], $data ['app'] );
           /* $action->assign ( $data );*/
            // 微博配置
        /*    $weiboSet = model ( 'Xdata' )->get ( 'admin_Config:feed' );
            $action->assign ( 'weibo_premission', $weiboSet ['weibo_premission'] );
            $return ['data'] = $action->fetch ();*/
            // 微博ID
            $return ['feedId'] = $data ['feed_id'];
            $return ['is_audit'] = $data ['is_audit'];
            echo json_encode($return);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //获取新消息数量
    case "sns.unread.list":
        $uid = $_REQUEST["uid"];
        if($uid = user_exist($uid)){
            $count = model('UserCount')->getUnreadCount($uid);
            echo json_encode($count);
        }
        else{
            $statusCode = -1;
            $message = "用户不存在";
            $data['statusCode'] = $statusCode;
            $data['message'] = $message;
            echo json_encode($data);
        }
        break;
    //公开课消息服务接口
    case "message.lesson":
    	$uid = $_REQUEST["userId"];
		if($uid = user_exist($uid)){
		    $params = [];
		    $params['uid'] = $uid;
		    $params['title'] = '公开课';
		    $params['body'] = $_REQUEST['message'];
		    $params['appname'] = 'lesson';
		    $params['node']='';
    	if( model('Notify')->sendMessage($params)){
   	 		$data['return'] = true;
    		echo json_encode($data);
    	}else{
    		$data['return'] = false;
    		echo json_encode($data);
   		}
   		}else{
    		$data['return'] = false;
    		echo json_encode($data);
    	}
   		break;
    case "sns.user.status": //获取用户状态接口，供定制化教师助手使用
        $login_name = t($_REQUEST['loginName']);
        $result = array();
        if(!empty($login_name)){
            $client = new \CyClient();
            $userinfo = $client->getUserByUniqueInfo('login_name',$login_name);
            if($userinfo){                
                $userdetail = $client->getUserDetail($userinfo->id);
                $ext_str_04 = $userdetail->userExt1->ext_str_04; //是否冻结 1:冻结 0：未冻结
                $ext_int_04 = $userdetail->userExt1->ext_int_04; //是否审核 1:审核通过 0：未审核 3：审核不通过
                if($ext_str_04==1){
                    $result['status'] = -1;
                    $result['message'] = '账号被冻结'; 
                    echo json_encode($result); 
                    exit();
                }
                if($ext_int_04==0 || $ext_int_04==3){
                    $result['status'] = -2;
                    $result['message'] = '账号未审核或审核被拒绝';
                    echo json_encode($result); 
                    exit();
                }
                $query = 'SELECT COUNT(id) AS count FROM ta_book_user WHERE login_name='."'$login_name'";
                $count = M()->query($query);
                if(intval($count[0]['count'])>0){                  
                    $result['status'] = 1;
                    $result['message'] = '账号已授权';
                    echo json_encode($result); 
                    exit();
                }else{
                    //TODO
                    $params['loginName'] = $login_name;
                    $params['appName'] = C('CLIENT_APP_NAME');
                    //0：已授权 7001：用户名为空 7002：套餐卡ID为空  7003：appName为空 7004：未授权
                    try{
                        $cardStatusJson = Restful::sendGetRequest($params,C('BOOK_AUTHOR_URL').'index.php?c=home&a=getUserCardState');
                        $cardStatusResult = json_decode($cardStatusJson);
                        $cardStatus = $cardStatusResult->status;
                        if($cardStatus=='0'){
                            $result['status'] = 1;
                            $result['message'] = '账号已授权';                           
                        }elseif($cardStatus=='7003'){
                            $result['status'] = -6;
                            $result['message'] = 'appName为空';
                        }elseif($cardStatus=='7004'){
                            $result['status'] = -5;
                            $result['message'] = '账号未授权';
                        }else{
                            $result['status'] = -7;
                            $result['message'] = '获取账号授权信息失败';                        
                        }
                    }catch(Exception $e){
                        $result['status'] = -8;
                        $result['message'] = '获取账号授权信息出异常';
                    }                    
                    echo json_encode($result); 
                    exit();
                }
            }else{
                $result['status'] = -3;
                $result['message'] = '账号不存在';
                echo json_encode($result); 
                exit();  
            }
        }else{
            $result['status'] = -4;
            $result['message'] = '用户名不能为空'; 
            echo json_encode($result);            
        }   
        break;
	case "sns.user.avatar":
		$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:$_REQUEST['id'];
		$val["image"] = model('Avatar')->init($uid)->getUserPhotoFromCyCore($uid);
		echo json_encode($val["image"]);
		break;
    case "sns.user.cylogin": //从cycore同步数据到sns
            $login_name = trim(htmlspecialchars($_GET['login_name']));
            if(!empty($login_name)){
                $cyuserdata = Model('CyUser')->getCyUserInfo($login_name);
                //cycore数据不存在
                if(empty($cyuserdata)){
                    $data['statusCode'] = -1;
                    $data['message'] = "cycore用户不存在！";
                    exit(json_encode($data));
                }
                //检查本地是否已存在用户
                $snsUser = Model('User')->getUserInfoByLogin($login_name);
                if(!empty($snsUser)){
                    $data['statusCode'] = 1;
                    $data['data']['uid'] = $snsUser['uid'];
                    exit(json_encode($data));
                }
                //用户数据同步到sns
                $cyuser = $cyuserdata['user'];
                $cyuser['locations'] = $cyuserdata['locations'];
                $cyuser['rolelist'] = $cyuserdata['rolelist'];
                $uid = Model('CyUser')->cySns($cyuser);
                $data['statusCode'] = 1;
                $data['data']['uid'] = $uid;
                echo json_encode($data);
            }else{
                $data['statusCode'] = -1;
                $data['message'] = "参数有误";
                echo json_encode($data);
            }
            break;
        //发布工作室动态
        case "sns.feed.send":
            $uid = $_REQUEST["uid"];
            if($uid = user_exist($uid)){
                // 返回数据格式
                $return = array('status'=>1, 'data'=>'');
                // 用户发送内容
                $d['content'] = isset($_REQUEST['content']) ? filter_keyword(h( urldecode($_REQUEST['content']))) : '';
                // 原始数据内容
                $d['body'] = filter_keyword(urldecode ($_REQUEST['body']));
                // 安全过滤
                foreach($_REQUEST as $key => $val) {
                    $_REQUEST[$key] = t($_REQUEST[$key]);
                }
                //工作室id
                $d['gid'] =isset($_REQUEST['gid'])?intval($_REQUEST['gid']):0;
                //浏览地址
                $d['source_url'] = " ".urldecode($_REQUEST['source_url']);  //应用分享到微博，原资源链接
                // 滤掉话题两端的空白
                $d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);
                //录音ID
                $d['record_id'] = $_REQUEST['record_id'];
                //标题
                $d['feed_title'] = $_REQUEST['feed_title'];
                // 附件信息
                $d['attach_id'] = trim(t($_REQUEST['attach_id']), "|");
                if ( !empty($d['attach_id']) ){
                    $d['attach_id'] = explode('|', $d['attach_id']);
                    array_map( 'intval' , $d['attach_id'] );
                }
                // 发送微博的类型
                $type = t($_REQUEST['type']);
                // 所属应用名称
                $app = isset($_REQUEST['app_name']) ? t($_REQUEST['app_name']) : APP_NAME;          // 当前动态产生所属的应用
                if(!$data = model('Feed')->put($uid, $app, $type, $d)) {
                    $return = array('status'=>0,'data'=>model('Feed')->getError());
                    echo json_encode($return);
                }
                // 微博ID
                $return ['feedId'] = $data ['feed_id'];
                echo json_encode($return);
            }
            else{
                $statusCode = -1;
                $message = "用户不存在";
                $data['statusCode'] = $statusCode;
                $data['message'] = $message;
                echo json_encode($data);
            }
            break;
}
