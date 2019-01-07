DROP TABLE IF EXISTS `goods_combo_diy`;
CREATE TABLE `goods_combo_diy` (
  `goods_combo_diy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `p_goods_code` varchar(64) DEFAULT '' COMMENT '父类商品代码',
  `p_sku` varchar(128) DEFAULT '' COMMENT '父类sku',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `num` int(8) DEFAULT '0' COMMENT '数量',
  `price` decimal(20,3) DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_combo_diy_id`),
  UNIQUE KEY `goods_code_spec` (`sku`,`p_sku`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品套餐组装表';
