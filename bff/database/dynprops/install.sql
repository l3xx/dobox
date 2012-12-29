/*
 Компонент dbDynprops
 взаимодействует со следующими таблицами:
*/

-- ------------------------------------------------------------------------
-- 1) таблица свойств:
-- owner_id - id записи(владельца данного св-ва)
-- data_field - номер столбца для хранения значения свойства
-- title - название свойства
-- type - тип 
-- parent - является ли данное свойство parent'ом (возможно ли прикрепление)
-- parent_id - id parent-свойства
-- parent_value - value parent-свойства
-- default_value - значение по умолчанию
-- req - обязательное для ввода
-- is_search - используется ли св-во при поиске
-- is_cache - кешируется ли св-во
-- extra: - дополнительные параметры св-ва
--    child_name - название прикреляемого свойства
--    dia - использовать диапазон значений при поиске, если "да", тогда необходимо указать start/end (для типа "число")
--    start - минимальное значение для типа "диапазон", начальное значение диапазона при поиске типа "число"
--    end - максимальное значение для типа "диапазон", конечное значение диапазона при поиске типа "число"
--    step - шаг (для типа "диапазон")   
-- enabled -включено ли св-во

DROP TABLE IF EXISTS `{prefix}_dynprops`;
CREATE TABLE IF NOT EXISTS `{prefix}_dynprops` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` smallint(5) unsigned NOT NULL DEFAULT 0, -- при наличии таблицы #3 это поле хранит id владельца (создавшего свойство)
  `title` varchar(100) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL,
  `parent` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `default_value` text NOT NULL, 
  `req` tinyint(1) NOT NULL DEFAULT 0, 
  `is_search` tinyint(1) NOT NULL DEFAULT 0,
  `is_cache` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `extra` text NOT NULL,
  `parent` tinyint(1) NOT NULL DEFAULT 0, -- является ли данное parent-свойством
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0, -- является ли данное свойство прикрепленным (id parent-свойства)
  `parent_value` smallint(5) unsigned NOT NULL DEFAULT 0, -- value parent-свойства
  `enabled` enum('Y','N') NOT NULL DEFAULT 'Y',
  `data_field` tinyint(1) NOT NULL DEFAULT 0, -- в случае если, нет связующей таблицы #3
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0, -- в случае если, нет связующей таблицы #3
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- ------------------------------------------------------------------------
-- 2) таблица значений, для свойств с множественным выбором:
-- dynprop_id - id свойства
-- name - название значения
-- value - значение
-- num - порядковый номер

DROP TABLE IF EXISTS `{prefix}_dynprops_multi`;
CREATE TABLE IF NOT EXISTS `{prefix}_dynprops_multi` (
  `dynprop_id` int(10) unsigned NOT NULL DEFAULT 0,     
  `name` varchar(100) NOT NULL DEFAULT '',
  `value` int NOT NULL,
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0,
  KEY `dynprop_id` (`dynprop_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ------------------------------------------------------------------------
-- 3) таблица связи записи(владельца) со свойствами
-- dynprop_id - id свойства                           
-- owner_id - id записи(владельца данного св-ва)      
-- data_field - номер столбца для хранения значения свойства
-- num - порядковый номер                           

DROP TABLE IF EXISTS `{prefix}_in_dynprops`;
CREATE TABLE IF NOT EXISTS `{prefix}_in_dynprops` (
  `dynprop_id` int(10) unsigned NOT NULL DEFAULT 0,
  `owner_id` smallint(5) unsigned NOT NULL DEFAULT 0,
  `data_field` tinyint(1) NOT NULL DEFAULT 0,
  `num` tinyint(1) unsigned NOT NULL DEFAULT 0,
  KEY `owner_id` (`owner_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ------------------------------------------------------------------------
-- 4) таблица владельцев
-- id - id владельца
-- pid - parent владельца
-- title - название владельца

DROP TABLE IF EXISTS `{prefix}`;
CREATE TABLE IF NOT EXISTS `{prefix}` (              
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `pid` smallint(5) unsigned NOT NULL default 0, 
  `title` varchar(100) NOT NULL default '', 
  `numleft`  smallint(5) unsigned DEFAULT NULL,    -- tree
  `numright` smallint(5) unsigned DEFAULT NULL,    -- tree
  `numlevel` smallint(5) unsigned DEFAULT NULL,    -- tree
  `enabled`  tinyint(1) unsigned NOT NULL DEFAULT 0,    
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`,`numleft`,`numright`), -- tree
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;