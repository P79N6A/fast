
DROP TABLE IF EXISTS `unique_code_scan_temporary_log`;
CREATE TABLE `unique_code_scan_temporary_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(64) DEFAULT '' COMMENT '扫描订单号',
  `unique_code` varchar(64) DEFAULT '',
  `barcode_type` varchar(64) DEFAULT '' COMMENT '条码类型,barcode:条形码;unique_code:唯一码;child_barcode:子条码;',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='商品唯一码跟踪日志临时表';

-- ----------------------------
-- Records of goods_barcode_child
-- ----------------------------
