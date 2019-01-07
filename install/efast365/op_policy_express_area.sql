DROP TABLE IF EXISTS `op_policy_express_area`;
CREATE TABLE `op_policy_express_area` (
  `policy_express_area_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL COMMENT '策略ID',
  `area_id` bigint(20) unsigned NOT NULL COMMENT '地区ID',
  PRIMARY KEY (`policy_express_area_id`),
  UNIQUE KEY `_key` (`area_id`) USING BTREE,
  KEY `_index1` (`pid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略-区域';