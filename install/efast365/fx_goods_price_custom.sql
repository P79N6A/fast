DROP TABLE IF EXISTS `fx_goods_price_custom`;
CREATE TABLE `fx_goods_price_custom` (
  `price_custom_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
  `custom_code` varchar(128) DEFAULT '' COMMENT '分销商code',
  `custom_name` varchar(128) DEFAULT '' COMMENT '分销商名称',
  `rebates` varchar(128) DEFAULT '' COMMENT '折扣（基于吊牌价）',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`price_custom_id`),
  UNIQUE KEY `custom_code_goods_line_code` (`goods_line_code`,`custom_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品定价(指定分销商)';
