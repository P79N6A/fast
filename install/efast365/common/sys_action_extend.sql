
DROP TABLE IF EXISTS `sys_action_extend`;
CREATE TABLE `sys_action_extend` (
  `action_id` int(11) NOT NULL,
  `extend_code` varchar(50) NOT NULL DEFAULT 'efast5_Standard' COMMENT 'efast5_Standard普通版本，efast5_Ultimate旗舰版本，efast5_Enterprise企业版本',
  PRIMARY KEY (`action_id`,`extend_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='版本不包含功能';


-- ----------------------------
-- Records of sys_action_extend
-- ----------------------------
INSERT INTO `sys_action_extend` VALUES ('1010701', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010702', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010703', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1010800', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('7020100', 'efast5_Standard');
INSERT INTO `sys_action_extend` VALUES ('1030106', 'efast5_Standard');