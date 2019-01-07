-- ----------------------------
-- Table structure for api_jumei_trade 聚美订单列表
-- ----------------------------
DROP TABLE IF EXISTS `api_jumei_trade`;
CREATE TABLE `api_jumei_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `order_id` varchar(50) NOT NULL COMMENT '交易编号',
  `shipping_system_id` varchar(50) NOT NULL COMMENT '商家仓库编号',
  `payment_method` varchar(20) DEFAULT '0' COMMENT '支付方式',
  `total_products_price` varchar(20) DEFAULT '0.00' COMMENT '商品总金额',
  `delivery_fee` varchar(20) DEFAULT '0.00' COMMENT '运费',
  `balance_paid_amount` varchar(20) DEFAULT '0.00' COMMENT '帐号余额支付金额',
  `price_discount_amount` varchar(20) DEFAULT '0.00' COMMENT '优惠总金额（现金券+满减满折+红包）',
  `promo_card_discount_price` varchar(20) DEFAULT '0.00' COMMENT '现金券优惠总金额',
  `order_discount_price` varchar(20) DEFAULT '0.00' COMMENT '满减满折优惠总金额',
  `red_envelope_discount_price_real` varchar(20) DEFAULT '0.00' COMMENT '红包抵扣金额',
  `payment_amount` varchar(20) DEFAULT '0.00' COMMENT '在线支付金额',
  `status` varchar(20) DEFAULT '' COMMENT '订单状态',
  `prefer_delivery_time_note` varchar(20) DEFAULT '' COMMENT '送货说明(weekend 只周末送货weekday 只工作日送货空     周末和工作日均可)',
  `creation_time` varchar(50) DEFAULT NULL COMMENT '下单时间',
  `timestamp` varchar(50) DEFAULT NULL COMMENT '付款时间',
  `delivery_time` varchar(50) DEFAULT NULL COMMENT '发货时间',
  `completed_time` varchar(50) DEFAULT NULL COMMENT '完成时间',
  `invoice_header` varchar(50) DEFAULT NULL COMMENT '发票抬头',
  `invoice_medium` varchar(50) DEFAULT NULL COMMENT '发票媒介',
  `invoice_contents` varchar(50) DEFAULT NULL COMMENT '开票类型',
  `need_invoice` varchar(50) DEFAULT NULL COMMENT '是否需要发票',
  `receiver_name` varchar(50) DEFAULT '' COMMENT '收货人姓名',
  `address` varchar(255) DEFAULT '' COMMENT '收货人地址-街道(包含省市县的全部地址，地址格式：省-市-县 具体地址)',
  `postalcode` varchar(50) DEFAULT '' COMMENT '邮编',
  `hp` varchar(20) DEFAULT '' COMMENT '手机(聚美要求下单用户必填)',
  `phone` varchar(20) DEFAULT '' COMMENT '固定电话，非必填',
  `email` varchar(20) DEFAULT '' COMMENT '电子邮箱',
  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='聚美订单列表';
