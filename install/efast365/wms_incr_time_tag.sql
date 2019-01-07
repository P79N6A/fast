DROP TABLE IF EXISTS `wms_incr_time_tag`;
CREATE TABLE `wms_incr_time_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `efast_store_code` varchar(30) NOT NULL COMMENT 'efast 仓库代码',
  `biz_code` varchar(30) NOT NULL COMMENT '业务CODE iwms_stock_sync iwms_quehou_sync',
  `start_time` datetime NOT NULL COMMENT '增量开始时间',
  `end_time` datetime NOT NULL COMMENT '增量结束时间',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 开始处理 1 处理成功',
  `msg` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx` (`efast_store_code`,`biz_code`,`end_time`,`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
