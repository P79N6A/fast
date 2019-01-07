DROP TABLE IF EXISTS `goods_combo`;
CREATE TABLE `goods_combo` (
  `goods_combo_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_code` VARCHAR(64) DEFAULT '' COMMENT '商品代码',
  `goods_name` VARCHAR(128) DEFAULT '' COMMENT '商品名称',
  `price` DECIMAL(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `goods_desc` VARCHAR(255) DEFAULT '' COMMENT '详细描述',
  `status` INT(4) DEFAULT '0' COMMENT '0：启用 1：停用',
  `create_time` DATETIME DEFAULT NULL COMMENT '添加时间',
  `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`goods_combo_id`),
  UNIQUE KEY `goods_code` (`goods_code`) USING BTREE,
  KEY `_statsu` (`status`) USING BTREE
) ENGINE=INNODB  DEFAULT CHARSET=utf8 COMMENT='商品套餐';