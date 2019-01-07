<?php

$u = array();

//增加赠品策略 创建时间字段
$u['FSF-1602'] = array(
	"ALTER TABLE `sys_print_templates`
ADD COLUMN `company_code`  varchar(128) NULL AFTER `print_templates_name`;",
    
"update base_express_company set rule='^((779|359|528|751|358|618|680|778|768|688|689|618|828|988|118|888|571|518|010|628|205|880|717|718|728|738|761|762|763|701|757|719)[0-9]{9})$|^((2008|2010|8050|7518)[0-9]{8})$|^((36)[0-9]{10})$' where company_code='ZTO';"
);
//支付宝对账
$u['FSF-1645'] = array(
    
"alter table api_taobao_alipay add column account_month date NOT NULL DEFAULT '0000-00-00' COMMENT '财务账期';",
"alter table api_taobao_alipay add column `check_accounts_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '核销状态 0未对账 10已核销 20部分核销 30虚拟核消  40人工核销 50核销失败';",
"alter table api_taobao_alipay add column `check_accounts_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '核销时间';",
"alter table api_taobao_alipay add column `check_accounts_user_code` varchar(20) NOT NULL DEFAULT '' COMMENT '核销人';    ",
       
    
"alter table oms_sell_settlement add column sell_month date NOT NULL DEFAULT '0000-00-00' COMMENT '业务账期';",
"alter table api_taobao_alipay add column account_month_ym varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '财务账期--仅年月';",
"alter table oms_sell_settlement add column account_month_ym varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '财务账期--仅年月';",
"alter table api_taobao_alipay add column sell_month_ym varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '业务账期--仅年月';",
"alter table oms_sell_settlement add column sell_month_ym varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '业务账期--仅年月';",
"alter table api_taobao_alipay add index idx_account_momth(shop_code,check_accounts_status,account_month_ym,sell_month_ym);",
"alter table oms_sell_settlement add index idx_account_momth(shop_code,check_accounts_status,account_month_ym,sell_month_ym);",
"CREATE TABLE `report_alipay` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `account_item` varchar(20) NOT NULL,
  `is_account_in` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1是收入 2是支出 3收入分解',
  `account_month_ym` varchar(7) NOT NULL,
  `je` float(10,2) NOT NULL,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8;",
    "CREATE TABLE `report_sell_settlement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `account_month_ym` varchar(7) NOT NULL,
  `ds_je` float(10,2) NOT NULL COMMENT '本月已核销的应收金额',
  `ys_je` float(10,2) NOT NULL COMMENT '期末应收款',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
",
    "INSERT INTO `sys_action` VALUES ('9030400','9030000','url','零售结算交易核销分析','acc/report_sell_settlement/do_list','4','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES ('9030200','9030000','url','支付宝核销分析','acc/report_alipay/do_list','2','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES ('9030600','9030000','url','对账科目','acc/alipay_account_item/do_list','6','1','0','1','0');",
    "CREATE TABLE `alipay_account_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `account_item` varchar(20) NOT NULL,
  `in_out_flag` tinyint(1) NOT NULL,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;",
    "INSERT INTO `alipay_account_item` VALUES ('1', '001', '交易收款', '1', '2015-09-01 08:02:10');",
"INSERT INTO `alipay_account_item` VALUES ('2', '002', '天猫佣金退款', '1', '2015-09-01 08:05:26');",
"INSERT INTO `alipay_account_item` VALUES ('3', '003', '代扣交易退回积分', '1', '2015-09-01 08:05:27');",
"INSERT INTO `alipay_account_item` VALUES ('4', '004', '淘宝客佣金退款', '1', '2015-09-01 08:05:27');",
"INSERT INTO `alipay_account_item` VALUES ('5', '005', '信用卡支付服务费退款', '1', '2015-09-01 08:05:29');",
"INSERT INTO `alipay_account_item` VALUES ('6', '006', '天猫佣金', '2', '2015-09-01 08:06:30');",
"INSERT INTO `alipay_account_item` VALUES ('7', '007', '代扣返点积分', '2', '2015-09-01 08:06:30');",
"INSERT INTO `alipay_account_item` VALUES ('8', '008', '淘宝客佣金代扣款', '2', '2015-09-01 08:06:31');",
"INSERT INTO `alipay_account_item` VALUES ('9', '009', '信用卡支付服务费', '2', '2015-09-01 08:06:32');",
"INSERT INTO `alipay_account_item` VALUES ('10', '010', '售后维权扣款', '2', '2015-09-01 08:06:33');",
    
"INSERT INTO `sys_schedule` VALUES ('45', 'alipay_accounts_cmd', '支付宝对账', '', '', '0', '1', '支付宝流水与系统数据对账', '{\"action\":\"api/finance/alipay_account_cmd\"}', '', '0', '0', '0', '86400', '0', 'api', '', '0', '0');",  
 
);
$u['FSF-1624']= array(
		"insert into `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
			values('','update_goods_listing','oms_taobao','商品自动上架','radio','[\"关闭\",\"开启\"]','0','5','1-开启 0-关闭','2015-09-01 13:17:36','在库的商品如果系统同步的商品库存大于0，则系统自动将商品上架，默认关闭');"
		);
$u['FSF-1646'] = array(
		"ALTER TABLE api_taobao_fx_order ADD column `fenxiao_oid` varchar(50) DEFAULT NULL COMMENT '子采购订单id';",
		"ALTER TABLE api_taobao_fx_order DROP KEY id;",
		"ALTER TABLE api_taobao_fx_order ADD KEY fenxiao_id(fenxiao_id);",
		"ALTER TABLE api_taobao_fx_order ADD UNIQUE KEY fenxiao_oid(fenxiao_oid);",
		);
$u['FSF-1635'] = array("delete from sys_user_pref where iid = 'sell_record_fh_list/table'");
$u['FSF-1654'] = array(
		"INSERT INTO `sys_schedule` VALUES ('47', 'fx_inv_upload_cmd', '淘宝分销商品库存同步', '', '', '0', '1', '库存同步数量与旗舰店一致，此服务30分钟运行一次', '{}', '', '0', '0', '0', '1800', '0', 'api', '', '0', '0');",
		"ALTER TABLE api_taobao_fx_product_sku ADD COLUMN `inv_up_time` datetime DEFAULT NULL COMMENT '向第三方平台库存上传时间';",
		"ALTER TABLE api_taobao_fx_product_sku ADD KEY outer_id(outer_id);"
);