DROP TABLE IF EXISTS `op_inv_sync_warn_goods`;
CREATE TABLE `op_inv_sync_warn_goods` (
    `warn_goods_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `goods_code` VARCHAR (64) DEFAULT '' COMMENT '商品代码',
    `sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '来源策略代码',
    `sku` VARCHAR (128) DEFAULT '' COMMENT 'sku',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`warn_goods_id`),
    KEY `ix_sync_code` (`sync_code`),
    KEY `ix_goods_code` (`goods_code`),
    KEY `ix_sku` (`sku`),
    KEY `ix_lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预警商品表';
