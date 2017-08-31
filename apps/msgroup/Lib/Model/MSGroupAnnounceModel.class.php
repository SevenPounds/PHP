<?php
/**
 * @package msgroup\Lib\Model
 */
class MSGroupAnnounceModel extends Model{
	protected $tableName = 'msgroup_notice';
	protected $fields = array(1=>'id',
			2=>'title',
			3=>'content',
			4=>'ctime',
			5=>'viewcount',
			6=>'gid',
			7=>'uid',
			8=>'uname',
			9=>'isDeleted',
			10=>'mtime',
			11=>'type');
	
	function  _initialize(){
		parent::_initialize();
	}
	
	/**
	 * 批量获取资讯文章类基本属性(不含附件)
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param array $condition
	 */
	public function GetNotices($condition, $limit, $order = 'ctime desc'){
		$map = array('isDeleted'=>0);
		$map = array_merge($map,$condition);
		if(isset($limit)){
			return $this->where($map)->limit($limit)->order($order)->select();
		}
		return $this->where($map)->order($order)->select();
	}
	
	/**
	 * 获取工作室的资讯文章类基本属性(不含附件)(适用于 列表展示 )
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $gid <工作室ID>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @param int $limit <每页显示数量>
	 * @param string $order <排序方式(字符串格式：字段 升降序)，默认desc，升序使用asc>
	 * @param int $page <页数，默认显示首页>
	 * @return array <出现异常返回false，无查询结果返回null，否则返回array>
	 */
	public function GetNoticesByGID($gid, $type, $limit, $order = 'ctime desc', $page = 1){
		$map = array('type'=>$type, 'gid'=>$gid, 'isDeleted'=>0);
		return $this->where($map)->order($order)->limit($limit)->page($page)->select();
	}
	
	/**
	 * 获取用户发布的资讯文章类基本属性(不含附件)
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $uid <用户ID>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @param int $limit <每页显示数量>
	 * @param string $order <排序方式(字符串格式：字段 升降序)，默认desc，升序使用asc>
	 * @param int $page <页数，默认显示首页>
	 * @return array <出现异常返回false，无查询结果返回null，否则返回array>
	 */
	public function GetNoticesByUID($uid, $type, $limit, $order = 'ctime desc', $page = 1){
		$map = array('type'=>$type, 'uid'=>$uid, 'isDeleted'=>0);
		return $this->where($map)->order($order)->limit($limit)->page($page)->select();
	}
	
	/**
	 * 获取工作室的资讯文章类数量(适用于 分页)
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $gid <工作室ID>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @return int <某类型文章资讯的数量>
	 */
	public function GetCountByGID($gid, $type){
		$map = array('type'=>$type, 'gid'=>$gid, 'isDeleted'=>0);
		return $this->where($map)->count();
	}
	
	/**
	 * 获取用户发布的资讯文章类数量(适用于 分页)
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $uid <用户ID>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @return int <某类型文章资讯的数量>
	 */
	public function GetCountByUID($uid, $type){
		$map = array('type'=>$type, 'gid'=>$uid, 'isDeleted'=>0);
		return $this->where($map)->count();
	}
	
	/**
	 * 获取资讯文章内容(含附件)
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $id <资讯文章ID>
	 * @return array <返回资讯文章的全部内容 array("announce"=>array(), "attachments"=>array())>
	 */
	public function GetNotice($id){
		$map = array('id'=>$id, 'isDeleted'=>0);
		$announce = array();
		$content = $this->where($map)->find();
		$announce['announce'] = $content;
		$attachments = D('MSGroupAnnounceAttach')->GetAttachs($id);
		$announce['attachments'] = $attachments;
		return $announce;
	}
	
	/**
	 * 发布资讯文章
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $uid <用户ID>
	 * @param int $gid <工作室ID>
	 * @param int $uname <用户名>
	 * @param string $title <标题>
	 * @param string $content <内容>
	 * @param array $attachments <附件列表 array($attach_id=>$attach_type)>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @return int <返回刚刚发布的资讯文章的主键值ID>
	 */
	public function Publish($uid, $gid, $uname, $title, $content, $attachments, $type = 0){
		$notice = array();
		$notice['title'] = $title;
		$notice['content'] = $content;
		$timestamp = time();
		$notice['ctime'] = $timestamp;
		$notice['mtime'] = $timestamp;
		$notice['uid'] = $uid;
		$notice['gid'] = $gid;
		$notice['uname'] = $uname;
		$notice['isDeleted'] = 0;
		$notice['type'] = $type;
		$noticeID = $this->add($notice);
		if($noticeID){
			$this->_syncToFeed($uid, $noticeID, $title, $content, $type, $gid);
		}
		if($noticeID && isset($attachments)){
			D("MSGroupAnnounceAttach")->AddAttachs($noticeID, $attachments);
		}
		return $noticeID;
	}
	
	/**
	 * 编辑资讯文章
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $id <资讯文章ID>
	 * @param string <$title 标题>
	 * @param string <$content 内容>
	 * @param array <$attachments 新的附件列表>
	 * @return <返回受影响的行数，出错返回false>
	 */
	public function Edit($id, $title, $content, $attachments){
		$map = array('id'=>$id, 'isDeleted'=>0);
		$data = array('title'=>$title, 'content'=>$content, 'mtime'=>time());
		//获取删除项和新增项
		$oriattachments = D('MSGroupAnnounceAttach')->GetAttachs($id);
		$arr = array();
		foreach($oriattachments as $val){
			$arr = $arr + array($val['attach_id']=>$val['attach_type']);
		}
		$intersect = array_intersect_assoc($attachments, $arr);
		$delids = array_diff_assoc($arr, $intersect);
		$addids = array_diff_assoc($attachments, $intersect);
		D("MSGroupAnnounceAttach")->AddAttachs($id, $addids);
		D("MSGroupAnnounceAttach")->deleteAttachs($delids);
		return $this->where($map)->data($data)->save();
	}
	
	/**
	 * 浏览量+1
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $id <资讯文章ID>
	 * @return <返回受影响的行数，出错返回false>
	 */
	public function AddViewCount($id){
		$map = array('id'=>$id, 'isDeleted'=>0);
		return $this->where($map)->setInc('viewcount');
	}
	
	/**
	 * 删除资讯文章
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param int $id <资讯文章ID>
	 * @return <返回受影响的行数，出错返回false>
	 */
	public function Delete($id){
		$map = array('id'=>$id, 'isDeleted'=>0);
		$data = array('isDeleted'=>1);
		//删除所有附件
		$attachments = D('MSGroupAnnounceAttach')->GetAttachs($id);
		$arr = array();
		foreach($attachments as $val){
			$arr = $arr + array($val['attach_id']=>$val['attach_type']);
		}
		D("MSGroupAnnounceAttach")->deleteAttachs($arr);
		return $this->where($map)->setField('isDeleted', 1);
	}
	
	/**
	 * 批量删除资讯文章
	 * @api
	 * @author zhaoliang <zhaoliang@iflytek.com>
	 * @param array $ids 
	 * @return boolean <批量删除是否成功>
	 */
	public function BatchDelete($ids){
		$suc = true;
		foreach($ids as $id){
			$suc = $suc && $this->Delete($id);
		}
		return $suc;
	}
	/**
	 * 发表Announce时发表动态
	 * @param int $uid <用户id>
	 * @param int $aid <文章的id>
	 * @param int $title <标题>
	 * @param int $content <内容>
	 * @param int $type <类型 1：通知公告，2：工作动态，3：研究成果，4：教学论文，5：教学日志>
	 * @param int $gid <名师工作室ID>
	 * 
	 * @author yxxing
	 */
	private function _syncToFeed($uid, $aid, $title, $content, $type, $gid) {
		$d = array();
		$d['content'] = '';
		$d['gid'] = $gid;
		$noticeApp = "通知公告";
		$d['source_url'] = U('msgroup/Index/announceDetail',array('announce_id'=>$aid,'gid'=>$gid));;
		switch($type){
			case 1:
				$noticeApp = "通知公告";
				break;
			case 2:
				$noticeApp = "工作动态";
				break;
			case 3:
				$noticeApp = "研究成果";
				break;
			case 4:
				$noticeApp = "教学论文";
				break;
			case 5:
				$noticeApp = "教学日志";
				break;
		}
		$d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 发表了'.$noticeApp.'【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
		$feed = model('Feed')->put($uid, 'msgroup', "msgroup", $d);
		return $feed['feed_id'];
	}
	
}
?>