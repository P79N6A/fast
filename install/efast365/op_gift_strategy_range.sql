
DROP TABLE IF EXISTS `op_gift_strategy_range`;
CREATE TABLE `op_gift_strategy_range` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `op_gift_strategy_detail_id` int(11) DEFAULT NULL COMMENT '规则id',
  `range_start` varchar(100) DEFAULT NULL COMMENT '开始范围',
  `range_end` varchar(100) DEFAULT NULL COMMENT '结束范围',
  `give_way` tinyint(3) NOT NULL DEFAULT '0' COMMENT '赠送方式0固定送赠品，1随机送赠品',
  `gift_num` int(11) NOT NULL DEFAULT '1' COMMENT '随机赠送数量',
  PRIMARY KEY (`id`),
  KEY `op_gift_strategy_detail_id` (`op_gift_strategy_detail_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
