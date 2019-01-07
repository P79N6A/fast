DROP TABLE IF EXISTS `base_order_label`;
CREATE TABLE `base_order_label` (
  `order_label_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_label_code` varchar(128) NOT NULL DEFAULT '' COMMENT '标签代码',
  `order_label_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `order_label_img` varchar(128) DEFAULT NULL COMMENT '标签',
  `is_sys` tinyint(1) DEFAULT '0' COMMENT '是否系统内置',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`order_label_id`),
  UNIQUE KEY `idxu_order_label_code` (`order_label_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;