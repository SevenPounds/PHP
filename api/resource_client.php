<?php
/**
 * @author yuliu2
 */
class Resource_Client{
	private $client = null;
	/**
	 * 
	 * @param string $url 资源服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}
	
	/**
	 * 根据用户cyuid获取用户角色类型
	 * @param string $cyuid 用户cyuid
	 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
	 */
	function getCloudUserRole($cyuid){
		return $this->client->getCloudUserRole($cyuid);
	}
	
	/**
	 * 区域平台根据应用名和授权码获取Token
	 * @param string $appName 应用名称
	 * @param string $authcode 授权码
	 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
	 * 注：status : 200 获取成功， 400 获取失败
	 */
	function getToken($appName, $authcode){
		return $this->client->getToken($appName, $authcode);
	}
	
	/**
	 * 根据用户令牌获取授权用户登录名
	 * @param string $appName 应用名称
	 * @param string $token 用户令牌
	 * @return string {"status":(返回状态), "message":(结果信息), "data":(信息)}
	 * 注：status : 200 获取成功， 400 获取失败
	 */
	function getLogin($appName, $token){
		return $this->client->getLogin($appName, $token);
	}
	
	/**
	 * 添加上传资源记录
	 * @param string $resid 资源id
	 * @param string $login	用户登录名
	 * @param string $restype 资源类型
	 * @param string $product_id 产品编号
	 */
	function addUploadRecord($resid,$login,$restype,$product_id){
		return $this->client->addUploadRecord($resid,$login,$restype,$product_id);
	}
	
	/**
	 * 区域平台删除已上传资源
	 * @param  $resid 	资源id
	 * @param  $login	资源上传者登录名
	 */
	function delUploadRecord($resid, $login) {
		return $this->client->delUploadRecord($resid, $login);
	}
	
	/**
	 * iflybook下载资源
	 * @param string $resid
	 */
	function getresource($resid){
		return $this->client->getresource($resid);
	}
	
	/**
	 * 获取上传和收藏的资源列表
	 */
	function list_resource($username, $restype, $reskeyword, $suffixAry, $pageindex, $pagesize){
		return $this->client->list_resource($username, $restype, $reskeyword, $suffixAry, $pageindex, $pagesize);
	}
	
	function addResourceToYunpan($cyuid, $rID){
		return $this->client->addResourceToYunpan($cyuid, $rID);
	}
}
?>
