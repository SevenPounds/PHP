<?php

/* *
 * 资源平台手机服务
 * @author yangli4@iflytek.com
 * @author yuliu2@iflytek.com
 */

define('SITE_PATH', dirname(dirname(__FILE__)));

require_once SITE_PATH . '/api/change_url.php';
require_once SITE_PATH . '/core/core.php';


/**
 * 发布说说/微博
 *
 * @param int $uid 			用户uid；
 * @param string $appName 	当前产生说说/微博所属的应用
 * @param string $type 		发送说说/微博的类型,默认为‘post’;
 *							如含有图片附件，则它为‘postimage’;
 *							如含有其他附件，则它为‘postfile’;
 * @param array $d 			说说/微博信息数组：
 *				$d['content']，用户发送内容
 *				$d['body']，原始数据内容
 *				$d['attach_id']，附件id，支持多附件，"|"分割，如：|1234|1235|
 */
function sendBlog($uid, $appName, $type, $d){
	// 返回数据格式
	$result = array('status'=>1, 'data'=>'');

	// 用户发送内容
	$d['content'] = isset($d['content']) ? filter_keyword(h($d['content'])) : '';
	// 原始数据内容
	$d['body'] = filter_keyword($d['body']);
	// 滤掉话题两端的空白
	$d['body'] = preg_replace("/#[\s]*([^#^\s][^#]*[^#^\s])[\s]*#/is",'#'.trim("\${1}").'#',$d['body']);	
	// 附件信息
	$d['attach_id'] = trim(t($d['attach_id']), "|");
	if ( !empty($d['attach_id']) ){
		$d['attach_id'] = explode('|', $d['attach_id']);
		array_map( 'intval' , $d['attach_id'] );
	}
	$data = model('Feed')->put($uid, $appName, $type, $d);
	if(!$data) {
		$result = array('status'=>0,'data'=>model('Feed')->getError());
		return $result;
	}

	// 发布邮件之后添加积分
	model ( 'Credit' )->setUserCredit ( $uid, 'add_weibo' );
	
	// 添加话题
	model ( 'FeedTopic' )->addTopic ( html_entity_decode ( $d ['body'], ENT_QUOTES ), $data['feed_id'], $type );

	// 更新用户最后发表的微博
	$last ['last_feed_id'] = $data ['feed_id'];
	$last ['last_post_time'] = $_SERVER ['REQUEST_TIME'];
	model ( 'User' )->where ( 'uid=' . $uid )->save ( $last );

	$result['data'] = $data;

	return $result;
}

/**
 * 转发服务
 *
 *
 * @param int $uid 			用户uid；
 * @param array $d 			转发信息数组：
 *				$d['body']，转发评论内容
 *				$d['sid']， 信息最原始id
 *				$d['curid']，被转发的信息id
 *				$d['app_name']，应用名称
 */
function shareFeed($uid, $d){
	if(intval($uid) < 1 || $d['sid'] < 1 || $d['curid'] < 1){
		$result['status'] = 0;
		$result['data'] = '参数不正确';
		return $result;
	}
	$GLOBALS['ts']['mid'] = $uid;
	
	$d['type']='feed';
	$d['curtable']='feed';
	$d['comment'] =0;
	$d['content'] = '';

	// 安全过滤
	foreach($d as $key => $val) {
		$d[$key] = t($d[$key]);
	}
	// 过滤内容值
	$d['body'] = filter_keyword($d['body']);
	// 判断资源是否删除
	if(empty($d['curid'])) {
		$map['feed_id'] = $d['sid'];
	} else {
		$map['feed_id'] = $d['curid'];
	}
	$map['is_del'] = 0;
	$isExist = model('Feed')->where($map)->count();
	if($isExist == 0) {
		$result['status'] = 0;
		$result['data'] = '内容已被删除，转发失败';
		return $result;
	}

	// 进行分享操作
	$result = model('Share')->shareFeed($d, 'share', null, $uid);
	// return $d;
	if($result['status'] == 1) {
		$app_name = $d['app_name'];

		// 添加积分
		if($app_name == 'public'){
			model('Credit')->setUserCredit($uid,'forward_weibo');
			//微博被转发
			$suid =  model('Feed')->where($map)->getField('uid');
			model('Credit')->setUserCredit($suid,'forwarded_weibo');
		}
		if($app_name == 'weiba'){
			model('Credit')->setUserCredit($uid,'forward_topic');
			//微博被转发
			$suid =  D('Feed')->where('feed_id='.$map['feed_id'])->getField('uid');
			model('Credit')->setUserCredit($suid,'forwarded_topic');
		}
		
	}
	return $result;
}

/**
 * 私信服务
 *
 * @param int $uid 			发送者uid；
 * @param int $toUid		接收者uid;
 * @param string $content	私信内容;
 *
 */
function sendPrivateLetter($uid,$toUid,$content){
	$d=array();
	$d['to'] = $toUid;
	$d['content']= $content;
	$d['type'] = null;

	$result = array('data'=>L('PUBLIC_SEND_SUCCESS'),'status'=>1);
	if (empty($uid) || empty($toUid)) {
		$result['data']='发送者或接收者不能为空';
		$result['status'] = 0;
		return $result;
	}
	if(trim(t($content)) == ''){
		$result['data'] = L('PUBLIC_COMMENT_MAIL_REQUIRED');
		$result['status'] = 0;
		return $result;
	}
	$toUid = trim(t($toUid),',');
	$to_num = explode(',', $toUid);
	if( sizeof($to_num)>10 ){
		$result['data'] = '';
		$result['status'] = 0;
		return $result;
	}
	$content = h($content);
	$res = model('Message')->postMessage($d, $uid);

	if ($res) {
		return $result;
	}else {
		$result['status'] = 0;
		$result['data']   = model('Message')->getError();;
		return $result;
	}
}

/**
 * 评论服务
 * array('app_name'=>'应用名称','table_name'=>'被评论内容表','row_id'=>'被评论内容id','app_uid'=>'被评论内容用户uid',
 * 		'uid'=>'评论者uid','content'=>'评论内容','to_comment_id'=>'被评论的评论id','to_uid'=>'被评论用户的uid','client_type'=>2)
 * @param array $data
 */
function addComment($data){
	// 返回结果集默认值
	$return = array (
			'status' => 0,
			'data' => L ( 'PUBLIC_CONCENT_IS_ERROR' )
	);
	if(empty($data) || intval($data['uid']) < 1 || intval($data['row_id']) < 1|| intval($data['app_uid']) < 1){
		$return['data'] = '参数有误';
		return $return;
	}
	$GLOBALS['ts']['mid'] = intval($data['uid']);
	// 安全过滤
	foreach ( $data as $key => $val ) {
		$data [$key] = t ( $data [$key] );
	}
	
	// 评论所属与评论内容
	$data ['app'] = $data ['app_name'];
	$data ['table'] = $data ['table_name'];
	$data ['content'] = h ( $data ['content'] );
	// 判断资源是否被删除
	$dao = M ( $data ['table'] );
	$idField = $dao->getPk ();
	$map [$idField] = $data ['row_id'];
	$sourceInfo = $dao->where ( $map )->find ();
	
	if (! $sourceInfo) {
		$return ['status'] = 0;
		$return ['data'] = '内容已被删除，评论失败';
		return $return ;
	}
	//兼容旧方法
	if(empty($data['app_detail_summary'])){
		$source = model ( 'Source' )->getSourceInfo ( $data ['table'], $data ['row_id'], false, $data ['app'] );
		$data['app_detail_summary'] = $source['source_body'];
		$data['app_detail_url']     = $source['source_url'];
		$data['app_uid']            = $source['source_user_info']['uid'];
	}else{
		$data['app_detail_summary'] = $data ['app_detail_summary'] . '<a class="ico-details" href="' . $data['app_detail_url'] . '"></a>';
	}
	
	// 添加评论操作
	$data ['comment_id'] = model ( 'Comment' )->addComment ( $data );
	
	/****---------在线答疑添加评论时增加行为记录 by xypan 0915---------****/
	if($data['table_name'] == 'onlineanswer_answer'){
			
		$ansid = $data['row_id'];
			
		// 找出问题id
		$qid = D('onlineanswer_answer')->where("ansid=$ansid")->field('qid')->find();
	
		// 记录行为
		D('Behavior','onlineanswer')->recordBehavior(array('qid'=>$qid['qid'],'uid'=>$GLOBALS['ts']['mid']));
	}
	/*******---------------end------------------********/
	
	if ($data ['comment_id']) {
		$return ['status'] = 1;
		$return ['data'] = '评论成功';
		
		// 同步到微吧
		if ($data ['app'] == 'weiba')
			$this->_upateToweiba ( $data );
			
		// 去掉回复用户@
		$lessUids = array ();
		if (! empty ( $data ['to_uid'] )) {
			$lessUids [] = $data ['to_uid'];
		}
		
		if ($_POST ['ifShareFeed'] == 1) {  // 转发到我的微博
			//解锁内容发布
			//unlockSubmit();
			$this->_updateToweibo ( $data, $sourceInfo, $lessUids );
		} else if ($data ['comment_old'] != 0) {  // 是否评论给原来作者
			//unlockSubmit();
			$this->_updateToComment ( $data, $sourceInfo, $lessUids );
		}
	}
	!$data['comment_id'] && $return['data'] = model('Comment')->getError();
	return $return;
}


$server = new PHPRPC_Server();
$server->add('sendBlog');
$server->add('addComment');
$server->add('shareFeed');
$server->add('sendPrivateLetter');

$server->start();
?>