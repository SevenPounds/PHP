<?php
//跳转家校互动传递参数
$cyuser = $this->cyuserdata;
$login_name =base64_encode($cyuser['user']['login']);
// $role = strval($cyuser['rolelist'][0]['name']);
$role=$this->roleEnName;

switch($role){
    case 'student':
        $typeNum = '1'; break;
    case 'parent':
        $typeNum = '2';break;
    case 'teacher':
        $typeNum= '3';break;
    default:
        $typeNum = '';break;

}
$user_type = $typeNum;
$ygjy_url = C('RS_SITE_URL').'/index.php?mod=RedirectServer&act=dealJxhd&login_name='.$login_name.'&user_type='.$user_type;

// 获取名师工作室列表
$groups = D('MSGroup', 'msgroup')->getMsGroupByUid($this->mid);

// 获取我的学校和我的班级信息
$cyClient = new CyClient();
$userCyuid = $GLOBALS['ts']['user']['cyuid'];
// 如果是家长，则获取孩子的学校和班级
if($role == 'parent'){
	$children = $cyClient->listChildren($userCyuid);
	$userCyuid = $children[0]->id;
}
$schools = $cyClient->listSchoolByUser($userCyuid);
$classes = $cyClient->listClassByUser($userCyuid);
if($schools){
	$schoolId = $schools[0]->id;
}
if($classes){
	$classId = $classes[0]->id;
}

/**
 * 全部应用
 * @var unknown_type
 */
$apps = array(
		"wdwd" => array("appname" => "我的网盘", "url" => C('PAN_SITE_URL').'index.php?m=Disk&c=Index&a=index', "target"=>true, 'app_icon' => "app_wdwd.png","class"=>"stu_wdwd","class_t"=>"tea-wdwp"),		
		"wdbkb" => array("appname" => "我的备课本", "url" =>C('PAN_SITE_URL').'index.php?m=Disk&c=CloudBook&a=index', "target"=>true, 'app_icon' => "app_wdbkb.png","class"=>"stu_wdbkb","class_t"=>"tea-wdbkb"),
		//"ktjl" => array("appname" => "课堂记录", "url" => C('PAN_SITE_URL').'index.php?m=Disk&c=Subject&a=index', "target"=>true, 'app_icon' => "app_ktjl.png"),
		"wdwk" => array("appname" => "课堂微课", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=TeachingRecord&a=record&classId='.$classId, "target"=>true, 'app_icon' => "app_wdwk.png","class"=>"stu_wdwk"),
		"wdxx" => array("appname" => "我的学校", "url" => C('ESCHOOL').'/index.php?m=School&schoolId='.$schoolId, "target"=>true, 'app_icon' => "app_wdxx.png" ,"class"=>"stu-wdxx","class_t"=>"tea-wdxx"),
		"wdbj" => array("appname" => "我的班级", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Common&a=comeIntoClass', "target"=>true, 'app_icon' => "app_wdbj.png","class"=>"stu-wdbj","class_t"=>"tea-wdbj"),
		"xqqz" => array("appname" => "圈子", "url" => U('group/SomeOne/index'), "target"=>true, 'app_icon' => "app_xqqz.png","class"=>"stu_xqqz","class_t"=>"tea-qz"),
		//"msgzs" => array("appname" => "名师工作室", "url" => U('msgroup/Index/index',array('gid'=>$groups[0]['gid'])), "target"=>true, 'app_icon' => "app_msgzs.png","class"=>"stu_msgzs"),
		//"msgzs" => array("appname" => "名师工作室", "url" => 'javascript:;',"target"=>false, 'app_icon' => "app_msgzs.png","class_t"=>"tea-ms"),

		"tk" => array("appname" => "题库", "url" => C('QUESTION_LIBRARY'), "target"=>true,),
		"tszy" => array("appname" => "听说作业", "url" => U('public/Index/platform'),),
		"zyzx" => array("appname" => "作业中心", "url" => C('HW_SITE_URL').'index.php?m=Homework&c=Index&a=index', "target"=>true,"class"=>"stu_zyzx"),
		//"jxhd" => array("appname" => "家校互动", "url" => C('JXHD'), "target"=>true,'app_icon' => "app_jxhd.png","class"=>"stu_jxhd","class_t"=>"tea-jxhd"),
		//"jxhd_yybs" => array("appname" => "家校互动(运营商版)", "url" => C('YGJY_URL'), "target"=>true,),
		"jypt" => array("appname" => "教研平台", "url" => C('QX_WORKTABLE_URL'), "target"=>true,),
		"zttl" => array("appname" => "主题讨论", "url" => U('research/Index/center'),'app_icon' => "app_zttl.png","class"=>"stu_zttl","class_t"=>"tea-zttl"),
		"wldy" => array("appname" => "网络调研", "url" => U('vote/Index/center'),'app_icon' => "app_wldy.png","class"=>"stu_wldy","class_t"=>"tea-wldy"),
		"wspk" => array("appname" => "网上评课", "url" => U('pingke/Index/center'),'app_icon' => "app_wspk.png","class"=>"stu_wspk","class_t"=>"tea-wspk"),
		"zxdy" => array("appname" => "在线答疑", "url" => U('onlineanswer/Index/center'),'app_icon' => "app_zxdy.png","class"=>"stu_zxdy","class_t"=>"tea-zxdy"),
		"xnsys" => array("appname" => "虚拟实验室", "url" => U('public/Index/virtual'),'app_icon' => "app_xnsys.png","class"=>"stu_xnsys","class_t"=>"tea-xnsys"),
		"dztsg" => array("appname" => "电子图书馆", "url" => U('public/Index/library'),),
		"szqk" => array("appname" => "数字期刊", "url" =>  U('public/Index/journal'),),
		//"bkgj" => array("appname" => "备课工具", "url" => 'javascript:void(0);',),
		"jszs" => array("appname" => "教师助手", "url" => C("RS_SITE_URL").'/index.php?app=changyan&mod=TeachingPlatform&act=index', "target"=>true,"class"=>"stu_wldy"),
		"zylx" => array("appname" => "资源遴选", "url" => U('resselection/Index/index'),),
		//"jgtj" => array("appname" => "应用监管", "url" => C('TRACK'),'app_icon' => "app_jgtj.png","class"=>"stu_jgtj","class_t"=>"yyjg"),
		"pjxt" => array("appname" => "评价系统", "url" => C('PJ_TEACHER'), "target"=>true,"class"=>"stu_pjxt"),
		"gkk" => array("appname" => "公开课", "url" => U('public/Lesson/index'),'app_icon' => "app_gkk.png","class"=>"stu_gkk"),
		
		"ss" => array("appname" => "说说", "url" => U('public/Index/userFeed',array('uid'=>$this->mid)), "target"=>true,'app_icon' => "app_ss.png","class"=>"stu_ss","class_t"=>"tea-ss"),
		"rz" => array("appname" => "日志", "url" => U('blog/Index/index',array('uid'=>$this->mid,'type'=>'blog')), "target"=>true,'app_icon' => "app_rz.png","class"=>"stu_rz","class_t"=>"tea-rz"),
		"xc" => array("appname" => "相册", "url" => U('photo/Index/albums',array('uid'=>$this->mid)), "target"=>true,'app_icon' => "app_xc.png","class"=>"stu_xc","class_t"=>"tea-xz"),
		"hy" => array("appname" => "好友", "url" => U('public/Profile/following',array('uid'=>$this->mid)), "target"=>true,'app_icon' => "app_hy.png","class"=>"stu_hy","class_t"=>"tea-hy"),
		
		//"zybz" => array("appname" => "作业布置", "url" => C('HW_SITE_URL').'index.php?m=Homework&c=Index&a=index', "target"=>true,'app_icon' => "app_wdwd.png","class"=>"stu_zybz","class_t"=>"tea-bzzy"),
		"bjzy" => array("appname" => "班级作业", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Homework&a=homework&classId='.$classId, "target"=>true,'app_icon' => "app_wdwd.png","class"=>"stu_bjzy"),
		"bjgx" => array("appname" => "班级共享", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Share&a=share&classId='.$classId, "target"=>true,'app_icon' => "app_wdwd.png","class"=>"stu_bjgx"),
		"bjlb" => array("appname" => "班级聊吧", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Topic&a=topic&classId='.$classId, "target"=>true,'app_icon' => "app_wdwd.png","class"=>"stu_bjlb"),
		
		"zxtw" => array("appname" => "在线提问", "url"=>U('onlineanswer/Index/center',array('uid'=>$GLOBALS['ts']['uid'],'type'=>'blog')),'app_icon' => "app_wdwd.png","class"=>"stu_zxtw"),
		//"zxtp" => array("appname" => "在线投票", "url"=>U('stuvote/Index/center',array('uid'=>$GLOBALS['ts']['uid'])),'app_icon' => "app_wdwd.png","class"=>"stu_zxtp"),
		//"hzxx" => array("appname" => "互助学习", "url"=>U('hzlearning/Index/center',array('uid'=>$GLOBALS['ts']['uid'])),'app_icon' => "app_wdwd.png","class"=>"stu_hzxx"),
		//"hzdbj" => array("appname" => "孩子的班级", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Common&a=comeIntoClass', "target"=>true, 'app_icon' => "app_wdbj.png","class"=>"stu-wdbj"),
		//"hzdxx" => array("appname" => "孩子的学校", "url" => C('ESCHOOL').'/index.php?m=School&schoolId='.$schoolId, "target"=>true, 'app_icon' => "app_wdxx.png","class"=>"stu-wdxx"),
		
		//"hzdzy" => array("appname" => "孩子的作业", "url" => C('ESCHOOL').'/index.php?m=Clazz&c=Homework&a=homework&classId='.$classId, "target"=>true, 'app_icon' => "app_wdbj.png","class"=>"stu_bjzy"),
		//"hzdpy" => array("appname" => "孩子的评语", "url" => C('JXHD').'index.php?m=Home&c=Remark&a=index', "target"=>true, 'app_icon' => "app_wdbj.png","class"=>"stu_hzdpy"),
		//"xxtz" => array("appname" => "学校通知", "url" => C('JXHD').'index.php?m=Home&c=Notice&a=index', "target"=>true, 'app_icon' => "app_wdxx.png","class"=>"stu_xxtz"),
		//"xxyjx" => array("appname" => "学校意见箱", "url" => C('JXHD').'index.php?m=Home&c=Opinion&a=index', "target"=>true, 'app_icon' => "app_wdbj.png","class"=>"stu_xxyjx"),
		
		//"yynsds" => array("appname" => "应用能手大赛", "url" => C('RS_SITE_URL').'index.php?app=match&mod=Index&act=index', "target"=>true, 'app_icon' => "app_wdbj.png","class_t"=>"tea-yynsds"),//应用能手
		"htgl" => array("appname" => "后台管理", "url" => U('public/Index/jumpToManage'), "target"=>true, 'app_icon' => "app_wdbj.png","class_t"=>"tea-htgl"),
);

switch($role){
	case 'student':
		$apps['wdwd']['url'] = 'javascript:void(0);';
		$apps['wdwd']['title'] = '敬请期待';
		$apps['wdwd']['target'] = false;
		break;
	case 'parent':
		$apps['wdwd']['url'] = 'javascript:void(0);';
		$apps['wdwd']['title'] = '敬请期待';
		$apps['wdwd']['target'] = false;
		break;
}

// 教师应用列表
$apps_teacher = array(
		'app_space' => array(
				/* 'tk' => $apps['tk'], 'tszy' => $apps['tszy'],
				'zyzx' => $apps['zyzx'], 'xnsys' => $apps['xnsys'],
				'pjxt' => $apps['pjxt'], 'dztsg' => $apps['dztsg'],
				'szqk' => $apps['szqk'], 'bkgj' => $apps['bkgj'],
				'jszs' => $apps['jszs'], 'jypt' => $apps['jypt'],
				'zttl' => $apps['zttl'], 'wldy' => $apps['wldy'],
				'wspk' => $apps['wspk'], 'zxdy' => $apps['zxdy'],
				'jxhd' => $apps['jxhd'], 'rz' => $apps['rz'],'jxhd_yybs' => $apps['jxhd_yybs'],
				'xc' => $apps['xc'], 'ss' => $apps['ss'],
				'hy' => $apps['hy'], */ 
				'xqqz' => $apps['xqqz'],
//                'ss' => $apps['ss'],
                'rz' => $apps['rz'],
                'hy' => $apps['hy'],
                'xc' => $apps['xc'],
		),
		'app_mine' => array(
				'wdwd' => $apps['wdwd'], //'wdbkb' => $apps['wdbkb'],'ktjl' => $apps['ktjl'],'wdwk' => $apps['wdwk'],
				'wdxx' => $apps['wdxx'],'wdbj' => $apps['wdbj']//,'yynsds'=>$apps['yynsds'],'jxhd' => $apps['jxhd'],//'msgzs' => $apps['msgzs'],
				 //'xqqz' => $apps['xqqz'],
		),
		
		//教学应用
		'app_jx' => array(
				'wdbkb' => $apps['wdbkb'],//'zybz' => $apps['zybz'],
				'xnsys' => $apps['xnsys'],
                'zxdy' => $apps['zxdy'],
		),
		
		//教研应用
		'app_jy' => array(
				'zttl' => $apps['zttl'],
                'wspk' => $apps['wspk'],
                'wldy' => $apps['wldy'],
		),
);

// 教研员应用列表
$apps_instructor = array(
		'app_space' => array(
				/* 'dztsg' => $apps['dztsg'], 'szqk' => $apps['szqk'],
				'jypt' => $apps['jypt'], 'zylx' => $apps['zylx'],
				'jgtj' => $apps['jgtj'], 'zttl' => $apps['zttl'],
				'wldy' => $apps['wldy'], 'wspk' => $apps['wspk'],
				'zxdy' => $apps['zxdy'], */
				'xqqz' => $apps['xqqz'],
//                'ss' => $apps['ss'],
				'rz' => $apps['rz'],
                'hy' => $apps['hy'],
				'xc' => $apps['xc'],
				
		),
		'app_mine' => array(
				'wdwd' => $apps['wdwd'],
                'zttl' => $apps['zttl'],
                'yynsds'=>$apps['yynsds'],
                'wspk' => $apps['wspk'],
				'wldy' => $apps['wldy'],
                'zxdy' => $apps['zxdy'],
                'msgzs' => $apps['msgzs'],
                'jgtj' => $apps['jgtj'],
				
		),
);

// 学生应用列表
$apps_student = array(
		'app_space' => array(
				/* 'tk' => $apps['tk'], 'tszy' => $apps['tszy'],
				'xnsys' => $apps['xnsys'], 'dztsg' => $apps['dztsg'],
				'szqk' => $apps['szqk'], 'jypt' => $apps['jypt'],
				'zxdy' => $apps['zxdy'], 'jxhd' => $apps['jxhd'],'jxhd_yybs' => $apps['jxhd_yybs'], */
				'xqqz' => $apps['xqqz'],
//				'ss' => $apps['ss'],
                'rz' => $apps['rz'],
				'hy' => $apps['hy'],
                'xc' => $apps['xc'],

		),
		'app_mine' => array(
				/* 'wdwd' => $apps['wdwd'], */
				'wdxx' => $apps['wdxx'],
				'wdbj' => $apps['wdbj'],
               /* 'xqqz' => $apps['xqqz'], */

		),
		//学习应用
		'app_xx' => array(
				'wdwk' => $apps['wdwk'],
                 'bjzy' => $apps['bjzy'],
				'bjgx' => $apps['bjgx'],
                'bjlb' => $apps['bjlb'],
				'xnsys' => $apps['xnsys'],
                'zxtw' => $apps['zxtw'],
				'zxtp' => $apps['zxtp'],
                'hzxx' => $apps['hzxx'],
		
		),
);

// 家长应用列表
$apps_parent = array(
		'app_space' => array(
				/* 'tszy' => $apps['tszy'], 'zxdy' => $apps['zxdy'],
				'xnsys' => $apps['xnsys'], 'dztsg' => $apps['dztsg'],
				'szqk' => $apps['szqk'],'jxhd' => $apps['jxhd'], 'jxhd_yybs' => $apps['jxhd_yybs'], */
				'xqqz' => $apps['xqqz'],
//				'ss' => $apps['ss'],'
				'rz' => $apps['rz'],
				'hy' => $apps['hy'],
                'xc' => $apps['xc'],
		),
		'app_mine' => array(
				'hzdxx' => $apps['hzdxx'],
                'hzdbj' => $apps['hzdbj'],
		),
		//家长应用
		'app_jz' => array(
				'hzdzy' => $apps['hzdzy'],
                'hzdpy' => $apps['hzdpy'],
                'xxtz' => $apps['xxtz'],
				'xxyjx' => $apps['xxyjx'],
                'xnsys' => $apps['xnsys'],
                'zxdy' => $apps['zxdy'],
		),
);

//教育管理者应用列表
$eduadmin = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//普通用户应用列表
$member = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//团队成员应用列表
$teammember = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//机构用户应用列表
$edupersonnel = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//超级管理员应用列表
$ledcsuperadmin = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//系统管理员应用列表
$deptLeader = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//学校管理员应用列表
$ledcschoolMng = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//区县管理员应用列表
$ledcdistrictMng = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//市级管理员应用列表
$ledccityMng = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

//省级管理员应用列表
$ledcprovinceMng = array(
    'app_space' => array(
        'xqqz'  => $apps['xqqz'],
//		'ss'    => $apps['ss'],'
        'rz'    => $apps['rz'],
        'hy'    => $apps['hy'],
        'xc'    => $apps['xc'],
    ),
);

return array(
	'teacher' => $apps_teacher,
	'instructor' => $apps_instructor,
	'student' => $apps_student,
	'parent' => $apps_parent,
	'htgl'	=>$apps['htgl'],
    'eduadmin'=> $eduadmin,
    'member'=>$member,
    'teammember'=>$teammember,
    'edupersonnel'=>$edupersonnel,
    'ledcsuperadmin'=>$ledcsuperadmin,
    'deptLeader'=>$deptLeader,
    'ledcschoolMng'=>$ledcschoolMng,
    'ledcdistrictMng'=>$ledcdistrictMng,
    'ledccityMng'=>$ledccityMng,
    'ledcprovinceMng'=>$ledcprovinceMng,
);