DROP TABLE IF EXISTS `base_shop_init`;
CREATE TABLE `base_shop_init` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_code` varchar(50) DEFAULT '' COMMENT '店铺ID',
  `store_code` varchar(50) DEFAULT '' COMMENT '初始化库存的仓库',
  `goods_status` tinyint(3) DEFAULT '0' COMMENT '0未执行，1初始品牌分类，2商品档案，9异常',
  `goods_message` varchar(200) DEFAULT '',
  `goods_load` int(5) DEFAULT NULL,
  `order_task_id` text,
  `order_message` varchar(200) DEFAULT '',
  `order_load` int(5) DEFAULT '0',
  `order_status` tinyint(3) DEFAULT '0' COMMENT '0未执行，1开始，2完成 9异常',
  `inv_message` varchar(200) DEFAULT NULL,
  `inv_status` tinyint(3) DEFAULT '0' COMMENT '0开始，1创建调整单据，2调整库存，9异常',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`shop_code`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8;