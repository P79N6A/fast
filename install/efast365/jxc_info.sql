
DROP TABLE IF EXISTS `jxc_info`;
CREATE TABLE `jxc_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_code` varchar(20) DEFAULT '' COMMENT '商品编码',
  `spec1_code` varchar(20) DEFAULT '' COMMENT '规格编码',
  `spec2_code` varchar(20) DEFAULT '' COMMENT '规格2编码',
  `sku` varchar(30) DEFAULT '' COMMENT '商品编码',
  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `lof_no` varchar(20) DEFAULT '' COMMENT '批次号',
  `production_date` date DEFAULT NULL COMMENT '生产日期',
  `num` int(11) DEFAULT '0' COMMENT '库存数量',
  `order_type` varchar(20) DEFAULT '' COMMENT '单据类型',
  `ymonth` varchar(7) DEFAULT '0000-00' COMMENT '年月',
  `mx_lastchanged` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '明细表的数据最后更新时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_idxu_key` (`sku`,`store_code`,`lof_no`,`production_date`,`order_type`,`ymonth`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='进销存按月统计表';
