<?php
/**
 * 名师工作室成员管理Action
 * @author sjzhao
 *
 */
class MemberAction extends BaseAction{
	public function _initialize() {
		parent::_initialize();

		// 权限判断
		if ($this->level != 3) {
			$this->error("当前用户没有权限");
		}
	}

	/**
	 * 成员管理首页
	 */
	public function index(){
		$this->display();
	}
	
	/**
	 * 添加成员弹出框
	 */
	public function showAdd(){
        $memberList = D('MSGroupMember')->getMemberByGid($this->gid);
        $userIds = array();
        foreach($memberList AS $_member){
            $userIds[] = $_member['uid'];
        }
        $this->assign('userIds', $userIds);
		$this->display();
	}
	
	/**
	 * 添加
	 */
	public function addMember(){
		$userIds = $_POST['userIds'] ? $_POST['userIds'] : '';
		$userIds = array_filter(explode("|", $userIds));
		
		//添加成员处理
		$_r = D("MSGroupMember")->updateMembers($userIds, $this->gid);
		
		$result = new stdClass();
		$result->status = 0;
		if($_r){
			$result->status = 1;
			$result->data = json_encode($userIds);
			$result->msg = "添加成员成功！";
		}
		
		exit(json_encode($result));
	}
	/**
	 * getMemberList 
	 * 
	 * @return void
	 */
	public function getMemberList() {
		$gid = $_POST['gid'];
		if (empty($gid)) {
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return;
		}

		$limit = $_POST['limit'] ? $_POST['limit'] : 10;
		$order = $_POST['order'] ? $_POST['order'] : 'ctime desc';
		$page = $_POST['page'] ? $_POST['page'] : 1;
		$keywords = $_POST['keywords'] ? $_POST['keywords'] : "";
		$j = $limit * ($page-1);
		$total_count = D('MSGroupMember')->getMemberListCount($gid, null, $keywords);
		$member_list = D('MSGroupMember')->getMemberList($gid, null, $keywords,$order,($page-1)*$limit,$limit, true);
		
		//ajax分页
		$page_params = array(
				'total_rows' => $total_count,
				'method'=>'ajax',
				'ajax_func_name' => 'msMember.page',
				'now_page' => $page,
				'list_rows' => $limit,
		);
		$ajax_page = new AjaxPage($page_params);
		$page_info = $ajax_page->show();

		$member_ids = array();
		foreach ($member_list AS $key => $member) {
			if ($member['uid']) {
				$member_ids[] = $member['uid'];
			}
		}
		$member_ids = implode('|', $member_ids);

		$var = array();
		$var['member_ids'] = $member_ids;
		$var['member_list'] = $member_list;
		$var['page_info'] = $page_info;
        $var['j'] = $j;//此变量用于序号
		$content = fetch('list', $var);

		$result = new stdClass();
		$result->status = 1;
		$result->data = $content;

		if ($content) {
			exit(json_encode($result));
		}
	}

	/**
	 * getAnnouncesList 
	 * 
	 * @return void
	 */
	public function deleteMember() {
		$gid = $_POST['gid'];
		if (empty($gid)) {
			echo "{'status':0,'msg':'工作室id不能为空'}";
			return false;
		}

		$uids = $_POST['memberIds'];
		if (empty($uids)) {
			echo "{'status':0,'msg':'成员id不能为空'}";
			return false;
		}

		foreach ($uids as $uid) {
			$result = D('MSGroupMember')->memberDelete($uid, $gid);
		}

		echo json_encode(array('status' => 1, 'msg' => '删除成功'));
		return true;
	}
}
?>
