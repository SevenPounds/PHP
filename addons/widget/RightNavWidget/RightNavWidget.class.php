<?php
/**
 * @author 
 * @version 1.0
 */
class RightNavWidget extends Widget {
	/**
	 * 右边导航
	 * @example
	 * 
	 * @return 
	 */
	public function render ($data) {
		$var['tpl'] = 'right_nav';
		$content = $this->renderFile(dirname(__FILE__).'/'.$var['tpl'].'.html', $var);
		return $content;
	}
}