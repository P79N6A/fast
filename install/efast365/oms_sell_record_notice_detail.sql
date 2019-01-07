DROP TABLE IF EXISTS `oms_sell_record_notice_detail`;
CREATE TABLE `oms_sell_record_notice_detail` (
  `sell_record_detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '单据编号',
  `deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台交易号',
  `sub_deal_code` varchar(64) NOT NULL DEFAULT '' COMMENT '平台子交易号',
  `goods_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(20) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(20) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(30) NOT NULL DEFAULT '' COMMENT 'sku',
  `barcode` varchar(30) NOT NULL DEFAULT '' COMMENT '条码',
  `goods_price` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品单价(实际售价)',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '数量',
  `goods_weigh` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '商品重量',
  `avg_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
  `platform_spec` varchar(255) NOT NULL DEFAULT '' COMMENT '平台规格',
  `is_gift` tinyint(4) NOT NULL DEFAULT '0' COMMENT '礼品标识：0-普通商品1-礼品',
  `sale_mode` varchar(10) NOT NULL DEFAULT 'stock' COMMENT '销售模式：现货stock，预售presale',
  `delivery_mode` varchar(10) NOT NULL DEFAULT 'days' COMMENT 'days承诺发货天数 ; time预售发货时间',
  `delivery_days_or_time` varchar(20) NOT NULL COMMENT '存放承诺发货期或预售发货时间',
  `plan_send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'SKU计划发货时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `pic_path` varchar(200) DEFAULT '' COMMENT '商品图片地址',
  PRIMARY KEY (`sell_record_detail_id`),
  UNIQUE KEY `idxu_key` (`sell_record_code`,`deal_code`,`sku`,`is_gift`) USING BTREE,
  KEY `index_sku` (`sku`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE,
  KEY `sub_deal_code` (`sub_deal_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10619 DEFAULT CHARSET=utf8 COMMENT='通知配货订单明细表';