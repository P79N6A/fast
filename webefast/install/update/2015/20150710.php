<?php
$u = array();
$u['FSF-1430'] = array(
	"INSERT INTO `base_sale_channel` VALUES ('29', 'chuchujie', 'ccj', '楚楚街', '1', '1', '', '2015-06-29 16:12:24')",
);
$u['FSF-1421'] = array(
		"ALTER TABLE pur_planned_record ADD `pur_type_code` VARCHAR(100) DEFAULT '' COMMENT '采购类型代码';",
);
$u['FSF-1417'] = array(
	"insert into `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `remark`, `memo`)
values('is_allowed_exceed','oms_property','退货商品数，不允许超过订单商品数','radio','[\"关闭\",\"开启\"]','1','1-开启 0-关闭','开启后，退货商品数，不允许超过订单商品数');"

);
$u['FSF-470'] = array(
    "ALTER TABLE `oms_sell_settlement_record` ADD COLUMN `pay_code` VARCHAR(20) DEFAULT ''  NOT NULL   COMMENT '支付方式代码' AFTER `shop_code`;",
    "ALTER TABLE `oms_sell_settlement_record` ADD  INDEX `pay_code` (`pay_code`);",
    "UPDATE `oms_sell_settlement_record` ossr, oms_sell_record osr SET ossr.`pay_code`=osr.`pay_code` WHERE ossr.`sell_record_code`=osr.`sell_record_code`;",
    "ALTER TABLE `oms_sell_settlement_record` ADD COLUMN `sell_settlement_code` VARCHAR(24) NOT NULL AFTER `id`;"
);
$u['FSF-1423'] = array(
    "INSERT INTO `base_express_company` (`company_id`, `company_code`, `company_name`, `rule`, `sys`, `is_active`, `remark`, `lastchanged`) VALUES ('64', 'KHZT', '自提', '', '1', '1', '', '2015-07-02 09:31:33');",
    "INSERT INTO `base_express` (`express_id`, `company_code`, `express_code`, `express_name`, `type`, `area_type`, `tel`, `status`, `is_cash_on_delivery`, `sys`, `goods_img`, `is_add_person`, `is_add_time`, `is_edit_person`, `is_edit_time`, `print`, `printer_name`, `remark`, `reg_mail_no`, `calc_type`, `base_fee`, `base_weight`, `per_fee`, `per_weight`, `free_fee`, `per_rule`, `zk`, `free_per_weight`, `print_type`, `rm_id`, `rm_shop_code`, `df_id`, `pt_id`, `lastchanged`) VALUES ('64', 'KHZT', 'KHZT', '自提', '0', '0', '', '1', '0', '1', '', '', NULL, '', NULL, NULL, NULL, '', '', '0', '0.000', '0.00', '0.000', '0.00', '0.000', '0.000', NULL, NULL, '0', NULL, '', NULL, NULL, '2015-07-02 09:46:30');"
);
$u['FSF-1427'] = array(
	"ALTER TABLE `oms_sell_record`   
		  ADD COLUMN `is_buyer_remark` TINYINT(4) DEFAULT 0  NOT NULL   COMMENT '是否有买家留言' AFTER `lastchanged`,
		  ADD COLUMN `is_seller_remark` TINYINT(4) DEFAULT 0  NOT NULL   COMMENT '是否有卖家留言' AFTER `is_buyer_remark`;",
);
$u['FSF-1446'] = array(
    "ALTER TABLE `wms_oms_trade`
    ADD COLUMN `process_fail_num`  tinyint(4) NULL DEFAULT 0 COMMENT '处理失败次数' AFTER `wms_order_from_flag`;",
    "ALTER TABLE `wms_b2b_trade`
    ADD COLUMN `process_fail_num`  tinyint(4) NULL DEFAULT 0 COMMENT '处理失败次数' AFTER `wms_order_from_flag`;",
);
$u['FSF-1200'] = array(
    "ALTER TABLE `oms_sell_return` ADD COLUMN `refund_id` VARCHAR(30) NULL   COMMENT '平台退单号' AFTER `sell_record_code`;",
    "UPDATE `oms_sell_return` `or`,`api_refund` `ar` SET `or`.`refund_id`=`ar`.`refund_id` WHERE `or`.`sell_return_code`=`ar`.`refund_record_code`;",
);
    $u['FSF-1464'] = array(
        "CREATE TABLE `sys_kc_sync_cfg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='库存同步百分比设置';",
        "INSERT IGNORE INTO `sys_params`(param_id,param_code,parent_code,param_name,type,form_desc,value,sort,remark,memo) VALUES ('', 'tran_order_auto_split', 'oms_property', '转单自动分仓拆单', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '转单自动分仓拆单');",

    );    
    $u['FSF-1467'] = array(
    		"ALTER TABLE api_jingdong_trade ADD COLUMN `modified` varchar(100) DEFAULT NULL COMMENT '订单更新时间'",
    		"ALTER TABLE api_mogujie_sku ADD COLUMN `sku_stock` varchar(50) DEFAULT NULL COMMENT 'sku库存'",
    		"ALTER TABLE api_mogujie_sku DROP KEY sku_id_sd",
    		"ALTER TABLE api_mogujie_sku DROP KEY sku_id",
    		"ALTER TABLE api_mogujie_sku ADD UNIQUE KEY `sku_id` (`sku_id`)",
    		"ALTER TABLE api_mogujie_sku ADD  KEY `sku_code` (`sku_code`)",
    		);
    $u['FSF-1419'] = array(
    		"CREATE TABLE `goods_combo` (
    		`goods_combo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		`goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
    		`goods_name` VARCHAR(128) DEFAULT '' COMMENT '商品名称',
    		`price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
    		`goods_desc` VARCHAR(255) DEFAULT '' COMMENT '详细描述',
    		`status` INT(4) DEFAULT '0' COMMENT '0：启用 1：停用',
    		`create_time` DATETIME DEFAULT NULL COMMENT '添加时间',
    		`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    		PRIMARY KEY (`goods_combo_id`),
    		UNIQUE KEY `goods_code` (`goods_code`) USING BTREE,
    		KEY `_statsu` (`status`) USING BTREE
    ) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='商品套餐';",
    		"CREATE TABLE `goods_combo_spec2` (
    		`goods_combo_spec2_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		`goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
    		`spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
    		`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    		PRIMARY KEY (`goods_combo_spec2_id`),
    		UNIQUE KEY `goods_code_and_size_code` (`goods_code`,`spec2_code`) USING BTREE
    ) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='套餐商品尺码';",
    		" CREATE TABLE `goods_combo_spec1` (
    		`goods_combo_spec1_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		`goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
    		`spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
    		`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    		PRIMARY KEY (`goods_combo_spec1_id`),
    		UNIQUE KEY `goods_code_and_color_code` (`goods_code`,`spec1_code`) USING BTREE
    ) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='套餐商品颜色';",
    		" CREATE TABLE `goods_combo_diy` (
    		`goods_combo_diy_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		`goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
    		`spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
    		`spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
    		`sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
    		`p_goods_code` VARCHAR(64) DEFAULT '' COMMENT '父类商品代码',
    		`p_sku` VARCHAR(128) DEFAULT '' COMMENT '父类sku',
    		`add_time` DATETIME DEFAULT NULL COMMENT '添加时间',
    		`num` INT(8) DEFAULT '0' COMMENT '数量',
    		`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    		PRIMARY KEY (`goods_combo_diy_id`),
    		UNIQUE KEY `goods_code_spec` (`sku`,`p_sku`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='商品套餐组装表';",
    		"CREATE TABLE `goods_combo_barcode` (
    		`goods_combo_barcode_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    		`goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
    		`spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
    		`spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
    		`sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
    		`price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
    		`barcode` VARCHAR(255) DEFAULT '' COMMENT '条码',
    		`add_time` DATETIME DEFAULT NULL COMMENT '添加时间',
    		`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    		PRIMARY KEY (`goods_combo_barcode_id`),
    		UNIQUE KEY `goods_code_spec` (`goods_code`,`spec1_code`,`spec2_code`) USING BTREE,
    		UNIQUE KEY `barcode` (`barcode`) USING BTREE,
    		UNIQUE KEY `sku` (`sku`)
    ) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='商品套餐条码';",
    		"INSERT INTO `sys_action` VALUES ('5020600', '5020000', 'url', '商品套餐列表', 'prm/goods_combo/do_list', '2', '1', '0', '1','0');",
    		"INSERT INTO `sys_action` VALUES ('5020601', '5020600', 'act', '添加商品套餐', 'prm/goods_combo/detail&action=do_add', '1', '1', '0', '1','0');",
    		"INSERT INTO `sys_action` VALUES ('5020602', '5020600', 'act', '编辑', 'prm/goods_combo/detail&app_scene=do_edit', '2', '1', '0', '1','0');",
    		"INSERT INTO `sys_action` VALUES ('5020603', '5020600', 'act', '启用/停用', 'prm/goods_combo/update_active', '3', '1', '0', '1','0');",
    		"INSERT INTO `sys_action` VALUES ('5020604', '5020600', 'act', '库存查看', 'prm/goods_combo/goods_inv', '3', '1', '0', '1','0');",
    );
    $u['FSF-1461'] = array(
    		"ALTER TABLE `goods_sku`
    		DROP COLUMN `weight`  ;",
    		"ALTER TABLE `goods_sku`
    		ADD COLUMN `weight` DECIMAL(10,3) DEFAULT '0.000' COMMENT '重量';",
    );
            $u['FSF-1452'] = array(
            "insert IGNORE into `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `remark`, `memo`)
values('oms_taobao','0','淘宝','group','淘宝平台参数','','','');",
            "update sys_params set parent_code='oms_taobao' where param_code='tmall_return' or param_code='order_link'",
            "INSERT IGNORE INTO `sys_action` VALUES ('1010801', '1010800', 'act', '转单自动分仓拆单', 'sys_params_tran_order_auto_split', '1', '1', '0', '1','0');",
        );


?>



