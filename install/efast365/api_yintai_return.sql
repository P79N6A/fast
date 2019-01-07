-- ----------------------------
-- Table structure for api_yintai_return
-- ----------------------------
DROP TABLE IF EXISTS `api_yintai_return`;
CREATE TABLE `api_yintai_return` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `SoNumber` varchar(50) DEFAULT NULL COMMENT '银泰订单号',
  `RmaType` varchar(50) DEFAULT NULL COMMENT '换出单类型1 退货 ,   2 换入,   3 拒收,   4 未送达 ,   5 换出  ',
  `ShippingContactWith` varchar(50) DEFAULT NULL COMMENT '联系人',
  `ShippingCellPhone` varchar(50) DEFAULT NULL COMMENT '手机',
  `ShippingPhone` varchar(50) DEFAULT NULL COMMENT '固话',
  `ShippingProvince` varchar(50) DEFAULT NULL COMMENT '收货人省',
  `ShippingCity` varchar(50) DEFAULT NULL COMMENT '收货人市',
  `ShippingArea` varchar(50) DEFAULT NULL COMMENT '收货人区',
  `ShippingAddress` varchar(255) DEFAULT NULL COMMENT '收货人地址',
  `ShippingZip` varchar(50) DEFAULT NULL COMMENT '邮编',
  `DeliverDate` varchar(50) DEFAULT NULL COMMENT '配送时间',
  `IsCod` varchar(50) DEFAULT NULL COMMENT '是否cod（1 是 0 否）',
  `Status` varchar(50) DEFAULT NULL COMMENT '未说明',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '数据变更时间戳',
  PRIMARY KEY (`id`),
  UNIQUE KEY `SONumber` (`SONumber`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '银泰退换货原始数据';