DROP TABLE IF EXISTS `order_check_strategy`;
CREATE TABLE `order_check_strategy` (
  `strategy_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `check_strategy_code` varchar(128) NOT NULL DEFAULT '' COMMENT '规则code',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：不启用 1：启用',
  `instructions` varchar(255) NOT NULL DEFAULT '0' COMMENT '规则说明',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`strategy_id`),
  UNIQUE KEY `check_strategy_code` (`check_strategy_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='订单审核规则表';
