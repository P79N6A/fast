DROP TABLE IF EXISTS `wms_trade_quehuo`;
CREATE TABLE `wms_trade_quehuo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sell_record_code` varchar(20) NOT NULL COMMENT 'EFAST订单号',
  `down_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下载时间',
  `process_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理WMS缺货单标识，0未处理  20失败 30成功',
  `process_err_msg` varchar(200) NOT NULL DEFAULT '' COMMENT 'efast处理WMS缺货单出错信息',
  `efast_store_code` varchar(30) NOT NULL DEFAULT '' COMMENT 'efast仓库代码',
  `wms_store_code` varchar(30) NOT NULL DEFAULT '' COMMENT 'wms仓库代码',
  `wms_djbh` varchar(30) NOT NULL DEFAULT '' COMMENT 'wms单据编号',
  `quehuo_desc` varchar(200) NOT NULL DEFAULT '' COMMENT 'WMS的缺货原因',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sell_record_code` (`sell_record_code`) USING BTREE,
  KEY `down_time` (`down_time`),
  KEY `process_flag` (`process_flag`),
  KEY `wms_store_code` (`wms_store_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

