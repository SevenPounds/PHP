<?php
class FileAction extends AccessAction {
	
	/**
	 * 文件通知首页
	 */
	public function index() {
		
		//导入分页类
		import("@.ORG.Page"); 
		
		// 当前登录用户的id
		$mid = $this->mid;
		
		// 查询条件
		$condition = array();
		$condition['uid'] = $mid;
		// 类型为3标识是文件通知
		$condition['type'] = 3;
		$condition['isDeleted']  = 0;
		
		// 获取排序字段
		$order = empty($_GET['order'])?'dt':$_GET['order'];
		
		// 把GET的order值放到模板变量上
		$this->order = $order;
		
		switch($_GET['order']){
			case 'uv':$order = "viewcount asc";break;
			case 'dv':$order = "viewcount desc";break;
			case 'ut':$order = "ctime asc";break;
			default:$order = "ctime desc";
		}
		
		// 分页时每页记录数
		$num = 10;
		
		// 实例化NoticeModel
		$Notice = D('Notice');
		
		// 查询出公告总数
		$count =  $Notice->getNoticeCount($condition);

		$p = new Page($count, $num);
		$nowPage = $p->nowPage;
		$p->setConfig('prev', '上一页');
		$p->setConfig('next', '下一页');
		$page = $p->show();

		// 公告列表
		$list = $Notice->getNoticeLists($nowPage,$condition,$num,$order);
		$isself=$this->uid == $this->mid;
		$this->isself=$isself;
		// 模板变量
		$this->nowpage = $nowPage;
		$this->j = $num * ($nowPage - 1);
		$this->page = $page;
		$this->list = $list;

		$this->setTitle('教研动态');
		$this->display();
	}

	/**
	 * 创建文件通知
	 */
	public function create() {
		$this->setTitle('创建动态');
		$this->display();
	}
	
	/**
	 * 把创建的公告存入数据库
	 */
	public function add() {
		
		// 当前登录的用户
		$user = $this->user;
		
		// 把标题和内容等信息存入$data数组
		$data = array();
		$data['title'] = trim(htmlspecialchars($_POST['title']));
		$data['content'] = safe($_POST['content']);
		$data['attach_ids'] = empty($_POST['attach_ids']) ? '' : $_POST['attach_ids'];
		$data['type'] = 3;
		$data['uid'] = $user['uid'];
		$data['uname'] = $user['uname'];
		
		// 实例化NoticeModel
		$Notice = D('Notice');
		
		// 插入数据库
		$res = $Notice->addNotice($data);
		if($res){
			$Notice->syncToFeed($user['uid'], $res, $data['title'], $data['content'], $data['type']);
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
	 * 通知详细页面
	 */
	public function detail() {
	
		$condition = array();
		$condition['id'] = intval($_GET['id']);
		$condition['isDeleted'] = 0;
		
		// 实例化NoticeModel
		$Notice = D('Notice');
		$detail = $Notice->getNoticeDetail($condition);
	
		// 查询出错
		if(!$detail|| $detail['type'] != 3){
			$this->error("动态已删除!");
		}
		
		$attachIds = D('notice_attach')->where("noticeId = {$detail['id']}")->field(array("attachId","attach_type"))->select();
		$ids = array();

		// 转换成id的一维数组
		foreach ($attachIds as $k=>&$attachId){
			$attachId['attach_id'] = $attachId['attachId'];
		}

		$mid = $this->mid;
		
		// 如果当前登录的用户不是通知发布人
		if($mid != $detail['uid']){
			$data = array();
			$data['viewcount'] = intval($detail['viewcount']) + 1;
			
			// 浏览次数加1
			$res = $Notice->updateNotice($condition['id'],$data);
			
			if($res){
				$detail['viewcount'] = intval($detail['viewcount']) + 1;
			}
		}
		
		// 模板变量
		$this->detail = $detail;
		$this->attachIds = $attachIds;
		
		$this->setTitle('教研动态');
		$this->display();
	}
	
	/**
	 * 编辑页面
	 */
	public function edit() {
		
		$condition = array();
		$condition['id'] = intval($_GET['id']);
		$condition['isDeleted'] = 0;
		
		// 实例化NoticeModel
		$Notice = D('Notice');
		$detail = $Notice->getNoticeDetail($condition);
	
		// 查询出错
		if(!$detail){
			$this->error("动态已删除!");
		}
		
		$attachIds = D('notice_attach')->where("noticeId = {$detail['id']}")->field(array("attachId","attach_type"))->select();
		$ids = array();
		
		// 转换成id的一维数组
		foreach ($attachIds as $k=>&$attachId){
			$attachId['attach_id'] = $attachId['attachId'];
		}
		
		// 把数组变成以逗号做间隔的字符串
		$ids = implode(",",$ids);
	
		// 模板变量
		$this->detail = $detail;
		$this->ids = $ids;
		$this->attachIds = $attachIds;
		
		$this->setTitle('教研动态');
		$this->display();
	}
	
	/**
	 * 把编辑后的信息存入数据库
	 */
	public function alter() {
		
		$id = intval($_POST['id']);
		
		// 把标题和内容等信息存入$data数组
		$data = array();
		$data['title'] = trim(htmlspecialchars($_POST['title']));
		$data['content'] = safe($_POST['content']);
		$data['attach_ids'] = empty($_POST['attach_ids']) ? '' : $_POST['attach_ids'];
		
		// 插入数据库
		$res = D('Notice')->updateNotice($id,$data);
		
		if($res){
			echo 1;
		}else{
			echo 0;
		}
	}

	/**
	 * 删除公告
	 */
	public function delete() {
		$id = intval($_POST['id']);
		
		// 实例化NoticeModel
		$Notice = D('Notice');
		
		// 插入数据库
		$res = $Notice->deleteNotice($id);
		
		if($res){
			echo 1;
		}else{
			echo 0;
		}
	}
}
