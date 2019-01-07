DROP TABLE IF EXISTS `api_vipshop_logistics_companies`;
CREATE TABLE `api_vipshop_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tms_carriers_id` varchar(50) DEFAULT NULL COMMENT '唯品会物流公司唯一ID',
  `carriers_code` varchar(100) DEFAULT NULL COMMENT '唯品会物流公司代码',
  `carriers_shortname` varchar(100) DEFAULT NULL COMMENT '唯品会物流公司简称',
  `carriers_name` varchar(100) DEFAULT NULL COMMENT '唯品会物流公司全称',
  `carriers_isvalid` varchar(100) DEFAULT NULL COMMENT '物流公司在唯品会是否启用',
  `company_code` varchar(10) DEFAULT NULL COMMENT '物流公司编码,与业务系统匹配',
  PRIMARY KEY (`id`),
  unique KEY(`tms_carriers_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '唯品会物流表';