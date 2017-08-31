<?php
/**
 * 教研公告Widget
 * @author yudylaw
 * @version TS3.0
 */
class EduAnnouncementWidget extends Widget {

	/**
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data) {
		$list = array();
		array_push($list,array('title'=>'小学信息技术','date'=>'2013-07-21'));
		array_push($list,array('title'=>'小学信息技术','date'=>'2013-07-21'));
		array_push($list,array('title'=>'小学信息技术','date'=>'2013-07-21'));
		$var['list'] = $list;
		$content = $this->renderFile(dirname(__FILE__)."/list.html", $var);

		return $content;
	}
}