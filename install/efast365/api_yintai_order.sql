-- ----------------------------
-- Table structure for api_yintai_order 银泰订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_yintai_order`;
CREATE TABLE `api_yintai_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SONumber` varchar(30) DEFAULT '' COMMENT '银泰订单号',
  `OriginalNumber` varchar(40) DEFAULT '' COMMENT '渠道订单号',
  `AddScore` varchar(30) DEFAULT '' COMMENT '买家获得积分',
  `DetailQuantity` varchar(50) DEFAULT '' COMMENT '订单明细数量',
  `BuyCount` varchar(50) DEFAULT '' COMMENT '购买数量',
  `ChildOrderNumber` varchar(50) DEFAULT '' COMMENT '订单明细单号',
  `VendorItemCode` varchar(30) DEFAULT '' COMMENT '供应商商品编码',
  `GoodPrice` varchar(30) DEFAULT '' COMMENT '商品价格',
  `ChildOrderStatus` varchar(30) DEFAULT '' COMMENT '子订单状态',
  `GoodName` varchar(50) NOT NULL COMMENT '商品名称',
  `PayAmount` varchar(30) NOT NULL COMMENT '应付金额',
  `LastCost` varchar(30) DEFAULT '' COMMENT '满减金额',
  `AverageCost` varchar(30) NOT NULL COMMENT '优惠券',
  `ShoppingCurrencyPay` varchar(30) DEFAULT '' COMMENT '银元',
  `PrintDescription` varchar(100) NOT NULL COMMENT '未说明',
  `PropertyDescription` varchar(255) DEFAULT '' COMMENT '未说明（应该是sku属性）',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SONumber_VendorItemCode` (`SONumber`,`VendorItemCode`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
