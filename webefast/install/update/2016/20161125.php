<?php

$u = array();

$u['829'] = array(
    "UPDATE `sys_action` SET `action_name`='资金账户' WHERE (`action_id`='9060100');",
    "CREATE TABLE alipay_key
(
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`pid` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '支付宝的pid',
	`key` VARCHAR(128) NOT NULL DEFAULT '' COMMENT '支付宝的key',
	`status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未启用 1启用',
	`remark` varchar(255) DEFAULT '' COMMENT '备注',
	`lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='阿里支付key存储表';",

);



$u['859']=array(
   "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'fuzzy_search', 'oms_property', 'S001_115  手机号/买家昵称模糊检索', 'radio', '[\"关闭\",\"开启\"]', '1', '0.00', '1-开启 0-关闭', '2016-11-08 15:59:32', '默认开启，即输入手机号/买家昵称模糊检索。关闭后，仅支持输入完整的手机号/买家昵称，否则检索不到。');"

);

$u['bug_724'] = array(
    'ALTER TABLE `goods_inv_record`
ADD INDEX `record_time` (`record_time`) USING BTREE ;',

);