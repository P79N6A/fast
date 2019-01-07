
DROP TABLE IF EXISTS `base_goods_tmp`;
CREATE TABLE `base_goods_tmp` (
  `goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `goods_name` varchar(128) DEFAULT '' COMMENT '商品名称',
  `category_code` varchar(64) DEFAULT '' COMMENT '分类代码',
  `brand_code` varchar(64) DEFAULT '' COMMENT '品牌代码',
  `series_code` varchar(64) DEFAULT '' COMMENT '系列代码',
  `season_code` varchar(64) DEFAULT '' COMMENT '季节代码',
  `goods_info` text COMMENT '商品信息详情',
  `goods_price_info` text COMMENT '商品价格详情',
  `goods_inv_info` text COMMENT '商品库存详情',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品快速建档临时表';

