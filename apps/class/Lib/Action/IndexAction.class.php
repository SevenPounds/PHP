<?php
/**
 * 频道首页控制器
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class IndexAction extends Action{

	public function index(){
		U('class/Index/main','',true);	
	}
	
	public function main(){
		//获取热点推荐
		$orderedids= D('Channel','channel')->getOrderedFeedId(5);
		$wonderfeed=model('Feed')->getFeeds($orderedids);
		foreach($wonderfeed as &$value){
			$value['feed_data']=unserialize($value['feed_data']);
			if(!empty($value['feed_data']['feed_title'])){
				$value['feed_data']['feed_title'] = parse_html(mStr($value['feed_data']['feed_title'], "20", "utf-8", true));
			} else{
				$value['feed_data']['content'] = parse_html(mStr($value['feed_data']['content'], "20", "utf-8", true));
			}
		}
		$this->assign("hotrecommend",$wonderfeed);
		
		//获取活跃家长
		$activeParents = m('User')->getActivePersons(0,9,UserRoleTypeModel::TEACHER,'follower_count');
		$activeStudents = m('User')->getActivePersons(0,9,UserRoleTypeModel::STUDENT,'follower_count');
		
		
		//获取最新动态
		$lastFeeds = m('feed')->getLastFeeds();
		$uids =getSubByKey($lastFeeds,'uid');
		$usersInfo = model('User')->getUserInfoByUids($uids);

		
		//将url前的文字截取，然后转换为html方式展现
		foreach($lastFeeds AS &$feed){
			$url = U('public/Profile/feed', array('feed_id'=>$feed['feed_id'], 'uid'=>$feed['uid']));
			$url = "<span class='green'><a href='".$url."' target='_blank'>去看看>></a></span>";
			$feed['feed_content'] = str_replace('&nbsp;', " ", $feed['feed_content']);
			$feed['feed_content'] = str_replace('[SITE_URL]', SITE_URL, htmlspecialchars_decode($feed['feed_content']));
			$feed['feed_content'] = str_replace('[PREVIEW_SITE_URL]', PREVIEW_SITE_URL, htmlspecialchars_decode($feed['feed_content']));
			$feed_content = $feed['feed_content'];
			$str_len = mb_strlen($feed_content, "UTF-8");
			$position = mb_strpos($feed_content, "http", null, "UTF-8");
			if($position){
				$feed_txt_content = mb_substr($feed_content, 0, $position, "UTF-8");
				$feed_url_content = mb_substr($feed_content, $position, $str_len - $position, "UTF-8");
			} else{
				$feed_txt_content = $feed_content;
			}
			$feed['feed_content'] = parse_html(replaceUrl(mStr($feed_txt_content, 30, 'utf-8', true).$feed_url_content));
			$feed['feed_content'].=" ".$url;
			$feed['feed_content'] = $feed['feed_content'];
			$feed['publish_time'] = date('Y-m-d H:i', $feed['publish_time']);
			$feed['user_space_url'] = $usersInfo[$feed['uid']]['space_url'];
			$feed['avatar_small'] = $usersInfo[$feed['uid']]['avatar_small'];
		}
		//活跃教师
		$this->activeTeachers = M('User')->getActivePersons(0,18,UserRoleTypeModel::RESAERCHER,'visitor_count');
		$this->activeInstructors = M('User')->getActivePersons(0,18,UserRoleTypeModel::TEACHER,'visitor_count');
	
		$this->assign("lastFeeds", $lastFeeds);
		$this->assign("activeParents", $activeParents);
		$this->assign("activeStudents", $activeStudents);
		$this->display();
	
	}
	/**
	 * 班级首页
	 */
	public function class_square(){
		$this->display();
	}
	
	public function school_garden(){
		$this->citys = Model("CyArea")->listAreaById(1123,'city',0,100);
		$this->province = 1123;//安徽省
		$this->display();
	}
	
	/**
	 * 加入组织 
	 * @param int $type 类型 
	 */
	private  function beeninorgs($type){
		switch($type){
			case 1:
				$template = '_beeninschool';
				$schools = D('CySchool')->get_orglist(1,'follower_count','DESC');
				if(empty($schools)){
					$schools = D('CySchool')->list_school(1,4);
				}
				$fids= getSubByKey($schools,'id');
				
				/***关注状态**/
				$orgnization_follow_state =  model('Follow')->getFollowStateByFids ( $this->mid, $fids,1);
				$var['data'] = $schools;
				$var['orgnization_follow_state'] = $orgnization_follow_state;
				break;
			case 2:
				$template = '_beeninclass';
				$data = D('CySchool')->get_orglist(2,'follower_count','DESC');
				if(empty($data)){
					$data = D('CyClass')->list_class(1,4);
				}
				
				foreach($data as &$org){
					$org['parent'] = D("CySchool")->get_school_info_by_id($org['schoolId']);
				}
				$var['data'] = $data;
				break;
		}
		$content['html'] = fetch($template,$var);
		if(empty($content['html'])){
			$content['status'] = 0;
			$content['msg'] = L('PUBLIC_WEIBOISNOTNEW');
		}else {
			$content['status'] = 1;
			$content['msg'] = L('PUBLIC_SUCCESS_LOAD');
		}
		return $content;
	}
	/**
	 * 已加入学校 
	 */
	public function beeninschool(){
		$content = $this->beeninorgs(1);
		exit(json_encode($content));
	}
	
	/**
	 * 已加入班级 
	 */
	public function beeninclass(){
		$content = $this->beeninorgs(2);
		exit(json_encode($content));
	}
	
	/**
	 * 获取区域的组织机构信息
	 */
	public function getOrg(){
		$areaId = $_POST['areaId'];
		$citys = Model("CyArea")->listAreaById($areaId,'county',0,100);
		$result['data']=$citys;
		foreach($citys as $key=>$value){
			$result['city']=$value;
			break;
		}
		echo json_encode($result);
	}
	
	
	/**
	 * 获取指定用户小名片所需要的数据
	 * @return string 指定用户小名片所需要的数据
	 */
	public function showFaceCard() {
		if(empty($_REQUEST['cid'])) {
			exit("未查询到班级信息 ");			// 错误的用户信息
		}
		$orgId = $_REQUEST['cid'];
		$orgType = intval($_REQUEST['orgType']);//1：学校，2：班级
		$this->_initOrg($orgType,$orgId);
		$this->display();
	}
	
	private function _initOrg($orgType,$cid){
		if($orgType == 1){
			$orgnization = model('CySchool')->get_school_info_by_id($cid);
		}else{
			$orgnization = model('CyClass')->get_class_info_by_id($cid);
		}
		$orgnization['spaceurl'] = getHomeUrl($orgnization);
		$orgnization['area'] =  Model('CyArea')->getFullAreaByOrgnization($orgnization);
		
		if($orgnization['type'] == 1){
			$orgnization['tcount'] = D("CyStatistics")->count_teacher_by_school($orgnization['id']);
			$orgnization['scount'] = D("CyStatistics")->count_student_by_school($orgnization['id']);
			$orgnization['ccount'] = D("CyStatistics")->count_class_by_school($orgnization['id']);
		}else{
			$orgnization['tcount'] = D("CyStatistics")->count_teacher_by_class($orgnization['id']);
			$orgnization['scount'] = D("CyStatistics")->count_student_by_class($orgnization['id']);
			$orgnization['pcount'] = D("CyStatistics")->count_parents_by_class($orgnization['id']);
		}
		
		$follow = D('ClassData','class')->getClassDataByFid($orgnization['id'],'follower_count',$orgnization['type']);
		$orgnization['follower'] = $follow['value'];
		
		$follow_state = model ( 'Follow' )->getFollowStateByFids( $this->mid, $cid, $orgnization['type']);
		$this->assign ( 'follow_state', $follow_state );
		$this->assign('org', $orgnization);
	}
	
	
}