DROP TABLE IF EXISTS `api_zhe800_logistics_companies`;
CREATE TABLE `api_zhe800_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logistics_id` int(11) NOT NULL DEFAULT '0' COMMENT '折800快递id',
  `logistics_name` varchar(50) NOT NULL DEFAULT '' COMMENT 'zhe800快递公司名称',
  `logistics_remark` varchar(100) NOT NULL DEFAULT '' COMMENT '备注说明',
  `company_code` varchar(30) NOT NULL DEFAULT '' COMMENT '系统快递代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `logistics_id` (`logistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8;

