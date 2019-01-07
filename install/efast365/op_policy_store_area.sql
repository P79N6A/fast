DROP TABLE IF EXISTS `op_policy_store_area`;
CREATE TABLE `op_policy_store_area` (
  `policy_store_area_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL COMMENT '策略ID',
  `area_id` bigint(20) unsigned NOT NULL COMMENT '地区ID',
  PRIMARY KEY (`policy_store_area_id`),
  UNIQUE KEY `_key` (`store_code`,`area_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=716 DEFAULT CHARSET=utf8 COMMENT='仓库策略-区域';