
DROP TABLE IF EXISTS `b2b_lof_datail`;
CREATE TABLE `b2b_lof_datail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '交易ID',
  `p_detail_id` int(11) DEFAULT '0' COMMENT '明显单据号',
  `order_code` varchar(128) DEFAULT '' COMMENT '单据编号',
  `order_type` varchar(128) DEFAULT '' COMMENT '单据类型：adjust调整单，purchase采购入库单',
  `goods_id` int(11) DEFAULT '0' COMMENT '商品ID',
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品编码',
  `spec1_id` int(11) DEFAULT '0' COMMENT '规格1id',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '规格编码',
  `spec2_id` int(11) DEFAULT '0' COMMENT '规格2id',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '规格2编码',
  `sku` varchar(128) DEFAULT '' COMMENT '商品编码',
  `store_id` int(11) DEFAULT '0' COMMENT '仓库id',
  `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `lof_no` varchar(64) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `num` int(11) DEFAULT '0' COMMENT '库存数量',
  `init_num` int(11) DEFAULT '0' COMMENT '初始数量',
  `fill_num` int(11) DEFAULT '0' COMMENT '完成数量',
  `occupy_type` tinyint(3) DEFAULT '0' COMMENT '1实物锁定，2实物扣减，3实物增加，0无效库存',
  `order_date` date DEFAULT '0000-00-00' COMMENT '业务时间',
  `create_time` int(11) DEFAULT '0' COMMENT '创建时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_index_key` (`order_type`,`order_code`,`sku`,`lof_no`),
  KEY `ix_occupy_type` (`occupy_type`) USING BTREE,
  KEY `ix_store_code` (`store_code`) USING BTREE,
  KEY `ix_order_date` (`order_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='单据批次表';

