<?php
/*
* 资源平台资源汇聚页 相关服务的客户端
*/

/**
 * 
 * @author yangli4
 *
 *	测试例子
 * $client = new UserCredit_Client('http://localhost/Space/api/spaceApp.php');
 * $res = $client->getOnlineAnswers(0,10,"answer_count desc");
 * echo json_encode($res);
 */
class SpaceApp_Client{
	private $client = null;
	/**
	 * 
	 * @param unknown_type $url 空间应用服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}

	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “在线答疑”
	 */
	function getOnlineAnswers($start = 0,$limit = 10,$order = "answer_count desc"){
		return $this->client->getOnlineAnswers($start,$limit,$order);
	}

	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “课题研究（主题讨论）”
	 */
	function getResearchInfos($start = 0,$limit = 10,$order){
		return $this->client->getResearchInfos($start,$limit,$order);
	}

	
	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “优秀空间”
	 * $roleName 角色名称：	PROVINCE_RESAERCHER			//省级教研员
	 * 						CITY_RESAERCHER				//市级教研员
	 * 						COUNTY_RESAERCHER			//区县级教研员
 	 * 						RESAERCHER					//教研员(传入这个值，按需求将查询 RESAERCHER，COUNTY_RESAERCHER，CITY_RESAERCHER，PROVINCE_RESAERCHER)
	 * 						TEACHER						//教师
	 * 						STUDENT 					//学生
	 * 						PARENTS 					//家长
	 */
	function getExcellentSpaces($start = 0,$limit = 10,$orderkey = 'follower_count',$orderDir='desc',$roleName='TEACHER'){
		return $this->client->getExcellentSpaces($start,$limit,$orderkey,$orderDir,$roleName);
	}

	/**
	 * @param int $limit
	 */
	function getHotMsGroup($limit = 4){
		return $this->client->getHotMsGroup($limit);
	}
	
	/**
	 * 根据起始位置，查询最大数量，以及排序字段，查询 “名师工作室”
	 */
	function getMingshiSpaces($start = 0,$limit = 10,$order = "follower_count" ,$orderDir='desc'){
		return $this->client->getMingshiSpaces($start,$limit,$order,$orderDir);
	}

	/**
	 * 通过id，获取个人空间或名师工作室信息
	 * 
	 * @param array $sids 编号数组(类型+编号)
 	 * @param int $host_login_name 查询者登陆用户名，有此条件则查询是否关注过返回的空间
	 */
	function getSpaceDetailBySids($sids,$host_login_name){
		return $this->client->getSpaceDetailBySids($sids,$host_login_name);
	}

	/**
	 * 关注空间
	 * 
	 * @param int $type 搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
	 * @param int $tid 目标id
	 * @param int $host_login_name 关注者登陆用户名
	 */
	function spaceFollow($type=1,$tid,$host_login_name){
		return $this->client->spaceFollow($type,$tid,$host_login_name);
	}

	/**
	 * 取消关注
	 * 
	 * @param int $type 搜索类型：1,教师；2,教研员；3,学生；4,家长；5,名师工作室
	 * @param int $tid 目标id
	 * @param int $login_name 关注者登陆用户名
	 */
	function spaceUnfollow($type=1,$tid,$host_login_name){
		return $this->client->spaceUnfollow($type,$tid,$host_login_name);
	}


	/**
	 * 添加一条用户改密验证信息
	 * @param array $data 验证信息数组，
	 *				$data['ctime'],提交时间；
	 *				$data['loginname'],用户登录名；
	 *				$data['key'],验证信息；
	 */
	function addUserPwdVerify($data){
		return $this->client->addUserPwdVerify($data);
	}

	/**
	 * 删除户改密验证信息
	 * @param array $map 条件数组
	 */
	function delUserPwdVerify($map){
		return $this->client->delUserPwdVerify($map);
	}


	/**
	 * 获取用户改密验证信息
	 * @param string $loginname 用户登录名
	 */
	function getUserPwdVerify($loginname){
		return $this->client->getUserPwdVerify($loginname);
	}

	/**
	 * 资源收藏
	 * @param string $login_name 用户名
	 * @param string $rid 资源id
	 * @return array('status'=>true|false,'message'=>'xx')
	 */
	function collectResource($login_name,$rid){
		return $this->client->collectResource($login_name,$rid,false);
		
	}

	/**
	 * 资源包收藏
	 * @param string $login_name 用户名
	 * @param string $rid 资源id
	 * @return array('status'=>true|false,'message'=>'xx')
	 */
	function collectResPackage($login_name,$rid){
		return $this->client->collectResource($login_name,$rid,true);
	}
	/**
	 * 修改云盘用户上传资源数
	 * @param string $login_name
	 * @param int $resCount
	 */
	function modifyUploadYunpanCount($login_name, $resCount){
		$this->client->modifyUploadYunpanCount($login_name, $resCount);
	}
	
	/**
	 * 修改云盘已使用容量
	 * @param string $login_name
	 * @param int $size
	 */
	function modifyYunpanUsedSize($login_name, $size){
		$this->client->modifyYunpanUsedSize($login_name, $size);
	}
	
	/**
	 * 向用户发送消息
	 * @param string $loginName
	 * @param string $node
	 * @param string $data
	 * @reutrn array('status'=>true|false,'msg'=>'xx')
	 */
	function sendNotify($loginName, $node, $data){
		return $this->client->sendNotify($loginName, $node, $data);
	}
	
	/**
	 * 根据条件获取用户角色映射表
	 * @param array $conditions 查询的条件
	 * @return array() 用户映射表数组
	 */
	function listRoleMap($conditions = array()){
		return $this->client->listRoleMap($conditions);
	}
	
	/**
	 * 根据用户登录名和资源id发布动态
	 * @param string $login 用户登录名
	 * @param string $app 应用名称，如：yunpan
	 * @param string $content 动态内容
	 * @param string $body 主题部分，例如：'我上传了资源【'.$file_name.'】'
	 * @param string $source_url 资源访问链接
	 * @return 返回调用结果
	 */
	function addAuditFeed($login, $app, $content, $body, $source_url){
		return $this->client->addAuditFeed($login, $app, $content, $body, $source_url);
	}
	
	/**
	 * 根据用户登录名初始化用户空间
	 * @param string $login 用户登录名
	 * @return 用户空间uid
	 */
	function initUserSpace($login){
		return $this->client->initUserSpace($login);
	}

    function shareFeed($login, $body,$rid,$content='',$app='changyan'){
        return $this->client->shareFeed($login, $body,$rid,$content,$app);
    }
    
    /**
     * 判断用户是否开通了个人空间
     */
    function hasUserSpace($login){
    	return $this->client->hasUserSpace($login);
    }
    
    /**
     * 同步资源分享动态
     * $login : 登录名
     * $body : 内容主题
     * $rid : 资源id
     * $url : 预览地址
     */
    function shareFeedComplete($uid, $body,$rid,$url,$content='',$app='changyan'){
    	return $this->client->shareFeedComplete($uid, $body,$rid,$content,$app,$url);
    }
    
    /**
	 * 获取分享资源到学科资源排前几名的用户相关信息
	 * @param int $top 分享排前几名的
	 * @param int $limit 查询指定数量用户分享的记录
	 * @return array(array('login_name'=>'zhangtest','count'=>'120','list'=>array()));
	 */
	function getPublishTop($top, $limit){
		return $this->client->getPublishTop($top, $limit);
	}

	
}

// test codes
// define('SITE_PATH', dirname(dirname(__FILE__)));
// require_once SITE_PATH . '/core/core.php';
// $spClient = new SpaceApp_Client('http://192.168.62.148/ChangYan/App.EduSNS/api/spaceApp.php');
// $res = $spClient->getPublishTop(3,12);
// echo json_encode($res);
?>			