DROP TABLE IF EXISTS `wbm_store_out_record_detail`;
CREATE TABLE `wbm_store_out_record_detail` (
  `store_out_record_detail_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) DEFAULT '0',
  `mid` INT(11) DEFAULT '0',
  `goods_id` INT(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` INT(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` INT(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
  `sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
  `refer_price` DECIMAL(20,3) DEFAULT '0.00' COMMENT '参考价',
  `price` DECIMAL(20,3) DEFAULT '0.00' COMMENT '单价',
  `rebate` DECIMAL(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` DECIMAL(20,3) DEFAULT '0.00' COMMENT '金额',
  `num` INT(11) DEFAULT '0' COMMENT '数量',
  `enotice_num` INT(11) DEFAULT '0' COMMENT '通知数量',
  `goods_property` INT(4) DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  `cost_price` DECIMAL(20,3) DEFAULT '0.00' COMMENT '成本单',
  `cost_price_forecast` DECIMAL(20,3) DEFAULT '0.00' COMMENT '预估成本单',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `is_fetch` INT(4) DEFAULT '0' COMMENT '0：正常 1：待处理',
  `record_code` VARCHAR(128) DEFAULT NULL COMMENT '单据编号',
  `trd_id` VARCHAR(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` VARCHAR(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` VARCHAR(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  UNIQUE KEY `record_sku` (`record_code`,`sku`),
  PRIMARY KEY (`store_out_record_detail_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='批发销货单明细表';
