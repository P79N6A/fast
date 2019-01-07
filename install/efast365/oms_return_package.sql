DROP TABLE IF EXISTS `oms_return_package`;
CREATE TABLE `oms_return_package` (
  `return_package_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT ' 退货包裹单类型 1退 2退+换 3修补',
  `return_package_code` varchar(20) NOT NULL DEFAULT '' COMMENT '退货包裹单号',  
  `sell_return_code` varchar(20) NOT NULL DEFAULT '',
  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '关联订单号',
  `deal_code` varchar(100) NOT NULL DEFAULT '' COMMENT '关联交易号',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '退货仓库代码',
  `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '退货仓库代码',
  `stock_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
  `return_order_status` int(11) NOT NULL DEFAULT '0' COMMENT '退货包裹单状态.0-未收货；1-已收货；2-已作废；',
  `return_name` varchar(20) NOT NULL DEFAULT '' COMMENT '收货人名称',
  `return_country` int(11) NOT NULL DEFAULT '0' COMMENT '国家',
  `return_province` int(11) NOT NULL DEFAULT '0' COMMENT '省',
  `return_city` int(11) NOT NULL DEFAULT '0' COMMENT '市',
  `return_district` int(11) NOT NULL DEFAULT '0' COMMENT '区',
  `return_street` int(11) NOT NULL DEFAULT '0' COMMENT '区',
  `return_address` varchar(100) NOT NULL DEFAULT '' COMMENT '地址(包含省市区)',
  `return_addr` varchar(100) NOT NULL DEFAULT '' COMMENT '地址(不包含省市区)',
  `return_mobile` varchar(32) NOT NULL DEFAULT '' COMMENT '手机',
  `return_phone` varchar(32) NOT NULL DEFAULT '' COMMENT '固定电话',
  `return_express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '退货快递公司CODE',
  `return_express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
  `tag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 正常 1 SKU异常 2 无名单 3 无退货申请',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '退货包裹单备注',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`return_package_id`),
  UNIQUE KEY `idxu_key` (`return_package_code`) USING BTREE,
  KEY `idx_create_time` (`create_time`),
  KEY `idx_lastchanged` (`lastchanged`),
  KEY `idx_store_code` (`store_code`),
  KEY `idx_return_name` (`return_name`),
  KEY `idx_return_mobile` (`return_mobile`),
  KEY `idx_tag` (`tag`),
  KEY `idx_sell_return_code` (`sell_return_code`),
  KEY `idx_sell_record_code` (`sell_record_code`),
  KEY `idx_deal_code` (`deal_code`),
  KEY `idx_shop_code` (`shop_code`),
  KEY `idx_return_express_code` (`return_express_code`),
  KEY `idx_return_express_no` (`return_express_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='退货包裹单'