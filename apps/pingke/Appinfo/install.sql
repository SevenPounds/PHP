
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_pingke`
-- ----------------------------
DROP TABLE IF EXISTS `ts_pingke`;
CREATE TABLE `ts_pingke` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `uid` int(11) NOT NULL COMMENT '评课创建人id',
  `title` varchar(255) NOT NULL COMMENT '评课标题',
  `description` text COMMENT '评课介绍',
  `teacher` varchar(20) DEFAULT NULL COMMENT '授课老师',
  `createtime` int(10) NOT NULL COMMENT '创建时间',
  `modifiedtime` int(10) DEFAULT NULL COMMENT '修改时间',
  `closedtime` int(10) DEFAULT NULL COMMENT '关闭时间',
  `status` int(10) NOT NULL COMMENT '课题进行状态 1：进行中  0：已完成',
  `public_status` int(10) DEFAULT NULL COMMENT '评课结果是否公开 1：公开  0：不公开',
  `discuss_count` int(11) DEFAULT '0' COMMENT '评论次数',
  `member_count` int(11) DEFAULT '0' COMMENT '参加成员总数',
  `video_id` varchar(100) NOT NULL COMMENT '评课视频的网关资源id',
  `summary_attachid` int(11) DEFAULT NULL COMMENT '评课总结文件附件id',
  `summary_name` varchar(255) DEFAULT NULL COMMENT '评课总结文件名称',
  `summary_path` varchar(255) DEFAULT NULL COMMENT '评课总结文件路径',
  `province` varchar(10) DEFAULT NULL COMMENT '省编号',
  `city` varchar(10) DEFAULT NULL COMMENT '市编号',
  `county` varchar(10) DEFAULT NULL COMMENT '区县编号',
  `subject` varchar(10) DEFAULT NULL COMMENT '学科编号',
  PRIMARY KEY (`id`),
  KEY `position` (`province`,`city`,`county`,`subject`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='网上评课记录表';

-- ----------------------------
-- Table structure for `ts_pingke_attach`
-- ----------------------------
DROP TABLE IF EXISTS `ts_pingke_attach`;
CREATE TABLE `ts_pingke_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pingke_id` int(11) NOT NULL COMMENT '评课id',
  `post_id` int(11) NOT NULL COMMENT '评课回复id',
  `attach_id` int(11) NOT NULL COMMENT '附件id',
  PRIMARY KEY (`id`),
  KEY `pingke_id` (`pingke_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='评课回复附件表';

-- ----------------------------
-- Table structure for `ts_pingke_member`
-- ----------------------------
DROP TABLE IF EXISTS `ts_pingke_member`;
CREATE TABLE `ts_pingke_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pingke_id` int(11) NOT NULL COMMENT '评课id',
  `uid` int(11) NOT NULL COMMENT '成员uid',
  `discuss_count` int(10) NOT NULL DEFAULT '0' COMMENT '回复次数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pingke_id` (`pingke_id`,`uid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='评课参与者表';


-- ----------------------------
-- Table structure for `ts_pingke_post`
-- ----------------------------
DROP TABLE IF EXISTS `ts_pingke_post`;
CREATE TABLE `ts_pingke_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pingke_id` int(11) DEFAULT NULL COMMENT '评课id',
  `uid` int(11) DEFAULT NULL COMMENT '评课用户uid',
  `content` text COMMENT '回复内容',
  `ctime` int(11) DEFAULT NULL COMMENT '回复时间',
  PRIMARY KEY (`id`),
  KEY `pingke_id` (`pingke_id`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='参与评课的回复记录表';


-- ----------------------------
-- ADD USER MESSAGE
-- ----------------------------
INSERT INTO `ts_notify_node` (`node`, `nodeinfo`, `appname`, `content_key`, `title_key`, `send_email`, `send_message`, `type`) VALUES ('pingke', '网上评课', 'pingke', 'PINGKE_CONTENT', 'PINGKE_TITLE', '0', '1', '1');
INSERT INTO `ts_lang` (`key`, `appname`, `filetype`, `zh-cn`, `en`, `zh-tw`) VALUES ('PINGKE_TITLE', 'PINGKE', '0', '{user}邀请你参加【{content}】网上评课', 'pingke', ' ');
INSERT INTO `ts_lang` (`key`, `appname`, `filetype`, `zh-cn`, `en`, `zh-tw`) VALUES ('PINGKE_CONTENT', 'PINGKE', '0', '{user}邀请你参加【{content}】网上评课。<a href=\"{sourceurl}\" target=\'_blank\'>去看看>></a>', 'pingke', ' ');

-- ----------------------------
-- DELETE USER MESSAGE
-- ----------------------------
INSERT INTO `ts_notify_node` (`node`, `nodeinfo`, `appname`, `content_key`, `title_key`, `send_email`, `send_message`, `type`) VALUES ('pingke_del', '网上评课', 'pingke', 'PINGKE_DEL_CONTENT', 'PINGKE_DEL_TITLE', '0', '1', '1');
INSERT INTO `ts_lang` (`key`, `appname`, `filetype`, `zh-cn`, `en`, `zh-tw`) VALUES ('PINGKE_DEL_CONTENT', 'pingke', '0', '{user}在网上评课【{content}】删除了你。', 'pingke', ' ');
INSERT INTO `ts_lang` (`key`, `appname`, `filetype`, `zh-cn`, `en`, `zh-tw`) VALUES ('PINGKE_DEL_TITLE', 'pingke', '0', '{user}在网上评课【{content}】删除了你。<a href=\"{sourceurl}\" target=\'_blank\'>去看看>></a>', 'pingke', ' ');


