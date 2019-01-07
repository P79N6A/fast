DROP TABLE IF EXISTS `pur_purchaser_record_detail`;

CREATE TABLE `pur_purchaser_record_detail` (
  `purchaser_record_detail_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) DEFAULT '0',
  `mid` INT(11) DEFAULT '0',
  `goods_id` INT(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` INT(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` INT(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
  `sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
  `refer_price` decimal(20,3) DEFAULT '0.000' COMMENT '参考价',
  `price` decimal(20,3) DEFAULT '0.000' COMMENT '单价',
  `rebate` DECIMAL(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` decimal(20,3) DEFAULT '0.000' COMMENT '金额',
  `num` INT(11) DEFAULT '0' COMMENT '入库数量',
  `notice_num` INT(11) DEFAULT '0' COMMENT '通知数量',
  `goods_property` INT(4) DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  `cost_price` decimal(20,3) DEFAULT '0.000' COMMENT '成本单',
  `cost_price_forecast` decimal(20,3) DEFAULT '0.000' COMMENT '预估成本单',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` VARCHAR(128) DEFAULT NULL COMMENT '单据编号',
   UNIQUE KEY `record_sku` (`record_code`,`sku`),
  PRIMARY KEY (`purchaser_record_detail_id`)
) ENGINE=INNODB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COMMENT='商品采购入库单明细表';

