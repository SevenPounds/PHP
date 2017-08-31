<?php

/**
 * 给部分行为增加消息通知
 * @param int $uid 作出行为的用户id
 * @param array $toUId 被通知者的ids
 * @param string $title 评课的名称
 * @param string $content 评论的内容
 * @param int $pingkeId 评课Id
 * @param string $node node名称（pingke/pingke_del）
 */
function addPingkeMessage($uid, $toUId, $title,$content, $pingkeId, $node="pingke"){
	
	// 增加通知::{user}邀请你参加{content}课题研究|网上评课。<a href="{sourceurl}" target='_blank'>去看看>></a>
	$author = model ( 'User' )->getUserInfo ( $uid );
	$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';
	
	$config ['title'] = cutStr($title,15);
	$config ['content'] = parse_html(cutStr($content, 34));
	$config ['sourceurl'] = U ('pingke/Index/show',array('id'=>$pingkeId));
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