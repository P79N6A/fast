-- ----------------------------
-- Table structure for api_yintai_trade 银泰订单主表
-- ----------------------------
DROP TABLE IF EXISTS `api_yintai_trade`;
CREATE TABLE `api_yintai_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SONumber` varchar(30) DEFAULT '' COMMENT '银泰订单号',
  `OriginalNumber` varchar(30) DEFAULT '' COMMENT '渠道订单号',
  `ScoreRatio` varchar(40) DEFAULT '' COMMENT '积分数',
  `OrderType` varchar(30) DEFAULT '' COMMENT '订单类型',
  `TradeSuccessTime` varchar(30) DEFAULT '' COMMENT '交易成功时间',
  `TradeCreateTime` varchar(30) DEFAULT '' COMMENT '交易创建时间',
  `BuyerName` varchar(30) DEFAULT '' COMMENT '买家名称',
  `ShipVia` varchar(30) DEFAULT '' COMMENT '物流方式',
  `OrderStatus` varchar(30) DEFAULT '' COMMENT '订单状态',
  `UpdateTime` varchar(255) DEFAULT '' COMMENT '修改时间',
  `PayAmount` longtext COMMENT '应付金额',
  `GoodsAmount` varchar(30) DEFAULT '' COMMENT '商品总金额',
  `ScoreAmout` varchar(30) DEFAULT '' COMMENT '积分金额',
  `ShippingVARCHARge` varchar(255) DEFAULT '' COMMENT '邮费',
  `PaidAmount` varchar(30) DEFAULT '' COMMENT '实付金额',
  `Quantity` varchar(30) DEFAULT '' COMMENT '购买数量',
  `IsCod` varchar(30) NOT NULL COMMENT '是否是cod订单',
  `ShippingAddress` varchar(255) DEFAULT '' COMMENT '收货人地址',
  `ShippingContactWith` varchar(30) DEFAULT '' COMMENT '收货人',
  `ShippingMobilePhone` varchar(30) DEFAULT '' COMMENT '联系方式',
  `ShippingProvince` varchar(30) DEFAULT '' COMMENT '省份',
  `ShippingCity` varchar(100) DEFAULT '' COMMENT '市',
  `ShippingArea` varchar(30) DEFAULT '' COMMENT '县',
  `ShippingPostCode` varchar(50) DEFAULT '' COMMENT '邮编',
  `shop_code` varchar(255) DEFAULT '' COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SONumber` (`SONumber`) USING BTREE,
  KEY `shop_code` (`shop_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;