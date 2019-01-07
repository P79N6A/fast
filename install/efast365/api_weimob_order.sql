-- ----------------------------
-- Table structure for api_weimob_order 微盟订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_weimob_order`;
CREATE TABLE `api_weimob_order` (
  `gid` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `Id` varchar(20) NOT NULL  COMMENT '标识',
  `AId` varchar(20) NOT NULL  COMMENT '商户标识',
  `MemberId` varchar(50) NOT NULL COMMENT '会员标识',
  `OpenId` varchar(50) NOT NULL COMMENT '用户在微信每公众号下的唯一标识',
  `OrderId` varchar(20) NOT NULL  COMMENT '订单标识',
  `ItemId` varchar(20) NOT NULL  COMMENT '',
  `ProductId` varchar(20) NOT NULL  COMMENT '',
  `GoodsCode` varchar(50) NOT NULL COMMENT 'spu_code',
  `ProductsCode` varchar(50) NOT NULL COMMENT 'sku_code',
  `ItemName` varchar(255) NOT NULL COMMENT '商品名称',
  `ImageUrl` varchar(255) NOT NULL COMMENT '商品图片',
  `ItemDescription` varchar(255) NOT NULL COMMENT '商品描述',
  `Qty` int(11) DEFAULT '0' COMMENT '商品数量',
  `Price` varchar(20) DEFAULT 0 COMMENT '商品价格',
  `RealPrice` varchar(20) DEFAULT 0 COMMENT '实际价格',
  `Amount` varchar(20) DEFAULT 0 COMMENT '商品金额',
  `RealAmount` varchar(20) DEFAULT 0 COMMENT '实际金额',
  `Evaluation` varchar(255) NOT NULL COMMENT '评价',
  `Reply` varchar(255) NOT NULL COMMENT '回复',
  `CreateBy` varchar(20) DEFAULT 0 COMMENT '创建人',
  `LastUpdateBy` varchar(20) DEFAULT 0 COMMENT '最后更新人',
  `CreateTime` datetime DEFAULT NULL COMMENT '创建时间',
  `LastUpdateTime` datetime DEFAULT NULL COMMENT '最后更新时间',
  `IsDelete` varchar(20) DEFAULT NULL COMMENT '是否删除',
  `InventoryType` varchar(50) NOT NULL COMMENT '减库存类型',
  `ReturnQty` int(11) DEFAULT '0' COMMENT '维权数量',
  `ReturnType` varchar(20) DEFAULT NULL  COMMENT '维权类型(1退款，2退货退款)',
  `ReturnStatus` varchar(20) DEFAULT NULL COMMENT 'ReturnStatus=维权进度(1商家同意退款，2商家同意退货，4等待买家发货，5商家收到退货，开始退款，7已退货退款，8商家拒绝维权，9取消维权，10微盟支付退款处理中)',
  `shop_code` varchar(50) DEFAULT NULL COMMENT '商店代码',
   PRIMARY KEY (`gid`),
  KEY `Id` (`Id`,`OrderId`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='微盟订单明细';
