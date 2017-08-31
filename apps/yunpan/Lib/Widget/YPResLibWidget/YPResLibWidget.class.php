<?php
/**
 * 资源库
 * @example {:W('YPResLib')}
 * @version TS3.0 
 */
class YPResLibWidget extends Widget{

	/**
	 * 渲染空间数据统计模板
	 */
	public function render($data){
		$app=$_REQUEST['app'];
		$mod=$_REQUEST['mod'];
		$act=$_REQUEST['act'];
		if($app=='public'&&$mod=='Index'&&$act=='index'){
			$var = array_merge($data, array());
			$content = $this->renderFile(dirname(__FILE__)."/content.html",$var);
			unset($var,$data);
			return $content;
		}		
    }      
}
?>