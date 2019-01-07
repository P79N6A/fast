<?php

$u = array();

$u['205'] = array(
    "ALTER TABLE `op_gift_strategy_detail`
    ADD COLUMN `is_goods_money`  tinyint(3) NULL DEFAULT 0 COMMENT '是否活动商品满额0,1' AFTER `range_type`;
",
);

$u['197'] = array(
    "ALTER TABLE base_store MODIFY store_property int(4) DEFAULT '0' COMMENT '仓库性质 0普通仓 1门店仓';",
);
$u['215'] = array(
    "INSERT INTO `base_sale_channel` VALUES ('39', 'huayang', 'hy', '华阳', '1', '1', '', '2016-04-13 13:57:24');",
);


$u['174'] = array(
    "INSERT INTO `sys_action` VALUES ('30000000','0','cote','门店管理','store-management','15','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30010000','30000000','group','门店库存','store-management-inv','4','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30010104','30010000','url','门店库存调拨单','stm/store_shift_record/entity_shop','5','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30010108','30010000','url','门店库存调整单','stm/stock_adjust_record/entity_shop','10','1','0','1','2');",
    "ALTER TABLE `stm_stock_adjust_record` ADD is_entity_shop tinyint(4) DEFAULT '0' COMMENT '是否为门店';"
);

$u['194'] = array(
    "INSERT INTO `sys_action` VALUES ('30030000','30000000','group','门店零售','store_sell','3','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30030100','30030000','url','门店销售订单','oms_shop/oms_shop/do_list','1','1','0','1','2');",
    "CREATE TABLE `oms_shop_sell_record` (
	`record_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
	`record_out_code` VARCHAR (50) DEFAULT '' COMMENT '订单外部编号',
	`record_type` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单类型:0-门店（默认）;1-电商',
	`record_source` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单来源：门店/各电商平台',
	`send_store_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '发货仓库代码',
	`send_way` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '发货方式：0-快递配送;1-买家自提（默认）',
	`express_code` VARCHAR (20) DEFAULT '' COMMENT '快递代码',
	`express_no` VARCHAR (20) DEFAULT '' COMMENT '快递单号',
	`online_shop_code` VARCHAR (20) DEFAULT '' COMMENT '网店代码',
	`offline_shop_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '门店代码',
	`cashier_code` VARCHAR (20) DEFAULT '' COMMENT '收银员代码',
	`guide_code` VARCHAR (20) DEFAULT '' COMMENT '导购员代码',
	`customer_code` VARCHAR (20) DEFAULT '' COMMENT '顾客代码',
	`buyer_name` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '购买人/会员昵称',
	`create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
	`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`pay_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单付款状态：0-未付款（默认）;1-部分付款（正在支付）;2-已付款',
	`pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '付款时间',
	`send_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单发货(或买家自提)状态：0-未发货（默认）;1-已发货',
	`send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货(或买家自提)时间',
	`cancel_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单作废状态：0-未作废（默认）;1-已作废',
	`cancel_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单作废时间',
	`cancel_cause` VARCHAR (255) DEFAULT '' COMMENT '订单作废原因',
	`check_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单审核状态：0-未审核（默认） ;1-已审核（主要用于O2O订单，用于接单场景）',
	`check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单审核时间',
	`receiver_name` VARCHAR (30) DEFAULT '' COMMENT '收货人姓名',
	`receiver_phone` VARCHAR (20) DEFAULT '' COMMENT '收货人手机号',
	`receiver_address` VARCHAR (100) NOT NULL DEFAULT '' COMMENT '收货人地址',
	`country` BIGINT (20) DEFAULT NULL COMMENT '国家',
	`province` BIGINT (20) DEFAULT NULL COMMENT '省',
	`city` BIGINT (20) DEFAULT NULL COMMENT '市',
	`district` BIGINT (20) DEFAULT NULL COMMENT '区/县',
	`street` BIGINT (20) DEFAULT NULL COMMENT '街道',
	`address` VARCHAR (100) NOT NULL DEFAULT '' COMMENT '详细地址(不包含省市区)',
	`goods_num` SMALLINT (11) NOT NULL DEFAULT '0' COMMENT '商品数量',
	`sku_num` TINYINT (11) NOT NULL DEFAULT '0' COMMENT 'sku数量',
	`record_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
	`express_money` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单运费',
	`buyer_real_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '买家实付总金额',
	`discount_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商家优惠总金额',
	`hand_adjust_money` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商家手工调整金额',
	`payable_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单应收总金额',
	`plan_send_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '订单计划发货时间',
	`record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	`remark` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '订单备注',
	PRIMARY KEY (`record_id`),
	UNIQUE KEY `idxu_code` (`record_code`) USING BTREE,
	KEY `offline_shop_code` (`offline_shop_code`),
	KEY `send_way` (`send_way`),
	KEY `pay_status` (`pay_status`),
	KEY `send_status` (`send_status`),
	KEY `cancel_status` (`cancel_status`),
	KEY `check_status` (`check_status`),
	KEY `lastchanged` (`lastchanged`),
	KEY `create_time` (`create_time`) USING BTREE,
	KEY `pay_time` (`pay_time`) USING BTREE,
	KEY `send_time` (`send_time`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单主单据';",
    "CREATE TABLE `oms_shop_sell_record_detail` (
	`sell_goods_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
	`goods_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '商品编码',
	`sku` VARCHAR (30) DEFAULT '' COMMENT '商品sku',
	`num` INT (11) DEFAULT '0' COMMENT '销售数量',
	`return_num` INT (11) NOT NULL DEFAULT '0' COMMENT '退货数量',
	`lock_num` INT (10) NOT NULL DEFAULT '0' COMMENT '库存占用数量',
	`price` DECIMAL (10, 3) DEFAULT '0.000' COMMENT '商品吊牌价',
	`rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
	`goods_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商品总金额',
	`avg_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
	`is_gift` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '赠品标识：0-普通商品（默认）;1-赠品',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`sell_goods_id`),
	KEY `record_code` (`record_code`),
	KEY `goods_code` (`goods_code`),
	KEY `sku` (`sku`),
	KEY `is_gift` (`is_gift`),
	KEY `lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单商品信息';",
    "CREATE TABLE `oms_shop_sell_record_pay` (
	`pay_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
	`pay_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '支付代码：现金/支付宝/微信/余额',
	`pay_account` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '支付账号',
	`pay_serial_no` VARCHAR (50) DEFAULT '' COMMENT '支付流水号',
	`pay_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`pay_id`),
	KEY `record_code` (`record_code`),
	KEY `pay_code` (`pay_code`),
	KEY `lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单支付信息';",
    "CREATE TABLE `oms_shop_sell_record_discount` (
	`discount_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
	`discount_way` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '优惠方式：满减优惠券/积分抵扣',
	`discount_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
	`discount_desc` VARCHAR (100) DEFAULT '' COMMENT '优惠描述',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`discount_id`),
	KEY `record_code` (`record_code`),
	KEY `discount_way` (`discount_way`),
	KEY `lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单优惠信息';",
    "CREATE TABLE `oms_shop_sell_record_log` (
	`log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
        `user_code` VARCHAR(30) NOT NULL COMMENT '操作人代码',
        `user_name` VARCHAR(30) NOT NULL COMMENT '操作人',
	`action_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '操作代码',
	`action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
	`pay_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单付款状态：0-未付款（默认）;1-部分付款（正在支付）;2-已付款',
	`send_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单发货(或买家自提)状态：0-未发货（默认）;1-已发货',
	`action_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
	`action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
	PRIMARY KEY (`log_id`),
	KEY `record_code` (`record_code`),
	KEY `action_code` (`action_code`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单操作日志信息';",
);

$u['195'] = array(
    "INSERT INTO `sys_action` VALUES ('30040000','30000000','group','门店收银','store_cashier','2','1','0','1','2');",
    "INSERT INTO `sys_action` VALUES ('30040100','30040000','url','前台收银','oms_shop/cashier/do_list','1','1','0','1','2');",
);
$u['204'] = array(
    "DELETE FROM sys_user_pref WHERE iid='shop_report/table';",
);
$u['148'] = array(
    "INSERT INTO `sys_action` VALUES ('9040000','9000000','group','成本管理','cost_manage','5','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES ('9040100','9040000','url','商品成本月结单','acc/cost_month/do_list','1','1','0','1','0');",
    "CREATE TABLE `cost_month` (
	`cost_month_id` INT (11) NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (64) DEFAULT '' COMMENT '单据编号',
	`ymonth` VARCHAR (7) NOT NULL DEFAULT '0000-00' COMMENT '月结月份',
	`store_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '仓库代码，多个仓库以逗号隔开',
	`begin_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '期初成本总金额',
	`begin_total` INT (11) DEFAULT '0' COMMENT '期初库存总数',
	`end_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '期末成本总金额',
	`end_total` INT (11) DEFAULT '0' COMMENT '期末库存总数',
	`purchase_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '月采购总金额',
	`purchase_total` INT (11) DEFAULT '0' COMMENT '月采购总数',
	`is_sure` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '确认状态：0-未确认，1-已确认',
	`is_check` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '审核状态：0-未审核，1-已审核',
	`check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
	`record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '单据创建时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	`remark` VARCHAR (255) DEFAULT '' COMMENT '备注',
	PRIMARY KEY (`cost_month_id`),
	UNIQUE KEY `idxu_record_code` (`record_code`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '成本月结单主单据';",
    
    "CREATE TABLE `cost_month_log` (
	`log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`record_code` VARCHAR (20) NOT NULL DEFAULT '0' COMMENT '月结单号',
	`user_code` VARCHAR (30) NOT NULL COMMENT '用户代码',
	`user_name` VARCHAR (30) NOT NULL COMMENT '用户名称',
	`sure_status` TINYINT (1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '确认状态',
	`check_status` TINYINT (1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '审核状态',
	`action_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '操作代码',
	`action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
	`action_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
	`action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
	PRIMARY KEY (`log_id`),
	KEY `record_code` (`record_code`),
	KEY `action_time` (`action_time`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '成本月结单操作日志';",
);
$u['239'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010109', '4010100', 'act', '修改', 'oms/sell_record/td_save', '2', '1', '0', '1', '0');",
);
$u['211'] = array(
     "ALTER TABLE `wbm_store_out_record` ADD COLUMN  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '地址(包含省市区)';",
     "ALTER TABLE `base_custom` ADD COLUMN `contact_person` varchar(128) DEFAULT '' COMMENT '联系人';",
);
$u['227'] = array(
    "INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) SELECT 'SF', 'SFGR', '云仓专配隔日', '0', '0', '', '1', '0', '1', '', '', NULL, '', NULL, NULL, NULL, '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', NULL, NULL, '0', NULL, '', NULL, NULL, '2016-03-29 09:46:30' from dual where not exists (select * from base_express WHERE express_code='SFGR');",
    "INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) SELECT 'HTKY', 'BSKD', '百世快递', '0', '0', '', '1', '0', '1', '', '', NULL, '', NULL, NULL, NULL, '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', NULL, NULL, '0', NULL, '', NULL, NULL, '2016-03-29 09:46:30' from dual where not exists (select * from base_express WHERE express_code='BSKD');",
    "INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) SELECT 'SF', 'SFCR', '云仓专配次日', '0', '0', '', '1', '0', '1', '', '', NULL, '', NULL, NULL, NULL, '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', NULL, NULL, '0', NULL, '', NULL, NULL, '2016-03-29 09:46:30' from dual where not exists (select * from base_express WHERE express_code='SFCR');",
);