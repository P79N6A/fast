-- ----------------------------
-- Table structure for api_yintai_return_detail
-- ----------------------------
DROP TABLE IF EXISTS `api_yintai_return_detail`;
CREATE TABLE `api_yintai_return_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SoNumber` varchar(50) DEFAULT NULL COMMENT '银泰订单号',
  `RMANumber` varchar(50) NOT NULL COMMENT 'RMA单号',
  `ItemCode` varchar(50) DEFAULT NULL COMMENT '商品编码',
  `ItemName` varchar(50) DEFAULT NULL COMMENT '商品名称',
  `VendorItemCode` varchar(50) DEFAULT NULL COMMENT '供应商商品编码',
  `UnitPrice` varchar(50) DEFAULT NULL COMMENT '售价',
  `Quantity` varchar(50) DEFAULT NULL COMMENT '数量',
  `CouponsAmount` varchar(50) DEFAULT NULL COMMENT '优惠券分摊金额',
  `LastCostAmount` varchar(50) DEFAULT NULL COMMENT '满减分摊金额',
  `YinTaiAmount` varchar(50) DEFAULT NULL COMMENT '银元分摊金额',
  `Point` varchar(50) DEFAULT NULL COMMENT '积分',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SONumber_RMANumber` (`SONumber`,`RMANumber`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='银泰退换货数据明细';