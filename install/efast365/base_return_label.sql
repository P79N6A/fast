DROP TABLE IF EXISTS `base_return_label`;
CREATE TABLE `base_return_label` (
  `return_label_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_label_code` varchar(128) NOT NULL DEFAULT '' COMMENT '标签代码',
  `return_label_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `return_label_img` varchar(128) DEFAULT NULL COMMENT '标签',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`return_label_id`),
  UNIQUE KEY `idxu_return_label_code` (`return_label_code`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;