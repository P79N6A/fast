DROP TABLE IF EXISTS `oms_sell_return_action`;
CREATE TABLE `oms_sell_return_action` (
  `sell_return_action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_return_code` varchar(30) NOT NULL COMMENT '退单号',
  `user_code` varchar(30) NOT NULL COMMENT '客服代码',
  `user_name` varchar(30) NOT NULL COMMENT '客服名称',
  `return_order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '退单状态,同退单表',
  `return_shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配送状态,同退单表',
  `finance_check_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '财审状态',
  `action_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_note` varchar(255) NOT NULL COMMENT '操作描述',
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日志时间',
  PRIMARY KEY (`sell_return_action_id`),
  KEY `sell_return_code` (`sell_return_code`),
  KEY `user_code` (`user_code`),
  KEY `action_name` (`action_name`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='退单操作日志';