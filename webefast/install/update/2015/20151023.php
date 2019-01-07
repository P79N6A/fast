<?php
$u = array();
$u['FSF-1721'] = array(
	"INSERT INTO `sys_action` VALUES ('9030500','9030000','url','零售结算交易核销查询','acc/sell_settlement/do_list','4','1','0','1','0');",
	"UPDATE sys_action SET action_name='零售结算核销查询' WHERE action_name='零售结算交易核销查询';",
	"UPDATE sys_action SET action_name='零售结算核销统计' WHERE action_name='零售结算交易核销统计';",
	"ALTER TABLE api_taobao_alipay ADD COLUMN `is_refresh` tinyint(2) NOT NULL DEFAULT '0' COMMENT '维护零售结算汇总的各类金额数据'; ",
	"ALTER TABLE api_taobao_alipay ADD KEY `is_refresh` (`is_refresh`); ",
	"ALTER TABLE oms_sell_settlement ADD COLUMN `ali_trade_je` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '支付宝交易收款';",
);
$u['FSF-1726'] = array(
	"CREATE TABLE `op_gift_strategy_customer` (
  `op_gift_strategy_customer_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `buyer_name` varchar(128) NOT NULL DEFAULT '' COMMENT '买家名称',
  `tel` varchar(30) NOT NULL DEFAULT '0' COMMENT '手机号码',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`op_gift_strategy_customer_id`),
  UNIQUE KEY `_index_key` (`strategy_code`,`buyer_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='赠品策略固定买家';
",
            "ALTER TABLE `op_gift_strategy_detail`
ADD COLUMN `is_fixed_customer`  tinyint(3) NOT NULL DEFAULT 0 COMMENT '是否固定会员送' AFTER `is_mutex`;",

);
$u['FSF-1739'] = array(
		"ALTER TABLE oms_sell_settlement ADD COLUMN `adjust_money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '调整金额';",
		"ALTER TABLE oms_sell_settlement_record ADD COLUMN `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '调整备注';",
);
$u['FSF-1742'] = array(
		"UPDATE sys_schedule SET loop_time=60 where code='logistics_upload_cmd';",
);
$u['FSF-1743'] = array(
	"UPDATE base_question_label SET is_active = 1",
);
$u['FSF-1725'] = array(
		"INSERT INTO `sys_action` VALUES ('4010500', '4010000', 'url', '交易监控', 'api/sell_record_monitor/do_list', '5', '1', '0', '1','0');",
		"ALTER TABLE api_taobao_trade ADD  KEY `shop_code_modified` (`shop_code`,`modified`)",
		"ALTER TABLE api_taobao_trade ADD  KEY `modified` (`modified`)",
		"ALTER TABLE api_taobao_trade ADD  KEY `shop_code_created` (`shop_code`,`created`)",
		"ALTER TABLE api_taobao_trade ADD  KEY `created` (`created`)",
		"ALTER TABLE api_order ADD  KEY `shop_code_order_last_update_time` (`shop_code`,`order_last_update_time`)",
		"ALTER TABLE api_order ADD  KEY `order_last_update_time` (`order_last_update_time`)",
		"ALTER TABLE api_order ADD  KEY `shop_code_order_first_insert_time` (`shop_code`,`order_first_insert_time`)",
		"ALTER TABLE api_order ADD  KEY `created` (`order_first_insert_time`)",
		"CREATE TABLE `api_order_monitor` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(100) NOT NULL,
		  `monitor_date` date NOT NULL,
		  `insert_time` datetime NOT NULL COMMENT '数据变更时间',
		  `base_order_total` int(11) NOT NULL COMMENT '系统订单数',
		  `taobao_order_total` int(11) DEFAULT NULL COMMENT '平台订单数',
		  `interval_time` char(255) DEFAULT '60' COMMENT '监控时间段',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `shop_code_monitor_time` (`shop_code`,`monitor_date`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		"CREATE TABLE `api_order_monitor_section` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
		  `monitor_date` date NOT NULL COMMENT '监控的日期',
		  `monitor_start_time` datetime NOT NULL COMMENT '监控的时间段-开始时间',
		  `monitor_end_time` datetime DEFAULT NULL COMMENT '监控的时间段-结束时间',
		  `base_order_total` int(11) NOT NULL COMMENT '系统订单数',
		  `taobao_order_total` int(11) NOT NULL COMMENT '平台订单数',
		  `insert_time` datetime DEFAULT NULL COMMENT '插入时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `shop_code_monitor_start_end_time` (`monitor_start_time`,`monitor_end_time`,`shop_code`),
		  KEY `monitor_date` (`monitor_date`),
		  KEY `shop_code_monitor_code` (`shop_code`,`monitor_date`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);
$u['FSF-1764'] = array(
		"alter table wbm_store_out_record add column `name` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人';",
		"alter table wbm_store_out_record add column `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '电话';",
		"alter table wbm_store_out_record add column `express` varchar(20) NOT NULL DEFAULT '' COMMENT '快递单号';",
		"alter table wbm_store_out_record add column `express_money` decimal(20,2) DEFAULT '0.00' COMMENT '运费';",
		"alter table wbm_store_out_record add column `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE';",
		"alter table b2b_box_record add column `is_check_and_accept` tinyint(4) NOT NULL DEFAULT '0';",
		"DROP TABLE b2b_box_record_datail",
		"CREATE TABLE `b2b_box_record_detail` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `record_code` varchar(20) DEFAULT '' COMMENT '装箱单编号',
		  `task_code` varchar(20) DEFAULT '' COMMENT '装箱任务编号',
		  `goods_code` varchar(20) DEFAULT '' COMMENT '商品编码',
		  `spec1_code` varchar(20) DEFAULT '' COMMENT '规格编码',
		  `spec2_code` varchar(20) DEFAULT '' COMMENT '规格2编码',
		  `sku` varchar(30) DEFAULT '' COMMENT 'SKU编码',
		  `lof_no` varchar(30) DEFAULT '' COMMENT '批次号',
		  `production_date` date DEFAULT NULL COMMENT '生产日期',
		  `num` int(11) DEFAULT '0' COMMENT '数量',
		  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
		  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `_idxu` (`record_code`,`sku`,`lof_no`,`production_date`) USING BTREE,
		  KEY `lastchanged` (`lastchanged`),
		  KEY `_idx_task_code` (`task_code`,`sku`,`lof_no`,`production_date`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='装箱商品明细';",
		"alter table api_weipinhuijit_store_out_record add column `express` varchar(20) NOT NULL DEFAULT '' COMMENT '快递单号';",
		"alter table api_weipinhuijit_delivery add column `express` varchar(20) NOT NULL DEFAULT '' COMMENT '快递单号';",
		
		);

