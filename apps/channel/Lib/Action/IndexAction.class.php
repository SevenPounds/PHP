<?php
/**
 * 频道首页控制器
 * @author zivss <guolee226@gmail.com>
 * @version TS3.0
 */
class IndexAction extends Action{
	/**
	 * 频道首页页面
	 * @return void
	 */
	public function index()	{
		// 添加样式
		$this->appCssList[] = 'channel.css';
		// 获取频道分类列表
		//获取热门分类;
		$hotcategory=D('Channel','channel')->getHotCategory(3);
		for($i=10;$i<13;$i++){//10小学11中学12专题类
			$channelCategory["cat".$i] = model('CategoryTree')->setTable('channel_category')->getCategoryList($i);
		}
		for($i=10;$i<13;$i++){
			$data=$channelCategory["cat".$i];
			foreach ($data as &$value){
			   $value['hotflag']=in_array($value['channel_category_id'],$hotcategory);
			}
			$channelCategory["cat".$i]=$data;
		}
		$this->assign('channelCategory', $channelCategory);
		$stylearray=array("","one","two","three");//最受欢迎分享样式
		$this->assign("stylearray",$stylearray);
		$userinfo=model ('Avatar')->init($GLOBALS['ts']['uid'])->getUserPhotoFromCyCore($GLOBALS['ts']['uid']);//获取用户头像
		$this->assign('useravatar',$userinfo['avatar_small']);
		//获取用户关注数据
		$isfollow=intval(M('channel_follow')->where('uid='.$this->mid)->count());
		$this->assign('isfollow',$isfollow);
		$followContent= model('ChannelFollow')->getFollowList($GLOBALS['ts']['mid']);
		foreach($followContent as &$value){
			switch ($value['pid']){
				case 10:
					$value['title']='小学'.$value['title'];
					break;
				case 11:
					$value['title']='中学'.$value['title'];
					break;
				default:
					break;
			}
		}
		$this->assign('followContent',$followContent);
		// 频道分类选中
		$cid = intval($_GET['cid']);
		$channelCategoryall=model('CategoryTree')->setTable('channel_category')->getCategoryList();
		$this->assign('channelCategoryall', $channelCategoryall);
		$categoryIds = getSubByKey($channelCategoryall, 'channel_category_id');
		if (!in_array($cid, $categoryIds) && !empty($cid)) {
			$this->error('您请求的频道分类不存在');
			return false;
		}
		$channelConf = model('Xdata')->get('channel_Admin:index');
		if(empty($cid)) {
			$cid = $channelConf['default_category'];
			if (empty($cid)) {
				$cid=-1;//cid为-1代表进入首页sjzhao
				//获取精彩分享数据
				$orderedids= model('Channel')->getOrderedFeedId(10);
				$wonderfeed=model('Feed')->getFeeds($orderedids);
				foreach($wonderfeed as &$value){
					$value['feed_data']=unserialize($value['feed_data']);
				}
				$this->assign('wonderfeed',$wonderfeed);
			}
		}
		$this->assign('cid', $cid);
		// 获取模板样式
		$templete = t($_GET['tpl']);
		$templete = empty($templete) ? 'load':$templete;
		if(empty($templete) || !in_array($templete, array('load', 'list'))) {
			$categoryConf = model('CategoryTree')->setTable('channel_category')->getCatgoryConf($cid);
			$templete = empty($categoryConf) ? (($channelConf['show_type'] == 1) ? 'list' : 'load') : (($categoryConf['show_type'] == 1) ? 'list' : 'load');
		}
		$this->assign('tpl', $templete);
		// 设置页面信息
		$titleHash = model('CategoryTree')->setTable('channel_category')->getCategoryHash();
		$title = empty($cid) ? '知识堂首页' : $titleHash[$cid];
		$this->assign('title',$title);
		$this->setTitle($title);
		$this->setKeywords($title);
		$this->setDescription(implode(',', getSubByKey($channelCategory,'title')));
		$this->display();
	}

	/**
	 * 获取分类数据列表
	 */
	public function getCategoryData(){
		$data = model('CategoryTree')->setTable('channel_category')->getCategoryList();
		$result = array();
		if(empty($data)) {
			$result['status'] = 0;
			$result['data'] = '获取数据失败';
		} else {
			$result['status'] = 1;
			$result['data'] = $data;
		}
		
		exit(json_encode($result));
	}

	/**
	 * 投稿发布框
	 * @return void
	 */
	public function contributeBox(){
		$cid = intval($_GET['cid']);
		$this->assign('cid', $cid);
		// 获取投稿分类信息
		$info = model('CategoryTree')->setTable('channel_category')->getCategoryInfo($cid);
		$title = '投稿到：['.$info['title'].']';
		$this->assign('title', $title);
		// 发布框类型
		$type = array('at', 'topic', 'contribute');
		$actions = array();
		foreach($type as $value) {
			$actions[$value] = false;
		}
		$this->assign('actions', $actions);

		$this->display();
	}
	/**
	 * 显示关注
	 * @return void
	 */
	public function showFollowIng(){
		$uid=intval($_GET['uid']);
		$channelCategory=array();
		$ChnnelFobj=model('ChannelFollow');
		for($i=10;$i<13;$i++){//10小学11中学12专题类
			$channelCategory["cat".$i] = model('CategoryTree')->setTable('channel_category')->getCategoryList($i);
		}
		for($m=10;$m<13;$m++){
			foreach($channelCategory["cat".$m] as $key=>$value){
				$channelCategory["cat".$m][$key]['followstatus']=$ChnnelFobj->getFollowStatus($uid, $value['channel_category_id']);
			}
		}
		$this->assign('channelCategory',$channelCategory);
		$this->display();
	}
	/**
	 * 添加分享链接
	 */
	public function addShareLink(){
		$this->display();
	}
	/**
	 * 添加分享
	 */
	public function addShare(){
		if(empty($_REQUEST['share_link']))
			$this->error("输入链接有误！");
		$error_msg = "";
		$var = array();
		$weiboSet = model('Xdata')->get('admin_Config:feed');
		$weibo_type = $weiboSet['weibo_type'];
		$weibo_premission = $weiboSet['weibo_premission'];
		// 权限控制
		$type = array('face', 'at', 'image', 'video', 'file', 'topic', 'contribute');
		foreach($type as $value) {
			!isset($actions[$value]) && $actions[$value] = true;
		}
		$link = $_REQUEST['share_link'];
		//不能添加本站地址
		if(strpos($link, SITE_URL) !== false ){
			var_dump(strpos($link, SITE_URL));
			$this->assign("error_msg", "不能添加本站地址！");
			$this->display();
			return;
		}
		//URL格式验证
		if(preg_match('/^(http|https|ftp)://([A-Z0-9][A-Z0-9_-]*(?:.[A-Z0-9][A-Z0-9_-]*)+):?(d+)?/?/i', $link)){
			$this->assign("error_msg", "链接格式不对");
			$this->display();
			return;
		}
		//获取链接所指网页内容
		if(extension_loaded ('curl')) {
			$ch = curl_init(trim ($link ));
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$content = curl_exec($ch);
			curl_close($ch);
		} else {
			$content = @file_get_contents(trim ($link ));
		}
		if(!$content || !strpos($content, "body")){
			$this->assign("error_msg", "无法解析该链接！");
			$this->display();
			return;
		}
		//获取链接所指网页标题
		preg_match('|<title>(.*?)<\/title>|i',$content, $arr1);
		$title = $arr1[1];
		//获取链接编码方式
		preg_match('|charset=(.*?)\"|i',$content, $char_set);
		if($char_set[1] && $char_set[1] != 'utf-8' && $char_set[1] != 'utf-8'){
			$title = mb_convert_encoding($title, "utf-8", $char_set[1]);
		}
		$this->assign("type", 'share');
		$this->assign("app_name", 'public');
		$this->assign("source_url", $link);
		$this->assign("initHTML", '我分享了一个链接，标题为：'.$title);
		$this->assign("title", $title);
		$this->assign("link", $link);
		$this->assign('weibo_type', $weibo_type);
		$this->assign('weibo_premission', $weibo_premission);
		$this->assign('actions', $actions);
		$this->display();
	}
	
}