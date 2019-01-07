
DROP TABLE IF EXISTS `base_supplier`;
CREATE TABLE `base_supplier` (
  `supplier_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `supplier_code` VARCHAR(128) DEFAULT '' COMMENT '供应商代码',
  `supplier_name` VARCHAR(128) DEFAULT '' COMMENT '供应商名称',
  `area_code` VARCHAR(128) DEFAULT '' COMMENT '区域代码',
  `user_code` VARCHAR(64) DEFAULT '' COMMENT '员工代码',
  `status` INT(4) DEFAULT '0' COMMENT '0：停用 1：启用',
  `contact_person` VARCHAR(128) DEFAULT '' COMMENT '联系人',
  `email` VARCHAR(128) DEFAULT '' COMMENT '邮箱',
  `phone` VARCHAR(128) DEFAULT '' COMMENT '电话',
  `weixin` VARCHAR(128) DEFAULT '' COMMENT '微信',
  `qq` VARCHAR(128) DEFAULT '' COMMENT 'QQ',
  `mobile` VARCHAR(128) DEFAULT '' COMMENT '手机',
  `tel` VARCHAR(128) DEFAULT '' COMMENT '电话',
  `address` VARCHAR(255) DEFAULT '' COMMENT '地址',
  `fax` VARCHAR(128) DEFAULT '' COMMENT '传真',
  `zipcode` VARCHAR(128) DEFAULT '' COMMENT '邮编',
  `website` VARCHAR(255) DEFAULT '' COMMENT '公司网站',
  `rebate` DECIMAL(4,3) DEFAULT '1.000' COMMENT '折扣',
  `price_type` VARCHAR(64) DEFAULT '' COMMENT '价格类型',
  `sys` INT(4) DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`supplier_id`)
) ENGINE=INNODB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='供应商';


