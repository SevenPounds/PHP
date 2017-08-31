<?php
/**
 * IndexAction
 * blog的Action.接收和过滤网页传参
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {
        private $filter;
        private $blog;
        private $lastblog;
        private $config;
        private static $friends=array();
        protected $app = null;

        /**
         * __initialize
         * 初始化
         * @access public
         * @return void
         */
        public function _initialize() {
            $this->app = $GLOBALS['ts']['app'];
			//设置日志Action的数据处理层
            $this->blog  = D('Blog','blog');
            $this->follow= D('Follow','blog');
            $this->config= D('AppConfig','blog')->getConfig();
            $this->assign($this->config);
            $isAdmin = model('UserGroup')->isAdmin($this->mid);
            $this->assign('isAdmin',$isAdmin);
            if(isset($_GET['mid'])){
            	$TitleInfo =model ( 'User' )->getUserInfo (intval($_GET['mid']));
            	$this->assign("isBlogUser",1);
            }else{
            	$visitUser =isset($_GET['uid'])?$_GET['uid']:$this->uid;
            	$TitleInfo =model ( 'User' )->getUserInfo (intval($visitUser));
            	$this->assign("isBlogUser",0);
            }
            $this->assign('TitleInfo',$TitleInfo);
        }
        // 日志统计数
        public function commentCount($list){
            foreach ($list['data'] as $key => $value) {
                $map['app'] = 'public';
                $map['table'] = 'blog';
                $map['row_id'] = $value['id'];
                $list['data'][$key]['commentCount'] = M('comment')->where($map)->count();
            }
            return $list;
        }
        /**
         * index_class
         * 好友的日志
         * @access public
         * @return void
         */
        public function index_class() {
        	$classId = intval($_GET['cid']);
        	$list = $this->blog->getAllData('popular',array('cid'=>$classId));
        	$list = $this->commentCount($list);//评论数统计
        	foreach ($list['data'] as $key => $value) {
        		$getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
        	}
        	$this->assign('user_info',$getUserInfo);

        	$relist= $this->blog->getIsHot();
        	$this->assign('relist',$relist);
        	$this->assign( 'uid',$this->mid );
        	$this->assign( 'classId',$classId );
        	$this->assign( 'order',t($_GET['order']) );
        	$this->assign( $list );
        	$this->assign( 'all','true' );
        	$this->setTitle("热门{$this->app['app_alias']}");
        	$this->display();
        }
        /**
         * index
         * 好友的日志
         * @access public
         * @return void
         */
        public function index() {
			$list = $this->blog->getAllData('new', array('uid'=>$this->mid));
            $uids = array_unique(getSubByKey($list['data'],'uid'));
            $this->assign('user_info',model('User')->getUserInfoByUids($uids));
			$this->assign( 'uid',$this->mid );
			$this->assign( 'order',t($_GET['order']) );
			$this->assign( $list );
			$this->assign( 'all','true' );
			$this->setTitle("最新{$this->app['app_alias']}");
			$this->display();
        }

        /**
         * search
         * 搜索日志
         * @access public
         * @return void
         */
        public function search() {
			$keyword	=	h($_GET['key']);
			//获得日志数据集,自动获得当前登录用户的好友日志
			$map['title']  = array('like',"%{$keyword}%");
			if($keyword) {
				$list = S('blogSearch_'.$keyword.$this->mid);
				if(empty($list)) {
					$list = $this->blog->getBlogList($map, '*', 'cTime desc', 10, $this->mid);
					S('blogSearch_'.$keyword.$this->mid,$list,60);
				}
			}
            foreach ($list['data'] as $key => $value) {
                $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
            }
            $this->assign('user_info',$getUserInfo);
			$relist= S('relist');
			if(empty($relist)) {
				$relist= $this->blog->getIsHot();
				S('relist',$relist,7200);
			}
			$this->assign('relist',$relist);
			$this->assign( 'api',$this->api);
			$this->assign( 'uid',$this->mid );
			$this->assign( $list );
			$this->assign( 'all','true' );
			$this->setTitle("搜索文章: ".$keyword);
			$this->display();
        }
        /**
         * 班级日志
         * @access public
         * @return void
         */
        public function my_class() {
        	//获得日志数据集
        	$outline = D( 'BlogOutline' );
        	$classId = intval($_GET['cid']);
        	// 归档数据
        	if($_GET['date']){
        		$list = $this->blog->getDataByDate($_GET['date'],array('cid'=>$classId));
        	}else{
	        	$list    = isset( $_GET['outline'] )?
	        	$outline->getClassBlogList( $classId ): //草稿箱
	        	$this->__getClassBlog( $classId,'*','cTime desc' ); //我的日志
        	}
        	foreach($list['data'] as $k => $v) {
        		$getUserInfo[$v['uid']] = model('User')->getUserInfo($v['uid']);
        		if ( empty($v['category_title']) && !empty($v['category']) )
        			$list['data'][$k]['category_title'] = M('blog_category')->where('id='.$v['category'])->getField('name');
        	}
        	$this->assign('user_info',$getUserInfo);

        	//获得分类的计数
        	$category = $this->__getBlogCategoryCount(array('cid'=>$classId));
        	//草稿箱计数
        	$outline = D( 'BlogOutline' )->where( 'uid ='.$this->mid )->count();

        	//检查是否可以查看全部日志
        	$this->__checkAllModel();
        	$relist= $this->blog->getIsHot();
        	$this->assign('relist',$relist);
        	//获得归档传输数据
        	$this->assign( 'oc',$outline );
        	$this->assign('category',$category);
        	$this->assign( $list );
        	$this->assign('classId',$classId);
        	$this->setTitle("班级的{$this->app['app_alias']}");
        	$this->display();
        }
        /**
         * my
         * 我的日志
         * @access public
         * @return void
         */
        public function my() {
        	//获得日志数据集
            $outline = D( 'BlogOutline' );
			$docret = $_GET['docret'];
			if(empty($docret) && null != $docret) {
				$this->assign('docret',$docret);
			}
            // 归档数据
            if($_GET['date']){
            	$list = $this->blog->getDataByDate($_GET['date'],array('uid'=>$this->mid));
            }else{
            	$list    = isset( $_GET['outline'] )?
            	$outline->getList( $this->mid ): //草稿箱
            	$this->__getBlog( $this->mid,'*','cTime desc' ); //我的日志
            }

            foreach($list['data'] as $k => $v) {
            	if ( empty($v['category_title']) && !empty($v['category']) )
            		$list['data'][$k]['category_title'] = M('blog_category')->where('id='.$v['category'])->getField('name');
            }

            //获得分类的计数
            $category = $this->__getBlogCategoryCount(array('uid'=>$this->mid));

            //草稿箱计数
            $outline = D( 'BlogOutline' )->where( 'uid ='.$this->mid )->count();

            //检查是否可以查看全部日志
			$relist= S('relist');
			if(empty($relist)) {
				$relist= $this->blog->getIsHot();
				S('relist',$relist,8400);
			}
            $this->assign('relist',$relist);
            //获得归档传输数据
            $this->assign( 'oc',$outline );
            $this->assign('category',$category);
            $this->assign( $list );
            $this->assign('totalrecords',$list['count']);
            $this->setTitle("我的{$this->app['app_alias']}");
            $this->display('index');
        }
        /**
         * 最新班级日志
         */
        public function news_class() {
        	//检查是否可以查看这个页面
        	if( $this->__checkAllModel() ) {
        		$classId = intval($_GET['cid']);
        		$list = $this->blog->getAllData('new', array('cid'=>$classId));
        		$list = $this->commentCount($list);//评论数统计
        		foreach ($list['data'] as $key => $value) {
        			$getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
        		}
        		$this->assign('user_info',$getUserInfo);
        		$relist= $this->blog->getIsHot();
        		$this->assign('relist',$relist);
        		$this->assign( 'api',$this->api);
        		$this->assign( 'uid',$this->mid );
        		$this->assign( 'classId',$classId );
        		$this->assign( 'order',t($_GET['order']) );
        		$this->assign( $list );
        		$this->assign( 'all','true' );
        		$this->setTitle("最新{$this->app['app_alias']}");
        		$this->display('index_class');
        	}else {
        		$this->error( L( 'error_all' ) );
        	}
        }
        public function news() {
	        //检查是否可以查看这个页面
			if( $this->__checkAllModel() ) {
    			$list = $this->blog->getAllData('new', array('uid'=>$this->mid));
                foreach ($list['data'] as $key => $value) {
                    $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
                }
                $this->assign('user_info',$getUserInfo);
                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',t($_GET['order']) );
                $this->assign( $list );
                $this->assign( 'all','true' );
                $this->setTitle("最新{$this->app['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
            }
        }
        public function followsblog() {
        	//检查是否可以查看这个页面
            if( $this->__checkAllModel() ) {
            	$list = $this->blog->getFollowsBlog($this->mid);

                foreach ($list['data'] as $key => $value) {
                    $getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
                }
                $this->assign('user_info',$getUserInfo);
                $this->assign( 'api',$this->api);
                $this->assign( 'uid',$this->mid );
                $this->assign( 'order',t($_GET['order']) );
                $this->assign( $list );
                $this->assign( 'all','true' );
                $this->setTitle("我的关注的{$this->app['app_alias']}");
                $this->display('index');
            }else {
            	$this->error( L( 'error_all' ) );
			}
        }
        /**
         * 2014-11-24  by nandeng
         * 热门日志
         */
        public function popularblog(){
        	//检查是否可以查看这个页面
        	if( $this->__checkAllModel() ) {
				$list = S('popularList'.$this->mid);
				if(empty($list)) {
					$list = $this->blog->getAllData('popular', array('uid' => $this->mid));
					S('popularList'.$this->mid,$list,1200);
				}
        		foreach ($list['data'] as $key => $value) {
        			$getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
        		}
        		$this->assign('user_info',$getUserInfo);
        		$this->assign( 'api',$this->api);
        		$this->assign( 'uid',$this->mid );
        		$this->assign( 'order',t($_GET['order']) );
        		$this->assign( $list );
        		$this->assign( 'all','true' );
        		$this->setTitle("热门{$this->app['app_alias']}");
        		$this->display('index');
        	}else {
        		$this->error( L( 'error_all' ) );
        	}
        }
        /**
         * 2014-11-20  by nandeng
         * 精华帖
         */
        public function essenceblog(){
        	//检查是否可以查看这个页面
        	if( $this->__checkAllModel() ) {
        		$list = $this->blog->getAllData('essence', array('uid'=>$this->mid));
        		foreach ($list['data'] as $key => $value) {
        			$getUserInfo[$value['uid']] = model('User')->getUserInfo($value['uid']);
        		}
        		$this->assign('user_info',$getUserInfo);
        		$relist= $this->blog->getIsHot($isHot=2);
        		$this->assign('data',$relist);
        		$this->assign( 'api',$this->api);
        		$this->assign( 'uid',$this->mid );
        		$this->assign( 'order',t($_GET['order']) );
        		$this->assign( 'all','true' );
        		$this->setTitle("精华{$this->app['app_alias']}");
        		$this->display('index');
        	}else {
        		$this->error( L( 'error_all' ) );
        	}
        }
        private function __checkAllModel() {
        	return true;

        	//获取配置，是否可以查看全部的日志
            if( $this->blog->getConfig( 'all' ) ) {
            	$this->assign( 'all','true' );
                return true;
            }
            return false;
        }
        /**
         * show
         * 日志显示页
         * @access public
         * @return void
         */
        public function show_class() {

        	unset($_SESSION['blog_use_widget_share']);
        	//获得日志id
        	$id = intval($_GET['id']);
        	$classId = intval($_GET['cid']);
        	$this->blog->setUid( $this->mid );

        	//全站日志
        	if( $this->blog->getConfig( 'all' ) ) {
        		$this->assign( 'all','true' );
        	}

        	//日志所有者
        	$bloguid = intval($_GET['mid']);

        	//获得日志的详细内容,第二参数通知是当前还是上一篇下一篇
        	isset( $_GET['action'] ) && $how = t($_GET['action']);
        	$list     = $this->blog->getBlogContent($id,$how,array('cid'=>$classId));
        	$list['user_info'] = model('User')->getUserInfo($list['uid']);

        	$list['content'] = htmlspecialchars_decode($list['content']);
        	$isExist = $this->blog->where('id='.$id)->find();
        	//检测是否有值。不允许非正常id访问
        	if( false == $list || empty($isExist) || $isExist['status'] == 2) {
        		$this->assign('jumpUrl',U('blog/Index/index_class',array('cid'=>$classId)));
        		$this->error( '日志不存在或者已删除！' );
        	}
        	$list['content'] = htmlspecialchars_decode($list['content']);

        	//获得正确的当前日志ID
        	$id = $list['id'];

        	//不是日志所有人读日志才会刷新阅读数.只有非日志发表人才进行阅读数刷新
        	if( !empty( $bloguid ) && $this->mid != $bloguid ) {
        		//浏览计数，防刷新
        		$this->blog->changeCount( $id );
        		//}
        	}


        	//获取发表人的id
        	$name          = $this->blog->getOneName( $bloguid );

        	//他人日志渲染特殊的变量和数据
        	if( $this->mid != $bloguid ) {
        		//如果是其它人的日志。需要获得最新的10条日志
        		$bloglist  = $this->blog->getBlogTitle( $list['uid'] );
        		$this->assign( 'bloglist',$bloglist );
        	}
        	$sub_content = preg_replace("/\s/i", "", trim(strip_tags($list['content'])));
        	if(strlen($sub_content) > 40)
        		$sub_content = mb_substr($sub_content, 0, 40, 'utf-8')."...";
        	//渲染公共变量
        	$relist= $this->blog->getIsHot();
        	$this->assign('relist',$relist);
        	$this->assign( $list );
        	$this->assign( 'blog', $list );
        	$this->assign( 'guest',$this->mid );
        	$this->assign( 'user_name', $GLOBALS['ts']['user']['uname'] );
        	$this->assign( 'name',$name['name'] );
        	$this->assign( 'uid',$bloguid );
        	$this->assign( 'classId',$classId );
        	$this->assign('isOwner', $this->mid == $bloguid ? '1' : '0');
        	$this->assign('isAdmin',model('UserGroup')->isAdmin($this->mid));
        	$this->assign( 'sub_content', $sub_content );

        	$this->setTitle(getUserName($list['uid']).'的文章: '.$list['title']);
        	$this->display('blogContent_class');
        }
	public function updateStatus() {
		$bloguid = intval($_REQUEST['mid']);
		$id = intval($_REQUEST['blogId']);
		if(!empty($bloguid) && !empty($id)) {
			//统计记录
			$this->operationLog["actionName"] = "blog";
			$this->operationLog["remark"] = "个人空间日志查看";
			model("OperationLog")->addOperationLog($this->operationLog);
			//记录来访者
			recordVisitor($bloguid, $this->mid);
			//不是日志所有人读日志才会刷新阅读数.只有非日志发表人才进行阅读数刷新
			if (!empty($bloguid) && $this->mid != $bloguid) {
				$this->blog->changeCount($id);
			}
		}
	}
        /**
         * show
         * 日志显示页
         * @access public
         * @return void
         */
        public function show() {

            unset($_SESSION['blog_use_widget_share']);
            //获得日志id
            $id = intval($_GET['id']);
            $this->blog->setUid( $this->mid );

            //全站日志 加上缓存
			$blogConfig = S('blogConfig');
			if(empty($blogConfig)) {
				$blogConfig = $this->blog->getConfig( 'all' );
				S ('blogConfig', $blogConfig, 3600 );//缓存资源信息
			}
            if( $blogConfig) {
                 $this->assign( 'all','true' );
            }

            //日志所有者
            $bloguid = intval($_GET['mid']);
            //获得日志的详细内容,第二参数通知是当前还是上一篇下一篇
            isset( $_GET['action'] ) && $how = t($_GET['action']);
            $list= $this->blog->getBlogContent($id,$how,array('uid'=>$bloguid));
			$list['user_info'] = S('userInfo_'.$list['uid']);
			if(empty($list['user_info'])) {
				$list['user_info'] = model('User')->getUserInfo($list['uid']);
			}

            $userDate=D('UserData')->getUserData($list['uid']);
            $this->assign('userData',$userDate);
            if($this->mid != $list['user_info']['uid']){
            	$resourceCount =D('YunpanPublish','yunpan')->getlistPublishCount($list['user_info']['login'],'01');
            	$this->assign('resourceCount',$resourceCount);
            }
            $node = model('Node');
            $subject=$node->getNameByCode('subject',$list['user_info']['subject']);
            $this->assign('subject',$subject);

            //获取用户角色，为了和主页对应
            $cyuserdata = D('CyUser')->getCyUserInfo($list['user_info']['login']);
			//支持多角色，获取登录用户当前使用的角色
            $roleEnName = D("UserLoginRole")->getUserCurrentRole($cyuserdata['user']['login'], $cyuserdata['rolelist']);
            //三级教研员信息
            if($roleEnName=='instructor'){
            	$instructor=D("CyUser")->getUserDetail($list['uid']);
            	$leveltype=$instructor['instructor_level'];

            	if(empty($leveltype)){
            		$levelName="教研员";
            	}else{
            		$levelName=UserRoleTypeModel::getCNRoleName($leveltype);
            	}
            	$this->assign('levelName',$levelName);

            }else{
            	$levelName=UserRoleTypeModel::getCNRoleName($roleEnName);
            	$this->assign('levelName',$levelName);
            }
            $usercredit=model('Credit')->getUserCredit($list['uid']);
            $this->assign('userCredit',$usercredit);

            $flags=true;
            while($flags){
			if($this->mid != $bloguid){
				$relationship = getFollowState($this->mid, $bloguid);
				if($list['private'] == 4){
					if(empty($how)){
						$this->assign('jumpUrl', U('blog/Index/index'));
						$this->error('本日志仅主人自己可见');
					}else{
						$list = $this->blog->getBlogContent($list['id'],$how,array('uid'=>$bloguid));
					}
				}else if($list['private'] == 2 && $relationship=='unfollow'){
					if(empty($how)){
						$this->assign('jumpUrl', U('blog/Index/index'));
					    $this->error('本日志仅主人的粉丝可见');
					}else{
					$list = $this->blog->getBlogContent($list['id'],$how,array('uid'=>$bloguid));
					}
				} else if ($list['private'] == 5 && model('Friend')->identifyFriend($this->mid, $bloguid) != FriendModel::ARE_FRIENDS) {
					if(empty($how)){
					   $this->assign('jumpUrl', U('blog/Index/index'));
				       $this->error('本日志仅主人朋友可见');
					}else{
					$list = $this->blog->getBlogContent($list['id'],$how,array('uid'=>$bloguid));
					}
				}else{
					$flags=false;
				}
			}else{
				$flags= false;
			}
            }

            $list['content'] = htmlspecialchars_decode($list['content']);
            $isExist = $this->blog->where('id='.$id)->find();
            //检测是否有值。不允许非正常id访问
            if( false == $list || empty($isExist) || $isExist['status'] == 2) {
            		$this->assign('jumpUrl',U('blog/Index'));
                    $this->error( '日志不存在或者已删除！' );
            }
            $list['content'] = htmlspecialchars_decode($list['content']);

            //获得正确的当前日志ID
            $id = $list['id'];
            // 关注关系
            $this->assign( 'relationship', $relationship );

            //检测密码
            if (isset($_POST['password'])) {
                if(md5(t($_POST['password'])) == $list['private_data']) {
                        cookie($id.'password',md5(t($_POST['password'])));
                        $list['private'] = 0;
                }
            } else {
                if( 3 == $list['private'] && cookie($id.'password') == $list['private_data']) {
                        $list['private'] = 0;
                }
            }
                //获取发表人的id
            $name   = $this->blog->getOneName( $bloguid );

            //他人日志渲染特殊的变量和数据
            if( $this->mid != $bloguid ) {
                //如果是其它人的日志。需要获得最新的10条日志
                $bloglist  = $this->blog->getBlogTitle( $list['uid'] );
                $this->assign( 'bloglist',$bloglist );
            }
            $sub_content = preg_replace("/\s/i", "", trim(strip_tags($list['content'])));
            if(strlen($sub_content) > 40)
            	$sub_content = mb_substr($sub_content, 0, 40, 'utf-8')."...";
            //渲染公共变量
			$relist= S('relist');
			if(empty($relist)) {
				$relist = $this->blog->getIsHot();
				S('relist',$relist,7200);
			}
            $this->assign('relist',$relist);
            $this->assign( $list );
			$this->assign('bid',$id);
            $this->assign( 'blog', $list );
            $this->assign( 'guest',$this->mid );
            $this->assign( 'name',$name['name'] );
            $this->assign( 'mid',$bloguid );
			$this->assign( 'blogId', $id );
            $this->assign('isOwner', $this->mid == $bloguid ? '1' : '0');
            $this->assign('isAdmin',model('UserGroup')->isAdmin($this->mid));
            $this->assign( 'sub_content', $sub_content );
            $this->assign( 'user_info', $list['user_info'] );
            $this->setTitle(getUserName($list['uid']).'的文章: '.$list['title']);
            $this->display('blogContent');
        }

        /**
         * personal
         * 个人的日志列表
         * @access public
         * @return void
         */
        public function personal() {
        //获得日志数据集
                $uid   = intval($_GET['uid']);
                if($uid <= 0)
                	$this->error('参数错误');
                //获得blog的列表
                $list  = $this->__getBlog($uid,'*','cTime desc');
                //获得分类的计数
                $category = $this->__getBlogCategoryCount(array('uid'=>$uid));

                $this->assign('api',$this->api);

                $this->assign('category',$category);
                $name = getUserName($uid);
                $this->assign('name', $name);
                $this->assign( $list );
                // 用户信息
                $userInfo = model('User')->getUserInfo($this->uid);
                $this->assign('userInfo',$userInfo);
                $this->assign('totalrecords',$list['count']);

                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->setTitle($name . '的' .$this->app['app_alias']);
                $this->display('index');
        }
        /**
         * 班级id或用户id
         */
        private function __getBlogCategoryCount($condition = array()) {
                $cateId = null;
                if(isset($_GET['cateId'])) {
                    $cateId = intval($_GET['cateId']);
                }
                if(isset($condition['cid'])){
					$category = S('getClassBlogCategory'.$cateId);
					if(empty($category)) {
						$category = $this->blog->getClassBlogCategory($condition['cid'], $cateId);
						S('getClassBlogCategory'.$cateId,$category,30);
					}
                }
                if(isset($condition['uid'])){
                	$category = $this->blog->getBlogCategory($condition['uid'],$cateId);
                }
                return $category;
        }

        /**
         * doDeleteblog
         * 删除blog
         * @access public
         * @return void
         */
        public function doDeleteblog(  ) {

                $this->blog->id = $_REQUEST['id']; //要删除的id;
                $classId = $_REQUEST['cid'];
                $result  = $this->blog->doDeleteblog(null,$this->mid);

                if( false != $result) {
					$url = empty($classId) ? U('blog/Index/my',array('docret'=>1)) : U('blog/Index/my_class',array('cid'=>$classId));
	                redirect($url);
                }else {
                    $this->error( "删除日志失败" );
                }
        }

	    public function updateCredit() {
			model('Credit')->setUserCredit($this->mid,'delete_blog');
		}
        /**
         * deleteCategory
         * 删除分类
         * @access public
         * @return void
         */
        public function deleteCategory(  ) {
                $data['id'] = intval($_POST['id']);
                if( 0 === $data['id'] )
                        return false;

                //删除分类和将分类的日志转移到其它分类里
                isset( $_POST['toCate'] ) && !empty( $_POST['toCate'] ) && $toCate   = $_POST['toCate'];

                $category   = D( 'BlogCategory' );
                return $category->deleteCategory( $data,$toCate,$this->blog );
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addBlog() {
				$classId = intval($_GET['cid']);
				if(empty($classId)){
					$category  = $this->blog->getCategory($this->mid);
				}else{
					$category  = $this->blog->getClassCategory($classId);
				}
                $savetime  = $this->blog->getConfig( 'savetime' );

                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'savetime',$savetime );
                $this->assign( 'blog_category',$category );

                $this->setTitle("发表{$this->app['app_alias']}");
                if(empty($classId)){
                	$this->display();
                }else{
                	$this->assign('classId',$classId);
                	$this->display('addBlog_class');
                }
        }

        /**
         * addBlog
         * 添加blog
         * @access public
         * @return void
         */
        public function addAjaxBlog() {
				$use = intval($_POST['used']);
                $category  = $this->blog->getCategory($this->mid);
                $savetime  = $this->blog->getConfig( 'savetime' );

                $relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'savetime',$savetime );
                $this->assign( 'category',$category );
                if($use){
                	$this->display('addAjaxBlog_used');
                }else{
                	 $this->display();
                }

        }

        public function edit() {
        		$classId = intval($_GET['cid']);
        		if(empty($classId)){
        			$tpl = 'edit';
        			$category = $this->blog->getCategory($this->mid);
        		}else{
        			$tpl = 'edit_class';
        			$this->assign('classId',$classId);
        			$category = $this->blog->getClassCategory($classId);
        		}
                $this->assign( 'blog_category',$category );
                $id = intval($_GET['id']);
                $isAdmin = model('UserGroup')->isAdmin($this->mid);
                if( $_GET['edit'] ) {
                        $outline = D( 'BlogOutline' );
                        //检查是否存在这篇日志
                        if( false == $list = $outline->getBlogContent( $id,null,array('uid'=>$_GET['mid'])))
                                $this->error( L( 'error_no_blog' ) );
                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if( $list['uid'] != $this->mid ) {
                                $this->error( L( 'error_no_role' ) );
                        }

                        $list['saveId'] = $list['id'];
                        unset( $list['id'] );

                        //定义连接
                        $link = __URL__."&act=do";
                        unset ( $list['friendId'] );
                //编辑新的日志
                }else {
                        $link = __URL__."&act=doUpdate";

                        if( false == $list = $this->blog->getBlogContent( $id,null,array('uid'=>$_GET['mid']) ))
                                $this->error( L( 'error_no_blog' ) );

                        //是否有权限修改本篇日志
                        //TODO 管理员
                        if(!$isAdmin && ($list['uid'] != $this->mid ))
                                $this->error( L( 'error_no_role' ) );
                }


				$relist= $this->blog->getIsHot();
                $this->assign('relist',$relist);

                $this->assign( 'link',$link );
                $this->assign( $list );
                $this->display($tpl);
        }

        /**
         * doAddblog
         * 添加blog
         * @access public
         * @return void
         */
        public function doAddBlog() {
            $title = text(h($_POST['title']));

        	if (empty($title)) {
            	$this->error( "请填写标题" );
            }

            if ( mb_strlen($title, 'UTF-8') > 25 ) {
				$this->error( "标题不得大于25个字符" );
            }

            //检查是否为空
            if (empty($_POST['content'])) {
                    $this->error( "日志内容不能为空" );
            }

            //处理发日志的数据
            $data = $this->__getPost();
            $data['cTime'] = time();
            $data['mTime'] = time();
            $category_name = M('blog_category')->where('id ='.t($_POST['category']))->find();
            $data['category_title'] = $category_name['name'];

            //添加日志
			$data["content_origin"] = $data["content"];
			$sensitiveWord = $this->sensitiveWord_svc;
			$contentReplace = $sensitiveWord->checkSensitiveWord($data['content']);
			$contentReplace = json_decode($contentReplace,true);
			if($contentReplace["Code"]!=0){
				return;
			}
			$data["content"] = $contentReplace["Data"];

			$data["title_origin"] = $data["title"];
			$titleReplace = $sensitiveWord->checkSensitiveWord($data["title"]);
			$titleReplace = json_decode($titleReplace,true);
			if($titleReplace["Code"]!=0){
				return;
			}
			$data["title"] = $titleReplace["Data"];

            $images = matchImages($data['content']);
            $images[0] && $data['cover'] = $images[0];
            Log::write("Blog data :".json_encode($data),Log::DEBUG);
            $add = $this->blog->add($data);
            $blogId = mysql_insert_id();
            //如果是有自动保存的数据。删除自动保存数据
            if( isset( $_POST['saveId'] ) && !empty( $_POST['saveId'] ) ) {
                    $BlogOutline = D( 'BlogOutline' );
                    $BlogOutline->where( 'id = '.$_POST['saveId'] )->delete();
            }

            if( $add ) {
				X('Credit')->setUserCredit($this->mid,'add_blog');
				$html = '【'.text($data['title']).'】'.getShort($data['content'],80).U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid));
				$sub_content = '@'.$GLOBALS['ts']['user']['uname'].' 发表了一篇班级日志【'.text($data['title']).'】'.getShort($data['content'],80);
				$image  = $images[0]?$images[0]:false;
				$classId = intval($_POST['cid']);
				$url = empty($classId) ? U('blog/Index/show',array('id'=>$add,'mid'=>$this->mid)) : U('blog/Index/show_class',array('id'=>$add,'cid'=>$classId,'mid'=>$this->mid));
				$this->ajaxData = array('url'=>$url,
					'id' =>$add,
				    'html'=>$html,
				    'image'=>$image,
					'title'=>t($_POST['title']),
				);

                $res['id'] = $blogId;
                $res['mid'] = $this->mid;
                $res['url'] = $url;

                $res['post_id'] = $add;
                $res['title'] = $title;
                if(!empty($classId)){
                    $res['content'] = $sub_content;
                    $res['cid'] = $classId;
                } else {
                    $res['content'] = $data['content'];
                }

                $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在日志发表了“".$data["title_origin"]."”的日志";
                $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["rz"]["code"],C("opType")["create"]['code'],$add,C("location")["localServer"]["code"],"","",$data['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
                Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

                //统计记录
                $this->operationLog["actionName"]="blog";
                $this->operationLog["remark"]="个人空间发表日志";
                model("OperationLog")->addOperationLog($this->operationLog);
                exit($this->ajaxReturn($res, '发布成功', 1));
            }else {
                $this->error( "添加失败" );
            }
        }

    public function syncToFeed()
    {
        $post_id = $_POST['post_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $uid = $_POST['uid'];
        $cid = $_POST['cid'];
        if (!$cid)
        {
            $feed_id =  $this->blog->syncToFeed($post_id, $title, $content, $uid);
        } else {
            $feed_id =  $this->blog->syncToClassFeed($post_id, $title, $content, $uid, $cid);
        }
        if(!$feed_id){
            echo -1;
        }else{
            D('blog')->where('id=' . $post_id)>setField('feed_id', $feed_id);
            echo 1;
        }
    }




        /**
         * doUpdate
         * 执行更新日志动作
         * @access public
         * @return void
         */
        public function doUpdate() {
        		if (empty($_POST['title'])) {
                    $this->error( "请填写标题" );
                }

        		if (mb_strlen($_POST['title'], 'UTF-8') > 25 ) {
                	$this->error( "标题不能大于25个字符" );
                }

                if( empty($_POST['content'])) {
                    $this->error( "日志内容不能为空" );
                }

                $this->blog->getOneName( intval($_POST['uid']) );

                $id       = intval($_POST['id']);
                //检查更新合法化
                if(!model('UserGroup')->isAdmin($this->mid) && ($this->blog->where( 'id = '.$id )->getField( 'uid' ) != $this->mid )) {
                        $this->error( L('error_no_role') );
                }

                $rzList = $this->blog->getBlogContent( $id,null,array('uid'=>$_GET['mid']));

                $data = $this->__getPost();
                $data["content_origin"] = $data["content"];
                $sensitiveWord = $this->sensitiveWord_svc;
                $contentReplace = $sensitiveWord->checkSensitiveWord($data['content']);
                $contentReplace = json_decode($contentReplace,true);
                if($contentReplace["Code"]!=0){
                	return;
                }
                $data["content"] = $contentReplace["Data"];

                $data["title_origin"] = $data["title"];
                $titleReplace = $sensitiveWord->checkSensitiveWord($data["title"]);
                $titleReplace = json_decode($titleReplace,true);
                if($titleReplace["Code"]!=0){
                	return;
                }
                $data["title"] = $titleReplace["Data"];
                $images = matchImages($data['content']);
                $data['cover'] = $images[0];
                $save = $this->blog->doSaveBlog($data,$id);

                if ($save) {
                	$classId = intval($_POST['cid']);
                    // redirect(U('blog/Index/show', array('id'=>$id, 'mid'=>$this->mid)));
                    $url = empty($classId) ? U('blog/Index/show', array('id'=>$id, 'mid'=>$this->mid)) : U('blog/Index/show_class', array('id'=>$id,'cid'=>$classId,'mid'=>$this->mid));
                    $res['id'] = $id;
                    $res['url'] = $url;
                    $res['mid'] = intval($_POST['uid']);

                    $conmment = $this->user['uname']."于".date("Y-m-d H:i:s",time())."在日志修改了“".$rzList['title_origin']."”的日志";
                    $logObj = $this->getLogObj(C("appType")["hdjl"]["code"],C("appId")["rz"]["code"],C("opType")["update"]['code'],$rzList['id'],C("location")["localServer"]["code"],"","",$rzList['title_origin'],$this->mid,$this->user['login'],$this->user['uname'],$this->roleEnName,"",$conmment);
                    Log::writeLog(json_encode($logObj),3,SITE_PATH.C("LOGRECORD_URL"));

                    exit($this->ajaxReturn($res, '发布成功', 1));
                } else {
                    $this->error( "修改失败" );
                }
        }

        private function __getPost() {
        		//得到发日志人的名字
                $userName = $this->blog->getOneName( intval($_POST['uid']) );
                $data['name']     = $userName['name'];
                $data['content']  = safe($_POST['content']);
                $data['uid']      = isset($_POST['uid']) ?intval($_POST['uid']) : $this->mid;
                $data['class_id']      = isset($_POST['cid']) ?intval($_POST['cid']) : NULL;
                $data['category'] = intval($_POST['category']);
                $data['password'] = text($_POST['password']);
                $data['mention']  = $_POST['fri_ids'];
                $data['title']    = !empty($_POST['title']) ? htmlspecialchars(text($_POST['title'])) :"无标题";
                $data['private']  = intval($_POST['private']);
                $data['canableComment'] = intval(t($_POST['cc']));

                //处理attach数据
                $data['attach']         = serialize($this->__wipeVerticalArray($_POST['attach']));
                if(empty($_POST['attach']) || !isset($_POST['attach'])) {
                        $data['attach'] = null;
                }
                return $data;
        }

        private function __wipeVerticalArray($array) {
                $result = array();
                foreach($array as $key=>$value) {
                        $temp = explode('|', $value);
                        $result[$key]['id'] = $temp[0];
                        $result[$key]['name'] = $temp[1];
                }
                return $result;

        }

        /**
         * autoSave
         * 自动保存
         * @access public
         * @return void
         */
        public function autoSave(  ) {
                $content = trim(str_replace('&amp;nbsp;','',t($_POST['content'])));
                //检查是否为空
                if( empty($_POST['content']) || empty( $content )  ) {
                        $this->error( "日志内容不能为空" );
                        exit();
                }

                $add="";
                $userName = $this->blog->getOneName( $this->mid );

                //处理数据
                $data['name']     = $userName['name'];
                $data['content']  = $_POST['content'];
                $data['uid']      = $this->mid;
                $data['category'] = $_POST['category'];
                $data['password'] = $_POST['password'];
                $data['mention']  = $_POST['mention'];
                $data['title']    = !empty($_POST['title']) ?$_POST['title']:"无标题";
                $data['private']  = intval($_POST['private']);
                $data['canableComment'] = intval(t($_POST['cc']));
                if( isset( $_POST['updata'] ) ) {
                //更新数据，而不是添加新的草稿
                        $add = intval(trim($_POST['updata']));
                        $result = $this->blog->updateAuto( $data,$add );
                }else {
                //自动保存
                        $add = $this->blog->autoSave($data);
                }
                if( $add || $result) {
                        echo date('Y-m-d h:i:s',time()).",".$add;
                }else {
                        echo -1;
                }
        }

        /**
         * outline
         * 草稿箱
         * @access public
         * @return void
         */
        public function outline(  ) {
                $this->assign( $list );
                $this->display();
        }

        /**
         * deleteOutline
         * 删除
         * @access public
         * @return void
         */
        public function deleteOutline(  ) {
                if( empty($_POST['id']) ) {
                        echo -1;
                        return;
                }


                $map['id'] = array( "in",array_filter( explode( ',' , $_POST['id'] ) ));
                $outline = D( 'BlogOutline' );
                //检查合法性
                if( $outline->where( $map )->getField( 'uid' ) != $this->mid ) {
                        echo -1;
                }

                if(  $outline->where( $map )->delete() ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * admin
         * 个人管理页面
         * @access public
         * @return void
         */
        public function admin() {
        	//获得分类名称
        	//获得分类下的日志数
        	$classId = intval($_GET['cid']);
        	if(!empty($classId)){
        		$category	= $this->__getBlogCategoryCount(array('cid'=>$classId));
        	}else{
        		$category	= $this->__getBlogCategoryCount(array('uid'=>$this->mid));
        	}
            $relist		= $this->blog->getIsHot();
            $this->assign('relist',$relist);
            $this->assign( 'category',$category );

            $this->setTitle("{$this->app['app_alias']}管理");
            if(!empty($classId)){
            	$this->assign('classId',$classId);
            	$this->display('admin_class');
            }else{
            	$this->display();
            }
        }


        /**
         * deleteCateFrame
         * 删除分类时，转移其中的日志
         * @access public
         * @return void
         */
        public function deleteCateFrame(  ) {
                $id       = intval($_GET['id']);
                $category = $this->blog->getCategory( $this->mid );
                foreach( $category as $key=>$value ) {
                        if( $value['id'] == $id)
                                unset( $category[$key] );
                }
                $this->assign( 'category',$category );
                $this->display();

        }

        /**
         * addCategory
         * 添加分类
         * @access public
         * @return void
         */
        public function addCategory() {
                $data['name'] = h(t($_POST['name']));

                // 如果不是添加班级日志分类,则cid为NULL by xypan 0909
                $data['cid'] = isset($_POST['cid'])? intval($_POST['cid']):NULL;
                $data['uid']  = empty($data['cid']) ? $this->mid : NULL;
                $data['name'] = keyWordFilter(h(t($_POST['name'])));

                $category   = D( 'BlogCategory' );
                $category->addCategory($data,$this->blog);
        }

        public function addCategorys() {
                $this->display();
        }

        //检测分类名是否存在
        public function isCategoryExist() {
            $name = t($_POST['name']);
            $list = M("BlogCategory")->where(array('name'=>$name,'uid'=>$this->mid))->getField("name");
            if($list){
                echo 1;//已存在
            }else{
                echo 0;
            }
        }

        public function filterCategory() {
            $category = t($_POST['name']);
            echo keyWordFilter($category);
        }

        /**
         * editCategory
         * 修改分类
         * @access public
         * @return void
         */
        public function editCategory() {
        	foreach($_POST['name'] as $k => $v){
                $_POST['name'][$k] = h(t($v));
                if(!$_POST['name'][$k]){
                    $this->error('分类名不能为空');
                }
            }


        	if ( count($_POST['name']) != count(array_unique($_POST['name'])) )
        		$this->error('分类名不允许重复, 请重新输入');

			$category = D( 'BlogCategory' );
            $result   = $category->editCategory( $_POST['name'] );

            // 更新日志信息
            foreach ($_POST['name'] as $k => $v) {
            	M('blog')->where("`category`='{$k}'")->setField('category_title', $v);
            }

            $this->assign('jumpUrl', U('blog/Index/admin'));
            $this->success('保存成功');
        }

        /**
         * TODO 删除
         * recommend
         * 推荐操作
         * @access public
         * @return void
         */
        public function recommend(  ) {
                $name          = $this->blog->getOneName($this->mid);
                $map['blogid'] = $_POST['id'];
                $map['uid']    = $this->mid;
                $map['name']   = $name['name'];
                $map['type']   = "recommend";
                $action        = $_POST['act'];

                //添加推荐和推荐人数据。并且更新日志的推荐数
                if( D( 'Blog' )->addRecommendUser( $map,$action ) ) {
                        echo 1;
                }else {
                        echo -1;
                }
        }

        /**
         * TODO 删除
         */
        public function commentSuccess() {
            $result = json_decode(stripslashes($_POST['data']));  //json被反解析成了stdClass类型
            $count = $this->__setBlogCount($result->appid);

            //发送两条消息
            $data = $this->__getNotifyData($result);
            $this->api->comment_notify('blog',$data,$this->appId);
            echo $count;
        }

        /**
         * TODO 删除
         */
        private function __getNotifyData($data) {
            //发送两条消息
            $result['toUid'] = $data->toUid;
            $need  = $this->blog->where('id='.$data->appid)->field('uid,title')->find();


            $result['uids'] =$need['uid'];
            $result['url'] = sprintf('%s/Index/show/id/%s/mid/%s','{'.$this->appId.'}',$data->appid,$result['uids']);
            $result['title_body']['comment'] = $data->comment;
            $result['title_data']['title'] = sprintf("<a href='%s'>%s</a>",$result['url'],$need['title']);
            $result['title_data']['type']  = "日志";
            return $result;
        }

        /**
         * TODO 删除
         */
        public function deleteSuccess() {
                $id = $_POST['id'];
                echo $this->__setBlogCount($id);;
        }

        /**
         * TODO 删除
         */
        private function __setBlogCount($id) {
                $count = $this->api->comment_getCount('blog',$id);
               $this->blog->setCount($id,$count);
                return $count;
        }
        /**
         * fileAway_class
         * 获取归档查询的数据
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function fileAway_class($cid,$cateId = null) {
        	$findTime           = t($_GET['date']); //获得传入的参数
        	$this->blog->status = 1;
        	$this->blog->cid    = $cid;
        	isset( $cateId ) && $this->blog->category = intval($cateId);
        	return $this->blog->fileAway( $findTime ) ;
        }
        /**
         * fileAway
         * 获取归档查询的数据
         * @param mixed $uid
         * @access private
         * @return void
         */
        private function fileAway($uid,$cateId = null) {
                $findTime           = t($_GET['date']); //获得传入的参数
                $this->blog->status = 1;
                $this->blog->uid    = $uid;
                isset( $cateId ) && $this->blog->category = intval($cateId);
                return $this->blog->fileAway( $findTime ) ;
        }
        /**
         * __getClassBlog
         * 获得班级blog列表
         * @param int|array|string $cid cid
         * @access private
         * @return void
         */
        private function __getClassBlog ($cid=null,$field=null,$order=null,$limit=null) {
        	//将数字或者数字型字符串转换成整型
        	is_numeric( $cid ) && $cid = intval( $cid );
        	$map['class_id'] = $cid;
        	//归档
        	if( isset( $_GET['date'] ) ) {
        		return $this->fileAway( $cid, $_GET['cateId'] );//TODO
        	}

        	//分类
        	if( isset( $_GET['cateId'] ) ) {
        		$this->blog->category = intval($_GET['cateId']);
        		$this->assign( 'cateId', intval($_GET['cateId']) );
        	}
        	return $this->blog->getBlogList ($map, $field, $order);
        }
        /**
         * __getblog
         * 获得blog列表
         * @param int|array|string $uid uid
         * @access private
         * @return void
         */
        private function __getBlog ($uid=null,$field=null,$order=null,$limit=null) {
        	//将数字或者数字型字符串转换成整型
            is_numeric( $uid ) && $uid = intval( $uid );

            //归档
            if( isset( $_GET['date'] ) ) {
                    return $this->fileAway( $uid, $_GET['cateId'] );
            }

            //分类
            if( isset( $_GET['cateId'] ) ) {
                    $this->blog->category = intval($_GET['cateId']);
                    $this->assign( 'cateId', intval($_GET['cateId']) );
            }

            //给blog对象的uid属性赋值
            if( isset( $uid ) ) {
            	$map['uid']   = $uid;
                if ($uid != $this->mid) {
                	$relationship	=	getFollowState($this->mid,$uid);
					if($relationship=='eachfollow'||$relationship=='havefollow'){
						$map['private']	= array('in',array(0,2));
					}else{
						$map['private']	= 0;
					}
                }
            }else {
                    if(empty($friends)) return false;
                    $map['uid']  = array( "in",$friends);
                    $this->blog->private = array('neq',2);
            }
            if(!$limit){

            }
            return $this->blog->getBlogList ($map, $field, $order);
        }

        public function getBlogCount(){
        	$uid = $_POST['uid'];
        	$list = $this->__getBlog($uid,'*','cTime desc');
        	echo $list['count'];
        }

        /**
         * _getWiget
         * 获得需要传递给widget的数据
         * @param mixed $link
         * @param mixed $condition
         * @access private
         * @return void
         */
        private function _getWiget($link,$condition = array()) {
                $map['fileaway']  = L( 'fileaway' );
                $map['link']      = $link;
                $map['condition'] = $condition ;
                $map['limit']     = $this->blog->getConfig( 'fileawaypage' );
                $map['tableName'] = C('DB_PREFIX').'_blog';
                $map['APP']       = __APP__;
                return $map;
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
        private static function _checkCategory( $cateId,$category ) {
                $temp = array();
                foreach( $category as $value ) {
                        $temp[] = $value['id'];
                }
                return in_array($cateId,$temp);
        }
        private function _checkUser( $uid ) {
                $result = $this->api->user_getInfo($uid,'id');
                return $result;
        }

        /**
         * 是否登陆
         */
        public function isLogin(){
        	if(empty($this->mid)){
        		$returns['status'] =501;
        	}else{
        		$returns['status'] =200;
        	}
        	echo json_encode($returns);
        }

        /**
         * 赞功能
         */
        public function addDigg(){
        	if(empty($this->mid)){
        		exit(json_encode(array('status'=>0,'info'=>'请您登录')));
        	}
        	$comment_id = intval($_POST['comment_id']);
        	$result = model('CommentDigg')->addDigg($comment_id, $this->mid);
        	if($result){
        		$res['status'] = 1;
        		$res['info'] = model('CommentDigg')->getLastError();
        	}else{
        		$res['status'] = 0;
        		$res['info'] = model('CommentDigg')->getLastError();
        	}
        	exit(json_encode($res));
        }

    /**
     * 取消赞功能
     */
    public function delDigg(){
        if(empty($this->mid)){
            exit(json_encode(array('status'=>0,'info'=>'请您登录')));
        }
        $comment_id = intval($_POST['comment_id']);
        $result = model('CommentDigg')->delDigg($comment_id, $this->mid);

        if($result){
            $res['status'] = 1;
            $res['info'] = model('CommentDigg')->getLastError();
        }else{
            $res['status'] = 0;
            $res['info'] = model('CommentDigg')->getLastError();
        }
        exit(json_encode($res));
    }

}
