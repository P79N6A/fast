DROP TABLE IF EXISTS `goods_inv_api_sync_log`;
CREATE TABLE `goods_inv_api_sync_log` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `sku` varchar(128) DEFAULT '',
  `barcode` varchar(128) DEFAULT '',
  `shop_code` varchar(128) DEFAULT '',
  `num` int(11) DEFAULT '0',
  `type` int(11) DEFAULT '0',
  `desc` varchar(255) DEFAULT '',
  `store_code` varchar(255) DEFAULT '',
  `inv_update_time` datetime DEFAULT NULL,
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存计算日志表';
