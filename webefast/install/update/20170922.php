<?php



$u['1655'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('warn_weight', 'waves_property', 'S002_017 预警重量', 'text', '', '1000', '0.00', '', '2015-12-15 11:20:43', '称重校验时,若实际重量大于预警重量,系统会给出提示');",
);
$u['1703'] = array(
    "ALTER TABLE `api_order` ADD COLUMN `salesman_code`  varchar(128) NOT NULL DEFAULT '' COMMENT '业务员编号';"
);

$u['1656'] = array(
    "INSERT INTO `base_sale_channel` ( `sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`, `remark`, `lastchanged`) VALUES ( 'maizuo', 'mz', '卖座网', '1', '1', '实际未对接该平台，仅为增加平台来源', now() ) "
);

$u['1664'] = array(
    //批量确认
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030118', '4030100', 'act', '批量确认', 'oms/return_opt/opt_pl_confirm', '8', '1', '0', '1', '0');",
    //批量取消确认
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030119', '4030100', 'act', '批量取消确认', 'oms/return_opt/opt_pl_unconfirm', '8', '1', '0', '1', '0');",
    //批量作废
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030120', '4030100', 'act', '批量作废', 'oms/return_opt/opt_pl_cancel', '8', '1', '0', '1', '0');",
    //批量收货
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030121', '4030100', 'act', '批量收货', 'oms/return_opt/opt_pl_receive', '8', '1', '0', '1', '0');",
    //批量退款
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4030122', '4030100', 'act', '批量退款', 'oms/return_opt/opt_pl_refund', '8', '1', '0', '1', '0');",
);


$u['1622'] = array(
    //(批量)取消波次
     "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7010120', '7010102', 'act', '(批量)取消波次', 'oms/waves_record/do_cancel_waves', '8', '1', '0', '1', '0');",
);

$u['1679'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12040000', '12000000', 'group', '道讯ERP', 'dnerp-manage', '101', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12040200', '12040000', 'url', '道讯ERP单据同步', 'erp/dxerp/trade_list', '1', '1', '0', '1', '1');",
    "CREATE TABLE `api_dxerp_trade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号/退单号)',
  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
  `order_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '单据类型 1 销售订单 2销售退单',
  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `upload_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
  `upload_time` datetime NOT NULL COMMENT '上传时间',
  `upload_msg` varchar(255) DEFAULT NULL COMMENT '上传失败原因',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sell_record_code` (`sell_record_code`,`order_type`) USING BTREE,
  KEY `upload_time` (`upload_time`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE,
  KEY `store_code` (`store_code`) USING BTREE,
  KEY `order_type` (`order_type`) USING BTREE,
  KEY `deal_code` (`deal_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='道讯erp上传单据';",
   'INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES (\'cli_daoxun_upload\', \'道讯ERP单据上传\', \'cli_daoxun_upload\', \'\', \'0\', \'6\', \'\', \'{\"app_act\":\"erp/dxerp/do_upload_cli\"}\', \'webefast/web/index.php\', \'0\', \'0\', \'0\', \'120\', \'0\', \'sys\', \'\', \'0\', NULL, \'0\');',
    "CREATE TABLE `dxerp_config` (
  `erp_config_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `erp_config_code` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP配置code',
  `erp_config_name` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP配置名称',
  `erp_address` varchar(128) NOT NULL DEFAULT '' COMMENT 'ERP地址',
  `upload_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'eFAST单据上传: 0 上传零售单据, 1 上传销售日报',
  `online_time` date NOT NULL COMMENT 'erp上线时间',
  `trade_sync` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '单据同步: 0不 启用, 1 启用',
  PRIMARY KEY (`erp_config_id`),
  UNIQUE KEY `erp_config_code` (`erp_config_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='道讯ERP配置';",
    'INSERT INTO `dxerp_config` (`erp_config_code`, `erp_config_name`, `erp_address`, `upload_type`, `online_time`, `trade_sync`) VALUES (\'dxerp_record_upload\', \'道讯ERP配置\', \'http://122.227.176.226:4004/diDiLuWeb/service\', \'0\', \'0000-00-00\', \'0\');',
);
