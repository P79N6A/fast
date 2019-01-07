-- ----------------------------
-- Table structure for api_yamaxun_trade 亚马逊订单主表
-- ----------------------------
DROP TABLE IF EXISTS `api_yamaxun_trade`;
CREATE TABLE `api_yamaxun_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  
  `AmazonOrderId` varchar(150) DEFAULT '' COMMENT '亚马逊所定义的订单编码',
  `OrderType` varchar(30) DEFAULT '' COMMENT '订单类型',
  `PurchaseDate` varchar(40) DEFAULT '' COMMENT '创建订单的日期',
  `BuyerEmail` varchar(30) DEFAULT '' COMMENT '匿名的买家邮件地址',
  
  `LastUpdateDate` varchar(30) DEFAULT '' COMMENT '订单的最后更新日期',
  `NumberOfItemsShipped` varchar(30) DEFAULT '' COMMENT '已配送的商品数量',
  `ShipServiceLevel` varchar(30) DEFAULT '' COMMENT '货件服务水平',
  `OrderStatus` varchar(30) DEFAULT '' COMMENT '订单状态',
  `SalesChannel` varchar(30) DEFAULT '' COMMENT '订单中第一件商品的销售渠道',
  `ShippedByAmazonTFM` varchar(30) DEFAULT '' COMMENT '指明订单配送方是否是亚马逊配送服务',
  
  `NumberOfItemsUnshipped` varchar(30) DEFAULT '' COMMENT '未配送的商品数量',
  `BuyerName` varchar(30) DEFAULT '' COMMENT '买家姓名',
  `LatestShipDate` varchar(30) DEFAULT '' COMMENT '您承诺的订单发货时间范围的第一天',
  `LatestDeliveryDate` varchar(30) DEFAULT '' COMMENT '您承诺的订单送达时间范围的最后一天',
  `EarliestDeliveryDate` varchar(30) DEFAULT '' COMMENT '您承诺的订单送达时间范围的第一天',
  `EarliestShipDate` varchar(30) NOT NULL COMMENT '您承诺的订单发货时间范围的第一天',
  `OrderTotalAmount` varchar(30) DEFAULT '' COMMENT '订单的总费用',
 
  `MarketplaceId` varchar(30) DEFAULT '' COMMENT '匿名的商城编码，指明订单产生的地点',
  `FulfillmentChannel` varchar(30) DEFAULT '' COMMENT '订单的配送方式：亚马逊配送 (AFN) 或卖家自行配送 (MFN)。',
  `PaymentMethod` varchar(30) DEFAULT '' COMMENT '订单的付款方式',
  
  `StateOrRegion` varchar(30) DEFAULT '' COMMENT '省份',
  `Phone` varchar(100) DEFAULT '' COMMENT '电话',
  `City` varchar(30) DEFAULT '' COMMENT '城市',
  `CountryCode` varchar(50) DEFAULT '' COMMENT '国家代码',
  
  `PostalCode` varchar(50) DEFAULT '' COMMENT '邮编',
  `County` varchar(50) DEFAULT '' COMMENT '区县',
  `Name` varchar(50) DEFAULT '' COMMENT '姓名',
  `AddressLine1` varchar(255) DEFAULT '' COMMENT '地址1',
  `AddressLine2` varchar(255) DEFAULT '' COMMENT '地址2',
  `AddressLine3` varchar(255) DEFAULT '' COMMENT '地址3',
  `IsPrime` varchar(50) DEFAULT '' COMMENT '未说明',
  `ShipmentServiceLevelCategory` varchar(50) DEFAULT '' COMMENT '订单配送服务级别分类。有效值为：Expedited，NextDay，SameDay，SecondDay，Scheduled，Standard',  
  `shop_code` varchar(255) DEFAULT '' COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `AmazonOrderId` (`AmazonOrderId`) USING BTREE,
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;