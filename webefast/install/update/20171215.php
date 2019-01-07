<?php
$u['1910'] = array(
    "UPDATE `sys_role` SET `role_desc` = '该角色为系统内置，不可删除，拥有订单查询/售后服务单导出明文权限' WHERE `role_code` = 'security';",
);
$u['1922'] = array(
    "ALTER TABLE `wms_config`
    ADD COLUMN `wms_system_type`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT 'wms系统类型' AFTER `wms_system`;",
);

$u['bug_1964']=array(
    'UPDATE sys_params SET parent_code=\'oms_common\',memo=\'	开启后，配合网络订单模块，平台商品列表中商品是否允许上架使用。商品允许上架，即同步库存成功后上架商品；商品不允许上架，即仅同步库存。(目前仅支持淘宝,小红书)\' WHERE param_code=\'update_goods_listing\';'
);

$u['1932'] = array(
    'ALTER TABLE `op_presell_plan`  ADD COLUMN `exit_status`  int(2) NOT NULL DEFAULT \'0\' COMMENT \'终止状态 0:未终止 1:已终止\'',
    'INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES (\'3050105\', \'3050100\', \'act\', \'立即终止\', \'op/presell/plan_exit_check\', \'4\', \'1\', \'0\', \'1\', \'0\');'
);

$u['1930'] = array(
    "ALTER TABLE wms_config ADD `wms_prefix` varchar(10) DEFAULT '' COMMENT 'wms单号前缀';"
);

$u['1973'] = array(
    "ALTER TABLE oms_sell_record ADD  `have_order_tag` TINYINT(3) DEFAULT 0 COMMENT '是否含有订单标签';",
    "update oms_sell_record set have_order_tag=1 where sell_record_code in (SELECT sell_record_code FROM oms_sell_record_tag where tag_type='order_tag' and tag_v in(select order_label_code from base_order_label) GROUP BY sell_record_code ) AND order_status=1 AND  shipping_status=1;"
);