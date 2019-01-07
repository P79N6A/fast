DROP TABLE IF EXISTS `oms_sell_record_action`;
CREATE TABLE `oms_sell_record_action` (
  `sell_record_action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '0' COMMENT '关联oms_sell_record主键',
  `user_code` varchar(30) NOT NULL COMMENT 'sys_user员工代码',
  `user_name` varchar(30) NOT NULL COMMENT '员工名称',
  `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态,同订单表',
  `shipping_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '配送状态,同订单表',
  `pay_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '付款状态,同订单表',
  `action_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
  `action_note` mediumtext NOT NULL COMMENT '操作描述',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`sell_record_action_id`),
  KEY `sell_record_id` (`sell_record_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='订单操作日志';