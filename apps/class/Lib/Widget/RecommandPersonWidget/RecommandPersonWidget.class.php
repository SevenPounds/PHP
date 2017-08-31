<?php
/**
 * 明星教师，家长，学生 
 * @author cheng
 *
 */
class RecommandPersonWidget extends Widget{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data)
	{
		$var['title'] = $data['title']?$data['title']:'明星教师';
		// 设置频道模板
		$template = 'loadPerson';
		$roleid = $data['roleid'];
		switch($roleid)
		{
			// 8是学生
			case '8':
			    $user_ids = model('User')->getStars($roleid);  
				$data = $this->_getUserInfo($user_ids);
				$var['data'] = array_chunk($data, 2,true);
				$var['content_div_id'] = "student_div";
				break;
			// 9是教师
			case '9':
				$user_ids = model('User')->getStars($roleid); 
				$data = $this->_getUserInfo($user_ids);
				$var['data'] = array_chunk($data, 2,true);
				$var['content_div_id'] = "teacher_div";
				break;
			// 10是家长	
			case '10':
				$user_ids = model('User')->getStars($roleid); 
				$data = $this->_getUserInfo($user_ids);
				$var['data'] = array_chunk($data, 2,true);
				$var['content_div_id'] = "parents_div";
				break;
		}
		
		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
	}
	
	/**
	 * 获取用户信息，包括最新微博动态
	 * @param int[] $user_ids
	 */
	public function _getUserInfo($uids){
		if(!$uids){
			return ;
		}
		$users = model("User")->getUserInfoByUids($uids);
		$followState = model("Follow")->getFollowStateByFids($this->mid, $uids);
		$feeds = model("Feed")->getLastFeed($uids);
		
		foreach($users as $k=>$user){
			if(!$user){
				unset($users[$k]);
				continue;
			}
			$users[$k]['followState'] = $followState[$user['uid']];
			$feed = $feeds[$users[$k]['uid']];
			
			if(!empty($feed)){
				$url = U('public/Profile/feed', array('feed_id'=>$feed['feed_id'], 'uid'=>$user['uid']));
				$url = "<span class='green'><a href='".$url."' target='_blank'>去看看>></a></span>";
				$feedContent =preg_replace("/img{data=([^}]*)(}|...)/","...", $feed['title']);
				$feedContent = parse_html($feedContent);
				$users[$k]['lastfeed'] = $feedContent.$url;
			}
			unset($feed);
			unset($user);
		}
		unset($uids);
		return $users;
	}
}
?>