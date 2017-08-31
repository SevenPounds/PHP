<?php
/**
 * 频道内容渲染Widget
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class ContentWidget extends Widget{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data){
		// 设置频道模板
		$template = empty($data['tpl']) ? 'load' : t($data['tpl']);
		$var['cid'] = intval($data['cid']);
		switch($template){
			case 'classload':
				break;
			default:
				$template = 'load';
				break;
		}
		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
    }

    public function loadSchool(){
		$newschool['data'] = model('CySchool')->get_orglist(2,'new_folower_count',null,8);
		$nfids = getSubByKey ( $newschool ['data'], 'id');
		$newschool['follow_state'] = model('Follow')->getFollowStateByFids ( $this->mid, $nfids,2 );
		$content['newshtml'] = fetch(dirname(__FILE__)."/_classlist.html", $newschool);
		
		$activeschool['data'] = model('CySchool')->get_orglist(2,'new_folower_count',null,8);
		$afids = getSubByKey ( $activeschool ['data'], 'id');
		$activeschool['follow_state'] = model('Follow')->getFollowStateByFids ( $this->mid, $afids,2 );
		$content['activeshtml'] = fetch(dirname(__FILE__)."/_classlist.html", $activeschool);
		
		if(empty($content['newshtml']) && empty($content['activeshtml'])) {
			$content['status'] = 0;
			$content['msg'] = L('PUBLIC_WEIBOISNOTNEW');
		} else {
			$content['status'] = 1;
			$content['msg'] = L('PUBLIC_SUCCESS_LOAD');
		}
		$return = $content;
		exit(json_encode($return));
    }
    /**
     * 载入频道内容
     * @return json 频道渲染内容
     */
    public function loadMore(){
    	$areaid = intval($_REQUEST['cid'])?intval($_REQUEST['cid']):1123;
    	$loadLimit = intval($_REQUEST['loadlimit']);
    	$page = intval($_REQUEST['p']);
    	$loadCount = intval($_REQUEST['loadcount']);
    	
    	$cityname = $_REQUEST['cityname'];
    	$townname = $_REQUEST['townname'];
    	
    	// 获取HTML数据
    	$content = $this->getData($page, $loadLimit, $areaid,$cityname,$townname);
		// 查看是否有更多数据
		if(empty($content['html']) && empty($content['pageHtml'])) {
			$return['status'] = 0;
			$return['msg'] = L('PUBLIC_WEIBOISNOTNEW');
		} else {
			$return['status'] = 1;
			$return['msg'] = L('PUBLIC_SUCCESS_LOAD');
    		$return['html'] = $content['html'];
    		$return['loadId'] = $content['lastId'];
    		$return['page'] = $content['page'];
            $return['firstId'] = (empty($_REQUEST['p']) && empty($_REQUEST['loadId']) ) ? $content['firstId'] : 0;
            $return['pageHtml'] = $content['pageHtml'];
		}
    	exit(json_encode($return));
    }

    public function getData($page, $loadLimit, $areaid,$cityname,$townname) {
		// 学校信息
		$list = model('CySchool')->get_school($areaid,$page,$loadLimit);
		if(!empty($list)){
			$content['page'] = $page+1;
			$var['data'] =$list['data'];
			$var['cityname'] = $cityname;
			$var['townname'] = $townname;
			/***关注状态**/
			$fids = getSubByKey ( $list ['data'], 'id');;
			$var['follow_state'] = model('Follow')->getFollowStateByFids ( $this->mid, $fids,1 );
		}
    	$content['pageHtml'] = $list['html'];
	    // 渲染模版
		$content['html'] = fetch(dirname(__FILE__).'/_load.html', $var);
	    return $content;    	
    }
    
}