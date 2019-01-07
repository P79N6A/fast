DROP TABLE IF EXISTS `op_inv_sync_ss_relation`;
CREATE TABLE `op_inv_sync_ss_relation` (
    `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
    `code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '编码',
    `type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '编码类型:1-店铺编码;2-仓库编码',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idxu_key` (`sync_code`,`code`,`type`),
    KEY `ix_sync_code` (`sync_code`),
    KEY `ix_code` (`code`),
    KEY `ix_type` (`type`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '库存策略主表店铺仓库关联表';
