-- ----------------------------
-- Table structure for api_dangdang_trade 当当订单主表
-- ----------------------------
DROP TABLE IF EXISTS `api_dangdang_trade`;
CREATE TABLE `api_dangdang_trade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` varchar(30) DEFAULT '' COMMENT '订单编号',
  `outerOrderID` varchar(30) DEFAULT '' COMMENT '外部订单编号支持包裹的“外部订单编号”和父订单的“外部订单编号”。',
  `orderState` varchar(40) DEFAULT '' COMMENT '订单状态：100等待到款，101 等待发货（商家后台页面中显示为“等待配货”状态的订单也会返回为“等待发货”），300 已发货，400 已送达，1000 交易成功，-100 取消，1100 交易失败，-200 已拆单，50 等待审核',
  `isCourierReceiptDetail` varchar(30) DEFAULT '' COMMENT '是否已打单',
  `message` varchar(100) DEFAULT '' COMMENT '买家留言',
  `remark` varchar(100) DEFAULT '' COMMENT '备注',
  `label` varchar(30) DEFAULT '' COMMENT '5类固定标记：1红色2黄色3绿色4蓝色5紫色',
  `lastModifyTime` varchar(30) DEFAULT '' COMMENT '最后修改时间',
  `paymentDate` varchar(30) DEFAULT '' COMMENT '付款时间',
  `orderMode` varchar(30) DEFAULT '' COMMENT '订单类型1： 自发2： 代发',
  `sendDate` varchar(30) DEFAULT '' COMMENT '发货日期',
  `isPresale` varchar(255) DEFAULT '' COMMENT '是否为预售期0 不是 1是',
  `OrderOperateList` longtext DEFAULT '' COMMENT 'JSON保存－订单操作列表信息含多条订单操作信息，操作信息见OperateInfo节点',
  `buyerInfo` text DEFAULT '' COMMENT 'JSON保存－买家信息(buyerPayMode:买家付款方式目前有以下几种:货到付款 网上支付 银行汇款 邮局汇款,goodsMoney:本订单商家应收金额,realPaidAmount:买家已支付金额 网银支付金额+礼品卡支付金额+当当账户余额支付金额,deductAmount:网银支付满额减优惠金额,promoDeductAmount:订单级促销优惠金额。包括的促销类型如下：满额减、满额打折,totalBarginPrice:顾客需为订单支付现金,postage:买家支付的邮费,giftCertMoney:买家支付的礼券金额,giftCardMoney:买家支付礼品卡的金额,accountBalance:买家支付账户余额,activityDeductAmount:移动端优惠金额
)',
  `dangdangAccountID` varchar(30) DEFAULT '' COMMENT '顾客当当网帐号的标志符',
  `consigneeName` varchar(30) DEFAULT '' COMMENT '收货人姓名',
  `consigneeAddr` varchar(255) DEFAULT '' COMMENT '收货地址含国家、省、市、区、详细地址',
  `consigneeAddr_State` varchar(30) DEFAULT '' COMMENT '收货国家',
  `consigneeAddr_Province` varchar(30) DEFAULT '' COMMENT '收货省份',
  `consigneeAddr_City` varchar(30) NOT NULL COMMENT '收货市',
  `consigneeAddr_Area` varchar(255) DEFAULT '' COMMENT '收货区',
  `consigneePostcode` varchar(30) DEFAULT '' COMMENT '邮编',
  `consigneeTel` varchar(30) DEFAULT '' COMMENT '收货人固定电话',
  `consigneeMobileTel` varchar(30) DEFAULT '' COMMENT '收货人 移动电话',
  `sendGoodsMode` varchar(100) DEFAULT '' COMMENT '送货方式${快递方式}送货上门${送货时间段}${快递方式}：普通快递 加急快递 邮政平邮 邮政EMS${送货时间段}：周一至周五 周六日及公共假期 时间不限例如：加急快递送货上门，时间不限',
  `sendCompany` varchar(30) DEFAULT '' COMMENT '物流公司名称',
  `sendOrderID` varchar(50) DEFAULT '' COMMENT '物流公司送货单编号',
  `DangdangWarehouseAddr` varchar(255) DEFAULT '' COMMENT '把包裹发到当当仓库地址 商家需要把包裹发到当当仓库地址',
  `PromoList` text DEFAULT '' COMMENT 'json保存－促销信息(promotionID:促销的id,promotionName:促销名称,promotionType:促销的类型编号,promoDicount:单个促销的优惠金额,promoAmount:该促销的订购数量)',
  `receiptName` varchar(50) DEFAULT '' COMMENT '发票抬头',
  `receiptDetails` varchar(100) DEFAULT '' COMMENT '发票内容',
  `receiptMoney` varchar(20) DEFAULT '' COMMENT '发票金额',
  `Is_DangdangReceipt` varchar(30) DEFAULT '' COMMENT '是否由当当代开发票：1：表示“由当当代开发票”2：表示“不由当当代开发票”',  
  `energySubsidy` text DEFAULT '' COMMENT '目前没有返回该信息 json保存－节能补贴信息(individual_or_company:购买方式1 个人2 公司,name:购买姓名,code:个人购买时填写身份证号码,公司购买时填写组织机构代码,bank:开户行,banking_account:银行账号)',

  `shop_code` varchar(255) DEFAULT '' COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`) USING BTREE,
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;