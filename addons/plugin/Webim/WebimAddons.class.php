<?php

/**
 * WebIM插件
 * @author ery.lee at gmail.com
 * @version 5.0
 * @copyright nextalk.im
 */
class WebimAddons extends NormalAddons
{
    protected $version = 'v5.6';
    protected $author  = '杭州巨鼎信息技术有限公司';
    protected $thanks  = 'ery lee at gmail.com';
    protected $site    = 'http://nextalk.im';
    protected $info    = 'WebIM微博站内即时消息';
    protected $pluginName = 'WebIM';
    protected $tsVersion  = "3.0";

    /**
     * getHooksInfo
     * 获得该插件使用了哪些钩子聚合类，哪些钩子是需要进行排序的
     * @access public
     * @return void
     */
    public function getHooksInfo(){
        $hooks['list'] = array('WebimHooks');
        return $hooks;
    }

    public function adminMenu() {
        $menu = array('config' => '设置',
                      'skin' => '主题',
                      'history' => '清除历史',);
        return $menu;
    }

    public function start() {
        //return true;
    }

    /**
     * 安裝插件，初始化WebIM數據庫表
     */
    public function install() {     

        $db_prefix = C('DB_PREFIX');
        $sql = "CREATE TABLE `{$db_prefix}webim_settings` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `uid` varchar(40) NOT NULL DEFAULT '',
                  `data` text,
                  `created` datetime DEFAULT NULL,
                  `updated` datetime DEFAULT NULL,
                  UNIQUE KEY `webim_setting_uid` (`uid`),
                  PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_histories` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `send` tinyint(1) DEFAULT NULL,
              `type` varchar(20) DEFAULT NULL,
              `to` varchar(50) NOT NULL,
              `from` varchar(50) NOT NULL,
              `nick` varchar(20) DEFAULT NULL COMMENT 'from nick',
              `body` text,
              `style` varchar(150) DEFAULT NULL,
              `timestamp` double DEFAULT NULL,
              `todel` tinyint(1) NOT NULL DEFAULT '0',
              `fromdel` tinyint(1) NOT NULL DEFAULT '0',
              `created` date DEFAULT NULL,
              `updated` date DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `webim_history_timestamp` (`timestamp`),
              KEY `webim_history_to` (`to`),
              KEY `webim_history_from` (`from`),
              KEY `webim_history_send` (`send`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_rooms` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `owner` varchar(40) NOT NULL,
              `name` varchar(40) NOT NULL,
              `nick` varchar(60) NOT NULL DEFAULT '',
              `topic` varchar(60) DEFAULT NULL,
              `url` varchar(100) DEFAULT '#',
              `created` datetime DEFAULT NULL,
              `updated` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `webim_room_name` (`name`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_rooms` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `owner` varchar(40) NOT NULL,
              `name` varchar(40) NOT NULL,
              `nick` varchar(60) NOT NULL DEFAULT '',
              `topic` varchar(60) DEFAULT NULL,
              `url` varchar(100) DEFAULT '#',
              `created` datetime DEFAULT NULL,
              `updated` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `webim_room_name` (`name`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_members` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `room` varchar(60) NOT NULL,
              `uid` varchar(40) NOT NULL,
              `nick` varchar(60) NOT NULL,
              `joined` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `webim_member_room_uid` (`room`,`uid`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_blocked` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `room` varchar(60) NOT NULL,
              `uid` varchar(40) NOT NULL,
              `blocked` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `webim_blocked_room_uid` (`uid`,`room`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        $sql = "CREATE TABLE `{$db_prefix}webim_visitors` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(60) DEFAULT NULL,
              `ipaddr` varchar(60) DEFAULT NULL,
              `url` varchar(100) DEFAULT NULL,
              `referer` varchar(100) DEFAULT NULL,
              `location` varchar(100) DEFAULT NULL,
              `created` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `webim_visitor_name` (`name`)
        )ENGINE=MyISAM AUTO_INCREMENT=10000 DEFAULT CHARSET=utf8";
        D()->execute($sql);

        return true;
    }

    public function uninstall() {
        $db_prefix = C('DB_PREFIX');
        $tables = array(
            'webim_settings',
            'webim_histories',
            'webim_rooms',
            'webim_members',
            'webim_blocked',
            'webim_visitors',
        );
        foreach($tables as $table) {
            $sql = "DROP TABLE IF EXISTS `{$db_prefix}{$table}`;";
            D()->execute($sql);
        }
        //TODO:
        return true;
    }

}
