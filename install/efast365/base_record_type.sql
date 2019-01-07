
DROP TABLE IF EXISTS `base_record_type`;
CREATE TABLE `base_record_type` (
  `record_type_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `record_type_code` varchar(128) DEFAULT '' COMMENT '单据类型代码',
  `record_type_name` varchar(128) DEFAULT '' COMMENT '单据类型名称',
  `record_type_property` int(4) DEFAULT '1' COMMENT '0-采购进货类型 1-采购退货类型 2-批发发货类型 3-批发退货类型 4-零售订单类型 5-零售退单类型 6-商品配货单类型 7-商店退货单类型 8-库存调整类型 9-入库类型 10-出库类型 11-财务调整 12-费用类型 13-移仓类型 14-问题单类型',
  `sys` int(4) DEFAULT '0' COMMENT '是否是系统值 0：不是 1：是 ',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`record_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 COMMENT='单据类型';
