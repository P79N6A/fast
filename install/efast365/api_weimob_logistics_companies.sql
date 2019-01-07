
DROP TABLE IF EXISTS `api_weimob_logistics_companies`;
CREATE TABLE `api_weimob_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logistics_id` varchar(50) DEFAULT '' COMMENT '快递公司code',
  `logistics_name` varchar(50) DEFAULT NULL COMMENT '物流公司名称',
  `logistics_remark` varchar(100) DEFAULT NULL COMMENT '备注说明',
  `company_code` varchar(10) DEFAULT NULL COMMENT '物流公司编码,与业务系统匹配',
  PRIMARY KEY (`id`),
  unique KEY(`logistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '微盟物流公司信息';