
DROP TABLE IF EXISTS `api_base_sku`;
CREATE TABLE `api_base_sku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) NOT NULL COMMENT '商品关联ID',
  `sku_id` varchar(50) NOT NULL COMMENT '平台商品SKUID-唯一键，特别注意，通色通码商品，需要维护此表，此时SKU_ID=ITEM_ID',
  `property` varchar(100) DEFAULT '' COMMENT '商品属性，存储淘宝商品的属性，例如商品颜色尺码等',
  `quantit` int(11) DEFAULT '0' COMMENT '商品数量',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '商品价格',
  `modified_on_shop` varchar(50) DEFAULT '' COMMENT '宝贝在平台的最后一次更新时间',
  `status` varchar(50) DEFAULT 'normal' COMMENT 'sku状态。 normal:正常 ；delete:删除',
  `created_on_shop` varchar(50) DEFAULT '' COMMENT '宝贝在平台的创建时间',
  `with_hold_quantity` int(1) DEFAULT '0' COMMENT '如果是拍下减库存，未付款占用的库存',
  `outer_id` varchar(50) DEFAULT '' COMMENT '商家编码',
  `response_conten` text COMMENT '渠道返回的完整的包，可以使用json存储',
  `created` datetime DEFAULT NULL COMMENT '数据创建时间',
  `updated` datetime DEFAULT NULL COMMENT '数据更新时间戳',
  `goods_sku_id` int(11) DEFAULT '0' COMMENT 'goods_sku_id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4366 DEFAULT CHARSET=utf8 COMMENT='平台商品明细表';
