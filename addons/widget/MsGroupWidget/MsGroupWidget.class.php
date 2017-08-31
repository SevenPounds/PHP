<?php
/**
 * 名师工作室widget
 * @author dingyf
 * @version TS3.0
 */

class MsGroupWidget extends Widget {
	/**
	 * @param array 传递参数 
	 * @param string 页面html
	 */
	public function render($data) {
		
		 $msgroup_list = D('MSGroup', 'msgroup')->getMsGroupByUid($GLOBALS['ts']['user']['uid']);
		 $var['msgroup_list'] = $msgroup_list;

		 $content = $this->renderFile(dirname(__FILE__) . "/msgrouplist.html", $var);
		 return $content;
	}
}
