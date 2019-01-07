DROP TABLE IF EXISTS `cost_month_log`;
CREATE TABLE `cost_month_log` (
    `log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '0' COMMENT '月结单号',
    `user_code` VARCHAR (30) NOT NULL COMMENT '用户代码',
    `user_name` VARCHAR (30) NOT NULL COMMENT '用户名称',
    `sure_status` TINYINT (1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '确认状态',
    `check_status` TINYINT (1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '审核状态',
    `action_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '操作代码',
    `action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
    `action_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
    `action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
    PRIMARY KEY (`log_id`),
    KEY `record_code` (`record_code`),
    KEY `action_time` (`action_time`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '成本月结单操作日志';