DROP TABLE IF EXISTS `api_order_tag`;
CREATE TABLE `api_order_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag_id` varchar(100) DEFAULT NULL COMMENT '订单标签记录id',
  `source` varchar(30) NOT NULL COMMENT '销售平台',
  `shop_code` varchar(255) NOT NULL COMMENT '店铺代码',
  `tid` varchar(20) NOT NULL COMMENT '交易号',
  `tag_type` tinyint(2) NOT NULL,
  `gmt_modified` datetime DEFAULT NULL COMMENT '平台中记录的最新修改时间',
  `gmt_created` datetime DEFAULT NULL,
  `tag_name` varchar(100) NOT NULL COMMENT '标签名称',
  `tag_value` text NOT NULL COMMENT '标签值，json格式',
  `visible` tinyint(1) DEFAULT NULL COMMENT '该标签在消费者端是否显示,0:不显示,1：显示',
  `insert_time` datetime DEFAULT NULL COMMENT '插入时间',
  PRIMARY KEY (`id`),
  KEY `tid` (`tid`),
  KEY `tag_type` (`tag_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单标签表';

