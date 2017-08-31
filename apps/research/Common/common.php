<?php

function get_file_url($path){
	return UPLOAD_URL.'/'.ltrim($path,'/');
}

/**
 * 给部分行为增加消息通知
 * @param int $uid 作出行为的用户id
 * @param array $toUId 被通知者的ids
 * @param string $title 课题|评课的名称
 * @param string $content 回复内容
 * @param int $research 课题|评课的 id
 * @param string $node node名称（reserach/research_comment_digg:评论的赞/research_comment/评论）
 */
function addResearchMessage($uid, $toUId, $title,$content, $researchId, $node="research"){

	// 增加通知::{user}邀请你参加{content}网络调研。<a href="{sourceurl}" target='_blank'>去看看>></a>
	$author = model ( 'User' )->getUserInfo ( $uid );
	$config['user'] = '<a href="'.$author ['space_url'].'" >'.$author ['uname'].'</a>';

	$config ['title'] = cutStr($title,15);
	$config ['content'] = parse_html(cutStr($content, 34));
	$config ['sourceurl'] = U ('research/Index/show',array('id'=>$researchId));
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