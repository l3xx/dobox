
-- таблица вопросов
DROP TABLE IF EXISTS `bff_faq`; 
CREATE TABLE `bff_faq` (
  `id` int(11) unsigned NOT NULL auto_increment,  
  `user_id` int(11) unsigned NOT NULL, 
  `category_id` smallint(5) unsigned NOT NULL,
  `question` varchar(200) NOT NULL default '',
  `answer` text NOT NULL default '',
  `pos_add` tinyint(1) unsigned NOT NULL default 0,
  `pos_up`  tinyint(1) unsigned NOT NULL default 0,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00', 
  `num`  int(11) unsigned NOT NULL default 0,  
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- таблица категорий вопросов
DROP TABLE IF EXISTS `bff_faq_categories`; 
CREATE TABLE `bff_faq_categories` (
  `id`  smallint(5) unsigned NOT NULL auto_increment, 
  `title` varchar(100) NOT NULL default '',
  `num` smallint(5) unsigned NOT NULL,    
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;