-- ----------------------------
-- Table structure for api_weimob_trade 微盟订单列表
-- ----------------------------
DROP TABLE IF EXISTS `api_weimob_trade`;
CREATE TABLE `api_weimob_trade` (
  `o_id` int(11) NOT NULL AUTO_INCREMENT COMMENT '',
  `Id` varchar(20) NOT NULL  COMMENT '订单标识',
  `AId` varchar(20) NOT NULL  COMMENT '商户标识',
  `OrderNo` varchar(50) NOT NULL COMMENT '订单编号',
  `MemberId` varchar(50) NOT NULL COMMENT '会员标识',
  `OpenId` varchar(50) NOT NULL COMMENT '用户在微信每公众号下的唯一标识',
  `MemberName` varchar(50) DEFAULT NULL COMMENT '会员昵称',
  `Source` varchar(50) NOT NULL COMMENT '订单来源',
  `OrderType` int(11) DEFAULT 1  COMMENT '订单类型(1普通订单，2众筹订单), ',
  `TotalAmount` varchar(20) DEFAULT 0 COMMENT ' 商品总金额(不含运费)',
  `DiscountAmount` varchar(20) DEFAULT 0 COMMENT '折扣金额(满减满折等扣减金额)',
  `RealAmount` varchar(20) DEFAULT 0 COMMENT '实际金额(含运费),',
  `DeliveryFee` varchar(50) DEFAULT 0 COMMENT '配送费用,',
  `RedPackageId` int(11) DEFAULT 0 COMMENT '红包编号',
  `RedPackageAmount` varchar(20) DEFAULT 0 COMMENT '红包金额',
  `CouponsNo` varchar(50) DEFAULT NULL COMMENT '优惠券编号',
  `CouponsAmount` varchar(20) DEFAULT 0 COMMENT '优惠券金额',
  `PointsAmount` varchar(20) DEFAULT 0 COMMENT '积分金额',
  `PointsUse` int(11) DEFAULT 0 COMMENT '积分',
  `DiscountInfo` varchar(50) DEFAULT NULL COMMENT '优惠信息',
  `BalanceAmount` varchar(20) DEFAULT 0 COMMENT '余额支付金额',
  `IsOnlinePay` varchar(20) DEFAULT NULL COMMENT '是否在线支付',
  `OrderStatus` int(5) DEFAULT 0 COMMENT '0=未处理,1= 已处理,2=已完成,3= 已关闭',
  `Complete` datetime DEFAULT NULL COMMENT '订单完成时间',
  `PayStatus` int(5) DEFAULT 0 COMMENT '支付状态',
  `PayTime` datetime DEFAULT NULL COMMENT '付款时间',
  `PaymentId` varchar(20) DEFAULT '' COMMENT '付款方式编号',
  `PaymentType` varchar(20) DEFAULT '' COMMENT '付款方式',
  `PaymentName` varchar(20) DEFAULT '' COMMENT '付款方式名称',
  `DeliveryStatus` varchar(20) DEFAULT 0 COMMENT ' 配送状态(0=未发货，1=已发货，2=已收货)',
  `DeliveryType` varchar(20) DEFAULT '' COMMENT '配送方式',
  `Carrier` varchar(20) DEFAULT '' COMMENT '承运商',
  `DeliveryNo` varchar(50) DEFAULT '' COMMENT '订单号',
  `ConsigneeName` varchar(50) DEFAULT NULL COMMENT '收货人的姓名',
  `ConsignorName` varchar(50) DEFAULT NULL COMMENT '发货人的名称',
  `ConsignorTel` varchar(50) DEFAULT NULL COMMENT '发货人电话',
  `ConsignorAddress` varchar(50) DEFAULT '0' COMMENT '发货人地址',
  `ConsigneeAddress` varchar(255) DEFAULT NULL COMMENT '收货人的详细地址 带省市区',
  `DeliveryTime` datetime DEFAULT NULL COMMENT '发货时间',
  `ReceivingTime` datetime DEFAULT NULL COMMENT '收获时间',
  `ConsigneeTel` varchar(20) DEFAULT NULL COMMENT '收货人的手机号码',
  `EvaluationStatus` tinyint(1) DEFAULT 0 COMMENT '评价状态0＝未评价，1＝已评价',
  `TotalQty` int(11) DEFAULT '0' COMMENT '商品购买数量',
  `TotalWeight` varchar(20) DEFAULT 0 COMMENT '商品总重量',
  `OrderFlagColor` varchar(20) DEFAULT NULL COMMENT '订单标识颜色',
  `OrderFlagContent` varchar(255) DEFAULT NULL COMMENT '订单标识内容',
  `Remark` varchar(255) DEFAULT NULL COMMENT '备注',
  `CreateTime` datetime DEFAULT NULL COMMENT '创建时间',
  `LastUpdateTime` datetime DEFAULT NULL COMMENT '最后更新时间',
  `CreateBy` varchar(50) DEFAULT NULL COMMENT '创建人',
  `LastUpdateBy` varchar(50) DEFAULT NULL COMMENT '最后更新人',
  `IsDelete` varchar(20) DEFAULT NULL COMMENT '是否删除',
  `IsMemberDelete` varchar(20) DEFAULT NULL COMMENT '是否会员删除',
  `IsMemberClose` varchar(20) DEFAULT NULL COMMENT '是否会员关闭',
  `shop_code` varchar(50) DEFAULT NULL COMMENT '商店代码',
  PRIMARY KEY (`o_id`),
  UNIQUE KEY `Id` (`Id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='微盟订单列表';
