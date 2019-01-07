
DROP TABLE IF EXISTS `sys_company_info`;
CREATE TABLE `sys_company_info` (
  `company_info_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `company_name` varchar(1000) DEFAULT '' COMMENT '公司名称',
  `company_address` varchar(255) DEFAULT '' COMMENT '公司地址',
  `tel` varchar(128) DEFAULT '' COMMENT '电话',
  `zipcode` varchar(128) DEFAULT '' COMMENT '邮编',
  `email` varchar(255) DEFAULT '' COMMENT '邮箱',
  `fax` varchar(128) DEFAULT '' COMMENT '传真',
  `website` varchar(255) DEFAULT '' COMMENT '公司网站',
  `bank` varchar(255) DEFAULT '' COMMENT '开户银行',
  `account_name` varchar(128) DEFAULT '' COMMENT '账号名称',
  `account` varchar(128) DEFAULT '' COMMENT '账号',
  `tax_account` varchar(128) DEFAULT '' COMMENT '税号',
  `legal_person` varchar(128) DEFAULT '' COMMENT '法人',
  `sys_title` varchar(255) DEFAULT '' COMMENT '系统标题',
  `hardware_manage` int(4) DEFAULT '0',
  `hardware_pos` int(4) DEFAULT '0',
  `hardware_no` varchar(64) DEFAULT '',
  `hardware_sn` varchar(64) DEFAULT '' COMMENT '加密狗licenNo和CompanyName的md5值',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`company_info_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='公司信息';

