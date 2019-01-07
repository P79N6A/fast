
DROP TABLE IF EXISTS `goods_lof`;
CREATE TABLE `goods_lof` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(50) NOT NULL DEFAULT '',
  `lof_no` varchar(128) NOT NULL COMMENT '批次号',
  `production_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '生产日期',
  `pur_code` varchar(128) NOT NULL COMMENT '产生批次 采购单号',
  `validity_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '有效期截止日期',
  `shelf_life` int(11) NOT NULL DEFAULT '0' COMMENT '保质期 天',
  `type` tinyint(3) NOT NULL DEFAULT '0' COMMENT '1为默认批次，0为进货批次',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_lof_no_index` (`sku`,`lof_no`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COMMENT='批次表';

