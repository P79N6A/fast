<?php
$u['715'] = array(
    "INSERT INTO `sys_schedule` ( `code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('delivery_person', '同步订单表的发货人数据', '', '', '0', '10', '开启后，oms_sell_record_action里的delivery_person数据同步到oms_sell_record的delivery_person数据,五分钟刷新一次', '{\"app_act\":\"rpt/goods_performance/sync_delivery_person\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '600', '0', 'sys', '', '0', NULL, '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21003020', '21003000', 'url', '验货绩效统计', 'rpt/goods_performance/do_list', '2', '1', '0', '1', '0');",
    "UPDATE `sys_action` SET `action_name`='绩效统计' WHERE (`action_id`='21003000');",
    "update sys_schedule set status=1 where code='delivery_person'",
);
$u['bug_584'] = array(

    "CREATE TABLE `oms_sell_record_combine` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `shop_code` varchar(128) DEFAULT NULL,
                `store_code` varchar(128) DEFAULT NULL,
                `pay_code` varchar(128) DEFAULT NULL,
                `buyer_name` varchar(128) DEFAULT NULL,
                `receiver_name` varchar(128) DEFAULT NULL,
                `receiver_province` varchar(50) DEFAULT NULL,
                `receiver_city` varchar(50) DEFAULT NULL,
                `receiver_district` varchar(50) DEFAULT NULL,
                `receiver_addr` varchar(128) DEFAULT NULL,
                `num` int(11) DEFAULT NULL,
                `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
                PRIMARY KEY (`id`),
                UNIQUE KEY `_key` (`shop_code`,`pay_code`,`buyer_name`,`receiver_name`,`receiver_province`,`receiver_city`,`receiver_district`,`receiver_addr`) USING BTREE
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);
