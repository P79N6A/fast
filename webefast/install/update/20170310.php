<?php

$u = array();

$u['1104'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`) VALUES ('', 'custom_power', 'sys_set', '分销商权限', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭');"
);

$u['1097'] = array(
    "ALTER TABLE `op_gift_strategy` ADD COLUMN strategy_new_type  tinyint(3) DEFAULT '0' COMMENT '策略类型 0:旧策略 1：新策略'"
);

$u['1088'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`, `type`, `action_name`, `action_code`, `status`, `sort_order`,`appid`) VALUES ('4020342','4020300', 'url', '批量替换商品', 'oms/order_opt/opt_change_detail', '1', '4','1');"
);
$u['1098'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050000', '3000000', 'group', '预售管理', 'presell-manage', '7', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050100', '3050000', 'url', '预售计划', 'op/presell/plan_do_list', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050101', '3050100', 'act', '新增', 'op/presell/plan_add', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050102', '3050100', 'act', '编辑', 'op/presell/plan_edit', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050103', '3050100', 'act', '同步', 'op/presell/plan_sync', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3050104', '3050100', 'act', '删除', 'op/presell/do_delete', '4', '1', '0', '1', '0');",
    "UPDATE sys_action SET sort_order=3 WHERE action_id='3020000';",
    "UPDATE sys_action SET sort_order=5 WHERE action_id='3040000';",
    "UPDATE sys_action SET sort_order=9 WHERE action_id='3030000';",
    "CREATE TABLE `op_presell_plan` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_code` VARCHAR (64) NOT NULL COMMENT '预售计划代码',
	`plan_name` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '预售计划名称',
	`start_time` INT (11) NOT NULL DEFAULT '0' COMMENT '预售开始时间',
	`end_time` INT (11) NOT NULL DEFAULT '0' COMMENT '预售结束时间',
	`sync_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '同步库存状态：0-未同步;1-已同步',
	`create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
	`create_time` INT (11) NOT NULL DEFAULT '0' COMMENT '插入时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_start_time` (`start_time`) USING BTREE,
	KEY `ind_end_time` (`end_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售计划表';",
    "CREATE TABLE `op_presell_plan_shop` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_code` VARCHAR (64) NOT NULL COMMENT '预售计划代码',
	`shop_code` VARCHAR (128) NOT NULL COMMENT '店铺代码',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_plan_shop` (`plan_code`, `shop_code`) USING BTREE,
	KEY `ind_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_shop_code` (`shop_code`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售计划店铺关联表';",
    "CREATE TABLE `op_presell_plan_detail` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_code` VARCHAR (128) NOT NULL COMMENT '预售计划代码',
	`sku` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '系统SKU',
	`presell_num` INT (11) DEFAULT '0' COMMENT '预售数量',
	`sell_num` INT (11) DEFAULT '0' COMMENT '销售数量',
	`plan_send_time` INT (11) NOT NULL DEFAULT '0' COMMENT '计划发货时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`plan_code`, `sku`) USING BTREE,
	KEY `ind_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_sku` (`sku`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售计划明细';",
    "CREATE TABLE `op_presell_log` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`plan_code` VARCHAR (64) NOT NULL COMMENT '预售计划代码',
	`user_code` VARCHAR (30) NOT NULL COMMENT '用户代码',
	`user_name` VARCHAR (30) NOT NULL COMMENT '用户名称',
	`action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
	`action_time` INT (11) NOT NULL DEFAULT '0' COMMENT '操作时间',
	`action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
	PRIMARY KEY (`id`),
	KEY `ind_plan_code` (`plan_code`) USING BTREE,
	KEY `ind_action_time` (`action_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预售计划日志表';",
);


$u['1015'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('dajiashequ','djsq','大家社区','1','1');");

$u['bug_998'] = array(
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070302);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070303);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070304);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070305);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070306);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070307);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070308);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070309);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070310);",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070302', '8070300', 'url', '锁定', 'oms/order_opt/opt_lock', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070303', '8070300', 'url', '解锁', 'oms/order_opt/opt_unlock', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070304', '8070300', 'url', '付款', 'oms/order_opt/opt_pay', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070305', '8070300', 'url', '取消付款', 'oms/order_opt/opt_unpay', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070306', '8070300', 'url', '作废', 'oms/order_opt/opt_cancel', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070307', '8070300', 'url', '复制订单', 'oms/order_opt/opt_copy', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070308', '8070300', 'url', '强制解锁', 'oms/order_opt/opt_force_unlock', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070309', '8070300', 'act', '结算', 'oms/order_opt/opt_settlement', '5', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070310', '8070300', 'act', '取消结算', 'oms/order_opt/opt_unsettlement', '5', '1', '0', '1', '0');",
);


$u['1094']=array(
    "CREATE TABLE `stm_stock_lock_record` (
  `stock_lock_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `order_time` datetime DEFAULT NULL COMMENT '下单日期',
  `order_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '单据状态：0:未锁定，1:锁定，2:释放，3:作废',
  `lock_obj` tinyint(4) NOT NULL DEFAULT '0' COMMENT '锁定对象： 0:无，1:唯品会jit，2:网络店铺',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `lock_num` int(11) DEFAULT '0' COMMENT '锁定数量',
  `release_num` int(11) DEFAULT '0' COMMENT '已释放数量',
  `available_num` int(11) DEFAULT '0' COMMENT '可用锁定数量',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `lock_person` varchar(64) DEFAULT '' COMMENT '锁定人',
  `lock_time` datetime DEFAULT NULL COMMENT '锁定时间',
  `release_person` varchar(64) DEFAULT '' COMMENT '释放人',
  `release_time` datetime DEFAULT NULL COMMENT '释放时间',
  `cancel_person` varchar(64) DEFAULT '' COMMENT '作废人',
  `cancel_time` datetime DEFAULT NULL COMMENT '作废时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`stock_lock_record_id`),
  UNIQUE KEY `_key` (`record_code`) USING BTREE,
  KEY `_index2` (`store_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='库存锁定单';",
    "CREATE TABLE `stm_stock_lock_record_detail` (
  `stock_lock_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '单价',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `lock_num` int(11) DEFAULT '0' COMMENT '锁定数量',
  `release_num` int(11) DEFAULT '0' COMMENT '已释放数量',
  `available_num` int(11) DEFAULT '0' COMMENT '可用锁定数量',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  PRIMARY KEY (`stock_lock_record_detail_id`),
  UNIQUE KEY `_index_key` (`sku`,`record_code`) USING BTREE,
  KEY `_index1` (`goods_code`) USING BTREE,
  KEY `_index2` (`record_code`) USING BTREE,
  KEY `_index3` (`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存锁定单明细表';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6040100', '6010000', 'url', '库存锁定单', 'stm/stock_lock_record/do_list', '6', '1', '0', '1', '0');",
);

$u['bug_1020'] = array("UPDATE base_sale_channel SET sale_channel_name='ofashion迷橙' WHERE sale_channel_code='ofashion'");

$u['960'] = array(
"INSERT ignore INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('archive', 'bserp2', 'to_sys', 1);",
"INSERT ignore INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('sell_record', 'bserp2', 'send', 1);",
"INSERT ignore INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('sell_return', 'bserp2', 'receiving', 1);",
"INSERT IGNORE INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('wbm_store_out', 'bserp2', 'send', 1);",
"INSERT IGNORE INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('wbm_return', 'bserp2', 'receiving', 1);",
"INSERT IGNORE INTO `mid_process_flow` (record_type,api_product,record_mid_type,check_type) VALUES ('inv', 'bserp2', 'to_sys', 1);",

);


$u['1101'] =  array(
    "ALTER TABLE base_supplier ADD `country` varchar(20) DEFAULT '1' COMMENT '国家'; "
);
$u['1128'] = array(
    "ALTER TABLE base_goods ADD COLUMN `is_custom_money` tinyint(1) DEFAULT '0' COMMENT '是否设置分销款 0：不是 1：是';",
    "ALTER TABLE base_goods ADD COLUMN `is_custom` tinyint(1) DEFAULT '0' COMMENT '是否设置分销商 0：不是 1：是';",
    "CREATE TABLE fx_appoint_goods 
(
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
	`custom_code` varchar(128) NOT NULL DEFAULT '' COMMENT '分销商代码',
	`fx_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销价',
	`fx_rebate` decimal(4,3) NOT NULL DEFAULT 1.000 COMMENT '指定分销商折扣',
	`modify_name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '修改人名称',
	`user_code` VARCHAR(64) NOT NULL DEFAULT '' COMMENT '修改人代码',
	`lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_key` (`goods_code`,`custom_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品指定分销商表';",
    "UPDATE `sys_action` SET `action_name`='分销商品定义' WHERE (`action_id`='8080100');",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8080100);"
);
$u['bug_1036'] = array(
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8070311);",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070311', '8070300', 'url', '生成退单', 'oms/order_opt/opt_create_return', '5', '1', '0', '1', '0');",
    "ALTER TABLE oms_sell_return ADD COLUMN change_fx_amount  decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销换货均摊金额';"
);
