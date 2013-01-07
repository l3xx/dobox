-- --------------------------------------------------------
--
-- Структура таблицы `bff_users_banlist`
--

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
