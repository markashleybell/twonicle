DROP TABLE IF EXISTS `twonicle`.`statuses`;
CREATE TABLE  `twonicle`.`statuses` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `statusid` bigint(20) unsigned NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL,
  `text` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `favorite` tinyint(4) NOT NULL DEFAULT '0',
  `extra` text NOT NULL,
  `coordinates` text,
  `geo` text,
  `place` text,
  `contributors` text,
  `pick` tinyint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM AUTO_INCREMENT=1750 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `twonicle`.`people`;
CREATE TABLE  `twonicle`.`people` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `screenname` varchar(25) NOT NULL,
  `realname` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `profileimage` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `extra` text NOT NULL,
  `enabled` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `twonicle`.`system`;
CREATE TABLE  `twonicle`.`system` (
  `k` varchar(45) NOT NULL,
  `v` varchar(45) NOT NULL,
  PRIMARY KEY (`k`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `twonicle`.`system` VALUES ('lastupdated', '1000000000');
INSERT INTO `twonicle`.`system` VALUES ('processing', '0');