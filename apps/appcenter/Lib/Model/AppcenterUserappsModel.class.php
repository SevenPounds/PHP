<?php
/**
 * 应用中心-用户应用模型
 * @author hhshi
 * @version 0.1
 */
class AppcenterUserappsModel extends Model{
	protected $tableName = 'appcenter_userapps';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'login',
			2 => 'appid',
			3 => 'ctime',
			4 => 'is_default'
	);

	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
	}
	
	/**
	 * 根据用户登录名，初始化用户默认应用
	 * @param string $login 用户登录名
	 */
	public function initDefaultApps($login,$roleEnName = 'teacher'){
		if(empty($login)){
			Log::write("登录名为空！", Log::DEBUG);
			return false;
		}
		
		// 检查用户是否已初始化默认应用，并执行相关操作
		$map = array('login'=>$login);
		$apps = $this->where($map)->select();
		if(count($apps) == 0){
			$map = array('status'=>'1','is_default'=>'1');
			$defaultApps = D("AppcenterApp","appcenter")->where($map)->select();
			
			// 添加一个假的应用，作为初始化标记
			$userApp = array('login'=>$login,'appid'=>0,'ctime'=>date('Y-m-d H:i:s',time()),'is_default'=>1);
			$this->add($userApp);
			
			// 获取应用配置
			$roleDefaultApps = include_once('./apps/appcenter/Conf/config.php');
			// 获取不同角色用户默认应用
			$roleApps = $roleDefaultApps['DEFAULT_APPS_BY_ROLE'][$roleEnName];
			foreach($defaultApps as $key=>$default){
				if(in_array($default['app_en_name'],$roleApps)){
					$userApp = array('login'=>$login,'appid'=>$default['appid'],'ctime'=>date('Y-m-d H:i:s',time()),'is_default'=>$default['is_default']);
					$result = $this->add($userApp);
					Log::write("Add Default app : " . "login : " . $login . " Appid : " . $default['appid'] . " result : " . $result, Log::DEBUG);
				}
			}
		}
	}
	
	/**
	 * 根据用于名获取用户应用集合
	 * @param string $login 用户登录名
	 */
	public function getUserApps($login){
		if(empty($login)){
			return array();
		}
		$map = array('login'=>$login);
		$userApps = $this->where($map)->order("ctime")->select();
		if(isset($userApps[0]['cid'])&&$userApps[0]['cid']){
			unset($userApps[0]);
		}
		
		$appids = array();
		foreach($userApps as $key=>$app){
			$getApp = D("AppcenterApp","appcenter")->where(array('status'=>1,'appid'=>$app['appid']))->find();
			if(!empty($getApp)){
				if($app["appid"]=="14"){ // 20160308 jiaqiang 按照需求将监管统计屏蔽掉
					continue;
				}
				$apps[] = $getApp;
			}
		}
		return $apps ? $apps : array();
	}
	
	/**
	 * 根据用户名和应用id添加应用
	 * @param string $login 登录名
	 * @param int $appId 应用id
	 * @return array('statuscode'=>true,'message'=>'添加成功！')
	 */
	public function addApp($login, $appId){
		if(empty($login) || empty($appId)){
			return array("statuscode"=>false,"message"=>"参数缺失！");
		}
		
		// 检查应用id是否纯数字
		if(preg_match("/^\d*$/", $appId)){
			$map = array('status'=>'1','appid'=>$appId);
			$app = D("AppcenterApp","appcenter")->where($map)->find();
			if(!empty($app)){
				// 检查该用户是否已经添加此应用
				$userApp = $this->where(array('login'=>$login,'appid'=>$appId))->find();
				if(!empty($userApp)){
					return array("statuscode"=>false,"message"=>"已有此应用，不可重复添加！");
				}
				
				$userApp = array('login'=>$login,'appid'=>$app['appid'],'ctime'=>date('Y-m-d H:i:s',time()),'is_default'=>$app['is_default']);
				$result = $this->add($userApp);
				if($result){
					return array("statuscode"=>true,"message"=>"添加成功！");
				}else{
					return array("statuscode"=>false,"message"=>"添加失败！");
				}
			}else{
				return array("statuscode"=>false,"message"=>"无此应用，添加失败！");
			}
		}else{
			return array("statuscode"=>false,"message"=>"应用id参数错误！");
		}
	}
	
	/**
	 * 根据用户登录名和应用id删除应用
	 * @param string $login 登录名
	 * @param int $appId 应用id
	 * @return array('statuscode'=>true,'message'=>'删除成功！')
	 */
	public function deleteApp($login, $appId){
		if(empty($login) || empty($appId)){
			return array("statuscode"=>false,"message"=>"参数缺失！");
		}
		
		// 检查应用id是否纯数字
		if(preg_match("/^\d*$/", $appId)){
			// 检查该用户是否已经添加此应用
			$userApp = $this->where(array('login'=>$login,'appid'=>$appId))->find();
			if(empty($userApp)){
				return array("statuscode"=>false,"message"=>"用户无此应用，不可进行删除操作！");
			}
			
			$map = array('login'=>$login,'appid'=>$appId);
			$result = $this->where($map)->delete();
			if($result){
				return array("statuscode"=>true,"message"=>"删除成功！");
			}else{
				return array("statuscode"=>false,"message"=>"删除失败！");
			}
		}else{
			return array("statuscode"=>false,"message"=>"应用id参数错误！");
		}
	}
}
?>