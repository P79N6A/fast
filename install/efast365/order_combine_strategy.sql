-- ----------------------------
-- Table structure for order_combine_strategy 订单合并规则
-- ----------------------------
DROP TABLE IF EXISTS `order_combine_strategy`;
CREATE TABLE `order_combine_strategy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `rule_code` varchar(40) NOT NULL DEFAULT '' COMMENT '规则代码',
  `rule_status_value` varchar(40) NOT NULL DEFAULT '0' COMMENT '规则状态值',
  `rule_desc` varchar(255) NOT NULL DEFAULT '' COMMENT '规则说明',
  `rule_scene_value` varchar(40) NOT NULL DEFAULT '' COMMENT '规则场景值',
  `remark` varchar(255)  NOT NULL DEFAULT '' COMMENT '说明',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_code` (`rule_code`)
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8 COMMENT='订单合并策略';