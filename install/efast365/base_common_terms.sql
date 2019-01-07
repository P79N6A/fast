
DROP TABLE IF EXISTS `base_common_terms`;
CREATE TABLE `base_common_terms` (
  `common_terms_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `common_terms_code` varchar(64) DEFAULT '' COMMENT '代码',
  `common_terms_name` varchar(128) DEFAULT '' COMMENT '名称',
  `type` int(4) DEFAULT '0' COMMENT '0：问题片语、1：挂起片语、2：作废片语、3：退货片语',
  `sys` int(4) DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `status` int(4) DEFAULT '0' COMMENT '0：停用 1：启用',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`common_terms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='常用术语';

