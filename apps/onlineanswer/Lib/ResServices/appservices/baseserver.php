<?php
require_once (dirname ( dirname ( __FILE__ ) ) . '/lib/client.php');

define('LOG_ROOT', dirname(dirname ( __FILE__ )) .'/logs/');
require_once (dirname(dirname ( __FILE__ )) . '/lib/logger.php');

class BaseServer {
	
	public $config = array(); //获取配置文件
	
	public $logger = null;  //写日志
	
	public $client = null;
	
    public function __construct(){
    	$this->config = $_Config;
    	$this->logger = Logger::getInstance();
    	$this->serverUrl = C('RES_SERVICE_URL');
    	$this->client = new ResourceServiceClient (C('CLIENT_APP_NAME'));
    	$this->client->set_host($this->serverUrl);
    }    
    
    /**
     * 对返回的结果反序列化
     * @param string $json
     */
    public function getResult($json){
    	if(is_string($json)){
    		$obj = json_decode($json);
    	}else if(is_array($json)){
    		$obj = $json;
    	}
    	return $obj;
    }
}

?>