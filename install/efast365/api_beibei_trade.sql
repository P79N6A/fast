-- ----------------------------
-- Table structure for api_beibei_trade 贝贝订单列表
-- ----------------------------
DROP TABLE IF EXISTS `api_beibei_trade`;
CREATE TABLE `api_beibei_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `oid` varchar(50) NOT NULL COMMENT '交易编号',
  `item_num` int(10) DEFAULT '0' COMMENT '商品购买数量',
  `nick` varchar(50) DEFAULT NULL COMMENT '买家昵称',
  `province` varchar(50) DEFAULT NULL COMMENT '收货人的所在省份',
  `city` varchar(50) DEFAULT NULL COMMENT '收货人的所在城市',
  `country` varchar(50) DEFAULT '' COMMENT '收货人的所在地区',
  `address` varchar(255) DEFAULT '' COMMENT '收货人的详细地址（不含省市区）',
  `event_id` varchar(20) DEFAULT '' COMMENT '专场ID',
  `status` varchar(20) DEFAULT '' COMMENT '状态(-1:返回所有,1:待发货,2:已发货,3:已完成)',
  `total_fee` varchar(50) DEFAULT 0 COMMENT '订单总价(已扣除贝贝承担的现金券和积分等费用，为商家实际所得金额 含运费)',
  `shipping_fee` varchar(50) DEFAULT 0 COMMENT '运费',
  `payment` varchar(20) DEFAULT 0 COMMENT '最终价格(用户付款金额)',
  `invoice_type` varchar(10) DEFAULT '' COMMENT '发票类型',
  `invoice_name` varchar(50) DEFAULT '' COMMENT '发票抬头',
  `remark` varchar(255) DEFAULT '' COMMENT '买家留言',
  `seller_remark` varchar(255) DEFAULT '' COMMENT '商家备注',
  `receiver_name` varchar(50) DEFAULT '' COMMENT '收货人的姓名',
  `receiver_phone` varchar(20) DEFAULT '' COMMENT '收货人的手机号码',
  `receiver_address` varchar(255) DEFAULT '' COMMENT '收货人地址',
  `create_time` datetime DEFAULT NULL COMMENT '交易创建时间',
  `pay_time` datetime DEFAULT NULL COMMENT '买家付款时间',
  `ship_time` datetime DEFAULT NULL COMMENT '发货时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `shop_code` varchar(50) DEFAULT '' COMMENT '商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `oid` (`oid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='贝贝订单列表';
