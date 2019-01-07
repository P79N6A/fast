
DROP TABLE IF EXISTS `base_express_company`;
CREATE TABLE `base_express_company` (
  `company_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_code` varchar(128) NOT NULL DEFAULT '' COMMENT '代码',
  `company_name` varchar(128) NOT NULL DEFAULT '' COMMENT '名称',
  `rule` varchar(500) DEFAULT NULL COMMENT '匹配规则',
  `sys` int(4) NOT NULL DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `is_active` tinyint(4) DEFAULT '0' COMMENT '0：不启用 1：启用',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `api_content` text,
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
