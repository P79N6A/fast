
DROP TABLE IF EXISTS `tmp_goods_inv`;
CREATE TABLE `tmp_goods_inv` (
  `search_str` char(32) DEFAULT NULL COMMENT '查询条件MD5后的唯一值',
  `goods_id` int(12) DEFAULT '0' COMMENT '商品ID',
  `goods_code` varchar(200) DEFAULT '' COMMENT '商品代码',
  `goods_inv` int(12) DEFAULT '0' COMMENT '计算后的库存值',
  `search_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' COMMENT '查询条件中的时间终点',
  `add_time` timestamp NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '本条数据的时间戳',
  KEY `search_str` (`search_str`) USING BTREE,
  KEY `goods_id` (`goods_id`) USING BTREE,
  KEY `goods_code` (`goods_code`) USING BTREE,
  KEY `goods_inv` (`goods_inv`) USING BTREE,
  KEY `search_time` (`search_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='库存查询临时表';

