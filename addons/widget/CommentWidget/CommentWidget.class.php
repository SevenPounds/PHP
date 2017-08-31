<?php
/**
  * 评论发布/显示框
  * @example W('Comment',array('tpl'=>'detail','row_id'=>72,'order'=>'DESC','app_uid'=>'14983','cancomment'=>1,'cancomment_old'=>0,'showlist'=>1,'canrepost'=>1))                                  
  * @author jason <yangjs17@yeah.net> 
  * @version TS3.0
  */
class CommentWidget extends Widget {
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
		$var ['canrepost'] = 1; // 是否允许转发
		$var ['cancomment_old'] = 1; // 是否可以评论给原作者
		$var ['showlist'] = 1; // 默认显示原评论列表
		$var ['app_name'] = 'public';
		$var ['tpl'] = 'Comment'; // 显示模板
		$var ['table'] = 'feed';
		$var ['limit'] = 10;
		$var ['order'] = 'DESC';
		$var ['initNums'] = model ( 'Xdata' )->getConfig ( 'weibo_nums', 'feed' );
		$_REQUEST ['p'] = intval ( $_GET ['p'] ) ? intval ( $_GET ['p'] ) : intval ( $_POST ['p'] );
  		if (empty($data)) {
  			$data['app_uid'] = intval($_POST['app_uid']);
  			$data['row_id'] = intval($_POST['row_id']);
  			$data['app_row_id'] = intval($_POST['app_row_id']);
  			$data['app_row_table'] = t($_POST['app_row_table']);
  			$data['isAjax'] = intval($_POST['isAjax']);
  			$data['showlist'] = intval($_POST['showlist']);
  			$data['cancomment'] = intval($_POST['cancomment']);
  			$data['cancomment_old'] = intval($_POST['cancomment_old']);
  			$data['app_name'] = t($_POST['app_name']);
  			$data['table'] = t($_POST['table']);
  			$data['canrepost'] = intval($_POST['canrepost']);
  		}
		// empty ( $data ) && $data = $_POST;
		is_array ( $data ) && $var = array_merge ( $var, $data );
		$var['app_uid'] = intval($var['app_uid']);
		$var['row_id'] = intval($var['row_id']);
		if ($var ['table'] == 'feed' && $this->mid != $var ['app_uid']) {
			if ($this->mid != $var ['app_uid']) {
				$userPrivacy = model ( 'UserPrivacy' )->getPrivacy ( $this->mid, $var ['app_uid'] );
				if ($userPrivacy ['comment_weibo'] == 1) {
					$return = array (
							'status' => 0,
							'data' => L ( 'PUBLIC_CONCENT_TIPES' ) 
					);
					return $var ['isAjax'] == 1 ? json_encode ( $return ) : $return ['data'];
				}
			}
			// 获取资源类型
			$sourceInfo = model ( 'Feed' )->get ( $var ['row_id'] );
			$var ['feedtype'] = $sourceInfo ['type'];
			// 获取源资源作者用户信息
			$appRowData = model('Feed')->get(intval($rowData['app_row_id']));
			$var['user_info'] = $appRowData['user_info'];
		}
		
		if ($var ['showlist'] == 1) { // 默认只取出前10条
			$map = array ();
			$map ['app'] = t ( $var ['app_name'] );
			$map ['table'] = t ( $var ['table'] );
			$map ['row_id'] = intval ( $var ['row_id'] ); // 必须存在
			if (! empty ( $map ['row_id'] )) {
				// 分页形式数据
				$var ['list'] = model ( 'Comment' )->getCommentList ( $map, 'comment_id ' . t($var ['order']), intval($var ['limit']) );
			}
		} // 渲染模版
		if($var['table']=='blog'&&!empty($var['row_id'])){
			//赞功能
			$comment_ids = getSubByKey($var['list']['data'],'comment_id');
			$var['diggArr'] = model('CommentDigg')->checkIsDigg($comment_ids, $GLOBALS['ts']['mid']);
		}
		// 转发权限判断
		$weiboSet = model ( 'Xdata' )->get ( 'admin_Config:feed' );
		if (! CheckPermission ( 'core_normal', 'feed_share' ) || ! in_array ( 'repost', $weiboSet ['weibo_premission'] )) {
			$var ['canrepost'] = 0;
		}
		in_array($var['table'],array('onlineanswer_answer','pingke_post','comment','research_post','vote_post')) && $var['tpl'] = 'Comment_online';
		$content = $this->renderFile ( dirname ( __FILE__ ) . "/" . $var ['tpl'] . '.html', $var );
		self::$rand ++;
		$ajax = $var ['isAjax'];
		unset ( $var, $data );
		// 输出数据
		$return = array (
				'status' => 1,
				'data' => $content 
		);
		
		return $ajax == 1 ? json_encode ( $return ) : $return ['data'];
	}

	/**
	 * 获取评论列表
	 *
	 * @return array
	 */
	public function getCommentList() {
		$map = array ();
		$map ['app'] = t ( $_REQUEST ['app_name'] );
		$map ['table'] = t ( $_REQUEST ['table'] );
		$map ['row_id'] = intval ( $_REQUEST ['row_id'] ); // 必须存在
		if (! empty ( $map ['row_id'] )) {
			// 分页形式数据
			$var['limit'] = 10;
			$var ['order'] = 'DESC';
			$var['ajax_page'] = $_REQUEST ['ajax_page'];
			$var ['cancomment'] = $_REQUEST ['cancomment'];
			$var ['showlist'] = $_REQUEST ['showlist'];
			$var ['app_name'] = t ( $_REQUEST ['app_name'] );
			$var ['table'] = t ( $_REQUEST ['table'] );
			$var ['row_id'] = intval ( $_REQUEST ['row_id'] );
			$var ['list'] = model ( 'Comment' )->getCommentList ( $map, 'comment_id ' . $var ['order'], $var ['limit'] );
		}
		
		//by ylzhao
		//如果ajax_page为1，就切换评论列表模板为commentList_ajax，进行无刷新分页
		if($_REQUEST ['ajax_page']){
			$credit = D('comment');
			$count = $credit->where ("row_id='". $_REQUEST['row_id']."' AND is_del=0 AND `table`='".$var ['table']."'")->count(); //计算记录数//'row_id='. $_POST['row_id'] AND '`table`=onlineanswer_answer'
			$limitRows = $var['limit']; // 设置每页记录数
			$p = new AjaxPage(array('total_rows'=>$count,
				'method'=>'ajax',
				'parameter'=>$_REQUEST['row_id'],
				'ajax_func_name'=>'page',//到core.comment.js中，调用换页的page函数
				'list_rows'=>$limitRows));
			$limit_value = $p->firstRow . "," . $p->listRows;
			$page = $p->show(); // 产生分页信息，AJAX的连接在此处生成
			$var['page'] = $page;
			$var ['list'] = model ( 'Comment' )->getCommentList ( $map, 'comment_id ' . $var ['order'], $limitRows );
			$var['tpl'] = 'commentList_ajax';
			in_array($var['table'],array('onlineanswer_answer','pingke_post','comment','research_post','vote_post')) && $var['tpl'] = 'commentList_online';
			$content = $this->renderFile ( dirname ( __FILE__ ) . '/'.$var['tpl'].'.html',$var,$row_id);
		}
		else
			$content = $this->renderFile ( dirname ( __FILE__ ) . '/commentList.html', $var );
		exit ( $content );
	}

	/**
	 * 添加评论的操作
	 *
	 * @return array 评论添加状态和提示信息
	 */
	public function addcomment() {
		// 返回结果集默认值
		$return = array (
				'status' => 0,
				'data' => L ( 'PUBLIC_CONCENT_IS_ERROR' ) 
		);
		if(empty($this->mid)){
			$return['data'] ='请您登录';
			exit(json_encode($return));
		}
		// 获取接收数据
		$data = $_POST;
        $data['app_uid'] = intval($_POST['app_uid']);
		// 安全过滤
		foreach ( $data as $key => $val ) {
			$data [$key] = t ( $data [$key] );
		}
		// 评论所属与评论内容
		$data ['app'] = $data ['app_name'];
		$data ['table'] = $data ['table_name'];
		// 判断资源是否被删除
		$dao = M ( $data ['table'] );
		$idField = $dao->getPk ();
		//$map [$idField] = $data ['row_id'];
        $map [$idField] = intval($data ['row_id']);
		$sourceInfo = $dao->where ( $map )->find ();

		if (! $sourceInfo) {
			$return ['status'] = 0;
			$return ['data'] = '内容已被删除，评论失败';
			exit ( json_encode ( $return ) );
		}

        //原始内容
        $data['content_origin'] = $data['content'];
        //敏感词检测
       $resultData = $this->sensitiveWord_svc->checkSensitiveWord($data['content']);
        $resultData = json_decode($resultData, true);
        if ($resultData["Code"] != 0) {
            return;
        }
		$data ['content'] = h ( $data ['content'] );

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
        $resultData = model ( 'Comment' )->getCommentInfo($data ['comment_id']);
        $data['content'] =h ( $resultData["content"] );
		/****---------在线答疑添加评论时增加行为记录 by xypan 0915---------****/
		if($data['table_name'] == 'onlineanswer_answer'){
			
			$ansid = $data['row_id'];
			// 找出问题id
			$qid = D('onlineanswer_answer')->where("ansid=$ansid")->field('qid')->find();
			// 记录行为
			D('Behavior','onlineanswer')->recordBehavior(array('qid'=>$qid['qid'],'uid'=>$GLOBALS['ts']['mid']));
		}
		/*******---------------end------------------********/
        if(isset($data["to_uid"])&& !empty($data["to_uid"])){
            $targetUser = M("User")->getUserInfo($data['to_uid']);
        }else{
            $targetUser = M("User")->getUserInfo($data['app_uid']);
        }

        $message_info = $this->getMessageInfo($data,$targetUser,$sourceInfo,$source);
        if($message_info["isReply"] == true){
            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在".$message_info["name"]."回复了“".$message_info["title"].'”的'.$message_info["name"];
        }else{
            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在".$message_info["name"]."评论了“".$message_info["title"].'”的'.$message_info["name"];
        }

        $logObj = $this->getLogObj(C("appType")[$message_info["appType"]]["code"],C("appId")[$message_info['rzcode']]["code"],C("opType")["comment"]['code'],$sourceInfo['id'],C("location")["localServer"]["code"],"","",$sourceInfo['title'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment,$targetUser['uid']);
        Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

             $map =array(
             "userid" => $targetUser["cyuid"],
             "type" => "4",
             "content" => $message_info["content"],
             "url" => $message_info["url"]
        ) ;
        $list = array($map);
        $message = json_encode($list);
        $sendMsg = array(
            "timestamp"=>time(),
            "appId" => $message_info["appId"],
            "message" => $message
        );

        Restful::sendMessage($sendMsg);

		if ($data ['comment_id']) {
			$return ['status'] = 1;
			$return ['data'] = $this->parseComment ( $data );
			
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

		exit ( json_encode ( $return ) );
	}

    private function  getMessageInfo($data,$targetUser,$sourceInfo,$source){
        $appName = $data['app_name'];
        $isSS = false;
        $isReply = false;
        switch($appName)
        {
            case "research":
                if(!isset($sourceInfo["research_id"])){
                    $isSS = true;
                    break;
                }
                $res = M('Research','research')->getResearchById($sourceInfo["research_id"],'*');
                $name = "主题讨论";
                $rzcode = "zttl";
                $content = $this->user['uname'].'回复了您在“'.$res['title'].'”主题讨论下的评论';
                $appId = C("RESEARCH_APP_ID");
                $url = C("SPACE").'index.php?app=research&mod=Index&act=show&id='.$sourceInfo["research_id"];
                $isReply = true;
                $title = $res['title_origin'];
                $appType = "jyyy";
                break;
            case "onlineanswer":
                if(!isset($sourceInfo["qid"])){
                    $isSS = true;
                    break;
                }
                $res  = M("Question","onlineanswer")->questionDetail($sourceInfo["qid"]);;
                $name = "在线答疑";
                $rzcode = "zxdy";
                $appId = C("ONLINEANSWER_APP_ID");
                $url = C("SPACE").'index.php?app=onlineanswer&mod=Index&act=detail&qid='.$sourceInfo["qid"];
                $content = $this->user['uname'].'回复了您在“'.$res['title'].'”在线答疑下的评论';
                $isReply = true;
                $title = $res['title_origin'];
                $appType = "jyyy";
                break;
            case "vote":
                if(!isset($sourceInfo["vote_id"])){
                    $isSS = true;
                    break;
                }

                $res = M("Vote",'vote')->find($sourceInfo["vote_id"]);
                $name = "网络调研";
                $rzcode = "wldy";
                $appId = C("VOTE_APP_ID");
                $url = C("SPACE").'index.php?app=vote&mod=Index&act=detail&id='.$sourceInfo['vote_id'];
                $content = $this->user['uname'].'回复了您在“'.$res['title'].'”调研主题下的评论';
                $isReply = true;
                $title = $res['title_origin'];
                $appType = "jyyy";
                //return ["name"=>"网络调研","rzcode"=>"wldy"];
                break;
            case "pingke":
                if(!isset($sourceInfo["pingke_id"])){
                    $isSS = true;
                    break;
                }
                $res  = M('pingke')->find($sourceInfo["pingke_id"]);
                $name = "网上评课";
                $rzcode = "wspk";
                $appId = C("PINGKE_APP_ID");
                $url = C("SPACE").'index.php?app=pingke&mod=Index&act=show&id='.$sourceInfo["pingke_id"];
                $content = $this->user['uname'].'回复了您在“'.$res['title'].'”网上评课下的评论';
                $isReply = true;
                $title = $res['title_origin'];
                $appType = "jyyy";
                break;
           case "public":
                if((isset($data['table'])&&$data['table'] == "photo")){
                    if(strpos($data['content'],'@') == false){
                        //一级回复
                        $content = $this->user['uname'].'评论您的“'.$sourceInfo["name"].'”照片';
                    }else{
                        $isReply = true;
                        //二级回复
                        $content = $this->user['uname'].'回复了您在“'.$sourceInfo["name"].'”照片下的评论';
                    }
                    $url = C("SPACE").'index.php?app=photo&mod=Index&act=photo&id='.$sourceInfo['id'].'&aid='.$sourceInfo['albumId'].'&uid='.$sourceInfo['userId'].'#show_pic';
                    $name = "相册";
                    $rzcode = "xc";
                    $title = $sourceInfo["name_origin"];
                }else{
                    $isSS = true;
                }
                $appType = "hdjl";
                $appId = C("SNS_APP_ID");
                break;
            case "blog":
                $name = "日志";
                $rzcode = "wspk";
                $appId = C("SNS_APP_ID");
                $url = C("SPACE")."index.php?app=blog&mod=Index&act=show&id=".$sourceInfo['id']."&mid=".$sourceInfo['uid'];
                $riName = $sourceInfo['title'];
                if($data["table_name"] == 'feed'){
                    $riName = replaceMessage($source['feed_content']);
                }
                if(strpos($data['content'],'@') == false){
                    $content = $this->user['uname'].'评论了您的“'.$riName.'”日志';
                }else{
                    $isReply = true;
                    $content = $this->user['uname'].'回复了您在“'.$riName.'”日志下的评论';
                }
                $title = $sourceInfo['title_origin'];
                $appType = "hdjl";
                break;
            default:
                $isSS = true;
        }
        if($isSS){
            $name = "说说";
            $rzcode = "ss";
            $appId = C("SNS_APP_ID");
            $feed_context = replaceMessage($source['feed_content']);
           /* if(strrpos($source['feed_content'],'】') != false){
                $name = substr($source['feed_content'],-2,strrpos($source['feed_content'],',点击查看',0));
            }else{
                $name = $source['feed_content'];
            }*/
            if(strpos($data['content'],'@') == false){
                $content = $this->user['uname'].'评论了您的“'.$feed_context.'”说说';
            }else{
                $isReply = true;
                $content = $this->user['uname'].'回复了您在“'.$feed_context.'”说说下的评论';
            }
            $appType = "hdjl";
            $title = $source['feed_content_origin'];
            $url = C("SPACE")."index.php?app=public&mod=Workroom&act=index&uid=".$data['app_uid'];
        }
        return ["name"=>$name,"rzcode"=>$rzcode,"content"=>$content,"appId"=>$appId,"url"=>$url,"isReply" =>$isReply,"title"=>$title,"appType"=>$appType];
    }


	/**
	 * 删除评论
	 *
	 * @return bool true or false
	 */
	public function delcomment() {
		// if( !CheckPermission('core_normal','comment_del') && !CheckPermission('core_admin','comment_del')){
		// return false;
		// }
		$comment_id = intval ( $_POST ['comment_id'] );
		$comment = model ( 'Comment' )->getCommentInfo ( $comment_id );
		// 不存在时
		if (! $comment) {
			return false;
		}
		// 非作者时
		if ($comment ['uid'] != $this->mid) {
			// 没有管理权限不可以删除
			if (! CheckPermission ( 'core_admin', 'comment_del' )) {
				return false;
			}
			// 是作者时
		} else {
			// 没有前台权限不可以删除
			if (! CheckPermission ( 'core_normal', 'comment_del' )) {
				return false;
			}
		}
		
		if (! empty ( $comment_id )) {
			return model ( 'Comment' )->deleteComment ( $comment_id, $this->mid );
		}
		return false;
	}
	
	/**
	 * 渲染评论页面 在addcomment方法中调用
	 */
	public function parseComment($data) {
		$data ['userInfo'] = model ( 'User' )->getUserInfo ( $GLOBALS ['ts'] ['uid'] );
		// 获取用户组信息
		$data ['userInfo'] ['groupData'] = model ( 'UserGroupLink' )->getUserGroupData ( $GLOBALS ['ts'] ['uid'] );
		$data ['content'] = preg_html ( $data ['content'] );
		$data ['content'] = parse_html ( $data ['content'] );
		$data ['iscommentdel'] = CheckPermission ( 'core_normal', 'comment_del' );
		$tpl = '_parseComment';
		in_array($data['table'],array('onlineanswer_answer','pingke_post','comment','research_post','vote_post')) && $tpl = '_parseComment_online';
		return $this->renderFile ( dirname ( __FILE__ ) . "/".$tpl.".html", $data );
	}

	// 同步到微吧
	function _upateToweiba($data) {
		$postDetail = D ( 'weiba_post' )->where ( 'feed_id=' . $data ['row_id'] )->find ();
		if (! $postDetail)
			return false;
		
		$datas ['weiba_id'] = $postDetail ['weiba_id'];
		$datas ['post_id'] = $postDetail ['post_id'];
		$datas ['post_uid'] = $postDetail ['post_uid'];
		$datas ['to_reply_id'] = $data ['to_comment_id'] ? D ( 'weiba_reply' )->where ( 'comment_id=' . $data ['to_comment_id'] )->getField ( 'reply_id' ) : 0;
		$datas ['to_uid'] = $data ['to_uid'];
		$datas ['uid'] = $this->mid;
		$datas ['ctime'] = time ();
		$datas ['content'] = $data ['content'];
		$datas ['comment_id'] = $data ['comment_id'];
		$datas ['storey'] = model ( 'comment' )->where ( 'comment_id=' . $data ['comment_id'] )->getField ( 'storey' );
		if (D ( 'weiba_reply' )->add ( $datas )) {
			$map ['last_reply_uid'] = $this->mid;
			$map ['last_reply_time'] = $datas ['ctime'];
			$map ['reply_count'] = array (
					'exp',
					"reply_count+1" 
			);
			D ( 'weiba_post' )->where ( 'post_id=' . $datas ['post_id'] )->save ( $map );
		}
	}

	// 转发到我的微博
	function _updateToweibo($data, $sourceInfo, $lessUids) {
		$commentInfo = model ( 'Source' )->getSourceInfo ( $data ['table'], $data ['row_id'], false, $data ['app'] );
		$oldInfo = isset ( $commentInfo ['sourceInfo'] ) ? $commentInfo ['sourceInfo'] : $commentInfo;
		
		// 根据评论的对象获取原来的内容
		$arr = array (
				'post',
				'share',
				'postimage',
				'postfile',
				'weiba_post',
				'postvideo'
		);
		$scream = '';
		if (! in_array ( $sourceInfo ['type'], $arr )) {
			$scream = '//@' . $commentInfo ['source_user_info'] ['uname'] . '：' . $commentInfo ['source_content'];
		}
		if (! empty ( $data ['to_comment_id'] )) {
			/**
			 * 修改BUG，将原来的init修改为setAppName和setAppTable的初始化方法
			 * @author yxxing
			 */
			$replyInfo = model ( 'Comment' )->setAppName( $data ['app'])->setAppTable($data ['table'] )->getCommentInfo ( $data ['to_comment_id'], false );
			$replyScream = '//@' . $replyInfo ['user_info'] ['uname'] . ' ：';
			$data ['content'] .= $replyScream . $replyInfo ['content'];
		}
		$s ['body'] = $data ['content'] . $scream;
		
		$s ['sid'] = $oldInfo ['source_id'];
		$s ['app_name'] = $oldInfo ['app'];
		$s ['type'] = $oldInfo ['source_table'];
		$s ['comment'] = $data ['comment_old'];
		$s ['comment_touid'] = $data ['app_uid'];
		
		// 如果为原创微博，不给原创用户发送@信息
		if ($sourceInfo ['type'] == 'post' && empty ( $data ['to_uid'] )) {
			$lessUids [] = $this->mid;
		}
		model ( 'Share' )->shareFeed ( $s, 'comment', $lessUids );
		model ( 'Credit' )->setUserCredit ( $this->mid, 'forwarded_weibo' );
	}
	
	// 评论给原来作者
	function _updateToComment($data, $sourceInfo, $lessUids) {
		$commentInfo = model ( 'Source' )->getSourceInfo ( $data ['app_row_table'], $data ['app_row_id'], false, $data ['app'] );
		$oldInfo = isset ( $commentInfo ['sourceInfo'] ) ? $commentInfo ['sourceInfo'] : $commentInfo;
		// 发表评论
		$c ['app'] = $data ['app'];
		$c ['table'] = 'feed'; // 2013/3/27
		$c ['app_uid'] = ! empty ( $oldInfo ['source_user_info'] ['uid'] ) ? $oldInfo ['source_user_info'] ['uid'] : $oldInfo ['uid'];
		$c ['content'] = $data ['content'];
		$c ['row_id'] = ! empty ( $oldInfo ['sourceInfo'] ) ? $oldInfo ['sourceInfo'] ['source_id'] : $oldInfo ['source_id'];
		if ($data ['app']) {
			$c ['row_id'] = $oldInfo ['feed_id'];
		}
		$c ['client_type'] = getVisitorClient ();
		
		model ( 'Comment' )->addComment ( $c, false, false, $lessUids );
	}
}