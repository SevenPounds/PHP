<?php
require_once('BaseModel.class.php');
//    Import( '@.Unit.Common' );
/**
 * BlogModel
 * 迷你日志Model层。操作相关迷你日志的数据业务逻辑
 * @package Model::Blog
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class BlogModel extends BaseModel {
	
	protected $tableName = 'blog';
	protected $error = '';
	protected $fields = array(
			0 => 'id',
			1 => 'uid',
			2 => 'class_id',
			3 => 'name',
			4 => 'title',
            5 => 'title_origin',
			6 => 'category',
			7 => 'category_title',
			8 => 'cover',
			9 => 'content',
            10 => 'content_origin',
			11 => 'readCount',
			12 => 'commentCount',
			13 => 'shareCount',
			14 => 'recommendCount',
			15 => 'tags',
			16 => 'cTime',
			17 => 'mTime',
			18 => 'rTime',
			19 => 'isHot',
			20 => 'type',
			21 => 'status',
			22 => 'private',
			23 => 'private_data',
			24 => 'hot',
			25 => 'canableCommment',
			26 => 'attach',
			27 => 'feed_id'
	);
	
	/**
	 * _type
	 * 日志的种类。默认为0
	 * @var float
	 * @access public
	 */
    public $_type = 0;
    public $cuid = 0;
    public $config = null;

    /**
     * limit
     * 每页显示多少条
     * @var float
     * @access public
     */

    public function _initialize() {
      //初始化只搜索status为0的。status字段代表没被删除的
      $this->status = 1;
      //获取配置
      $this->config = D('AppConfig','blog')->getConfig();
      parent::_initialize();
    }

    public function setCount($appid,$count) {
        $map['id'] = $appid;
        $map2['commentCount'] = $count;
        return $this->where($map)->save($map2);
    }
    /**
     * getBlogList
     * 通过userId获取到用户列表
     * 通过用户Id获取用户心情
     * @param array|string|int $userId
     * @param array|object $options 查询参数
     * @access public
     * @return object|array
     */
    public function getBlogList($map = null, $field=null, $order = null, $limit, $uid) {
      //处理where条件
      $map = $this->merge($map);
      $limit = !empty($limit) ? $limit : 20;
      // 连贯查询.获得数据集
      if(!empty($uid)) {
        $map['_string'] = ' private = 0 ';
        // 获取博主关注的UID
        $blogAuthorUids = model('Follow')->where('uid='.$uid)->field('fid')->findAll();
        $blogAuthorUids = getSubByKey($blogAuthorUids, 'fid');
        if(!empty($blogAuthorUids)) {
          $authorMap = implode(',', $blogAuthorUids);

          if(!empty($map['_string'])) {
            $map['_string'] .= ' OR (uid IN ('.$authorMap.') AND private = 2)';
          } else {
            $map['_string'] .= '(uid IN ('.$authorMap.') AND private = 2)';
          }
        }

        // 仅仅对自己可见
        if(!empty($map['_string'])) {
          $map['_string'] .= ' OR (uid = '.$uid.' AND private = 4)';
        } else {
          $map['_string'] .= ' (uid = '.$uid.' AND private = 4)';
        }
        // 仅对粉丝可见
        
        if(!empty($map['_string'])) {
          $map['_string'] .= ' OR uid='.$uid;
        } else {
          $map['_string'] .= ' uid='.$uid;
        }
      }
      $result = $this->where($map)->field($field)->order($order)->findPage($limit);
      //对数据集进行处理
      $data = $result['data'];
      $data = $this->replace($data); //本类重写父类的replace方法。替换日志的分类和追加日志的提及到的人
      $result['data'] = $data;

      return $result;
    }

    /**
     * getBlogList
     * 通过userId获取到用户列表
     * 通过用户Id获取用户心情
     * @param array|string|int $userId
     * @param array|object $options 查询参数
     * @access public
     * @return object|array
     */
    public function getClassBlogList($map = null, $field=null, $order = null, $limit) {

    	//处理where条件
  //  	$map = $this->merge($map);
    	$limit = !empty($limit) ? $limit : 20;
    	// 连贯查询.获得数据集
    	$result = $this->where($map)->field($field)->order($order)->findPage($limit);
    	//对数据集进行处理
    	return $result;
    }
    
    
    /**
     * getBlogContent
     * 重写父类的getBlogContent
     * @param mixed $id
     * @param mixed $how
     * @param mixed $condition
     * @access public
     * @return void
     */
    public function getBlogContent( $id,$how =null,$condition = array()  ) {
        $result         = parent::getBlogContent( $id,$how,$condition );
        if(false == $result) return false;
        $result['role']  = $this->checkCommentRole( $result['canableComment'],$uid,$this->cuid );
        $result['title'] = stripslashes(t( $result['title'] ));
        $result['attach'] = unserialize($result['attach']);
        return $result;
    }

    public function setUid($value) {
        $this->cuid = $value;
    }
    /**
     * getMentionBlog
     * 获取提到我的好友的帖子数据
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function getMentionBlog( $uid = null ) {
        return false;

    }
    /**
     * 获取班级日志分类
     */
	public function getClassCategory($cid){
		$category	= self::factoryModel( 'Category' );
		$categorycontent = $category->getClassCategory($cid);
		return $categorycontent;
	}
	
    public function getCategory( $uid ) {
        $category        = self::factoryModel( 'Category' );
        if( isset( $uid ) ) {
            $categorycontent = $category->getUserCategory($uid);
        }else {
            $categorycontent = $category->getCategory();
        }
        return $categorycontent;
    }

    /**
     * checkCommentRole
     * 检查是否可以评论
     * @param mixed $role 评论权限
     * @param mixed $userId 日志所有者
     * @access protected
     * @return void
     */
    private function checkCommentRole( $role,$userId,$mid ) {
        if( $userId == $mid ) {
            return 1;
        }
        switch ( $role ) {
            case 1:  //全站可评论
                $result = 1;
                break;
            case 2:  //好友可评论
                if( $this->api->friend_areFriends($mid,$userId) ) {
                    $result = 1;
                }else {
                    $result = 2;

                }
                break ;
            case 3:  //关闭评论
                $result = 3;
                break;
        }
        return $result;
    }
  public function getIsHot($isHot = 1) {  //获取推荐日志...重复//TS_2.0
	  	
	  	if($isHot == 2){
	  		$map['isHot'] = $isHot;
	  	}else{
	  		$map['isHot'] = array('neq',2);
	  	} 
    	//处理where条件
	    $map['status']= 1;
	    $order        = 'rTime DESC';

	        //连贯查询.获得数据集
	    $hotlist = $this->where( $map )->order( $order )->findAll();
        //对数据集进行处理
        //$data           = $result['data'];
        //$data           = $this->replace( $data ); //本类重写父类的replace方法。替换日志的分类和追加日志的提及到的人
        //$data           = intval( $this->config->replay ) ? $this->appendReplay($data):$data;//追加回复
		//dump ($data);
        return $hotlist;
    }
    // 获取日志的数据
    public function getAllData($order, $condition = array()) {
      //TODO 根据条件决定排序方式,尚有优化空间
      $temp_order_map = $this->getOrderMap($order);
      $uid = NULL;
      if(isset($condition['uid'])){
      	$uid = $condition['uid'];
      }
      if(isset($condition['cid'])){
      	$map = array('class_id'=>$condition['cid']);
      	$temp_order_map['map'] = $map;
      }
      //根据以上处理条件获取数据集
      // $temp_order_map['map']['private'] = 0;
      $result = $this->getBlogList($temp_order_map['map'],null,$temp_order_map['order'], null, $uid);
      $result['category'] = $this->getCategory();
      return $result;
    }

    public function getFollowsBlog($mid){
      $followlist = model("Follow")->getFollowingListAll($mid,null);
  		foreach($followlist as $key=>$value) {
      	 $folist[$key]=$value['fid'];
      }
  		$map['uid']  = array('in',$folist);
  		// $map['private'] = 0;
  		$order = 'cTime DESC';
      $result = $this->getBlogList($map,null,$order,null,$mid);
      $result['category'] = $this->getCategory();
      return $result;
    }

    private function getOrderMap($order){
           switch( $order ) {
                case 'hot':    //推荐阅读
                    $map['isHot'] = 1;
                    $order        = 'rTime DESC';
                    break;
                case 'new':    //最新排行
                	$map['isHot'] = array('neq',2);
                    $order = 'cTime DESC';
                    break;
                case 'popular':    //人气排行
                	$map['isHot'] = array('neq',2);
                    // $order        = '((readCount/100)*(commentCount/100)*(cTime/3600)) DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    $order = 'readCount DESC, commentCount DESC , cTime DESC';
                    break;
                case 'read':   //阅读排行
                    $order = 'readCount DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    break;
                case 'comment':   //评论排行
                    $order = 'commentCount DESC';
                    //$map['cTime'] = self::_orderDate( $this->config->allorder );//取得时间
                    break;
                case 'essence':    //精华日志
                    $map['isHot'] = 2;
                    $order = 'commentCount DESC, readCount DESC , cTime DESC';
                    break;                   
                default:      //默认时间排行
                    $order = 'cTime DESC';
            }
            // $map['private'] = array('neq',2);
            $result['map'] = $map;
            $result['order'] = $order;
            return $result;
    }
    /**
     * getClassBlogCategoryCount
     * 根据cid获得班级日志分类的日志数
     * @param mixed $cid
     * @access public
     * @return void
     */
    public function getClassBlogCategoryCount( $cid ) {
    	$sql = "SELECT count( 1 ) as count, category
    	FROM `{$this->tablePrefix}blog`
    	WHERE `category` IN (
    	SELECT `id`
    	FROM {$this->tablePrefix}blog_category
    	WHERE `cid` = 0 OR `cid` = {$cid}
    	) AND `class_id` = {$cid} AND `status` = 1
    	GROUP BY category
    	";
    	$result = $this->query( $sql );
    	return $result;
    }
    /**
     * getBlogCategoryCount
     * 根据uid获得日志分类的日志数
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function getBlogCategoryCount( $uid ) {
        $sql = "SELECT count( 1 ) as count, category
                    FROM `{$this->tablePrefix}blog`
                    WHERE `category` IN (
                                          SELECT `id`
                                          FROM {$this->tablePrefix}blog_category
                                          WHERE `uid` = 0 OR `uid` = {$uid}
                                      ) AND `uid` = {$uid} AND `status` = 1
                                          GROUP BY category
            ";
        $result = $this->query( $sql );
        return $result;
    }
    public function getClassBlogCategory($cid,$cateId) {
    	$list = $this->getClassCategory($cid);
    	$result = $this->getBlogCount($cid,$list,true);
    	if(isset( $cateId ) && !self::_checkCategory( $cateId,$list )) return false;
    	return $result;
    }
    public function getBlogCategory($uid,$cateId) {
        $list = $this->getCategory($uid);
        $result = $this->getBlogCount($uid,$list);
        if(isset( $cateId ) && !self::_checkCategory( $cateId,$list )) return false;
        return $result;
    }
    
    /**
     * 获取日志总数 
     * @param  $id 
     * @param  $is_class
     */
    public function getTotalCount($id,$is_class = false){
    	$_map = array();
    	if($is_class){
    		$_map['class_id'] = $id;
    	}else{
    		$_map['uid'] = $id;
    	}
    	$result = $this->where($_map)->count();
    	return $result;
    }
	/**
	 * $id 班级id或用户id
	 * $is_class 是否是班级
	 */
    public function getBlogCount($id,$list,$is_class = false) {
        $result = $list;
        $count = $is_class ? $this->getClassBlogCategoryCount( $id ) : $this->getBlogCategoryCount( $id );
        //重组数据
        $count_arr = array();
        foreach ( $count as $value ) {
            $count_arr[$value['category']] = $value['count'];
        }
        foreach ($result as &$value) {
            $value['count'] = $count_arr[$value['id']] ? $count_arr[$value['id']] : 0;
        }
        return $result;
    }
    /**
     * _checkCategory
     * 检查分类是否合法
     * @param mixed $cateId
     * @param mixed $category
     * @static
     * @access private
     * @return void
     */
    private static function _checkCategory($cateId,$category ) {
        $temp = array();
        foreach( $category as $value ) {
            $temp[] = $value['id'];
        }
        return in_array($cateId,$temp);
    }

    /**
     * doDeleteBlog
     * 删除Mili日志，检查配置DELETE=true,则真实删除。如果DELETE=false，则是状态为1;
     * @param array|string $map 删除条件
     * @access public
     * @return void
     */
    public function doDeleteBlog( $map = null,$uid=null ) {
    //获得配置信息
        $config    = $this->config['delete'];

        //获得删除条件
        $condition = $this->merge( $map );

        //检测uid是否合法
        $mid = $this->where( $condition )->getField( 'uid' );
        //监测管理员

        $isAdmin = S('UserGroupIsAdmin_'.$uid);
        if(empty($isAdmin)) {
            $isAdmin = model('UserGroup')->isAdmin($uid);
            S('UserGroupIsAdmin_'.$uid,$isAdmin,3600);
        }
        if( isset($uid) && $uid != $mid && !$isAdmin) {
            return false;
        }
        //判断是否合法。不允许删除整个表
        if( !isset( $condition ) && empty( $condition ) )
            throw new ThinkException( "不允许删除整个表" );
        //如果配置文件中delete的值为true则真是删除，如果delete=false,则设置status＝2;
        if( false == $config ) {
            $result = $this->where($condition)->setField('status', 2);
        }
        return $result;
    }

    /**
     * changeCount
     * 修改日志的浏览数
     * @param mixed $blogid
     * @access public
     * @return void
     */
    public function changeCount( $blogid ) {
        $sql = "UPDATE {$this->tablePrefix}blog
                    SET readCount=readCount+1,hot = commentCount*readCount+round(readCount/(commentCount+1),0)
                    WHERE id='$blogid' LIMIT 1 ";
        $result = $this->execute($sql);
        if ( $result ) {
            return true;

        }
        return false;
    }

    /**
     * changeShareCount
     * 修改日志的分享
     * @param mixed $blogid
     * @access public
     * @return void
     */
    public function changeShareCount( $blogid ) {
    	$sql = "UPDATE {$this->tablePrefix}blog	SET shareCount=shareCount+1 WHERE id='$blogid' LIMIT 1 ";
    	$result = $this->execute($sql);
    	if ( $result ) {
    		return true;
    	}
    	return false;
    }
    
    /**
     * fileAway
     * 归档查询
     *
     * @param string|array $findTime 查询时间。
     * @param mixed $condition 查询条件
     * @param Model $object 查询对象
     * @param mixed $limit 查询limit
     * @access public
     * @return void
     */
    public function fileAway($findTime ,$condition = null) {
    //如果是数组。进行的解析不同
        if( is_array( $findTime) ) {
            $start_temp   = $this->paramData( strval($findTime[0] ));
            $end_temp     = $this->paramData( strval($findTime[1] ));

            $start        = $start_temp[0];
            $end          = $end_temp[1];
        }else {
            $findTime  = strval( $findTime );
            $paramTime = self::paramData( $findTime );
            $start     = $paramTime[0];
            $end       = $paramTime[1];
        }
        $this->cTime = array( 'between', array( $start,$end ) );
        //如果查询时没有设置其它查询条件，就只是按时间来进行归档查询
        $map = $this->merge( $condition );
        //查询条件赋值
        $result = $this->where( $map )->field( '*' )->order( 'cTime DESC' )->findPage( $this->config['limitpage']);
        $result['data'] = $this->replace( $result['data'] );//追加内容

        //追加用户名
        return $result;
    }
    /**
     * 发日志同步到微博
     * @param integer post_id 帖子ID
     * @param string title 帖子标题
     * @param string content 帖子内容
     * @param integer uid 发布者uid
     * @return integer feed_id 微博ID
     */
    public function syncToFeed($post_id,$title,$content,$uid,$publish_time = null) {
    	$d['content'] = '';
    	$d['publish_time'] = $publish_time;
    	$d['body'] = '我发表了一篇日志【'.$title.'】'.getShort($content,50).'&nbsp;';
    	$d['source_url'] = U('blog/Index/show', array("id"=>$post_id, "mid"=>$uid));
    	$feed = model('Feed')->put($uid, 'blog', 'blog_post', $d);
    	return $feed['feed_id'];
    }
    /**
     * 发日志同步到微博
     * @param integer post_id 帖子ID
     * @param string title 帖子标题
     * @param string content 帖子内容
     * @param integer uid 发布者uid
     * @return integer feed_id 微博ID
     */
    public function syncToClassFeed($post_id,$title,$content,$uid ,$cid) {
    	$d['content'] = '';
    	$d['body'] = $content.'&nbsp;';
    	$d['class_id'] = $cid;
    	$d['source_url'] = U('blog/Index/show_class', array("id"=>$post_id, "cid"=>$cid, "mid"=>$uid));
    	$feed = model('Feed')->put($uid, 'class', 'blog_post', $d);
    	return $feed['feed_id'];
    }
    /**
     * 为feed提供应用数据来源信息 - 与模板blog_post.feed.php配合使用
     * @param integer row_id 帖子ID
     * @param bool _forApi 提供给API的数据
     */
    public function getSourceInfo($row_id, $_forApi = false){
    	$info =  $this->find($row_id);
    	if(!$info) return false;
    	$info['source_user_info'] = model('User')->getUserInfo($info['uid']);
    	$info['source_user'] = $info['uid'] == $GLOBALS['ts']['mid'] ? L('PUBLIC_ME'): $info['source_user_info']['space_link'];			// 我
    	$info['source_type'] = '日志';
    	$info['source_title'] = $forApi ? parseForApi($info['source_user_info']['space_link']) : $info['source_user_info']['space_link'];	//微博title暂时为空
    	$info['source_url'] = U('blog/Index/show', array('id'=>$row_id,'mid'=>$info['uid']));
    	$info['ctime'] = $info['cTime'];
    	$feed = D('feed_data')->field('feed_id,feed_content')->find($info['feed_id']);
    	$info['source_content'] = $feed['feed_content'];
    	$info['app_row_table'] = 'blog';
    	$info['app_row_id'] = $info['id'];
    	return $info;
    }
    /**
     * doAddBlog
     * 添加日志
     * @param mixed $map 日志内容
     * @param mixed $feed 是否发送动态
     * @access public
     * @return void
     */
    public function doAddBlog ($map,$import) {
    	$map['private']		= '0';
        $map['cTime']        = isset( $map['cTime'] )?$map['cTime']:time();
        $map['mTime']        =$map['cTime'];
        $map['type']  		 = isset( $map['type'])?$map['type']:$this->_type;
        $map['private_data'] = md5($map['password']);
        $map['category_title'] = M('blog_category')->where("`id`={$map['category']}")->getField('name');
        $content 			 = $map['content'];// 用于发通知截取
        $map['content'] 	 = t(h($map['content']));

        unset( $map['password'] );
        $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;//解析提到的好友
        unset( $map['mention'] );

        $map    = $this->merge( $map );
        $addId  = $this->add( $map );

        $temp = array_filter( $friendsId );
        //$appid = A('Index')->getAppId();
        //添加日志提到的好友
        if( !empty( $friendsId ) && !empty($temp) ) {
            $mention = self::factoryModel( 'mention' );
            $result  = $mention->addMention( $addId,$temp );
            for($i =0 ;$i<count($temp);$i++){
                  setScore($map['uid'], 'mention');
            }

            //发送通知给提到的好友

            $body['content']     = getBlogShort(t($content),40);
            $url                 = sprintf( "%s/Index/show/id/%s/mid/%s",'{'.$appid.'}',$addId,$map['uid'] );
            $title_data['title']   = sprintf("<a href='%s'>%s</a>",$url,$map['title']);
            $this->doNotify( $temp,"blog_mention",$title_data,$body,$url );
        }
        if( !$addId ) {
            return false;
        }
        //获得配置信息
        $config    = $this->config['delete'];
        if( $config ) {
        //修改空间中的计数
            $count = $this->where( 'uid ='.$map['uid'] )->count();
        }else {
        //修改空间中的计数
            $count = $this->where( 'uid ='.$map['uid'].' AND status <> 2' )->count();
        }
        //$this->api->space_changeCount( 'blog',$count );

        //发送动态
        if( $import ) {
        //$title['title']   = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",__APP__,$addId,$map['uid'],$map['title']);
            $title['title']   = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",'{SITE_URL}',$addId,$map['uid'],$map['title']);
            $title['title'] = stripslashes($title['title']);
            //setScore($map['uid'],'add_blog');
//            $body['content'] = getBlogShort($this->replaceSpecialChar(t($map['content'])),80);
            $body['content'] = $this->getBlogShort($this->replaceSpecialChar(t($map['content'])),80);
            $body['title'] = stripslashes($body['title']);
            //$this->doFeed("blog",$title,$body);
        }else {
            //setScore($map['uid'],'add_blog');
            $result['appid'] = $addId;
            $result['title'] = sprintf("<a href=\"%s/Index/show/id/%s/mid/%s\">%s</a>",'{SITE_URL}',$addId,$map['uid'],$map['title']);
            return $result;
        }


        return $addId;
    }

function getBlogShort($content,$length = 60) {
	$content	=	real_strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

    public function doSaveBlog( $map,$blogid ) {
        $map['mTime'] = isset( $map['cTime'] )?$map['cTime']:time();
        $map['type']  = isset( $map['type'])?$map['type']:$this->_type;
        $map['category_title'] = M('blog_category')->where("`id`={$map['category']}")->getField('name');
        $map['private_data'] = md5($map['password']);
        // $map['content'] 	 = t(h($map['content']));
		$map['feed_id'] = 111;
        unset( $map['password'] );
        $fp = fopen("c:\log1.txt", "w");
        if($fp){
        	fwrite($fp, $map['content']);
        	fclose($fp);
        }
        $my = str_replace("\uxA0", "", $map['content']);
        $fp = fopen("c:\log2.txt", "w");
        if($fp){
	       	 fwrite($fp, $my);
	       	 fclose($fp);
        }
        //添加blog相关好友
        $friendsId = isset( $map['mention'] )?explode(',',$map['mention']):null;
        unset( $map['mention'] );
        $map    = $this->merge( $map );

        if( !empty( $friendsId ) ) {
            $mention = self::factoryModel( 'mention' );
            $result  = $mention->updateMention( $blogid,$friendsId );
        }
        $addId  = $this->where( 'id = '.$blogid )->save( $map );

        if( !$result && !empty( $friendsId ) ) {
            return false;
        }

        return $addId;

    }

    /**
     * updateAuto
     * 更新日志的列表
     * @param mixed $map
     * @param mixed $id
     * @access public
     * @return void
     */
    public function updateAuto( $map,$id ) {
        $outline = self::factoryModel( 'outline' );
        return $outline->doUpdateOutline( $map,$id );

    }
    /**
     * autosave
     * 自动保存
     * @param mixed $map
     * @access public
     * @return void
     */
    public function autosave( $map ) {
        $outline = self::factoryModel( 'outline' );
        return $outline->doAddOutline( $map );
    }
    /**
     * getConfig
     * 获取配置
     * @param mixed $index
     * @access public
     * @return void
     */
    public function getConfig( $index ) {
        $config = $this->config[$index];
        return $config;
    }


    /**
     * unsetConfig
     * 删除配置
     * @param mixed $index
     * @param mixed $group
     * @access public
     * @return void
     */
    public function unsetConfig( $index , $group = null ) {
        if( isset( $group ) ) {
            unset( $this->config->$group->$index );
        }else {
            unset( $this->config->$index );
        }
        return $this;
    }

    /**
     * DateToTimeStemp
     * 时间换算成时间戳返回
     * @param mixed $stime
     * @param mixed $etime
     * @access public
     * @return void
     */
    public function DateToTimeStemp( $stime,$etime ) {
        $stime = strval( $stime );
        $etime = strval( $etime );

        //如果输入时间是YYMMDD格式。直接换算成时间戳
        if( isset( $stime[7] ) && isset( $etime[7] ) ) {
        //开始时间
            $syear  = substr( $stime,0,4 );
            $smonth = substr( $stime,4,2 );
            $sday   = substr( $stime,6,2 );
            $stime  = mktime( 0, 0, 0, $smonth,$sday,$syear );

            //结束时间
            $eyear  = substr( $etime,0,4 );
            $emonth = substr( $etime,4,2 );
            $eday   = substr( $etime,6,2 );
            $etime  = mktime( 0, 0, 0, $emonth,$eday,$eyear );

            return array( 'between',array( $stime,$etime ) );
        }

        //如果输入时间是YYYYMM格式
        $start_temp   = $this->paramData( $stime );
        $end_temp     = $this->paramData( $etime );
        $start        = $start_temp[0];
        $end          = $end_temp[1];

        return array( 'between',array( $start,$end ) );
    }

    public function getBlogTitle( $uid ) {
        $map['uid'] = $uid;
        $map = $this->merge( $map );
        return $this->where( $map )->field( 'title,id' )->order( 'cTime desc' )->limit( "0,10" )->findAll();
    }

    /**
     * 获得精彩分享
     * @param unknown_type $uid
     */
    public function getExcellentShare() {
    	$excellentBlogList = $this->where("status=1 AND private=0 AND isHot!=2")->order( 'commentCount DESC' )->limit( "0,5" )->findAll();
    	return $excellentBlogList;
    }
    
    /**
     * checkGetSubscribe
     * 检查和返回以注册过的订阅源
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function checkGetSubscribe( $uid ) {
        $subscribe  = $this->factoryModel( 'subscribe' );
        $map['uid'] = $uid;
        $source_id  = $subscribe->getSourceId( $map );

        unset( $map );

        $source    = $this->factoryModel( 'source' );
        if( empty($source_id))
            return false;
        $map['id'] = array( 'in',$source_id );
        $result    = $source->getSource( $map );

        //重组数据,根据服务名和用户名重组链接
        foreach ( $result as &$value ) {
            switch( $value['service'] ) {
                case "163":
                    $link = "http://%s.blog.163.com/rss/";
                    break;
                case "sohu":
                    $link = "http://%s.blog.sohu.com/rss";
                    break;
                case "baidu":
                    $link = "http://hi.baidu.com/%s/rss/";
                    break;
                case "sina":
                    $link = "http://blog.sina.com.cn/rss/%s.xml";
                    break;
                case "msn":
                    $link = "http://%s.spaces.live.com/feed.rss";
                    break;
                default:
                    $link = $value['service'];
            //throw new ThinkException( "系统异常" );
            }
            $value['link'] = sprintf( $link,$value['username'] );
        //unset ( $value['service'] );
        //unset( $value['username'] );
        }
        return $result;
    }

    /**
     * doIsHot
     * 设置推荐
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsHot( $map,$act ) {
        if( empty($map) ) {
            throw new ThinkException( "不允许空条件操作数据库" );
        }
        switch( $act ) {
            case "recommend":   //推荐
                $field = array( 'isHot','rTime' );
                $val = array( 1,time() );
                $result = $this->setField( $field,$val,$map );
                break;
            case "cancel":   //取消推荐
                $field = array( 'isHot','rTime' );
                $val = array( 0,0 );
                $result = $this->setField( $field,$val,$map );
                break;
            case "togreat":   //精华
                $field = array( 'isHot','rTime' );
                $val = array( 2,time() );
                $result = $this->setField( $field,$val,$map );
                break;
        }
        return $result;
    }

    /**
     * replace
     * 对数据集进行追加处理
     * @param array $data 数据集
     * @param array $mention 需要被追加的值
     * @access protected
     * @return void
     */
    protected function replace( $data,$mentiondata = null ) {
        $result         = $data;
        $categoryname   = $this->getCategory(null);  //获取所有的分类


        //TODO 配置信息,截取字数控制

        foreach ( $result as &$value ) {
            if(3 == $value['private']) {
               // if(Cookie::get($value['id'].'password') == $value['private_data']) {
               //     $value['private'] = 0;
               // }   Change
            }
            $value['content']  = str_replace( "&amp;nbsp;","",h($value['content']));
//            $value['category'] = array(
//                "name" => $categoryname[$value['category']]['name'],
//                "id"   => $value['category']); //替换日志类型

            //日志截断
            $short = $this->config->titleshort == 0 ? 4000: $this->config->titleshort;
            
            $suffix = (StrLenW( $value['content'] ) > $short) ? $this->config->suffix : '';
            $value['content'] = getBlogShort( $value['content'], $short ) . $suffix;

            //日志标题
            $value['title'] = stripslashes( $value['title'] );
        }
        return $result;
    }


    /**
     * changeType
     * 将数组中的数据转换成指定类型
     * @param mixed $data
     * @param mixed $type
     * @access private
     * @return void
     */
    private static function changeType( $data , $type ) {
        $result = $data;

        switch( $type ) {
            case 'int':
                $method = "intval";
                break;
            case 'string':
                $method = "strtval";
                break;
            default:
                throw new ThinkException( '暂时只能转换数组和字符串类型' );
        }
        foreach ( $result as &$value ) {
            is_numeric( $value ) && $value = $method( $value );
        }
        return $result;
    }


    private function replaceSpecialChar($code) {
        $code = str_replace("&amp;nbsp;", "", $code);

        $code = str_replace("<br>", "", $code);

        $code = str_replace("<br />", "", $code);

        $code = str_replace("<P>",  "", $code);

        $code = str_replace("</P>","",$code);

        return trim($code);
    }
    /**
     * _orderDate
     * 解析日志排序时间区段
     * @param mixed $options
     * @access private
     * @return void
     */
    private function _orderDate( $options ) {
        if('all' == $options) return array('lt',time());
        $now_year  = intval( date( 'Y',time() ) );
        $now_month = intval( date( 'n',time() ) );
        $now_day   = intval( date( 'j',time() ) );

        //定义偏移量
        $month = self::_getExcursion($options, 'month');
        $year = self::_getExcursion($options, 'year');
        $day = self::_getExcursion($options, 'day');

        //换算时间戳
        $toDate = mktime( 0,0,0,$now_month-$month,$now_day-$day,$now_year-$year );
        //返回数组型数据集
        return array( "between",array( $toDate,time() ) );
    }
    private static function _getExcursion($options,$field){
        $excursion = array(
                            'one'   => array('month'=>1),
                           'three' => array('month'=>3),
                           'half'  => array('month'=>6),
                           'year'  => array('year'=>1),
                           'oneDay'=> array('day'=>1),
                           'threeDay'=>array('day'=>3),
                           'oneWeek'=>array('day'=>7),
                           );
        return isset($excursion[$options][$field])?$excursion[$options][$field]:0;
    }

        /**
         * addRecommendUser 
         * 添加日志推荐
         * @param mixed $map 
         * @param mixed $action 
         * @param mixed $obj 
         * @access public
         * @return void
         */
        public function addRecommendUser( $map,$action ){
            //推荐
            if( 'recommend' == $action  ){
                $this->add( $map );
                $sql = "UPDATE {$this->tablePrefix}blog
                        SET recommendCount = recommendCount + 1
                        WHERE `id` = {$map['blogid']}
                    ";
            //取消推荐
            }else{
                $this->where($map)->delete( );
                $sql = "UPDATE {$this->tablePrefix}blog
                        SET recommendCount = recommendCount - 1
                        WHERE `id` = {$map['blogid']}
                    ";
            }
            $result = $this->execute( $sql ) ;
            return $result;

        }
        
    public function checkRecommend( $uid,$blogid ){
      $map['uid']    = $uid;
      $map['type']   = 'recommend';
      $map['blogid'] = $blogid;
      return $this->where( $map )->find();
    }
    // 获取归档数据
    public function getDataByDate($date,$condition = array(),$limit){
      $date = $date;
      $year = substr($date,0,4);
      $month = substr($date,-2);
      $month1 = ($month+1)%12;
      if(!$month1){ $month1 = 12;}
      if($month == 12){ $year = $year + 1; }
      $sTime = mktime(0,0,0,$month,1,$year);
      $eTime = mktime(0,0,0,$month1,1,$year);
      $map['cTime'] = array('between',array($sTime,$eTime));
      if(isset($condition['uid'])){
      	$map['uid'] = $condition['uid'];
      }
      if(isset($condition['cid'])){
      	$map['class_id'] = $condition['cid'];
      }
      $limit = !empty($limit) ? $limit : 20;
      $list = $this->where($map)->order('cTime DESC')->findPage($limit);
      return $list;
    }
    
    /**
     * 获取最新的日志
     */
    public function getNewBlogs(){
    	$newBlogList = $this->where("status=1 AND private=0 AND isHot!=2")->order( 'cTime DESC' )->limit( "0,5" )->findAll();
    	return $newBlogList;
    }
    /**
     * 根据isHot,评论数，浏览量和时间排序最火日志
     * return array
     */
    public function geAppHotBlog(){
    	$hot_list =$this->field('id,uid,title,content,commentCount')->where(array('isHot'=>1,'status'=>1,'private'=>0))->order('commentCount desc, readCount desc, rTime desc ')->limit("0,5")->findAll();
    	$result =array();
    	foreach ($hot_list as $key){
    		$map['row_id']=$key['id'];
    		$map['table'] ='blog';
    		$map['to_uid'] =0;
    		$map['is_del']=0;
    		$comment =D('Comment')->field('uid,content,digg_count')->where($map)->order('digg_count DESC,ctime ASC')->find();
    		$blogUser =D('User')->getUserInfo($key['uid']);
    		if(!empty($comment)){
    			$commentUser =D('User')->getUserInfo($comment['uid']);
    		}
    		$blogInfo['hotUser']=$blogUser;
    		$blogInfo['commentUser']=$commentUser;
    		$blogInfo['id']=$key['id'];
    		$blogInfo['content'] =str_replace(array('&nbsp;',' '),'',$key['content']);
    		$blogInfo['title']=htmlentities($key['title']);
    		$blogInfo['discuss_count']=$key['commentCount'];
    		$blogInfo['comment_content']=parse_html(htmlentities($comment['content']));
    		$blogInfo['comment_count']=$comment['digg_count'];
    		$blogInfo['source_url']=U('blog/Index/show',array('mid'=>$key['uid'],'id'=>$key['id']));
    		unset($comment);
    		unset($commentUser);
    		array_push($result,$blogInfo);
    	}
    	return $result;
    }
    
    /**
     * 根据条件获取日志列表
     * 注：供后台使用
     * @author ylzhao
     */
    public function getBlogs($conditions, $page=1, $limit=10, $order){
    	$map = array();
    	if(isset($conditions['isHot'])){
    		$map['b.`isHot`'] = $conditions['isHot'];
    	}
    	if(isset($conditions['private'])){
    		$map['b.`private`'] = $conditions['private'];
    	}
    	if(isset($conditions['title'])){
    		$map['b.`title`'] = array('like', "%".$conditions['title']."%");
    	}
    	$fields = 'b.*,u.`uname`';
    	$tables = $this->tablePrefix.$this->tableName.' b ';
    	$join = 'LEFT JOIN '.$this->tablePrefix.'user u ON b.`uid` = u.`uid`';
    	$order = empty($order) ? "cTime DESC" : $order;
    	$map['b.`status`'] = 1;
    	$start = ($page - 1) * $limit;
    	$result['data'] = $this->field($fields)->table($tables)->where($map)->join($join)->order($order)->limit("$start, $limit")->select();
    	$result['count'] = $total = $this->table($tables)->where($map)->join($join)->count();
    	return $result;
    }
    
}
