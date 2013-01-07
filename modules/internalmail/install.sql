-- --------------------------------------------------------
-- Структура таблицы `bff_internalmail`

DROP TABLE IF EXISTS `bff_internalmail`;
CREATE TABLE `bff_internalmail` (
  `id` int(11) unsigned NOT NULL auto_increment,  
  `author` int(11) unsigned NOT NULL,
  `recipient` int(11) unsigned NOT NULL,
  `message` text NOT NULL,
  `attach`  text DEFAULT NULL,
  `status` tinyint(1) NOT NULL default '0',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `readed` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
-- Структура таблицы `bff_internalmail_folders`

DROP TABLE IF EXISTS `bff_internalmail_folders`;
CREATE TABLE IF NOT EXISTS `bff_internalmail_folders` (
  `id` mediumint(8) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------
-- Структура таблицы `bff_internalmail_folders_users`
        
DROP TABLE IF EXISTS `bff_internalmail_folders_users`;
CREATE TABLE IF NOT EXISTS `bff_internalmail_folders_users` (
  `folder_id` mediumint(8) unsigned NOT NULL DEFAULT '1',
  `user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `interlocutor_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`interlocutor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

