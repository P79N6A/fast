-- ----------------------------
-- Table structure for api_dangdang_order 当当订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_order`;
CREATE TABLE `api_dangdang_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` varchar(30) DEFAULT '' COMMENT '订单编号',
  `itemID` varchar(40) DEFAULT '' COMMENT '商品标识符',
  `outerItemID` varchar(30) DEFAULT '' COMMENT '企业商品标识符',
  `itemName` varchar(50) DEFAULT '' COMMENT '商品名称',
  `itemType` varchar(10) DEFAULT '' COMMENT '0、商品1、赠品',
  `specialAttribute` varchar(255) DEFAULT '' COMMENT '使用“自定义属性名称”，例子如下：<specialAttribute>颜色>>军绿;鞋码>>38</specialAttribute>',
  `marketPrice` varchar(30) DEFAULT '' COMMENT '市场价',
  `is_energySubsidy` varchar(30) DEFAULT '' COMMENT '是否节能补贴商品0 不是节能补贴商品 1 是节能补贴商品',
  `subsidyPrice` varchar(30) DEFAULT '' COMMENT '节能补贴金额',
  `unitPrice` varchar(30) DEFAULT '' COMMENT '成交价',
  `orderCount` varchar(30) DEFAULT '' COMMENT '订购数量',
  `sendGoodsCount` varchar(30) DEFAULT '' COMMENT '发货数量 当订单状态为“已发货”，才返回此项',
  `belongProductsPromoID` varchar(30) DEFAULT '' COMMENT '商品所属商品集合促销编号',
  `productItemId` varchar(30) DEFAULT '' COMMENT '商品明细编号 代表这个商品在这个促销的唯一标示id',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID_itemID` (`orderID`,`itemID`) USING BTREE,
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;
