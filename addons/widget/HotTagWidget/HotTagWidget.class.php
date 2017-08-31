<?php
/**
 * 热门标签
 * @author "sjzhao <sjzhao@iflytek.com>"
 *
 */
class HotTagWidget extends Widget{
	/**
	 * !CodeTemplates.overridecomment.nonjd!
	 * @see Widget::render()
	 * @param string type 应用名称
	 * @param int limit 热门标签个数限制,默认5个
	 */
	public function render($data) {
		// TODO 自动生成的方法存根
		$var = array();
		$var['tpl'] = 'hot';
		is_array($data) && $var = array_merge($var,$data);
		$type = $var['type'];//应用名称
		$limit = $var['limit'] ? $var['limit']:5;
		$var['hottag'] = model('Tag')->getHotTag($type, $type, $limit);
		$content = $this->renderFile(dirname(__FILE__)."/".$var['tpl'].".html", $var);
		return $content;
	}

	
}
?>