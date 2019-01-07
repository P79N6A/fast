DROP TABLE IF EXISTS `order_check_strategy_detail`;
CREATE TABLE `order_check_strategy_detail` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `check_strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规则code',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '商品barcode',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `check_strategy_code_content` (`check_strategy_code`,`content`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='订单审核规则详细表';