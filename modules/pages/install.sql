-- --------------------------------------------------------
-- Структура таблицы `bff_pages`

DROP TABLE IF EXISTS `bff_pages`;
CREATE TABLE `bff_pages` (
  `id` int(11) unsigned NOT NULL auto_increment,      
  `title` varchar(255) NOT NULL default '',
  `filename` varchar(100) NOT NULL default '',
  `mkeywords` varchar(255) NOT NULL default '',
  `mdescription` varchar(255) NOT NULL default '',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `issystem` tinyint NOT NULL default 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;