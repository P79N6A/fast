
DROP TABLE IF EXISTS `sys_params`;
CREATE TABLE `sys_params` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `param_id` varchar(20) NOT NULL DEFAULT '' COMMENT '系统参数编号',
  `param_code` varchar(40) NOT NULL DEFAULT '' COMMENT '参数代码',
  `parent_code` varchar(40) NOT NULL DEFAULT '' COMMENT '上级参数代码',
  `param_name` varchar(40) NOT NULL DEFAULT '' COMMENT '参数名称',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '参数类型 group text checkbox select',
  `form_desc` varchar(1000) NOT NULL DEFAULT '' COMMENT 'JSON表单描述',
  `value` varchar(255) NOT NULL DEFAULT '' COMMENT '参数值',
  `sort` decimal(8,2) NOT NULL DEFAULT '0.00' COMMENT '参数界面显示排序号',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '说明',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `memo` text COMMENT '说明文字',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_param_code` (`param_code`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COMMENT='系统参数表';

