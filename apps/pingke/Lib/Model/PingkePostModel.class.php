<?php
/**
 * 参与评课的回复记录model
 * @package pingke\Lib\Model
 * @author zqxiang@iflytek.com
 * @version 创建时间：2013-12-9 下午2:53:43
 */

class PingkePostModel extends Model {
	protected $tableName = 'pingke_post';
	
	function  _initialize() {
		parent::_initialize();
	}
	
	/**
	 * 发表回复
	 * 
	 * @param array $params	发表回复时传的相关数据	(支持字段：array('pingke_id'=>, 'uid'=>, 'content'=>, 'attach_id'=>))
	 * 
	 * @return mixed
	 */
	function createPost($params) {
		$pingke_id = intval($params['pingke_id']);
		$uid = intval($params['uid']);
		
		// 插入回复内容
		$data = array();
		$data['pingke_id'] = $pingke_id;
		$data['uid'] = $uid;
		$data['content'] = $params['content'];
        $data['content_origin'] = $params['content_origin'];
		$data['record_id'] = $params['record_id'];
		$data['ctime'] = time();
		$add = M($this->tableName)->add($data);
		
		if ($add) {
			// 更新评论总数(pingke.discuss_count)
			$updateTotalCount = M('pingke')->where(array('id'=>$pingke_id))->save(array('discuss_count' => array('exp', 'discuss_count+1')));
			
			// 更新用户的评论次数(pingke_member.discuss_count)
			M('pingke_member')->where(array('pingke_id'=>$pingke_id, 'uid'=>$uid))->save(array('discuss_count' => array('exp', 'discuss_count+1')));
			//评课评论系统消息发送
			$pingke =M('pingke')->where(array('id'=>$pingke_id))->find();
			if($data['uid']!=$pingke['uid']){
				addPingkeMessage($data['uid'],$pingke['uid'],$pingke['title'],$data['content'],$pingke_id,'pingke_comment');
			}
			/*----------系统消息END---------*/
			if ($updateTotalCount) {
				// 添加附件记录
				if (!empty($params['attach_id'])) {
					foreach ($params['attach_id'] as $attachId) {
						M('pingke_attach')->add(array('pingke_id'=>$pingke_id, 'post_id' => $add, 'attach_id' => intval($attachId)));
					}
				}
				
				return $add;
			}
		}
		
		return false;
	}
	
	/**
	 * 获取当前评课回复数据的总数
	 *
	 * @param int $pingke_id 评课id
	 * @param int $uid 用户uid
	 *
	 * @return int
	 */
	function getPostListCount($pingke_id, $uid = '') {
		$param = array('pingke_id'=>intval($pingke_id));
		if (!empty($uid)) $param['uid'] = intval($uid);
		return M($this->tableName)->where($param)->count();
	}
	
	/**
	 * 获取当前评课回复数据
	 * 
	 * @param int $pingke_id 评课id
	 * @param int $page 页数
	 * @param int $limit
	 * @param string $order 排序默认最新的在前
	 * @param int $uid 用户uid
	 * 
	 * @return array
	 */
	function getPostList($pingke_id, $page = 1, $limit = 20, $order = 'id desc', $uid = '') {
		$param = array('pingke_id'=>intval($pingke_id));
		if (!empty($uid)) $param['uid'] = intval($uid);
		$result = M($this->tableName)->where($param)->order($order)->page("$page, $limit")->select();
		$tmp_user = array();
		foreach ($result as &$r){
			if (empty($tmp_user[$r['uid']])) {
				$tmp_user[$r['uid']] = model('User')->getUserInfo($r['uid']);
			}
			$r['user'] = $tmp_user[$r['uid']];
			$r['attach'] = M('pingke_attach')->where(array('pingke_id'=>intval($pingke_id), 'post_id'=>$r['id']))->select();
		}
	    return $result;
	}
	
	/**
	 * 获取用户的发言总数
	 * @param int $pingke_id
	 * @param int $uid
	 *
	 * @return int
	 */
	function getPostCountByUser($pingke_id, $uid) {
		return M('pingke_member')->where(array('pingke_id'=>intval($pingke_id), 'uid'=>intval($uid)))->getField('discuss_count');
	}
	
	/**
	 * 获取用户的发言
	 * @param int $pingke_id
	 * @param int $uid
	 * @param int $page
	 * @param int $limit
	 * @param string $order
	 * 
	 * @return array
	 */
	function getPostByUser($pingke_id, $uid, $page = 1, $limit = 20, $order = 'id desc') {
		$data = array();
		$user = model('User')->getUserInfo($uid);
		if (!empty($user)) {
			$data['user'] = $user;
			$data['post'] = M("$this->tableName p")->join($this->tablePrefix.'pingke_attach a on p.id=a.post_id and p.pingke_id = a.pingke_id')->field('p.*, a.attach_id')->where(array('p.pingke_id'=>intval($pingke_id), 'p.uid'=>intval($uid)))->order($order)->page("$page, $limit")->select();
		}
		
		return $data;
	}

	/**
	 * 删除用户发言
	 * yangli4
	 * @param int $post_id
	 * @param int $uid 用户uid。前端删除自己评论时在调用方法中获取， 禁止在html/js中传递此值
	 *
	 * @return int
	 */
	function deletePostByPostid($post_id, $uid = '') {
		if(isset($post_id)) {
			$map = array('id' => $post_id);
			if (!empty($uid)) $map['uid'] = intval($uid); //删除自己的评论时做验证  add by zqxiang@iflytek.com 2014-01-14
			$pingke_id = $this->where($map)->getField('pingke_id');
			if ($pingke_id) {
				$del = $this->where($map)->delete();
				if ($del) {
					M('pingke')->where(array('id'=>$pingke_id, 'discuss_count' => array('gt', 0)))->save(array('discuss_count' => array('exp', 'discuss_count-1')));//add by zqxiang@iflytek.com 2014-01-14
				}
				return $del;
			}
		}
		
		return -1;
	}
	
	/**
	 * 获取精品网上评课的列表
	 * @param array $condition 查询条件
	 * @param int $num 查询数量
	 * @return array 查询结果集
	 * @author ylzhao
	 */
	public function getExcellentPostList($condition, $num, $order = "agree_count DESC,ctime DESC"){
		$condition['agree_count'] = array('gt',0);
		$result = $this->where($condition)->order($order)->limit($num)->select();
		foreach ($result as &$r){
			$r['user_info'] = model ( 'User' )->getUserInfoForSearch ( $r ['uid'],'uid,uname');
			$r['content'] = parse_html($r['content']);
		}
		return $result;
	}
}
