<?php

/**
 * 
 * @author xrding 
 *
 *	测试例子
 *	$client = new Admin_Client('http://localhost/ThinkSNS/api/admin.php');
 *	echo $client->getTopNav();
 */
class Admin_Client{
	private $client = null;
	/**
	 * 
	 * @param string $url 资源服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}
	
	/**
	 * 获取顶部导航栏数据
	 */
	function getTopNav() {
		return $this->client->getTopNav();
	}
	
	/**
	 * 获取底部导航栏数据
	 */
	function getBottomNav(){
		return $this->client->getBottomNav();
	}
}
?>
