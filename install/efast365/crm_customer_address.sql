
DROP TABLE IF EXISTS `crm_customer_address`;
CREATE TABLE `crm_customer_address` (
  `customer_address_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `customer_code` varchar(30) NOT NULL DEFAULT '' COMMENT '顾客代码',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '收货地址',
  `country` varchar(20) NOT NULL DEFAULT '0' COMMENT '国家',
  `province` varchar(20) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(20) NOT NULL DEFAULT '' COMMENT '城市',
  `district` varchar(20) NOT NULL DEFAULT '' COMMENT '地区',
  `zipcode` varchar(255) NOT NULL DEFAULT '' COMMENT '邮编',
  `tel` varchar(255) NOT NULL DEFAULT '' COMMENT '电话',
  `home_tel` varchar(255) NOT NULL DEFAULT '' COMMENT '座机',
  `is_add_time` datetime NOT NULL COMMENT '新建时间',
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '收货人姓名',
  `street` varchar(20) NOT NULL DEFAULT '',
  `is_default` tinyint(4) NOT NULL,
  PRIMARY KEY (`customer_address_id`),
  KEY `customer_code` (`customer_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='顾客收货地址'