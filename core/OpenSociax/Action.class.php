<?php

use  phpCAS;
require_once(ADDON_PATH . '/library/phpCAS/CAS.php');
/**
 * ThinkSNS Action控制器基类
 * @author  liuxiaoqing <liuxiaoqing@zhishisoft.com>
 * @version TS3.0
 */
abstract class Action
{//类定义开始

    // 当前Action名称
    private		$name =  '';
    protected   $tVar =  array();
    protected   $trace = array();
    protected   $templateFile = '';
	protected   $appCssList = array();
    protected   $langJsList = array();

	protected	$site = array();
	protected	$user = array();
	protected   $cyuserdata = array();
	protected	$app = array();
	protected	$mid = 0;
	protected	$uid = 0;
	protected   $cymid = 0;
	protected   $cyuid = 0;
	protected   $roleEnName = null;
    protected $operationLog;
	protected $cyService;
    protected $sensitiveWord_svc;
    protected $epspClient;
    protected $apis_client;
    protected $appkey;
    protected $appsecret;
    protected $host;

    protected $session;
    /**
     * 架构函数 取得模板对
     * @access public
     */
    public function __construct() {
        ini_set("session.cookie_httponly", true);
        $this->appkey = C("CLIENT_APP_NAME");
        $this->appsecret = C("CLIENT_APP_SECRET");
        $this->host = C("ATTACH_SERVICE_URL");

        $this->apis_client = new \CyStorage\Apis(C("CLIENT_APP_NAME"),C("CLIENT_APP_SECRET"),C("ATTACH_SERVICE_URL"));

        $this->cyService = new \CyClient();
        $this->initSSO();
        $this->initUser();
		$this->initSite();
        $this->initApp();
        Addons::hook('core_filter_init_action');
		//控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        $this->operationLog=array();
        $schoolDetail=$this->cyService->listSchoolByUser($this->cyuid,0,100);
        $this->operationLog["cyUserId"]=$this->cyuid;
        $this->operationLog["appName"]="sns";
        $this->operationLog["schoolId"]=$schoolDetail[0]->id;
        $this->operationLog["role"]=$this->roleEnName;
        $this->operationLog["provinceId"]=$schoolDetail[0]->provinceId;
        $this->operationLog["cityId"]=$schoolDetail[0]->cityId;
        $this->operationLog["districtId"]=$schoolDetail[0]->districtId;
        $this->sensitiveWord_svc = new \SensitivewordClient();
        
        $this->userInfo=$_SESSION["login_userInfo"];
        $curRoleName = null;
        if(isset($this->roleEnName)){
        	$curRoleName = D("UserLoginRole")->getCNRoleName($this->roleEnName);
        }
        $this->assign('roleEnName', $this->roleEnName);
        $this->assign('managerLevel', $curRoleName);
    }

    /**
     * 站点信息初始化
     * @access private
     * @return void
     */
	private function initSite() {
		
		//载入站点配置全局变量
		$this->site = model('Xdata')->get('admin_Config:site');

        if($this->site['site_closed'] == 0 && APP_NAME !='admin'){
        	//TODO  跳转到站点关闭页面
            $this->page404($this->site['site_closed_reason']); exit();
        }
        
        //检查是否启用rewrite
        if(isset($this->site['site_rewrite_on'])){
            C('URL_ROUTER_ON',($this->site['site_rewrite_on']==1));
        }

        //初始化语言包
        $cacheFile = C('F_CACHE_PATH').'/initSiteLang.lock.php';
        if(!file_exists($cacheFile)){
        	model('Lang')->initSiteLang();
        }        
        
        //LOGO处理
        $this->site['logo'] = getSiteLogo($this->site['site_logo']);
        
        //默认登录后首页
        if(intval($this->site['home_page'])){
            $appInfo = model('App')->where('app_id='.intval($this->site['home_page']))->find();
            $this->site['home_url'] = U($appInfo['app_name'].'/'.$appInfo['app_entry']);
        }else{ 
            if(in_array($this->roleEnName, array(UserRoleTypeModel::STUDENT,UserRoleTypeModel::TEACHER,UserRoleTypeModel::RESAERCHER)) ) {
               $this->site['home_url'] = U('public/Index/index');
            }else{
               $this->site['home_url'] = U('public/Index/index');  
            } 
        }
		
        //赋值给全局变量
        $GLOBALS['ts']['site'] = $this->site;

        //网站导航
        $GLOBALS['ts']['site_top_nav'] = model('Navi')->getTopNav();
        $GLOBALS['ts']['site_bottom_nav'] = model('Navi')->getBottomNav();
        $GLOBALS['ts']['site_bottom_child_nav'] = model('Navi')->getBottomChildNav($GLOBALS['ts']['site_bottom_nav']);

        //获取可搜索的内容列表
        if(false===($searchSelect=S('SearchSelect'))){
            $searchSelect = D('SearchSelect')->findAll();
            S('SearchSelect',$searchSelect);
        }
        
        //网站所有的应用
        $GLOBALS['ts']['site_nav_apps'] = model('App')->getAppList(array('status'=>1,'add_front_top'=>1),9);

        //网站全局变量过滤插件
        Addons::hook('core_filter_init_site');

        $this->assign('site', $this->site);
        $this->assign('site_top_nav', $GLOBALS['ts']['site_top_nav']);
        $this->assign('site_bottom_nav', $GLOBALS['ts']['site_bottom_nav']);
        $this->assign('site_bottom_child_nav',$GLOBALS['ts']['site_bottom_child_nav']);
        $this->assign('site_nav_apps', $GLOBALS['ts']['site_nav_apps']);
        $this->assign('menuList', $searchSelect);
		if(strtolower(C('PRODUCT_CODE')) === 'changyan'){
			tsconfig(include CONF_PATH.'/config.'.strtolower(C('PRODUCT_CODE')).'.php');
			$this->assign('columnNavs', C('columnNavs'));
			$this->assign('newsNavs',C('newsNavs'));
			$this->assign('communityNavs',C('communityNavs'));
			$this->assign('marketNavs',C('marketNavs'));
		}
        
        return true;
	}

    /**
     * 应用信息初始化
     *
     * @access private
     * @return void
     */
    private function initApp() {        
        //是否为核心的应用
        if(in_array(APP_NAME,C('DEFAULT_APPS'))){
            return true;
        }
        
        //加载后台已安装应用列表        
        $GLOBALS['ts']['app'] = $this->app = model('App')->getAppByName(APP_NAME);        

        if(empty($this->app) || !$this->app){
        	$this->error('此应用不存在');
        	return false;
        }
        if($this->app['status'] == 0){
        	$this->error('此应用已经关闭');
        	return false;
        }
         Addons::hook('core_filter_init_app');
        return true;
    }

     /**
     * 初始化sso
     */
    private function initSSO(){
        phpCAS::setDebug(); 
        phpCAS::setVerbose(true); 
        phpCAS::client(C('SSO_SERVER'),C('SSO_LOGIN_URL'), true);
        phpCAS::setNoCasServerValidation(); 
        phpCAS::handleLogoutRequests(false); 
    }
    
    /**
     * 用户信息初始化
     * @access private
     * @return void
     */
	private function initUser() {
        if(model('Passport')->needSSOLogin()){
            phpCAS::checkAuthentication();
            if(phpCAS::isSessionAuthenticated()) {
                $openId = phpCAS::getUser();
                $attributes = phpCAS::getAttributes();
                $cas_username = $attributes["loginName"];
            }
        }else{
            $attributes = phpCAS::getAttributesWithoutValidation(); 
            $cas_username = $attributes["loginName"];          
        }     
		if ($cas_username) {
			$cyuserdata = Model('CyUser')->getCyUserInfo($cas_username);
			$cyuser = $cyuserdata['user'];
			$cyuser['locations'] = $cyuserdata['locations'];
			$cyuser['rolelist'] = $cyuserdata['rolelist'];
		
			if(!empty($cyuser['cyuid'])){
				Model('CyUser')->cyLogin($cyuser);
			}
		}
		
        //当前登录者uid
		$GLOBALS['ts']['mid'] = $this->mid = intval($_SESSION['mid']);
        //当前访问对象的uid
		//$GLOBALS['ts']['uid'] = $this->uid = intval($_REQUEST['uid'] == 0 ? $this->mid : $_REQUEST['uid']);
        if(isset ( $_GET ['cyuid'] ) || !empty ( $_GET ['cyuid'] )){
            $cyuid = t($_GET['cyuid']);
            $this->uid = model ( 'User' )->getUidByCyuid($cyuid);
        }else{
            $this->uid = intval($_REQUEST['uid'] == 0 ? $this->mid : $_REQUEST['uid']);
        }
        $GLOBALS['ts']['uid'] = $this->uid;

            // 获取用户基本资料
        $GLOBALS['ts']['user'] = !empty($this->mid) ? $this->user = model('User')->getUserInfo($this->mid) : array();
        $this->cyuserdata = !empty($this->mid) ? D('CyUser')->getCyUserInfo($this->user['login']) : array();

        $GLOBALS['ts']['cyuserdata'] = &$this->cyuserdata;//同步更新
      	if($this->mid != $this->uid) {
      		$GLOBALS['ts']['_user'] = !empty($this->uid) ? model('User')->getUserInfo($this->uid) : array();
      		$GLOBALS['ts']['_cyuserdata'] = !empty($this->uid) ? D('CyUser')->getCyUserInfo($GLOBALS['ts']['_user']['login']) : array();
      	} else {
      		$GLOBALS['ts']['_user'] = $GLOBALS['ts']['user'];
      		$GLOBALS['ts']['_cyuserdata'] = $this->cyuserdata;
      	}
      	$this->cymid = $GLOBALS['ts']['user']['cyuid'] =  $GLOBALS['ts']['cyuserdata']['user']['cyuid'];
      	$this->cyuid = $GLOBALS['ts']['_user']['cyuid'] = $GLOBALS['ts']['_cyuserdata']['user']['cyuid'];
        
      	//初始化用户角色信息
      	$this->_initUserRole($cas_username, $this->cyuserdata['rolelist']);

      	// 初始化用户默认应用列表
      	D("AppcenterUserapps","appcenter")->initDefaultApps($GLOBALS['ts']['user']['login'],$this->roleEnName);
      	        
        //应用权限判断
        if(!empty($this->app) && $this->app['status'] == 0){
            $this->error('此应用已经关闭');
        }
        if($this->uid>0){
            //当前用户的所有已添加的应用
            // $GLOBALS['ts']['_userApp']  = $userApp =  model('UserApp')->getUserApp($this->uid);
            //当前用户的统计数据
            $GLOBALS['ts']['_userData'] = $userData = model('UserData')->getUserData($this->uid);
            $userCredit = model('Credit')->getUserCredit($this->uid);
            $userCreditRanking = model('Credit')->getUserRanking($this->uid);
            $follow_state = model ( 'Follow' )->getFollowState($this->mid,$this->uid);
            $count = model('UserCount')->getUnreadCount($GLOBALS['ts']['mid']);
            $this->assign('messageCount',$count);
            $this->assign('follow_state',$follow_state);
            $this->assign('creditRank',$userCreditRanking);
            $this->assign('userCredit',$userCredit);	
    		$this->assign('_userData',$userData);
    		$this->assign('userData',$userData);
    		$this->assign('user_info',$GLOBALS['ts']['_user']);
//     		$this->assign('_userApp',$userApp);
        }

        // 获取当前Js语言包
        $this->langJsList = setLangJavsScript();

        $this->assign('mid', $this->mid);   //登录者
        $this->assign('uid', $this->uid);   //访问对象
        $this->assign('user', $this->user); //当前登陆的人

        $user_info=$this->user;


        if(($_SESSION["sns_member"]==null|| $_SESSION["sns_member"]=="")&& $user_info!=null&& $user_info!="")
        {
            $login_name = $user_info['login'];
            $userId = $user_info['cyuid'];
            D("Login")->Insert($userId, $login_name);
        }
        $_SESSION ['sns_member'] = $user_info;
        $_SESSION["login_userInfo"] = $this->user;
		
        $this->assign('initNums',model('Xdata')->getConfig('weibo_nums','feed'));
        Addons::hook('core_filter_init_user');
        return true;
	}

    /**
     * 初始化用户当前用户角色信息
     */
    protected function _initUserRole($loginName='', $roles){
    	$roleEnName = D("UserLoginRole")->getUserCurrentRole($loginName, $roles);
        switch($roleEnName){
            case UserRoleTypeModel::PARENTS:
            case UserRoleTypeModel::STUDENT:
                break;
            case UserRoleTypeModel::PROVINCE_RESAERCHER:
            case UserRoleTypeModel::CITY_RESAERCHER:
            case UserRoleTypeModel::COUNTY_RESAERCHER:
            case UserRoleTypeModel::RESAERCHER:
            case UserRoleTypeModel::TEACHER:
            break;
            case UserRoleTypeModel::MENBER:
            case UserRoleTypeModel::TEAMMEMBER:
            case UserRoleTypeModel::EDUPERSONNEL:
            case UserRoleTypeModel::LEDCSUPERADMIN:
            case UserRoleTypeModel::DEPTLEADER:
            case UserRoleTypeModel::LEDCSCHOOLMNG:
            case UserRoleTypeModel::LEDCDISTRICTMNG:
            case UserRoleTypeModel::LEDCCITYMNG:
            case UserRoleTypeModel::LEDCPROVINCEMNG:
                break;

        }
        $this->roleEnName = $roleEnName;
//         $this->roleEnName = "teacher";
//         $this->assign('roleEnName', $this->roleEnName);//放到构造方法中设置
    }

	/**
	 * 判断用户访问app的权限
	 * @return boolean 
	 */
	public function checkAppAccess(){
		$access_conf = include(CONF_PATH.'/access.inc.php');
		$role_app = $access_conf['role_app'];
		//判断用户角色
		$role = 'teacher';
		$cyuserdata = $GLOBALS['ts']['cyuserdata'];
		if(isset($cyuserdata['rolelist'])){
/* 			if(count($cyuserdata['rolelist']) > 0){
				$role = $cyuserdata['rolelist'][0]['name'];
			} */
		//支持多角色，改为从getUserCurrentRole获取当前切换的角色
			$role = D("UserLoginRole")->getUserCurrentRole($cyuserdata['user']['login'], $cyuserdata['rolelist']);
			if(empty($role)){//第304行，源码是默认为'teacher'，为保持一致加的处理
				$role = 'teacher';
			}
		}
		$role_key = "";
		foreach (array_keys($role_app) as $k){
			if(strpos($k, $role)!==false){
				$role_key = $k;
				break;
			}
		}
		if($role_key !== ""){
			$apps = $role_app[$role_key]['app'];
			if(isset($apps[APP_NAME])){
				if(count($apps[APP_NAME]) > 0){
					//判断当前action是否在不检查列表中
					if(isset($apps[APP_NAME]['no_permission'])&&in_array($GLOBALS['ts']["_act"], $apps[APP_NAME]['no_permission']))
						return true;
					//不同身份在同一应用下的权限需要靠参数判断
					$ret = true;
					unset($apps[APP_NAME]['no_permission']);//no_permission作为特例，不参与参数判断
					
					foreach ($apps[APP_NAME] as $k=>$v){
						$ret = $ret && (isset($_REQUEST[$k])?in_array($_REQUEST[$k], $v):true);
					}
					return $ret;
				}
				else{
					return true;
				}
			}
		}
		return false;
	}
	
    /**
     * 单点登录
     */
    private function _ssoLogin(){
	    	
	   	$login_url = C('sso_server'). "/login";
	    $service = C('sso_service');
	   	$app_dir = basename(SITE_PATH);
	   	$cas_return_key = $app_dir . '_casreturn';
	    
	    if (!isset($_SESSION[$cas_return_key]))
	    {
	    	$nextpage =  empty($_SERVER['REQUEST_URI']) ? SITE_URL : $_SERVER['REQUEST_URI'];//URL太长，改从session传递
	    	$md5str = md5($nextpage);
	    	$_SESSION[$md5str] = $nextpage;
	    	 
	    	$login_url =  $login_url . "?service=" . urlencode($service.'?nextpage='.$md5str);
	    	
	    	redirect($login_url.'&redirect=true');
	    }
   		unset($_SESSION[$cas_return_key]);
   		//未授权页面，直接跳转到首页
   		if(!CASRestful::isSessionAuthenticated()){
   			redirect(C('LOGIN_URL'));
   		}
    }
	
	/**
	 * 重设访问对象的用户信息 主要用于重写等地方
     * @return void
	 */
	public function reinitUser($uid=''){
		if(empty($uid) || $this->mid == $uid){
			return true;
		}
		
		$GLOBALS['ts']['uid'] = $_REQUEST['uid']  = $this->uid =	$uid;
		$GLOBALS['ts']['_user'] = model('User')->getUserInfo($this->uid);
		//当前用户的所有已添加的应用
		$GLOBALS['ts']['_userApp']  = $userApp = model('UserApp')->getUserApp($this->uid);
		//当前用户的统计数据
		$GLOBALS['ts']['_userData'] = $userData = model('UserData')->getUserData($this->uid);
		$userCredit = model('Credit')->getUserCredit($this->uid);
		
		$this->assign('uid', $this->uid);	//访问对象
		$this->assign('_userData',$userData);
		$this->assign('_userApp',$userApp);
		$this->assign('userCredit',$userCredit);
	}
    
    /**
     * 魔术方法 有不存在的操作的时候
     * @access public
     * @param string $method 方法名
     * @param array $parms
     * @return mix
     */
    public function __call($method,$parms) {
        if( 0 === strcasecmp($method,ACTION_NAME)) {
            // 检查扩展操作方法
            $_action = C('_actions_');
            if($_action) {
                // 'module:action'=>'callback'
                if(isset($_action[MODULE_NAME.':'.ACTION_NAME])) {
                    $action  =  $_action[MODULE_NAME.':'.ACTION_NAME];
                }elseif(isset($_action[ACTION_NAME])){
                    // 'action'=>'callback'
                    $action  =  $_action[ACTION_NAME];
                }
                if(!empty($action)) {
                    call_user_func($action);
                    return ;
                }
            }
            // 如果定义了_empty操作 则调用
            if(method_exists($this,'_empty')) {
                $this->_empty($method,$parms);
            }else {
                // 检查是否存在默认模版 如果有直接输出模版
                	$this->display();
            }
        }elseif(in_array(strtolower($method),array('ispost','isget','ishead','isdelete','isput'))){
            return strtolower($_SERVER['REQUEST_METHOD']) == strtolower(substr($method,2));
        }else{
            throw_exception(__CLASS__.':'.$method.L('_METHOD_NOT_EXIST_'));
        }
    }

    /**
     * 模板Title
     * @access public
     * @param mixed $input 要
     * @return
     */
    public function setTitle($title = '') {
        Addons::hook('core_filter_set_title', $title);
		$this->assign('_title',$title);
	}

    /**
     * 模板keywords
     * @access public
     * @param mixed $input 要
     * @return
     */
    public function setKeywords($keywords = '') {
        $this->assign('_keywords',$keywords);
    }

    /**
     * 模板description
     * @access public
     * @param mixed $input 要
     * @return
     */
    public function setDescription($description = '') {
        $this->assign('_description',$description);
    }

    /**
     * 模板变量赋
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的
     * @return void
     */
    public function assign($name,$value='') {
        if(is_array($name)) {
            $this->tVar   =  array_merge($this->tVar,$name);
        }elseif(is_object($name)){
            foreach($name as $key =>$val)
                $this->tVar[$key] = $val;
        }else {
            $this->tVar[$name] = $value;
        }
    }

    /**
     * 魔术方法：注册模版变量
     * @access protected
     * @param string $name 模版变量
     * @param mix $value 变量值
     * @return mixed
     */
    public function __set($name,$value) {
        $this->assign($name,$value);
    }

    /**
     * 取得模板显示变量的值
     * @access protected
     * @param string $name 模板显示变量
     * @return mixed
     */
    protected function get($name) {
        if(isset($this->tVar[$name]))
            return $this->tVar[$name];
        else
            return false;
    }

    /**
     * Trace变量赋值
     * @access protected
     * @param mixed $name 要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function trace($name,$value='') {
        if(is_array($name))
            $this->trace   =  array_merge($this->trace,$name);
        else
            $this->trace[$name] = $value;
    }

    /**
     * 模板显示
     * 调用内置的模板引擎显示方法
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类
     * @return voi
     */
    protected function display($templateFile='',$charset='utf-8',$contentType='text/html') {
        echo $this->fetch($templateFile,$charset,$contentType,true);
    }

    /**
     *  获取输出页面内容
     * 调用内置的模板引擎fetch方法
     * @access protected
     * @param string $templateFile 指定要调用的模板文件
     * 默认为空 由系统自动定位模板文件
     * @param string $charset 输出编码
     * @param string $contentType 输出类
     * @return strin
     */
    protected function fetch($templateFile='',$charset='utf-8',$contentType='text/html',$display=false) {
        $this->assign('appCssList',$this->appCssList);
        $this->assign('langJsList', $this->langJsList);
        Addons::hook('core_display_tpl', array('tpl'=>$templateFile,'vars'=>$this->tVar,'charset'=>$charset,'contentType'=>$contentType,'display'=>$display));
        return fetch($templateFile, $this->tVar, $charset, $contentType, $display);
    }

    /**
     * 操作错误跳转的快捷方
     * @access protected
     * @param string $message 错误信息
     * @param Boolean $ajax 是否为Ajax方
     * @return voi
     */
    protected function error($message,$ajax=false) {
        Addons::hook('core_filter_error_message', $message);
        $this->_dispatch_jump($message,0,$ajax);
    }

    protected function page404($message){
        $this->assign('site_closed',$this->site['site_closed']);
        $this->assign('message',$message);
        $this->display(THEME_PATH.'/page404.html');
    }
    /**
     * 操作成功跳转的快捷方
     * @access protected
     * @param string $message 提示信息
     * @param Boolean $ajax 是否为Ajax方
     * @return voi
     */
    protected function success($message,$ajax=false) {
        Addons::hook('core_filter_success_message', $message);
        $this->_dispatch_jump($message,1,$ajax);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $info 提示信息
     * @param boolean $status 返回状态
     * @param String $status ajax返回类型 JSON XML
     * @return void
     */
    protected function ajaxReturn($data,$info='',$status=1,$type='JSON') {
        // 保证AJAX返回后也能保存日志
        if(C('LOG_RECORD')) Log::save();
        $result  =  array();
        $result['status']  =  $status;
        $result['info'] =  $info;
        $result['data'] = $data;
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        if(strtoupper($type)=='JSON') {
            // 返回JSON数据格式到客户端 包含状态信息
            header("Content-Type:text/html; charset=utf-8");
            exit(json_encode($result));
        }elseif(strtoupper($type)=='XML'){
            // 返回xml格式数据
            header("Content-Type:text/xml; charset=utf-8");
            exit(xml_encode($result));
        }elseif(strtoupper($type)=='EVAL'){
            // 返回可执行的js脚本
            header("Content-Type:text/html; charset=utf-8");
            exit($data);
        }else{
            // TODO 增加其它格式
        }
    }

    /**
     * Action跳转(URL重定向） 支持指定模块和延时跳转
     * @access protected
     * @param string $url 跳转的URL表达式
     * @param array $params 其它URL参数
     * @param integer $delay 延时跳转的时间 单位为秒
     * @param string $msg 跳转提示信息
     * @return void
     */
    protected function redirect($url,$params=array(),$delay=0,$msg='') {
        if(C('LOG_RECORD')) Log::save();
        $url    =   U($url,$params);
        redirect($url,$delay,$msg);
    }

    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param Boolean $ajax 是否为Ajax方式
     * @access private
     * @return void
     */
    private function _dispatch_jump($message,$status=1,$ajax=false) {
        // 判断是否为AJAX返回
        if($ajax || $this->isAjax()) {
            $data['jumpUrl'] = false;
            if($this->get('jumpUrl')){
                $data['jumpUrl'] = $this->get('jumpUrl');
            }
            $this->ajaxReturn($data,$message,$status);
        }
        // 提示标题
        $this->assign('msgTitle',$status? L('_OPERATION_SUCCESS_') : L('_OPERATION_FAIL_'));
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        $this->assign('status',$status);   // 状态
        empty($message) && ($message = $status==1?'操作成功':'操作失败');
        $this->assign('message',$message);// 提示信息
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        if($status) { //发送成功信息
            // 成功操作后默认停留1秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"2");
            // 默认操作成功自动返回操作前页面
            if(!$this->get('jumpUrl')) $this->assign("jumpUrl",$_SERVER["HTTP_REFERER"]);
            //sociax:2010-1-21
			//$this->display(C('TMPL_ACTION_SUCCESS'));
			$this->display(THEME_PATH.'/success.html');
		}else{
            //发生错误时候默认停留3秒
            if(!$this->get('waitSecond'))    $this->assign('waitSecond',"5");
            // 默认发生错误的话自动返回上页
            if(!$this->get('jumpUrl')) $this->assign('jumpUrl',"javascript:history.back(-1);");
			//sociax:2010-1-21
            //$this->display(C('TMPL_ACTION_ERROR'));

			$this->display(THEME_PATH.'/success.html');
        }
        if(C('LOG_RECORD')) Log::save();
        // 中止执行  避免出错后继续执行
        exit ;
    }

    /**
     * 是否AJAX请求
     * @access protected
     * @return bool
     */
    protected function isAjax() {
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) ) {
            if('xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
                return true;
        }
        if(!empty($_POST[C('VAR_AJAX_SUBMIT')]) || !empty($_GET[C('VAR_AJAX_SUBMIT')]))
            // 判断Ajax方式提交
            return true;
        return false;
    }

    /**
     * @param $appType  业务分类  见枚举类型《业务系统分类》
     * @param $appId  业务系统Id 见枚举类型《业务系统分类》
     * @param $opType 操作类型   见枚举类型《日志操作动作》
     * @param $resId  资源ID
     * @param $localStorage 资源当前存储位置 01（本地服务器），02（附件服务），03（资源网关），04(网盘网关)
     * @param $destStorage 若分享时资源有ID有变动时资源所存放位置01（本地服务器），02（附件服务），03（资源网关），04(网盘网关)
     * @param $destResId  分享后的资源ID
     * @param $resName 资源的名称
     * @param $mid  资源用户中间ID，因SNS用户ID未使用用户中心用户ID，则使用映射ID
     * @param $uName 用户loginName
     * @param $nickName 昵称
     * @param $role 角色名称
     * @param $url 资源预览URL，当为删除操作时不需要url     暂去除此字段
     * @param $comment 操作轨迹记录：如（张三于2016年02月26日创建了[我创建的一个新圈子]圈子。）
     * @param $strExt1
     * @param $strExt2
     * @param $strExt3
     * @param $strExt4
     * @return \epdcloud\epsp\api\LogObject
     */
     protected function getLogObj($appType,$appId,$opType,$resId,$localStorage,$destStorage,$destResId,$resName,$mid,$uName,$nickName,$role,$url,$comment,$destUId,$strExt1,$strExt2,$strExt3,$strExt4){
        $logObj = new stdClass();
        $logObj->appType = strval($appType);
        $logObj->appId = strval($appId);
        $logObj->opType = strval($opType);
        $logObj->resId = strval($resId);
        $logObj->localStorage = strval($localStorage);
        $logObj->destStorage = strval($destStorage);
        $logObj->destResId = strval($destResId);
        $logObj->resName = strval($resName);
        $logObj->mid = strval($mid);
        $logObj->uName = strval($uName);
        $logObj->nickName = strval($nickName);
        $logObj->role = strval($role);
        $logObj->ip = strval(get_client_ip());
        //$logObj->url = strval($url);
        $logObj->comment = strval($comment);
        $logObj->isdel = strval(0);
        $logObj->createTime = time()*1000;
        $logObj->strExt1 = strval($strExt1);
        $logObj->strExt2 = strval($strExt2);
        $logObj->strExt3 = strval($strExt3);
        $logObj->strExt4 = strval($strExt4);
         $logObj->destUId =strval($destUId);
        return $logObj;
    }



    protected  function  attchSave($module,$action){
        if (!empty($_FILES)) {
            try {
                $_login = $GLOBALS['ts']['user']['login'];
                if (!$_login) {
                    exit('{"status":0,"data":"终止非法操作！"}');
                }
                $file = $_FILES['uploadfile']['tmp_name'];
                $fileParts = pathinfo($_FILES['uploadfile']['name']);
                $name = $fileParts['basename'];
                $attachInfo = array();
                $attachInfo['save_path'] = date('Y-m-d');
                $attachInfo['extension'] = $fileParts['extension'];
                $attachInfo['save_name'] = uniqid() . '.' . $fileParts['extension'];

                $dirName = UPLOAD_PATH . '/' . $attachInfo['save_path'];
                //	$targetFile = rtrim($targetFolder,'/') . '/' . md5($fileParts['filename']) . '.' . $fileParts['extension'];
                $targetFile = $dirName . '/' . $attachInfo['save_name'];
                @mkdir($dirName, 0777, true);
                $m = move_uploaded_file($file, $targetFile);

                $res_info->general->title = $fileParts['filename'] . '.' . $fileParts['extension'];

                Log::write($_login . "开始上传附件" . $fileParts['filename'] . '.' . $fileParts['extension'], Log::DEBUG);
//本地地址
//				$savaPath =realpath(UPLOAD_URL).'/'.$attachInfo['save_path'].'/'.$info["Filedata"]["savename"];
                //$filePath = md5($_login . time() . rand(1, 1000)) . "/" . $_FILES['uploadfile']["size"] . "/" . $fileParts['filename'] . '.' . $fileParts['extension'];
//				$convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array(  "format"=>"flv"))]);
                $filePath = C("CLIENT_APP_NAME"). "/" . $fileParts['filename'] . '.' . $fileParts['extension'];
                try {
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath);
//					$result = AttachServer::getInstance()->upload($targetFile, $filePath,false,$convertParams);

                    $params = array(
                        "filePath" => $filePath,
                        "callbackMethod" => "GET",
                        "callbackUrl" => $this->host,
                        "module" => $module,
                        "action" => $action,
                        "userId" => $GLOBALS['ts']['user']["cyuid"]
                        /*                        "callbackParams" => "k1=v1&k2=v2"*/
                    );
                    $before_info = $this->apis_client->uploadFileOnBefore($params);
                    $file_info = $this->apis_client->uploadFile($before_info->contextId, $targetFile);
                    $previewId = $file_info->contextId;

                    //上传至附加网关
                    if($_REQUEST['attach_type']=="feed_file") {
                        /*$data['data']['preview_id'] ="";
                        $flvname =substr($attachInfo['save_name'], 0,strripos($attachInfo['save_name'], '.')).'.flv';
                        $filePath = md5(time().rand(1,1000)) . "/" .$_FILES['Filedata']["size"]."/". $flvname;
                        //转换类型;系统默认
                        $convertParams =array('type'=>'custom',"options" => [array("action"=> "video2video","destination"=> $filePath, "parameters"=>array("format"=>"flv"))]);*/
                        //上传至附加网关

                        $convertParams = array(
                            "contextId" => $before_info->contextId,
                            "convType" => 16,
                            "convParams" => "format=flv"
                            /* "convCallbackUrl" => $this->host,
                             "convCallbackMethod" => "GET",
                             "convCallbackProtocol" => "HTTP"*/
                        );
                        $result =  $this->apis_client->startConvert($convertParams);
                        $previewId = $result->converts["0"]->convId;
                        //$result = \AttachServer::getInstance()->upload($targetFile,$filePath,false,$convertParams);
                        if(isset($result->contextId)){
                            $data['data']['preview_id'] = $result->contextId;
                        }
                    }

                    if (file_exists($targetFile)) {
                        @unlink($targetFile);
                    }
                    Log::write("result:" . json_encode($file_info));

                    return ["result" =>$file_info,"name"=>$fileParts['filename']. $fileParts['extension'],"extension"=>$fileParts['extension']];

                    exit;
                } catch (Exception $e) {
                    Log::write($e->getMessage());
                }

            }catch (Exception $e){
                Log::write($e->getMessage());
            }
        }
        return null;
    }

  /*  protected function getConment($op,){

    }*/
};//类定义结束