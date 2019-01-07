<?php


$u['2161'] = array(
    "ALTER TABLE api_weipinhuijit_po ADD `po_start_time` datetime COMMENT 'po开始时间';"
);
$u['2164'] = array(//售后服务单: 新增商品权限
    "INSERT INTO `sys_action` VALUES ('4030124', '4030100', 'act', '新增商品', 'oms/return_opt/opt_add_goods', '9', '1', '0', '1', '0');",
    "INSERT INTO sys_role_action(`role_id`,`action_id`) SELECT `role_id`,'4030124' AS `action_id` FROM sys_role_action WHERE action_id = '4030100'"
);

$u['2160']=array(
        "INSERT INTO sys_action (action_id,parent_id, type, action_name, action_code, sort_order, appid, other_priv_type, status, ui_entrance) VALUES ('8040201','8040200', 'act', '删除', 'api/api_weipinhuijit_po_pick/delete', 1, 1, 0, 1, 0);",
        "INSERT INTO sys_action (action_id,parent_id, type, action_name, action_code, sort_order, appid, other_priv_type, status, ui_entrance) VALUES ('8040601','8040600', 'act', '删除', 'api/api_weipinhuijit_multi_po_pick/delete', 1, 1, 0, 1, 0);"
);
$u['2149']=array(
    "ALTER TABLE `oms_sell_record` ADD COLUMN `package_num` tinyint(2) DEFAULT 1 COMMENT '订单包裹数量';"
);

$u['1314']=array(
    "CREATE TABLE `api_weipinhuijit_goods_sync_logs` (
	  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `barcode` varchar(128) DEFAULT '' COMMENT '商品条码',
	  `shop_code` varchar(100) NOT NULL COMMENT '店铺编码',
	  `warehouse` varchar(128) DEFAULT '' COMMENT '唯品会仓库编码',
	  `cooperation_no` varchar(128) DEFAULT '' COMMENT '常态合作编码',
	  `sync_inv_num` int(8) DEFAULT '-1' COMMENT '最终同步到平台的库存',
	  `desc_text` varchar(128) DEFAULT '' COMMENT '库存计算过程及其描述',
	  `inv_num` int(8) DEFAULT '0' COMMENT '本地已计算可同步库存',
	  `sync_val` int(8) DEFAULT '0' COMMENT '仓库同步比例',
	  `amount` int(11) DEFAULT '0' COMMENT '平台已成交销售订单商品数量',
	  `num` int(8) DEFAULT '0' COMMENT '平台可售库存:剩余库存+占用库存',
	  `leaving_stock` int(11) DEFAULT '0' COMMENT '平台剩余库存',
	  `current_hold` int(11) DEFAULT '0' COMMENT '平台占用库存:购物车+未支付订单',
	  `circuit_break_value` int(11) DEFAULT '0' COMMENT '平台熔断值',
	  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '时间戳',
	  PRIMARY KEY (`id`),
	  KEY `_key` (`barcode`,`shop_code`,`warehouse`,`cooperation_no`) USING BTREE
	) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COMMENT='唯品会商品库存同步日志表';"
);
//修改wms_custom_goods_sku索引
$u['bug_2302']=array(
    "alter table wms_custom_goods_sku drop index `wms_sku`;",
    "alter table wms_custom_goods_sku drop index `ind_sku`;",
    "alter table wms_custom_goods_sku add unique index `wms_sku`(`sku`,`wms_config_id`);",
    "alter table wms_custom_goods_sku add index `ind_sku`(`barcode`);"
);


