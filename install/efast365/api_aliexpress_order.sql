DROP TABLE IF EXISTS `api_aliexpress_order`;
CREATE TABLE `api_aliexpress_order` (
  `api_aliexpress_order_id` int(11) unsigned AUTO_INCREMENT,
  `shop_code` varchar(30) DEFAULT '' COMMENT '店铺nick',
  `lotNum` varchar(50) DEFAULT '' COMMENT '',
  `productAttributes` varchar(50) DEFAULT '' COMMENT '商品属性',
  `orderStatus` varchar(50) DEFAULT '' COMMENT '订单状态',
  `productUnit` varchar(50) DEFAULT '' COMMENT '商品单位',
  `skuCode` varchar(50) DEFAULT '' COMMENT '商品编码',
  `productId` varchar(50) DEFAULT '' COMMENT '商品ID',
  `id` varchar(50) DEFAULT '' COMMENT '子订单ID',
  `frozenStatus` varchar(50) DEFAULT '' COMMENT '冻结状态',
  `issueStatus` varchar(50) DEFAULT '' COMMENT '纠纷状态',
  `productCount` varchar(50) DEFAULT '' COMMENT '商品数量',
  `fundStatus` varchar(50) DEFAULT '' COMMENT '资金状态',
  `initOrderAmt` varchar(50) DEFAULT '' COMMENT '子订单初始金额',
  `initOrderAmtCur` varchar(50) DEFAULT '' COMMENT '',
  `productPrice` varchar(50) DEFAULT '' COMMENT '商品价格',
  `productPriceCur` varchar(50) DEFAULT '' COMMENT '',
  `productName` varchar(50) DEFAULT '' COMMENT '商品标题',
  PRIMARY KEY (`api_aliexpress_order_id`),
  unique key(`id`,`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '速卖通订单明细原始数据';