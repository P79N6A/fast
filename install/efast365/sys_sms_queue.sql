
DROP TABLE IF EXISTS `sys_sms_queue`;
CREATE TABLE `sys_sms_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增',
  `sms_tpl_id` int(11) NOT NULL COMMENT '消息类型',
  `order_sn` varchar(20) NOT NULL COMMENT '订单号',
  `sd_id` int(11) NOT NULL COMMENT '订单对应的商店id',
  `user_nick` varchar(20) NOT NULL COMMENT '会员',
  `tel` varchar(20) NOT NULL COMMENT '发送手机号',
  `msg_content` text NOT NULL COMMENT '消息内容',
  `msg_content_hash` varchar(40) NOT NULL COMMENT '消息内容hash值',
  `add_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `send_time` datetime NOT NULL COMMENT '发送时间',
  `status` char(3) NOT NULL DEFAULT '0' COMMENT ' 发送状态 0：未发送 1：发送成功  2：发送失败 3:发送已提交',
  `req_result` varchar(255) NOT NULL DEFAULT '' COMMENT '标志id,胜券返回id',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_msg_content_hash` (`msg_content_hash`,`tel`) USING BTREE,
  KEY `idx_tel` (`tel`),
  KEY `idx_send_time` (`send_time`) USING BTREE,
  KEY `idxu_order_sn` (`order_sn`,`sms_tpl_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8 COMMENT='短信发送队列';
