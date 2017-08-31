<?php
/**
 * 我参与的课题Widget
 * @author yudylaw
 * @version TS3.0
 */
class KetiListWidget extends Widget {

	/**
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data) {
		$list = array();
		$list=D('Research','research')->getResearchByJuid($GLOBALS['ts']['mid'], 'research');
		$var['list'] = $list['data'];
		$content = $this->renderFile(dirname(__FILE__)."/ketiList.html", $var);

		return $content;
	}
}
