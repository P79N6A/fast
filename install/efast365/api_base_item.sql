
DROP TABLE IF EXISTS `api_base_item`;
CREATE TABLE `api_base_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` varchar(50) NOT NULL COMMENT '平台商品ID-唯一键',
  `shop_code` varchar(50) NOT NULL COMMENT '店铺代码',
  `seller_nick` varchar(50) NOT NULL COMMENT '卖家昵称（店铺登陆主账号）',
  `pic_url` varchar(150) DEFAULT '' COMMENT '商品主图',
  `quantit` int(11) DEFAULT '0' COMMENT '商品数量',
  `price` decimal(10,2) DEFAULT '0.00' COMMENT '商品价格',
  `modified_on_shop` varchar(50) DEFAULT '' COMMENT '宝贝在平台的最后一次更新时间',
  `approve_status` int(1) DEFAULT '1' COMMENT '商品上传后的状态。0出售中，1库中',
  `created_on_shop` varchar(50) DEFAULT '' COMMENT '宝贝在平台的创建时间',
  `is_fenxiao` int(1) DEFAULT '0' COMMENT ' 非分销商品：0，代销：1，经销：2 ',
  `sub_stock` int(1) DEFAULT '1' COMMENT '商品是否支持拍下减库存:1支持;2取消支持(付款减库存);0(默认)不更改 集市卖家默认拍下减库存; 商城卖家默认付款减库存',
  `with_hold_quantity` int(1) DEFAULT '0' COMMENT '如果是拍下减库存，未付款占用的库存',
  `has_sku` int(1) DEFAULT '1' COMMENT '有SKU：1，无SKU(通色通码类型)：0',
  `outer_id` varchar(50) DEFAULT '' COMMENT '商家编码',
  `title` varchar(255) DEFAULT '' COMMENT '平台商品标题',
  `response_conten` text COMMENT '渠道返回的完整的包，可以使用json存储',
  `created` datetime DEFAULT NULL COMMENT '数据创建时间',
  `updated` datetime DEFAULT NULL COMMENT '数据更新时间戳',
  `goods_id` int(11) unsigned DEFAULT NULL COMMENT '关联base_goods的goods_id',
  `is_generate_inv` int(1) DEFAULT '0' COMMENT '是否生成库存调整单0:未生成,1:已生成',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=459 DEFAULT CHARSET=utf8 COMMENT='平台商品表';

