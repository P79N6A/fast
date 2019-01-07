CREATE TABLE `stm_goods_diy_record` (
  `goods_diy_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_code` varchar(64) DEFAULT '' COMMENT '单据编号',
  `order_time` datetime DEFAULT NULL COMMENT '下单时间',
  `record_time` date DEFAULT NULL COMMENT '业务日期',
  `store_code` varchar(128) DEFAULT '' COMMENT '仓库代码',
  `relation_code` varchar(128) DEFAULT '' COMMENT '关联单号',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `is_sure` int(4) DEFAULT '0' COMMENT '0未确认 1确认',
  `is_execute` int(4) DEFAULT '0' COMMENT '0未执行 1执行',
  `is_add_person` varchar(64) DEFAULT '' COMMENT '添加人',
  `is_add_time` datetime DEFAULT NULL COMMENT '添加时间',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_diy_record_id`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='商品组装单主表';