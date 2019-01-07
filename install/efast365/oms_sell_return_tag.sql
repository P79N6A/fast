DROP TABLE IF EXISTS `oms_sell_return_tag`;
CREATE TABLE `oms_sell_return_tag` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sell_return_code` varchar(20) NOT NULL COMMENT '退单号',
  `tag_type` varchar(10) NOT NULL COMMENT '标签类型',
  `tag_v` varchar(128) NOT NULL COMMENT '标签值',
  `tag_desc` varchar(128) NOT NULL COMMENT '标签描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sell_return_code` (`sell_return_code`,`tag_type`,`tag_v`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='退单标签表';


