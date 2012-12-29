
DROP TABLE IF EXISTS `bff_bbs_categories`;
CREATE TABLE `bff_bbs_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL default '0',     
  `numleft` int(10) unsigned default NULL,
  `numright` int(10) unsigned default NULL,
  `numlevel` int(10) unsigned default NULL,
  `title` varchar(100) NOT NULL default '',  
  `keyword` varchar(100) NOT NULL default '',
  `textshort` text NOT NULL default '',
  `textfull` text NOT NULL default '',  
  `enabled` tinyint(1) unsigned NOT NULL default 1, 
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `mkeywords` text NOT NULL,
  `mdescription` text NOT NULL,    
  `regions` tinyint(1) unsigned NOT NULL default 0,
  `prices` tinyint(1) unsigned NOT NULL default 0, 
  `prices_sett` text NOT NULL default '',
  `items` mediumint(8) unsigned NOT NULL DEFAULT 0, 
  PRIMARY KEY  (`id`),
  KEY `keyword` (`keyword`),
  UNIQUE KEY `id`(`id`,`numleft`,`numright`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
-- ------------------------------------------------------------------------------------
-- таблица типов категорий объектов
DROP TABLE IF EXISTS `bff_bbs_categories_types`;
CREATE TABLE `bff_bbs_categories_types` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `cat_id` int(10) unsigned NOT NULL default '0',     
  `title` varchar(100) NOT NULL default '',  
  `enabled` tinyint(1) unsigned NOT NULL default 1, 
  `items` mediumint(8) unsigned NOT NULL DEFAULT 0, 
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0, 
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица динамических свойств всех категорий объектов
DROP TABLE IF EXISTS `bff_bbs_categories_dynprops`;
CREATE TABLE `bff_bbs_categories_dynprops` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `cat_id` smallint(5) unsigned NOT NULL default '0',
  `title` varchar(100) NOT NULL default '',
  `type` tinyint(1) NOT NULL,
  `default_value` text NOT NULL,
  `req` tinyint(1) unsigned NOT NULL default '0',
  `is_search` tinyint(1) NOT NULL default '0',
  `is_cache` tinyint(1) unsigned NOT NULL default '0',
  `extra` text NOT NULL,
  `parent` tinyint(1) unsigned NOT NULL default '0',
  `parent_id` int(10) unsigned NOT NULL default '0',
  `parent_value` smallint(5) unsigned NOT NULL default '0',
  `enabled` enum('Y','N') NOT NULL default 'Y',
  `data_field` tinyint(1) unsigned NOT NULL default '0',
  `num` tinyint(1) unsigned NOT NULL default '0',
  `txt` tinyint(1) unsigned NOT NULL default '0',
  `in_table` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица для dynprop-multiple
DROP TABLE IF EXISTS `bff_bbs_categories_dynprops_multi`;
CREATE TABLE IF NOT EXISTS `bff_bbs_categories_dynprops_multi` (
  `dynprop_id` int(10) unsigned NOT NULL DEFAULT 0,     
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` int NOT NULL,
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ------------------------------------------------------------------------------------
-- таблица связывающая динамические свойства с категориями
-- data_field - bff_bbs_items::f{data_field}    
DROP TABLE IF EXISTS `bff_bbs_categories_in_dynprops`;
CREATE TABLE IF NOT EXISTS `bff_bbs_categories_in_dynprops` (
  `dynprop_id` int(10) unsigned NOT NULL DEFAULT 0,
  `cat_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `data_field` tinyint(1) NOT NULL DEFAULT '0',
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0,
  KEY `dynprop_id` (`dynprop_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- таблица объявлений
DROP TABLE IF EXISTS `bff_bbs_items`;
CREATE TABLE IF NOT EXISTS `bff_bbs_items` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `user_id` int(11) unsigned NOT NULL default 0,
  `uid` varchar(41) NOT NULL default '',
  `cat1_id` int(10) unsigned NOT NULL default 0,
  `cat2_id` int(10) unsigned NOT NULL default 0,
  `cat_id` int(10) unsigned NOT NULL default 0,
  `cat_type` smallint(5) unsigned NOT NULL default 0,
  `country_id` int(10) unsigned NOT NULL default 0,
  `region_id` int(10) unsigned NOT NULL default 0,
  `city_id` int(10) unsigned NOT NULL default 0,
  `status` tinyint(1) unsigned NOT NULL default 1,
  `svc` tinyint(1) unsigned NOT NULL default 0,
  `press` tinyint(1) unsigned NOT NULL default 0,
  `pass` blob NOT NULL,
  `keyword` varchar(100) NOT NULL default '',
  `descr` text NOT NULL,
  `descr_regions` text NOT NULL,
  `info` text NOT NULL,
  `img` text NOT NULL,
  `imgcnt` tinyint(1) unsigned NOT NULL default '0',
  `imgfav` varchar(25) NOT NULL,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  `mkeywords` text NOT NULL,
  `mdescription` text NOT NULL,
  `price` decimal(9,0) unsigned NOT NULL default '0',
  `price_bart` tinyint(1) unsigned NOT NULL default '0',
  `price_torg` tinyint(1) unsigned NOT NULL default '0',
  `contacts_name` varchar(150) NOT NULL,
  `contacts_email` varchar(150) NOT NULL,
  `contacts_phone` varchar(150) NOT NULL,
  `contacts_skype` varchar(150) NOT NULL,
  `contacts_site` text NOT NULL,
  `video` text NOT NULL,
  `publicated` timestamp NOT NULL default '0000-00-00 00:00:00',
  `publicated_to` timestamp NOT NULL default '0000-00-00 00:00:00',
  `publicated_order` timestamp NOT NULL default '0000-00-00 00:00:00',
  `premium_to` timestamp NOT NULL default '0000-00-00 00:00:00',
  `premium_order` timestamp NOT NULL default '0000-00-00 00:00:00',
  `marked_to` timestamp NOT NULL default '0000-00-00 00:00:00',
  `moderated` tinyint(1) unsigned NOT NULL default 0,
  `blocked_num` tinyint(1) unsigned NOT NULL default 0,
  `blocked_reason` text NOT NULL,
  `status_prev` tinyint(1) unsigned NOT NULL default 1,
  `views_total` mediumint(9) NOT NULL default '0',
  `f1` double NOT NULL,
  `f2` double NOT NULL,
  `f3` double NOT NULL,
  `f4` double NOT NULL,
  `f5` double NOT NULL,
  `f6` double NOT NULL,
  `f7` double NOT NULL,
  `f8` double NOT NULL,
  `f9` double NOT NULL,
  `f10` double NOT NULL,
  `f11` text NOT NULL,
  `f12` text NOT NULL,
  `f13` text NOT NULL,
  `f14` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `keyword` (`keyword`),
  KEY `uid` (`uid`),
  FULLTEXT `search_index` (descr),
  INDEX contacts_phone (contacts_phone)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица комментариев объявления    
DROP TABLE IF EXISTS `bff_bbs_items_comments`;
CREATE TABLE IF NOT EXISTS `bff_bbs_items_comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `comment` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица очереди email уведомлений о новых комментариях

DROP TABLE IF EXISTS `bff_bbs_items_comments_enotify`;
CREATE TABLE `bff_bbs_items_comments_enotify` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL,
  `comment_id` int(10) unsigned NOT NULL,                  
  `email` varchar(150) NOT NULL,
  `comment` text NOT NULL, 
  `created` int(11) unsigned NOT NULL default 0,
  `sended` tinyint(1) unsigned NOT NULL default 0,
  PRIMARY KEY  (`id`),
  INDEX  (`created`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица просмотров объявления
DROP TABLE IF EXISTS `bff_bbs_items_views`;
CREATE TABLE IF NOT EXISTS `bff_bbs_items_views` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `views` int(10) unsigned NOT NULL,
  `views_date` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`,`views_date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица избранных объектов
DROP TABLE IF EXISTS `bff_bbs_items_fav`;
CREATE TABLE IF NOT EXISTS `bff_bbs_items_fav` (
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,  
  UNIQUE KEY `item_id` (`item_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------------------
-- таблица жалоб
DROP TABLE IF EXISTS `bff_bbs_items_claims`;
CREATE TABLE `bff_bbs_items_claims` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `comment` text NOT NULL,
  `reasons` int(11) NOT NULL default '0',
  `viewed` tinyint(1) unsigned NOT NULL default '0',
  `ip` varchar(50) NOT NULL,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;
