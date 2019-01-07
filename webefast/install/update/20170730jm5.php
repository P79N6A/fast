<?php

$u['1391_5'] = array(
    "CREATE  TABLE IF NOT EXISTS  `sys_encrypt_record` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `tb_name` varchar(128) DEFAULT NULL,
                        `min_id` bigint(20) DEFAULT NULL,
                        `max_id` bigint(20) DEFAULT NULL,
                        `sys_id` bigint(20) DEFAULT NULL,
                        `all_num` int(11) DEFAULT NULL,
                        `num` int(11) DEFAULT '0',
                        `is_over` tinyint(3) DEFAULT '0',
                        `error_num` int(11) NOT NULL DEFAULT '0',
                        PRIMARY KEY (`id`),
                        KEY `_key` (`tb_name`) USING BTREE
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) 
    VALUES ('cli_encrypt_task', '历史数据加密', '', '', '1', '10', '', '{\"app_act\":\"cli/cli_encrypt_task\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '300', '0', 'sys', '', '0', NULL, '0');",
    
"create table oms_sell_record_ebak like oms_sell_record",

);
