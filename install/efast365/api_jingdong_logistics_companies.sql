
DROP TABLE IF EXISTS `api_jingdong_logistics_companies`;
CREATE TABLE `api_jingdong_logistics_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) NOT NULL COMMENT 'efast店铺CODE',
  `vender_id` int(11) NOT NULL COMMENT '京东商家ID',
  `logistics_id` int(11) NOT NULL COMMENT '京东快递logistics_id',
  `logistics_name` varchar(50) DEFAULT NULL COMMENT '物流公司名称',
  `logistics_remark` varchar(100) DEFAULT NULL COMMENT '备注说明',
  `sequence` int(11) NOT NULL DEFAULT '0' COMMENT '序列',
  `company_code` varchar(10) DEFAULT NULL COMMENT '物流公司编码,与业务系统匹配',
  PRIMARY KEY (`id`),
  unique KEY(`logistics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;