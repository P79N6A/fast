DROP TABLE IF EXISTS `api_aliexpress_logistics_companies`;
CREATE TABLE `api_aliexpress_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendOrder` varchar(50) DEFAULT NULL COMMENT '',
  `trackingNoRegex` varchar(100) DEFAULT NULL COMMENT '单号匹配规则',
  `logisticsCompany` varchar(100) DEFAULT NULL COMMENT '物流公司名称',
  `minProcessDay` varchar(100) DEFAULT NULL COMMENT '最少处理周期，单位：天',
  `maxProcessDay` varchar(100) DEFAULT NULL COMMENT '最大处理周期，单位：天',
  `displayName` varchar(100) DEFAULT NULL COMMENT '物流公司全称',
  `serviceName` varchar(100) DEFAULT NULL COMMENT '？？？',
  `company_code` varchar(10) DEFAULT NULL COMMENT '物流公司编码,与业务系统匹配',
  PRIMARY KEY (`id`),
  unique KEY(`logisticsCompany`,`serviceName`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '速卖通物流表';