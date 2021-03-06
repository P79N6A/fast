DROP TABLE IF EXISTS `sys_user`;
CREATE TABLE `sys_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` varchar(128) DEFAULT '0' COMMENT '角色id',
  `user_code` varchar(64) DEFAULT '' COMMENT '代码',
  `login_type` int(4) DEFAULT '0' COMMENT '0：后台用户 1：门店用户',
  `p_code` varchar(64) DEFAULT '' COMMENT '上级代码',
  `shop_code` varchar(64) DEFAULT '' COMMENT '商店代码',
  `org_code` varchar(128) DEFAULT '000' COMMENT '渠道代码',
  `user_name` varchar(128) DEFAULT '' COMMENT '用户名称',
  `password` varchar(128) DEFAULT '' COMMENT '密码',
  `style` varchar(128) DEFAULT '' COMMENT '风格',
  `status` int(4) DEFAULT '1' COMMENT '1：启用 0：停用',
  `create_mode` int(4) DEFAULT '1' COMMENT '1：正常创建 2：批量创建',
  `init_password` varchar(128) DEFAULT '0' COMMENT '初始密码',
  `department_code` varchar(64) DEFAULT '' COMMENT '部门代码',
  `position_code` varchar(64) DEFAULT '' COMMENT '岗位代码',
  `type` INT(4) DEFAULT '0' COMMENT '0:普通账户 1：店长 2：收银员 3:导购员'
  `relation_shop` VARCHAR(64) DEFAULT '' COMMENT '关联店铺代码',
  `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
  `sex` varchar(64) DEFAULT '0' COMMENT '性别 0:保密 1：男 2：女',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `phone` varchar(64) DEFAULT '' COMMENT '手机号码',
  `tel` varchar(64) DEFAULT '' COMMENT '电话号码',
  `email` varchar(64) DEFAULT '' COMMENT 'email',
  `address` varchar(255) DEFAULT '' COMMENT '工作地址',
  `province` varchar(64) DEFAULT '' COMMENT '所在省或直辖市',
  `city` varchar(64) DEFAULT '' COMMENT '所在城市',
  `is_salesman` tinyint(4) DEFAULT '1' COMMENT '员工性质 是否为业务员 1：是 0：不是',
  `is_customer` tinyint(4) DEFAULT '0' COMMENT '员工性质 是否为客服 1：是 0：不是',
  `is_clerk` tinyint(4) DEFAULT '0' COMMENT '员工性质 是否为店员 1：是 0：不是',
  `is_manage` int(4) DEFAULT '0' COMMENT '是否是主管 1：是 0：不是',
  `is_work` int(4) DEFAULT '1' COMMENT '是否在职 1：是 0：不是',
  `is_login` int(4) DEFAULT '1' COMMENT '是否能登录 1：是 0：不是',
  `is_taobao` int(4) DEFAULT '0' COMMENT '是否是淘宝用户，用于点数控制不计入用户点数 1：是 0：不是',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后一次登录时间',
  `last_login_ip` varchar(128) DEFAULT '' COMMENT '最后一次登录ip',
  `sys` int(4) DEFAULT '0' COMMENT '是否是系统值 1：是 0：不是',
  `weixin_id` varchar(128) DEFAULT '' COMMENT '微信id',
  `favorites` varchar(255) DEFAULT '' COMMENT '收藏夹',
  `remark` varchar(255) DEFAULT '' COMMENT '备注',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `trd_id` varchar(64) DEFAULT '' COMMENT '第三方来源GUID',
  `trd_type` varchar(32) DEFAULT '' COMMENT '第三方来源类型:erp/3000/efast',
  `trd_time` varchar(64) DEFAULT '' COMMENT '第三方导入或者更新的时间',
  `is_default_guide` tinyint(4) DEFAULT '0' COMMENT '是否默认导购员 0否 1是',
  `is_strong` int(4) DEFAULT '0' COMMENT '是否是强密码 1：是 2：不是 0：默认密码未更改',
  `login_fail_num` tinyint(1) DEFAULT '0' COMMENT '登录出错次数,登录成功后要清0',  
  `session_id` varchar(64) DEFAULT '' COMMENT '当前登录用户的session_id',
  `create_person` VARCHAR(50) DEFAULT '' COMMENT '创建人',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `_index_key` (`user_code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='系统用户';



