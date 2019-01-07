DROP TABLE IF EXISTS `base_shop_sku`;
CREATE TABLE base_shop_sku (
    `shop_sku_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `shop_code` VARCHAR (128) DEFAULT '' COMMENT '门店代码',
    `goods_code` VARCHAR (64) DEFAULT '' COMMENT '商品代码',
    `sku` VARCHAR (30) DEFAULT '' COMMENT 'SKU',
    `goods_price` DECIMAL (20, 3) DEFAULT '0.000' COMMENT '商品级售价',
    `sku_price` DECIMAL (20, 3) DEFAULT '0.000' COMMENT '条码级售价',
    `status` TINYINT(1) DEFAULT '1' COMMENT '是否启用：0-停用 1-启用',
    `create_person` VARCHAR (24) NOT NULL DEFAULT '' COMMENT '创建人',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`shop_sku_id`),
    UNIQUE KEY `idxu_key` (`shop_code`,`goods_code`,`sku`),
    KEY `ix_shop_code` (`shop_code`),
    KEY `ix_goods_code` (`goods_code`),
    KEY `ix_sku` (`sku`),
    KEY `ix_lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店商品(SKU)关联表';
