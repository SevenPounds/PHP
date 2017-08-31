<?php
/**
 * 资源平台手机服务客户端
 * @author yangli4@iflytek.com
 * @author yuliu2@iflytek.com
 */



class MobileSpaceClient{
	private $client = null;
	/**
	 * 
	 * @param String $url 服务地址
	 */
	function __construct($url){
		$this->client = new PHPRPC_Client($url);
	}
	
	
	/**
	 * 发布说说/微博
	 *
	 * @param int $uid 			用户uid；
	 * @param string $appName 	当前产生说说/微博所属的应用
	 * @param string $type 		发送说说/微博的类型,默认为‘post’;
	 *							如含有图片附件，则它为‘postimage’;
	 *							如含有其他附件，则它为‘postfile’;
	 * @param array $d 			说说/微博信息数组：
	 *				$d['content']，用户发送内容
	 *				$d['body']，原始数据内容
 	 *				$d['attach_id']，附件id，支持多附件，"|"分割，如：|1234|1235|
	 */
	function sendBlog($uid, $appName, $type, $d){
		return $this->client->sendBlog($uid, $appName, $type, $d);
	}
	
	/**
	 * 提供给手机端的评论服务
	 * app_name根据被评论内容来源相关、table_name是内容存储在哪张表中;
	 * 如果是未评论他人的评论,to_comment_id,to_uid均可以不设置;
	 * array('app_name'=>'应用名称','table_name'=>'被评论内容表','row_id'=>'被评论内容id','app_uid'=>'被评论内容用户uid',
	 * 		'uid'=>'评论者uid','content'=>'评论内容','to_comment_id'=>'被评论的评论id','to_uid'=>'被评论用户的uid','client_type'=>2)
	 * @param array $data
	 * @return 	array ('status' => 0 or 1,'data' => '操作提示内容')
	 */
	function addComment($data){
		return $this->client->addComment($data);
	}

	/**
	 * 转发服务
	 *
	 *
	 * @param int $uid 			用户uid；
	 * @param array $d 			转发信息数组：
	 *				$d['body']，转发评论内容
	 *				$d['sid']， 信息最原始id
	 *				$d['curid']，被转发的信息id
	 *				$d['app_name']，应用名称
	 */
	function shareFeed($uid, $d){
		return $this->client->shareFeed($uid, $d);
	}

	/**
	 * 私信服务
	 *
	 * @param int $uid 			发送者uid；
	 * @param int $toUid		接收者uid;
	 * @param string $content	私信内容;
	 *
	 */
	function sendPrivateLetter($uid,$toUid,$content){
		return $this->client->sendPrivateLetter($uid,$toUid,$content);
	}
	
}


/********************************************集成代码示例****************************************************/

// $client = new mobileSpaceClient('http://localhost/WebApps/ThinkSNS/api/mobileSpaceService.php');

// echo json_encode($client->sendBlog(894, 'public', 'post', array('body'=>'mobile blog')));

// echo json_encode($client->shareFeed(894, array('body'=>'mobile blog share',
// 												 'sid'=>3616,
// 												 'curid'=>3629,
// 												 'app_name'=>'msgroup')));

// echo json_encode($client->sendPrivateLetter(900, 894, 'mobile private letter'));

// $data = array('uid'=>894,'app_name'=>'reslib','table_name'=>'feed',
// 		'row_id'=>3614,'app_uid'=>556,'content'=>'@rrt研发    你听见了没。','to_comment_id'=>751,'to_uid'=>894);//'to_comment_id'=>0,'to_uid'=>0
// $res = $client->addComment($data);

?>