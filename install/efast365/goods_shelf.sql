
DROP TABLE IF EXISTS `goods_shelf`;
CREATE TABLE `goods_shelf` (
  `goods_shelf_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` varchar(64) DEFAULT '' COMMENT '规格1代码',
  `spec2_code` varchar(64) DEFAULT '' COMMENT '规格2代码',
  `sku` varchar(64) DEFAULT '' COMMENT '系统SKU码',
  `batch_number` varchar(64) DEFAULT '' COMMENT '批次编号',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `shelf_code` varchar(64) DEFAULT '' COMMENT '库位代码',
  `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_shelf_id`),
  UNIQUE KEY `_key` (`sku`,`batch_number`,`store_code`,`shelf_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商品和库位管理关系';

