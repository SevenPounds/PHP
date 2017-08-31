<?php

/**
 * 给部分行为增加消息通知
 * @param int $uid 作出行为的用户id
 * @param array $toUId 被通知者的ids
 * @param string $title 网络调研的名称
 * @param int $qid 	在线答疑id
 * @param string $node node名称（answer/agreeAnswer）
 */
function addMessage($uid, $toUId, $title,$content, $qid, $node="answer"){
	// 增加通知::  {user} 赞了你的回答：<br/>{content}。<a href="{sourceurl}" target='_blank'>去看看>></a>
	//发送者
	$author = model ( 'User' )->getUserInfo ( $uid );
	$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';
	
	$config ['title'] = cutStr($title,15);
	$config ['content'] = parse_html(cutStr($content, 34));
	$config ['sourceurl'] = U ( 'onlineanswer/Index/detail', array (
			'qid' => $qid,
	) );
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