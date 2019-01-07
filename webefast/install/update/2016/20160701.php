<?php

$u = array();
$u['400'] = array(
    "alter table sys_user change login_type login_type tinyint(3) DEFAULT '0' COMMENT '0：后台用户 1：门店用户 2：分销账户'",
);
$u['412'] = array(
    "DELETE FROM `sys_params` WHERE `param_code` = 'safety_control'",
    "INSERT INTO `sys_params` (`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`, `remark`, `memo`) VALUES('safety_control','security_set','订单敏感数据加密','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','启用后，系统针对敏感数据，比如会员昵称、收货人、手机、收货地址、固定电话模糊化显示。');"
);
$u['429'] = array(
    "INSERT INTO `sys_action` VALUES ('8010400', '8010000', 'url', '分销商等级', 'base/custom_grades/do_list', '1', '1', '0', '1','0');",
    "CREATE TABLE `fx_custom_grades` (
        `grade_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `grade_code` varchar(128) DEFAULT '' COMMENT '等级代码',
        `grade_name` varchar(128) DEFAULT '' COMMENT '等级名称',
        `custom_num` int(11) DEFAULT '0' COMMENT '分销数量',
        `remark` varchar(255) DEFAULT '' COMMENT '备注',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`grade_id`),
        UNIQUE KEY `account_code` (`grade_code`) USING BTREE
      ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商等级表';",
    "CREATE TABLE `fx_custom_grades_detail` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `grade_code` varchar(128) DEFAULT '' COMMENT '等级代码',
    `custom_type` varchar(128) DEFAULT '' COMMENT '分销商类型',
    `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
    `custom_name` varchar(128) DEFAULT '' COMMENT '分销商名称',
    `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`id`)
  ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商等级对应分销商详情表';"
);

$u['bug_314'] = array(
    "DELETE FROM sys_action WHERE action_id in('4030103','4030104','4030107','4030108');"
);
