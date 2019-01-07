
DROP TABLE IF EXISTS `base_area`;
CREATE TABLE `base_area` (
  `id` BIGINT(20) NOT NULL,
  `type` INT(10) NOT NULL COMMENT '1:country/国家;2:province/省/自治区/直辖市;3:city/地区(省下面的地级市);4:district/县/市(县级市)/区;abroad:海外',
  `name` VARCHAR(100) NOT NULL DEFAULT '' COMMENT '地域名称',
  `parent_id` VARCHAR(150) NOT NULL DEFAULT '' COMMENT '父节点区域标识',
  `zip` VARCHAR(36) DEFAULT '' COMMENT '邮编',
  `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `url` VARCHAR(100) DEFAULT '',
  `catch` VARCHAR(100) DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_pn` (`parent_id`,`name`) USING BTREE,
  KEY `idx_type` (`type`),
  KEY `idx_parent_id` (`parent_id`),
  KEY `idx_name` (`name`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='地址区域表';