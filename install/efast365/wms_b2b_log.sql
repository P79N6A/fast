DROP TABLE IF EXISTS `wms_b2b_log`;
CREATE TABLE `wms_b2b_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '单据类型',
  `user_name` varchar(20) NOT NULL,
  `action_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
  `action` varchar(40) NOT NULL DEFAULT '' COMMENT '操作类型 upload_request_wms,wms_response_upload_success,wms_response_upload_fail,cancel_request_wms,wms_response_cancel_success,wms_response_cancel_fail,wms_trade_success,wms_trade_close',
  `action_msg` varchar(300) NOT NULL DEFAULT '' COMMENT '操作说明',
  PRIMARY KEY (`id`),
  KEY `record_code` (`record_code`),
  KEY `record_type` (`record_type`),
  KEY `action_time` (`action_time`),
  KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;