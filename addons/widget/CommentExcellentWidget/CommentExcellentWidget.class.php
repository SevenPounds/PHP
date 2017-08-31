<?php
/**
  * 精彩回复
  * @example W('CommentExcellent',array('tpl'=>'detail','row_id'=>72,'order'=>'DESC','app_uid'=>'14983','cancomment'=>1,'cancomment_old'=>0,'showlist'=>1,'canrepost'=>1))                                  
  * @author ylzhao
  */
class CommentExcellentWidget extends Widget {
	private static $rand = 1;
	
	/**
	 *
	 * @param
	 *        	string tpl 显示模版 默认为comment，一般使用detail表示详细资源页面的评论
	 * @param
	 *        	integer row_id 评论对象所在的表的ID
	 * @param
	 *        	string order 评论的排序，默认为ASC 表示从早到晚,应用中一般是DESC
	 * @param
	 *        	integer app_uid 评论的对象的作者ID
	 * @param
	 *        	integer cancomment 是否可以评论 默认为1,由应用中判断好权限之后传入给wigdet
	 * @param
	 *        	integer cancomment_old 是否可以评论给原作者 默认为1,应用开发时统一使用0
	 * @param
	 *        	integer showlist 是否显示评论列表 默认为1
	 * @param
	 *        	integer canrepost 是否允许转发 默认为1,应用开发的时候根据应用需求设置1、0
	 */
	public function render($data) {
		$var = array ();
		// 默认配置数据
		$var ['cancomment'] = 1; // 是否可以评论
		$var ['app_name'] = 'blog'; // 默认显示日志的精彩回复
		$var ['tpl'] = 'comment_blog'; // 显示模板
		$var ['table'] = 'blog';
		$var ['limit'] = 5;
		$var ['order'] = 'DESC';
		$_REQUEST ['p'] = intval ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : intval ( $_POST ['p'] );
		is_array ( $data ) && $var = array_merge ( $var, $data );
		$var['row_id'] = intval($var['row_id']);
		
		$map = array ();
		// 分页形式数据
		switch($var ['app_name']){
			case "blog":
				$map ['app'] = t ( $var ['app_name'] );
				$map ['table'] = t ( $var ['table'] );
				$map ['row_id'] = intval ( $var ['row_id'] );
				$var ['list_filed'] =array('comment_id','row_id','uid','digg_count','comment_count');
				$var ['list'] = model ( 'Comment' )->getExcellentCommentList ( $map, 'digg_count DESC', intval($var ['limit']) );
				//赞功能
				$comment_ids = getSubByKey($var['list'],'comment_id');
				$var['diggArr'] = model('CommentDigg')->checkIsDigg($comment_ids, $GLOBALS['ts']['mid']);
				break;
			case "research":
				$var ['list_filed'] =array('id','research_id','post_userid','agree_count','comment_count');
				$map ['research_id'] = intval ( $var ['row_id'] );
				$var ['list'] = model ( 'Post','research')->getExcellentPostList ( $map ,intval($var ['limit']) );
				//赞功能
				$post_ids = getSubByKey($var['list'],'id');
				$var['diggArr'] = model('AgreeBehaviour','research')->getIsBehaviourUser($post_ids, $GLOBALS['ts']['mid']);
				break;
			case "pingke":
				$var ['list_filed'] =array('id','pingke_id','uid','agree_count','comment_count');
				$map ['pingke_id'] = intval ( $var ['row_id'] );
				$var ['list'] = model ( 'PingkePost','pingke')->getExcellentPostList ( $map, intval($var ['limit']) );
				//赞功能
				$post_ids = getSubByKey($var['list'],'id');
				$var['diggArr'] = model('AgreeBehaviour','pingke')->getIsBehaviourUser($post_ids, $GLOBALS['ts']['mid']);
				break;
			case "vote":
				$var ['list_filed'] =array('id','vote_id','uid','agree_count','comment_count');
				$map ['vote_id'] = intval ( $var ['row_id'] );
				$var ['list'] = model ( 'VotePost','vote' )->getExcellentCommentList ( $map,intval($var ['limit']) );
				//赞功能
				$comment_ids = getSubByKey($var['list'],'id');
				$var['diggArr'] = model('AgreeBehaviour','vote')->getIsBehaviourUser($comment_ids, $GLOBALS['ts']['mid']);
				$var['table']='vote_post';
				break;
			case "onlineanswer":
				$var ['list_filed'] =array('ansid','qid','uid','agree_count','comment_count');
				$map ['qid'] = intval ( $var ['row_id'] );
				$var ['list'] = model ( 'Answer','onlineanswer')->getExcellentAnswerList ( $map, intval($var ['limit']) );
				//赞功能
				$post_ids = getSubByKey($var['list'],'id');
				$var['diggArr'] = model('Agree','onlineanswer')->checkIsAgreed($post_ids, $GLOBALS['ts']['mid']);
			}
		// 渲染模版
		$content = $this->renderFile ( dirname ( __FILE__ ) . "/" . $var ['tpl'] . '.html', $var );
		return $content;
	}
	

}