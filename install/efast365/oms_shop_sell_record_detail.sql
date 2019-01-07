DROP TABLE IF EXISTS `oms_shop_sell_record_detail`;
CREATE TABLE `oms_shop_sell_record_detail` (
    `sell_goods_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
    `goods_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '商品编码',
    `sku` VARCHAR (30) DEFAULT '' COMMENT '商品sku',
    `num` INT (11) DEFAULT '0' COMMENT '销售数量',
    `return_num` INT (11) NOT NULL DEFAULT '0' COMMENT '退货数量',
    `lock_num` INT (10) NOT NULL DEFAULT '0' COMMENT '库存占用数量',
    `price` DECIMAL (10, 3) DEFAULT '0.000' COMMENT '商品吊牌价',
    `rebate` decimal(4,3) DEFAULT '1.000' COMMENT '折扣',
    `goods_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商品总金额',
    `avg_money` DECIMAL (10, 3) NOT NULL DEFAULT '0.000' COMMENT '均摊金额',
    `is_gift` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '赠品标识：0-普通商品（默认）;1-赠品',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`sell_goods_id`),
    UNIQUE KEY idxu_key (record_code,sku,is_gift),
    KEY `record_code` (`record_code`),
    KEY `goods_code` (`goods_code`),
    KEY `sku` (`sku`),
    KEY `is_gift` (`is_gift`),
    KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单商品信息';