
DROP TABLE IF EXISTS `sys_role`;
CREATE TABLE `sys_role` (
  `role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_code` varchar(64) DEFAULT '' COMMENT '角色代码',
  `role_name` varchar(128) DEFAULT '' COMMENT '角色名称',
  `role_type` int(4) DEFAULT '0' COMMENT '类型 0:manage风格 1:pos风格',
  `status` int(4) DEFAULT '1' COMMENT '0：停用 1：启用',
  `sys` int(4) DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `role_desc` varchar(255) DEFAULT '' COMMENT '描述',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='系统用户角色';

