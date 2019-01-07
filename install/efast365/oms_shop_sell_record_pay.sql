DROP TABLE IF EXISTS `oms_shop_sell_record_pay`;
CREATE TABLE `oms_shop_sell_record_pay` (
    `pay_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
    `pay_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '支付代码：现金/支付宝/微信/余额',
    `pay_account` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '支付账号',
    `pay_serial_no` VARCHAR (50) DEFAULT '' COMMENT '支付流水号',
    `pay_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.00' COMMENT '支付金额',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`pay_id`),
    KEY `record_code` (`record_code`),
    KEY `pay_code` (`pay_code`),
    KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单支付信息';