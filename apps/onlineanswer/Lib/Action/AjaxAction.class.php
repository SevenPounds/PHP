<?php
class AjaxAction extends Action{
	
	public function getQuestionList(){
		// 当前登录用户的id
		$uid = $this->mid;
		//当前选中导航
		$nav = isset($_POST['nav'])?$_POST['nav']:'';
		$choose =isset($_POST['choose'])?$_POST['choose']:0;
		$condition = array();
		$status = isset($_POST['status'])?$_POST['status']:'';
		$keyword = isset($_POST['keyword'])?$_POST['keyword']:'';
		$tagid = isset($_POST['tagid'])?$_POST['tagid']:'';
		$order ="";
		//0 最新问题，1 我的问题，2 我的参与，default 答疑中心
		switch($nav){
			case 0:
				$num = 20;
				break;
			case 1:
				$num = 5;
				break;
			case 2:
				$num = 5;
				break;
			case 3:
				$num=10;
				//0:最新;1:最热;2:精华;3:我关注的人
				switch($choose){
					case 0:
						$condition['isHot'] = array('neq',2);
						$order ='q.ctime DESC , q.answer_count DESC';
						break;
					case 1:
						$condition['isHot'] = array('neq',2);
						$order='q.answer_count DESC, q.ctime DESC';
						break;
					case 2:
						$condition['isHot'] = 2;
						$order='q.answer_count DESC, q.ctime DESC';
						break;
					case 3:
						$in_arr = M('user_follow')->field('fid')->where("uid={$this->mid}")->findAll();
						$in_arr = $this->_getInArr($in_arr);
						$condition['q.uid'] = array('IN', trim($in_arr,"()")) ;
						$order='q.ctime DESC';
						break;
						
				}
		}
		$condition['isDeleted'] = 0;
		$condition['keyword']  =$keyword;
		if($status!=""){
			$condition['status'] = $status;
		}
		if($tagid!=""){
			$condition['tagid'] =$tagid;
		}
		$list = D('Question')->getQuestionList($uid,$num,$nav,$condition,$order);
		$totalcount = $list['count'];
		if($nav!=0){
            $ajaxpage = new AjaxPage(array('total_rows'=>$totalcount,
            'method'=>'ajax',
            'ajax_func_name'=>'Question.requestData',
            'now_page'=>$list['nowPage'],
            'list_rows'=>$num));
            $page = $ajaxpage->showAppPager();
            $this->assign( "page", $page );
		}
		$list = $this->codeToName($list['data']);
		$this->assign ( 'list', $list);
		$this->assign('nav',$nav);
		$this->setTitle( '在线答疑' );
		$this->assign("totalcount",$totalcount);
		$nav == 3 ? $this->display('center_list') : $this->display ('ajax_list');
	}

	public function codeToName($list){
		$node = model('Node');
		foreach ($list as &$value){
			$value['grade'] = $node->getNameByCode('grade',$value['grade']);
			$value['subject'] = $node->getNameByCode('subject',$value['subject']);
		}
		return $list;
	}
	
	/**
	 * 删除我的问题
	 */
	public function delete() {
		$qid = intval($_POST['qid']);
		$uid = $this->mid;
		// 实例化NoticeModel
		$Question = D('Question');
		// 插入数据库
		$res = $Question->deleteQuestion($qid,$uid);
		if($res){
			echo("{status:'200',data:$res}");
		}else{
			echo("{status:'400',data:$res}");
		}
	}
	/*
	 * 根据qid获取问题的答案
	*/
	public function getAnswerbyQid(){
		$qid = intval($_POST['qid']);
		$type = $_POST['type'];//1为全部问题、2为我的问题
		$uid = $this->mid;
		$nowpage = $_REQUEST['p']?$_REQUEST['p']:1;
	    // 实例化AnswerModel
		$Answer = D('Answer');
		// 查询出该问题的所有回答列表
		switch ($type){
			case 1:
				$answersall = $Answer->answers($qid);
				break;
			case 2:
				$answersall = $Answer->getUserAnswers($qid,$uid);
				break;
		}
		$answers = $answersall['data'];
		$count= $answersall['count'];
		foreach($answers as &$answer){
			$answer = array_merge ( $answer, model ( 'Avatar' )->init ( $answer['uid'] )->getUserPhotoFromCyCore ($answer['uid'],"uid",C("ONLINEANSWER_APP_ID")) );
		}
		//发表与否控制,如果回答过该问题或者该问题已有最佳答案则关闭发表答案功能
		//查询问题详情
		$list = D('Question')->questionDetail($qid);
		if($list['status'] == '1'){
			for($i=0;$i<count($answers);$i++){
				if($answers[$i]['is_best'] == '1'){
					unset($answers[$i]);//问题已解决则被采纳的回答分开展示
				}
			}
		}
		// 当前登录用户不是提问者，默认没有回答过该问题
		$flag = true;
		foreach ($answers as $value){
			if($uid == $value['uid']){
				// 当前登录用户已经回答过该问题
				$flag = false;
			}
		}
		$isPublish = $flag && !$list['status'] && !($uid == $list['uid']);
		// 如果回答列表不为空
		if(!empty($answers)){
			// 创建一个 存储所有回答id的数组
			$ansids = array();
			// 遍历回答数组
			foreach($answers as $value){
				array_push($ansids,$value['ansid']);
			}
			// 实例化AgreeModel
			$Agree = D('Agree');
			// 模板变量
			$this->agreeArray = $Agree->checkIsAgreed($ansids,$uid);
		}
		$p = new AjaxPage(array('total_rows'=>$count,
				'method'=>'ajax',
				'ajax_func_name'=>'getAnswersByQid',
				'parameter'=>$type,
				'now_page'=>$nowpage,
				'list_rows'=>10));
		$page = $p->showAppPager();
		$this->answers  = $answers;
		$this->uid = $uid;
		$this->qid = $qid;
		$this->quid = $list['uid'];
		$this->page  = $page;
		$this->isPublish = $isPublish;
		$this->status = $list['status'];
		$this->display("_detail_answering_new");
	}
	/*
	 * 根据aid删除答案
	*/
	public function deleteAnswerByAid(){
		$aid = intval($_POST['aid']);
		$res = D('Answer')->deleteAnswer($aid);
		if($res){
			echo("{status:'200',data:$res}");
		}else{
			echo("{status:'400',data:$res}");
		}
	}
	/*
	 * 动态获取参与成员
	 */
	public function getJoinMember(){
		$qid = $_REQUEST['qid'];
		$nowpage = $_REQUEST['p'];
		$uid = $this->mid;
        $res = D('Answer')->getQuestionAnswerUsers($qid);
		$data = $res['data'];
		foreach($data as $key=>&$dad){
            $dad = array_merge ( $dad, model ( 'Avatar' )->init ( $dad['uid'] )->getUserPhotoFromCyCore ($dad['uid'],"uid",C("ONLINEANSWER_APP_ID")) );
		}
		$p = new AjaxPage(array('total_rows'=>$res['count'],
				'method'=>'ajax',
				'ajax_func_name'=>'getJoinMember',
				'now_page'=>$nowpage,
				'list_rows'=>10));
		$page = $p->showAppPager();
		$this->members = $data;
		$this->display("ajax_member");
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
}
