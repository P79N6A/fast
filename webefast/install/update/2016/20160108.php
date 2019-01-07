<?php
$u = array();
$u['FSF-1929'] = array(
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
			values('','weight_different_notice','waves_property','S002_012  称重时，重量偏差提醒','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','','开启：实际重量大于商品重量且大于偏差范围，或实际重量小于商品重量时，报警声音提示；关闭：不做重量差异判断');",
		"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) 
			values('weight_different','waves_property','S002_013   重量偏差范围（千克）','text','','0','','实际重量大于理论重量，且大于此偏差，报警声音提示');"
);

$u['FSF-1942'] = array(
    "ALTER TABLE `op_gift_strategy_detail`
ADD COLUMN `is_repeat`  tinyint(3) NULL DEFAULT 0 COMMENT '是否重复送' AFTER `is_fixed_customer`;",
    
    "ALTER TABLE `wms_oms_trade`
MODIFY COLUMN `order_weight`  decimal(20,3) NOT NULL DEFAULT 0 COMMENT '订单重量' AFTER `express_no`;"
);

$u['FSF-1940'] = array(
    "ALTER TABLE `goods_combo_diy`
    ADD COLUMN `price`  decimal(20,3) NULL AFTER `num`;",
);
$u['FSF-1945'] = array(
		"INSERT INTO `base_sale_channel` VALUES ('37', 'vdian', 'vd', '微店', '1', '1', '', '2015-12-25 13:57:24');",
		);
$u['FSF-1954'] = array(
		"INSERT INTO `sys_action` VALUES ('4020202', '4020200', 'act', '导出', 'oms/sell_record/export_list', '1', '1', '0', '1','0');",
		"INSERT INTO `sys_action` VALUES ('4020323', '4020300', 'act', '导出', 'oms/sell_record/export_ext_list', '1', '1', '0', '1','0');",
);
$u['FSF-1955'] = array(
		"ALTER TABLE oms_sell_record ADD COLUMN `order_label_code` varchar(128) NOT NULL DEFAULT '' COMMENT '订单标签'",
		"ALTER TABLE oms_sell_record ADD KEY `order_label_code` (`order_label_code`)",
		);



$u['FSF-1918'] = array(
    "
        CREATE TABLE `wms_b2b_order_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `wms_sl` int(11) NOT NULL DEFAULT '0' COMMENT 'wms商品数量',
  `item_type` tinyint(3) DEFAULT '1' COMMENT '1是正品，0次品',
  `new_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '自动生存单号',
  `is_create` smallint(2) NOT NULL DEFAULT '0' COMMENT '单据是否创建',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`,`item_type`,`new_record_code`) USING BTREE,
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `record_type` (`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
);


