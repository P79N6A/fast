
DROP TABLE IF EXISTS `goods_inv_lof`;
CREATE TABLE `goods_inv_lof` (
  `goods_inv_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
  `spec1_id` int(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` varchar(64) NOT NULL DEFAULT '' COMMENT '颜色代码',
  `spec2_id` int(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` varchar(64) NOT NULL DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
  `store_id` int(11) DEFAULT '0' COMMENT '仓库id',
  `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `stock_num` int(11) DEFAULT '0' COMMENT '实物数量',
  `lock_num` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '实物锁定',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `lof_no` varchar(58) NOT NULL DEFAULT '' COMMENT '批次',
  `production_date` date NOT NULL COMMENT '生产日期',
  PRIMARY KEY (`goods_inv_id`),
  UNIQUE KEY `_index_key` (`goods_code`,`spec1_code`,`spec2_code`,`store_code`,`lof_no`),
  KEY `sku` (`sku`) USING BTREE,
  KEY `_store_code` (`store_code`) USING BTREE,
  KEY `_index_goods` (`goods_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品批次库存';
