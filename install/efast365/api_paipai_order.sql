-- ----------------------------
-- Table structure for api_paipai_order 拍拍订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_paipai_order`;
CREATE TABLE `api_paipai_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dealCode` varchar(30) DEFAULT '' COMMENT '订单编码',
  `dealSubCode` varchar(30) DEFAULT '' COMMENT '子订单id',
  `itemCode` varchar(40) DEFAULT '' COMMENT '商品编码',
  `itemCodeHistory` varchar(40) DEFAULT '' COMMENT '订单的商品快照编码',
  `itemLocalCode` varchar(40) DEFAULT '' COMMENT '商家自定义编码',
  `skuId` varchar(40) DEFAULT '' COMMENT '商品的库存唯一标识码,由拍拍平台生成',
  `stockLocalCode` varchar(30) DEFAULT '' COMMENT '商品库存编码',
  `stockAttr` varchar(100) DEFAULT '' COMMENT '买家下单时选择的库存属性',
  `itemDetailLink` varchar(100) DEFAULT '' COMMENT '商品详情的url',
  `itemName` varchar(30) DEFAULT '' COMMENT '产品名称',
  `itemPic80` varchar(100) DEFAULT '' COMMENT '商品图片的url',
  `itemRetailPrice` varchar(20) DEFAULT '0' COMMENT '商品的原价 暂时不起作用',
  `itemDealPrice` varchar(20) DEFAULT '0' COMMENT '买家下单时的商品价格',
  `itemAdjustPrice` varchar(20) DEFAULT '0' COMMENT '订单的调整价格:正数为订单加价,负数为订单减价',
  `itemDiscountFee` varchar(20) DEFAULT '0' COMMENT '购买商品的红包值、折扣优惠价。。。',
  `itemDealCount` varchar(50) DEFAULT '' COMMENT '购买的数量',
  `account` varchar(30) DEFAULT '' COMMENT '充值帐号（点卡类商品订单中才有意义）',
  `itemFlag` varchar(30) DEFAULT '',
  `tradePropertymask` varchar(30) DEFAULT '' COMMENT '自订单属性串，多个属性之间用下划线_隔开1=商品是否有抵押金 2=买家发送货时快递发送 4=参加了直通车推广的商品 8=子单打款包含了邮费 64=商品是虚拟商品 128=需要隐藏购买者信息 256=有第三方信息 512=无评价入口 1024=本商品是通过直通车商品链接关联购买的订单 2048=7天免邮包退 4096=买家使用了拍拍红包 8192=自动发货卡密订单 16384=支持货到付款订单 32768=卖家标注缺货 65536=拍下即扣的auction1商品 131072=团购商品标识 262144=消保订单14天包退 524288=消保订单 7天包退 1048576=订单查询Flag， 手机订单。 2097152=订单对买家屏蔽收货地址信息 4194304=闪电发货 8388608=正品，假一罚三 16777216=拍下即扣减商品数(今日特价商品) 33554432=卖家实收款项金额为0 67108864=特权商品VIP价格，会员价 134217728=女裳 268435456=预存款自动发货 536870912=买家是QQ会员 1073741824=QQ会员店',
  `availableAction` varchar(30) DEFAULT '',
  `wanggouQuanId` varchar(30) DEFAULT '' COMMENT '网购现金券ID',
  `wanggouQuanAmt` varchar(30) DEFAULT '' COMMENT '网购现金券金额',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',
  PRIMARY KEY (`id`),
  KEY `dealCode` (`dealCode`,`dealSubCode`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='拍拍订单明细';
