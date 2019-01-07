DROP TABLE IF EXISTS `base_store_type`;
CREATE TABLE `base_store_type` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`type_code` varchar(128) DEFAULT '' COMMENT '仓库类别代码',
`type_name` varchar(128) DEFAULT '' COMMENT '仓库类别名称',
`remark` varchar(255) DEFAULT '' COMMENT '备注',
`lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
PRIMARY KEY (`id`),
UNIQUE KEY `type_code` (`type_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='仓库类别';



