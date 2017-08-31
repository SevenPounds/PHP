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
 * @param string $title 网络调研的名称
 * @param int $voteId 网络调研Id
 * @param string $node node名称（vote/vote_del）
 */
function addVoteMessage($uid, $toUId, $title,$content, $voteId, $node="vote"){
	
	// 增加通知::{user}邀请你参加{content}网络调研。<a href="{sourceurl}" target='_blank'>去看看>></a>
	$author = model ( 'User' )->getUserInfo ( $uid );
	$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';

	$config ['title'] = cutStr($title,15);
	$config ['content'] = parse_html(cutStr($content, 34));
	$config ['sourceurl'] = U ('vote/Index/detail',array('id'=>$voteId));
	model ( 'Notify' )->sendNotify ( $toUId, $node, $config,$uid);
}
/**
 * 处理消息字段内容和标题
 * @param unknown $str 字符串
 * @param unknown $length 截取长度
 */
function cutStr($str,$length){
	$str =t($str);
	$str=str_replace('◆','',$str);
	return getShort($str, $length,'...');
}
?>