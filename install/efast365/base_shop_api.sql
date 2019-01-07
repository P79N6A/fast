
DROP TABLE IF EXISTS `base_shop_api`;
CREATE TABLE `base_shop_api` (
  `shop_api_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(40) NOT NULL COMMENT '商店代码',
  `source` varchar(20) NOT NULL DEFAULT '' COMMENT '来源',
  `api` text COMMENT 'api参数',
  `tb_shop_type` varchar(10) NOT NULL DEFAULT 'C' COMMENT '淘宝店铺类型。可选值:B(B商家),C(C商家)',
  `order_update_time` datetime DEFAULT NULL COMMENT '订单最后更新时间',
  `order_return_update_time` datetime DEFAULT NULL COMMENT '退单订单最后更新时间',
  `on_sale_goods_update_time` datetime DEFAULT NULL COMMENT '商品最后更新时间',
  `inv_goods_update_time` datetime DEFAULT NULL COMMENT '商品最后更新时间',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_xiaodai` int(4) NOT NULL DEFAULT '0' COMMENT '是否回流给阿里小贷接口 0:未处理, 1已处理, 已处理的不再进行处理',
  `nick` varchar(40) NOT NULL DEFAULT '',
  `app_key` varchar(200) NOT NULL DEFAULT '',
  `app_secret` varchar(200) NOT NULL DEFAULT '',
  `session_key` varchar(200) NOT NULL DEFAULT '',
  `extra_params` text,
  `kh_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`shop_api_id`),
  UNIQUE KEY `shop_code` (`shop_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='商店api参数';