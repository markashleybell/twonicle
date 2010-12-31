DROP TABLE IF EXISTS `twarchive`.`tweets`;
CREATE TABLE  `twarchive`.`tweets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userid` int(10) unsigned NOT NULL,
  `tweetid` bigint(20) unsigned NOT NULL,
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
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `twarchive`.`tweetusers`;
CREATE TABLE  `twarchive`.`tweetusers` (
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
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;