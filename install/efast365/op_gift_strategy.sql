DROP TABLE IF EXISTS `op_gift_strategy`;
CREATE TABLE `op_gift_strategy` (
  `op_gift_strategy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `strategy_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `start_time` int(11) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end_time` int(11) NOT NULL DEFAULT '0' COMMENT '结束数据',
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `is_once_only` tinyint(3) NOT NULL DEFAULT '0' COMMENT '一个会员赠送1次,0否，1是',
  `is_continue_no_inv` tinyint(3) NOT NULL DEFAULT '0' COMMENT '库存不足是否继续送,0否，1是',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `status` tinyint(3) DEFAULT '0' COMMENT '0：停用 1：启用',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_check` int(4) DEFAULT '0' COMMENT '0未审核 1审核',
  `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
  `time_type` tinyint(3) DEFAULT '0' COMMENT '时间维度 0:付款时间 1：下单时间',
  `combine_upshift` tinyint(3) DEFAULT '0' COMMENT '合并订单赠品升档 0:否 1：是',
  PRIMARY KEY (`op_gift_strategy_id`),
  UNIQUE KEY `strategy_code` (`strategy_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='赠品赠品策略';
