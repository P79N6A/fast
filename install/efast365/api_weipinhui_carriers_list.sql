DROP TABLE IF EXISTS `api_weipinhui_carriers_list`;
CREATE TABLE `api_weipinhui_carriers_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '唯品会订单列表',
  `tms_carriers_id` varchar(50) NOT NULL COMMENT 'tms端承运商ID',
  `carriers_name` varchar(100) DEFAULT '0' COMMENT '承运商全称',
  `carriers_isvalid` tinyint(1) DEFAULT '0' COMMENT '承运商状态 1启用， 0 关闭',
  `carriers_shortname` varchar(50) DEFAULT NULL COMMENT '承运商简称',
  `carriers_code` varchar(50) DEFAULT NULL COMMENT '承运商编码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_carriers_code` (`carriers_code`) USING BTREE,
  KEY `idx_tms_carriers_id` (`tms_carriers_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;