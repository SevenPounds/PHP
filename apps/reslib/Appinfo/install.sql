
SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ts_resource`
-- ----------------------------
DROP TABLE IF EXISTS `ts_resource`;
CREATE TABLE `ts_resource` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rid` char(100) NOT NULL COMMENT '资源ID(GUID)',
  `title` text,
  `keywords` text,
  `uid` int(11) DEFAULT NULL COMMENT '上传者ID',
  `username` char(15) DEFAULT '匿名' COMMENT '上传者名称',
  `uploaddateline` int(11) DEFAULT NULL COMMENT '上传时间',
  `suffix` char(15) DEFAULT NULL COMMENT '资源文件后缀名(如:.doc)',
  `type1` char(10) DEFAULT NULL COMMENT '一级分类',
  `type2` char(10) DEFAULT NULL COMMENT '二级分类',
  `restype` char(10) DEFAULT '' COMMENT '资源类型\r\n0000 请选择 \r\n0100 教学设计\r\n0600 教学课件\r\n0200 课堂实录\r\n1300 难点解析\r\n0300 媒体素材\r\n0400 习题精选\r\n',
  `downloadtimes` int(11) NOT NULL DEFAULT '0',
  `praisetimes` int(11) NOT NULL DEFAULT '0',
  `negationtimes` int(11) NOT NULL DEFAULT '0',
  `score` float DEFAULT '0' COMMENT '资源评分',
  `praiserate` double DEFAULT NULL,
  `is_del` tinyint(1) NOT NULL DEFAULT '0' COMMENT '资源状态',
  `description` text,
  `grade` char(11) DEFAULT NULL,
  `subject` char(11) DEFAULT NULL,
  `province` int(11) DEFAULT NULL COMMENT '省ID',
  `city` int(11) DEFAULT NULL COMMENT '市ID',
  `county` int(11) DEFAULT NULL COMMENT '区县ID',
  `school_id` int(11) DEFAULT NULL COMMENT '学校ID',
  `audit_uid` int(11) DEFAULT NULL COMMENT '审核人uid',
  `audit_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '审核状态，0未审核，1已经审核通过，2审核未通过',
  `province_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '省级精品，默认0，精品1，非精品2',
  `city_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '市级精品，默认0，精品1，非精品2',
  `county_level` tinyint(1) NOT NULL DEFAULT '0' COMMENT '区县精品，默认0，精品1，非精品2',
  `province_auditor` int(11) DEFAULT NULL COMMENT '省级遴选人',
  `city_auditor` int(11) DEFAULT NULL COMMENT '市级遴选人',
  `county_auditor` int(11) DEFAULT NULL COMMENT '区县级遴选人',
  `size` int(11) unsigned zerofill NOT NULL COMMENT '资源大小',
  `creator` varchar(255) NOT NULL COMMENT '登陆名',
  `product_id` varchar(20) NOT NULL DEFAULT 'other' COMMENT '资源来源标识，如rrt、bbt等',
  `countyratedate` int(11) DEFAULT NULL COMMENT '区县级遴选时间',
  `cityratedate` int(11) DEFAULT NULL COMMENT '市级遴选时间',
  `provinceratedate` int(11) DEFAULT NULL COMMENT '省级遴选时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rid` (`rid`) USING BTREE,
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_resource_capacity`
-- ----------------------------
DROP TABLE IF EXISTS `ts_resource_capacity`;
CREATE TABLE `ts_resource_capacity` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `login_name` varchar(11) NOT NULL COMMENT '用户登录名',
  `used_capacity` bigint(20) NOT NULL COMMENT '用户已使用容量(单位是byte)',
  `total_capacity` bigint(20) NOT NULL COMMENT '用户的总容量,单位是byte',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_resource_operation`
-- ----------------------------
DROP TABLE IF EXISTS `ts_resource_operation`;
CREATE TABLE `ts_resource_operation` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '资源ID',
  `resource_id` int(11) NOT NULL,
  `operationtype` int(8) NOT NULL COMMENT '资源操作的类型(下载、转发、收藏)',
  `dateline` int(11) NOT NULL,
  `login_name` varchar(255) NOT NULL COMMENT '用户的login 登陆名',
  `uid` int(11) NOT NULL,
  `gid` int(11) DEFAULT NULL COMMENT '用户所在工作室的id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for `ts_resource_recommend`
-- ----------------------------
DROP TABLE IF EXISTS `ts_resource_recommend`;
CREATE TABLE `ts_resource_recommend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) NOT NULL COMMENT '引用resource表的id',
  `login_name` varchar(255) NOT NULL DEFAULT '' COMMENT '被推荐资源的用户的login',
  `uid` int(11) NOT NULL,
  `dateline` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
