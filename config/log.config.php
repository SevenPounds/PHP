<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2016/3/2
 * Time: 11:37
 */

return array(
    //应用分类
    'appType' => [
        'jyyy' => array('name' => '教研应用','code' => 'JYYY'),'hdjl' => array('name' => '互动交流','code' => 'HDJL'),
        'zxkt' => array('name' => '在线课堂','code' => 'ZXKT'),'wpyy' =>array('name' => '网盘应用','code' => 'WPYY'),
        'jyzy' => array('name' => '教育资源','code' => 'JYZY'),'jxyy' => array('name' => '教学应用','code' => 'JXYY')
    ],
    //所属系统分类
    'appId'=> [
        'wldy' => array('name' => '网络调研','code' => 'WLDY'),'zttl' => array('name' => '主题讨论','code' => 'ZTTL'),
        'wspk' => array('name' => '网上评课','code' => 'WSPK'),'zxdy' => array('name' => '在线答疑','code' => 'ZXDY'),
        'jgtj' => array('name' => '监管统计','code' => 'JGTJ'),'wljy' => array('name' => '网络教研','code' => 'WLJY'),
        'xqqz' => array('name' => '兴趣圈子','code' => 'XQQZ'),'xxsq' => array('name' => '学校社区','code' => 'XXSQ'),
        'jssq' => array('name' => '教师社区','code' => 'JSSQ'),'grkj' => array('name' => '个人空间','code' => 'GRKJ'),
        'wdbj' => array('name' => '我的班级','code' => 'WDBJ'),'wdxx' => array('name' => '我的学校','code' => 'WDXX'),
        'rz' => array('name' => '日志','code' => 'RZ'),'xc' => array('name' => '相册','code' => 'XC'),
        'ss' => array('name' => '说说','code' => 'SS'),'hy' => array('name' => '好友','code' => 'HY'),
        'gkk' =>array('name' => '公开课','code' => 'GKK'),'ktwk' => array('name' => '课堂微课','code' => 'KTWK'),
        'wdwd' => array('name' => '我的文档','code' => 'WDWD'),'wdbkb' => array('name' => '我的备课本','code' => 'WDBKB'),
        'ysyyk' => array('name' => '一师一优课','code' => 'YSYYK'),'zyzx' => array('name' => '资源中心','code' => 'ZYZX'),
        'xnsys' => array('name' => '虚拟实验室','code' => 'XNSYS'),'wlbk' => array('name' => '网络备课','code' => 'WLBK')
    ],
    //操作分类
    'opType' => [
        'upload' => array('op' => '上传','code' => '01'),
        'share' => array('op' => '分享','code' => '02'),
        'comment' => array('op' => '评论','code' => '03'),
        'update' => array('op' => '修改','code' => '04'),
        'create' => array('op' => '发布/创建','code' => '05'),
        'reply' => array('op' => '回复','code' => '06'),
    ],
    //存储位置分类
    'location' => [
        'localServer' => array('name' => '本地服务器','code' => '01'),
        'attachServer' => array('name' => '附件服务','code' => '02'),
        'resourceServer' => array('name' => '资源网关','code' => '03'),
        'panServer' => array('name' => '网盘网关','code' => '04'),
    ],
    'LOGRECORD_URL' => "/log/",
    "LOGRECORD_SIZE"  => 1024*1024*20

);