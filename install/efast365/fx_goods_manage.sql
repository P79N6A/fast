DROP TABLE IF EXISTS `fx_goods_manage`;
CREATE TABLE `fx_goods_manage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `goods_line_code` varchar(128) DEFAULT '' COMMENT '产品线代码',
  `goods_line_name` varchar(128) DEFAULT '' COMMENT '产品线名称',
  `goods_num` varchar(128) DEFAULT '' COMMENT '商品总数',
  `sku_num` varchar(128) DEFAULT '' COMMENT 'SKU总数',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `last_change_time` datetime NOT NULL COMMENT '最后更改时间',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `goods_line_code` (`goods_line_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销产品线管理';
