<?php

$u = array();

$u['229'] = array(
    "INSERT INTO `sys_params` VALUES ('240', '', 'wms_is_get_jd_cod_express', 'app', 'S008_007 上传物流单号至WMS（JD货到付款）', 'radio', '[\"关闭\",\"开启\"]', '0', '10.00', '1-开启 0-关闭', '2016-02-29 15:50:31', '前提：有对接WMS且WMS要求JD货到付款订单需要上传物流单号(暂只支持奇门WMS)');",
);

$u['236'] = array(
    "DELETE FROM `sys_user_pref` WHERE iid='oms/sell_record_td_list';",

);
$u['262'] = array(
    "ALTER TABLE `pur_purchaser_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `pur_order_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `pur_planned_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `pur_return_notice_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `pur_return_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `wbm_notice_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `wbm_return_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
    "ALTER TABLE `wbm_store_out_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ;",
);



$u['229'] = array(
"ALTER TABLE `oms_waves_strategy`
DROP INDEX `name` ,
ADD UNIQUE INDEX `name` (`name`, `type`) USING BTREE ;",
);


$u['230'] = array(
    "ALTER TABLE `sys_action` DROP INDEX `action_code`;",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7010111', '7010001', 'url', '已发货订单列表', 'oms/sell_record/shipped_list', '32', '1', '0', '1', '0');"
);
$u['240'] = array(
    "INSERT INTO `base_sale_channel` VALUES ('40', 'shangpin', 'sp', '尚品网', '1', '1', '', '2016-04-21 13:57:24');",
);
$u['184'] = array(
    "ALTER TABLE `oms_sell_record_detail` ADD `cost_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品成本单价' AFTER goods_price;",
);

$u['238'] = array(
    "DELETE FROM `sys_user_pref` where iid = 'sell_return_after_service/table';",
);
$u['233'] = array(
    "DELETE FROM sys_user_pref WHERE iid='oms/sell_record_shipped_list';",
);
