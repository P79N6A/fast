DROP TABLE IF EXISTS `oms_waves_record`;
CREATE TABLE `oms_waves_record` (
  `waves_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(128) NOT NULL COMMENT '单据编号',
  `record_time` datetime NOT NULL COMMENT '制单时间',
  `store_code` varchar(128) NOT NULL COMMENT '仓库代码',
  `express_code` varchar(128) NOT NULL COMMENT '快递公司',
  `sell_record_count` int(11) unsigned NOT NULL COMMENT '订单数量',
  `goods_count` int(11) unsigned NOT NULL COMMENT '商品数量',
  `cancelled_goods_count` int(11) unsigned NOT NULL COMMENT '取消商品数量',
  `valide_goods_count` int(11) unsigned NOT NULL COMMENT '有效商品数量',
  `total_amount` decimal(20,3) unsigned NOT NULL DEFAULT '0.000' COMMENT '总金额',
  `picker` varchar(128) NOT NULL COMMENT '拣货员',
  `is_accept` tinyint(3) unsigned NOT NULL COMMENT '是否验收(0未验收, 1验收通过)',
  `accept_time` datetime NOT NULL COMMENT '通过验收时间',
  `accept_user` varchar(128) NOT NULL COMMENT '验收操作人',
  `is_cancel` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否取消(0未取消, 1取消)',
  `cancel_time` datetime NOT NULL COMMENT '取消时间',
  `cancel_user` varchar(128) NOT NULL COMMENT '取消操作人',
  `is_print_sellrecord` tinyint(3) DEFAULT '0' COMMENT '是否打印订单 0 未打印 1 部分打印 2 全部打印',
  `is_print_express` tinyint(3) DEFAULT '0' COMMENT '是否打印快递单 0 未打印 1 部分打印 2 全部打印',
  `is_print_goods` tinyint(3) DEFAULT '0' COMMENT '是否打印商品',
  `is_deliver` tinyint(3) DEFAULT '0' COMMENT '是否发货',
  `delivery_time` int(11) DEFAULT '0' COMMENT '发货时间',
  PRIMARY KEY (`waves_record_id`),
  KEY `index1` (`is_accept`) USING BTREE,
  KEY `index2` (`store_code`) USING BTREE,
  KEY `index3` (`is_cancel`) USING BTREE,
  KEY `index4` (`is_deliver`) USING BTREE,
  KEY `index5` (`is_print_express`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='波次拣货单';


