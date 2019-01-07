-- ----------------------------
-- Table structure for api_dangdang_refund
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_refund`;
CREATE TABLE `api_dangdang_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderId` varchar(50) DEFAULT NULL COMMENT '订单编号',
  `shopId` varchar(50) DEFAULT NULL COMMENT '商家编码',
  `isAgree` varchar(50) DEFAULT NULL COMMENT '审核状态1 未审核2 审核通过3 审核不通过',
  `refundSource` varchar(50) DEFAULT NULL COMMENT '退款来源1部分发,2:配送失败,4:商家自退款(退货),5:换货缺货退款(退货),7:逆向退运费',
  `totalAmount` varchar(50) DEFAULT NULL COMMENT '退款商品总金额',
  `refundAmount` varchar(50) DEFAULT NULL COMMENT '实退金额',
  `refundDate` varchar(50) DEFAULT NULL COMMENT '退款时间',
  `creationDate` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `lastModifiedDate` varchar(50) DEFAULT NULL COMMENT '最后修改时间',
  `remark` varchar(50) DEFAULT NULL COMMENT '备注',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderId` (`orderId`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '当当退款原始数据';