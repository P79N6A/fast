DROP TABLE IF EXISTS `base_question_label`;
CREATE TABLE `base_question_label` (
  `question_label_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `question_label_code` varchar(128) NOT NULL DEFAULT '' COMMENT '标签代码',
  `question_label_name` varchar(128) NOT NULL DEFAULT '' COMMENT '标签名称',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：不启用 1：启用',
  `is_sys` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统内置',
  `content` text,
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`question_label_id`),
  UNIQUE KEY `idxu_question_label_code` (`question_label_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='订单设问标签表';

