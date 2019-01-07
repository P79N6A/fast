<?php
$u = array();

$u['FSF-1811'] = array(
    "INSERT INTO `sys_schedule` VALUES ('50', 'create_jxc_report_day', '商品进销存报表生成', 'create_oms_report_day', '', '1', '10', '商品进销存报表生成', '{\"app_act\":\"rpt\\/report_jxc\\/sync_data\",\"app_fmt\":\"json\"}', 'webefast/web/index.php', '0', '0', '0', '86400', '0', 'sys', '', '1446768000', '0');",
);

$u['FSF-1812'] = array(
    " DELETE from oms_sell_record_notice  where sell_record_code in(
    SELECT r.sell_record_code from  oms_sell_record r
     where r.order_status =3)",
    "   DELETE from oms_sell_record_notice_detail  where sell_record_code in(
    SELECT r.sell_record_code from  oms_sell_record r
     where r.order_status =3  )
     ",
);
$u['FSF-1787'] = array(
		"INSERT INTO `sys_action` VALUES ('5040101', '5040100', 'act', '导出', 'api/taobao/goods/ph_export_list', '2', '1', '0', '1','0');",
);
$u['FSF-1814'] = array(
		"ALTER TABLE oms_sell_record MODIFY `deal_code_list` varchar(500) NOT NULL DEFAULT '' COMMENT '平台交易号列表'",
		"ALTER TABLE oms_deliver_record MODIFY `deal_code_list` varchar(500) NOT NULL DEFAULT '' COMMENT '平台交易号列表'",
		);
$u['FSF-1815'] = array(
		"CREATE TABLE `api_weipinhui_carriers_list` (
		  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯品会订单列表',
		  `tms_carriers_id` varchar(50) NOT NULL COMMENT 'tms端承运商ID',
		  `carriers_name` varchar(100) DEFAULT '0' COMMENT '承运商全称',
		  `carriers_isvalid` tinyint(1) DEFAULT '0' COMMENT '承运商状态 1启用， 0 关闭',
		  `carriers_shortname` varchar(50) DEFAULT NULL COMMENT '承运商简称',
		  `carriers_code` varchar(50)  DEFAULT NULL COMMENT '承运商编码',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `idx_carriers_code` (`carriers_code`) USING BTREE,
		  KEY `idx_tms_carriers_id` (`tms_carriers_id`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		);