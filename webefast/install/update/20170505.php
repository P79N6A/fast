<?php

$u['1255'] = array(
        "CREATE TABLE fx_goods_adjust_price_record
(
	`adjust_price_record_id` INT(11) unsigned AUTO_INCREMENT NOT NULL,
	`record_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '调价单编号',
	`record_status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '是否启用 0未启用 1已启用',
	`adjust_price_object` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '调价对象 1分销商 2分销商分类',
	`object_code` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '对象编号：分销商code、分销分类code',
	`settlement_price_type` TINYINT (1) NOT NULL DEFAULT 0 COMMENT '结算价格类型: 0吊牌价 1成本价 2批发价 3进货价',
	`settlement_rebate` DECIMAL(3,2) DEFAULT 1.00 COMMENT '折扣',
	`start_time` INT(11) NOT NULL DEFAULT 0 COMMENT '调整开始时间',
	`end_time` INT(11) NOT NULL DEFAULT 0 COMMENT '调整结束时间',
	`add_time` INT(11) NOT NULL DEFAULT 0 COMMENT '添加时间',
	`user_code` VARCHAR(64) DEFAULT '' COMMENT '创建人',
	`lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`adjust_price_record_id`),
	UNIQUE KEY `_key` (`record_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品调价单（分销商）';",
"CREATE TABLE fx_goods_adjust_price_detail
(
	`adjust_price_detail_id` INT(11) unsigned AUTO_INCREMENT NOT NULL,
	`record_code` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '调价单编号',
	`pid` INT(11) NOT NULL DEFAULT 0 COMMENT '调价单id',
	`sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
	`goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
	`settlement_price` DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT '结算价格',
	`settlement_money` DECIMAL(10,3) NOT NULL DEFAULT 0.000 COMMENT '结算金额',
	`settlement_rebate` DECIMAL(3,2) DEFAULT 1.00 COMMENT '折扣',
	`lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`adjust_price_detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品调价单明细（分销商）';",
"CREATE TABLE `fx_goods_adjust_price_log` (
  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `adjust_price_record_id` int(11) NOT NULL DEFAULT 0 COMMENT '调价单id',
  `user_id` varchar(64) DEFAULT '' COMMENT '用户ID',
  `user_code` varchar(64) DEFAULT '' COMMENT '用户代码',
  `action_name` varchar(64) DEFAULT '' COMMENT '操作名称',
  `action_time` INT(11) DEFAULT NULL COMMENT '操作时间',
  `action_remark` varchar(64) DEFAULT NULL COMMENT '操作描述',
  `record_status` varchar(50) NOT NULL DEFAULT '' COMMENT '单据状态',
  PRIMARY KEY (`log_id`),
	KEY `idx_adjust_price_record_id` (`adjust_price_record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品调价单操作日志';",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080500', '8080000', 'url', '商品调价单', 'fx/goods_adjust_price/do_list', '6', '1', '0', '1', '0');",
);

$u['1314'] = array(
    "INSERT INTO `sys_action` VALUES ('91020207','7010001','url','波次单扫描验货','oms/waves_record_scan/do_list','28','1','0','1','0');",
);

$u['1274'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080101', '8080100', 'act', '一键添加', 'fx/goods/set_all_goods_fx', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8080102', '8080100', 'act', '一键清除', 'fx/goods/remove_all_goods', '2', '1', '0', '1', '0');",
);
$u['bug_1243'] = array(
    "UPDATE oms_sell_return
SET refund_total_fee = return_avg_money + seller_express_money + compensate_money + adjust_money - change_avg_money - change_express_money
WHERE
	refund_total_fee <> (return_avg_money + seller_express_money + compensate_money + adjust_money - change_avg_money - change_express_money)",
);