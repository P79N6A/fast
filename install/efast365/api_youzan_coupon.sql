-- ----------------------------
-- Table structure for api_youzan_coupon 有赞订单优惠券
-- ----------------------------
DROP TABLE IF EXISTS `api_youzan_coupon`;
CREATE TABLE `api_youzan_coupon` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `tid` varchar(50) NOT NULL COMMENT '交易编号',
  `coupon_id` int(11) NOT NULL COMMENT '该组卡券的ID',
  `coupon_name` varchar(50) DEFAULT NULL COMMENT '该组卡券的名称',
  `coupon_type` varchar(20) DEFAULT NULL COMMENT '卡券的类型。可选值：PROMOCARD（优惠券）、PROMOCODE（优惠码）',
  `coupon_content` varchar(50) DEFAULT NULL COMMENT '卡券内容。当卡券类型为优惠码时，值为优惠码字符串',
  `coupon_description` varchar(50) DEFAULT NULL COMMENT '全店都通用	卡券的说明',
  `coupon_condition` varchar(50) DEFAULT NULL COMMENT '卡券使用条件说明',
  `used_at` datetime DEFAULT NULL COMMENT '使用时间',
  `discount_fee` decimal(20,2) DEFAULT '0.00' COMMENT '优惠的金额，单位：元，精确到小数点后两位',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='有赞订单优惠券';
