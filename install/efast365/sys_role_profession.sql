DROP TABLE IF EXISTS `sys_role_profession`;
CREATE TABLE `sys_role_profession` (
  `sys_role_profession_id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_code` VARCHAR(64) DEFAULT '' COMMENT '角色代码',
  `relate_code` VARCHAR(64) DEFAULT '' COMMENT '相关代码',
  `profession_type` INT(10) UNSIGNED NOT NULL DEFAULT '1' COMMENT '业务类型 1：店铺 2：仓库 3：品牌 ',
  PRIMARY KEY (`sys_role_profession_id`)
) ENGINE=INNODB DEFAULT CHARSET=utf8 COMMENT='业务权限表';