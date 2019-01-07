<?php
$u = array();
$u['273'] = array(
    "ALTER TABLE `api_weipinhuijit_store_out_record`
DROP INDEX `store_out_record_no` ,
ADD UNIQUE INDEX `store_out_record_no` (`store_out_record_no`, `pick_no`) USING BTREE ;",
    "ALTER TABLE `api_weipinhuijit_delivery_detail`
    ADD UNIQUE INDEX `_key` (`sku`, `record_code`, `pick_no`, `delivery_id`) USING BTREE ;
",
);

$u['267'] = array(
    "INSERT INTO `sys_action` VALUES ('21010500', '21010000', 'url', '商品滞销分析', 'rpt/unsalable_report/do_list', '10', '1', '0', '1','0');"
);
$u['bug_190'] = array(
    "INSERT INTO sys_action VALUE ('5010503','5010500','act','添加规格2','prm/spec2/detail&app_scene=add','3','1','0','1','0');",
    "UPDATE sys_action SET parent_id = '5010500' WHERE action_id = '5010502';",
);
$u['265'] = array(
    "UPDATE sys_action SET action_name = '门店会员' WHERE action_id = 30020000;",
    "UPDATE sys_action SET action_name = '门店会员列表' WHERE action_id = 30020100;",
    "INSERT INTO `sys_action` VALUES ('30010300', '30010000', 'url', '门店商品库存查询', 'prm/inv/do_list', '11', '1', '0', '1','2');"
);
$u['268'] = array(
    "UPDATE sys_action SET action_name = '网络订单应收明细' WHERE action_id = '9020100';",
    "UPDATE sys_action SET action_name = '网络交易综合查询' WHERE action_id = '9020200';",
);
$u['275'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('is_only_gift', 'oms_property', '订单赠品工具，仅能添加赠品', 'radio', '[\"关闭\",\"开启\"]', '0', '13', '1-开启 0-关闭', '2016-05-05 18:48:01', '开启后，只能添加商品属性为赠品的商品，关闭后，所有商品都可以添加。');"
);
$u['265'] = array(
    "UPDATE `sys_print_templates` SET print_templates_code = 'jd_cod', print_templates_name = '京东快递货到付款' WHERE print_templates_name = '京东快递_电子面单';", //修改重复的code
    
    "ALTER TABLE `base_pay_type` ADD UNIQUE (`pay_type_code`);", //支付方式表code增加唯一索引
    
    "ALTER TABLE `sys_print_templates` ADD UNIQUE (`print_templates_code`);", //打印模版code增加唯一索引
    
    "INSERT INTO `base_pay_type` (`pay_type_code`, `pay_type_name`, `is_fetch`, `relation_code`, `status`, `is_vouch`, `is_cod`, `remark`, `lastchanged`, `charge`) VALUES ('cash', '现金支付', '0', '0', '1', '1', '0', '门店', now(), '0.000');", //增加付款方式
    
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('cashier_ticket', '小票模板', NULL, '30', '0', '0', '0', '75', '200', '无', '{\"conf\":\"cashier_ticket\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"\",\"report_top\":\"5\",\"report_left\":\"5\"}', '&lt;div id=&quot;report&quot;&gt;&lt;div title=&quot;报表头&quot; class=&quot;group&quot; id=&quot;report_top&quot;&gt;&lt;div class=&quot;row border&quot; id=&quot;row_0&quot; style=&quot;height: 40px;&quot; nodel=&quot;1&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_0&quot; style=&quot;width: 245px; height: 40px; text-align: center; line-height: 40px; font-size: 18px;&quot;&gt;欢迎光临&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_9&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_118&quot; style=&quot;width: 245px; height: 22px; text-align: center; line-height: 22px; font-size: 14px;&quot;&gt;{@店铺名称}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_10&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_119&quot; style=&quot;height: 22px; text-align: left; line-height: 22px;&quot;&gt;订单号：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_123&quot; style=&quot;width: 120px; height: 22px; text-align: left; line-height: 22px;&quot;&gt;{@订单号}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_13&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_122&quot; style=&quot;height: 22px; text-align: left; line-height: 22px;&quot;&gt;日期：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_124&quot; style=&quot;width: 120px; height: 22px; text-align: left; line-height: 22px;&quot;&gt;{@日期}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div title=&quot;表格&quot; class=&quot;group&quot; id=&quot;report_table_body&quot; type=&quot;table&quot; nodel=&quot;1&quot;&gt;&lt;table class=&quot;table&quot; id=&quot;table_1&quot; border=&quot;0&quot; cellspacing=&quot;0&quot; cellpadding=&quot;0&quot;&gt;&lt;tr&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 55px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_69&quot; style=&quot;width: 55px;&quot;&gt;商品编码&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 50px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_70&quot; style=&quot;width: 50px;&quot;&gt;{@规格1名}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 50px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_71&quot; style=&quot;width: 50px;&quot;&gt;{@规格2名}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_72&quot; style=&quot;width: 40px; height: 20px; line-height: 20px;&quot;&gt;数量&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_title&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_th_73&quot; style=&quot;width: 40px;&quot;&gt;金额&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;&lt;!--detail_list--&gt;&lt;/table&gt;&lt;/div&gt;&lt;div title=&quot;表格尾&quot; class=&quot;group&quot; id=&quot;report_table_bottom&quot;&gt;&lt;div class=&quot;row border&quot; id=&quot;row_2&quot; nodel=&quot;1&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_6&quot; style=&quot;width: 60px; text-align: left;&quot;&gt;总计：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_22&quot; style=&quot;width: 120px; text-align: left;&quot;&gt;{@订单实收}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_14&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_125&quot; style=&quot;height: 22px; text-align: right; line-height: 22px;&quot;&gt;结算方式：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_126&quot; style=&quot;width: 120px; height: 22px; text-align: left; line-height: 22px;&quot;&gt;{@结算方式}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;div title=&quot;报表尾&quot; class=&quot;group&quot; id=&quot;report_bottom&quot;&gt;&lt;div class=&quot;row border&quot; id=&quot;row_3&quot; style=&quot;height: 22px;&quot; nodel=&quot;1&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_7&quot; style=&quot;width: 60px; text-align: left;&quot;&gt;收银员：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_54&quot; style=&quot;width: 120px; height: 22px; text-align: left; line-height: 22px;&quot;&gt;{@收银员}&lt;/div&gt;&lt;/div&gt;&lt;div class=&quot;row border&quot; id=&quot;row_16&quot;&gt;&lt;div class=&quot;column&quot; id=&quot;column_128&quot; style=&quot;height: 22px; line-height: 22px;&quot;&gt;打印时间：&lt;/div&gt;&lt;div class=&quot;column&quot; id=&quot;column_129&quot; style=&quot;width: 120px; height: 22px; text-align: left; line-height: 22px;&quot;&gt;{@打印时间}&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;&lt;/div&gt;', '&lt;tr&gt;&lt;td class=&quot;td_detail&quot; style=&quot;width: 55px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_td_69&quot; style=&quot;width: 55px;&quot;&gt;{#商品编码}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_detail&quot; style=&quot;width: 50px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_td_70&quot; style=&quot;width: 50px;&quot;&gt;{#规格1值}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_detail&quot; style=&quot;width: 50px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_td_71&quot; style=&quot;width: 50px;&quot;&gt;{#规格2值}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_detail&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_td_72&quot; style=&quot;width: 40px;&quot;&gt;{#数量}&lt;/div&gt;&lt;/td&gt;&lt;td class=&quot;td_detail&quot; style=&quot;width: 40px;&quot;&gt;&lt;div class=&quot;td_column&quot; id=&quot;column_td_73&quot; style=&quot;width: 40px;&quot;&gt;{#金额}&lt;/div&gt;&lt;/td&gt;&lt;/tr&gt;', '');", //增加小票模板
    
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('1011000', '1010000', 'url', '门店参数设置', 'sys/params/shop_do_list', '9', '1', '0', '1', '2');", //增加门店参数设置菜单
    
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('ticket_print', 'cashier_set', '收银完成打印小票', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-开启 0-关闭', NOW(), '默认收银后自动打印小票，若客户无需打印小票，可关闭');", //增加门店收银打印小票配置项

);

$u['294'] = array(
     "INSERT INTO `base_sale_channel` VALUES ('41', 'juanpi', 'jp', '卷皮网', '1', '1', '', '2016-05-07 13:57:24');",
);
$u['261'] = array(
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_erp3_o2o_upload_record', '单据上传', 'cli_erp3_o2o_upload_record', '', '0', '3', '仅支持BSERP3产品对接，系统的门店仓对应的网络订单和有退货的售后服务单，已确认后上传到ERP3。此服务60分钟运营一次。', '{\"app_act\":\"o2o/o2o_mgr/cli_upload_record\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'api', '', '0', NULL, '0');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_erp3_o2o_record_info', '取ERP3收发货状态', 'cli_erp3_o2o_upload_record', '', '0', '3', '仅支持BSERP3产品对接，获取门店订单发货状态。此服务60分钟运营一次。', '{\"app_act\":\"o2o/o2o_mgr/cli_o2o_record_info\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'api', '', '0', NULL, '0');",
    "INSERT INTO `sys_schedule` (`code`, `name`, `task_type_code`, `sale_channel_code`, `status`, `type`, `desc`, `request`, `path`, `max_num`, `add_time`, `last_time`, `loop_time`, `task_type`, `task_module`, `exec_ip`, `plan_exec_time`, `plan_exec_data`, `update_time`) VALUES ('cli_erp3_o2o_order_shipping', '收发货状态同步到系统', 'cli_erp3_o2o_order_shipping', '', '0', '3', '仅支持BSERP3产品对接，将门店订单发货状态同步到系统。此服务60分钟运营一次。', '{\"app_act\":\"o2o/o2o_mgr/cli_order_shipping\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '3600', '0', 'api', '', '0', NULL, '0');",
    "CREATE TABLE `o2o_oms_order` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
        `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
        `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
        `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
        `sys_sl` int(11) NOT NULL DEFAULT '-1' COMMENT '系统商品数量',
        `api_sl` int(11) NOT NULL DEFAULT '-1' COMMENT '接口商品数量',
        `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`barcode`) USING BTREE,
        KEY `barcode` (`barcode`) USING BTREE,
        KEY `record_code` (`record_code`) USING BTREE,
        KEY `record_type` (`record_type`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    "CREATE TABLE `o2o_oms_trade` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
        `in_out_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '单据出入库类型 1出库 2入库 移仓单根据这个标识区分出入库',
        `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
        `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
        `order_status` varchar(20) NOT NULL DEFAULT '' COMMENT 'api订单状态',
        `upload_request_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上传请求时间',
        `upload_request_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传请示是否发送成功的标识，10 表示上传成功',
        `upload_response_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'api回传订单是否接单的时间',
        `upload_response_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'api回传订单是否接单，0 没回传 10 表示接单成功 20 表示接单失败 30表示主动查询接单成功',
        `upload_response_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT 'api回传接单失败的信息',
        `cancel_request_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '取消请求发出时间',
        `cancel_request_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '取消请求是否发送成功的标识，0表示没有取消请求 10表示是取消请求发出成功',
        `cancel_response_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'api回传取消订单是否成功的时间',
        `cancel_response_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'api回传取消订单是否成功，0 没回传 10 表示取消成功 20 表示取消失败 30表示主动查询取消成功',
        `cancel_response_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT 'api回传取消订单失败的信息',
        `process_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理api回传信息的时间',
        `process_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理api回传信息成功的标识, 0 表示待处理 10 解析成功 20 处理出错 30 处理结束',
        `process_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT '处理API回传信息失败的原因',
        `api_order_from_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '接口回传信息来源, 10 来源于回传 20 来源于查询接口',
        `process_fail_num` tinyint(4) DEFAULT '0' COMMENT '处理失败次数',
        `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'api发货的物流公司CODE',
        `express_no` varchar(30) NOT NULL DEFAULT '' COMMENT 'api发货的物流单号',
        `order_weight` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '订单重量',
        `api_order_time` int(11) NOT NULL COMMENT 'wms 发/收货的时间',
        `api_order_flow_end_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'api订单流转结束 取消成功 已发货/收货/关闭订单',
        `sys_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '系统仓库代码',
        `api_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT 'api仓库代码',
        `api_product` varchar(20) NOT NULL DEFAULT '' COMMENT '对接产品名称 bserp',
        `api_record_code` varchar(50) NOT NULL DEFAULT '' COMMENT 'api单据编号',
        `sale_channel_code` varchar(20) NOT NULL DEFAULT '' COMMENT '销售平台代码',
        `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '店铺代码',
        `deal_code` varchar(100) NOT NULL DEFAULT '' COMMENT '订单交易号',
        `buyer_name` varchar(10) NOT NULL DEFAULT '' COMMENT '买家昵称',
        `json_data` text NOT NULL COMMENT '订单/退单 主信息数据',
        `api_result_md5` varchar(32) NOT NULL DEFAULT '' COMMENT 'api收发货结果的MD5值',
        `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
        `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        `cancel_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1取消，0未取消',
        `new_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '新单号',
        PRIMARY KEY (`id`),
        UNIQUE KEY `idx_order_sn_type` (`record_code`,`record_type`) USING BTREE,
        KEY `record_type` (`record_type`) USING BTREE,
        KEY `order_status` (`order_status`) USING BTREE,
        KEY `upload_request_time` (`upload_request_time`) USING BTREE,
        KEY `upload_request_flag` (`upload_request_flag`) USING BTREE,
        KEY `upload_response_time` (`upload_response_time`) USING BTREE,
        KEY `upload_response_flag` (`upload_response_flag`) USING BTREE,
        KEY `cancel_request_time` (`cancel_request_time`) USING BTREE,
        KEY `cancel_request_flag` (`cancel_request_flag`) USING BTREE,
        KEY `cancel_response_time` (`cancel_response_time`) USING BTREE,
        KEY `cancel_response_flag` (`cancel_response_flag`) USING BTREE,
        KEY `process_time` (`process_time`) USING BTREE,
        KEY `process_flag` (`process_flag`) USING BTREE,
        KEY `api_order_from_flag` (`api_order_from_flag`) USING BTREE,
        KEY `express_no` (`express_no`) USING BTREE,
        KEY `api_order_time` (`api_order_time`) USING BTREE,
        KEY `api_order_flow_end_flag` (`api_order_flow_end_flag`) USING BTREE,
        KEY `in_out_flag` (`in_out_flag`) USING BTREE,
        KEY `record_code` (`record_code`) USING BTREE,
        KEY `sys_store_code` (`sys_store_code`) USING BTREE,
        KEY `api_store_code` (`api_store_code`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);

$u['254'] = array(
    "ALTER TABLE `stm_goods_diy_record_detail` ADD COLUMN lof_no VARCHAR(64) default '' COMMENT '批次号';",
    "ALTER TABLE `stm_goods_diy_record_detail` ADD COLUMN production_date date DEFAULT NULL COMMENT '生产日期';",
    "ALTER TABLE `stm_goods_diy_record_detail` ADD COLUMN type VARCHAR(100) DEFAULT 'diy' COMMENT 'diy:组装；lof:批次';",
    "ALTER TABLE `stm_goods_diy_record_detail` ADD COLUMN diy_sku VARCHAR(100) DEFAULT '' COMMENT '组装商品sku';",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN mid;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN goods_id;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN spec1_id;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN spec1_code;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN spec2_id;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN spec2_code;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN refer_price;",
    "ALTER TABLE `stm_goods_diy_record_detail` DROP COLUMN remark;",
    "ALTER TABLE stm_goods_diy_record_detail DROP KEY record_sku;",
    "ALTER TABLE stm_goods_diy_record_detail ADD UNIQUE KEY `record_sku` (`record_code`,`diy_sku`,`sku`,`lof_no`);",
    "ALTER TABLE `stm_goods_diy_record` DROP COLUMN is_finish;",
    "ALTER TABLE `stm_goods_diy_record` DROP COLUMN is_finish_person;",
    "ALTER TABLE `stm_goods_diy_record` DROP COLUMN is_finish_time;"
);

$u['295'] = array(
    "UPDATE `sys_print_templates` SET `template_body`='LODOP.PRINT_INITA(\"0mm\",\"0mm\",480,450,\"京东快递_电子面单\");\r\nLODOP.SET_PRINT_PAGESIZE(0,1000,1140,\"\");\r\nLODOP.ADD_PRINT_TEXTA(\"express_no\",251,95,182,21,c[\"express_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",13);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",199,106,111,18,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no_pack_no\",14,11,359,50,\"128Auto\",c[\"express_no_pack_no\"]);\r\nLODOP.ADD_PRINT_BARCODEA(\"express_no_pack_no\",301,17,216,37,\"128Auto\",c[\"express_no_pack_no\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",199,29,66,18,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_address\",166,29,180,17,c[\"receiver_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",164,5,20,63,\"客户信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(11,1,227,2,0,1);\r\nLODOP.ADD_PRINT_LINE(11,1,10,376,0,1);\r\nLODOP.ADD_PRINT_LINE(227,1,228,376,0,1);\r\nLODOP.ADD_PRINT_LINE(11,375,227,376,0,1);\r\nLODOP.ADD_PRINT_LINE(87,1,88,376,0,1);\r\nLODOP.ADD_PRINT_LINE(137,1,136,376,0,1);\r\nLODOP.ADD_PRINT_LINE(87,191,163,192,0,1);\r\nLODOP.ADD_PRINT_LINE(163,1,164,376,0,1);\r\nLODOP.ADD_PRINT_LINE(137,27,227,28,0,1);\r\nLODOP.ADD_PRINT_LINE(137,229,227,230,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",89,3,57,18,\"始发地：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",89,193,57,20,\"目的地：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(227,260,163,261,0,1);\r\nLODOP.ADD_PRINT_LINE(197,230,196,375,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",164,229,38,32,\"客户签字\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"payable_money\",200,269,105,21,c[\"payable_money\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",196,229,38,31,\"应收金额\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_RECT(248,1,375,173,0,1);\r\nLODOP.ADD_PRINT_LINE(274,1,275,376,0,1);\r\nLODOP.ADD_PRINT_LINE(339,1,340,376,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",275,2,73,21,\"客户信息：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(420,235,274,236,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_name\",275,70,73,21,c[\"receiver_name\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"receiver_mobile\",275,142,89,21,c[\"receiver_mobile\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",275,236,38,19,\"备注\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",295,235,142,44,\"备注信息\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_LINE(367,1,368,376,0,1);\r\nLODOP.ADD_PRINT_LINE(340,95,368,96,0,1);\r\nLODOP.ADD_PRINT_LINE(339,322,367,323,0,1);\r\nLODOP.ADD_PRINT_LINE(393,1,394,376,0,1);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",367,236,44,14,\"商家ID：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",380,236,61,14,\"商家订单号：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",394,236,53,15,\"始发城市：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_addr\",394,286,91,25,c[\"sender_addr\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",10);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sell_record_code\",380,293,84,14,c[\"sell_record_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial\",89,53,130,18,c[\"initial\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial_code\",105,19,167,32,c[\"initial_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",20);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"end_point\",89,243,131,20,c[\"end_point\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",8);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"end_point_code\",108,193,183,29,c[\"end_point_code\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",20);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"real_weigh\",67,281,95,19,c[\"real_weigh\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"initial_order\",138,230,146,25,c[\"initial_order\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",16);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",232,207,133,14,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"print_time\",422,200,113,17,c[\"print_time\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"shop_id_jd\",367,279,96,14,c[\"shop_id_jd\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"custom_txt:\",367,1,61,14,\"寄方信息：\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender\",367,61,75,14,c[\"sender\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_phone\",367,135,85,14,c[\"sender_phone\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\nLODOP.ADD_PRINT_TEXTA(\"sender_address\",380,1,237,15,c[\"sender_address\"]);\r\nLODOP.SET_PRINT_STYLEA(0,\"FontName\",\"黑体\");\r\nLODOP.SET_PRINT_STYLEA(0,\"FontSize\",6);\r\nLODOP.SET_PRINT_STYLEA(0,\"ReadOnly\",0);\r\n' WHERE (`print_templates_code`='jd');",
);