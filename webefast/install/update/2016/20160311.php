<?php
$u = array();
$u['59'] = array(
    "INSERT INTO `sys_action` VALUES ('12030200','12030000','url','BS3000J商品库存维护','erp/bs3000j_inv_sync/trade_list','1','1','0','1','1');",
    "ALTER TABLE api_bs3000j_item_quantity ADD COLUMN `sku` varchar(255) DEFAULT '' COMMENT 'sku';",
    "ALTER TABLE api_bs3000j_item_quantity ADD COLUMN `barcode` varchar(255) DEFAULT '' COMMENT 'barcode';",
);
$u['055'] = array(
    "INSERT INTO `alipay_account_item` VALUES ('14','109', '花呗支付服务费', '2', '2016-03-01 08:06:33');",
    
);
$u['057'] = array(
    "ALTER TABLE api_taobao_goods ADD COLUMN `cat` varchar(255) DEFAULT NULL COMMENT '分类';",
    "ALTER TABLE api_taobao_goods ADD COLUMN `brand` varchar(255) DEFAULT NULL COMMENT '品牌';",
    "ALTER TABLE api_goods ADD COLUMN `cat` varchar(255) DEFAULT NULL COMMENT '分类';",
    "ALTER TABLE api_goods ADD COLUMN `brand` varchar(255) DEFAULT NULL COMMENT '品牌';",
    "CREATE TABLE `api_taobao_itemcats` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `cid` varchar(50) NOT NULL COMMENT '商品所属类目ID',
        `parent_cid` varchar(50) NOT NULL DEFAULT '0' COMMENT '父类目ID=0时，代表的是一级的类目',
        `name` varchar(255) NOT NULL COMMENT '类目名称',
        `is_parent` tinyint(1) NOT NULL COMMENT '该类目是否为父类目(即：该类目是否还有子类目)',
        `status` varchar(20) DEFAULT NULL COMMENT '状态。可选值:normal(正常),deleted(删除)',
        `sort_order` tinyint(11) DEFAULT NULL COMMENT '排列序号，表示同级类目的展现次序，如数值相等则按名称次序排列。取值范围:大于零的整数',
        `taosir_cat` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否度量衡类目',
        PRIMARY KEY (`id`),
        UNIQUE KEY `cid` (`cid`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
    
);

$u['43'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace`, `template_body_default`) VALUES ('sell_return', '售后服务单模版', NULL, '30', '0', '0', '0', '210', '150', 'Microsoft XPS Document Writer', '{\"conf\":\"sell_return\",\"page_next_type\":\"1\",\"css\":\"tprint_report\",\"page_size\":\"\"}', '<div id=\"report\"><div id=\"report_top\" class=\"group\" title=\"报表头\"><div style=\"height: 50px;\" nodel=\"1\" id=\"row_0\" class=\"row border\"><div style=\"width: 400px; font-size: 24px; height: 50px; line-height: 50px; text-align: right;\" id=\"column_0\" class=\"column\">退单商品清单</div><div style=\"height: 50px; line-height: 50px;\" id=\"column_87\" class=\"column\"></div><div style=\"height: 50px; line-height: 50px; width: 180px;\" id=\"column_88\" class=\"column\"><img src=\"assets/tprint/picon/barcode.png\" type=\"1\" class=\"barcode\" style=\"height:50px;width:180px;\" title=\"{@退单号}\"></div></div><div style=\"height: 30px;\" id=\"row_4\" class=\"row border\"><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_9\" class=\"column\">退单编号：</div><div style=\"width: 120px; text-align: left; height: 30px; line-height: 30px;\" id=\"column_12\" class=\"column\">{@退单号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_13\" class=\"column\">关联订单号：</div><div style=\"height: 30px; line-height: 30px; text-align: left; width: 120px;\" id=\"column_14\" class=\"column\">{@关联订单号}</div><div style=\"height: 30px; line-height: 30px; width: 100px; text-align: right;\" id=\"column_15\" class=\"column\">创建时间：</div><div style=\"height: 30px; line-height: 30px; width: 120px; text-align: left;\" id=\"column_16\" class=\"column\">{@创建时间}</div></div><div id=\"row_7\" class=\"row border\"><div style=\"height: 22px; line-height: 22px; text-align: right; width: 100px;\" id=\"column_116\" class=\"column\">买家：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_139\" class=\"column\">{@退货人名称}</div><div style=\"height: 22px; line-height: 22px; width: 100px; text-align: right;\" id=\"column_140\" class=\"column\">手机：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_141\" class=\"column\">{@电话}</div><div style=\"height: 22px; line-height: 22px; width: 100px; text-align: right;\" id=\"column_142\" class=\"column\">商店名称：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_143\" class=\"column\">{@商店名称}</div></div><div id=\"row_8\" class=\"row border\"><div style=\"height: 22px; line-height: 22px; width: 100px; text-align: right;\" id=\"column_117\" class=\"column\">仓库：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_166\" class=\"column\">{@退货仓库}</div><div style=\"height: 22px; line-height: 22px; width: 100px; text-align: right;\" id=\"column_167\" class=\"column\">应退款：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_168\" class=\"column\">{@实际退款总金额}</div><div style=\"height: 22px; line-height: 22px; width: 100px; text-align: right;\" id=\"column_169\" class=\"column\">退单类型：</div><div style=\"height: 22px; line-height: 22px; text-align: left; width: 120px;\" id=\"column_170\" class=\"column\">{@退单类型}</div></div><div id=\"row_11\" class=\"row border\"><div style=\"height: 22px; line-height: 22px; text-align: right; width: 100px;\" id=\"column_192\" class=\"column\">收货地址：</div><div style=\"height: 22px; line-height: 22px; width: 340px; text-align: left;\" id=\"column_193\" class=\"column\">{@退货地址}</div><div style=\"height: 22px; line-height: 22px; text-align: right; width: 100px;\" id=\"column_194\" class=\"column\">原订单支付方式：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_195\" class=\"column\">{@支付方式}</div></div></div><div type=\"table\" nodel=\"1\" id=\"report_table_body\" class=\"group\" title=\"表格\"><table id=\"table_1\" class=\"table\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\"><tr><td style=\"width: 150px;\" class=\"td_title\"><div style=\"width: 150px;\" class=\"td_column\" id=\"column_th_69\">商品名称</div></td><td style=\"width: 135px;\" class=\"td_title\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_th_70\">商品编码</div></td><td style=\"width: 130px;\" class=\"td_title\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_th_71\">条形码</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px; height: 20px; line-height: 20px;\" class=\"td_column\" id=\"column_th_72\">单价</div></td><td style=\"width: 90px;\" class=\"td_title\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_th_73\">数量</div></td><td style=\"width: 80px;\" class=\"td_title\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_th_74\">金额</div></td></tr><!--detail_list--></table></div><div id=\"report_table_bottom\" class=\"group\" title=\"表格尾\"><div style=\"height: 0px;\" nodel=\"1\" id=\"row_2\" class=\"row border\"></div></div><div id=\"report_bottom\" class=\"group\" title=\"报表尾\"><div style=\"height: 22px;\" nodel=\"1\" id=\"row_3\" class=\"row border\"><div style=\"width: 100px;\" id=\"column_7\" class=\"column\"></div><div style=\"height: 22px; line-height: 22px; width: 280px; text-align: left;\" id=\"column_54\" class=\"column\"></div><div style=\"height: 22px; line-height: 22px; width: 80px; text-align: center;\" id=\"column_55\" class=\"column\">打印时间：</div><div style=\"height: 22px; line-height: 22px; width: 120px; text-align: left;\" id=\"column_56\" class=\"column\">{@打印时间}</div></div></div></div>', '<tr><td style=\"width: 150px;\" class=\"td_detail\"><div style=\"width: 150px;\" class=\"td_column\" id=\"column_td_69\">{#商品名称}</div></td><td style=\"width: 135px;\" class=\"td_detail\"><div style=\"width: 135px;\" class=\"td_column\" id=\"column_td_70\">{#商品编码}</div></td><td style=\"width: 130px;\" class=\"td_detail\"><div style=\"width: 130px;\" class=\"td_column\" id=\"column_td_71\">{#条形码}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_72\">{#单价}</div></td><td style=\"width: 90px;\" class=\"td_detail\"><div style=\"width: 90px;\" class=\"td_column\" id=\"column_td_73\">{#完成数量}</div></td><td style=\"width: 80px;\" class=\"td_detail\"><div style=\"width: 80px;\" class=\"td_column\" id=\"column_td_74\">{#均摊金额}</div></td></tr>', '');"
);

$u['083'] = array(
    "CREATE TABLE `sys_user_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_code` varchar(128) NOT NULL DEFAULT '' COMMENT '用户代码',
  `task_code` varchar(128) NOT NULL DEFAULT '' COMMENT '任务代码',
  `task_id` int(11) DEFAULT '0' COMMENT '任务ID',
  `content` text,
  `msg` text,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`user_code`,`task_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;",
);
$u['045'] = array(
   "ALTER TABLE oms_sell_record_detail DROP KEY idxu_key",
   "ALTER TABLE oms_sell_record_detail ADD UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_delete`,`is_gift`);",
   "ALTER TABLE oms_sell_record_notice_detail DROP KEY idxu_key",
   "ALTER TABLE oms_sell_record_notice_detail ADD UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_gift`);",
   "ALTER TABLE oms_deliver_record_detail DROP KEY idxu_key",
   "ALTER TABLE oms_deliver_record_detail ADD UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_gift`,`waves_record_id`);",
);

