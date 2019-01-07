-- ----------------------------
-- Table structure for api_yamaxun_order 银泰订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_yamaxun_order`;
CREATE TABLE `api_yamaxun_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `AmazonOrderId` varchar(150) DEFAULT '' COMMENT '亚马逊所定义的订单编码',
  `OrderItemId` varchar(150) DEFAULT '' COMMENT '亚马逊所定义的订单商品编码',
  `QuantityOrdered` varchar(30) DEFAULT '' COMMENT '订单中的商品数量',
  `Title` varchar(100) DEFAULT '' COMMENT '商品的名称',
  `ShippingTaxAmount` varchar(30) DEFAULT '' COMMENT '商品配送费用所缴税费',
  `PromotionDiscountAmount` varchar(50) DEFAULT '' COMMENT '报价中的总促销折扣',
  `ConditionId` varchar(50) DEFAULT '' COMMENT '商品的状况。有效值为：New,Used,Collectible,Refurbished,Preorder,Club',
  `ASIN` varchar(50) DEFAULT '' COMMENT '商品的亚马逊商品编码',
  `SellerSKU` varchar(50) DEFAULT '' COMMENT '商品的卖家 SKU',
  `InvoiceRequirement` varchar(30) DEFAULT '' COMMENT '发票要求信息。有效值为：Individual - 买家要求对订单中的每件商品单独开具发票。Consolidated – 买家没有要求对订单中的每件商品单独开具发票。您可以对订单中的所有商品开具一张发票，或者对订单中的每件商品单独开具发票。MustNotSend – 买家不要求开具发票。',
  `InvoiceInformation` varchar(30) DEFAULT '' COMMENT '发票要求信息。有效值为：NotApplicable - 买家不要求开具发票。BuyerSelectedInvoiceCategory – 亚马逊建议使用此操作返回的 BuyerSelectedInvoiceCategory 的值作为发票类目。ProductTitle – 亚马逊建议使用商品名称作为发票类目。',
  `InvoiceTitle` varchar(100) NOT NULL COMMENT '买家指定的发票抬头',
  `BuyerSelectedInvoiceCategory` varchar(30) NOT NULL COMMENT '买家在下订单时选择的发票类目信息',
  `GiftWrapTaxAmount` varchar(30) NOT NULL COMMENT '礼品包装费用所缴税费',
  `QuantityShipped` varchar(30) DEFAULT '' COMMENT '已配送的商品数量',
  `ShippingPriceAmount` varchar(100) NOT NULL COMMENT '商品的配送费用',
  `GiftWrapPriceAmount` varchar(255) DEFAULT '' COMMENT '商品的礼品包装费用',
  `ConditionSubtypeId` varchar(255) DEFAULT '' COMMENT '商品的子状况。有效值为：New,Mint,Very Good,Good,Acceptable,Poor,Club,OEM,Warranty,Refurbished Warranty,Refurbished,Open Box,Any,Other',
  `ItemPriceAmount` varchar(255) DEFAULT '' COMMENT '商品的售价。请注意，订单商品涉及到商品及其数量。即，ItemPrice 的值等于 商品的售价 x 商品的订购数量。另外，ItemPrice 不包括 ShippingPrice 和 GiftWrapPrice',
  `ItemTaxAmount` varchar(255) DEFAULT '' COMMENT '商品价格所缴税费',
  `ShippingDiscountAmount` varchar(255) DEFAULT '' COMMENT '商品配送费用所享折扣',
  `CODFeeAmount` varchar(255) DEFAULT '' COMMENT '货到付款服务收取的费用',
  `CODFeeDiscountAmount` varchar(255) DEFAULT '' COMMENT '货到付款费用的折扣',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AmazonOrderId_OrderItemId` (`AmazonOrderId`,`OrderItemId`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
