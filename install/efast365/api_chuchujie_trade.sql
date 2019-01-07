
DROP TABLE IF EXISTS `api_chuchujie_trade`;
CREATE TABLE `api_chuchujie_trade` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` varchar(50) NOT NULL COMMENT '订单id',
		  `order_url` varchar(250) NOT NULL,
		  `status_text` varchar(100) DEFAULT '' COMMENT '订单状态文本说明',
		  `total_price` varchar(20) DEFAULT '0.00' COMMENT '订单总金额',
		  `ctime` varchar(20) DEFAULT '' COMMENT '订单的创建时间',
		  `comment` varchar(255) DEFAULT '' COMMENT '买家留言',
		  `express_price` varchar(20) DEFAULT '0.00' COMMENT '运费',
		  `express_id` varchar(20) DEFAULT '' COMMENT '快递号',
		  `express_company` varchar(20) DEFAULT '' COMMENT '快递公司',
		  `pay_time` varchar(20) DEFAULT '' COMMENT '付款时间',
		  `send_time` varchar(20) DEFAULT '' COMMENT '发货时间',
		  `last_status_time` varchar(20) DEFAULT '' COMMENT '订单关闭时间',
		  `seller_note` varchar(255) DEFAULT '' COMMENT '商家备注',
		  `postcode` varchar(50) DEFAULT NULL COMMENT '邮编',
		  `nickname` varchar(100) DEFAULT NULL COMMENT '收件人',
		  `phone` varchar(50) DEFAULT NULL COMMENT '收货人手机号',
		  `address` varchar(255) DEFAULT NULL COMMENT '收货人地址',
		  `province` varchar(50) DEFAULT NULL COMMENT '省',
		  `city` varchar(50) DEFAULT NULL COMMENT '市',
		  `district` varchar(50) DEFAULT NULL COMMENT '区',
		  `street` varchar(100) DEFAULT NULL COMMENT '街道',
		  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
		  PRIMARY KEY (`id`),
		  UNIQUE KEY `order_id` (`order_id`) USING BTREE
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
