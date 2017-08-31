<?php
/**
 * 网上评课model
 * @package pingke\Lib\Model
 * @author zqxiang@iflytek.com
 * @version 创建时间：2013-12-9 下午2:53:43
 */

class PingkeModel extends Model {
	protected $tableName = 'pingke';
	protected $fields = array(0=>'id',1=>'gid',2=>'uid',3=>'title',4=>'description',5=>'teacher',6=>'createtime',7=>'modifiedtime',8=>'closedtime',9=>'status',10=>'public_status',11=>'discuss_count',12=>'member_count',13=>'video_id',
		14=>'context_id',15=>'summary_attachid',16=>'summary_name',17=>'summary_path',18=>'province',19=>'city',20=>'county',21=>'subject',
		22=>'is_del',23=>'to_space',24=>'accessType',25=>'isHot',26=>'title_origin',27=>'description_origin',28=>'teacher_origin');

	function  _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 评课检索结果总数
	 * @param array $params  支持字段array('province'=>, 'city'=>, 'county'=>, 'subject'=>)
	 * @param String $searchKeywords 检索关键字
	 * @param int $status 课题进行状态  0：已完成 1：进行中
	 * 
	 * @return int
	 */
	function searchPingkeCount($params, $searchKeywords = '', $status = -1) {
		$map = array();
		if(isset($params['uid'])){
			$map['u.uid']= $params['uid'];
		}
		if(isset($params['isHot'])){
			$map['p.isHot'] =$params['isHot'];
		}
		$map['p.`is_del`'] = isset($params['is_del'])?$params['is_del']:0;
		if ($status == 1 || $status == 0) $map['p.`status`'] = $status;
		if (!empty($searchKeywords)) $map['p.`title`'] = array('like', '%'.trim($searchKeywords).'%');

		$join =' INNER JOIN '.$this->tablePrefix."user u on p.`uid`=u.`uid` ";
		$tables = $this->tablePrefix.$this->tableName.' p ';
		return $this->table($tables)->where($map)->join($join)->count();	
	}
	
	/**
	 * 评课检索结果
	 * @param array $params  支持字段array('province'=>, 'city'=>, 'county'=>, 'subject'=>)
	 * @param String $searchKeywords 检索关键字
	 * @param int $status 课题进行状态  0：已完成 1：进行中
	 * @param int $page
	 * @param int $limit
	 * @param string $order
	 * 
	 * @return array
	 */
	function searchPingke($params, $searchKeywords = '', $status = -1, $page = 1, $limit = 20, $order = 'p.`id` desc') {
		$map = array();
		if(isset($params['uid'])){
			$map['u.uid']= $params['uid'];
		}
		if(isset($params['isHot'])){
			$map['p.isHot'] =$params['isHot'];
		}
		$map['p.`is_del`'] = isset($params['is_del'])?$params['is_del']:0;
		if ($status == 1 || $status == 0) $map['p.`status`'] = $status;
		if (!empty($searchKeywords)) $map['p.`title`'] = array('like', '%'.trim($searchKeywords).'%');

		$join =' INNER JOIN '.$this->tablePrefix."user u on p.`uid`=u.`uid` ";
		$tables = $this->tablePrefix.$this->tableName.' p ';
	
		return $this->table($tables)->join($join)->field('p.*, u.`uname`')->where($map)->order($order)->page("$page, $limit")->select();
	}
	
	/**
	 * 查询我发起/参加的课题总数
	 * @param int $uid	用户uid
	 * @param int $type	类别：1：发起的  2：参与的
	 * @param String $searchKeywords 检索关键字
	 * @param int $status 课题进行状态  0：已完成 1：进行中
	 *
	 * @return int
	 */
	function getMyPingkeListCount($uid, $type = 1, $searchKeywords = '', $status = -1) {
		$count = 0;
		if ($type == 1) {
			$map = array('uid'=>intval($uid));
			$map['is_del'] = 0;//过滤已删除评课
			if (!empty($searchKeywords)) $map['title'] = array('like', '%'.$searchKeywords.'%');
			if ($status != -1) $map['status'] = intval($status);
			$count = M($this->tableName)->where($map)->count();
		} else {
			$map = array('m.uid'=>intval($uid));
			$map['p.`is_del`'] = 0;//过滤已删除评课
			if (!empty($searchKeywords)) $map['p.title'] = array('like', '%'.$searchKeywords.'%');
			if ($status != -1) $map['p.status'] = intval($status);
			$inner = 'inner join ' . $this->tablePrefix . $this->tableName . ' p on m.pingke_id = p.id';
			$count = M('pingke_member m')->join($inner)->where($map)->field('p.*')->count();
		}
	
		return $count;
	}
	
	/**
	 * 查询我发起/参加的课题
	 * @param int $uid	用户uid
	 * @param int $type	类别：1：发起的  2：参与的
	 * @param int $page
	 * @param int $limit
	 * @param string $order
	 * @param String $searchKeywords 检索关键字
	 * @param int $status 课题进行状态  0：已完成 1：进行中
	 * 
	 * @return array
	 */
	function getMyPingkeList($uid, $type = 1, $page = 1, $limit = 20, $order = 'id desc', $searchKeywords = '', $status = -1) {
		$dataList = array();
		if ($type == 1) {
			$map = array('uid'=>intval($uid));
			$map['is_del'] = 0;//过滤已删除评课
			if (!empty($searchKeywords)) $map['title'] = array('like', '%'.$searchKeywords.'%');
			if ($status != -1) $map['status'] = intval($status);
			$dataList = M($this->tableName)->where($map)->order($order)->page("$page, $limit")->select();
		} else {
			$map = array('m.uid'=>intval($uid));
			$map['p.`is_del`'] = 0;//过滤已删除评课
			if (!empty($searchKeywords)) $map['p.title'] = array('like', '%'.$searchKeywords.'%');
			if ($status != -1) $map['p.status'] = intval($status);
			$inner = 'inner join ' . $this->tablePrefix . $this->tableName . ' p on m.pingke_id = p.id';
			$dataList = M('pingke_member m')->join($inner)->where($map)->field('p.*, m.is_new')->group('p.id')->order($order)->page("$page, $limit")->select();
			foreach ($dataList as &$r){
				$r['user'] = model('User')->getUserInfo($r['uid']);
			}
		}
		
		return $dataList;
	}
	
	/**
	 * 获取网上评课详细信息
	 * @param int $id 评课id
	 * 
	 * @return array
	 */
	function getPingKeDetails($id) {
		return M($this->tableName)->find($id);
	}
	
	/**
	 * 获取评课发起人信息（获取所属单位、研讨次数、头像等）
	 * @param int $pingke_id 评课id
	 *
	 * @return array
	 */
	function getCreatorInfo($pingke_id) {
		$result = array();
		$id = intval($pingke_id);
	
		//获取评课创建者uid、评论次数
		$pingke = M("$this->tableName p")->join($this->tablePrefix.'pingke_post a on p.id=a.pingke_id and p.uid = a.uid')->field('p.uid, count(a.id) as count')->where(array('p.id'=>$id))->find();
		if (empty($pingke)) return $result;	//评课不存在
	
		//获取创建者信息
		$user = model('User')->getUserInfo($pingke['uid']);
		if (empty($user)) return $result;
		$user['post_count'] = $pingke['count'];	//研讨次数
	
		$info = model('CyUser')->getCyUserInfo($user['login']);
		if (!empty($info)) {
			$org = current(current($info['orglist']));
			$user['org_name'] = $org['name'];
		}
	
		return $user;
	}
	
	/**
	 * 创建网上评课
	 *
	 * @param array $params 网上评课的课题基本信息【必传参数为：array('uid'=>, 'title'=>, 'description'=>, 'teacher'=>, 'video_id'=>, 'province'=>, 'city'=>, 'county'=>, 'subject'=>)】
	 * @param array $members 成员uid列表(一维数组)
	 * @param array $gids 发布到名师工作室的id数组
	 *
	 * @return int 网上评课id
	 */
	function createPingke($params, $members, $gids) {
		$members = array_unique($members);
	
		// 保存网上评课基本信息
		$map = array();
		$map['uid'] = intval($params['uid']);
		$map['title'] = t($params['title']);
		$map['description'] = t($params['description']);
        $map['title_origin'] = t($params['title_origin']);
        $map['description_origin'] = t($params['description_origin']);
		$map['teacher'] = t($params['teacher']);
        $map['teacher_origin'] = t($params['teacher_origin']);
		$map['video_id'] = $params['video_id'];
		$map['context_id'] = $params['context_id'];
		$map['province'] = $params['province'];
		$map['city'] = $params['city'];
		$map['county'] = $params['county'];
		$map['subject'] = $params['subject'];
        $map['to_space'] = $params['to_space'];
        $map['accessType'] = $params['accessType'];
		$map['createtime'] = time();
		$map['status'] = 1;
		$map['discuss_count'] = 0;
		$map['member_count'] = count($members);
		$insertId = M($this->tableName)->add($map);
	
		//	保存成员信息
		if ($insertId) {
			//add by yxxing 产生评课动态及向被邀请者发送消息
			$viewUrl = U("pingke/Index/show",array("id"=>$insertId));

			//同步到名师工作室
			if(isset($gids)){
				D('MSGroupTeachingApp','msgroup')->addTeachingAppInfo($gids, $insertId, 'pingke');
			}
			$this->_syncToFeed($map['uid'], $viewUrl, $map['title'], $map['description'], $gids , $map['to_space']);
            //添加用户的常用用户
            D('UserFavorite')->inviteUser($map['uid'], $members, 'pingke');
			addPingkeMessage($map['uid'],$members,'',$map['title'],$insertId,'pingke');
			foreach ($members as $uid) {
				if (!empty($uid)) {
					M('pingke_member')->add(array('pingke_id'=>$insertId, 'uid'=>intval($uid)));
				}
			}
			return $insertId;
		}
	
		return 0;
	}
	
	/**
	 * 更新评课信息
	 * 仅支持以下字段更新：评课id, 评课创建人uid, 评课标题、评课介绍、授课老师、视频文件、课题进行状态、评课结果是否公开、评课总结文件附件id、评课总结文件名称、评课总结文件路径
	 * 
	 * @param array $params 【必传参数为：array('id'=>,'uid'=>, 'title'=>, 'description'=>, 'teacher'=>, 'video_id'=>, 'status'=>, 'public_status'=>, 'summary_attachid'=>, 'summary_name'=>, 'summary_path'=>)】
	 * @param array $members 成员uid列表(一维数组)
	 * @param array $gids 发布到名师工作室的id数组
	 * @param boolean $is_finish 是否结束当前评课，默认为否
	 * 
	 * @return boolean
	 */
	function updatePingke($params, $members, $gids, $is_finish = false) {
		$id = intval($params['id']);
		$uid = intval($params['uid']);
		
		// 验证
		$pingke = M($this->tableName)->find($id);
		if (empty($pingke)) return false;	//评课不存在
		if ($pingke['status'] == '0') return false;	//已结束
		if ($pingke['uid'] != $uid) return false;	//当前用户不是创建者
		
		//更新操作
		$data = array();
		if (!empty($params['title'])) $data['title'] = $params['title'];
		if (!empty($params['description'])) $data['description'] = $params['description'];
        if (!empty($params['title_origin'])) $data['title_origin'] = $params['title_origin'];
        if (!empty($params['description_origin'])) $data['description_origin'] = $params['description_origin'];
		if (!empty($params['teacher'])) $data['teacher'] = $params['teacher'];
        if (!empty($params['teacher_origin'])) $data['teacher_origin'] = $params['teacher_origin'];
		if (!empty($params['video_id'])) $data['video_id'] = $params['video_id'];
		if (isset($params['status'])) $data['status'] = $params['status'];
		if (isset($params['public_status'])) $data['public_status'] = $params['public_status'];
        if (isset($params['to_space'])) $data['to_space'] = $params['to_space'];
		if (!empty($params['summary_attachid'])) $data['summary_attachid'] = intval($params['summary_attachid']);
		if (!empty($params['summary_name'])) $data['summary_name'] = $params['summary_name'];
		if (!empty($params['summary_path'])) $data['summary_path'] = $params['summary_path'];
		isset($params['accessType']) && $data['accessType'] = $params['accessType'];
		
		if (!empty($data)) {
			$data['modifiedtime'] = time();
			if ($data['status'] == '0') $data['closedtime'] = time();
			
 			$update = M($this->tableName)->where(array('id' => $id))->save($data);
			
 			if ($is_finish) return true;

			//同步到名师工作室
 			if($update){
				D('MSGroupTeachingApp','msgroup')->updateTeachingAppInfo($gids, $id, 'pingke');
 			}
			if ($update && $params['accessType']==0) return true;
			
			if ($update) { //成员信息更新仅限于指定成员类型
				//成员信息更新
				$_oldMemberList = M('pingke_member')->where(array('pingke_id'=>$id))->Field("uid")->select();
				$oldMemberList = array();
				foreach($_oldMemberList AS $m){
					$oldMemberList[] = $m['uid'];
				}				
				$toDel = array_diff($oldMemberList, $members);
				$toAdd = array_diff($members, $oldMemberList);
                //添加用户的常用用户
                D('UserFavorite')->inviteUser($uid, $toAdd, 'pingke');
				addPingkeMessage($uid, $toAdd, '',$params['title'], $id);
				addPingkeMessage($uid, $toDel,'',$params['title'], $id, "pingke_del");
				foreach($toDel AS $_toDelUid){
					if (!empty($_toDelUid)) M('pingke_member')->where(array('pingke_id'=>$id,'uid'=>$_toDelUid))->delete();
				}
				foreach ($toAdd as $muid) {
					if (!empty($muid)) M('pingke_member')->add(array('pingke_id'=>$id, 'uid'=>intval($muid)));
				}
				M($this->tableName)->where(array('id'=>$id))->save(array('member_count'=>count($members)));
				
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 查询教研员用户
	 * @param array $params 查询条件(仅支持 array('eduorg_id'=>, 'subject'=>) 需要添加条件请阅读retrieveInstructor接口文档
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchInstructorUser($params, $page = 1, $limit = 20) {
		$user = D('CyUser');
		return $user->searchInstructorUser($params, $page, $limit);
	}
	
	/**
	 * 查询教师用户
	 * @param array $params 查询条件(仅支持 array('city_id'=>, 'district_id'=>, 'school_id'=>, 'subject'=>) 需要添加条件请阅读retrieveTeacher接口文档
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchTeacherUser($params, $page = 1, $limit = 20) {
		$user = D('CyUser');
		return $user->searchTeacherUser($params, $page, $limit);
	}
	
	/**
	 * 查询教研员/教师用户
	 * @param string $user_name
	 * @param int $page 页数(1开始)
	 * @param int $limit
	 *
	 * @return mixed (array(object))
	 */
	public function searchInstructorAndTeacher($user_name, $page = 1, $limit = 20) {
		$user = D('CyUser');
		return $user->searchInstructorAndTeacher($user_name, $page, $limit);
	}

	/**
	 * 删除评课（支持删除多个）
	 * @param int/array $pingkeids 评课id
	 **/
	public function deletePingke($pingkeids){
		if(is_array($pingkeids)){
			$this->where(array('id'=>array('in', $pingkeids)))->save(array('is_del' => 1));
			return 1;
		}
		else{
			return $this->where(array('id' => $pingkeids))->save(array('is_del' => 1));
		}
	}
	
	/**
	 * 根据用户uid和评课id删除评课
	 * @param unknown_type $uid
	 * @param unknown_type $pingkeids
	 */
	public function deletePingkeByUid($uid, $pingkeids){
		$map = array('id'=>$pingkeids, 'uid'=>$uid);
		$ret = $this->where($map)->setField('is_del',1);
		return $ret;
	}
	
	/**
	 * 获取热门评课
	 * @param int $limit
	 */
	public function getHotPingke($limit){
		$map = array();
		$map['p.`is_del`'] = 0;
		$order = "p.`discuss_count` DESC";
		$tables = $this->tablePrefix.$this->tableName." p";
		$join =' INNER JOIN '.$this->tablePrefix."user u on p.`uid`=u.`uid` ";
		return $this->table($tables)->field('p.`id`,p.`uid`,p.`title`,u.`uname`')->where($map)->join($join)->order($order)->limit($limit)->select();
	}
	/**
	 * 根据isHot,评论数，成员数，时间排序最火主题讨论
	 * return array
	 */
	public function geAppHotPingke(){
		//获取评课列表
		$hot_list =$this->field('id,uid,description,title,discuss_count')->where(array('isHot'=>1,'is_del'=>0,'accessType'=>0))->order('discuss_count desc, member_count desc, createtime desc ')->limit("0,5")->findAll();
		$result =array();
		foreach ($hot_list as $key){
			$map=array();
			$map['pingke_id']=$key['id'];
			//获取评课评论信息
			$comment =D('PingkePost','pingke')->field('uid,content,agree_count')->where($map)->order('agree_count desc, comment_count desc, ctime desc')->find();
			//评课用户信息
			$blogUser =D('User')->getUserInfo($key['uid']);
			if(!empty($comment)){
				//评课评论用户信息
				$commentUser =D('User')->getUserInfo($comment['uid']);
			}
			$blogInfo['hotUser']=$blogUser;
			$blogInfo['commentUser']=$commentUser;
			$blogInfo['id']=$key['id'];
			$blogInfo['content'] =htmlentities($key['description']);
			$blogInfo['title']=htmlentities($key['title']);
			$blogInfo['discuss_count']=$key['discuss_count'];
			$blogInfo['comment_content']=parse_html(htmlentities($comment['content']));
			$blogInfo['comment_count']=$comment['agree_count'];
			$blogInfo['source_url']=U('pingke/Index/show',array('id'=>$key['id']));
			unset($commentUser);
			unset($comment);
			array_push($result,$blogInfo);
		}
		return $result;
	}
	/**
	 * 根据条件获取网上评课列表
	 * 注：供后台使用
	 * @author ylzhao
	 */
	public function getPingkes($conditions, $page=1, $limit=10, $order){
		$map = array();
		if(isset($conditions['isHot'])){
			$map['isHot'] = intval($conditions['isHot']);
		}
		if(isset($conditions['accessType'])){
			$map['accessType'] = $conditions['accessType'];
		}
		if(isset($conditions['title'])){
			$map['title'] = array('like', "%".$conditions['title']."%");
		}
		$fields = 'p.*,u.`uname`';
		$tables = $this->tablePrefix.$this->tableName.' p ';
		$join = 'LEFT JOIN '.$this->tablePrefix.'user u ON p.`uid` = u.`uid`';
		$order = empty($order) ? "createtime DESC" : $order;
		$map['p.`is_del`'] = 0;
		$start = ($page - 1) * $limit;
		$result['data'] = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->limit("$start, $limit")->select();
		$result['count'] = $total = $this->table($tables)->where($map)->join($join)->count();
		return $result;
	}
	/**
	 * doIsHot
	 * 设置推荐
	 * @param array $map
	 * @param string $act
	 * @author ylzhao
	 */
	public function doIsHot( $map,$act ) {
		if( empty($map) ) {
			throw new ThinkException( "不允许空条件操作数据库" );
		}
		switch( $act ) {
			case "recommend":   //推荐
				$result = $this->where($map)->setField("isHot",1);
				break;
			case "togreat":   //精华
				$result = $this->where($map)->setField("isHot",2);
				break;
			case "cancel":   //取消
				$result = $this->where($map)->setField("isHot",0);
				break;
		}
		return $result;
	}
	/**
	 * 发表网上评课动态
	 * @param int $uid 用户id
	 * @param string $viewUrl 预览地址
	 * @param int $title 标题
	 * @param int $content 内容
	 * @param array $gids 名师工作室ID
     * @param int $toSpace 是否同步到我的工作室
	 * 
	 * @author yxxing
	 */
	private function _syncToFeed($uid, $viewUrl, $title, $content, $gids, $toSpace) {
		$d = array();
		$d['content'] = '';
		$d['source_url'] = $viewUrl;
        $d['body'] = '我发起了网上评课【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
        $feed = model('Feed')->put($uid, 'pingke', "pingke", $d);
        if(!empty($gids)){
			foreach ($gids as $gid) {
				if(!empty($gid)){
					$d['gid'] = $gid;
					$d['body'] = "@" . $GLOBALS['ts']['user']['uname'] . ' 发起了网上评课【'.getShort($title,20).'】'.getShort($content,30).'&nbsp;';
					$feed = model('Feed')->put($uid, 'msgroup', "msgroup", $d);
				}
			}
		}
	}
}