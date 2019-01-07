DROP TABLE IF EXISTS `goods_unique_code_log`;
CREATE TABLE `goods_unique_code_log` (
  `unique_log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `unique_code` varchar(30) DEFAULT '' COMMENT '唯一码',
  `barcode` varchar(30) DEFAULT '' COMMENT '条形码',
  `sku` varchar(30) DEFAULT '' COMMENT '系统sku码',
  `record_type` varchar(50) DEFAULT '' COMMENT '单据类型',
  `record_code` varchar(50) DEFAULT '' COMMENT '单据编号',
  `action_name` varchar(50) DEFAULT '' COMMENT '操作名称',
  `action_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
  `goods_code` varchar(30) DEFAULT '' COMMENT '商品编码',
  `spec1_code` varchar(30) DEFAULT '' COMMENT '规格1',
  `spec2_code` varchar(30) DEFAULT '' COMMENT '规格2',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`unique_log_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品唯一码跟踪';
