
DROP TABLE IF EXISTS `api_chuchujie_order`;
CREATE TABLE `api_chuchujie_order` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` varchar(50) NOT NULL COMMENT '订单id',
		  `goods_id` varchar(250) NOT NULL COMMENT '商品id',
		  `goods_title` varchar(255) DEFAULT '' COMMENT '商品标题',
		  `goods_img` varchar(255) DEFAULT '0.00' COMMENT '商品图片url',
		  `price` varchar(20) DEFAULT '0.00' COMMENT '单价',
		  `amount` varchar(20) DEFAULT '0' COMMENT '数量',
		  `goods_no` varchar(100) DEFAULT '' COMMENT '货号',
		  `outer_id` varchar(255) DEFAULT '' COMMENT '商品sku编码',
		  `short_title` varchar(100) DEFAULT '' COMMENT '标题简写',
		  `refund_status_text` varchar(20) DEFAULT '' COMMENT '退货状态',
		  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
		  `prop` varchar(255) DEFAULT '' COMMENT '规格',
		  PRIMARY KEY (`id`),
		  KEY `order_id` (`order_id`) USING BTREE
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
