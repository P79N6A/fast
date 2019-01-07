DROP TABLE IF EXISTS `oms_shop_sell_record_discount`;
CREATE TABLE `oms_shop_sell_record_discount` (
    `discount_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
    `discount_way` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '优惠方式：满减优惠券/积分抵扣',
    `discount_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.00' COMMENT '优惠金额',
    `discount_desc` VARCHAR (100) DEFAULT '' COMMENT '优惠描述',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`discount_id`),
    KEY `record_code` (`record_code`),
    KEY `discount_way` (`discount_way`),
    KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单优惠信息';