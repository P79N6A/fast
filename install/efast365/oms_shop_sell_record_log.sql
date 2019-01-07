DROP TABLE IF EXISTS `oms_shop_sell_record_log`;
CREATE TABLE `oms_shop_sell_record_log` (
    `log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
    `user_code` VARCHAR(30) NOT NULL COMMENT '操作人代码',
    `user_name` VARCHAR(30) NOT NULL COMMENT '操作人',
    `action_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '操作代码',
    `action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
    `pay_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单付款状态：0-未付款（默认）;1-部分付款（正在支付）;2-已付款',
    `send_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单发货(或买家自提)状态：0-未发货（默认）;1-已发货',
    `action_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '操作时间',
    `action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
    PRIMARY KEY (`log_id`),
    KEY `record_code` (`record_code`),
    KEY `action_code` (`action_code`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单操作日志信息';