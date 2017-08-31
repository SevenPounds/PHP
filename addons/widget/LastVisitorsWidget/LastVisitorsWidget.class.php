<?php
/**
 * @author yxxing
 * @version 1.0
 */
class LastVisitorsWidget extends Widget {
	/**
	 * 最近来访者
	 * @example
	 * 
	 * @return 
	 */
	public function render ($data) {
		$var = array();
		is_array($data) && $var = array_merge($var, $data);
		$var['tpl'] = 'last_visitors';

        $list = S($data['uid']."last_visitors");
        if(!$list){
            $list = model('UserVisitor')->getLastVisitors($data['uid']);
            S($data['uid']."last_visitors",$list,10);
        }

		$new_list = array();
		foreach($list AS $visitor){
			$visitor['time'] = date('n月j日', $visitor['vtime']);
			$user_info = model ( 'User' )->getUserInfo ( $visitor['vuid'] );
			$visitor['user_info'] = $user_info;
			$new_list[] = $visitor;
		}
		$var['visitors'] = $new_list;
		$content = $this->renderFile(dirname(__FILE__).'/'.$var['tpl'].'.html', $var);
		//释放变量
		unset($var,$data);
		return $content;
	}
	public function loadVisitors(){
		$uid = $_POST['uid'];
		$var = array();
		$var['tpl'] = 'ajax_last_visitors';
		$list = model('UserVisitor')->getLastVisitors($uid);
		$new_list = array();
		foreach($list AS $visitor){
			$visitor['time'] = date('n月j日', $visitor['vtime']);
			$user_info = model ( 'User' )->getUserInfo ( $visitor['vuid'] );
			$visitor['user_info'] = $user_info;
			$new_list[] = $visitor;
		}
		//总访问数
		$total_count = 0;
		//今天访问次数
		$perday_count = 0;
		$perday_result = D('UserData') ->where(array("uid"=>$uid, "key"=>"perday_visitor_count"))->find();
		$total_result = D('UserData') ->where(array("uid"=>$uid, "key"=>"visitor_count"))->find();
		if($total_result){
			$total_count = $total_result['value'];
		}
		if($perday_result){
			//当前时间
			$now_time = date("Y-m-d",time());
			//今天开始时间
			$today_start = strtotime($now_time);
			//今天结束时间
			$today_end = $today_start + 60*60*24;
			//上次访问时间
			$last_time = strtotime($perday_result['mtime']);
			$last_time > $today_start && ($last_time < $today_end) && $perday_count = $perday_result['value'];
		}
		unset($list);
		$var['visitors'] = $new_list;
		$var['total_count'] = $total_count;
		$var['perday_count'] = $perday_count;
		$content = $this->renderFile(dirname(__FILE__).'/'.$var['tpl'].'.html', $var);
		//释放变量
		unset($var,$data);
		return json_encode($content);
	}
}