DROP TABLE IF EXISTS `wbm_notice_record_detail`;
CREATE TABLE `wbm_notice_record_detail` (
  `notice_record_detail_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` INT(11) DEFAULT '0',
  `mid` INT(11) DEFAULT '0',
  `goods_id` INT(11) DEFAULT '0' COMMENT 'goods_id',
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec1_id` INT(11) DEFAULT '0' COMMENT 'color_id',
  `spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
  `spec2_id` INT(11) DEFAULT '0' COMMENT 'size_id',
  `spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
  `sku` VARCHAR(128) DEFAULT '' COMMENT 'sku',
  `refer_price` DECIMAL(20,3) DEFAULT '0.000' COMMENT '参考价',
  `price` DECIMAL(20,3) DEFAULT '0.000' COMMENT '单价',
  `rebate` DECIMAL(4,3) DEFAULT '1.000' COMMENT '折扣',
  `money` DECIMAL(20,3) DEFAULT '0.000' COMMENT '金额',
  `num` INT(11) DEFAULT '0' COMMENT '数量',
  `finish_num` INT(11) DEFAULT '0' COMMENT '完成数量',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `record_code` VARCHAR(128) DEFAULT NULL COMMENT '单据编号',
   UNIQUE KEY `record_sku` (`record_code`,`sku`),
  PRIMARY KEY (`notice_record_detail_id`)
) ENGINE=INNODB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='商品批发通知明细表';