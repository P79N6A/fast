-- ----------------------------
-- Table structure for `api_taobao_trade_trace`
-- ----------------------------
DROP TABLE IF EXISTS `api_taobao_trade_trace`;
CREATE TABLE `api_taobao_trade_trace` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) NOT NULL COMMENT '商店代码',
  `tid` varchar(80) NOT NULL COMMENT '淘宝交易号',
  `order_ids` varchar(60) NOT NULL COMMENT '淘宝子订单ID',
  `status` varchar(30) NOT NULL COMMENT '全链路订单处理状态',
  `action_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '订单业务处理时间',
  `remark` varchar(100) NOT NULL COMMENT '备注信息',
  `seller_nick` varchar(40) NOT NULL COMMENT '卖家NICK',
  `efast_process_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未上传 1上传成功 2上传失败',
  `efast_process_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上传时间',
  `fail_repeat_num` tinyint(4) NOT NULL DEFAULT '0' COMMENT '失败重试次数',
  `err_msg` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu_tid_status` (`tid`,`status`),
  KEY `idx_tid` (`tid`),
  KEY `idx_order_ids` (`order_ids`),
  KEY `idx_status` (`status`),
  KEY `idx_action_time` (`action_time`),
  KEY `idx_seller_nick` (`seller_nick`),
  KEY `idx_seller_nick_status` (`seller_nick`,`status`),
  KEY `idx_shop_codestatus` (`shop_code`,`status`),
  KEY `idx_shop_code_time_process_flag` (`shop_code`,`efast_process_flag`,`action_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '淘宝订单全链路状态回传信息表';