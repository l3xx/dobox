
DROP TABLE IF EXISTS "bff_enotify_subscribe";
CREATE TABLE "bff_enotify_subscribe" (
  `id` int(11) unsigned NOT NULL auto_increment, 
  `user_id` int(11) unsigned NOT NULL,
  `created` integer default 0, 
  PRIMARY KEY (`id`),
  KEY `created` (`created`) 
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;