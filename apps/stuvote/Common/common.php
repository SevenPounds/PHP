<?php
function IsHotList(){
	//读取推荐列表
	$votes = M('vote')->where(' isHot="1" ')->order( 'rTime DESC' )->limit(20)->findAll();
	foreach($votes as &$value){
		$value['username'] = getUserName($value['uid']);
		$value['title']    = getShort($value['title'],12-strlen($value['username'])/2);
	}
	return $votes;
}
//获取配置
function getConfig($key){
	$config = model('Xdata')->lget("vote");
	$config['defaultTime'] = $config['defaultTime']?$config['defaultTime']:7776000;
	$config['limitpage']   = $config['limitpage']?$config['limitpage']:20;
	$config['join']  	   = $config['join']=='following'?$config['join']:'all';
	return $config[$key];
}


/**
 * 给部分行为增加消息通知
 * @param int $uid 作出行为的用户id
 * @param array $toUId 被通知者的ids
 * @param string $title 网络投票的名称
 * @param int $voteId 网络投票Id
 * @param string $node node名称（vote/vote_del）
 */
function addVoteMessage($uid, $toUId, $title,$voteId=0, $node="stuvote",$content='',$is_stuQue = 1){
	
	// 增加通知::{user}邀请你参加{content}网络投票。<a href="{sourceurl}" target='_blank'>去看看>></a>
	$author = model ( 'User' )->getUserInfo ( $uid );
	$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';

	$config ['content'] = t($title);
	$config ['content'] = str_replace('◆','',$config ['content']);
	$config ['content'] = mStr($config ['content'], 34);
    if ('stuvote_post' == $node || 'stuvote_vote' == $node){
        $config ['title'] = t($content);
        $config ['title'] = str_replace('◆','',$config ['title']);
        $config ['title'] = mStr($config ['title'], 34);
    }
    $config ['sourceurl'] = U ('stuvote/Index/detail',array('id'=>$voteId));
	model ( 'Notify' )->sendNotify ( $toUId, $node, $config,$uid,$is_stuQue);
}

/**
 * 添加用户行为记录
 * @param $uid:发出行为的用户id
 * @param $to_uid:被发出行为的用户id
 * @param $row_id:发出行为的记录的主键
 * @param $to_rowid:被发出行为的记录的主键
 * @param $table:发出行为的记录所在的表
 */
function setVoteActionRecord($uid,$to_uid,$row_id,$to_rowid,$table){
    $actionData['uid'] = $uid;
    $actionData['to_uid'] = $to_uid;
    $actionData['row_id'] = $row_id;
    $actionData['to_rowid'] = $to_rowid;
    $actionData['app'] = 'vote';
    $actionData['table'] = $table;
    model('UserAction')->setAction($actionData);
}
?>