DROP TABLE IF EXISTS `sys_kc_sync_cfg`;
CREATE TABLE `sys_kc_sync_cfg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(20) NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='库存同步百分比设置';