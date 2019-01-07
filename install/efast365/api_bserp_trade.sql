
DROP TABLE IF EXISTS `api_bserp_trade`;
CREATE TABLE `api_bserp_trade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号/退单号)',
  `deal_code` varchar(80) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '平台交易号列表',
  `order_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '单据类型 1 销售订单 2销售退单',
  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `upload_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
  `upload_time` datetime NOT NULL COMMENT '上传时间',
  `upload_msg` varchar(255) DEFAULT NULL COMMENT '上传失败原因',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sell_record_code` (`sell_record_code`,`order_type`),
  KEY `upload_time` (`upload_time`),
  KEY `shop_code` (`shop_code`),
  KEY `store_code` (`store_code`),
  KEY `order_type` (`order_type`),
  KEY `deal_code` (`deal_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='erp上传单据';
