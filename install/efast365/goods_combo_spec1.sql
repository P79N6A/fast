DROP TABLE IF EXISTS `goods_combo_spec1`;
CREATE TABLE `goods_combo_spec1` (
  `goods_combo_spec1_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `spec1_code` VARCHAR(64) DEFAULT '' COMMENT '颜色代码',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_combo_spec1_id`),
  UNIQUE KEY `goods_code_and_color_code` (`goods_code`,`spec1_code`) USING BTREE
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='套餐商品颜色';
