<?php

$u = array();
$u['621'] = array(
    "ALTER TABLE `api_taobao_goods` MODIFY COLUMN `is_relation` tinyint(2) NOT NULL DEFAULT '0' COMMENT '商品是否绑定:0-未匹配;1-商家编码匹配成功;'",
    "ALTER TABLE `api_taobao_sku` MODIFY COLUMN `is_relation` tinyint(2) NOT NULL DEFAULT '0' COMMENT 'sku是否绑定:0-未匹配;1-SKU商家编码匹配成功;2-商品规格匹配成功';"
);

$u['608'] = array("INSERT INTO `sys_schedule` (`code`,`name`,`status`,`type`,`desc`,`request`,`path`,`loop_time`,`task_type`,`task_module`) VALUES('opt_record_by_seller_remark','更新淘宝商家备注并拦截设问','1','11','开启后，系统自动将已确认未发货且更新了商家备注的订单进行拦截设问，默认10分钟执行一次','{\"app_act\":\"oms/sell_record/opt_record_by_sell_remark\",\"app_fmt\":\"json\"}','webefast/web/index.php','600','0','sys');");

$u['626'] = array(
    //快速审单权限
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020324', '4020300', 'act', '快速审单', 'oms/sell_record/inspect_record', '6', '1', '0', '1', '0');
",
    //快速审单查看缓存表
    "CREATE TABLE `oms_sell_record_inspect` (
        `inspect_id` int(11) NOT NULL AUTO_INCREMENT,
        `type` tinyint(4) NOT NULL DEFAULT '0' COMMENT '检索类型 0区域 1条形码',
        `type_val` varchar(20) NOT NULL DEFAULT '' COMMENT '类型值',
        `type_val_name` varchar(20) NOT NULL DEFAULT '' COMMENT '类型名称',
        `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
        `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '店铺代码',
        PRIMARY KEY (`inspect_id`),
        UNIQUE KEY `type_type_val` (`type`,`type_val`,`shop_code`) USING BTREE,
        KEY `type` (`type`) USING BTREE,
        KEY `type_val_index` (`type_val`) USING BTREE,
        KEY `shop_code` (`shop_code`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快速审单表';"
);

$u['635'] = array(
    " DELETE FROM sys_user_pref WHERE iid='oms/sell_record_short_list'"
);

$u['528'] = array(
    "ALTER TABLE `api_tb_goods_sell_prop` DROP INDEX idxu_code;",
    "ALTER TABLE `api_tb_goods_sell_prop` ADD UNIQUE idxu_code (`shop_code`,`goods_code`,`sku`);",
    "ALTER TABLE `api_tb_goods_sell_prop` MODIFY `spec1_code` varchar(255) NOT NULL DEFAULT '' COMMENT '颜色编码(淘宝)';",
    "ALTER TABLE `api_tb_goods_sell_prop` MODIFY `spec2_code` varchar(255) NOT NULL DEFAULT '' COMMENT '尺码编码(淘宝)';",
    "ALTER TABLE `api_tb_goods_sell_prop` ADD `spec1_name` varchar(255) NOT NULL DEFAULT '' COMMENT '颜色名称(淘宝、系统)' AFTER `spec2_code`;",
    "ALTER TABLE `api_tb_goods_sell_prop` ADD `spec2_name` varchar(255) NOT NULL DEFAULT '' COMMENT '尺码名称(淘宝、系统)' AFTER `spec1_name`;"
);
$u['616'] = array(
    "ALTER TABLE wms_archive ADD wms_config_id int(10) not null comment'wms的id号';",
    "update wms_archive as rl inner join sys_api_shop_store as rr on rl.efast_store_code=rr.shop_store_code set  rl.wms_config_id=rr.p_id;"
);

$u['620'] = array(
    "delete from sys_user_pref where iid='goods_do_list/table';"
);

$u['612'] = array(
    "CREATE TABLE IF NOT EXISTS `oms_echart_data` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
        `shop_code` varchar(20) DEFAULT NULL COMMENT '商店代码，如果字段为空，即指所有商店',
        `order_type` varchar(20) DEFAULT NULL COMMENT '订单类型（交易总笔数, 已转入的交易笔数, 已确认订单笔数, 已拣货订单笔数, 已发货订单笔数, 已发货回写订单笔数, 未转入的交易笔数, 未确认订单笔数, 未拣货订单笔数, 已拣货未发货的订单笔数, 未回写订单笔数）',
        `order_num` decimal(11,2) DEFAULT NULL COMMENT '订单数',
        `lastchanged` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '记录时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `shop_code` (`shop_code`,`order_type`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='双十一看板表';",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('echart_data', '双十一看板数据更新', '', '', '1', '10', '开启后，双十一看板数据每隔5分钟刷新一次', '{\"app_act\":\"demo/echarts/view\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '300', '0', 'sys', '', '0', '', '0');
",
    "UPDATE `sys_schedule` SET `code`='echart_data', `name`='双十一看板数据更新', `task_type_code`='', `sale_channel_code`='', `status`='1', `type`='10', `desc`='开启后，双十一看板数据每隔5分钟刷新一次', `request`='{\"app_act\":\"demo/echarts/saveChartsDataByTask\",\"app_fmt\":\"json\"}', `path`='webefast/web/index.php', `max_num`='0', `add_time`='0', `last_time`='0', `loop_time`='300', `task_type`='0', `task_module`='sys', `exec_ip`='', `plan_exec_time`='0', `plan_exec_data`='', `update_time`='0' WHERE (`code`='echart_data');
"
);

$u['633'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`,`short_code`,`sale_channel_name`,`is_system`,`is_active`) VALUES('feiniu','feiniu','飞牛网','1','1');");