<?php

$u = array();
$u['369'] = array(
    "ALTER table oms_sell_record_rank add COLUMN customer_code VARCHAR(128) DEFAULT '' COMMENT '用户ID';",
    "ALTER table oms_sell_record_rank add COLUMN strategy_code VARCHAR(128) DEFAULT '' COMMENT '策略code';",
    "ALTER TABLE `oms_sell_record_rank`  ADD  INDEX sell_record_code (`sell_record_code`);",
    "ALTER TABLE `oms_sell_record_rank` ADD `order_status` tinyint(11) NOT NULL;",
    "ALTER TABLE `op_strategy_log` ADD  INDEX sell_record_code (`sell_record_code`);",
    "ALTER TABLE `op_strategy_log` ADD  INDEX strategy_code (`strategy_code`);",
);
$u['376'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010301', '4010300', 'act', '库存同步(单个/批量)', 'api/sys/goods/sync_goods_inv', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010302', '4010300', 'act', '允许库存同步(单个/批量)', 'oms/api_goods/p_update_active', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4010303', '4010300', 'act', '禁止库存同步(单个/批量)', 'oms/api_goods/p_update_active/ban', '1', '1', '0', '1', '0');",
);
$u['371'] = array(
    "ALTER TABLE oms_return_package ADD COLUMN `buyer_name` varchar(20) NOT NULL DEFAULT '' COMMENT '购买人名称';",
    "UPDATE oms_return_package AS op,oms_sell_return AS os SET op.buyer_name = os.buyer_name WHERE op.sell_return_code = os.sell_return_code",
);

$u['370'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3020800', '3020000', 'url', '库存同步策略', 'op/inv_sync/do_list', '7', '1', '0', '0', '0');",
);

$u['379'] = array(
    "ALTER table oms_sell_record add COLUMN fenxiao_code VARCHAR(128) DEFAULT '' COMMENT '分销商code' AFTER fenxiao_name;",
    "ALTER table oms_sell_record add COLUMN fenxiao_power TINYINT(3) DEFAULT '0' COMMENT '分销商货权' AFTER fenxiao_name;",
    "ALTER table oms_sell_record add COLUMN fenxiao_account TINYINT(3) DEFAULT '0' COMMENT '分销是否结算' AFTER fenxiao_power;",
    "ALTER table fx_running_account add COLUMN relation_code VARCHAR(128) DEFAULT '' COMMENT '关联单据编号' AFTER record_code;",
    "INSERT INTO `sys_action` VALUES ('4020340', '4020300', 'act', '结算', 'oms/order_opt/opt_settlement', '5', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('4020341', '4020300', 'act', '取消结算', 'oms/order_opt/opt_unsettlement', '5', '1', '0', '1','0');"
);
$u['373'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'sell_record_fh_list/table';",
);
$u['372'] = array(
"CREATE TABLE `wms_inv_compare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compare_code` varchar(128) DEFAULT NULL,
  `store_code` varchar(50) NOT NULL,
  `wms_type` varchar(50) NOT NULL,
  `wms_store_code` varchar(50) NOT NULL,
  `compare_num` int(11) NOT NULL DEFAULT '0',
  `compare_sku_num` int(11) NOT NULL DEFAULT '0',
  `compare_time` datetime NOT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`compare_code`) USING BTREE,
  KEY `_index1` (`store_code`) USING BTREE,
  KEY `_index2` (`compare_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
 "CREATE TABLE `wms_inv_compare_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `compare_code` varchar(128) DEFAULT NULL,
  `store_code` varchar(50) NOT NULL,
  `wms_type` varchar(50) NOT NULL,
  `wms_store_code` varchar(50) NOT NULL,
  `sku` varchar(128) NOT NULL,
  `sys_num` int(11) DEFAULT '0',
  `wms_num` int(11) DEFAULT '0',
  `barcode` varchar(128) NOT NULL,
  `compare_time` datetime NOT NULL,
  `remark` varchar(128) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`compare_code`,`store_code`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
    
 " INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`)
 VALUES ('down_wms_stock_compare', '仓储库存对照', 'down_wms_stock_compare', '', '0', '2', '仓储库存对照,每天凌晨以后执行', '{\"app_act\":\"wms/wms_mgr/down_wms_stock_compare\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '86400', '0', 'sys', '', '0', '{\"time\":[\"00:30\"]}', '0');",
 
    "INSERT INTO `sys_action` VALUES ('7030104','7030001','url','库存对照','wms/wms_trade/inv_compare_list','40','1','0','1','0');",
);
$u['375'] = array(
    "ALTER table api_order_send add COLUMN `fail_num` tinyint(3) DEFAULT '0' COMMENT '回写错误次数';",
);
$u['bug_287'] = array(
    "ALTER TABLE oms_sell_record_notice_detail ADD combo_sku varchar(128) DEFAULT '' COMMENT '套餐条形码';",
    "UPDATE oms_sell_record_notice_detail AS rl,oms_sell_record_detail AS r2 SET rl.combo_sku = r2.combo_sku WHERE rl.sell_record_code = r2.sell_record_code AND rl.deal_code = r2.deal_code AND rl.sku = r2.sku AND rl.is_gift = r2.is_gift;",
);
$u['bug_300']=array(
    "ALTER TABLE wbm_return_record MODIFY COLUMN `record_time` date DEFAULT NULL COMMENT '业务日期';",
);