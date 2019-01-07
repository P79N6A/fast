
DROP TABLE IF EXISTS `goods_inv`;
CREATE TABLE `goods_inv` (
  `goods_inv_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `org_code` varchar(128) DEFAULT '000' COMMENT '渠道代码',
  `spec1_id` int(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` int(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '尺码代码',
  `sku` varchar(128) DEFAULT '' COMMENT 'sku',
  `store_id` int(11) DEFAULT '0' COMMENT '仓库id',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `stock_num` int(11) DEFAULT '0' COMMENT '实物库存',
  `lock_num` int(11) unsigned DEFAULT '0' COMMENT '实物锁定',
  `frozen_num` int(11) DEFAULT '0' COMMENT '冻结数量',
  `pre_sale_num` int(11) DEFAULT '0' COMMENT '预售库存',
  `pre_sale_lock_num` int(11) DEFAULT '0' COMMENT '预售锁定',
  `out_num` int(11) unsigned DEFAULT '0' COMMENT '缺货库存',
  `road_num` int(11) DEFAULT '0' COMMENT '在途库存',
  `safe_num` int(11) DEFAULT '0' COMMENT '安全库存',
  `record_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '业务变更时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_inv_id`),
  UNIQUE KEY `_index_key` (`sku`,`store_code`) USING BTREE,
  KEY `goods_code` (`goods_code`) USING BTREE,
  KEY `sku` (`sku`) USING BTREE,
  KEY `store_code` (`store_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品库存';

