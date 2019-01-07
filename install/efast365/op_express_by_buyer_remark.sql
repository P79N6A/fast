DROP TABLE IF EXISTS `op_express_by_buyer_remark`;
CREATE TABLE `op_express_by_buyer_remark` (
  `op_express_id` int(11) NOT NULL AUTO_INCREMENT,
  `express_code` varchar(128) NOT NULL DEFAULT '' COMMENT '配送方式代码',
  `key_word` varchar(255) NOT NULL DEFAULT '' COMMENT '关键字',
  PRIMARY KEY (`op_express_id`),
  UNIQUE KEY `_index` (`express_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

