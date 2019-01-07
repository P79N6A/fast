-- ----------------------------
-- Table structure for api_jingdong_trade 京东订单主表
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_trade`;
CREATE TABLE `api_jingdong_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vender_id` varchar(30) DEFAULT '' COMMENT '商家id',
  `order_id` varchar(30) DEFAULT '' COMMENT '京东订单ID',
  `order_state` varchar(40) DEFAULT '' COMMENT '订单状态（英文）',
  `pay_type` varchar(30) DEFAULT '' COMMENT '支付方式（1货到付款, 2邮局汇款, 3自提, 4在线支付, 5公司转账, 6银行转账）',
  `delivery_type` varchar(255) DEFAULT '' COMMENT '送货（日期）类型（1-只工作日送货(双休日、假日不用送);2-只双休日、假日送货(工作日不用送);3-工作日、双休日与假日均可送货;其他值-返回"任意时间"）',
  `order_total_price` varchar(30) DEFAULT '' COMMENT '订单总金额',
  `order_payment` varchar(30) DEFAULT '' COMMENT '用户应付金额',
  `freight_price` varchar(30) DEFAULT '' COMMENT '商品的运费',
  `seller_discount` varchar(30) DEFAULT '' COMMENT '商家优惠金额',
  `order_state_remark` varchar(255) DEFAULT '' COMMENT '订单状态说明（中文），具体返回值列表请发邮件至jos#jd.com获取',
  `invoice_info` varchar(255) DEFAULT '' COMMENT '发票信息"invoice_info: 不需要开具发票"下无需开具发票；其它返回值请正常开具发票',
  `order_remark` varchar(255) DEFAULT '' COMMENT '买家下单时订单备注',
  `order_start_time` varchar(30) DEFAULT '' COMMENT '下单时间',
  `order_end_time` varchar(30) DEFAULT '' COMMENT '结单时间，如返回信息为“0001-01-01 00:00:00”或“1970-01-01 00:00:00”等特殊值，可认为此订单为未完成状态。',
  `province` varchar(30) DEFAULT '' COMMENT '省',
  `city` varchar(30) DEFAULT '' COMMENT '市',
  `county` varchar(30) DEFAULT '' COMMENT '县',
  `fullname` varchar(30) DEFAULT '' COMMENT '姓名',
  `telephone` varchar(30) DEFAULT '' COMMENT '固定电话',
  `mobile` varchar(30) DEFAULT '' COMMENT '手机',
  `full_address` varchar(255) DEFAULT '' COMMENT '完整地址',
  `shop_code` varchar(255) DEFAULT '' COMMENT 'efast商店代码',
  `order_seller_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单货款金额（订单总金额-商家优惠金额）',
  `cky2_name` varchar(30) DEFAULT '' COMMENT '配送中心名称',
  `vender_remark` varchar(500) DEFAULT NULL COMMENT '商家备注',
  `modified` varchar(100) DEFAULT NULL COMMENT '订单更新时间',
  `return_order` varchar(10) DEFAULT '0' COMMENT '售后订单标记 0:不是换货订单 1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_id` (`order_id`) USING BTREE,
  KEY `cky2_name` (`cky2_name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;