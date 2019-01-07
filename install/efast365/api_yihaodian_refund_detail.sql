-- ----------------------------
-- Table structure for api_yihaodian_refund_detail
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_refund_detail`;
CREATE TABLE `api_yihaodian_refund_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `refundCode` varchar(50) DEFAULT NULL COMMENT '退货单号(退货编号)',
  `productId` varchar(50) NOT NULL COMMENT '1号店产品ID',
  `productCname` varchar(50) DEFAULT NULL COMMENT '产品名称',
  `orderItemNum` varchar(50) DEFAULT NULL COMMENT '顾客购买数量',
  `orderItemPrice` varchar(50) DEFAULT NULL COMMENT '退货单价',
  `productRefundNum` varchar(50) DEFAULT NULL COMMENT '退货数量',
  `originalRefundNum` varchar(50) DEFAULT NULL COMMENT '用户申请退货数量',
  `orderItemId` varchar(50) DEFAULT NULL COMMENT '订单明细ID',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refundCode_orderItemId` (`refundCode`,`orderItemId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='一号店退单数据明细';