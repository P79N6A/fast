-- ----------------------------
-- Table structure for api_dangdang_return_detail
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_return_detail`;
CREATE TABLE `api_dangdang_return_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `returnExchangeCode` varchar(50) DEFAULT NULL COMMENT '退/换货单编号',
  `itemID` varchar(50) NOT NULL COMMENT '商品标识符',
  `itemName` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `itemSubhead` varchar(50) DEFAULT NULL COMMENT '商品副标题',
  `unitPrice` varchar(50) DEFAULT NULL COMMENT '商品在该订单中的购买价格',
  `orderCount` varchar(50) DEFAULT NULL COMMENT '商品在该订单中的购买数量',
  `outerItemID` varchar(50) DEFAULT NULL COMMENT '暂时没有数据，为空',
  `oneLevelReverseReason` varchar(100) DEFAULT NULL COMMENT '一级退换货原因',
  `twoLevelReverseReason` varchar(100) DEFAULT NULL COMMENT '二级退换货原因',
  `reverseDetailReason` varchar(100) DEFAULT NULL COMMENT '退换货详情',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `returnExchangeCode_itemID` (`returnExchangeCode`,`itemID`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='当当退换货数据明细';