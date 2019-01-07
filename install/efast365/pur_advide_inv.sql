DROP TABLE IF EXISTS `pur_advide_inv`;
CREATE TABLE `pur_advide_inv` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_code` varchar(128) NOT NULL,
  `sku` varchar(128) NOT NULL,
  `stock_num` int(11) DEFAULT '0' COMMENT '在库数',
  `road_num` int(11) DEFAULT '0' COMMENT '在途数量',
  `wait_deliver_num` int(11) DEFAULT '0' COMMENT '等待发货数量',
  `out_num` int(11) DEFAULT NULL,
  `pur_num` int(11) DEFAULT '0' COMMENT '补货数',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`store_code`,`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;