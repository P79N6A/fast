<?php

$u = array();

$u['923'] = array(
    "ALTER TABLE oms_deliver_record_detail ADD COLUMN `picking_num` int(11) NOT NULL DEFAULT '0' COMMENT '拣货数量（二次拣货）';",
    "UPDATE sys_params SET memo = '开启后，需刷新系统或按F5，新增菜单：配发货->订单发货管理->订单包裹查询在波次单打印详情页，可下拉选择包裹再获取单号以及打印。开启或关闭此功能，需要保证已匹配单号订单发货或者称重完成。' WHERE param_code = 'is_more_deliver_package';",
);
$u['924'] = array(
    "DELETE FROM sys_role_action WHERE role_id = 100;",
    "UPDATE `sys_action` SET `action_name`='充值', `action_code`='fx/account/balance_detail_recharge' WHERE (`action_id`='9060101');",
    "UPDATE `sys_action` SET `action_name`='扣款', `action_code`='fx/account/balance_detail_deduct_money' WHERE (`action_id`='9060102');",
    "UPDATE `sys_action` SET `action_name`='明细', `action_code`='fx/account/do_account_detail' WHERE (`action_id`='9060103');",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8000000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8090000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8090100);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8090200);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070100);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070200);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070300);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070400);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9000000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060100);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060400);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060402);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060500);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060103);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,9060101);",
    "CREATE TABLE `fx_income_pay_serial` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `pra_out_trade_no` varchar(20) DEFAULT NULL COMMENT '流水号',
  `income_serial_number` varchar(128) DEFAULT '' COMMENT '收支明细流水号',
  `alipay_trade_no` varchar(20) DEFAULT NULL COMMENT '支付宝交易号',
  `pra_total_fee` decimal(10,2) DEFAULT '0.00' COMMENT '支付金额',
  `buyer_email` varchar(50) DEFAULT NULL COMMENT '支付人email',
  `seller_email` varchar(50) DEFAULT NULL COMMENT '收款人email',
  `pay_status` tinyint(1) DEFAULT '0' COMMENT '支付状态 0 未支付 1 已支付（未支付不会再次支付）',
  `pay_time` INT NOT NULL DEFAULT 0 COMMENT '交易成功时间',
  `pay_begin_time` INT NOT NULL DEFAULT 0 COMMENT '交易开始时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pra_out_trade_no` (`pra_out_trade_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商充值流水表';",
    "CREATE TABLE `fx_pay_temporary` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `pra_out_trade_no` varchar(20) DEFAULT NULL COMMENT '充值流水号',
  `serial_number` varchar(128) DEFAULT '' COMMENT '收支明细流水号',
  `custom_code` varchar(128) NOT NULL DEFAULT '' COMMENT '分销代码',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `record_time` int(11) NOT NULL DEFAULT '0' COMMENT '业务时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `pay_type_code` varchar(128) NOT NULL DEFAULT '' COMMENT '支付方式代码',
  `capital_account` varchar(32) NOT NULL DEFAULT '' COMMENT '资金帐户(资金流水)',
  `capital_type` tinyint(1) DEFAULT NULL COMMENT '明细类型:0-扣款;1-充值(资金流水)',
  `abstract` varchar(64) NOT NULL DEFAULT '' COMMENT '摘要',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_pra_out_trade_no` (`pra_out_trade_no`) USING BTREE
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商充值临时表';",
);

$u['939'] = array(
    //组装单增加唯一键
    "ALTER TABLE stm_goods_diy_record ADD UNIQUE `uni_record_code` (`record_code`);",
);

$u['943'] = array(
    "UPDATE wbm_store_out_record  SET is_store_out_time = lastchanged WHERE is_store_out=1 "
);

$u['846'] = array(
    //金蝶字段属性维护
    "ALTER TABLE kisdee_config MODIFY `online_time` date NOT NULL DEFAULT '0000-00-00' COMMENT 'KIS上线时间'"
);
$u['952'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040700', '8040000', 'url', '专场商品管理', 'api/wph/sales/do_list', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040701', '8040700', 'act', '获取专场', 'api/wph/sales/get_sales_list', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040702', '8040700', 'act', '专场商品库存同步', 'api/wph/sales_sku/inv_sync', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040703', '8040700', 'act', '调整平台库存', 'api/wph/sales_sku/adjust_inv', '3', '1', '0', '1', '0');",
    "DROP TABLE IF EXISTS api_wph_sales;",
    "CREATE TABLE `api_wph_sales` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	`sales_no` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '专场ID',
	`name` VARCHAR (100) NOT NULL DEFAULT '' COMMENT '专场名称',
	`sale_st` INT (11) NOT NULL DEFAULT '0' COMMENT '专场开售时间',
	`sale_et` INT (11) NOT NULL DEFAULT '0' COMMENT '专场停售时间',
	`warehouse` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '唯品会仓库',
	`insert_time` INT (11) NOT NULL DEFAULT '0' COMMENT '插入时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_sales_no` (`sales_no`) USING BTREE,
	KEY `ind_shop_code` (`shop_code`) USING BTREE,
	KEY `ind_sale_st` (`sale_st`) USING BTREE,
	KEY `ind_sale_et` (`sale_et`) USING BTREE,
	KEY `ind_insert_time` (`insert_time`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会专场信息表';",
    "DROP TABLE IF EXISTS api_wph_sales_sku_relation;",
    "CREATE TABLE `api_wph_sales_sku_relation` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sales_no` INT (11) NOT NULL COMMENT '专场id',
	`barcode` VARCHAR (128) NOT NULL COMMENT '平台商品条形码',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_sales_no_barocde` (`sales_no`, `barcode`) USING BTREE,
	KEY `ind_sales_no` (`sales_no`) USING BTREE,
	KEY `ind_barcode` (`barcode`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会专场-SKU关系表';",
    "DROP TABLE IF EXISTS api_wph_sales_sku;",
    "CREATE TABLE `api_wph_sales_sku` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	`barcode` VARCHAR (128) NOT NULL COMMENT '平台商品条形码',
	`sku` VARCHAR (64) NOT NULL COMMENT '系统SKU',
	`product_name` VARCHAR (128) NOT NULL DEFAULT '0' COMMENT '平台商品名称',
	`brand_id` VARCHAR (128) NOT NULL DEFAULT '0' COMMENT '平台品牌ID',
	`brand_name` VARCHAR (128) NOT NULL DEFAULT '0' COMMENT '平台品牌名称',
	`init_num` INT (11) DEFAULT '0' COMMENT '初始化库存数',
	`pt_sale_num` INT (11) DEFAULT '0' COMMENT '平台可售库存数',
	`diff_num` INT (11) DEFAULT '0' COMMENT '增减库存数',
	`last_sync_num` INT (11) DEFAULT '0' COMMENT '最后同步库存数',
	`sales_st` INT (11) NOT NULL DEFAULT '0' COMMENT '商品上线售卖时间',
	`last_sync_time` INT (11) NOT NULL DEFAULT '0' COMMENT '最后同步时间',
	`sync_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '同步状态：0-未同步;1-成功;2-失败',
	`fail_info` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '同步失败信息',
	`insert_time` INT (11) NOT NULL DEFAULT '0' COMMENT '插入时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_barcode` (`barcode`) USING BTREE,
	KEY `ind_sku` (`sku`) USING BTREE,
	KEY `ind_last_sync_time` (`last_sync_time`) USING BTREE,
	KEY `ind_lastchanged` (`lastchanged`) USING BTREE,
	KEY `ind_sales_st` (`sales_st`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会专场SKU表';"
);

$u['bug_786'] = array(
    "delete from sys_schedule where code='delivery_person';",
);

$u['bug_849'] = array(
    "ALTER TABLE `fx_income_pay` ADD COLUMN `relevance_serial_number` varchar(128) NOT NULL DEFAULT '' COMMENT '红冲（作废）关联流水号';",
    "ALTER TABLE `fx_income_pay` ADD COLUMN `relevance_red_serial_number` varchar(128) NOT NULL DEFAULT '' COMMENT '关联红冲（作废）流水号';",
);

$u['971'] = array(
    "update sys_schedule set name='缺货订单自动解除缺货',`desc`='开启后，每隔10分钟运行一次；缺货商品库存补足后，系统会自动按照计划发货时间依次解除缺货状态；'  where code='cli_batch_remove_short';",
);

$u['916'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3010200', '3010000', 'url', '会员增长量分析', 'rpt/custom_improve/do_list', '2', '1', '0', '1', '0')"
);
