<?php
$u = array();
$u['22'] = array(
    "
insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
		values('','postage_auto','app','S008_005 补邮商品自动处理’','radio','[\"关闭\",\"开启\"]','0','10','1-开启 0-关闭','','开启后 订单全是补邮商品订单自动处理设置城已经发货订单');

"
);

$u['023'] = array(
    "ALTER TABLE `api_order` ADD COLUMN `pay_code` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式代码';",
    "INSERT INTO `base_pay_type` VALUES ('4', 'balance', '余额支付', '0', '0', '1', '1', '0', '', '2016-02-23 16:42:42', '0.000');",
    "INSERT INTO `base_pay_type` VALUES ('5', 'chinabank', '网银在线', '0', '0', '1', '1', '0', '', '2016-02-23 16:42:42', '0.000');",
    "INSERT INTO `base_pay_type` VALUES ('6', 'weixinpay', '微信支付', '0', '0', '1', '1', '0', '', '2016-02-23 16:42:42', '0.000');",

 );

$u['28'] = array(
    "ALTER TABLE `oms_sell_record_tag`
ADD COLUMN `desc`  varchar(256) NULL DEFAULT '' COMMENT '标签备注' AFTER `tag_desc`;",
);

$u['bug_034'] = array(
    "alter table wbm_store_out_record add country bigint(20) not null default '0'",
    "alter table wbm_store_out_record add province bigint(20) not null default '0'",
    "alter table wbm_store_out_record add city bigint(20) not null default '0'",
    "alter table wbm_store_out_record add district bigint(20) not null default '0'",
    "alter table wbm_store_out_record add street bigint(20) not null default '0'",
    "alter table oms_return_package modify column return_country bigint(20);",
    "alter table oms_return_package modify column return_province bigint(20);",
    "alter table oms_return_package modify column return_city bigint(20);",
    "alter table oms_return_package modify column return_district bigint(20);",
    "alter table oms_return_package modify column return_street bigint(20);",
    "alter table oms_sell_record modify column receiver_country bigint(20);",
    "alter table oms_sell_record modify column receiver_province bigint(20);",
    "alter table oms_sell_record modify column receiver_city bigint(20);",
    "alter table oms_sell_record modify column receiver_district bigint(20);",
    "alter table oms_sell_record modify column receiver_street bigint(20);",
    "alter table oms_sell_return modify column return_country bigint(20);",
    "alter table oms_sell_return modify column return_province bigint(20);",
    "alter table oms_sell_return modify column return_city bigint(20);",
    "alter table oms_sell_return modify column return_district bigint(20);",
    "alter table oms_sell_return modify column return_street bigint(20);",
   
    "alter table oms_sell_record_notice modify column receiver_country bigint(20);",
    "alter table oms_sell_record_notice modify column receiver_province bigint(20);",
    "alter table oms_sell_record_notice modify column receiver_city bigint(20);",
    "alter table oms_sell_record_notice modify column receiver_district bigint(20);",
    "alter table oms_sell_record_notice modify column receiver_street bigint(20);",
);

$u['bug_050'] = array(
    "alter table oms_sell_record modify column buyer_name varchar(30);",
    "alter table oms_sell_record modify column receiver_name varchar(30);"
);

$u['030'] = array(
    "DROP TABLE IF EXISTS `oms_return_package_action`;",
    "CREATE TABLE `oms_return_package_action` (
    `return_package_action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `return_package_code` varchar(20) NOT NULL DEFAULT '0' COMMENT '关联oms_return_package主键',
    `user_code` varchar(30) NOT NULL COMMENT '员工代码',
    `user_name` varchar(30) NOT NULL COMMENT '员工名称',
    `order_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '包裹单状态 0:未入库;1:已入库',
    `action_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作名称',
    `action_note` mediumtext NOT NULL COMMENT '操作描述',
    `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '日志时间',
    PRIMARY KEY (`return_package_action_id`),
    KEY `return_package_code` (`return_package_code`) USING BTREE
  ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='包裹单操作日志';",
    "INSERT INTO `sys_action` VALUES ('7020000','7000000','group','订单退货拆包','with-delivery-orp','1','1','0','1','0') ON DUPLICATE KEY UPDATE action_code = 'with-delivery-orp';",
    "INSERT INTO `sys_action` VALUES ('7020100','7020000','url','退货包裹单','oms/sell_return/package_list','1','1','0','1','0') ON DUPLICATE KEY UPDATE action_code = 'oms/sell_return/package_list';"
    );
$u['046'] = array(
    "DELETE FROM `sys_user_pref` WHERE iid='sell_record_do_list/table';",
    "DELETE FROM `sys_user_pref` WHERE iid='oms/sell_record_combine_ex_list';"
);
$u['024'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('refund_all_cancel_order', 'app', 'S008_005 发货前订单整单退款，作废订单', 'radio', '[\"关闭\",\"开启\"]', '0', '5', '1-开启 0-关闭', '0000-00-00 00:00:00', '开启后，订单申请整单退，转退单时作废订单');",
);
$u['046'] = array(
    "ALTER TABLE op_gift_strategy DROP COLUMN is_stop_no_inv;",
    "ALTER TABLE op_gift_strategy ADD COLUMN `is_continue_no_inv` tinyint(3) NOT NULL DEFAULT '0' COMMENT '库存不足是否继续送,0否，1是';",
    
);
$u['bug082'] = array(
    "ALTER TABLE oms_sell_settlement MODIFY COLUMN `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号';",
    "ALTER TABLE oms_sell_settlement_detail MODIFY COLUMN `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号';",
    "ALTER TABLE oms_sell_settlement_record MODIFY COLUMN `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号';",
    
);
$u['bug_077'] = array(
    "ALTER TABLE `oms_return_package_detail` ADD COLUMN `apply_num`  int(11) NOT NULL DEFAULT '0' COMMENT '退单申请数量';",

);