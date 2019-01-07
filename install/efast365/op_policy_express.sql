DROP TABLE IF EXISTS `op_policy_express`;
CREATE TABLE `op_policy_express` (
  `policy_express_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `policy_express_name` varchar(128) NOT NULL,
  `policy_express_code` varchar(128) NOT NULL,
  `is_fee_first` tinyint(3) unsigned NOT NULL COMMENT '启用后,''最低运费判断''优先级 > ''配送方式''优先级',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0未启用 1已启用',
  `store_code` text,
  PRIMARY KEY (`policy_express_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略';
