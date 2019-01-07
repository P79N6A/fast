<?php

$u = array();

//wq  订单合并增加规则换货单/拆分单和正常单允许合并
$u['FSF-1858'] = array(
    "update  order_combine_strategy set rule_scene_value=0 where   rule_status_value=0;",
     "insert into order_combine_strategy (rule_code,rule_status_value,rule_desc,rule_scene_value)
VALUES ('order_combine_is_change',1,'换货单参与合并','0'), ('order_combine_is_split',1,'拆分单参与合并','0');",
);

$u['FSF-1854'] = array("INSERT INTO `sys_action` VALUES ('6020300', '6020000', 'url', '商品进销存分析', 'rpt/report_jxc/do_list&url_id=inv', '3', '1', '0', '1','0');");

$u['FSF-1852'] = array(
	"INSERT INTO `sys_action` VALUES ('7010109','7010102','act','整单发货/批量发货','oms/waves_record/waves_batch_send','60','1','0','1','0');"
);

$u['FSF-1862'] = array(
    "CREATE TABLE `op_policy_store` (
  `policy_store_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL,
  `sort` int(11) unsigned NOT NULL COMMENT '优先级：数据越大优先级越高',
  PRIMARY KEY (`policy_store_id`),
  UNIQUE KEY `_key` (`store_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略';
",
    "CREATE TABLE `op_policy_store_area` (
  `policy_store_area_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL COMMENT '策略ID',
  `area_id` bigint(20) unsigned NOT NULL COMMENT '地区ID',
  PRIMARY KEY (`policy_store_area_id`),
  UNIQUE KEY `_key` (`store_code`,`area_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=716 DEFAULT CHARSET=utf8 COMMENT='仓库策略-区域';
",
 "INSERT INTO `sys_action` VALUES ('3020500', '3020000', 'url', '仓库适配策略', 'op/policy_store/do_list', '6', '1', '0', '1','0');",
 "ALTER TABLE `op_policy_store`
MODIFY COLUMN `sort`  int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '优先级：数据越大优先级越高' AFTER `store_code`,
ADD COLUMN `area_desc`  text NULL AFTER `store_code`;
", 
);
$u['FSF-1866'] = array(
		"ALTER TABLE op_policy_express_rule MODIFY `first_weight` decimal(10,2) unsigned NOT NULL COMMENT '首重-千克';",
		"ALTER TABLE op_policy_express_rule MODIFY `first_weight_price` decimal(10,2) unsigned NOT NULL COMMENT '首重单价';",
		"ALTER TABLE op_policy_express_rule MODIFY `added_weight` decimal(10,2) unsigned NOT NULL COMMENT '续重-千克';",
		"ALTER TABLE op_policy_express_rule MODIFY `added_weight_price` decimal(10,2) unsigned NOT NULL COMMENT '续重单价';",
		"ALTER TABLE op_policy_express_rule MODIFY `added_weight_type` char(20)  NOT NULL DEFAULT 'g0' COMMENT '续重规则 g0实重 g1半重 g2过重';",
		"ALTER TABLE oms_deliver_record_package ADD COLUMN `is_weigh` int(4) NOT NULL DEFAULT '0' COMMENT '称重 0未称重 1称重';",
		"ALTER TABLE oms_deliver_record_package ADD COLUMN `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量';",
		"ALTER TABLE oms_deliver_record_package ADD COLUMN `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用';",
		"ALTER TABLE oms_deliver_record_package ADD COLUMN `weigh_time` datetime DEFAULT NULL COMMENT '称重时间';",
		"ALTER TABLE oms_deliver_record_package ADD COLUMN `weigh_person` varchar(20) NOT NULL DEFAULT '' COMMENT '称重人';",
		"ALTER TABLE oms_sell_record ADD COLUMN `weigh_person` varchar(20) NOT NULL DEFAULT '' COMMENT '称重人';",
		"ALTER TABLE oms_deliver_record ADD COLUMN `weigh_person` varchar(20) NOT NULL DEFAULT '' COMMENT '称重人';",
		"ALTER TABLE oms_sell_record MODIFY `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量';",
		"ALTER TABLE oms_sell_record MODIFY `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用';",
		"ALTER TABLE oms_deliver_record MODIFY `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量';",
		"ALTER TABLE oms_deliver_record MODIFY `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用';",
		"ALTER TABLE oms_sell_record ADD KEY `express_no` (`express_no`); ",
		"ALTER TABLE oms_deliver_record ADD KEY `express_no` (`express_no`); ",
		"CREATE TABLE `oms_sell_record_cz` (
		  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
		  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
		  `deal_code_list` varchar(500) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
		  `sale_channel_code` varchar(20) NOT NULL,
		  `alipay_no` varchar(30) NOT NULL DEFAULT '' COMMENT '支付宝交易号',
		  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
		  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
		  `pay_type` varchar(20) NOT NULL DEFAULT 'secured' COMMENT 'secured 担保交易 cod货到付款 nosecured 非担保交易',
		  `pay_code` varchar(20) NOT NULL DEFAULT '' COMMENT '支付方式代码',
		  `pay_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '付款状态 0:未付款;1:付款中(部分付款);2:已付款',
		  `pay_time` datetime NOT NULL COMMENT '支付时间',
		  `customer_code` varchar(20) NOT NULL DEFAULT '' COMMENT '会员代码',
		  `buyer_name` varchar(20) NOT NULL DEFAULT '' COMMENT '购买人名称',
		  `receiver_name` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人名称',
		  `receiver_country` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人国家',
		  `receiver_province` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人省',
		  `receiver_city` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人市',
		  `receiver_district` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人区',
		  `receiver_street` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人街道',
		  `receiver_address` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人地址(包含省市区)',
		  `receiver_addr` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人地址(不包含省市区)',
		  `receiver_zip_code` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人邮政编码',
		  `receiver_mobile` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人手机',
		  `receiver_phone` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人固定电话',
		  `receiver_email` varchar(40) NOT NULL DEFAULT '' COMMENT '收货人email',
		  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
		  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
		  `goods_weigh` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总重量-克',
		  `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量-千克',
		  `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用',
		  `weigh_time` datetime DEFAULT NULL COMMENT '称重时间',
		  `weigh_person` varchar(20) NOT NULL DEFAULT '' COMMENT '称重人',
		  `order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总额,商品总额+运费+配送手续费',
		  `goods_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '商品总额',
		  `express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
		  `delivery_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '配送手续费',
		  `payable_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单应付款,商品均摊总金额+运费',
		  `paid_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单已付款,已支付状态下与应付款相等',
		  `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '转入系统时间',
		  `record_time` datetime NOT NULL COMMENT '下单时间',
		  `delivery_time` datetime NOT NULL COMMENT '发货时间',
		  `delivery_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '发货日期',
		  `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
		  `is_notice_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '通知配货时间',
		  `check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '确认时间',
		  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `express_no` (`express_no`),
		  KEY `idxu_record_code` (`sell_record_code`) USING BTREE,
		  KEY `idxu_deal_code` (`deal_code`),
		  KEY `record_time` (`record_time`),
		  KEY `delivery_time` (`delivery_time`),
		  KEY `pay_time` (`pay_time`),
		  KEY `weigh_time` (`weigh_time`),
		  KEY `express_code` (`express_code`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='称重订单';",
		"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) values('cz_com_name','waves_property','S002_009    电子称COM端口号','text','','3','','称重检验必设置项,默认为3。');",
		"insert into sys_params(param_code,parent_code,param_name,type,form_desc,`value`,remark,memo) values('cz_baud_rate','waves_property','S002_010    电子称波特率','text','','9600','','称重检验必设置项，默认9600。');",
		"INSERT INTO `sys_action` VALUES ('7010106','7010001','url','订单称重校验','oms/sell_record_cz/view','40','1','0','1','0');",
		"INSERT INTO `sys_action` VALUES ('7010107','7010001','url','已称重订单列表','oms/sell_record_cz/do_list','41','1','0','1','0');",
);

$u['FSF-1877'] = array("update sys_schedule set loop_time = 300 where code = 'fx_inv_upload_cmd'");
$u['FSF-1885'] = array(
		"ALTER TABLE api_weipinhuijit_pick_goods ADD UNIQUE KEY `pick_no_barcode` (`pick_no`,`barcode`); ",
);




