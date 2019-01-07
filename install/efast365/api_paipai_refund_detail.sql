-- ----------------------------
-- Table structure for api_paipai_refund_detail 拍拍退单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_paipai_refund_detail`;
CREATE TABLE `api_paipai_refund_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dealCode` varchar(30) DEFAULT '' COMMENT '订单编码',
  `dealSubCode` varchar(30) DEFAULT '' COMMENT '子订单id 商品列表里第一个子订单号',
  `itemCode` varchar(40) DEFAULT '' COMMENT '商品编码',
  `itemCodeHistory` varchar(40) DEFAULT '' COMMENT '订单的商品快照编码',
  `itemLocalCode` varchar(40) DEFAULT '' COMMENT '商家自定义编码',
  `skuId` varchar(40) DEFAULT '' COMMENT '商品的库存唯一标识码,由拍拍平台生成',
  `stockLocalCode` varchar(30) DEFAULT '' COMMENT '商品库存编码',
  `stockAttr` varchar(100) DEFAULT '' COMMENT '买家下单时选择的库存属性',
  `refundInfoList` text  COMMENT '退款单信息列表(json)',
  `shop_code` varchar(30) NOT NULL COMMENT 'efast商店代码',  
  PRIMARY KEY (`id`),
  KEY `dealCode` (`dealCode`,`dealSubCode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='拍拍退单明细';
