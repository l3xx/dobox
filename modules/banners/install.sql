
DROP TABLE IF EXISTS `bff_banners`; 
CREATE TABLE IF NOT EXISTS `bff_banners` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `banner_type` tinyint(1) NOT NULL DEFAULT 3,
  `cat` text NOT NULL DEFAULT '',
  `banner` text NOT NULL DEFAULT '',
  `flash` text NOT NULL DEFAULT '',
  `clickurl` char(150) NOT NULL,
  `position` char(30) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `show_start` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `show_finish` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `show_limit` int(11) NOT NULL DEFAULT '0',
  `showurl` varchar(255) NOT NULL,
  `showurl_recursive` tinyint(2) NOT NULL DEFAULT '0',
  `title` varchar(150) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `alt` varchar(150) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `bff_banners_stat`;      
CREATE TABLE IF NOT EXISTS `bff_banners_stat` (
  `id` int(11) NOT NULL,
  `clicks` int(11) NOT NULL,
  `shows` int(11) NOT NULL,
  `period` date NOT NULL,
  PRIMARY KEY (`id`,`period`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

