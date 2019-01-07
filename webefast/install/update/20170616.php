<?php

$u['1384'] = array(
    "UPDATE `sys_action` SET `parent_id`='2070000' WHERE `action_id`='8010400';",
    "UPDATE `sys_action` SET `parent_id`='2070000' WHERE `action_id`='8010600';",
    "UPDATE `sys_action` SET `status`='0' WHERE (`action_id`='8010000');"
);

$u['1365'] = array(
    "CREATE TABLE `api_youhuo_deliver` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_no` varchar(128) NOT NULL DEFAULT '' COMMENT '出库单号',
  `purchase_no` varchar(128) NOT NULL DEFAULT '' COMMENT '采购单号',
  `notice_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '批发通知单号',
  `shop_code` varchar(100) DEFAULT NULL COMMENT '店铺code',
  `numbers` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
  `express_no` varchar(50) NOT NULL DEFAULT '' COMMENT '物流包裹号',
  `express_code` varchar(50) NOT NULL DEFAULT '' COMMENT '物流公司code,对应有货的id',
  `is_delivery` int(1) NOT NULL DEFAULT '0' COMMENT '是否确认出库(0:未出库；1:已出库)',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliver_no` (`delivery_no`) USING BTREE,
  KEY `purchase_no` (`purchase_no`) USING BTREE,
  KEY `store_out_record_code` (`notice_record_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COMMENT='有货出库单表';",
    "CREATE TABLE `api_youhuo_deliver_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `delivery_no` varchar(128) NOT NULL DEFAULT '' COMMENT '出库单号',
  `store_out_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '批发销货单号',
  `factory_code` varchar(150) NOT NULL COMMENT '厂家编号(条码)',
  `sku` varchar(150) DEFAULT NULL COMMENT '系统sku',
  `numbers` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `deliver_no_barcode` (`delivery_no`,`store_out_record_code`,`factory_code`) USING BTREE,
  KEY `deliver_no` (`delivery_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='有货出库单明细表';",
    "CREATE TABLE `api_youhuo_purchase_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL COMMENT '采购单号',
  `amount` decimal(20,2) DEFAULT '0.00' COMMENT '总价，精确到小数点后2位',
  `numbers` int(10) NOT NULL DEFAULT '0' COMMENT '采购总数量',
  `deliver_num` int(10) NOT NULL DEFAULT '0' COMMENT '已发货总数量',
  `phone` varchar(20) NOT NULL DEFAULT '' COMMENT '手机号',
  `recipients` varchar(20) NOT NULL DEFAULT '' COMMENT '收件人',
  `address` varchar(100) NOT NULL DEFAULT '' COMMENT '地址(包含省市区)',
  `create_time` int(10) DEFAULT NULL COMMENT '采购单创建时间，UNIX时间戳',
  `brand_id` int(11) NOT NULL COMMENT '品牌ID',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `is_execute` tinyint(1) DEFAULT '0' COMMENT '是否生成销货单',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `notice_num` int(10) DEFAULT '0' COMMENT '通知数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_no` (`purchase_no`) USING BTREE,
  KEY `purchase` (`purchase_no`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='有货采购单表';",
    "CREATE TABLE `api_youhuo_purchase_record_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL COMMENT '采购单号',
  `factory_code` varchar(150) NOT NULL COMMENT '厂家编号(条码)',
  `sku` varchar(150) NOT NULL COMMENT '系统sku',
  `numbers` int(10) NOT NULL DEFAULT '0' COMMENT '采购数量',
  `deliver_num` int(10) NOT NULL DEFAULT '0' COMMENT '已发货数量',
  `create_time` int(10) DEFAULT NULL COMMENT '采购单商品创建时间，UNIX时间戳',
  `purchase_price` decimal(20,2) DEFAULT '0.00' COMMENT '价格，精确到小数点后2位（成交价格）',
  `notice_num` int(10) DEFAULT '0' COMMENT '通知数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_no_barcode` (`purchase_no`,`factory_code`) USING BTREE,
  KEY `purchase_no` (`purchase_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='有货采购单明细表';",
    "CREATE TABLE `api_youhuo_store_out_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL DEFAULT '' COMMENT '采购单号',
  `notice_record_code` varchar(128) NOT NULL DEFAULT '' COMMENT '批发通知单号',
  `store_out_record_code` varchar(128) DEFAULT '' COMMENT '批发销货单号',
  `delivery_no` varchar(128) NOT NULL DEFAULT '' COMMENT '出库单号',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `express_no` varchar(50) NOT NULL DEFAULT '' COMMENT '物流包裹号',
  `express_code` varchar(50) NOT NULL DEFAULT '' COMMENT '物流公司code,对应有货的id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_1` (`notice_record_code`,`purchase_no`) USING BTREE,
  KEY `purchase_no` (`purchase_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='有货采购单，批发通知单，出库单关联表';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6050000', '6000000', 'group', '有货JIT', 'yoho_purchase_manage', '22', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6050100', '6050000', 'url', '有货采购单管理', 'api/api_yoho_purchase/do_list', '1', '1', '0', '1', '0');",
    "INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`, `remark`, `lastchanged`) VALUES ('yoho', 'yoho', '有货', '1', '1', '', '2017-03-10 13:28:50');",
    "ALTER TABLE api_youhuo_deliver add COLUMN `insert_time` datetime DEFAULT NULL COMMENT '插入时间'",
    "ALTER TABLE api_youhuo_deliver add COLUMN `delivery_time` datetime DEFAULT NULL COMMENT '确认出库时间'",
    "ALTER TABLE api_youhuo_deliver add COLUMN `delivery_log` text DEFAULT NULL COMMENT '回写日志'",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6050200', '6050000', 'url', '有货采购单回写', 'api/api_yoho_delivery/do_list', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6050300', '6050000', 'url', '有货采购退单管理', 'api/api_yoho_return/do_list', '3', '1', '0', '1', '0');",


    //采购退
    "CREATE TABLE `api_youhuo_return` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL COMMENT '采购单号',
  `is_execute` tinyint(1) DEFAULT '0' COMMENT '是否生成退货单',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `numbers` int(10) DEFAULT '0' COMMENT '总退货商品数',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `return_notice_code` varchar(50) DEFAULT NULL COMMENT '退货通知单号',
  `money` decimal(20,2) DEFAULT '0.00' COMMENT '总价，精确到小数点后2位',
  `store_in_num` int(10) DEFAULT '0' COMMENT '总入库数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_no` (`purchase_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='有货采购退单表';",
    "CREATE TABLE `api_youhuo_return_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL COMMENT '采购单号',
  `factory_code` varchar(150) NOT NULL COMMENT '厂家编号(条码)',
  `sku` varchar(150) DEFAULT NULL COMMENT '系统sku',
  `numbers` int(10) NOT NULL DEFAULT '0' COMMENT '采购数量',
  `create_time` int(10) DEFAULT NULL COMMENT '采购单商品创建时间，UNIX时间戳',
  `purchase_price` decimal(20,2) DEFAULT '0.00' COMMENT '价格，精确到小数点后2位（成交价格）',
  `notice_num` int(10) DEFAULT '0' COMMENT '通知数',
  `store_in_num` int(10) DEFAULT '0' COMMENT '入库数',
  PRIMARY KEY (`id`),
  UNIQUE KEY `purchase_no_barcode` (`purchase_no`,`factory_code`) USING BTREE,
  KEY `purchase_no` (`purchase_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COMMENT='有货采购退单明细表';",
    "CREATE TABLE `api_youhuo_return_relation_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `purchase_no` varchar(128) NOT NULL COMMENT '采购单号',
  `return_notice_code` varchar(128) NOT NULL COMMENT '批发退货通知单号',
  `return_code` varchar(128) NOT NULL DEFAULT '' COMMENT '批发退货单号',
  `insert_time` datetime DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_record_no` (`return_notice_code`,`purchase_no`) USING BTREE,
  KEY `purchase_no` (`purchase_no`) USING BTREE,
  KEY `return_notice_code` (`return_notice_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='有货采购退单，批发退货通知单关联表';",
);

$u['1369'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('2050105', '2050100', 'act', '删除', 'base/shop/do_delete', '5', '1', '0', '1', '0');"
);
$u['1353'] = array(
    "ALTER TABLE `crm_goods`
CHANGE COLUMN `sync_ratio`  `sync_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '同步比例',
ADD COLUMN `lock_num`  int(10) NOT NULL DEFAULT '0' ,
ADD COLUMN `sell_num` int(11) DEFAULT '0' COMMENT '销售数量',
ADD COLUMN  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
ADD COLUMN  `goods_from_id` varchar(64) DEFAULT '' COMMENT '商品ID, 淘宝平台：num_iid';",

    "ALTER TABLE `crm_goods_children`
ADD COLUMN `lock_num` int(10) DEFAULT '0',
ADD COLUMN  `p_sync_num` int(10) DEFAULT '0' COMMENT '套餐锁定库存数',
ADD COLUMN  `is_sync` tinyint(4) DEFAULT '1' COMMENT '套餐子商品是否需要同步库存',
ADD  UNIQUE KEY `_key` (`activity_code`,`shop_code`,`sku`,`p_sku`);",

);
$u['1386'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`) VALUES('siku', 'siku', '寺库网', '1', '1');");

$u['1379'] = array(
    "ALTER TABLE api_weipinhuijit_warehouse ADD COLUMN `custom_code` varchar(128) DEFAULT '' COMMENT '绑定的分销商代码' AFTER `warehouse_name`;",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040101', '8040100', 'act', '获取拣货单', 'api/api_weipinhuijit_po/get_pick', '1', '1', '0', '1', '0');"
);

$u['bug_1290']=array(
    "update sys_action set `status`=0 WHERE action_id='5050000';",
);


$u['bug_1372'] = array(
    "delete from sys_action WHERE action_id='22000000';",
);

$u['bug_1391']=array(
    "update sys_action SET  parent_id='12020000',`action_name`='O2O单据上传',sort_order=4,`status`=0 WHERE action_id='12040100';",
    "update sys_action SET  `action_name`='百胜BSERP' WHERE action_id='12020000';",
    "DELETE from sys_action WHERE action_id='12040000';",
    "update sys_action SET  `action_name`='BSERP单据同步' WHERE action_id='12020100';",
    "update sys_action SET  `action_name`='BSERP批发单据' WHERE action_id='12020200';",
    "update sys_action SET  `action_name`='BSERP商品库存' WHERE action_id='12020300';",
);
$u['bug_1381'] = array(
    "ALTER TABLE api_weipinhuijit_delivery_detail DROP INDEX `pick_po_sku`;",
    "ALTER TABLE api_weipinhuijit_delivery_detail ADD UNIQUE KEY `pick_po_sku` (`barcode`,`record_code`,`pick_no`,`po_no`,`delivery_id`) USING BTREE",
);


$u['bug_1365'] = array("DELETE FROM base_express WHERE express_code='SFC'",
                        "DROP TABLE IF EXISTS api_country_data",
                        "CREATE TABLE `api_country_data` (
                            `id` INT (11) NOT NULL AUTO_INCREMENT,
                            `tid` VARCHAR (255) DEFAULT NULL COMMENT '平台交易号',
                            `sale_channel_code` VARCHAR (255) DEFAULT NULL COMMENT '平台代码',
                            `api_country` VARCHAR (255) DEFAULT NULL COMMENT '平台上国家',
                            PRIMARY KEY (`id`),
                            UNIQUE KEY `tid` (`tid`, `sale_channel_code`) USING BTREE
                        ) ENGINE = INNODB DEFAULT CHARSET = utf8;");

$u['bug_1401'] = array(
    "UPDATE sys_action SET action_name='删除明细' WHERE action_id=4010110;",
    "UPDATE sys_action SET action_name='交易下载' WHERE action_id=4010105;"
);

$u['1464'] = array("INSERT INTO `sys_params` (`param_code`,	`parent_code`,	`param_name`,	`type`,	`form_desc`,	`value`, `remark`, `memo`) 
                    VALUES('update_express_money_to_new_erp','erp','订单/退单运费上传ERP ','radio','[\"关闭\",\"开启\"]','1','1-开启 0-关闭',
                    '仅限新接口模式，默认开启，即运费会上传给ERP，ERP可以新增虚拟商品来显示运费金额</br>
                    开启参数：</br>
                    销售订单：订单运费</br>
                    销售退单：退单运费+赔付金额+手工调整金额</br>
                    关闭参数:</br>
                    销售订单：0</br>
                    销售退单：赔付金额+手工调整金额</br>');");

$u['1465'] = array(
    "UPDATE sys_schedule SET loop_time=60 WHERE code='weipinhuijit_getOccupiedOrders_cmd';"
);

$u['1423']=array(
    "ALTER TABLE api_youhuo_deliver ADD COLUMN `brand_id` int(11) COMMENT '品牌ID' AFTER shop_code",
    "ALTER TABLE api_youhuo_return ADD COLUMN `brand_id` int(11) COMMENT '品牌ID' AFTER shop_code",
);