<?php
$u['1780'] = array(
    "CREATE TABLE `api_weipinhuijit_po_unpick` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `shop_code` varchar(100) NOT NULL COMMENT '店铺code',
  `po_no` varchar(255) NOT NULL COMMENT 'po编号',
  `warehouse_not_pick` int(10) NOT NULL COMMENT '分仓库的未拣货数',
  `supply_num` int(10) NOT NULL COMMENT '补货数',
  `warehouse_code` varchar(255) NOT NULL COMMENT '唯品会仓库编码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_index` (`po_no`,`warehouse_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='po分仓未拣货信息'"
);
$u['1685'] = array(
    "ALTER TABLE `api_order`
ADD COLUMN `taxpayers_code`  varchar(64) NULL DEFAULT '' COMMENT '税号' AFTER `invoice_pay_type`;
",
);
$u['1781'] = array(
    //网络订单 下一步\已发货单导入\订单导入
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020101', '4020100', 'act', '下一步', 'oms/sell_record/next_step', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020102', '4020100', 'act', '已发货单导入', 'oms/sell_record/deliver_record_import', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020103', '4020100', 'act', '订单导入', 'oms/sell_record/sell_record_import', '3', '1', '0', '1', '0');",
    //网络订单 下一步\已发货单导入\订单导入
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070101', '8070100', 'act', '下一步', 'oms/order_opt/fx_next_step', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070102', '8070100', 'act', '分销已发货单导入', 'oms/order_opt/fx_deliver_record_import', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8070103', '8070100', 'act', '分销订单导入', 'oms/order_opt/fx_sell_record_import', '3', '1', '0', '1', '0');",
);

$u['1831'] = array(
    "insert into `sys_action` ( `action_code`, `action_name`, `other_priv_type`, `status`, `ui_entrance`, `parent_id`, `sort_order`, `type`, `appid`, `action_id`) values ( 'oms/sell_record/question_split_order', '批量拆单', '0', '1', '0', '4020400', '0', 'act', '1', '4020405');",
);

//在表次表中添加操作人代码和名称
$u['1787'] = array(
    "ALTER TABLE `oms_waves_record` ADD COLUMN `user_code` varchar(64) DEFAULT '' COMMENT '生成波次操作人代码';",
    "ALTER TABLE `oms_waves_record` ADD COLUMN `user_name` varchar(128) DEFAULT '' COMMENT '生成波次操作人';",
);