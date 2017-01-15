/**
 * DbCache的数据库结构
 */

drop table if exists `cache`;

CREATE TABLE `cache` (
	`key` VARCHAR(128) NOT NULL,
	`expire` INT(10) UNSIGNED NULL DEFAULT NULL,
	`data` BLOB NULL,
	PRIMARY KEY (`key`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB
;
