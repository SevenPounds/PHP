SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `ts_notice`;
CREATE TABLE `ts_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `ctime` int(11) NOT NULL,
  `type` tinyint(1) NOT NULL,
  `viewcount` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `uname` varchar(50) NOT NULL,
  `isDeleted` tinyint(1) NOT NULL,
  `cid` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `ts_notice_attach`;
CREATE TABLE `ts_notice_attach` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `noticeId` int(11) NOT NULL,
  `attachId` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;
