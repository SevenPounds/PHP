<?php
/**
 * 推荐校园
 * @author cheng
 *
 */
class RecommandCampusWidget extends Widget{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data)
	{
		$var['title']=$data['title']?$data['title']:'最受欢迎学校<font class="top">TOP5</font>';
		// 设置频道模板
		$template = empty($data['tpl']) ? 'recommandschool' : t($data['tpl']);
		$var['tpl']=$template;
		$content = $this->renderFile(dirname(__FILE__)."/".$template.".html", $var);
		return $content;
	}
	
	public function ajax_render(){
		$tpl = empty($_REQUEST['tpl']) ? 'recommandschool' : t($_REQUEST['tpl']);
		switch($tpl)
		{
			case 'friendshipschool':
				$template = '_friendlist.html';
				$var['data'] = model('CySchool')->get_orglist(1,'follower_count','DESC',5);
				if(empty($var['data'])){
					$var['data'] = model('CySchool')->list_school(1,5);
				}
				break;
			case 'activeschool':
				$template = '_schoollist.html';
				$var['active'] =true;
				$var['data'] = model('CySchool')->get_orglist(1,'visitor_count','DESC',5);
				if(empty($var['data'])){
					$var['data'] = model('CySchool')->list_school(1,5);
				}
				$fids = getSubByKey($var['data'],'id');
				$var['vcount'] = D('ClassData','class')->getVisitorCount($fids);
				break;
			default:
				$template = '_schoollist.html';
				$var['data'] = model('CySchool')->get_orglist(1,'follower_count','DESC',5);
				if(empty($var['data'])){
					$var['data'] = model('CySchool')->list_school(1,5);
				}
				break;
		}
		
		/***关注状态**/
		$orgids = getSubByKey($var['data'],'id');
		$var['orgnization_follow_state'] = model('Follow')->getFollowStateByFids ( $this->mid, $orgids,1 );
		
		$content['html'] = fetch(dirname(__FILE__).'/'.$template, $var);
		if(empty($content['html'])){
			$content['status'] = 0;
			$content['msg'] = L('PUBLIC_WEIBOISNOTNEW');
		}else {
			$content['status'] = 1;
			$content['msg'] = L('PUBLIC_SUCCESS_LOAD');
		}
		exit(json_encode($content));
	}
	
}
?>