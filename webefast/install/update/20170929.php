<?php

$u['1655'] = array(
    "CREATE TABLE `api_service` (
        `service_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `service_code` varchar(64) NOT NULL DEFAULT '' COMMENT '接口服务商代码',
        `service_desc` varchar(128) NOT NULL DEFAULT '' COMMENT '服务配置描述',
        `service_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '启用状态:0-停用;1-启用',
        `method_type` varchar(20) NOT NULL DEFAULT '' COMMENT '接口类型:0-公共接口;cancel-取消',
        `is_unified` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否为统一标准接口:0-否;1-是',
        `online_date` date DEFAULT NULL COMMENT '上线日期',
        `api_url` varchar(255) NOT NULL DEFAULT '' COMMENT '接口统一地址',
        `api_method_url` varchar(255) NOT NULL DEFAULT '' COMMENT '接口方法地址（非统一标准接口,每个接口方法对应一个访问地址）',
        `api_param_json` varchar(1000) NOT NULL DEFAULT '{}' COMMENT '固定参数',
        `param_value1` varchar(255) DEFAULT NULL COMMENT '不同服务商可自定义,主要用于动态参数',
        `param_value2` varchar(255) DEFAULT NULL COMMENT '不同服务商可自定义,主要用于动态参数',
        PRIMARY KEY (`service_id`),
        UNIQUE KEY `uni_code` (`service_code`,`method_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='接口服务商公共配置';",
    "CREATE TABLE `api_service_relate_archives` (
	`archives_id` INT (10) UNSIGNED NOT NULL AUTO_INCREMENT,
	`service_id` INT (10) NOT NULL DEFAULT '0' COMMENT '关联服务商ID',
	`archives_type` TINYINT (1) DEFAULT NULL COMMENT '档案类型: 1-店铺;2-仓库',
	`archives_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '档案代码',
	PRIMARY KEY (`archives_id`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '接口服务关联系统档案';",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'api_cancel', '', '接口订单拦截', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', now(), '开启后，订单拦截同时会调用接口取消外部系统订单');"
);
$u['1726'] = array(
    "ALTER TABLE `api_order_detail` ADD COLUMN `hope_send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '预售商品计划发货时间';"
);
