<?php

$u = array();
$u['1939'] = array(
    "DELETE FROM sys_params WHERE `param_code` in('cz_com_name','cz_baud_rate');"
);
$u['1953'] = array(
    "CREATE TABLE `base_area_compare` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `ident_code` varchar(128) NULL COMMENT '数据代码',
        `ident_type` TINYINT(1) NULL COMMENT '数据类型 1-销售平台 2-配送方式',
        `area_id` varchar(150) DEFAULT '' COMMENT '系统地址id',
        `out_area_id` VARCHAR(150) DEFAULT '' COMMENT '外部地址id',
        `out_area_name` VARCHAR(150) DEFAULT '' COMMENT '外部地址名称',
        PRIMARY KEY (`id`),
        UNIQUE KEY `sale_channel_code` (`ident_code`,`ident_type`,`area_id`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='外部地址对照';",
    "INSERT INTO base_area_compare VALUES (NULL,'SF',2,'542600000000','','林芝地区');"
);

$u['bug_2013'] = array(
    //修改订单明细商品编码字段长度与商品表一致
    "ALTER TABLE oms_sell_record_detail MODIFY goods_code VARCHAR(64) DEFAULT '' NOT NULL COMMENT '商品代码';"
);

//12.20

$u['1898'] = array(
    //添加商品税务编码表
    "CREATE TABLE `goods_tax` (
  `tax_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `barcode` varchar(128) DEFAULT '' COMMENT '条形码码',
  `sku` varchar(64) DEFAULT NULL,
  `tax_code` varchar(128) DEFAULT '' COMMENT '税务编码',
  `use_num` tinyint(3) DEFAULT '0' COMMENT '0未使用，1正在使用',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`tax_id`),
  UNIQUE KEY `barcode` (`barcode`) USING BTREE,
  UNIQUE KEY `sku` (`sku`) USING BTREE,
  KEY `goods_code` (`goods_code`) USING BTREE,
  KEY `tax_code` (`tax_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品条形码和税务编码关联表';
",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020300', '17000000', 'group', '商品税务管理', 'unique_tax_tl_manage', '78', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020400', '91020300', 'url', '商品税务编码维护', 'prm/goods_tax_tl/do_list', '0', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020401', '91020400', 'act', '按商品条码导入', 'prm/goods_tax_tl/export_by_barcode', '0', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020402', '91020400', 'act', '按商品编码导入', 'prm/goods_tax_tl/export_by_goods_code', '1', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020403', '91020400', 'act', '编辑', 'prm/goods_tax_tl/detail&app_scene=edit', '2', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020404', '91020400', 'act', '删除', 'prm/goods_tax_tl/do_delete', '3', '1', '0', '1', '1');",
);

//修改字段长度
$u['1915 '] = array(
    "ALTER TABLE api_order MODIFY invoice_type VARCHAR(128) DEFAULT '' COMMENT '平台发票类型';",
);

$u['bug_2089 '] = array(
    "ALTER TABLE wms_oms_trade MODIFY new_record_code varchar(30) default '' not null comment '新单号';",
    "ALTER TABLE wms_b2b_trade MODIFY new_record_code varchar(30) default '' not null comment '新单号';",
);

