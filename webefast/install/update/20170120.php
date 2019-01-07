<?php

$u = array();

$u['963'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3010300', '3010000', 'url', '会员消费金额分析', 'rpt/custom_consume/do_list', '3', '1', '0', '1', '0');"
);

$u['1001'] = array("INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`,`memo`) VALUES('download_gift','oms_taobao','下载平台赠品并标识赠品','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','开启后，下载平台工具添加的赠品并写入系统标识为赠品，仅支持平台赠品为赠品类目的商品。');");

$u['998'] = array(
    "CREATE TABLE `base_store_staff` (
  `staff_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `staff_code` varchar(128) DEFAULT '' COMMENT '员工代码',
  `staff_name` varchar(128) DEFAULT '' COMMENT '员工名称',
  `staff_type` int(4) DEFAULT '0' COMMENT '员工类型 0拣货员',
  `status` tinyint(1) DEFAULT '1' COMMENT '0：停用 1：启用',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`staff_id`),
  UNIQUE KEY `staff_code` (`staff_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='仓库员工档案表';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21003030', '21003000', 'url', '拣货绩效统计', 'rpt/pick_goods/do_list', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('2020500', '2020000', 'url', '仓库员工档案', 'base/store_staff/do_list', '5', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('2020501', '2020500', 'act', '删除', 'base/store_staff/delete', '1', '1', '0', '1', '0');"
);

$u['986'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12020300', '12020000', 'url', 'BSERP2商品库存', 'erp/bserp/inv_sync_trade_list', '3', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12030300', '12030000', 'url', 'BS3000J批发单据', 'erp/bs3000j/wbm_list', '3', '1', '0', '1', '1');"
);

$u['966'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('clodop_print', 'waves_property', '启用Clodop云打印控件', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '开启前，必须保证系统已安装Clodop云打印控件，否则还是以老控件lodop打印');"
);
$u['952'] = array(
    //唯品会专场商品表增加商品编码字段
    "ALTER TABLE api_wph_sales_sku ADD `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码' AFTER `sku`;",
    //更新权限名称
    "UPDATE `sys_action` SET `action_name`='库存同步(单个/批量)' WHERE `action_id`='8040702';",
    "DROP TABLE IF EXISTS api_wph_sales_sku_relation;",
    "CREATE TABLE `api_wph_sales_sku_relation` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	`sales_no` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '专场ID',
	`barcode` VARCHAR (128) NOT NULL COMMENT '平台商品条形码',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`shop_code`,`sales_no`, `barcode`) USING BTREE,
        KEY `ind_shop_code` (`shop_code`) USING BTREE,
	KEY `ind_sales_no` (`sales_no`) USING BTREE,
	KEY `ind_barcode` (`barcode`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会专场-SKU关系表';",
    "ALTER TABLE api_wph_sales_sku ADD 	`is_allow_sync` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '是否允许同步：0-否;1-是' AFTER `last_sync_time`;"
);
$u['1009'] = array(
    "update sys_params set parent_code='0' where param_code='order_link';"
);
