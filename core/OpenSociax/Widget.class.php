<?php
/**
 * ThinkSNS Widget类 抽象类
 * @author liuxiaoqing <liuxiaoqing@zhishisoft.com>,liu21st <liu21st@gmail.com>
 * @version TS3.0 only
 */
use CyStorage\V2;
abstract class Widget {

	// 使用的模板引擎 每个Widget可以单独配置不受系统影响
	protected $template = '';
	protected $attr = array ();
	protected $cacheChecked = false;
	protected $mid;
	protected $uid;
	protected $user;
	protected $site;
	protected $roleEnName = null;
    protected $sensitiveWord_svc;
    protected $apis_client;
	/**
	 * 渲染输出 render方法是Widget唯一的接口
	 * 使用字符串返回 不能有任何输出
	 * @access public
	 * @param mixed $data  要渲染的数据
	 * @return string
	 */
	abstract public function render($data);

	/**
	 * 架构函数,处理核心变量
	 * 使用字符串返回 不能有任何输出
	 * @access public
	 * @return void
	 */
	public function __construct(){
        $this->appkey = C("CLIENT_APP_NAME");
        $this->appsecret = C("CLIENT_APP_SECRET");
        $this->host = C("ATTACH_SERVICE_URL");

        $this->apis_client = new \CyStorage\Apis($this->appkey,$this->appsecret,$this->host);
		//当前登录者uid
		$GLOBALS['ts']['mid'] = $this->mid =	intval($_SESSION['mid']);
		
		//当前访问对象的uid
		//$GLOBALS['ts']['uid'] = $this->uid =	intval($_REQUEST['uid']==0?$this->mid:$_REQUEST['uid']);
        if(isset ( $_GET ['cyuid'] ) || !empty ( $_GET ['cyuid'] )){
            //$this->uid = D ( 'User' )->getUidByCyuid(intval($_GET ['cyuid']));
            $cyuid = t($_GET['cyuid']);
            $this->uid = model ( 'User' )->getUidByCyuid($cyuid);
        }else{
            $this->uid = intval($_REQUEST['uid']==0?$this->mid:$_REQUEST['uid']);
        }
        $GLOBALS['ts']['uid'] = $this->uid;
		
		// 赋值当前访问者用户
		//$GLOBALS['ts']['user'] = $this->user = model('User')->getUserInfo($this->mid);
		//if($this->mid != $this->uid){
		//	$GLOBALS['ts']['_user'] = model('User')->getUserInfo($this->uid);
		//}else{
		//	$GLOBALS['ts']['_user'] = $GLOBALS['ts']['user'];
		//}

        //$this->cyuserdata = !empty($this->mid) ? D('CyUser')->getCyUserInfo($this->user['login']) : array();
        $this->cyuserdata = $GLOBALS['ts']['cyuserdata'];
        $this->roleEnName = $this->cyuserdata['rolelist'][0]['name'];
        //支持多角色，获取登录用户当前使用的角色，未登录过则取第一个,无角色返回null
        $this->roleEnName = D("UserLoginRole")->getUserCurrentRole($this->cyuserdata['user']['login'], $this->cyuserdata['rolelist']);
        // 检测用户角色信息
         $roleData = $this->cyuserdata['rolelist'][0];
         $roleMap = D('CyUser')->getMappingRole($roleData['name']);
         if(!empty($roleMap)){
         	//Log::write("CyUser->getMappingRole：".json_encode($roleMap),Log::DEBUG);
         	$this->cyuserdata['rolelist'][0]['id'] = $roleMap->id;
         	$this->cyuserdata['rolelist'][0]['name'] = $roleMap->enName;
         }
         $GLOBALS['ts']['cyuserdata'] = &$this->cyuserdata;//同步更新
		
		//当前用户的所有已添加的应用
		//$GLOBALS['ts']['_userApp']  = $userApp = model('UserApp')->getUserApp($this->uid);
		//当前用户的统计数据
		//$GLOBALS['ts']['_userData'] = $userData = model('UserData')->getUserData($this->uid);
		
		//$this->site = D('Xdata')->get('admin_Config:site');
		//$this->site['logo'] = getSiteLogo($this->site['site_logo']);
		//$GLOBALS['ts']['site'] = $this->site;
        $this->sensitiveWord_svc = new \SensitivewordClient();
		//语言包判断
		if( TRUE_APPNAME != 'public' && APP_NAME != TRUE_APPNAME){
			addLang(TRUE_APPNAME);
		}
		Addons::hook('core_filter_init_widget');
	}

	/**
	 * 渲染模板输出 供render方法内部调用
	 * @access public
	 * @param string $templateFile  模板文件
	 * @param mixed $var  模板变量
	 * @param string $charset  模板编码
	 * @return string
	 */
	protected function renderFile($templateFile = '', $var = '', $charset = 'utf-8') {
		$var['ts'] = $GLOBALS['ts'];
		if (! file_exists_case ( $templateFile )) {
			// 自动定位模板文件
			// $name = substr ( get_class ( $this ), 0, - 6 );
			// $filename = empty ( $templateFile ) ? $name : $templateFile;
			// $templateFile =   'widget/' . $name . '/' . $filename . C ( 'TMPL_TEMPLATE_SUFFIX' );
			// if (! file_exists_case ( $templateFile ))
			throw_exception ( L ( '_WIDGET_TEMPLATE_NOT_EXIST_' ) . '[' . $templateFile . ']' );
		}
		//$template = $this->template ? $this->template : strtolower ( C ( 'TMPL_ENGINE_TYPE' ) ? C ( 'TMPL_ENGINE_TYPE' ) : 'php' );
		$content = fetch($templateFile,$var,$charset);
		return $content;
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
     * @param $url 资源预览URL，当为删除操作时不需要url   暂去除
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
        $logObj->destUId = strval($destUId);
        return $logObj;
    }

    public function getMessage(){

    }
}
?>