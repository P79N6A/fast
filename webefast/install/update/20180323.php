<?php

//更新十一数据统计操作间隔时间
$u['2167'] = array(
    "UPDATE `sys_schedule` SET `loop_time` = '7200' WHERE `code` = 'echart_data' "
);
$u['2147']=array(
    "alter table `oms_sell_record` add column `settlement_time`  datetime not null default '0000-00-00 00:00:00' comment '分销结算时间' after `is_fx_settlement`;",
);

//退货包裹单
$u['2169']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7020102', '7020100', 'act', '确认入库', 'oms/sell_return/return_shipping', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_role_action` (`role_id`,`action_id`) SELECT `role_id`,'7020102' AS `action_id` FROM `sys_role_action` WHERE `action_id` = '7020100'"
);

$u['2171'] = array(
    "ALTER TABLE erp_config ADD COLUMN `erp_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '对接方式: 0:直连, 1:奇门' ",
    "ALTER TABLE erp_config ADD COLUMN `target_key` varchar(128) DEFAULT '' COMMENT '奇门目标的app_key'",
    "ALTER TABLE erp_config ADD COLUMN `customer_id` varchar(128) DEFAULT '' COMMENT '奇门customer_id'",

    "ALTER TABLE mid_api_config ADD COLUMN `erp_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '对接方式: 0:直连, 1:奇门' ",
    "ALTER TABLE mid_api_config ADD COLUMN `target_key` varchar(128) DEFAULT '' COMMENT '奇门目标的app_key'",
    "ALTER TABLE mid_api_config ADD COLUMN `customer_id` varchar(128) DEFAULT '' COMMENT '奇门customer_id'",
);
//给wms_archive表添加索引
$u['2167'] = array(
    "alter table wms_archive add index `ind_is_success`(`is_success`);"
);

$u['bug_2332'] = array(
    "UPDATE `sys_role_manage_price` SET `desc` = '在商品库存查询导出/商品进销存分析/调整单/商品列表进行控制，开启后，此角色对应用户可看到商品的成本价，其他用户显示****' WHERE `manage_code` = 'cost_price'"
);