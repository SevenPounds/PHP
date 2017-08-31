<?php
/**
 * 资源模型 - 业务逻辑模型
 * @example
 * 根据表名及资源ID，获取对应的资源信息
 * @author jason <yangjs17@yeah.net>
 * @version TS3.0
 */
class SourceModel {
	
	/**
	 * 获取指定资源，并格式化输出
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $_forApi
	 *        	是否提供API，默认为false
	 * @param string $appname
	 *        	自定应用名称，默认为public
	 * @return [type] [description]
	 */
	public function getSourceInfo($table, $row_id, $_forApi = false, $appname = 'public') {
		static $forApi = '0';
		$forApi == '0' && $forApi = intval ( $_forApi );
		
		$key = $forApi ? $table . $row_id . '_api' : $table . $row_id;
		if ($info = static_cache ( 'source_info_' . $key )) {
			return $info;
		}
		switch ($table) {
			case 'feed' :
				$info = $this->getInfoFromFeed ( $table, $row_id, $_forApi );
				break;
			case 'comment' :
				$info = $this->getInfoFromComment ( $table, $row_id, $_forApi );
				break;
			case 'poster' :
				$poster = D ( 'poster' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $poster ['uid'] );
				$info ['source_url'] = U ( 'poster/Index/posterDetail', array (
						'id' => $row_id 
				) );
				$info ['source_body'] = $poster ['title'] . '<a class="ico-details" href="' . U ( 'poster/Index/posterDetail', array (
						'id' => $row_id 
				) ) . '"></a>';
				break;
			case 'event' :
				$event = D ( 'event' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $event ['uid'] );
				$info ['source_url'] = U ( 'event/Index/eventDetail', array (
						'id' => $row_id,
						'uid' => $event ['uid'] 
				) );
				$info ['source_body'] = $event ['title'] . '<a class="ico-details" href="' . U ( 'event/Index/eventDetail', array (
						'id' => $row_id,
						'uid' => $event ['uid'] 
				) ) . '"></a>';
				break;
			case 'blog' :
				$blog = D ( 'blog' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $blog ['uid'] );
				$info ['source_url'] = U ( 'blog/Index/show', array (
						'id' => $row_id,
						'mid' => $blog ['uid'] 
				) );
				$info ['source_body'] = $blog ['title'] . '<a class="ico-details" href="' . U ( 'blog/Index/show', array (
						'id' => $row_id,
						'mid' => $blog ['uid'] 
				) ) . '"></a>';
				$info['source_content'] = $info ['source_body'];//应该插入微博内容，不仅只有日志标题。 
				//$info['source_content'] = $blog['title'] . '<a href="{'.U('blog/Index/show',array('id'=>$row_id,'mid'=>$blog ['uid']))}.'" class="ico-details" target="_blank"></a>';
				break;
			case 'photo':
				$photo = D('photo')->where('id='.$row_id)->field('name, albumId, userId')->find();
				$info['source_user_info'] = model('User')->getUserInfo($photo['userId']);
				$info['source_url'] = U('photo/Index/photo', array('id'=>$row_id, 'aid'=>$photo['albumId'], 'uid'=>$photo['userId']));
				$info['source_body'] = $photo['name'].'<a class="ico-details" href="'.$info['source_url'].'"></a>';
				break;
			//网络调研模块
			case 'vote' :
				$vote = D ( 'vote' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $vote ['uid'] );
				$info ['source_url'] = U ( 'vote/Index/detail', array (
						'id' => $row_id
				) );
				$info ['source_body'] = $vote ['title'] . '<a class="ico-details" href="' . U ( 'vote/Index/detail', array (
						'id' => $row_id
				) ) . '"></a>';
				break;
			//网络调研回复信息获取
			case 'vote_post':
				$vote = D ( 'vote_post' )->where ( 'id=' . $row_id )->field ( 'content,uid,vote_id' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $vote ['uid'] );
				$info ['source_url'] = U ( 'vote/Index/detail', array (
						'id' => $vote ['vote_id']
				) );
				$info ['source_body'] = parse_html($vote ['content']) . '<a class="ico-details" href="' . U ( 'vote/Index/detail', array (
						'id' => $vote ['vote_id']
				) ) . '"></a>';
				break;
				//by ylzhao
				//在线答疑回复消息获取
			case 'onlineanswer_answer' :
				$onlineanswer = D('onlineanswer_answer')->where('ansid='. $row_id)->find();
				$info ['source_user_info']=	model ( 'User' )->getUserInfo ( $onlineanswer['uid'] );
				$info['source_url'] = U('onlineanswer/Index/detail', array ('qid' => $onlineanswer['qid']));
				$info ['source_body'] = parse_html($onlineanswer ['content']) . '<a class="ico-details" href="' . U ( 'onlineanswer/Index/detail', array (
						'qid' => $onlineanswer ['qid']
				) ) . '"></a>';
				break;
				//主题讨论回复信息获取
			case 'research_post':
				$research = D ( 'research_post' )->where ( 'id=' . $row_id )->field ( 'content,post_userid,research_id' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $research ['post_userid'] );
				$info ['source_url'] = U ( 'research/Index/show', array (
						'id' => $research ['research_id']
				) );
				$info ['source_body'] = parse_html($research ['content']) . '<a class="ico-details" href="' . U ( 'research/Index/show', array (
						'id' => $research ['research_id']
				) ) . '"></a>';
				break;
				//网上评课回复信息获取.
			case 'pingke_post':
				$research = D ( 'pingke_post' )->where ( 'id=' . $row_id )->field ( 'content,uid,pingke_id' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $research ['uid'] );
				$info ['source_url'] = U ( 'research/Index/show', array (
						'id' => $research ['pingke_id']
				) );
				$info ['source_body'] = parse_html($research ['content']) . '<a class="ico-details" href="' . U ( 'pingke/Index/show', array (
						'id' => $research ['pingke_id']
				) ) . '"></a>';
				break;
			//by yxxing
			//教研指导、论文、反思评论发送消息
			case 'paper' :
				$paper = D ( 'paper' )->where ( 'id=' . $row_id )->field ( 'title,uid' )->find ();
				$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $paper ['uid'] );
				$info ['source_url'] = U ( 'paper/Index/preview', array (
						'id' => $row_id,
						'uid' => $paper ['uid'] 
				) );
				$info ['source_body'] = $paper ['title'] . '<a class="ico-details" href="' . U ( 'paper/Index/preview', array (
						'id' => $row_id,
						'uid' => $paper ['uid'] 
				) ) . '"></a>';
				$info['source_content'] = $info ['source_body'];//应该插入微博内容，不仅只有日志标题。 
				//$info['source_content'] = $blog['title'] . '<a href="{'.U('blog/Index/show',array('id'=>$row_id,'mid'=>$blog ['uid']))}.'" class="ico-details" target="_blank"></a>';
				break;
			
			default :
				// 单独的内容，通过此路径获取资源信息
				$appname = strtolower ( $appname );
				$name = ucfirst ( $appname );
				$dao = D ( $name . 'Protocol', $appname, false );
				if (method_exists ( $dao, 'getSourceInfo' )) {
					$info = $dao->getSourceInfo ( $row_id, $_forApi );
				}
				unset ( $dao );
				
				// 兼容旧方案
				if (!$info) {
					$modelArr = explode ( '_', $table );
					$model = '';
					foreach ( $modelArr as $v ) {
						$model .= ucfirst ( $v );
					}
					$dao = D ( $model, $appname );
					if (method_exists ( $dao, 'getSourceInfo' )) {
						$info = $dao->getSourceInfo ( $row_id, $_forApi );
					}
				}
				break;
		}
		$info ['source_table'] = $table;
		$info ['source_id'] = $row_id;
		static_cache ( 'source_info_' . $key, $info );
		return $info;
	}
	
	/**
	 * 从Feed中提取资源数据
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $forApi
	 *        	是否提供API，默认为false
	 * @return array 格式化后的资源数据
	 */
	private function getInfoFromFeed($table, $row_id, $forApi) {
		$info = model ( 'Feed' )->getFeedInfo ( $row_id, $forApi );
		//如果是班级或者学校的微博，则获取班级或学校信息
		if(intval($info['class_id']) != 0 ){
			if($info['app'] == "school"){
				$info ['source_user_info'] = D('CySchool')->get_shool_info_for_feed(intval($info['class_id']));
			}else{
				$info ['source_user_info'] = D('CyClass')->get_class_info_for_feed(intval($info['class_id']));
			}
			$info ['source_user_info']['uid'] = $info['uid'];
		}elseif(intval($info['gid']) != 0){
			$info ['source_user_info'] = D('MSGroup', "msgroup")->getMSGroupInfoForFeed(intval($info['gid']));
			$info ['source_user_info']['uid'] = $info['uid'];
		} else{
			$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $info ['uid'] );
		}
		$info ['source_user'] = $info ['uid'] == $GLOBALS ['ts'] ['mid'] ? L ( 'PUBLIC_ME' ) : $info ['source_user_info'] ['space_link']; // 我
		$info ['source_type'] = '动态';
		$info ['source_title'] = $forApi ? parseForApi ( $_info ['user_info'] ['space_link'] ) : $_info ['user_info'] ['space_link']; // 微博title暂时为空
		$info ['source_url'] = U ( 'public/Profile/feed', array (
				'feed_id' => $row_id,
				'uid' => $info ['uid'] 
		) );
		$info ['source_content'] = $info ['content'];
		$info ['ctime'] = $info ['publish_time'];
		unset ( $info ['content'] );
		return $info;
	}
	
	/**
	 * 从评论中提取资源数据
	 *
	 * @param string $table
	 *        	资源表名
	 * @param integer $row_id
	 *        	资源ID
	 * @param boolean $forApi
	 *        	是否提供API，默认为false
	 * @return array 格式化后的资源数据
	 */
	private function getInfoFromComment($table, $row_id, $forApi) {
		$_info = model ( 'Comment' )->getCommentInfo ( $row_id, true );
		$info ['uid'] = $_info ['app_uid'];
		$info ['row_id'] = $_info ['row_id'];
		$info ['is_audit'] = $_info ['is_audit'];
		$info ['source_user'] = $info ['uid'] == $GLOBALS ['ts'] ['mid'] ? L ( 'PUBLIC_ME' ) : $_info ['user_info'] ['space_link']; // 我
		$info ['comment_user_info'] = model ( 'User' )->getUserInfo ( $_info ['user_info'] ['uid'] );
		$forApi && $info ['source_user'] = parseForApi ( $info ['source_user'] );
		$info ['source_user_info'] = model ( 'User' )->getUserInfo ( $info ['uid'] );
		$info ['source_type'] = L ( 'PUBLIC_STREAM_COMMENT' ); // 评论
		$info ['source_content'] = $forApi ? parseForApi ( $_info ['content'] ) : $_info ['content'];
		$info ['source_url'] = $_info ['sourceInfo'] ['source_url'];
		$info ['ctime'] = $_info ['ctime'];
		$info ['app'] = $_info ['app'];
		$info ['sourceInfo'] = $_info ['sourceInfo'];
		// 微博title暂时为空
		$info ['source_title'] = $forApi ? parseForApi ( $_info ['user_info'] ['space_link'] ) : $_info ['user_info'] ['space_link'];
		
		return $info;
	}
}