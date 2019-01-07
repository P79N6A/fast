
DROP TABLE IF EXISTS `op_gift_strategy_customer`;
CREATE TABLE `op_gift_strategy_customer` (
  `op_gift_strategy_customer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `buyer_name` varchar(128) NOT NULL DEFAULT '' COMMENT '买家名称',
  `tel` varchar(30) NOT NULL DEFAULT '0' COMMENT '手机号码',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `op_gift_strategy_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '明细ID',
  PRIMARY KEY (`op_gift_strategy_customer_id`),
  UNIQUE KEY `_index_key` (`op_gift_strategy_detail_id`,`buyer_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='赠品策略固定买家';
