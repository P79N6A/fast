
DROP TABLE IF EXISTS `goods_inv_lof_record`;
CREATE TABLE `goods_inv_lof_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_code` varchar(20) NOT NULL COMMENT '单据编号',
  `order_type` varchar(20) NOT NULL COMMENT '单据类型：adjust调整单，purchase采购入库单',
  `order_data` date NOT NULL COMMENT '单据业务日期',
  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `goods_code` varchar(20) DEFAULT '' COMMENT '商品编码',
  `spec1_code` varchar(20) DEFAULT '' COMMENT '规格编码',
  `spec2_code` varchar(20) DEFAULT '' COMMENT '规格2编码',
  `sku` varchar(30) DEFAULT '' COMMENT '商品编码',
  `lof_no` varchar(30) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `num` int(11) DEFAULT '0' COMMENT '库存数量',
  `effect_type` tinyint(3) DEFAULT NULL COMMENT '影响库存类型：1实物库存增加,2实物库存扣减,3实物锁定增加,4实物锁定扣减',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  KEY `idx_gs` (`store_code`,`goods_code`,`spec1_code`,`spec2_code`,`lof_no`,`production_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存批次流水表';

