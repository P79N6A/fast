-- ----------------------------
-- Table structure for api_yz_order 有赞订单明细
-- ----------------------------
DROP TABLE IF EXISTS `api_yz_order`;
CREATE TABLE `api_yz_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '有赞订单明细表',
  `num_iid` varchar(50) NOT NULL COMMENT '商品数字编号',
  `sku_id` varchar(50) DEFAULT NULL COMMENT 'Sku的ID',
  `num` varchar(50) DEFAULT NULL COMMENT '商品购买数量',
  `outer_sku_id` varchar(50) DEFAULT NULL COMMENT '商家编码（商家为Sku设置的外部编号）',
  `title` varchar(50) DEFAULT NULL COMMENT '商品标题',
  `seller_nick` varchar(50) DEFAULT NULL COMMENT '卖家昵称',
  `price` varchar(20) DEFAULT NULL COMMENT '商品价格',
  `total_fee` varchar(50) DEFAULT NULL COMMENT '应付金额（商品价格乘以数量的总金额）',
  `discount_fee` varchar(20) DEFAULT '0' COMMENT '子订单级订单优惠金额（目前都是 0）',
  `payment` varchar(20) DEFAULT NULL  COMMENT '实付金额。精确到2位小数，单位：元',
  `sku_properties_name` varchar(50) DEFAULT NULL  COMMENT 'SKU的值，即：商品的规格。如：机身颜色:黑色;手机套餐:官方标配',
  `pic_path` varchar(255) DEFAULT NULL  COMMENT '商品主图片地址',
  `pic_thumb_path` varchar(255) DEFAULT NULL  COMMENT '商品主图片缩略图地址',
  `buyer_messages` varchar(500) DEFAULT NULL  COMMENT '子订单级订单优惠金额（目前都是 0）',
  `tid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`) USING BTREE,
  KEY `num_iid` (`num_iid`) USING BTREE,
  KEY `sku_id` (`sku_id`) USING BTREE,
  KEY `tid` (`tid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
