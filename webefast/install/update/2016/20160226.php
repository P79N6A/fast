<?php
$u = array();

$u['007'] = array(
"ALTER TABLE `oms_sell_record_notice`
ADD COLUMN `sku_all_num`  varchar(256) NULL DEFAULT '' AFTER `sku_all`;"
 );
$u['009'] = array(
  "CREATE TABLE `oms_sell_return_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sell_return_code` varchar(20) NOT NULL COMMENT '退单号',
  `tag_type` varchar(10) NOT NULL COMMENT '标签类型',
  `tag_v` varchar(128) NOT NULL COMMENT '标签值',
  `tag_desc` varchar(128) NOT NULL COMMENT '标签描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sell_return_code` (`sell_return_code`,`tag_type`,`tag_v`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='退单标签表';"
);
$u['003'] = array(
    "ALTER TABLE oms_sell_record ADD COLUMN `sign_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '签收时间';",
    "CREATE TABLE `api_taobao_logistics_trace` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `shop_code` varchar(200) NOT NULL COMMENT '店铺code',
        `tid` varchar(30) NOT NULL COMMENT '交易号',
        `company_name` varchar(30) NOT NULL COMMENT '物流公司名称 ',
        `out_sid` varchar(30) NOT NULL COMMENT '物流单号',
        `action` varchar(100) DEFAULT NULL COMMENT '流转节点',
        `status` varchar(50) DEFAULT NULL COMMENT '订单的物流状态',
        `step_info` text COMMENT '流转信息列表',
        `status_time` datetime DEFAULT NULL COMMENT '最后状态更新时间',
        `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `tid_out_sid` (`tid`,`out_sid`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
    
);
$u['016'] = array(
    "INSERT INTO `alipay_account_item` VALUES ('13','108', '运费险', '2', '2016-02-18 08:06:33');",
);
$u['018'] = array(
    "INSERT INTO `sys_schedule` VALUES ('51', 'erp_barcode_download_cmd', '条码同步', '', '', '0', '3', '仅支持BS3000J产品对接，下载条码档案并更新到系统，此服务240分钟运行一次。', '{\"action\":\"sys/erp_config\\/barcode_download_cmd\"}', '', '0', '0', '0', '14400', '0', 'api', '', '0', '','0');",
    "CREATE TABLE `api_bs3000j_barcode` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
        `SPTM` varchar(100) DEFAULT NULL COMMENT '商品条码',
        `SPDM` varchar(100) DEFAULT NULL COMMENT '商品代码',
        `BYZD1` varchar(255) DEFAULT NULL COMMENT 'SPGG1表Byzd1',
        `GG1DM` varchar(50) DEFAULT NULL COMMENT '颜色代码',
        `GG2DM` varchar(50) DEFAULT NULL COMMENT '尺码代码',
        `update_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未更新，1已更新，2异常',
        `uptime` datetime NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `SPTM` (`SPTM`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='bs3000+条形码更新';",
);
$u['19'] = array(
    "DELETE FROM sys_action WHERE action_id=3020300;",  
);


$u['19'] = array(
    "DELETE FROM sys_action WHERE action_id=3020300;",  
);


$u['bug_019'] = array(
    "UPDATE sys_schedule
SET `DESC` = '此服务默认约1分钟执行一次，与“运营”模块“订单审核规则”配合使用。
(特别说明：商家可以在“订单审核规则”设置若干个审单时间，系统将按照商家的设置，在指定时间自动按规则审单)'
WHERE
	CODE = 'auto_confirm';",  
);

$u['bug_023'] = array(
    "UPDATE sys_schedule SET `name`='上传wms档案' WHERE id=15;"
); 


$u['bug_028'] = array(
    "INSERT INTO `sys_action` VALUES ('7030110', '7030101', 'act', '强制取消', 'wms/wms_mgr/force_cancel', '10', '1', '0', '1','0');",
); 
$u['bug_037'] = array(
    "INSERT INTO `sys_action` VALUES ('7010108','7010102','act','验收且发货','oms/waves_record/do_accept_and_send','1','1','0','1','0');",
);

$u['bug_045'] = array(
    "delete from sys_print_templates where print_templates_code = 'deliver_record'",
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('deliver_record', '发货单模版(新)', NULL, '30', '0', '0', '0', '210', '150', 'pdfFactory Pro', '{\"conf\":\"deliver_record\",\"page_next_type\":\"0\",\"css\":\"tprint_report\",\"page_size\":\"\"}', '<div id=\"report\"><div id=\"report_top\" class=\"group\" title=\"报表头\"><div style=\"height: 50px;\" nodel=\"1\" id=\"row_0\" class=\"row border\"><div style=\"width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;\" id=\"column_0\" class=\"column\">发货单</div><div style=\"height: 50px; line-height: 50px;\" id=\"column_87\" class=\"column\"></div><div style=\"height: 50px; line-height: 50px; width: 180px;\" id=\"column_88\" class=\"column\"><img src=\"assets/tprint/picon/barcode.png\" type=\"1\" class=\"barcode\" style=\"height:50px;width:180px;\" title=\"{@订单号}\"></div></div><div style=\"height: 30px;\" id=\"row_4\" class=\"row border\"><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_9\" class=\"column\">交易号：</div><div style=\"width: 120px; text-align: left; height: 30px; line-height: 30px;\" id=\"column_12\" class=\"column\">{@交易号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_13\" class=\"column\">仓库：</div><div style=\"height: 30px; line-height: 30px; text-align: left; width: 120px;\" id=\"column_14\" class=\"column\">{@发货仓库}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_15\" class=\"column\">商品总数量：</div><div style=\"height: 30px; line-height: 30px; width: 120px; text-align: left;\" id=\"column_16\" class=\"column\">{@商品总数量}</div></div></div><div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\"><table id=\"table_1\" class=\"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"width: 135px;\" class=\"td_title\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_th_69\">商品名称</div></td><td style=\"width: 135px;\" class=\"td_title\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_th_70\">商品编码</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_th_71\">规格1</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px; height: 20px; line-height: 20px;\" class=\"td_column\" id=\"column_th_72\">规格2</div></td><td style=\"width: 120px;\" class=\"td_title\"><div style=\"width: 120px;\" class=\"td_column\" id=\"column_th_73\">条形码</div></td><td style=\"width: 60px;\" class=\"td_title\"><div style=\"width: 60px;\" class=\"td_column\" id=\"column_th_74\">数量</div></td><td style=\"width: 70px;\" class=\"td_title\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_th_75\">库位</div></td></tr><!--detail_list--></table></div><div id=\"report_table_bottom\" class=\"group\" title=\"表格尾\"><div nodel=\"1\" id=\"row_2\" class=\"row border\"><div style=\"width: 450px; text-align: left;\" id=\"column_6\" class=\"column\">合计</div><div style=\"width: 120px; text-align: left;\" id=\"column_22\" class=\"column\">{@支付方式}</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_50\" class=\"column\">{@商品总数量}</div></div></div><div id=\"report_bottom\" class=\"group\" title=\"报表尾\"><div style=\"height: 22px;\" nodel=\"1\" id=\"row_3\" class=\"row border\"><div style=\"width: 100px;\" id=\"column_7\" class=\"column\">打印人：</div><div style=\"height: 22px; line-height: 22px; width: 280px; text-align: left;\" id=\"column_54\" class=\"column\">{@打印人}</div><div style=\"height: 22px; line-height: 22px; width: 80px; text-align: center;\" id=\"column_55\" class=\"column\">打印时间：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_56\" class=\"column\">{@打印时间}</div></div></div></div>', '<tr><td style=\"width: 135px;\" class=\"td_detail\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_td_69\">{#商品名称}</div></td><td style=\"width: 135px;\" class=\"td_detail\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_td_70\">{#商品编码}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_71\">{#规格1}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_72\">{#规格2}</div></td><td style=\"width: 120px;\" class=\"td_detail\"><div style=\"width: 120px;\" class=\"td_column\" id=\"column_td_73\">{#条形码}</div></td><td style=\"width: 60px;\" class=\"td_detail\"><div style=\"width: 60px;\" class=\"td_column\" id=\"column_td_74\">{#数量}</div></td><td style=\"width: 70px;\" class=\"td_detail\"><div style=\"width: 70px;\" class=\"td_column\" id=\"column_td_75\">{#库位}</div></td></tr>', '');"
);

 
