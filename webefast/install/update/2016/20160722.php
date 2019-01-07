<?php

$u = array();

$u['458'] = array(
                "UPDATE `sys_schedule` SET `type` = '0',`loop_time`='300',`desc`='此服务默认约5分钟执行一次',`status` = (SELECT `value` FROM `sys_params` WHERE `param_code`='oms_notice') WHERE `code` = 'auto_notice';",
            );

$u['456'] = array("ALTER TABLE api_weipinhuijit_delivery_detail ADD goods_delivery_status TINYINT (1) DEFAULT 0 COMMENT '0-未导入 1-已导入 商品导入状态';");

$u['419'] = array("UPDATE `sys_action` SET `action_code` = 'oms/sell_record_notice/multi_do_list' WHERE `action_id` = '7010104';");

$u['455'] = array(
    "ALTER TABLE b2b_box_record ADD COLUMN box_order varchar(20) COMMENT '箱序号' AFTER record_code;",
);

$u['457']=array("CREATE TABLE `api_weipinhuijit_return_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `barcode` varchar(50) NOT NULL COMMENT '商品条形码',
  `product_name` varchar(50) NOT NULL COMMENT '商品名称',
  `grade` int(10) NOT NULL COMMENT '货品等级',
  `po_no` varchar(50) NOT NULL COMMENT '采购订单号',
  `qty` int(10) NOT NULL COMMENT '实退数量',
  `box_no` varchar(50) NOT NULL COMMENT '箱号',
  PRIMARY KEY (`id`),
  KEY `po_no` (`po_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='唯品会退供单商品明细表';",
    
  "CREATE TABLE `api_weipinhuijit_return` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_sn` varchar(50) NOT NULL COMMENT '退供单号',
  `warehouse` varchar(50) NOT NULL COMMENT '退供发货仓',
  `return_type` int(10) NOT NULL COMMENT '退供类型',
  `pay_type` tinyint(1) DEFAULT '0' COMMENT '支付方式：0，货到付款；1，现付月结；',
  `consignee` varchar(50) NOT NULL COMMENT '退供收货人',
  `country` varchar(50) NOT NULL COMMENT '国家标识',
  `state` varchar(50) NOT NULL COMMENT '省/州',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `region` varchar(50) NOT NULL COMMENT '区/县',
  `town` varchar(50) NOT NULL COMMENT '乡镇/街道',
  `address` varchar(50) NOT NULL COMMENT '收货地址',
  `postcode` int(10) NOT NULL COMMENT '邮政编码',
  `telephone` varchar(50) NOT NULL COMMENT '座机',
  `mobile` varchar(50) NOT NULL COMMENT '移动电话',
  `to_email` varchar(50) NOT NULL COMMENT '退供通知email地址',
  `cc_email` varchar(50) NOT NULL COMMENT '退供抄送email地址',
  `self_reference` tinyint(1) DEFAULT '0' COMMENT '是否自提:0，品骏配送；1，供应商自提；',
  `is_execute` tinyint(1) DEFAULT '0' COMMENT '是否生成退货单',
  PRIMARY KEY (`id`),
  UNIQUE KEY `return_sn` (`return_sn`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='唯品会退供单表';",
    
  " CREATE TABLE `api_weipinhuijit_return_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `notice_record_no` varchar(50) NOT NULL COMMENT '通知单号',
  `return_sn` varchar(50) NOT NULL COMMENT '退供单号',
  `return_record_no` varchar(50) NOT NULL COMMENT '批发退货单号',
  `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`),
  KEY `notice_record_no` (`notice_record_no`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='批发退货单关联表';",

"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `insert_time` datetime DEFAULT NULL COMMENT '创建时间';",
"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `shop_code` varchar(50) NOT NULL COMMENT '店铺code';",
"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `box_num` int(10) DEFAULT NULL COMMENT '总箱数';",
"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `num` int(10) DEFAULT NULL COMMENT '总商品数';",
"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `return_notice_code` varchar(50) NOT NULL COMMENT '退货通知单号';",
"ALTER TABLE `api_weipinhuijit_return` ADD COLUMN `return_notice_num` int(10) DEFAULT '0' COMMENT '退货通知数';",

  
"ALTER TABLE `api_weipinhuijit_return_detail` ADD  COLUMN `return_sn` varchar(50) NOT NULL COMMENT '退供单号';",
"ALTER TABLE `api_weipinhuijit_return_detail` ADD  COLUMN `goods_code` varchar(50) NOT NULL COMMENT '商品编码';",
    
"ALTER TABLE `api_weipinhuijit_return` DROP COLUMN `is_delivery`;",
"ALTER TABLE `api_weipinhuijit_return` DROP COLUMN `delivery_no` ;",
"ALTER TABLE `api_weipinhuijit_return` DROP COLUMN `store_out_record_no`;",
"ALTER TABLE `api_weipinhuijit_return` DROP COLUMN `express`;",
"ALTER TABLE `api_weipinhuijit_return` DROP COLUMN `storage_no`;",

"ALTER TABLE `api_weipinhuijit_return_record` DROP COLUMN `delivery_no` ;",
"ALTER TABLE `api_weipinhuijit_return_record` DROP COLUMN `warehouse`;",
"ALTER TABLE `api_weipinhuijit_return_record` DROP COLUMN `delivery_id`;",
"ALTER TABLE `api_weipinhuijit_return_record` DROP COLUMN `storage_no`;",

"INSERT INTO `sys_action` VALUES ('8040500','8040000','url','唯品会退货管理','api/api_weipinhuijit_return/do_list','5','1','0','1','0');",
 );

$u['450'] = array(
    "ALTER TABLE goods_unique_code_log ADD COLUMN `shop_code` varchar(128) DEFAULT '' COMMENT '商店代码' AFTER sku;",
    "UPDATE goods_unique_code_log uc SET shop_code = (SELECT shop_code FROM oms_sell_record sr WHERE sr.sell_record_code = uc.record_code) WHERE record_type = 'sell_record';",
    "UPDATE goods_unique_code_log uc SET shop_code = (SELECT shop_code FROM oms_sell_return sn WHERE sn.sell_return_code = uc.record_code) WHERE record_type = 'sell_return';",
    "DELETE FROM sys_user_pref WHERE iid = 'goods_unique_code_do_log_list/table';",
);
$u['bug_356'] = array("update wbm_return_record_detail set money = price*rebate*num where money=0 AND num>0;",
                      "UPDATE wbm_return_record
                        SET money = (
                            SELECT
                                sum(money)
                            FROM
                                wbm_return_record_detail
                            WHERE
                                wbm_return_record_detail.record_code = wbm_return_record.record_code
                        )
                        WHERE
                            wbm_return_record.money = 0
                        AND is_sure = 1;");

$u['447'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('kdniao_enable', 'app', '快递鸟', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '2015-06-12 15:07:13', '开启后，可查询已发货快递单号物流跟踪信息');",
);

$u['445'] = array(
    "UPDATE `sys_params` SET memo='开启后，请至运营->策略管理->库存同步策略中维护同步店铺以及同步比例，否则不会进行同步。' WHERE param_code='inv_sync';",
);

$u['444'] = array(
    "CREATE TABLE `fx_purchaser_record` (
  `purchaser_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `init_code` varchar(128) DEFAULT '' COMMENT '原单号',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `is_check` int(4) DEFAULT '0' COMMENT '0未确认  1确认',
  `record_time` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `order_time` datetime DEFAULT NULL COMMENT '下单日期',
  `num` int(11) DEFAULT '0' COMMENT '计划采购数',
  `finish_num` int(11) DEFAULT '0' COMMENT '实际入库数',
  `sum_money` decimal(20,3) DEFAULT '0.000' COMMENT '总金额',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
  `express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  `is_settlement` tinyint(3) DEFAULT '0' COMMENT '分销结算，1:已结算，0:未结算',
  `is_deliver` tinyint(3) DEFAULT '0' COMMENT '出库，1:已出库，0:未出库',
  `deliver_time` datetime DEFAULT NULL COMMENT '出库日期',
  `country` bigint(20) DEFAULT NULL,
  `province` bigint(20) DEFAULT NULL,
  `city` bigint(20) DEFAULT NULL,
  `district` bigint(20) DEFAULT NULL,
  `street` bigint(20) DEFAULT NULL,
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '地址(不包含省市区)',
  `contact_person` varchar(128) DEFAULT '' COMMENT '联系人',
  `mobile` varchar(128) DEFAULT '' COMMENT '手机',
  `relation_code` varchar(128) DEFAULT '' COMMENT '关联单号',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',                  
  PRIMARY KEY (`purchaser_record_id`),
  UNIQUE KEY `_key` (`record_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='经销采购订单'",
    "CREATE TABLE `fx_purchaser_record_detail` (
  `purchaser_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0',
  `record_code` varchar(128) DEFAULT NULL COMMENT '单据编号',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '采购单价',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `finish_num` int(11) DEFAULT '0' COMMENT '实际出库数',
  `num` int(11) DEFAULT '0' COMMENT '计划采购数',
  `goods_property` int(4) DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本单',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`purchaser_record_detail_id`),
  UNIQUE KEY `record_sku` (`record_code`,`sku`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品采购入库单明细表';",
    "alter table wbm_notice_record add COLUMN `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号';",
    "alter table wbm_notice_record add COLUMN `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE';"
);
$u['362'] = array(
    "update sys_action set parent_id = '6000000', sort_order = 5 where action_code = 'wbm_manage';",
    "update sys_action set parent_id = '6000000', sort_order = 7 where action_code = 'with-xiang-odm';",
    "update sys_action set sort_order = 10 where action_code = 'stm_manage';",
    "update sys_action set sort_order = 20 where action_code = 'stm_search';",
    "update sys_action set sort_order = 2 where action_code = 'base-fenxiao';",
    "update sys_action set action_name ='淘宝分销' where action_code = 'platform-fenxiao';",
    "update sys_action set sort_order = 10 where action_code = 'platform-weipinhuijit';",
    "alter table wbm_notice_record add COLUMN `jx_code` varchar(128) DEFAULT NULL COMMENT '经销采购订单编号';",
    "alter table wbm_store_out_record add COLUMN `jx_code` varchar(128) DEFAULT NULL COMMENT '经销采购订单编号';",
    "ALTER table base_store add COLUMN is_enable_cusom tinyint(3) DEFAULT '0' COMMENT '启用分销,0:未启用;1:启用';"
);
$u['452'] = array(
    " ALTER TABLE api_taobao_trade MODIFY COLUMN promotion_details text DEFAULT '' COMMENT '优惠信息';"
);