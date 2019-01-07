<?php

$u['1810'] = array(
     //定时脚本
    "INSERT INTO `sys_schedule` ( `code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('inventory_control_compare', '库存差异对比', 'inventory_control_compare', '', '0', '0', '库存差异对比,每天凌晨以后执行', '{\"app_act\":\"rpt/inventory_mgr/inventory_compare\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '86400', '0', 'sys', '', '0', '{\"time\":[\"00:30\"]}', '0');
",
    //库存对比表
    "CREATE TABLE `rpt_inv_compare` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compare_code` varchar(128) DEFAULT NULL COMMENT '对比号',
  `store_code` varchar(50) NOT NULL,
  `inventory_sku_num` int(11) NOT NULL DEFAULT '0' COMMENT '可用在库库存总数',
  `unique_num` int(11) NOT NULL DEFAULT '0' COMMENT '唯一码可用总数',
  `compare_num` int(11) NOT NULL DEFAULT '0' COMMENT '差异库存总数',
  `compare_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '对照时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`compare_code`) USING BTREE,
  KEY `_index1` (`store_code`) USING BTREE,
  KEY `_index2` (`compare_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存差异对照报表';",
    //详情表
    "CREATE TABLE `rpt_inv_compare_detail` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `compare_code` varchar(128) DEFAULT NULL,
  `store_code` varchar(50) NOT NULL,
  `sku` varchar(128) NOT NULL,
  `sys_num` int(11) DEFAULT '0' COMMENT '可用在库库存数',
  `unique_num` int(11) DEFAULT '0' COMMENT '可用唯一码在库数',
  `barcode` varchar(128) NOT NULL,
  `compare_time` datetime NOT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`compare_code`,`store_code`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    //差异报表菜单显示
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030500', '5030000', 'url', '库存差异对比报表', 'rpt/inventory_control/do_list', '4', '1', '0', '1', '0');
",
);

$u['1840'] = array(
    //唯一码导入增加权限
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('5030310', '5030300', 'act', '唯一码导入', 'prm/goods_unique_code_tl/export_list', '1', '1', '0', '1', '0');
",
);