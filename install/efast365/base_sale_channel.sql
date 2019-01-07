
DROP TABLE IF EXISTS `base_sale_channel`;
CREATE TABLE `base_sale_channel` (
  `sale_channel_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `sale_channel_code` varchar(30) NOT NULL DEFAULT '' COMMENT '代码',
  `short_code` varchar(30) DEFAULT NULL,
  `sale_channel_name` varchar(30) NOT NULL DEFAULT '' COMMENT '名称',
  `is_system` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：自定义 1：系统定义',
  `is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0：不启用 1：启用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`sale_channel_id`),
  UNIQUE KEY `idxu_sale_channel_code` (`sale_channel_code`) USING BTREE,
  UNIQUE KEY `idxu_sale_channel_name` (`sale_channel_name`) USING BTREE,
  KEY `idx_lastchanged` (`lastchanged`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='销售渠道';

