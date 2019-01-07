<?php
$u = array();
$u['77'] = array(
    "UPDATE base_question_label SET remark = '生成换货单导致订单已付款 小于 应付款，需再付款。系统将自动设为问题单' WHERE question_label_code = 'CHANGE_GOODS_MAKEUP'"
);

$u['80'] = array(
    "ALTER TABLE oms_sell_return add COLUMN `finsih_status` tinyint(1) DEFAULT '0' COMMENT '完成状态';",
    "INSERT INTO `sys_action` VALUES ('4030114', '4030100', 'act', '完成', 'oms/return_opt/opt_finish', '1', '1', '0', '1','0');",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('return_auto_finish', 'oms_property', '售后服务单自动完成', 'radio', '[\"关闭\",\"开启\"]', '1', '11', '1-开启 0-关闭', '2016-03-08 18:38:38', '开启后，退款单，确认退款后自动完成；退货退款单，收货完成和退款完成后，自动完成');"
);
$u['bug_114'] = array(
    "DELETE FROM sys_action WHERE action_id = 1010300;",
);

$u['100'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`) 
        VALUES	('3020703','3020700','act','启用/停用','op/op_gift_strategy/check_repeat','3','1');",
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`) 
        VALUES	('3020704','3020700','url','审核','op/op_gift_strategy/do_check','5','1');"
);

$u['bug_122'] = array(
    "ALTER TABLE `sys_auth`
ADD UNIQUE INDEX `_key` (`code`) USING BTREE ;",
    
);

$u['bug_123'] = array(
    "ALTER TABLE `oms_sell_return` ADD INDEX return_order_status (`return_order_status`);",
    "ALTER TABLE `oms_sell_return` ADD INDEX return_shipping_status (`return_shipping_status`);",
    "ALTER TABLE `oms_sell_return` ADD INDEX finance_check_status (`finance_check_status`);",
    "ALTER TABLE `oms_sell_return` ADD INDEX receive_time (`receive_time`);",
    "ALTER TABLE `oms_sell_return` ADD INDEX agreed_refund_time (`agreed_refund_time`);",
    "ALTER TABLE `api_refund` ADD INDEX order_first_insert_time (`order_first_insert_time`);",
    "ALTER TABLE `api_refund` ADD INDEX is_change (`is_change`);",
);

$u['074'] = array(
    "CREATE TABLE `op_express_by_goods` (
      `op_express_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `sku` varchar(30) NOT NULL COMMENT 'sku',
      `express_code` varchar(128) NOT NULL COMMENT '快递代码',
      `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      PRIMARY KEY (`op_express_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略-指定商品匹配指定快递';",
);
$u['bug_023'] = array(
    "UPDATE sys_schedule SET `name`='上传wms档案' WHERE id=15;"
);
$u['110'] = array(
    "ALTER TABLE api_taobao_sku ADD COLUMN `spec2_name` varchar(255) DEFAULT '' COMMENT '规格2';",
    "ALTER TABLE api_taobao_sku ADD COLUMN `spec1_name` varchar(255) DEFAULT '' COMMENT '规格1';",
    "ALTER TABLE api_goods_sku DROP COLUMN spec1_name;",
    "ALTER TABLE api_goods_sku DROP COLUMN spec2_name;",
    

);
$u['bug:111'] = array(
    "alter table api_goods_sku add key is_allow_sync_inv_new(is_allow_sync_inv,inv_num,shop_code);",
);


$u['bug_127'] = array(
    "DELETE FROM `sys_action` WHERE action_id=10000000;",
    "DELETE FROM `sys_action` WHERE action_name='订单转移';",
);
