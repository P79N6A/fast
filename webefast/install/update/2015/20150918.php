<?php

$u = array();

//当当面单表
$u['FSF-1653'] = array(
	"CREATE TABLE `api_dangdang_print` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `shop_code` varchar(100) DEFAULT NULL COMMENT '店铺代码',
	  `receiptTitle` varchar(100) DEFAULT NULL COMMENT '面单标题',
	  `shopWarehouse` varchar(50) DEFAULT NULL COMMENT '商家所在仓库',
	  `orderID` varchar(50) NOT NULL COMMENT '订单编号',
	  `orderCreateTime` varchar(50) DEFAULT NULL COMMENT '订单创建时间',
	  `consigneeName` varchar(50) DEFAULT NULL COMMENT '收货人姓名',
	  `consigneeAddr` varchar(255) DEFAULT NULL COMMENT '收货地址',
	  `consigneeAddr_State` varchar(50) DEFAULT NULL COMMENT '收货国家',
	  `consigneeAddr_Province` varchar(50) DEFAULT NULL COMMENT '收货省份',
	  `consigneeAddr_City` varchar(50) DEFAULT NULL COMMENT '收货市',
	  `consigneeAddr_Area` varchar(50) DEFAULT NULL COMMENT '收货区',
	  `consigneePostcode` varchar(50) DEFAULT NULL COMMENT '邮编',
	  `consigneeTel` varchar(50) DEFAULT NULL COMMENT '收货人固定电话',
	  `consigneeMobileTel` varchar(50) DEFAULT NULL COMMENT '收货人移动电话',
	  `shopName` varchar(50) DEFAULT NULL COMMENT '店铺名称',
	  `shopID` varchar(50) DEFAULT NULL COMMENT '店铺编号',
	  `consignerName` varchar(100) DEFAULT NULL COMMENT '发货人名称',
	  `consignerTel` varchar(100) DEFAULT NULL COMMENT '发货人电话',
	  `consignerAddr` varchar(255) DEFAULT NULL COMMENT '发货人地址',
	  `totalBarginPrice` decimal(10,2) DEFAULT NULL COMMENT '总价',
	  `sendGoodsTime` varchar(255) DEFAULT NULL COMMENT '送货时间',
	  `expressCompany` varchar(100) DEFAULT NULL COMMENT '快递公司',
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `orderID` (`orderID`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);

$u['FSF-1652'] = array("alter table oms_sell_record_detail add return_money decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '退货金额'",
		"UPDATE oms_sell_record_detail A
	JOIN (
		SELECT
			r1.sell_record_code as b_sell_record_code,
			r2.sku as b_sku,
			sum(r2.recv_num) as b_return_num,
			sum(r2.avg_money) as b_return_money
		FROM oms_sell_return r1
		LEFT JOIN
			oms_sell_return_detail r2
		ON
			r2.sell_return_code = r1.sell_return_code
		WHERE
			r1.return_shipping_status=1 GROUP by r1.sell_record_code,r2.sku
	) B
	ON
		A.sell_record_code=B.b_sell_record_code and A.sku = B.b_sku
	SET
		A.return_num = B.b_return_num,
		A.return_money = B.b_return_money"
);

$u['FSF-1657'] = array(
    "ALTER TABLE `api_logs`
MODIFY COLUMN `params`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '请求参数' AFTER `url`,
MODIFY COLUMN `post_data`  mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '请求业务参数' AFTER `params`;",
    "ALTER TABLE `wms_archive`
    ADD COLUMN `efast_store_code`  varchar(128) NOT NULL AFTER `api_product`,
    DROP INDEX `idxu` ,
    ADD UNIQUE INDEX `idxu` (`efast_store_code`, `type`, `code`) USING BTREE ;
    ",
    
"INSERT INTO `sys_print_templates` VALUES ('32', 'cainiao_sf', '云栈顺丰电子面单', 'SF', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"SF\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte2ltqXdqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
"INSERT INTO `sys_print_templates` VALUES ('33', 'cainiao_yd', '云栈韵达电子面单', 'YUNDA', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"YUNDA\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte2ltqXdqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
"INSERT INTO `sys_print_templates` VALUES ('34', 'cainiao_sto', '云栈申通电子面单', 'STO', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"STO\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte1ltmYdqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
"INSERT INTO `sys_print_templates` VALUES ('35', 'cainiao_zto', '云栈中通电子面单', 'ZTO', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"ZTO\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte2ltm5dqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
"INSERT INTO `sys_print_templates` VALUES ('36', 'cainiao_yto', '云栈圆通电子面单', 'YTO', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"YTO\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte2ltqWdqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
"INSERT INTO `sys_print_templates` VALUES ('37', 'cainiao_gto', '云栈国通电子面单', 'GTO', '1', '2', '0', '0', '0', '0', '无', '{\"detail\":\"\",\"deteil_row\":\"0\",\"itemkey\":\"\"}', '{\"cp_code\":\"GTO\",\"config\":\"\",\"self_body\":\"@w0Luru1dt1vovf0ncLzatfvfptbncG0kw1astLrpuf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjotevgvf0ncKLuru1qyw5LBfaqveW9mb0kdqPBufjov0LeveHDdqPjvevnugfUzwXqufrmptqWmb0kdqPBufjosevjr0Huxq0ksvrftvaHBMvSufautd04mdbncG0kw1rnvKvsu0LptL0ncLrnvKvsu0LptJ0Ymde1ltb5lte0lte2ltqWdqPBsvrftuvorf0ncG==\\r\\n\",\"express_type\":\"\",\"ali_waybill_cp_logo_up\":\"0\",\"ali_waybill_cp_logo_down\":\"0\"}', '[]', '');
",
);
$u['FSF-1666'] = array(
		"ALTER TABLE api_taobao_alipay ADD KEY `deal_code_create_time` (`deal_code`,`create_time`);",
		"UPDATE sys_action SET action_name='支付宝流水核销查询',sort_order=2 WHERE action_name='支付宝核销查询'",
		"UPDATE sys_action SET action_name='支付宝流水核销统计',sort_order=1 WHERE action_name='支付宝核销分析'",
		"UPDATE sys_action SET action_name='零售结算交易核销统计',sort_order=3 WHERE action_name='零售结算交易核销分析'",
		"UPDATE sys_action SET sort_order=5 WHERE action_name='支付宝收支流水'",
		"ALTER TABLE oms_sell_settlement ADD COLUMN `sale_right_fee` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '售后维权退款'",
		"ALTER TABLE oms_sell_settlement ADD COLUMN `commission_fee2` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '淘宝客佣金代扣款'",
		"ALTER TABLE oms_sell_settlement ADD COLUMN `credit_code_fee` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '信用卡支付服务费'",
		"delete from sys_schedule where name='支付宝对账' or name='支付宝流水下载';",
		"INSERT INTO `sys_schedule` VALUES ('6', 'alipay_download_cmd', '支付宝流水下载', '', '', '0', '4', '需要订购天猫店铺的支付宝，此服务才有效，默认每隔一小时运行一次，默认关闭', '', '', '0', '0', '0', '3600', '0', 'api', '', '0', '0');",
		"INSERT INTO `sys_schedule` VALUES ('45', 'alipay_accounts_cmd', '支付宝对账', '', '', '0', '4', '功能说明：<br />1.将支付宝流水，科目为交易收款类收入，更新到零售汇总查询中的交易实际收入中<br />2.核对零售结算汇总查询，应收是否与实收相等，如果相等，分别为零售结算汇总数据、支付宝数据打上记账标记<br />此服务默认两个小时运行一次，默认关闭', '', '', '0', '0', '0', '7200', '0', 'api', '', '0', '0');",
		"ALTER TABLE api_taobao_alipay ADD KEY `idx_account_item_code` (`account_item_code`);",
		"ALTER TABLE report_alipay ADD COLUMN `account_item_code` varchar(20) DEFAULT '' COMMENT '会计科目编号';",
		"ALTER TABLE report_alipay ADD KEY `idx_account_item_code` (`account_item_code`);",
		"ALTER TABLE report_alipay MODIFY  `je` decimal(11,3) DEFAULT '0.000' COMMENT '金额';",
		);


$u['FSF-1660'] = array(
		"ALTER TABLE api_goods ADD COLUMN `invalid_time` datetime DEFAULT NULL COMMENT '标记为删除状态的时间'",
		);
