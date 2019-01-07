-- ----------------------------
-- Table structure for api_yihaodian_refund
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_refund`;
CREATE TABLE `api_yihaodian_refund` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `refundCode` varchar(50) DEFAULT NULL COMMENT '退货单号(退货编号)',
  `orderId` varchar(50) DEFAULT NULL COMMENT '订单ID',
  `orderCode` varchar(50) DEFAULT NULL COMMENT '订单号',
  `refundStatus` varchar(50) DEFAULT NULL COMMENT '退货状态(0:待审核;3:客服仲裁;4:已拒绝;11:退货中-待顾客寄回;12:退货中-待确认退款;13:换货中;27:退款完成;33:换货完成;34:已撤销;40:已关闭)',
  `deliveryFee` varchar(50) DEFAULT NULL COMMENT '退款运费',
  `productAmount` varchar(50) DEFAULT NULL COMMENT '产品退款金额(不包括运费)',
  `contactName` varchar(50) DEFAULT NULL COMMENT '商家联系人(回寄联系人)',
  `contactPhone` varchar(50) DEFAULT NULL COMMENT '商家联系电话',
  `sendBackAddress` varchar(255) DEFAULT NULL COMMENT '商家联系地址',
  `reasonMsg` varchar(200) DEFAULT NULL COMMENT '退货原因',
  `refundProblem` varchar(255) DEFAULT NULL COMMENT '退货问题描述',
  `evidencePicUrls` varchar(50) DEFAULT NULL COMMENT '图片url列表(逗号分隔)',
  `receiverName` varchar(50) DEFAULT NULL COMMENT '顾客姓名',
  `receiverPhone` varchar(50) DEFAULT NULL COMMENT '顾客联系电话',
  `receiverAddress` varchar(50) DEFAULT NULL COMMENT '顾客地址',
  `applyDate` varchar(50) DEFAULT NULL COMMENT '申请时间',
  `merchantMark` varchar(255) DEFAULT NULL COMMENT '备忘类型(0:red,1:dark_yellow,2:yellow,3:green,4:cyan,5:blue,6: purl)',
  `merchantRemark` varchar(50) DEFAULT NULL COMMENT '商家备注',
  `approveDate` varchar(50) DEFAULT NULL COMMENT '审核时间',
  `sendBackDate` varchar(50) DEFAULT NULL COMMENT '顾客寄回时间',
  `rejectDate` varchar(50) DEFAULT NULL COMMENT '拒绝时间',
  `cancelTime` varchar(50) DEFAULT NULL COMMENT '取消时间',
  `expressName` varchar(50) DEFAULT NULL COMMENT '顾客回寄的快递公司',
  `expressNbr` varchar(50) DEFAULT NULL COMMENT '顾客回寄的快递单号',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refundCode` (`refundCode`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '一号店退款退单原始数据';