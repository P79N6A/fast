DROP TABLE IF EXISTS `op_gift_strategy_goods`;
CREATE TABLE `op_gift_strategy_goods` (
  `op_gift_strategy_goods_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `op_gift_strategy_detail_id` int(11) NOT NULL DEFAULT '0' COMMENT '明细ID',
  `sku` varchar(128) NOT NULL DEFAULT '' COMMENT 'SKU',
  `is_gift` tinyint(3) NOT NULL DEFAULT '0' COMMENT '0，购买商品，1赠品',
  `num` int(11) NOT NULL DEFAULT '0' COMMENT '购买数量，赠送数量',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `op_gift_strategy_range_id` int(11) NOT NULL DEFAULT '0' COMMENT '金额/数量范围id',
  `is_combo` tinyint(3) DEFAULT '0' COMMENT '0普通商品1套餐',

  PRIMARY KEY (`op_gift_strategy_goods_id`),
  UNIQUE KEY `_index_key` (`op_gift_strategy_detail_id`,`sku`,`is_gift`,`op_gift_strategy_range_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='赠品策略商品明细';
