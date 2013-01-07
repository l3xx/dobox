-- --------------------------------------------------------
-- Структура таблицы `bff_users`


DROP TABLE IF EXISTS `bff_users`;
CREATE TABLE `bff_users` (
  `user_id` int(11) unsigned NOT NULL auto_increment,
  `admin` tinyint(1) unsigned NOT NULL default 0,
  `cat` text NOT NULL DEFAULT '',
  `member` tinyint(1) unsigned NOT NULL default 1,
  `login` varchar(40) NOT NULL default '',
  `password` varchar(30) NOT NULL default '',
  `email` varchar(40) NOT NULL,
  `email_hash` bigint(20) NOT NULL default 0,
  `balance` float(10,2) NOT NULL default '0.00',
  `balance_spent` float(10,2) unsigned NOT NULL default '0.00',
  `city_id` smallint(5) unsigned NOT NULL default 0,
  `region_id` smallint(5) unsigned NOT NULL default 0,
  `name` varchar(70) NOT NULL,
  `surname` varchar(100) NOT NULL,
  `avatar` varchar(50) default NULL,
  `login_ts` timestamp NOT NULL default '0000-00-00 00:00:00',
  `login_last_ts` timestamp NOT NULL default '0000-00-00 00:00:00',
  `activity_ts` timestamp NOT NULL default '0000-00-00 00:00:00',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `ip_reg` int(10) unsigned NOT NULL default 0,
  `ip_login` int(10) unsigned NOT NULL default 0,
  `session_id` varchar(50) NOT NULL,
  `blocked` tinyint(1) unsigned NOT NULL default 0,
  `blocked_reason` text NOT NULL,
  `deleted` tinyint(1) unsigned NOT NULL default 0,
  `activatekey` varchar(32) NOT NULL,
  `activated` enum('0','1','2') NOT NULL default 1, 
  `subscribed` enum('0','1') NOT NULL default 0,
  `birthdate` date NOT NULL default '1901-01-01',
  `sex` enum('0','1','2') NOT NULL default 0, 
  `statusmsg` text,
  `about` text NOT NULL,
  `icq` varchar(15) NOT NULL,
  `skype` varchar(100) NOT NULL,
  `cell` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `site` varchar(150) NOT NULL,
  `im_newmsg` smallint(5) unsigned NOT NULL default 0,
  `im_noreply` tinyint(1) unsigned NOT NULL default 0,
  `vk_id` int(11) unsigned NOT NULL default 0, 
  `vk_data` text,
  `social` varchar(10) NOT NULL,  
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `vk_id` (`vk_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

-- --------------------------------------------------------
-- Структура таблицы `bff_users_banlist`

DROP TABLE IF EXISTS `bff_users_banlist`;
CREATE TABLE `bff_users_banlist` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `uid` int(11) unsigned NOT NULL default '0',
  `ip` varchar(40) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `started` int(11) unsigned NOT NULL default '0',
  `finished` int(11) unsigned NOT NULL default '0',
  `exclude` tinyint(1) unsigned NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `reason` varchar(255) NOT NULL default '',
  `status` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `started` (`started`),
  KEY `uid` (`uid`,`exclude`),
  KEY `email` (`email`,`exclude`),
  KEY `ip` (`ip`,`exclude`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------
-- Структура таблицы `bff_users_groups`

DROP TABLE IF EXISTS `bff_users_groups`;
CREATE TABLE `bff_users_groups` (
  `group_id` smallint(5) unsigned NOT NULL auto_increment,
  `issystem` tinyint(1) NOT NULL default '0',
  `adminpanel` tinyint(1) NOT NULL default '0',
  `keyword` char(40) NOT NULL default '',
  `title` char(100) NOT NULL default '',
  `color` char(40) NOT NULL default '#000',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `modified` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`group_id`),
  UNIQUE KEY `ukey` (`group_id`,`keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;


INSERT INTO `bff_users_groups` (`group_id`, `issystem`, `adminpanel`, `keyword`, `title`, `color`, `created`, `modified`) VALUES
(7, 1, 1, 'x71', 'Главный администратор', '#55AF0A', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(17, 1, 1, 'c60', 'Модератор', '#D8062D', '0000-00-00 00:00:00', '2010-05-12 13:34:24'),
(22, 1, 0, 'z24', 'Пользователь', 'gray', '0000-00-00 00:00:00', '2009-04-06 01:29:00'),
(25, 1, 0, 'cc', 'Клиент', 'blue', '2010-12-13 00:23:49', '2010-12-13 00:23:59');

-- --------------------------------------------------------
-- Структура таблицы `bff_users_groups_permissions`

DROP TABLE IF EXISTS `bff_users_groups_permissions`;
CREATE TABLE `bff_users_groups_permissions` (
  `unit_id` int(11) unsigned NOT NULL default '0',
  `unit_type` char(60) NOT NULL,
  `item_id` int(11) NOT NULL default '0',
  `item_type` char(60) NOT NULL,
  `created` timestamp NOT NULL default '0000-00-00 00:00:00'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

DROP TABLE IF EXISTS `bff_user_in_group`;
CREATE TABLE `bff_user_in_group` (
  `user_id` int(11) unsigned NOT NULL default '0',
  `group_id` smallint(5) unsigned NOT NULL default '0',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO `bff_user_in_group` (`user_id`, `group_id`, `created`) VALUES
(1, 17, '2010-12-04 16:56:57'),
(2, 17, '2010-05-10 19:50:29'),
(3, 17, '2010-06-04 23:13:04'),
(1, 7, '2010-12-04 16:56:57');

