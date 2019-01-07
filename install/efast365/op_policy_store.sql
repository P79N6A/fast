DROP TABLE IF EXISTS `op_policy_store`;
CREATE TABLE `op_policy_store` (
  `policy_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL,
  `area_desc` text,
  `sort` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '优先级：数据越大优先级越高',
  PRIMARY KEY (`policy_store_id`),
  UNIQUE KEY `_key` (`store_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略';