
-- -----------------------------------------------------------------------------
-- Таблица счетов
-- amount - сумма счета (виртуальны деньги, т.е. реальные пересчитанные в основную валюту)
-- money - сумма пополнения счета (реальные деньги)

DROP TABLE IF EXISTS `bff_bills`;
CREATE TABLE `bff_bills` (
  `id` int(11) NOT NULL auto_increment,    
  `user_id` int(11) unsigned NOT NULL default 0,
  `user_balance` float NOT NULL default 0,
  `type` tinyint(1) unsigned NOT NULL default 1,
  `psystem` tinyint(1) unsigned NOT NULL default 0,
  `amount` float NOT NULL default 0,
  `money` float NOT NULL default 0,
  `currency` char(3) NOT NULL default 'rur',
  `created` timestamp NOT NULL default '0000-00-00 00:00:00',
  `payed` timestamp NOT NULL default '0000-00-00 00:00:00',
  `status` tinyint(1) unsigned NOT NULL default 0,
  `description` varchar(200) NOT NULL default '',
  `details` varchar(200) NOT NULL default '', 
  `item_id` int(10) unsigned NOT NULL default 0, 
  `svc_id` tinyint(1) unsigned NOT NULL default 0,
  `ip` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

