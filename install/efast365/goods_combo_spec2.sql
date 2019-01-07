DROP TABLE IF EXISTS `goods_combo_spec2`;
CREATE TABLE `goods_combo_spec2` (
  `goods_combo_spec2_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec2_code` VARCHAR(64) DEFAULT '' COMMENT '尺码代码',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_combo_spec2_id`),
  UNIQUE KEY `goods_code_and_size_code` (`goods_code`,`spec2_code`) USING BTREE
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='套餐商品尺码';