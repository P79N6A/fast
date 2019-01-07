DROP TABLE IF EXISTS `goods_diy`;
CREATE TABLE `goods_diy` (
  `goods_diy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `p_goods_code` varchar(64) DEFAULT '' COMMENT '父类商品代码',
  `p_sku` varchar(128) DEFAULT '' COMMENT '父类sku',
  `add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `num` int(8) DEFAULT '0' COMMENT '数量',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_diy_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='组装商品表';
