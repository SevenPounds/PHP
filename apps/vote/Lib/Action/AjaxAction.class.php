<?php
class AjaxAction extends Action {
	var $per_page = 10;
	/**
	 * 调研查询列表
	 * @return mixed
	 */
	public function getVoteList(){
		// 当前登录用户的id
		$uid = $this->mid;

		//当前选中导航
		$nav = isset($_POST['nav']) ? $_POST['nav'] : '';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : ''; //搜索关键字
		$status = isset($_POST['status'])? intval($_POST['status']) : 0; //调研状态

		$num = 5;
		//0 我发起的，1 我参与的，default 调研中心

		switch($status){
			case -1://已结束
				$map['deadline'] = array('LT',time());
				break;
			case 1://进行
				$map['deadline'] = array('GT',time());
				break;
			default://全部
				break;
		}
		if(!empty($keyword)){
			$map['title'] = array('LIKE', '%' . $keyword . '%');
		}

		$voteDao = D('Vote');

		switch($nav){
			case 0:
				$map['creator_login_name']  = $GLOBALS['ts']['user']['login'];
				$list = $voteDao->getVotesBycondition(1,$map,'id DESC',$num);

				break;

			case 1:
				$map['uid']  = $this->mid;
				$list = $voteDao->getVotesBycondition(2,$map,'id DESC',$num);
				break;
		}

		//ajax分页
		$ajaxpage = new AjaxPage(array('total_rows' => $list['count'],
			'method' => 'ajax',
			'ajax_func_name' => 'Vote.requestData',
			'now_page' => $list['nowPage'],
			'list_rows' => $num));
		$page = $ajaxpage->showAppPager();
		$this->assign('page', $page);

		$this->assign('totalcount', $list['count']>99?'99+':$list['count']);
		$this->assign('j', $num * ($list['nowPage'] - 1));
		$this->assign ('list', $list['data']);
		$this->assign('nav', $nav);

		$this->setTitle('网络调研');
		$this->display ('_table_vote');
	}

	/**
	 * 调研中心列表
	 * @return mixed
	 */
	public function getCenterList(){
		// 当前登录用户的id
		$uid = $this->mid;
		$num = $this->per_page;
		//当前选中导航
		$nav = isset($_POST['nav']) ? $_POST['nav'] : '';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$status = isset($_POST['status'])? $_POST['status'] : 0; //调研状态
		$tagsid = isset($_POST['tags'])? intval($_POST['tags']): 0;
		if($tagsid){
			$map['tagid'] = $tagsid;
		}

		switch(intval($status)){
			case -1://已结束
				$map['deadline'] = array('LT',time());
				break;
			case 1://进行
				$map['deadline'] = array('GT',time());
				break;
			default://全部
				break;
		}
		if(!empty($keyword)){
			$map['title'] = array('LIKE', '%' . $keyword . '%');
		}
		//TODO 标签条件查询
		$voteDao = D('Vote');

		//0 最新，1 热门，2 精华，3 关注
		switch($nav){
			case 0:
				$map['isHot'] = array('neq',2);
				$list = $voteDao->voteCenterSearch($map,'cTime DESC , vote_num DESC,commentCount DESC',$num);
				break;
			case 1:
				$map['isHot'] = array('neq',2);
				$list = $voteDao->voteCenterSearch($map,'vote_num DESC, commentCount DESC, cTime DESC',$num);
				break;
			case 2:
				$map['isHot'] = 2;
                $list = $voteDao->voteCenterSearch($map,'vote_num DESC, commentCount DESC, cTime DESC',$num);
				break;
			case 3:
				$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
				$in_arr = $this->_getInArr($in_arr);
				$map['uid'] = array('IN', trim($in_arr,"()")) ;
				$list = $voteDao->voteCenterSearch($map,'cTime DESC',$num);
				break;
		}


		//ajax分页
		$ajaxpage = new AjaxPage(array('total_rows' => $list['count'],
			'method' => 'ajax',
			'ajax_func_name' => 'Vote.requestData',
			'now_page' => $list['nowPage'],
			'list_rows' => $num));
		$page = $ajaxpage->showAppPager();
		$this->assign('page', $page);
		$this->assign('j', $num * ($list['nowPage'] - 1));
		$this->assign ('list', $list['data']);
		$this->assign('nav', $nav);
		$this->assign('keyword', $keyword);

		$this->setTitle('调研中心');
		$this->display ('_table_center');
	}


	/**
	 * 参与
	 * @deprecated
	 * @return array()
	 */
	function getPartakeVoteList() {
		$voteUserDao = D('VoteUser');
		$voteDao = D('Vote');

		$map = "uid = {$this->mid} AND opts <> ''";
		$temp = $voteUserDao->where($map)->field('distinct(vote_id)')->findAll();

		$votes = array();
		foreach( $temp as $value ) {
			$void[] = $value['vote_id'];
		}
		$where['id']   = array( 'in',$void );
		$votes = $voteDao->where( $where )->order( 'id DESC' )->findPage(getConfig('limitpage'));

		return $votes;
	}

	/**
	 * 最热
	 * @deprecated
	 * @return array
	 */
	function getHotVoteList() {
		$voteDao = D('Vote','vote');
		$map = array();
		$order = 'vote_num DESC';
		$votes	= $voteDao->where($map)->order($order)->findPage($this->perpage);

		return $votes;
	}

	/**
	 * 最新
	 * @deprecated
	 * @return array
	 */
	function getNewVoteList() {
		$voteDao = D('Vote','vote');
		$map = array();
		$order = 'cTime DESC';
		$votes	= $voteDao->where($map)->order($order)->findPage($this->perpage);

		return $votes;
	}

	/**
	 * 我关注
	 * @deprecated
	 * @return array
	 */
	function getFollowVoteList() {
		$voteDao = D('Vote','vote');

		$order = 'cTime DESC';
		$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
		$in_arr = $this->_getInArr($in_arr);
		$map    = " uid IN $in_arr ";

		$votes	= $voteDao->where($map)->order($order)->findPage($this->perpage);

		return $votes;
	}

	function _getInArr($in_arr) {

		$in_str = "(";
		foreach($in_arr as $key=>$v) {
			$in_str .= $v['fid'].",";
		}
		$in_str = rtrim($in_str,",");
		$in_str .= ")";
		return $in_str;

	}

	/**
	 * 获取投票用户信息  chengcheng3
	 */
	function getVoteUsers(){
		$id = intval($_REQUEST['id']);
		$pageSize = 40;//暂时去掉分页，一次获取一定量用户信息
		if( empty( $id ) || 0 == $id ) {
			exit(json_encode( "非法访问投票情况",true ));
		}
		$vote_users = D( 'VoteUser' )->getVoteUsers($id,'usr.id DESC',$pageSize);

		foreach ($vote_users['data'] as $k=>$v) {
			//获取创建者信息
			$user = model('Cache')->get('VoteUsers_'.$v['uid']);
			if(empty($user)) {
				$user = model('User')->getUserInfo($v['uid']);
				model('Cache')->set('VoteUsers_'.$v['uid'], $user, 3600);
			}
			if (empty($user)) continue;
			$userList[$k] = $user;
		}

		$this->assign("userList", $userList);

		$this->display('_voteUserList');
	}
	/**
	 * 添加一级回复
	 */
    public function addComment(){
        //检测用户是否登录
        if (empty($this->mid)) {
            $return['status'] = '400';
            $return['msg'] = '回复成功';
            exit(json_encode($return));
        }
        $data['vote_id'] = $_POST['vote_id'];
        //原始内容
        $data['content_origin'] = htmlspecialchars(t($_POST['content']));

        //敏感词检测
        $resultData = $this->sensitiveWord_svc->checkSensitiveWord($data['content_origin']);
        $resultData = json_decode($resultData, true);
        if ($resultData["Code"] != 0) {
            return;
        }

       $data['content'] = $resultData["Data"];
        $data['uid'] = $this->mid;
        $s = D('VotePost', 'vote')->addPost($data);

        $vote = D("Vote")->find($data['vote_id']);

		$user = model('Cache')->get('VoteUsers_'.$vote['uid']);
		if(empty($user)) {
			$user = model('User')->getUserInfo($vote['uid']);
			model('Cache')->set('VoteUsers_'.$vote['uid'], $user, 3600);
		}
        $vote['user_info'] = $user;
		$vote['name']=$user['uname'];

        if ($s) {
            $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在网络调研评论了“".$vote['title_origin']."”的网络调研";
            $logObj = $this->getLogObj(C("appType")["jyyy"]["code"],C("appId")["wldy"]["code"],C("opType")["reply"]['code'],$s,C("location")["localServer"]["code"],"","",$data['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment,$vote['user_info']['uid']);
            Log::writeLog(json_encode($logObj,JSON_UNESCAPED_UNICODE),3,SITE_PATH.C("LOGRECORD_URL"));

            //网络调研一级回复 消息记录
            $conmment = $this->user['uname'].'评论了您发起的“'.$vote['title'].'”调研主题';
            $map =array(
                "userid" => $vote['user_info']['cyuid'],
                "type" => "4",
                "content" => $conmment,
                "url" => C("SPACE").'index.php?app=vote&mod=Index&act=detail&id='.$data['vote_id']
            ) ;
            $list = array($map);
            $message = json_encode($list);
            $sendMsg = array(
                "timestamp"=>time(),
                "appId" => C("VOTE_APP_ID"),
                "message" => $message
            );
            Restful::sendMessage($sendMsg);
            $return['status'] = '200';
            $return['msg'] = '回复成功';
        } else {
            $return['status'] = '500';
            $return['msg'] = '回复失败，请稍后再试';
        }
        echo json_encode($return);
    }

	/**
	 * 删除一级回复
	 */
	public function delComment(){
		//检测用户是否登录
		if(empty($this->mid)){
			$return['status'] ='400';
			$return['msg'] ='回复成功';
			exit(json_encode($return));
		}
		$comment_id = t($_POST['comment_id']);
		$comData['id'] = $comment_id;
		$comment_info = D('VotePost','vote')->getPost($comData);

		if($this->mid !=$comment_info['uid']){
			$return['status'] ='500';
			$return['msg'] ='删除失败';
		}else{
			$s =D('VotePost','vote')->delPost($comment_id);
			if($s){
				$return['status'] ='200';
				$return['msg'] ='删除成功';
			}else{
				$return['status'] ='500';
				$return['msg'] ='删除失败，请稍后再试';
			}
		}

		echo json_encode($return);
	}
	/**
	 *  一级回复赞功能
	 */
	public function addAgree(){
		//检测用户是否登录
		if(empty($this->mid)){
			$return['status'] ='400';
			$return['msg'] ='回复成功';
			exit(json_encode($return));
		}

		$post_id =$_POST['post_id'];
		$s =D('AgreeBehaviour','vote')->addBehaviour($post_id,$this->mid);
		if($s){
			$return['status'] ='200';
			$return['msg'] ='点赞成功';
		}else{
			$return['status'] ='500';
			$return['msg'] ='点赞失败，请稍后再试';
		}
		echo json_encode($return);


	}
}
?>
