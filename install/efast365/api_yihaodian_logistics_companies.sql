
DROP TABLE IF EXISTS `api_yihaodian_logistics_companies`;
CREATE TABLE `api_yihaodian_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logistics_id` int(11) NOT NULL COMMENT '快递公司ID(可能发生变化)',
  `companyName` varchar(50) DEFAULT NULL COMMENT '快递公司名称',
  `queryURL` varchar(100) DEFAULT NULL COMMENT '快递公司查询地址',
  `status` int(11) DEFAULT '0' COMMENT '状态0:启用1:关闭',
  `company_code` varchar(10) DEFAULT NULL COMMENT '物流公司编码,与业务系统匹配',
  PRIMARY KEY (`id`),
  UNIQUE KEY(`logistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;