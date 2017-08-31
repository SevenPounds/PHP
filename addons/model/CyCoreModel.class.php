<?php
/**
 * 第三方Client服务Model
 * @since 2014/4/3
 * @author cheng
 *
 */
class CyCoreModel {
	
	public function __construct(){
		//注册AUTOLOAD方法
		if ( function_exists('spl_autoload_register') )
			spl_autoload_register(array($this,'autoload'));
		
	}
	
	/**
	 * 魔法方法生成属性值
	 * @param  $val
	 */
	public function __get($val){
			switch($val){
				case 'Resource':
					return new  ResourceClient();
					break;
				case 'Category':
					return new  CategoryClient();
					break;
				case 'Tree':
					return new  TreeClient();
					break;
				case 'CyCore':
					return new CyClient();
				default:break;
			}
	}
	
	/**
	 * 自动化导入文件
	 */
	public  static function  autoload($classname){
		$paths = array(ADDON_PATH.'/library/Clients/ResourceClient',ADDON_PATH.'/library/Clients',ADDON_PATH.'/library/Clients/UserCenter');
		foreach ($paths as $path){
			if(tsload($path.'/'.$classname.'.php'))
				// 如果加载类成功则返回
				return ;
		}
	}
}

