DROP TABLE IF EXISTS `api_jingdong_trade_coupon`;
CREATE TABLE `api_jingdong_trade_coupon` (
  `id` int(11) unsigned AUTO_INCREMENT,
  `order_id` varchar(30) DEFAULT ''  COMMENT '订单编号',
  `coupon_price` varchar(30) DEFAULT '' COMMENT '优惠金额',
  `coupon_type` varchar(30) DEFAULT '' COMMENT '优惠类型:20-套装优惠, 28-闪团优惠,29-团购优惠, 30-单品促销优惠,34-手机红包, 35-满返满送(返现),39-京豆优惠,41-京东券优惠,52-礼品卡优惠,100-店铺优惠',
  `sku_id` varchar(30) DEFAULT '' COMMENT '京东sku编号',
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'API京东优惠信息表';