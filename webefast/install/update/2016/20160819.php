<?php

$u = array();
$u['523'] = array(
    //退货包裹单表完善，接口创建无名包裹单新增init_code字段，完善其他列备注
    "ALTER TABLE oms_return_package ADD COLUMN `init_code` varchar(50) NOT NULL DEFAULT '' COMMENT '原单号' AFTER return_type;",
    "ALTER TABLE oms_return_package MODIFY COLUMN `sell_return_code` varchar(20) NOT NULL DEFAULT '' COMMENT '关联退单号';",
    "ALTER TABLE oms_return_package MODIFY COLUMN `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '退货店铺代码';"
);
$u['528'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,	`ui_entrance`) VALUES ('21003000','21000000','group','客服统计','cs-reports','5','1','0','1','0');",
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,`ui_entrance`) VALUES ('21003010','21003000','url','客服绩效统计','rpt/custom_service/do_list','1','1','0','1','0');"
);
$u['500'] = array(
    //门店菜单ui_entrance置为0
    "UPDATE sys_action SET ui_entrance=0 WHERE action_id in('1011000','2050200','2050300');",
);
$u['535'] = array(
    "ALTER TABLE oms_sell_return ADD COLUMN change_store_code varchar(20) NOT NULL DEFAULT '' COMMENT '换货仓库代码';",
);

$u['503'] = array(
    "CREATE TABLE fx_purchaser_return_record
(
	fx_purchaser_return_id INT unsigned PRIMARY KEY AUTO_INCREMENT,
	return_record_code varchar(64) NOT NULL DEFAULT '' COMMENT '单据编号',
	init_code varchar(64) NOT NULL DEFAULT '' COMMENT '原单号',
	custom_code varchar(128) NOT NULL DEFAULT '' COMMENT '分销商代码',
	store_code varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
	is_check tinyint(3) NOT NULL DEFAULT '0' COMMENT '0未确认  1确认',
  record_time date NOT NULL DEFAULT '0000-00-00' COMMENT '业务时间',
  order_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下单日期',
  num int(11) NOT NULL DEFAULT '0' COMMENT '计划退货总数',
	express_money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  finish_num int(11) NOT NULL DEFAULT '0' COMMENT '实际入库总数',
  sum_money decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '总金额',
  is_add_person varchar(64) NOT NULL DEFAULT '' COMMENT '添加人',
  is_settlement tinyint(3) NOT NULL DEFAULT '0' COMMENT '分销结算，1:已结算，0:未结算',
  is_store_in tinyint(3) NOT NULL DEFAULT '0' COMMENT '入库，1:已入库，0:未入库',
  is_store_in_time datetime DEFAULT '0000-00-00 00:00:00' COMMENT '入库日期',
	remark varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  UNIQUE KEY `return_record_code` (`return_record_code`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经销采购退货单';",
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,`ui_entrance`) VALUES ('8090200','8090000','url','经销采购退货单','fx/purchase_return_record/do_list','1','1','0','1','0');
",
    "CREATE TABLE fx_purchaser_return_record_detail
(
	return_record_detail_id int(11) unsigned PRIMARY KEY AUTO_INCREMENT,
	pid int(11) DEFAULT '0',
	record_code varchar(128) NOT NULL DEFAULT '' COMMENT '单据编号',
	goods_code varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
  goods_name varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
	spec1_code varchar(64) NOT NULL DEFAULT '' COMMENT '颜色代码',
  spec2_code varchar(64) NOT NULL DEFAULT '' COMMENT '尺码代码',
  barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条形码',
  sku varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
  price decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '采购单价',
  money decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '金额',
  finish_num int(11) NOT NULL DEFAULT '0' COMMENT '实际退货数',
  num int(11) NOT NULL DEFAULT '0' COMMENT '计划退货数',
  goods_property int(4) NOT NULL DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  cost_price decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '成本单',
  remark varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  lastchanged timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经销采购退货单明细';",
    "ALTER TABLE wbm_return_notice_record ADD COLUMN jx_return_code varchar(128) NOT NULL DEFAULT '' COMMENT '经销采购退货单编号';",
    "ALTER TABLE fx_settlement MODIFY record_type varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款;purchase_settlement: 采购结算;purchase_refund：采购退款';",
    "UPDATE sys_action SET `sort_order`='2' WHERE (`action_id`='8090200');"
);

$u['433'] = array(
    "ALTER TABLE `wms_b2b_trade`
MODIFY COLUMN `json_data`  longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '订单/退单 主信息数据' AFTER `buyer_nick`;
",
);

$u['517'] = array(
    " ALTER TABLE `api_order`
ADD COLUMN `order_first_insert_time_int`  int(11) NULL AFTER `order_first_insert_time`;",
    "update api_order SET order_first_insert_time_int= UNIX_TIMESTAMP(order_first_insert_time)",
    "ALTER TABLE `op_gift_strategy_detail`
ADD INDEX `strategy_code` (`strategy_code`, `status`) USING BTREE ;
",
    "ALTER TABLE `op_gift_strategy`
ADD INDEX `_index1` (`start_time`, `end_time`, `status`, `time_type`) USING BTREE ;
",
);
$u['540'] = array(
    "CREATE TABLE `oms_sell_record_process` (
      `process_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `sell_record_code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT '订单号',
      `process_status` VARCHAR(50) NOT NULL COMMENT '单据状态',
      `operate_time` INT(11) NOT NULL DEFAULT '0' COMMENT '操作时间',
      `remark` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '备注',
      `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      PRIMARY KEY (`process_id`),
      KEY `ix_code` (`sell_record_code`),
            KEY `ix_operate_time` (`operate_time`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单流水通知';",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('order_store_process', 'app', '查询订单在仓库中的状态（仅限开放API）', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '条件：WMS需要将订单在仓库中的状态通过API推送到系统，订单详情页显示按钮‘WMS状态查询’');",
);
$u['541'] = array(
    //门店导入商品权限
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050105', '30050100', 'url', '导入商品', 'prm/shop_goods/import_data', '5', '1', '0', '1', '0');",
);
