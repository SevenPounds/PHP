<?php
/*
 * 游客访问的黑/白名单，不需要开放的，可以注释掉
 */
return array (
	"access" => array (
		/*
		'public/Register/*' => true, // 注册
		'public/Passport/*' => true, // 登录
		*/
		'appcenter/Index/*'	=> true,
		'public/Widget/*'	=> true, // 插件
		'page/Index/index'	=> true,
		'public/Avatar/*'   => true, //头像
		'api/*/*' => true, // API
		'admin/*/*'	=> true,
		                      
		// 网站公告
		'public/Index/announcement' => true,
		'public/Index/userFeed'		=> true,
		
		// 个人主页
		'public/Profile/index' => true,
		'public/Profile/following' => true,
		'public/Profile/follower' => true,
		'public/Profile/data' => true,
		'public/Profile/class_following' => true,
		'public/Profile/campus_following' => true,
			
		//日志功能
		'blog/Index/personal' => true,
		'blog/Index/show' => true,
		'blog/Index/isLogin' => true,
		'blog/Index/addDigg' => true,
			
		//相册功能
		'photo/Index/albums' => true,
		'photo/Index/album' => true,
		'photo/Index/photo' => true,
		'photo/Index/weibophoto' => true,
		'photo/Index/weiboalbum' => true,
		'photo/Index/isLogin' => true,
			
		//资源功能
		'teachingapp/Index/index' => true,
        
        //资源展示
		'resview/Resource/*' => true,
		'resview/Ajax/*' => true,
			
		// 微博内容
		'public/Profile/feed' => true,
		
		// 微博话题
		'public/Topic/index' => true,

		// 微博排行榜
		'public/Rank/*' => true,
		
		// 频道
		'channel/Index/*' => true,
		
		// 找人
		'people/Index/*' => true,

		// 微吧
		'weiba/Index/index' => true,
		'weiba/Index/detail' => true,
		'weiba/Index/postDetail' => true,
		'weiba/Index/postList' => true,
		'weiba/Index/weibaList' => true,
		
		//社区相关 
		'class/*/*' => true, //开放社区
			
		// 升级查询
		'public/Tool/*' => true,
		
		'wap/*/*' => true,

		'develop/Public/*' => true,
			
		//对外Api
		'reslib/WhiteboardApi/*' => true, //班班通电子版本
		'yunpan/BBT/*' => true, //班班通电子版本
		'reslib/Api/*' => true, //iflybook
		
		
		//开放android 访问
		'public/Avatar/*' => true, 
		'widget/Upload/save' => true, 

		//开放教师工作室
		'public/Workroom/*' => true, 
		'msgroup/*/*' => true, 
		'public/Ajax/*' => true, 
	),
    //会同步单点登录用户信息
	'sso_login' => array(
		'public/Index/index' => true,
        'public/Index/virtual' => true,
		'public/Mention/index' => true,
	    'public/Message/index' => true,
        'public/Profile/following' => true,
        'public/Profile/follower' => true,
		'public/Workroom/teacher_studio'=>true,
		'public/Workroom/index'=>true,
		'vote/Index/*'=>true,
		'research/Index/index'=>true,
		'research/Index/follows'=>true,
		'research/Index/center'=>true,
		'research/Index/add'=>true,
		'pingke/Index/index'=>true,
		'pingke/Index/follows'=>true,
		'pingke/Index/center'=>true,
		'pingke/Index/add'=>true,		
		'onlineanswer/Index/follows'=>true,
		'onlineanswer/Index/center'=>true,
		'onlineanswer/Index/create'=>true,
		'group/SomeOne/index'=>true,	
		'group/Index/add'=>true,
		'group/Member/index'=>true,
		'blog/Index/*'=>true,
		'photo/Index/index'=>true,
		'photo/Index/albums'=>true,
		'photo/Upload/index'=>true,
		'photo/Upload/flash'=>true,			
	),
		
	/**
	 * 注册跳转控制
	 */
	'register'=>array(
		'public/Ajax/*'=>true,
		'public/Workroom/*'=>true,
		'public/Avatar/*'=>true,
		'public/Follow/*'=>true,
		'public/FollowGroup/*'=>true,
		'public/Index/showFaceCard'=>true
	),
		
	'role_app'=>array(
		//教师app权限
		'teacher'=>array(
			'app'=>array(
				'teachingapp'=>array(),
				'research'=>array(),
				'pingke'=>array('no_permission'=>array('show')),
				'vote'=>array('no_permission'=>array('detail')),
				'onlineanswer'=>array('no_permission'=>array('detail')),
				'reslib'=>array(),
				'yunpan'=>array(),
				'paper'=>array('type'=>array(1,2), 'no_permission'=>array('preview'))
			),
			'widget'=>array()
		),
		//教研员app权限
		'instructor/province/city/district'=>array(
			'app'=>array(
				'teachingapp'=>array(),
				'eduannounce'=>array('no_permission'=>array('detail')),
				'reselection'=>array(),
				'research'=>array('no_permission'=>array('show')),
				'pingke'=>array('no_permission'=>array('show')),
				'vote'=>array('no_permission'=>array('detail')),
				'onlineanswer'=>array('no_permission'=>array('detail')),
				'reslib'=>array(),
				'yunpan'=>array(),
				'paper'=>array('type'=>array(4,6,7,8,9,10), 'no_permission'=>array('preview'))
			),
			'widget'=>array()
		),
        //家长app权限
		'parents'=>array(
			'app'=>array(
				'teachingapp'=>array(),
				'onlineanswer'=>array('no_permission'=>array('detail')),
				'reslib'=>array(),
				'yunpan'=>array(),
                'vote'=>array(),
			),
			'widget'=>array()
		),
        //学生app权限
		'student'=>array(
			'app'=>array(
				'teachingapp'=>array(),
				'onlineanswer'=>array('no_permission'=>array('detail')),
				'reslib'=>array(),
				'yunpan'=>array(),
                'vote'=>array(),
			),
			'widget'=>array()
		),
        //教育管理者app权限
        'eduadmin'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
				'teachingapp'=>array(),
				'yunpan'=>array()
            ),
            'widget'=>array()
        ),

        //省级教研员 app权限
        'province'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),

        //市级教研员 app权限
        'city'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
        //区县级教研员 app权限
        'district'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
        //机构用户 app权限
        'edupersonnel'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
        //超级管理员 app权限
        'ledcsuperadmin'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),

        //系统管理员 app权限
        'deptLeader'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),

        //学校管理员 app权限
        'ledcschoolMng'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),

        //区县管理员 app权限
        'ledcdistrictMng'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
        //市级管理员 app权限
        'ledccityMng'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
        //省级管理员 app权限
        'ledcprovinceMng'=>array(
            'app'=>array(
                'research'=>array(),
                'pingke'=>array(),
                'vote'=>array(),
                'onlineanswer'=>array(),
                'teachingapp'=>array(),
                'yunpan'=>array()
            ),
            'widget'=>array()
        ),
	)
);
