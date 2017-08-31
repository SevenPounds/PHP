<?php
/**
 * 应用中心
 * @author hhhsi
 * @version 0.1
 */
class IndexAction extends Action{
	protected $M_Apps = '';
	protected $M_UserApps = '';
	protected $M_AppCategory = '';
	protected $M_AppCenterScore = '';
	protected $prePage = 15;
	protected $login = '';
	protected $roleApps = array();
	protected $clientApps = array();
	/**
	 * 构造函数
	 */
	function __construct() {
		parent::__construct();
		$this->login = $GLOBALS['ts']['user']['login'];
		$this->M_Apps = D("AppcenterApp","appcenter");
		$this->M_UserApps = D("AppcenterUserapps","appcenter");
		$this->M_AppCategory = D("AppcenterCategory","appcenter");
		$this->M_AppCenterScore = D("AppcenterScore","appcenter");
		// 获取应用权限配置
        $appConfig = S("appConfig");
        if(empty($appConfig)){
            $appConfig = include_once('./apps/appcenter/Conf/app.config.php');
            $expire = 60;
            S("appConfig",$appConfig,$expire);
        }
		if($this->login){
			$this->roleApps = $appConfig[$this->roleEnName]['app_space'];
		}
		
		$this->assign('clientApps', C("CLIENT_APPS"));
	}

	/**
	 * 应用中心首页
	 */
	public function index(){
		// 获取热门应用
		$hotApps = $this->M_Apps->getHotApps(3);

          //统计
          $this->operationLog["actionName"]="appcenter";
          $this->operationLog["remark"]="应用中心";
          model("OperationLog")->addOperationLog($this->operationLog);

		// 获取推荐应用
		$recommendApps = $this->M_Apps->getRecommendApps(C('RECOMMEND_APPS'));
		
		// 获取所有应用分类列表
		$appCategory = $this->M_AppCategory->getAllCategory();
		
		// 获取全部应用第一页数据
		$pageInfo = $this->M_Apps->getAppsByCategory(1, $this->prePage);
		$appCount = $pageInfo['count'];
		$paging =  getAppcenterPaging(1,$appCount,$this->prePage,'Appcenter.changeApps');

		$appList = $this->checkIsAdded($pageInfo['list'],$this->roleApps);
		$this->appList = $appList;
		$this->paging = $paging;
		$this->hotApps = $this->checkIsAdded($hotApps,$this->roleApps);
		$this->recommendApps = $this->checkIsAdded($recommendApps,$this->roleApps);
		$this->appCategory = $appCategory;
		$this->display();
	}
	
	/**
	 * 异步获取应用列表模版
	 */
	public function appList(){
		$cid = !empty($_REQUEST['cid']) ? $_REQUEST['cid'] : 0;
		$page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : 1;
		
		// 获取指定类型和分页的数据
		$pageInfo = $this->M_Apps->getAppsByCategory($page, $this->prePage,$cid);
		$appCount = $pageInfo['count'];
		$paging =  getAppcenterPaging($page,$appCount,$this->prePage,'Appcenter.changeApps');

		$this->appList = $this->checkIsAdded($pageInfo['list'],$this->roleApps);
		$this->paging = $paging;
		$this->display();
	}
	
	/**
	 * 用户添加或删除应用操作
	 */
	public function appOperate(){
		$type = $_REQUEST['type'];
		$appid = $_REQUEST['appid'];
		if(!$this->login){
			exit(json_encode(array("statuscode"=>false,"message"=>"请先登录！")));
		}

		if(preg_match("/^\d*$/", $appid) && ($type == 'add' || $type == 'delete')){
			if($type == 'add'){
				// 检查权限
				$app = $this->M_Apps->where(array('appid'=>$appid))->find();
				if(!empty($this->roleApps[$app['app_en_name']])){
					$result = $this->M_UserApps->addApp($this->login,$appid);
					$u_r = $this->M_Apps->setAppCountInfo($appid,'usercount',1);
				}else{
					$result = array("statuscode"=>false,"message"=>"您无添加此应用的权限！");
				}
			}else{
				$result = $this->M_UserApps->deleteApp($this->login,$appid);
			}
			exit(json_encode($result));
		}else{
			exit(json_encode(array("statuscode"=>false,"message"=>"参数错误！")));
		}
	}
	
	/**
	 * 增加用户访问量和使用量
	 */
	public function addAppCount(){
		$appid = $_REQUEST['appid'];
		if(!$appid) {
			$appid = $_POST['appid'];
		}
		$r = $this->M_Apps->setAppCountInfoView($appid);
		if($r> 0){
			$result['statuscode'] = "200";
			$result['message'] = "增加用户和访问量成功！";
		}else {
			$result['statuscode'] = "400";
			$result['message'] = "增加用户和访问量失败！";
		}
		echo json_encode($result);
	}
	
	/**
	 * 增加应用下载量
	 */
	public function addDownloadCount(){
		$appid = $_REQUEST['appid'];
		$d_r = $this->M_Apps->setAppCountInfo($appid,'downloadcount',1);
		if($d_r > 0){
			$result['statuscode'] = "200";
			$result['message'] = "增加下载量成功！";
		}else{
			$result['statuscode'] = "400";
			$result['message'] = "增加下载量失败！";
		}
		echo json_encode($result);
	}
	
	/**
	 * 检查应用列表是否已添加
	 * @param array $apps 要检查的应用列表
	 */
	private function checkIsAdded($apps, $roleApps = array()){
		$appIds = array();
		if($this->login){
			$userApps = $this->M_UserApps->getUserApps($this->login);
			
			// 获取用户已添加的应用id数组
			foreach($userApps as $key=>$val){
				$appIds[] = $val['appid'];
			}

			// 遍历当前应用列表，检查是否已添加应用
			foreach($apps as $key=>&$app){
				$app['is_add'] = in_array($app['appid'],$appIds) ? true : false;
				// 添加应用地址跳转权限
				$userApp = $roleApps[$app['app_en_name']];
				if(!empty($userApp)){
					$app['url'] = $roleApps[$app['app_en_name']]['url'];
					$app['target'] = $roleApps[$app['app_en_name']]['target'] ? true : false;
					$app['access'] = true;
				}else{
					$app['access'] = false;
				}
				$app['is_login'] = true;

                // 智学网url拼接参数
                if ($app['app_en_name'] == 'zhixuewang') {
                    $role_apps = $this->roleApps;
                    $serviceUrl = $role_apps[$app['app_en_name']]['url'];
                    $domain = C('domain');
                    $appid = C('appid');
                    $expires = '1434243600';
                    $time = time();
                    $sessionkey = C('sessionkey');
                    $uid = $GLOBALS['ts']['user']['cyuid'];
                    $signStr = 'appid=' . $appid . 'domain=' . $domain . 'expires=' . $expires . 'iframe=1sessionkey=' . $sessionkey . 'time=' . $time . 'user=' . $uid . '暂不定义，最好采用毫无意义的随机串';
                    $sign = md5($signStr);
                    $url = $serviceUrl . 'iframe=1&time=' . $time . '&appid=' . $appid . '&domain=' . $domain . '&user=' . $uid . '&sessionkey=' . $sessionkey . '&expires=' . $expires . '&sig=' . $sign;
                    $app['url'] = $url;
                }
                // 智学网url拼接参数 end
			}
		}
		return $apps;
	}
	
	/**
	 * 应用二级页面
	  *2014-9-28
	 */
	public function appDetail(){
    $appid=$_REQUEST['appid'];
		//每页显示评论记录的个数
		$limit=4;		
		//当前应用
		$app=$this->M_Apps->getAppById($appid);
		//推荐应用,只获取前三条
		$recomdApps=$this->M_Apps->getRecommendApps(C('RECOMMEND_APPS'));
		//获取相关应用
		$apps=$this->M_Apps->getAppsByCategory(0,4,$app['cid']);
		//获取评论
		$commentLists = D('AppcenterComment')->getComments(array('rowid'=>$appid),1,$limit);
		//评论分页展示
		$commentCounts = D('AppcenterComment')->getCommentCount(array('rowid'=>$appid));
		//获取该应用是否评论
	    $hasScore=$this->M_AppCenterScore->hasScore($appid,$this->login);
	    $this->assign('hasScore',$hasScore);
		$page=getAppcenterPaging(1,$commentCounts,$limit,'appDetail.getCommentsByPage');
		$this->assign("commentsPages",$page);
		$this->assign("commentLists",$commentLists);
		$this->assign('apps',$this->checkIsAdded($apps['list'],$this->roleApps));
		$this->assign('recomdApps',$this->checkIsAdded(array_slice($recomdApps,0,3),$this->roleApps));
		$this->assign('app',$app);
		$this->display();
	}
	
	/**
	 * 对应用进行评分
	  *2014-9-28
	 */
	public function addScore() {
		if(empty($this->login)){
			exit(json_encode(array('status'=>'501','message'=>'请先登录')));
		}
		$appid=$_REQUEST['appid'];
		$score=$_REQUEST['score'];
		$app=$this->M_Apps->getAppById($appid);
		$count=$this->M_AppCenterScore->getCountByApp($appid);
		$newScore=($app['score']*$count+$score)/($count+1);
		$newScore=round($newScore, 1);
		$this->M_Apps->updateAppScore($appid,$newScore);
	    $res=$this->M_AppCenterScore->addScoreRecord(array("login"=>$this->login,"appid"=>$appid));
		exit(json_encode($res));
		
	}
	

	/**
	 * 应用评论
	 */
	public function comment(){	
		// 评论内容
		$content = $_POST['content'];	
		// 资源id
		$rowid = $_POST['appid'];			
		// 被回复的评论id
		$commentId = empty($_POST['commentId'])?0:$_POST['commentId'];	
		// 被回复的评论作者
		$toLogin = empty($_POST['toLogin'])?'':$_POST['toLogin'];		
		// 当前登录用户的登录账号
		if(empty($this->login)){
			exit(json_encode(array('status'=>'501','message'=>'请登录')));
		}	
		if(empty($content)){
			exit(json_encode(array('status'=>'502','message'=>'请填写评论内容')));
		}	
		if(empty($rowid)){
			exit(json_encode(array('status'=>'500','message'=>'网络连接错误...')));
		}	
		$data['content'] = htmlspecialchars($content);
		$data['content_origin'] = $data['content'];
		$sensitiveWord = $this->sensitiveWord_svc;
		$contentReplace = $sensitiveWord->checkSensitiveWord($data['content']);
		$contentReplace = json_decode($contentReplace,true);
		if($contentReplace["Code"]!=0){
			return;
		}
		$data['content'] = $contentReplace['Data'];
		$data['login'] = $this->login;
		$data['to_comment_id'] = $commentId;
		$data['to_login'] = $toLogin;
		$data['rowid'] = $rowid;
		$data['app']='appcenter';
		$res = D('AppcenterComment')->comment($data);
		if($res){
		   exit(json_encode(array('status'=>'200','message'=>'评论成功')));
		}			
	}

	/**
	 * 获取评论列表
	  *2014-9-29
	 */
	public function appComments(){
		// 应用id
		$condition['rowid'] = $_POST['appid'];
		// 当前页
		$page = empty($_POST['page'])?'1':$_POST['page'];		
		$limit = 4;
		$commentLists = D('AppcenterComment')->getComments($condition,$page,$limit);
		if(empty($commentLists)){
			$commentCounts = 0;
		}else{
			$commentCounts = D('AppcenterComment')->getCommentCount($condition);
		}
		$page=getAppcenterPaging($page,$commentCounts,$limit,'appDetail.getCommentsByPage');
		$this->assign("commentsPages",$page);
		
		$this->assign("commentLists",$commentLists);
		$this->assign('commentCounts',$commentCounts);		
		$this->display();
	}
}
?>