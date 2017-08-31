<?php
//跳转家校互动传递参数
$cyuser = $this->cyuserdata;
$login_name =base64_encode($cyuser['user']['login']);
//$role = strval($cyuser['rolelist'][0]['name']);
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
		"wdwd" => array("title"=>"", "appname" => "我的文档", "url" => C('PAN_SITE_URL').'index.php?m=Disk&c=Index&a=index', "target"=>true, 'img' => "__THEME__/app_icon/app_wdwd.png"),		
		"wdbkb" => array("title"=>"","appname" => "我的备课本", "url" =>C('PAN_SITE_URL').'index.php?m=Disk&c=CloudBook&a=index', "target"=>true, 'img' => "__THEME__/app_icon/app_wdbkb.png"),
		"ktjl" => array( "title"=>"","appname" => "课堂记录", "url" => C('PAN_SITE_URL').'index.php?m=Disk&c=Subject&a=index', "target"=>true, 'img' => "__THEME__/app_icon/app_ktjl.png"),
		"wdwk" => array( "title"=>"","appname" => "我的微课", "url" => C('PAN_SITE_URL').'index.php?m=Disk&c=WeiKe&a=index', "target"=>true, 'img' => "__THEME__/app_icon/app_wdwk.png"),
		"wdxx" => array("title"=>"", "appname" => "我的学校", "url" => C('ESCHOOL').'index.php?m=School&schoolId='.$schoolId, "target"=>true, 'img' => "__THEME__/app_icon/app_wdxx.png"),
		"wdbj" => array("title"=>"", "appname" => "我的班级", "url" => C('ESCHOOL').'index.php?m=Clazz&classId='.$classId, "target"=>true, 'img' => "__THEME__/app_icon/app_wdbj.png"),
		"xqqz" => array("title"=>"", "appname" => "兴趣圈子", "url" => U('group/SomeOne/index'), "target"=>true, 'img' => "__THEME__/app_icon/app_xqqz.png"),
		"msgzs" => array("title"=>"", "appname" => "名师工作室", "url" => U('msgroup/Index/index',array('gid'=>$groups[0]['gid'])), "target"=>true, 'img' => "__THEME__/app_icon/app_msgzs.png"),

		"tk" => array("title"=>"", "appname" => "题库", "url" => C('QUESTION_LIBRARY'), "target"=>true, 'img' => "__THEME__/app_icon/app_tk.png"),
		"tszy" => array("title"=>"", "appname" => "听说作业", "url" => U('public/Index/platform'), 'img' => "__THEME__/app_icon/app_tszy.png"),
		"zyzx" => array("title"=>"", "appname" => "作业中心", "url" => C('HW_SITE_URL').'index.php?m=Homework&c=Index&a=index', 'img' => "__THEME__/app_icon/app_zyzx.png"),
		"jxhd" => array("title"=>"", "appname" => "家校互动", "url" => $ygjy_url, "target"=>true, 'img' => "__THEME__/app_icon/app_jxhd.png"),
		"jypt" => array("title"=>"", "appname" => "教研平台", "url" => C('QX_WORKTABLE_URL'), "target"=>true, 'img' => "__THEME__/app_icon/app_jypt.png"),
		"zttl" => array("title"=>"", "appname" => "主题讨论", "url" => U('research/Index/index'), 'img' => "__THEME__/app_icon/app_zttl.png"),
		"wldy" => array("title"=>"", "appname" => "网络调研", "url" => U('vote/Index/index'), 'img' => "__THEME__/app_icon/app_wldy.png"),
		"wspk" => array("title"=>"", "appname" => "网上评课", "url" => U('pingke/Index/index'), 'img' => "__THEME__/app_icon/app_wspk.png"),
		"zxdy" => array("title"=>"", "appname" => "在线答疑", "url" => U('onlineanswer/Index/index'), 'img' => "__THEME__/app_icon/app_zxdy.png"),
		"xnsys" => array("title"=>"", "appname" => "虚拟实验室", "url" => U('public/Index/virtual'), 'img' => "__THEME__/app_icon/app_xnsys.png"),
		"dztsg" => array("title"=>"", "appname" => "电子图书馆", "url" => U('public/Index/library'), 'img' => "__THEME__/app_icon/app_dztsg.png"),
		"szqk" => array("title"=>"", "appname" => "数字期刊", "url" =>  U('public/Index/journal'), 'img' => "__THEME__/app_icon/app_szqk.png"),
		"bkgj" => array("title"=>"敬请期待", "appname" => "备课工具", "url" => 'javascript:void(0);', 'img' => "__THEME__/app_icon/app_bkgj.png"),
		"dmtjxxt" => array("title"=>"", "appname" => "多媒体教学系统", "url" => C("RS_SITE_URL").'/index.php?app=changyan&mod=TeachingPlatform&act=index', "target"=>true, 'img' => "__THEME__/app_icon/app_dmtjxxt.png"),
		"zylx" => array("title"=>"敬请期待", "appname" => "资源遴选", "url" => 'javascript:void(0);', 'img' => "__THEME__/app_icon/app_zylx.png"),
		"jgtj" => array("title"=>"", "appname" => "监管统计", "url" => C('TRACK'), 'img' => "__THEME__/app_icon/app_jgtj.png"),
		"pjxt" => array("title"=>"", "appname" => "评价系统", "url" => C('PJ_TEACHER'), "target"=>true, 'img' => "__THEME__/app_icon/app_pjxt.png"),
		
		"ss" => array("title"=>"", "appname" => "说说", "url" => U('public/Index/userFeed',array('uid'=>$this->mid)), "target"=>true, 'img' => "__THEME__/app_icon/app_ss.png"),
		"rz" => array("title"=>"", "appname" => "日志", "url" => U('blog/Index/index',array('uid'=>$this->mid,'type'=>'blog')), "target"=>true, 'img' => "__THEME__/app_icon/app_rz.png"),
		"xc" => array("title"=>"", "appname" => "相册", "url" => U('photo/Index/albums',array('uid'=>$this->mid)), "target"=>true, 'img' => "__THEME__/app_icon/app_xc.png"),
		"hy" => array("title"=>"", "appname" => "好友", "url" => U('public/Profile/following',array('uid'=>$this->mid)), "target"=>true, 'img' => "__THEME__/app_icon/app_hy.png"),
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
$tApps = array(
		'app_space' => array(
				$apps['tk'],$apps['tszy'],
				$apps['zyzx'],$apps['xnsys'],
				$apps['pjxt'],$apps['dztsg'],
				$apps['szqk'],$apps['bkgj'],
				$apps['dmtjxxt'],$apps['jypt'],
		),
		'app_hide' => array(
				$apps['zttl'],$apps['wldy'],
				$apps['wspk'],$apps['zxdy'],
				$apps['jxhd'],$apps['rz'],
				$apps['xc'],$apps['ss'],
				$apps['hy'],
		),
		'app_mine' => array(
				$apps['wdwd'],$apps['wdbkb'],
				$apps['ktjl'],$apps['wdwk'],
				$apps['wdxx'],$apps['wdbj'],
				$apps['msgzs'],$apps['xqqz'],
		),
);

// 教研员应用列表
$rApps = array(
		'app_space' => array(
				$apps['dztsg'],$apps['szqk'],
				$apps['jypt'],$apps['zylx'],
				$apps['jgtj'],$apps['zttl'],
				$apps['wldy'],$apps['wspk'],
				$apps['zxdy'],$apps['ss'],
				$apps['rz'],$apps['xc'],
				$apps['hy'],
		),
		'app_mine' => array(
				$apps['wdwd'],$apps['xqqz'],$apps['msgzs'],
		),
);

// 学生应用列表
$sApps = array(
		'app_space' => array(
				$apps['tk'],$apps['tszy'],//$apps['kwzy'],
				$apps['xnsys'],$apps['dztsg'],
				$apps['szqk'],$apps['jypt'],
				$apps['zxdy'],$apps['jxhd'],
				$apps['ss'],$apps['rz'],
				$apps['xc'],$apps['hy'],
		),
		'app_mine' => array(
				$apps['wdwd'],$apps['wdxx'],$apps['wdbj'],$apps['xqqz'],
		),
);

// 家长应用列表
$pApps = array(
		'app_space' => array(
				$apps['tszy'],$apps['zxdy'],
				$apps['jxhd'],$apps['ss'],
				$apps['rz'],$apps['xc'],
				$apps['hy'],
		),
		'app_mine' => array(
				$apps['wdxx'],$apps['wdbj'],$apps['xqqz'],
		),
);

return array(
	'teacher' => $tApps,
	'instructor' => $rApps,
	'student' => $sApps,
	'parent' => $pApps
);