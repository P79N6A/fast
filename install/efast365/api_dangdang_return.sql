-- ----------------------------
-- Table structure for api_dangdang_return
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_return`;
CREATE TABLE `api_dangdang_return` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returnExchangeCode` varchar(50) DEFAULT NULL COMMENT '退/换货单编号',
  `orderID` varchar(50) DEFAULT NULL COMMENT '订单编号',
  `returnExchangeStatus` varchar(50) DEFAULT NULL COMMENT '退货/换货状态（一个是“1退货”，一个是“2换货”，一个是“3代退货”）',
  `orderMoney` varchar(50) DEFAULT NULL COMMENT '该退换货订单的总金额',
  `orderTime` varchar(50) DEFAULT NULL COMMENT '申请退换货的时间',
  `orderStatus` varchar(50) DEFAULT NULL COMMENT '处理状态为以下其中一种：1待处理，2已处理',
  `orderResult` varchar(50) DEFAULT NULL COMMENT '如果“处理状态”是“待处理”，则不返回此项处理结果为以下其中一种：1同意，2拒绝，3延期',
  `returnExchangeOrdersApprStatus` varchar(50) DEFAULT NULL COMMENT '审核状态选项：2：表示“审核不通过”1：表示“审核通过”0：表示“待审核”',
  `warning` varchar(255) DEFAULT NULL COMMENT '未说明',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `returnExchangeCode` (`returnExchangeCode`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '当当退换货原始数据';