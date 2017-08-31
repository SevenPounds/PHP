<?php
return array(
/**
 * 资源平台栏目配置信息
 */
'columnNavs' => array (
		'0' => array (
				'show' => 'rescenter',
				'name' => '学科资源',
				'url' => C('RS_SITE_URL').'/index.php?app=changyan&mod=Rescenter&act=index',
				'ico' => 'icon/zy_xk.gif',
				'describe' => '优质同步到课资源'
		),
		'1' => array (
				'show' => 'videocenter',
				'name' => '视频资源',
				'url' => C('RS_SITE_URL').'/index.php?app=changyan&mod=Videocenter&act=index',
				'ico' => 'icon/zy_sp.gif',
				'describe' => '优质同步到课资源'
		),
		'2' => array (
				'show' => 'specialtopic',
				'name' => '专题资源',
				'url' => C('RS_SITE_URL').'/index.php?app=changyan&mod=Specialtopic&act=index',
				'ico' => 'icon/zy_zt.gif',
				'describe' => '丰富专题性质资源'
		)
),

//资讯栏目
'newsNavs' => array(
		'index' => array(
				'name'=>'资讯首页',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=index',
				'ico' =>'icon/zx_index.gif',
				'num' =>8
		),
		'jyxw' => array(
				'name'=>'教育新闻',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=jyxw',
				'ico' =>'icon/zx_jyxw.gif',
				'num' =>9
				),
		'hyfz' => array(
				'name'=>'行业发展',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=hyfz',
				'ico' =>'icon/zx_hyfz.gif',
				'num' =>6
				),
		'zcwj' => array(
				'name'=>'政策文件',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=zcwj',
				'ico' =>'icon/zx_zcwj.gif',
				'num' =>7
				),
		'zcjd' => array(
				'name'=>'政策解读',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=zcjd',
				'ico' =>'icon/zx_zcjd.gif',
				'num' =>6
				),
		'jyyj' => array(
				'name'=>'教育研究',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=jyyj',
				'ico' =>'icon/zx_jyyj.gif',
				'num' =>6
				),
		'jyxxh' => array(
				'name'=>'教育信息化',
				'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=News&act=news&type=jyxxh',
				'ico' =>'icon/zx_xxh.gif',
				'num' =>7
				)
		),

//社区
'communityNavs' => array(
			'0' => array(
					'name'=>'班级广场',
					'url' =>U('class/Index/class_square'),//C('bbs_space').'/index.php?app=class&mod=Index&act=class_square',
					'ico' =>'icon/comicon_02.gif',
			),
			'1' => array(
					'name'=>'青青校园',
					'url' =>U('class/Index/school_garden'),//C('bbs_space').'/index.php?app=class&mod=Index&act=school_garden',
					'ico' =>'icon/comicon_03.gif',
			),
			'2' => array(
					'name'=>'知识堂',
					'url' =>U('channel/Index/index'),//C('bbs_space').'/index.php?app=channel&mod=Index&act=index',
					'ico' =>'icon/comicon_04.gif',
			),
		),

'marketNavs' => array(
			'market' => array(
					'name'=>'资源市场',
					'url' =>C('RS_SITE_URL').'/index.php?app=changyan&mod=Resmarket&act=market',
					'ico' =>'icon/cs_zycs.gif',
			),
			'1' => array(
					'name'=>'APP商城',
					'url' =>'#',
					'ico' =>'icon/cs_app.gif',
			),
			'2' => array(
					'name'=>'我的店铺',
					'url' =>'#',
					'ico' =>'icon/cs_wddp.gif',
			),
		)
);
?>