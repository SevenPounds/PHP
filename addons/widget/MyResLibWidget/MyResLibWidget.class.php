<?php
/**
 * 资源库
 * @example {:W('MyResLib')}
 * @version TS3.0 
 */
class MyResLibWidget extends Widget{

	/**
	 * 渲染空间数据统计模板
	 */
	public function render($data){
		$var = array_merge($data, array());
		$content = $this->renderFile(dirname(__FILE__)."/content.html",$var);

		unset($var,$data);

		return $content;
    }
    
    /**
     * 获取当前登录用户上传、下载、收藏的统计数
     */
    public function getData(){
    	$login = $GLOBALS['ts']['user']['login'];
    	$upload = ResoperationType::UPLOAD;
    	$download = ResoperationType::DOWNLOAD;
    	$collection = ResoperationType::COLLECTION;
    	
    	$data = array();
    	$data['upload'] = $this->getResoperationCount($login,$upload);
    	$data['download'] = $this->getResoperationCount($login,$download);
    	$data['collection'] = $this->getResoperationCount($login,$collection);
    	
    	echo  json_encode($data);
    }
    
    /**
     * 获取资源库容量
     */
    public function getCapacity(){
    	
    	$oneGB = 1024*1024*1024;
    	
    	$oneMB = 1024*1024;
    	
    	$oneKB = 1024;
    	
    	$result = array();
    	
    	$loginName = $this->user['login'];
    	
    	$res = D('resource_capacity')->where("login_name='$loginName'")->field('used_capacity,total_capacity')->find();
		
    	if($res){
    		$res['used_capacity'] = floatval($res['used_capacity']);
    		$res['total_capacity'] = floatval($res['total_capacity']);
    		
    		// 已使用容量在总容量中所占的百分比
    		$result['percentage'] = floatval($res['used_capacity'])/$res['total_capacity'];
    		$result['percentage'] = number_format($result['percentage'],5);
    		
    		if($res['used_capacity'] < ($oneMB/2)){
    			$result['usedCapacity'] = floatval($res['used_capacity'])/$oneKB;
    			$result['usedCapacity'] = number_format($result['usedCapacity'],1);
    			$result['flag'] = "K";
    		}else if($res['used_capacity'] < ($oneGB/2)){
    			$result['usedCapacity'] = floatval($res['used_capacity'])/$oneMB;
    			$result['usedCapacity'] = number_format($result['usedCapacity'],1);
    			$result['flag'] = "M";
    		}else{ 
    			$result['usedCapacity'] = floatval($res['used_capacity'])/$oneGB;
    			$result['usedCapacity'] = number_format($result['usedCapacity'],1);
    			$result['flag'] = "G";
    		}
    		$result['totalCapacity'] = $res['total_capacity']/$oneGB;
    		$result['totalCapacity'] = number_format($result['totalCapacity'],1);
    	}else{
    		// 如果当前用户在数据库中的没有容量记录就新建一条记录
    		$data = array();
    		// 用户资源库初始总容量
    		$capacityGB = C('RESLIB_USER_TOTAL_CAPACITY');
    		$capacity = intval($capacityGB)*$oneGB;
    		$data['login_name'] = $loginName;
    		$data['used_capacity'] = 0;
    		$data['total_capacity'] = $capacity;
    		D('resource_capacity')->add($data);
    			
    		$result['usedCapacity'] = 0;
    		$result['usedCapacity'] = number_format($result['usedCapacity'],1);
    		$result['totalCapacity'] = $capacityGB;
    		$result['flag'] = "K";
    		$result['percentage'] = 0;
    	}
    	
    	/**--------------------去除XX.0的情况----------------------------**/
    	$result['usedCapacity'] = strval($result['usedCapacity']);
    	$lastChar = substr($result['usedCapacity'],-1);
    	if($lastChar === '0'){
    		$result['usedCapacity'] = substr($result['usedCapacity'],0,(strlen-2));
    	}
    	/**---------------------------end---------------------------**/
    	
    	echo json_encode($result);
    }
    
    /**
     * 获取资源库操作的统计数
     * @param int $login_name 用户login_name
     * @param int $operationtype 操作类型
     */
    private function getResoperationCount($login_name,$operationtype){
    	$res = D('resource_operation')->where("operationtype=$operationtype and login_name = '".$login_name."'")->count();
    	return $res;
    }
}
?>