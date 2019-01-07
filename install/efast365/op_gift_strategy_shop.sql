
DROP TABLE IF EXISTS `op_gift_strategy_shop`;
CREATE TABLE `op_gift_strategy_shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '策略代码',
  `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '店铺代码',
  PRIMARY KEY (`id`),
  UNIQUE KEY `strategy_code_shop` (`strategy_code`,`shop_code`),
  KEY `strategy_code` (`strategy_code`),
  KEY `shop_code` (`shop_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
