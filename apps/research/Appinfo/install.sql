SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_photo`
-- ----------------------------
DROP TABLE IF EXISTS `ts_research`;
CREATE TABLE `ts_research` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '课题id',
  `uid` int(11) NOT NULL COMMENT '课题创建人id',
  `title` varchar(255) DEFAULT NULL COMMENT '课题名称',
  `description` text COMMENT '课题简介',
  `type` varchar(50) DEFAULT NULL COMMENT '类型      research:课题研究  onlineEval:网上评课',
  `createtime` int(11) DEFAULT NULL COMMENT '创建时间',
  `modifiedtime` int(11) DEFAULT NULL COMMENT '修改时间',
  `closedtime` int(11) DEFAULT NULL COMMENT '关闭时间',
  `status` int(10) DEFAULT NULL COMMENT '课题进行状态 1：进行中  0：已完成',
  `public_status` int(10) DEFAULT NULL COMMENT '课题成功公开状态 1：公开  0：不公开',
  `discuss_count` int(11) DEFAULT NULL COMMENT '研讨次数',
  `member_count` int(11) DEFAULT NULL COMMENT '参加成员总数',
  `summary_attachid` int(11) DEFAULT NULL COMMENT '课题总结文件附件id',
  `summary_name` varchar(255) DEFAULT NULL COMMENT '课题总结文件名称',
  `summary_path` varchar(255) DEFAULT NULL COMMENT '课题总结文件路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_research_attach`;
CREATE TABLE `ts_research_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增加',
  `app_type` varchar(50) DEFAULT NULL COMMENT '应用英文名称',
  `app_id` int(11) DEFAULT NULL COMMENT '应用id',
  `attach_id` int(11) DEFAULT NULL COMMENT '附件id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_research_post`;
CREATE TABLE `ts_research_post` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增长',
  `research_id` int(11) DEFAULT NULL COMMENT '课题研究id',
  `post_userid` int(11) DEFAULT NULL COMMENT '发表帖子的用户id',
  `content` text COMMENT '帖子内容',
  `createtime` int(11) DEFAULT NULL COMMENT '发表帖子时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `ts_research_user`;
CREATE TABLE `ts_research_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自动增长',
  `research_id` int(11) NOT NULL COMMENT '课题研究id',
  `member_id` int(11) NOT NULL COMMENT '加入成员id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=89 DEFAULT CHARSET=utf8;