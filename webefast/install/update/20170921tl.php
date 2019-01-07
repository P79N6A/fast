<?php

$u = array();

$u['bug_1718'] = array(
"DROP TABLE IF EXISTS `oms_sell_invoice_record`;",
"CREATE TABLE `oms_sell_invoice_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(128) DEFAULT NULL,
  `deal_code_list` varchar(500) DEFAULT NULL,
  `invoice_no` varchar(128) DEFAULT NULL,
  `is_red` tinyint(3) DEFAULT '0' COMMENT '是否是红票',
  `invoice_type` tinyint(3) DEFAULT '1' COMMENT '1电子，2纸质',
  `value_type` tinyint(3) DEFAULT '0' COMMENT '是否增值发票1为增值发票',
  `invoice_title` varchar(128) DEFAULT NULL,
  `invoice_amount` decimal(20,2) DEFAULT NULL,
  `invoice_time` datetime DEFAULT NULL,
  `invoice_person` varchar(64) DEFAULT NULL,
  `shop_code` varchar(128) DEFAULT NULL,
  `record_time` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0发起开票，1开票成功，2开票失败',
  `error_message` varchar(128) DEFAULT '' COMMENT '错误日志',
  `fpqqlsh` varchar(64) DEFAULT NULL COMMENT '发票请求唯一流水号',
  `kplsh` varchar(64) DEFAULT NULL COMMENT '开票流水号',
  `fwm` varchar(128) DEFAULT NULL COMMENT '防伪码',
  `ewm` varchar(128) DEFAULT NULL COMMENT '二维码',
  `fpzl_dm` varchar(128) DEFAULT NULL COMMENT '发票种类代码',
  `fp_dm` varchar(128) DEFAULT NULL COMMENT '发票代码',
  `kprq` varchar(32) DEFAULT NULL COMMENT '开票日期',
  `kplx` varchar(64) DEFAULT NULL COMMENT '开票类型',
  `hjbhsje` varchar(128) DEFAULT NULL COMMENT '不含税金额',
  `kphjse` varchar(64) DEFAULT NULL COMMENT '税额',
  `pdf_file` text COMMENT 'Base64（ pdf文件）',
  `pdf_url` varchar(500) DEFAULT NULL COMMENT 'PDF下载路径',
  `czdm` varchar(64) DEFAULT NULL COMMENT '操作代码',
  `returncode` varchar(64) DEFAULT NULL COMMENT '结果代码',
  `returnmessage` varchar(128) DEFAULT NULL COMMENT '结果描述',
  PRIMARY KEY (`id`),
  KEY `_index1` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"
,
"DROP TABLE IF EXISTS `oms_sell_invoice`;",
"CREATE TABLE `oms_sell_invoice` (
  `invoice_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
  `deal_code` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `deal_code_list` varchar(500) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
  `customer_code` varchar(20) NOT NULL DEFAULT '' COMMENT '会员代码',
  `buyer_name` varchar(30) DEFAULT NULL COMMENT '买家昵称',
  `receiver_name` varchar(30) DEFAULT NULL COMMENT '收货人名称',
  `company_name` varchar(128) NOT NULL DEFAULT '' COMMENT '公司名称',
  `taxpayers_code` varchar(128) NOT NULL DEFAULT '' COMMENT '纳税人代码',
  `registered_country` bigint(20) DEFAULT NULL COMMENT '国家',
  `registered_province` bigint(20) DEFAULT NULL COMMENT '省',
  `registered_city` bigint(20) DEFAULT NULL COMMENT '市',
  `registered_district` bigint(20) DEFAULT NULL COMMENT '区',
  `registered_street` bigint(20) DEFAULT NULL COMMENT '街道',
  `registered_addr` varchar(100) NOT NULL DEFAULT '' COMMENT '注册详细地址(不包含省市区)',
  `registered_address` varchar(128) NOT NULL DEFAULT '' COMMENT '注册详细地址（包含省市区）',
  `phone` varchar(128) DEFAULT '' COMMENT '注册电话',
  `bank` varchar(64) NOT NULL DEFAULT '' COMMENT '开户银行',
  `bank_account` varchar(64) NOT NULL DEFAULT '' COMMENT '开户银行账号',
  `invoice_amount` decimal(20,2) NOT NULL COMMENT '开票金额',
  `status` tinyint(3) DEFAULT '0' COMMENT '0不开票，1已经发货，可以开票',
  `is_invoice` tinyint(3) DEFAULT '0' COMMENT '0未开票,1正在开票，2开发成功',
  `is_red` tinyint(3) DEFAULT '0' COMMENT '0未开红票,1正在开红票，2开发成功',
  `payable_money` decimal(20,3) DEFAULT '0.000' COMMENT '应付金额',
  `discount_money` decimal(20,3) DEFAULT '0.000' COMMENT '优惠金额',
  `invoice_type` tinyint(3) DEFAULT '1' COMMENT '1电子，2纸质',
  `invoice_title` varchar(128) DEFAULT NULL,
  `invoice_content` varchar(255) DEFAULT NULL,
  `invoice_number` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`invoice_id`),
  UNIQUE KEY `idxu_record_code` (`sell_record_code`) USING BTREE,
  UNIQUE KEY `idxu_deal_code` (`deal_code`),
  KEY `key_status` (`status`) USING BTREE,
  KEY `is_invoice` (`is_invoice`) USING BTREE,
  KEY `is_red` (`is_red`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='销售订单增值发票表';
",
    "CREATE TABLE `js_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `p_id` int(11) DEFAULT NULL,
  `shop_code` varchar(128) DEFAULT NULL,
  `invoice_type` tinyint(32) DEFAULT '1' COMMENT '1电子，2普通纸质，3增值',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

",
);


$u['tl_1608'] = array(
    "ALTER TABLE `oms_sell_record_detail` ADD COLUMN `platform_name` varchar(200) DEFAULT NULL COMMENT '平台商品名称';",
);
$u['1607'] = array(
    "INSERT INTO `sys_print_templates` (`print_templates_id`, `print_templates_code`, `print_templates_name`, `company_code`, `type`, `is_buildin`, `offset_top`, `offset_left`, `paper_width`, `paper_height`, `printer`, `template_val`, `template_body`, `template_body_replace_key`, `template_body_replace`, `template_body_default`, `new_old_type`) VALUES ('211', 'guarantee_flash', '质保单', NULL, '2', '1', '0', '0', '0', '0', '无', '', '<?xml version=\"1.0\" encoding=\"utf-8\"?><ReportSettings version=\"1.2\"><PageWidth>29.7</PageWidth><PageHeight>21</PageHeight><LeftMargin>1</LeftMargin><RightMargin>1</RightMargin><TopMargin>1.5</TopMargin><BottomMargin>1.5</BottomMargin><PageHeaderRepeat>false</PageHeaderRepeat><PageFooterRepeat>false</PageFooterRepeat><TableHeaderRepeat>true</TableHeaderRepeat><ShowPageNumber>true</ShowPageNumber><PageNumberFormat>【第{0}页 共{1}页】</PageNumberFormat><PageHeaderSettings><ItemSetting type=\"CaptionRowSetting\"><Height>1.3</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>质 量 保 证 书</Value><Style><TextAlign>center</TextAlign><FontSize>18</FontSize><FontBold>true</FontBold></Style><Width>22.6</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@订单号</Value><Control type=\"BarCode\"><Set name=\"eBarCodeType\">CODE128</Set><Set name=\"nBarCodeScaleX\">1</Set><Set name=\"nBarCodeScaleY\">1</Set></Control><Width>5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>请输入内容...</Value><Width>0</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><Height>0.8</Height><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>客户：</Value><Width>2.3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@顾客</Value><Width>6.6</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>1.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>8.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>订单号：</Value><Width>2.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@订单号</Value><Width>5</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageHeaderSettings><PageFooterSettings><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>店名：</Value><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@店名</Value><Width>14.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>结算信息</Value><Width>3.8</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>现金：</Value><Width>1.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>4.5</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>联系电话：</Value><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>4.2</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>14.4</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>刷卡：</Value><Width>1.5</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@刷卡</Value><Width>16.1</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Value>备注：</Value><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>平台交易号：</Value><Width>2.7</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@备注</Value><Width>15.6</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>抵用券：</Value><Width>1.8</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@抵用卷</Value><Width>2.9</Width></ItemSetting></CaptionCellSettings></ItemSetting><ItemSetting type=\"CaptionRowSetting\"><CaptionCellSettings><ItemSetting type=\"CaptionCellSetting\"><Width>3</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Width>17.9</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>购买日期：</Value><Width>2.2</Width></ItemSetting><ItemSetting type=\"CaptionCellSetting\"><Value>=@购买时间</Value><Width>2.7</Width></ItemSetting></CaptionCellSettings></ItemSetting></PageFooterSettings><TableColumnSettings><ItemSetting type=\"TableColumnSetting\"><Width>3.7</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>3.5</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.8</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.8</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.9</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>2.8</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>1.6</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>3.3</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>3.3</Width></ItemSetting><ItemSetting type=\"TableColumnSetting\"><Width>3.3</Width></ItemSetting></TableColumnSettings><TableHeaderSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>饰品编号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>证书号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>饰品信息（主石、辅石、总重量）</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style><ColSpan>6</ColSpan></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"></ItemSetting><ItemSetting type=\"TableCellSetting\"></ItemSetting><ItemSetting type=\"TableCellSetting\"></ItemSetting><ItemSetting type=\"TableCellSetting\"></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>数量（件）</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>金额（元）</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableHeaderSettings><TableDetailSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Value>=#饰品编号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#证书号</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#主石重量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>(主石重量)/</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#辅石重量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>(辅石重量)/</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#总重量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>(总重量)</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#数量</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#金额</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder><BottomBorder>false</BottomBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableDetailSettings><TableFooterSettings><ItemSetting type=\"TableRowSetting\"><TableCellSettings><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>合计（元）：</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting><ItemSetting type=\"TableCellSetting\"><Value>=#合计</Value><Style><LeftBorder>false</LeftBorder><RightBorder>false</RightBorder><TopBorder>false</TopBorder></Style></ItemSetting></TableCellSettings></ItemSetting></TableFooterSettings><TableGroupSettings><ItemSetting type=\"TableGroupSetting\"></ItemSetting></TableGroupSettings></ReportSettings>', NULL, '', '', '');",

    "CREATE TABLE `goods_unique_code_tl` (
  `jewelry_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unique_code` varchar(30) DEFAULT '' COMMENT '唯一码',
  `barcode` varchar(30) DEFAULT '' COMMENT '商品条形码',
  `factory_code` varchar(30) DEFAULT '' COMMENT '厂家款号',
  `tongling_code` varchar(30) DEFAULT '' COMMENT '通灵款',
  `goods_name` varchar(45) NOT NULL DEFAULT '' COMMENT '饰品名称',
  `relative_purity` varchar(30) DEFAULT '' COMMENT '成色',
  `relative_purity_of_gold` varchar(30) DEFAULT '' COMMENT '金成色',
  `international_num` varchar(45) DEFAULT '' COMMENT '国际证书号',
  `check_station_num` varchar(45) DEFAULT '' COMMENT '检测站证书号',
  `identity_num` varchar(20) DEFAULT '' COMMENT '身份证',
  `jewelry_brand` varchar(30) DEFAULT '' COMMENT '品牌',
  `jewelry_brand_child` varchar(30) DEFAULT '' COMMENT '子品牌',
  `metal_color` varchar(20) DEFAULT '' COMMENT '金属颜色',
  `jewelry_color` varchar(20) DEFAULT '' COMMENT '颜色',
  `jewelry_clarity` varchar(20) DEFAULT '' COMMENT '净度',
  `jewelry_cut` varchar(20) DEFAULT '' COMMENT '切工',
  `pri_diamond_weight` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '主钻石重量',
  `pri_diamond_count` decimal(10,4) DEFAULT '0.0000' COMMENT '主石数量',
  `ass_diamond_weight` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '辅钻石重量',
  `ass_diamond_count` decimal(10,4) DEFAULT '0.0000' COMMENT '辅石数量',
  `total_weight` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '珠宝总重量',
  `jewelry_type` varchar(20) DEFAULT '' COMMENT '类别',
  `ring_size` varchar(20) DEFAULT '0' COMMENT '手寸长度',
  `total_price` decimal(10,2) DEFAULT '0.00' COMMENT '销售含税价',
  `credential_type` varchar(30) DEFAULT '' COMMENT '证书类型',
  `credential_weight` decimal(10,4) DEFAULT '0.0000' COMMENT '证书重量',
  `record_num` varchar(30) DEFAULT '' COMMENT '货单号',
  `short_name` varchar(30) DEFAULT '' COMMENT '饰品简称',
  `user_defined_property_1` varchar(45) DEFAULT '' COMMENT '自定义属性1',
  `user_defined_property_2` varchar(45) DEFAULT '' COMMENT '自定义属性2',
  `user_defined_property_3` varchar(45) DEFAULT '' COMMENT '自定义属性3',
  `user_defined_property_4` varchar(45) DEFAULT '' COMMENT '自定义属性4',
  `user_defined_property_5` varchar(45) DEFAULT '' COMMENT '自定义属性5',
  `user_defined_property_6` varchar(45) DEFAULT '' COMMENT '自定义属性6',
  `user_defined_property_7` varchar(45) DEFAULT '' COMMENT '自定义属性7',
  `user_defined_property_8` varchar(45) DEFAULT '' COMMENT '自定义属性8',
  `status` tinyint(3) unsigned NOT NULL COMMENT '0未出库 1已出库',
  `sku` varchar(30) DEFAULT '' COMMENT '系统sku码',
  `guarantee_num` varchar(30) NOT NULL DEFAULT '' COMMENT '质保证书号',
  `out_time` datetime NOT NULL COMMENT '出库时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`jewelry_id`),
  UNIQUE KEY `unique_code` (`unique_code`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COMMENT='通灵珠宝唯一码';",


    "ALTER TABLE `js_fapiao` ADD COLUMN `invoice_config_name` varchar(128) NOT NULL DEFAULT '' COMMENT '配置名称';",
    "ALTER TABLE `js_fapiao` ADD COLUMN `HSJ_BZ` tinyint(3) NOT NULL DEFAULT '1' COMMENT '含税价标志 0不含税价 1含税价';",
    //增加唯一码商品税收编码字段
    "ALTER TABLE `goods_unique_code_tl` ADD COLUMN `good_revenue_code` varchar(30) NOT NULL DEFAULT '' COMMENT '商品税收编码' after `tongling_code`;",
    "ALTER TABLE `goods_unique_code_tl` MODIFY `good_revenue_code` varchar(30) NOT NULL DEFAULT '' COMMENT '商品税收分类编码' after `barcode`;",

);

//通灵
$u['tl_1607']=array(
   
    //商品唯一码编辑删除
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020208', '5030300', 'act', '编辑', 'prm/goods_unique_code_tl/detail&action=do_edit', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020209', '5030300', 'act', '删除', 'prm/goods_unique_code_tl/do_delete', '0', '1', '0', '1', '0');",
    
    //系统集成开票信息
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('17000000', '0', 'cote', '开票管理', 'api_invoice_manage', '80', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('17000100', '91020214', 'url', '开票主体配置', 'sys/invoice/JsFapiao/do_list', '0', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020210', '91020214', 'url', '订单开票列表', 'oms/invoice/order_invoice/do_list', '0', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020212', '91020214', 'url', '订单开票查询', 'oms/invoice/order_invoice/do_seach', '0', '1', '0', '1', '1');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('91020214', '17000000', 'group', '订单开票', 'order_invoice_manage', '77', '1', '0', '1', '1');",
   

//积分报表菜单
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21004000', '21000000', 'group', '积分报表', 'integral_statistic', '6', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21004010', '21004000', 'url', '积分报表统计', 'rpt/integral_report/do_list', '1', '1', '0', '1', '0');",

    //淘宝中间表添加优惠字段
    "ALTER TABLE api_taobao_trade ADD COLUMN coupon_fee VARCHAR (100) DEFAULT '0' COMMENT '红包'",
    "ALTER TABLE api_taobao_trade ADD COLUMN alipay_point VARCHAR (100) DEFAULT '0' COMMENT '集分宝'",
    //京东中间表添加优惠字段
    "ALTER TABLE api_jingdong_trade ADD COLUMN balance_used decimal(10,2) DEFAULT '0.00' COMMENT '京东余额'",

);

//新增
//通灵
$u['tl_1607_1']=array(
"ALTER TABLE `oms_sell_invoice`
ADD COLUMN `is_company`  tinyint(3) NULL DEFAULT 0 COMMENT '是否企业开票0，1' AFTER `receiver_name`;",
    "ALTER TABLE `oms_sell_invoice`
ADD COLUMN `shop_code`  varchar(128) NULL AFTER `receiver_name`;
",

    //9.22
    //在发票表中添加URL字段
    "ALTER TABLE js_fapiao ADD COLUMN electron_URL VARCHAR (128) DEFAULT '' COMMENT '电子发票URL'",
    "ALTER TABLE js_fapiao ADD COLUMN paper_URL VARCHAR (128) DEFAULT '' COMMENT '纸质发票URL'",
    "ALTER TABLE js_fapiao ADD COLUMN is_sea TINYINT (3) DEFAULT '0' COMMENT '是否是海洋石油发票 0.不是 1.是'",

    
    //修改发票表
"DROP TABLE IF EXISTS `js_fapiao`;",
    " CREATE TABLE `js_fapiao` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nsrsbh` varchar(32) NOT NULL COMMENT '企业识别号,开票方识别号',
  `nsrmc` varchar(256) DEFAULT NULL COMMENT '企业名称,开票方名称',
  `nsrdzdah` varchar(32) DEFAULT NULL COMMENT '--开票方电子档案号',
  `swjg_dm` varchar(32) DEFAULT NULL COMMENT '--税务机构代码',
  `dkbz` tinyint(3) DEFAULT '0' COMMENT '代开标志1、 自开(0)2、 代开(1)',
  `xhf_nsrsbh` varchar(32) DEFAULT NULL COMMENT '--销货方识别号',
  `xhfmc` varchar(256) DEFAULT NULL COMMENT '--销货方名称',
  `xhf_dz` varchar(128) DEFAULT NULL COMMENT '销货方地址',
  `xhf_dh` varchar(32) DEFAULT NULL COMMENT '销货方电话',
  `xhf_yhzh` varchar(128) DEFAULT NULL COMMENT '销货方银行账号',
  `appid` varchar(32) DEFAULT NULL COMMENT '--应用标识',
  `username` varchar(64) DEFAULT NULL COMMENT '平台编码',
  `requestcode` varchar(64) DEFAULT NULL COMMENT '数据交换请求发起方代码',
  `password` varchar(128) DEFAULT NULL COMMENT '密码',
  `taxpayerid` varchar(128) DEFAULT NULL COMMENT '--纳税人识别号',
  `authorizationcode` varchar(128) DEFAULT NULL COMMENT '纳税人授权码',
  `kpy` varchar(32) DEFAULT NULL COMMENT '开票员',
  `sky` varchar(32) DEFAULT NULL COMMENT '--收款员',
  `fhr` varchar(32) DEFAULT NULL COMMENT '--复核人',
  `hy_dm` varchar(32) DEFAULT NULL COMMENT '--行业代码',
  `hy_mc` varchar(64) DEFAULT NULL COMMENT '--行业名称',
  `is_same_tax` tinyint(3) DEFAULT '0' COMMENT '0,1',
  `tax_rate` int(11) DEFAULT '0' COMMENT '税率:17、13、11、6、5、4、3、0',
  `is_paper_max` tinyint(3) DEFAULT '0' COMMENT '纸质发票是否这周最高额度',
  `paper_max` decimal(20,2) DEFAULT NULL COMMENT '最高额度',
  `is_electron_max` tinyint(3) DEFAULT '0' COMMENT '电子发票是否最高额度',
  `electron_max` decimal(20,2) DEFAULT NULL COMMENT '最高额度',
  `invoice_config_name` varchar(128) DEFAULT '' COMMENT '配置名称',
  `hsj_bz` tinyint(3) NOT NULL DEFAULT '1' COMMENT '含税价标志 0不含税价 1含税价',
  `electron_url` varchar(128) DEFAULT '' COMMENT '电子发票URL',
  `paper_url` varchar(128) DEFAULT '' COMMENT '纸质发票URL',
  `is_sea` tinyint(3) DEFAULT '0' COMMENT '是否是海洋石油发票 0.不是 1.是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

    
"DROP TABLE IF EXISTS `oms_sell_invoice_record`;",
"CREATE TABLE `oms_sell_invoice_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(128) DEFAULT NULL,
  `deal_code_list` varchar(500) DEFAULT NULL,
  `invoice_no` varchar(128) DEFAULT NULL,
  `is_red` tinyint(3) DEFAULT '0' COMMENT '是否是红票',
  `invoice_type` tinyint(3) DEFAULT '1' COMMENT '1电子，2纸质',
  `value_type` tinyint(3) DEFAULT '0' COMMENT '是否增值发票1为增值发票',
  `invoice_title` varchar(128) DEFAULT NULL,
  `invoice_amount` decimal(20,2) DEFAULT NULL,
  `invoice_time` datetime DEFAULT NULL,
  `invoice_person` varchar(64) DEFAULT NULL,
  `shop_code` varchar(128) DEFAULT NULL,
  `record_time` datetime DEFAULT NULL,
  `status` tinyint(4) DEFAULT '0' COMMENT '0发起开票，1开票成功，2开票失败',
  `error_message` varchar(128) DEFAULT '' COMMENT '错误日志',
  `fpqqlsh` varchar(64) DEFAULT NULL COMMENT '发票请求唯一流水号',
  `kplsh` varchar(64) DEFAULT NULL COMMENT '开票流水号',
  `fwm` varchar(128) DEFAULT NULL COMMENT '防伪码',
  `ewm` varchar(128) DEFAULT NULL COMMENT '二维码',
  `fpzl_dm` varchar(128) DEFAULT NULL COMMENT '发票种类代码',
  `fp_dm` varchar(128) DEFAULT NULL COMMENT '发票代码',
  `kprq` varchar(32) DEFAULT NULL COMMENT '开票日期',
  `kplx` varchar(64) DEFAULT NULL COMMENT '开票类型',
  `hjbhsje` varchar(128) DEFAULT NULL COMMENT '不含税金额',
  `kphjse` varchar(64) DEFAULT NULL COMMENT '税额',
  `pdf_file` text COMMENT 'Base64（ pdf文件）',
  `pdf_url` varchar(500) DEFAULT NULL COMMENT 'PDF下载路径',
  `czdm` varchar(64) DEFAULT NULL COMMENT '操作代码',
  `returncode` varchar(64) DEFAULT NULL COMMENT '结果代码',
  `returnmessage` varchar(128) DEFAULT NULL COMMENT '结果描述',
  PRIMARY KEY (`id`),
  KEY `_index1` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",


    //在开票详情表中添加开票备注字段
    "ALTER TABLE `oms_sell_invoice_record` ADD COLUMN `invoice_remark` varchar(128) DEFAULT '' COMMENT '开票备注';",
    "ALTER TABLE `oms_sell_invoice_record`
MODIFY COLUMN `ewm`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '二维码' AFTER `fwm`;",
    //添加冲红原因字段 9.29添加
    "ALTER TABLE `oms_sell_invoice_record` ADD COLUMN `chyy` varchar(255) DEFAULT '' COMMENT '冲红原因';",
    
    //10.11添加
);

