<?php

$u['1503'] = array(
    "DELETE FROM sys_params WHERE `param_code` = 'return_default_store_code';"
);

$u['1494'] = array(
    "UPDATE `sys_params` SET `form_desc`='[\"未确认\",\"已确认\",\"已拣货\",\"只更新不设问\"]', `remark`='0-未确认 1-已确认 2-已拣货 3-只更新不设问', `memo` = '对设置状态前（包含设置状态）的订单进行拦截设问并更新，其他状态只更新备注，不拦截设问；选择‘只更新不设问’：只对订单备注进行更新，不拦截设问订单' WHERE (`param_code`='sync_seller_remark_node');"
);

$u['bug_1488'] = array(
    "UPDATE sys_action SET action_name='日报管理' WHERE action_id='15020200';",
);

$u["1497"] = array(
    "CREATE TABLE `op_inv_sync_warn_sku` (
  `warn_sku_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sync_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `warn_sku_val` int(11) NOT NULL DEFAULT '0' COMMENT '条码预警值',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`warn_sku_id`),
  UNIQUE KEY `idxu_key` (`sync_code`,`sku`,`shop_code`),
  KEY `ix_sync_code` (`sync_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='条码预警表';",
);

$u['1493'] = array(
    "insert IGNORE into `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`,`sort`, `remark`, `memo`) 
VALUES (  'export_is_format_goods', 'app', 'S008_014 导出数据格式化', 'radio', '[\"关闭\",\"开启\"]\r\n', '1',  '30','1-开启 0-关闭' ,'关闭后，若商品编码和条形码为纯数字且超过一定位数会导致科学计数法。');
",
);


$u['1511'] = array(
    "DELETE from sys_schedule WHERE code='weipinhuijit_getOccupiedOrders_cmd';",
    "ALTER TABLE api_weipinhuijit_pick ADD COLUMN  `deduct_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '核销接口识别状态 0未核销，1已核销'",
);

$u['bug_1502'] = array(
    'UPDATE sys_schedule SET type=6 WHERE code IN ("cli_upload_mid", "cli_order_shipping_mid", "cli_sync_archive", "cli_sync_inv");',


    "INSERT INTO `sys_schedule` (`code`,`name`,`task_type_code`,`status`,`type`,`request`,`path`,`loop_time`,`task_type`,`task_module`) VALUES('cli_sys_to_mid','集成单据生成','cli_sys_to_mid','1','10','{\"app_act\":\"mid/mid/cli_sys_to_mid\"}','webefast/web/index.php','60','0','sys');",


    "CREATE TABLE `mid_day_report` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_day_date` date NOT NULL DEFAULT '0000-00-00',
  `shop_code` varchar(64) NOT NULL,
  `store_code` varchar(64) NOT NULL DEFAULT '',
  `record_type` varchar(64) NOT NULL DEFAULT '',
  `create_time` int(11) DEFAULT '0',
  `sku_num` int(11) DEFAULT '0',
  `data_num` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`report_day_date`,`shop_code`,`store_code`,`record_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8;

"
);
