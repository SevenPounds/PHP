<?php
/**
 * 资源推荐Widget
 * @author yudylaw
 * @version TS3.0
 */
class ResouceRecommendWidget extends Widget {

	/**
	 * @param array $data 配置相关数据
	 * @return string 渲染页面的HTML
	 */
	public function render($data) {
		$content = $this->renderFile(dirname(__FILE__)."/resourceList.html");
		return $content;
	}
}