
DROP TABLE IF EXISTS `base_return_reason`;
CREATE TABLE `base_return_reason` (
  `return_reason_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `return_reason_code` varchar(30) NOT NULL DEFAULT '' COMMENT '代码',
  `return_reason_name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `return_reason_type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '原因类型 1销售 2采购 3批发',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：不启用 1：启用',
  `is_sys` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否系统内置',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`return_reason_id`),
  UNIQUE KEY `idxu_return_reason_code` (`return_reason_code`) USING BTREE,
  UNIQUE KEY `idxu_return_reason_name` (`return_reason_name`) USING BTREE,
  KEY `idx_lastchanged` (`lastchanged`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='商品退货原因表';
