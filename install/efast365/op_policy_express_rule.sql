DROP TABLE IF EXISTS `op_policy_express_rule`;
CREATE TABLE `op_policy_express_rule` (
  `policy_express_rule_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned NOT NULL COMMENT '策略ID',
  `express_code` varchar(128) NOT NULL COMMENT '配送方式',
  `priority` tinyint(3) unsigned NOT NULL COMMENT '优先级',
  `first_weight` decimal(10,2) unsigned NOT NULL COMMENT '首重-千克',
  `first_weight_price` decimal(10,2) unsigned NOT NULL COMMENT '首重单价',
  `added_weight` decimal(10,2) unsigned NOT NULL COMMENT '续重-千克',
  `added_weight_price` decimal(10,2) unsigned NOT NULL COMMENT '续重单价',
  `added_weight_type` char(20)  NOT NULL DEFAULT 'g0' COMMENT '续重规则 g0实重 g1半重 g2过重',
  `free_credit` decimal(20,6) unsigned NOT NULL COMMENT '免费额度',
  `discount` decimal(20,6) unsigned NOT NULL COMMENT '折扣',
  PRIMARY KEY (`policy_express_rule_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略-规则';
