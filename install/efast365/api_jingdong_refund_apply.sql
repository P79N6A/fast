-- ----------------------------
-- Table structure for api_jingdong_refund_apply
-- ----------------------------
DROP TABLE IF EXISTS `api_jingdong_refund_apply`;
CREATE TABLE `api_jingdong_refund_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createdDate` varchar(50) DEFAULT NULL COMMENT '创建时间',
  `financeId` varchar(50) DEFAULT NULL COMMENT '退款单据编号',
  `opName` varchar(50) DEFAULT NULL COMMENT '操作人 ',
  `opPin` varchar(50) DEFAULT NULL COMMENT '操作帐号',
  `payMoney` varchar(50) DEFAULT NULL COMMENT '支付金额',
  `refundMoney` varchar(50) DEFAULT NULL COMMENT '退款金额',
  `wareId` varchar(50) DEFAULT NULL COMMENT '商品编号',
  `account` varchar(50) DEFAULT NULL COMMENT '开户名',
  `bank` varchar(50) DEFAULT NULL COMMENT '支行',
  `bilv` varchar(50) DEFAULT NULL COMMENT '退货比率(百分比) ',
  `carriage` varchar(50) DEFAULT NULL COMMENT '运费',
  `idFinance` varchar(50) DEFAULT NULL COMMENT '申请单号',
  `margReason` varchar(50) DEFAULT NULL COMMENT '差额原因 ',
  `notes` varchar(50) DEFAULT NULL COMMENT '申请备注',
  `orderId` varchar(50) DEFAULT NULL COMMENT '订单号',
  `reason` varchar(50) DEFAULT NULL COMMENT '退货原因',
  `rebate` varchar(50) DEFAULT NULL COMMENT '折旧费',
  `refundment` varchar(50) DEFAULT NULL COMMENT '差额',
  `type` varchar(50) DEFAULT NULL COMMENT '退款方式文字描述',
  `mark` varchar(50) DEFAULT NULL COMMENT '退款备注 ',
  `afsRefundId` varchar(50) DEFAULT NULL COMMENT '退款信息ID ',
  `afsServiceId` varchar(50) DEFAULT NULL COMMENT '服务单号 ',
  `payInfo` varchar(50) DEFAULT NULL COMMENT '支付来源情况',
  `suggestAmount` varchar(50) DEFAULT NULL COMMENT '商家建议京东退款的金额',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `refund_order_sku__item_id` (`afsServiceId`,`orderId`,`wareId`,`afsRefundId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='京东退款单原始数据明细';