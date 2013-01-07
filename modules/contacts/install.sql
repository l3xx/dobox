
DROP TABLE IF EXISTS `bff_contacts`;
CREATE TABLE `bff_contacts` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ctype`  tinyint(1) unsigned NOT NULL default 0,
  `user_id` int(11) unsigned NOT NULL default 0,
  `email` varchar(70) NOT NULL default '',
  `phone`  varchar(70) NOT NULL default '',
  `message` text NOT NULL,   
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',       
  `viewed`  tinyint(1) unsigned NOT NULL default 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
