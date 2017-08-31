<?php
/**
 * 名师工作室左边菜单widget
 * @author sjzhao
 * @version 1.0
 */
class LeftMenuWidget extends Widget{
	/**
	 * 模板渲染
	 * @param array $data 相关数据
	 * @return string 频道内容渲染入口
	 */
	public function render($data) {
		// 设置频道模板
		$template = 'list';

		// 配置参数
		$content = $this->renderFile(dirname(__FILE__) . "/" . $template . ".html", $data);

		return $content;
	}
}
?>
