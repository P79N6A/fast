-- ----------------------------
-- Table structure for api_yihaodian_order 一号店订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_yihaodian_order`;
CREATE TABLE `api_yihaodian_order` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` varchar(30) DEFAULT '' COMMENT '订单明细ID',
  `orderId` varchar(30) DEFAULT '' COMMENT '订单ID',
  `productId` varchar(40) DEFAULT '' COMMENT '产品id',
  `productCName` varchar(30) DEFAULT '' COMMENT '产品名称',
  `orderItemAmount` varchar(30) DEFAULT '' COMMENT '金额',
  `orderItemNum` varchar(255) DEFAULT '' COMMENT '数量',
  `orderItemPrice` varchar(30) DEFAULT '' COMMENT '单价',
  `originalPrice` varchar(30) DEFAULT '' COMMENT '产品原价',
  `groupFlag` varchar(30) DEFAULT '' COMMENT '团购产品标识，1表示团购产品,0表示非团购产品',
  `merchantId` varchar(30) DEFAULT '' COMMENT '商家id',
  `processFinishDate` varchar(30) DEFAULT '' COMMENT '退换货完成时间',
  `updateTime` varchar(30) DEFAULT '' COMMENT '更新时间',
  `outerId` varchar(30) DEFAULT '' COMMENT '产品外部编码',
  `deliveryFeeAmount` varchar(30) DEFAULT '' COMMENT '商品运费分摊金额',
  `promotionAmount` varchar(30) DEFAULT '' COMMENT '促销活动立减分摊金额',
  `couponAmountMerchant` varchar(30) DEFAULT '' COMMENT '商家抵用券分摊金额',
  `couponPlatformDiscount` varchar(30) DEFAULT '' COMMENT '1mall平台抵用券分摊金额',
  `subsidyAmount` varchar(30) DEFAULT '' COMMENT '节能补贴金额',
  `productDeposit` varchar(30) DEFAULT '' COMMENT '单品订金金额',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
