DROP TABLE IF EXISTS `op_inv_sync_shop_ratio`;
CREATE TABLE `op_inv_sync_shop_ratio` (
    `ratio_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
    `shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
    `store_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '仓库代码',
    `sync_ratio` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '同步比例',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`ratio_id`),
    UNIQUE KEY `idxu_key` (`sync_code`,`shop_code`,`store_code`),
    KEY `ix_sync_code` (`sync_code`),
    KEY `ix_shop_code` (`shop_code`),
    KEY `ix_store_code` (`store_code`),
    KEY `ix_sync_ratio` (`sync_ratio`),
    KEY `ix_lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '店铺比例配置表';
