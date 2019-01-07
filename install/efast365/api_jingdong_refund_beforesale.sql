-- ----------------------------
-- Table structure for api_jingdong_refund_beforesale
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_refund_beforesale`;
CREATE TABLE `api_jingdong_refund_beforesale` (
  `shop_code` varchar(50) DEFAULT NULL COMMENT '店铺代码',
  `id` int(10) DEFAULT 0 COMMENT '退款单id， 主键',
  `status` varchar(50) DEFAULT NULL COMMENT '审核状态 0:待审核，1：商家审核通过，2：商家审核不通过，3：财务审核通过，4：财务审核不通过，5：人工审核通过。',
  `order_id` varchar(50) DEFAULT NULL COMMENT '订单号',
  `buyer_id` varchar(50) DEFAULT NULL COMMENT '客户帐号 ',
  `buyer_name` varchar(50) DEFAULT NULL COMMENT '客户姓名 ',
  `check_time` varchar(50) DEFAULT NULL COMMENT '审核日期',
  `apply_time` varchar(50) DEFAULT NULL COMMENT '申请时间',
  `check_username` varchar(50) DEFAULT NULL COMMENT '审核人 ',
  `apply_refund_sum` varchar(50) DEFAULT NULL COMMENT '退款金额 ',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_order_id` (`id`,`shop_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京东售前退款原始信息（整单退没有商品明细）';