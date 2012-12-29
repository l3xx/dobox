
DROP TABLE IF EXISTS `bff_sitemap`;
CREATE TABLE `bff_sitemap` (
  `node_id` int(10) unsigned NOT NULL default 0,
  `keyword` varchar(15) NOT NULL,
  `menu_title` varchar(150) NOT NULL default '',
  `menu_link` text NOT NULL default '',
  `menu_target` enum('_self','_blank') NOT NULL default '_self',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `mkeywords` text NOT NULL,
  `mdescription` text NOT NULL,
  PRIMARY KEY  (`node_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ;

DROP TABLE IF EXISTS `bff_sitemap_tree`;
CREATE TABLE `bff_sitemap_tree` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `pid` int(10) unsigned NOT NULL default '0',
  `numleft` int(11) default NULL,
  `numright` int(11) default NULL,
  `numlevel` int(11) default NULL,
  `enabled` tinyint(1) unsigned NOT NULL default 1,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`,`numleft`,`numright`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
