<?php

$u['2200']=array(
    "update sys_schedule set `desc` = '启用后，系统将自动从非淘系平台，拉取各店铺的平台订单信息' where code = 'order_download_cmd'",
    "update sys_schedule set `desc` = '启用后，系统将自动从非淘系平台，拉取各店铺的平台退单信息' where code = 'refund_download_cmd'",
    "update sys_schedule set `desc` = '需要订购天猫店铺的支付宝，此服务才有效，默认关闭' where code = 'alipay_download_cmd'",
    "update sys_schedule set `desc` = '开启后，缺货商品库存补足后，系统会自动按照计划发货时间依次解除缺货状态；' where code = 'cli_batch_remove_short'",
    "update sys_schedule set `desc` = '此服务与“运营”模块“订单审核规则”配合使用。
(特别说明：商家可以在“订单审核规则”设置若干个审单时间，系统将按照商家的设置，在指定时间自动按规则审单)' where code = 'auto_confirm'",
    "update sys_schedule set `desc` = '' where code = 'auto_confirm_return_money'",
    "update sys_schedule set `desc` = '仅支持BSERP2和BS3000J产品对接，档案同步的内容包括：商品基本信息、商品颜色、商品尺码、大类、季节、品牌等（商品条形码需要在系统人工操作生成）。' where code = 'erp_item_download_cmd'",
    "update sys_schedule set `desc` = '仅支持BSERP2和BS3000J产品对接，此服务包含两个功能项：1.库存获取，通过接口获取ERP最新的商品库存2.库存覆盖，即将获取的ERP库存，覆盖系统库存。' where code = 'erp_item_inv_update_cmd'",
    "update sys_schedule set `desc` = '仅支持BSERP2和BS3000J产品对接，系统的网络订单和有退货的售后服务单，上传到ERP。' where code = 'erp_trade_upload_cmd'",
    "update sys_schedule set `desc` = '功能说明：1.将支付宝流水，科目为交易收款类收入，更新到零售汇总查询中的交易实际收入中2.核对零售结算汇总查询，应收是否与实收相等，如果相等，分别为零售结算汇总数据、支付宝数据打上记账标记，默认关闭' where code = 'alipay_accounts_cmd'
",
    "update sys_schedule set `desc` = '库存同步数量与旗舰店一致' where code = 'fx_inv_upload_cmd'",
    "update sys_schedule set `desc` = '通过数据分析提供补货建议具体数值' where code = 'create_pur_advise_data'",
    "update sys_schedule set `desc` = '仅支持BSERP2和BS3000J产品对接，下载条码档案并更新到系统。' where code = 'erp_barcode_download_cmd'",
    "update sys_schedule set `desc` = '' where code = 'auto_notice'",
    "update sys_schedule set `desc` = '仅支持BSERP2和BS3000J产品对接，系统的批发销货单和批发退货单，上传到ERP。' where code = 'erp_wbm_record_upload_cmd'",
    "update sys_schedule set `desc` = '开启后，系统自动将未初始化的商品初始化到商品列表' where code = 'auto_goods_init'",
    "update sys_schedule set `desc` = '开启后，系统自动将指定状态且更新了商家备注的订单进行拦截设问' where code = 'opt_record_by_seller_remark'",
    "update sys_schedule set `desc` = '启用后，系统将自动从淘宝平台，拉取各店铺的平台订单信息' where code = 'taobao_order_download_cmd'",
    "update sys_schedule set `desc` = '启用后，系统将自动从淘宝平台，拉取各店铺的平台退单信息' where code = 'taobao_refund_download_cmd'",
    "update sys_schedule set `desc` = '仅支持BSERP2，系统的销售订单锁定库存上传到ERP。' where code = 'cli_upload_lock_inv'",
    "update sys_schedule set `desc` = '定时自动从唯品会平台自动获取已销售订单占用库存数，以便保障库存记录能及时对平台已销售占用库存进行预锁占用' where code = 'weipinhuijit_getOccupiedOrders_cmd'",
    "update sys_schedule set `desc` = '开启后，创建超过系统设置的售后服务单自动作废时间的未确认的售后服务单将会被作废' where code = 'return_order_to_delete'",
    "update sys_schedule set `desc` = '仅支持BSERP3产品对接，系统的门店仓对应的网络订单和有退货的售后服务单，已确认后上传到ERP3。' where code = 'cli_erp3_o2o_upload_record'",
    "update sys_schedule set `desc` = '仅支持BSERP3产品对接，获取门店订单发货状态。' where code = 'cli_erp3_o2o_record_info'",
    "update sys_schedule set `desc` = '仅支持BSERP3产品对接，将门店订单发货状态同步到系统。' where code = 'cli_erp3_o2o_order_shipping'"
);
$u["2206"] = array(
"update sys_params set memo = '开启后，订单列表和订单查询页面的交易号、订单号、手机号、买家昵称、快递单号查询支持模糊查询' where param_code = 'fuzzy_search'"
);
$u["2097"] = array( //短信模块
    //短信模块菜单
    "INSERT INTO `sys_action` VALUES ('3060000', '3000000', 'group', '短信管理', 'sms_manage', '10', '1', '0', '1','0')",
    "INSERT INTO `sys_action` VALUES ('3060100', '3060000', 'url', '短信参数设置', 'op/sms_config/do_list', '1', '1', '0', '1','0')",
    "INSERT INTO `sys_action` VALUES ('3060200', '3060000', 'url', '店铺短信设置', 'op/sms_shop_config/do_list', '2', '1', '0', '1','0')",
    "INSERT INTO `sys_action` VALUES ('3060300', '3060000', 'url', '短信模板', 'op/sms_tpl/do_list', '3', '1', '0', '1','0')",
//    "INSERT INTO `sys_action` VALUES ('3060400', '3060000', 'url', '短信营销群发', 'op/sms_marketing/do_list', '4', '1', '0', '1','0')",
    "INSERT INTO `sys_action` VALUES ('3060500', '3060000', 'url', '短信任务列表', 'op/sms_queue/do_list', '5', '1', '0', '1','0')",
    //店铺短信设置权限
    "INSERT INTO `sys_action` VALUES ('3060201', '3060200', 'act', '启用/停用', 'op/sms_shop_config/opt_update_active', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060202', '3060200', 'act', '编辑', 'op/sms_shop_config/detail#scene=edit', '1', '1', '0', '1', '0')",
    //短信模板权限
    "INSERT INTO `sys_action` VALUES ('3060301', '3060300', 'act', '新增', 'op/sms_tpl/detail#scene=add', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060302', '3060300', 'act', '编辑', 'op/sms_tpl/detail#scene=edit', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060303', '3060300', 'act', '预览', 'op/sms_tpl/do_preview', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060304', '3060300', 'act', '删除', 'op/sms_tpl/do_delete', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060305', '3060300', 'act', '预计算字数与条数', 'op/sms_tpl/opt_word_count', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060306', '3060300', 'act', '发送测试', 'op/sms_tpl/opt_send_test', '1', '1', '0', '1', '0')",
    //短信任务列表权限
    "INSERT INTO `sys_action` VALUES ('3060501', '3060500', 'act', '导出', 'op/sms_queue/export_list', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060502', '3060500', 'act', '查看', 'op/sms_queue/do_preview', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060503', '3060500', 'act', '发送（含批量）', 'op/sms_queue/opt_send_sms', '1', '1', '0', '1', '0')",
    "INSERT INTO `sys_action` VALUES ('3060504', '3060500', 'act', '终止（含批量）', 'op/sms_queue/opt_over_sms', '1', '1', '0', '1', '0')",
    //系统参数设置(短信相关)
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('sms_config', '0', '短信参数设置', 'group', '', '', '0', '', '2014-12-25 10:51:10', '', NULL)",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('sms_config_common', 'sms_config', '短信通用设置', 'group', '', '', '0', '', '2018-01-15 14:01:02', '', NULL)",
//    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('sms_config_marketing', 'sms_config', '短信营销设置', 'group', '', '', '0', '', '2018-01-15 14:01:02', '', NULL)",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('sms_send_timeout_time', 'sms_config_common', '短信发送超时时间', 'text', '', '24', '0', '小时', '2017-05-17 18:57:12', '参数范围:1-24小时，当前时间-短信发送计划时间>该处设定时间时，自动终止短信发送任务', '')",
//    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('sms_send_interval_time', 'sms_config_common', '短信发送间隔时间', 'text', '', '1', '0', '秒', '2018-01-15 14:01:02', '参数值：不低于1秒，发送太频繁短信供应商会自动屏蔽，拒绝处理数据', NULL)",
    //短信发送任务表
    "CREATE TABLE `op_sms_queue` (
        `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增',
        `sms_type` varchar(50) NOT NULL COMMENT '短信类型:deliver=发货通知,send_test=发送测试',
        `keywords`  varchar(64) NOT NULL DEFAULT '' COMMENT '业务关键字(订单号等)',
        `buyer_name` varchar(30) NOT NULL COMMENT '会员昵称',
        `tel` varchar(20) NOT NULL COMMENT '发送手机号',
        `sms_info` text NOT NULL COMMENT '消息内容',
        `sms_num` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '(预计)使用短信数',
        `send_start_time` varchar(30) NOT NULL DEFAULT '' COMMENT '每日起始发送时间',
        `send_end_time` varchar(30) NOT NULL DEFAULT '' COMMENT '每日终止发送时间',
        `plan_send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '计划发送时间',
        `send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
        `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '发送状态:0=未发送,1=发送成功,2=发送失败,3=终止,4=发送中',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
        `create_time` datetime NOT NULL COMMENT '创建时间',
        PRIMARY KEY (`id`),
        KEY `tel` (`tel`) USING BTREE,
        KEY `status` (`status`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信发送任务表';",
    //店铺短信配置表
    "CREATE TABLE `op_sms_config_shop` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `shop_code` varchar(128) NOT NULL COMMENT '商店代码',
        `is_active` TINYINT(1) DEFAULT '0' COMMENT '是否启用:0.关闭1.开启',
        `enable_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '启用时间',
        `send_start_time` varchar(30) NOT NULL DEFAULT '' COMMENT '每日起始发送时间',
        `send_end_time` varchar(30) NOT NULL DEFAULT '' COMMENT '每日终止发送时间',
        `order_type` varchar(24) NOT NULL DEFAULT '' COMMENT '订单类型逗号分隔is_fenxiao:0.普通1.淘分销2.网络分销',
        `delivery_notice_status` TINYINT(4) DEFAULT '0' COMMENT '发货通知启用状态:0.关闭1.开启',
        `delivery_notice_tpl_id` int(11) DEFAULT '0' COMMENT '发货通知模板id',
        `delivery_notice_last_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '最后生成发货通知短信时间',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
        `create_time` datetime NOT NULL COMMENT '创建时间',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `shop_code` (`shop_code`) USING BTREE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='店铺短信配置表';",
    //短信模板表
    "CREATE TABLE `op_sms_tpl` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `tpl_type` varchar(30) NOT NULL DEFAULT '' COMMENT '模版类型',
        `tpl_name` varchar(128) NOT NULL COMMENT '模版名称',
        `sms_sign` varchar(128) NOT NULL DEFAULT '' COMMENT '短信签名',
        `sms_info` text NOT NULL COMMENT '短信内容(包含变量)',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
        `create_time` datetime NOT NULL COMMENT '创建时间',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        KEY `tpl_name` (`tpl_name`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='短信模板';",
    //短信总账表
    "CREATE TABLE `op_sms_account` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `num` int(11) NOT NULL DEFAULT '0' COMMENT '短信总数',
        `lock_num` int(11) NOT NULL DEFAULT '0' COMMENT '锁定数量',
        `used_num` int(11) NOT NULL DEFAULT '0' COMMENT '已使用数量',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='短信总账表';",
    "INSERT INTO `op_sms_account` VALUES ('1', '0', '0', '0');",
    //短信购买记录表
    "CREATE TABLE `op_sms_buy_record` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `user_code` varchar(128) NOT NULL COMMENT '购买人code',
        `order_no` varchar(64) NOT NULL DEFAULT '' COMMENT '订单号',
        `sms_price` decimal(14,3) DEFAULT '0.000' COMMENT '短信单价',
        `sms_num` int(11) NOT NULL DEFAULT '0' COMMENT '购买短信数量',
        `pay_money` decimal(14,2) DEFAULT '0.00' COMMENT '支付金额',
        `pay_status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '支付状态 1待付款 2已付款 3支付失败',
        `pay_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '支付时间',
        `create_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
        `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `order_no` (`order_no`)
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='短信购买记录表';",
    //短信套餐表
    "CREATE TABLE `op_sms_package` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `package_name` varchar(64) NOT NULL DEFAULT '' COMMENT '套餐名称',
        `sms_num` int(11) NOT NULL DEFAULT '0' COMMENT '套餐短信数量',
        `sms_price` decimal(14,3) DEFAULT '0.000' COMMENT '短信单价',
        `pay_money` decimal(14,2) DEFAULT '0.00' COMMENT '套餐价格',
        `create_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='短信套餐表';",
    //短信中间表 (数据库待定, 先在本地创建)
    "CREATE TABLE `sms_task` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `sms_type` varchar(50) NOT NULL COMMENT '短信类型:deliver=发货通知',
        `kh_id` int(11) NOT NULL COMMENT '客户ID',
        `sys_sms_id` int(11) NOT NULL COMMENT '系统短信ID',
        `send_channel` varchar(20) NOT NULL DEFAULT '' COMMENT '发送通道:yunrong=云融正通',
        `send_channel_code` varchar(100) NOT NULL DEFAULT '' COMMENT '发送通道短信ID',
        `phone` varchar(20) NOT NULL COMMENT '手机号',
        `content` text NOT NULL DEFAULT '' COMMENT '短信内容',
        `num` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '短信条数',
        `status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '发送状态:0=未发送,1=发送成功,2=发送失败,3=发送中',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
        `send_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '发送时间',
        `report_content` varchar(255) NOT NULL DEFAULT '' COMMENT '报告内容',
        `report_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '报告时间',
        `is_push_report` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否推送短信报告到客户表',
        PRIMARY KEY (`id`),
        UNIQUE KEY `_kh_sms_id` (`kh_id`,`sys_sms_id`) USING BTREE,
        KEY `_index_1` (`send_time`,`status`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='短信任务中间表';",
    //定时任务 (待处理)
//    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`)
//        VALUES ('create_delivered_sms', '生成订单发货通知短信', '', '', '0', '0', '开启后，已发货订单自动生成通知短信，需要在短信管理中启用店铺短信设置，才会生效', '{\"app_act\":\"op/sms_queue/create_delivered_sms\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '600', '0', 'sys', '', '0', NULL, '0')",
//    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`)
//        VALUES ('auto_send_sms', '自动发送短信任务', '', '', '0', '0', '开启后，自动发送短信任务列表中的短信', '{\"app_act\":\"op/sms_queue/auto_send_sms\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '600', '0', 'sys', '', '0', NULL, '0')",
//    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`)
//        VALUES ('auto_send_sms_centre', '自动批量发送短信', '', '', '0', '0', '开启后，自动发送短信中间表中的短信', '{\"app_act\":\"sms/send_batch_sms&is_one_by_one=false\",\"app_fmt\":\"json\"}', 'webservice/web/index.php', '0', '0', '0', '600', '0', 'sys', '', '0', NULL, '0')",
//    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`)
//        VALUES ('auto_sms_report', '自动推送短信报告', '', '', '0', '0', '开启后，自动推送短信报告到客户表', '{\"app_act\":\"op/sms_queue/auto_send_sms\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '600', '0', 'sys', '', '0', NULL, '0')",
);

$u['2198'] = array(
    "INSERT INTO `sys_params` (`id`, `param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`, `data`) VALUES ('1267', '', 'aligenius_upload_check', 'ag', 'AG004 自动上传审核信息', 'radio', '[\"关闭\",\"开启\"]', '0', '5.00', '1-开启 0-关闭', '2018-03-27 11:53:23', '开启后，系统每5分钟自动推送审核结果（默认推送审核通过）至AG平台，（此时AG后台对应单据状态从“待处理”进入“待审核列表”，商家若在AG“自动化退款策略”中配置“同意审核”指令，可实现该场景的批量自动退款）', NULL);",
    "UPDATE `sys_params` SET `param_name`='AG001 自动上传未发货订单取消结果', `sort`='2.00', `memo`='开启后,系统每5分钟自动将“未发货仅退款订单”的拦截取消结果回传AG平台（AG后台可在“待我处理的退款”页面实现人工筛选后的批量退款处理）' WHERE (`param_code`='aligenius_sendgoods_cancel');",
    "UPDATE `sys_params` SET `param_name`='AG003 单据退款审核', `sort`='4.00', `memo`='开启后，AG模块显示“待同步退款审核状态”页签，单据取消/入库状态推送后增加审核流程，审核后单据才会完成' WHERE (`param_code`='aligenius_refunds_check');",
    "UPDATE `sys_params` SET `param_name`='AG002 自动上传已发货退货入库结果', `sort`='3.00', `memo`='开启后，系统每5分钟自动将“买家已退货待卖家确认收货”订单的退货入仓状态回传AG平台（AG后台可在“待我处理的退款”页面实现人工筛选后的批量退款处理）' WHERE (`param_code`='aligenius_warehouse_update');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_aligenius_sendgoods_cancel', '自动上传未发货订单取消结果', 'ag', '', '0', '13', '开启后,系统每5分钟自动将“未发货仅退款订单”的拦截取消结果回传AG平台（AG后台可在“待我处理的退款”页面实现人工筛选后的批量退款处理）', '{\"app_act\":\"oms/taobao_ag/cli_aligenius_sendgoods_cancel\"}', 'webefast/web/index.php', '0', '0', '', '300', '0', 'sys', '', '', NULL, '1');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_aligenius_warehouse_update', '自动上传已发货退货入库结果', 'ag', '', '0', '13', '开启后，AG模块显示“待同步退款审核状态”页签，单据取消/入库状态推送后增加审核流程，审核后单据才会完成', '{\"app_act\":\"oms/taobao_ag/cli_aligenius_warehouse_update\"}', 'webefast/web/index.php', '0', '0', '', '300', '0', 'sys', '', '', NULL, '1');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_aligenius_upload_check', '自动上传审核信息', 'ag', '', '0', '13', '开启后，系统每5分钟自动推送审核结果（默认推送审核通过）至AG平台，（此时AG后台对应单据状态从“待处理”进入“待审核列表”，商家若在AG“自动化退款策略”中配置“同意审核”指令，可实现该场景的批量自动退款）', '{\"app_act\":\"oms/taobao_ag/cli_aligenius_upload_check\"}', 'webefast/web/index.php', '0', '0', '', '300', '0', 'sys', '', '', NULL, '1');"

);