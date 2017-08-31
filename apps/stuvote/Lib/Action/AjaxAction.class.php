<?php
class AjaxAction extends Action {
	var $per_page = 10;

	/**
	 * 投票查询列表
	 * @return mixed
	 */
	public function getVoteList(){
		// 当前登录用户的id
		$uid = $this->mid;

		//当前选中导航
		$nav = isset($_POST['nav']) ? $_POST['nav'] : '';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : ''; //搜索关键字
		$status = isset($_POST['status'])? intval($_POST['status']) : 0; //投票状态
		
		$num = 5;
		//0 我发起的，1 我参与的，default 投票中心
		
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
		if(empty($list['count'])){
			$list['count'] = 0;
		}
		$this->assign('totalcount', $list['count']>99?'99+':$list['count']);
		$this->assign('j', $num * ($list['nowPage'] - 1));
		$this->assign ('list', $list['data']);
		$this->assign('nav', $nav);

		$this->setTitle('在线投票');
		$this->display ('_table_vote');
	}

	/**
	 * 投票中心列表
	 * @return mixed 
	 */
	public function getCenterList(){
		// 当前登录用户的id
		$uid = $this->mid;
		$num = $this->per_page;
		//当前选中导航
		$nav = isset($_POST['nav']) ? $_POST['nav'] : '';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$status = isset($_POST['status'])? $_POST['status'] : 0; //投票状态
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
		
		//0 热门，1 最新，2 关注
		switch($nav){
			case 0:
                $map['isHot'] = array('neq',2);
				$list = $voteDao->voteCenterSearch($map,'commentCount desc,vote_num DESC',$num);
				break;

			case 1:
                $map['isHot'] = array('neq',2);
				$list = $voteDao->voteCenterSearch($map,'cTime DESC',$num);
				break;

			case 2:
				$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
				$in_arr = $this->_getInArr($in_arr);
				$map['uid'] = array('IN', trim($in_arr,"()")) ;
				$list = $voteDao->voteCenterSearch($map,'cTime DESC',$num);
				break;
            case 3:   //精华投票
                $map['isHot'] = 2;
                $list = $voteDao->voteCenterSearch($map,'commentCount desc, vote_num desc, cTime desc',$num);
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

		$this->setTitle('投票中心');
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
			$user = model('User')->getUserInfo($v['uid']);
			if (empty($user)) continue;
			$userList[$k] = $user;
		}
		
		$this->assign("userList", $userList);
		
		$this->display('_voteUserList');
	}
}
?>
