DROP TABLE IF EXISTS `api_weipinhuijit_warehouse`;
CREATE TABLE `api_weipinhuijit_warehouse` (
    `warehouse_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `warehouse_no` INT (11) NOT NULL COMMENT '序号值',
    `warehouse_code` VARCHAR (128) DEFAULT '' COMMENT '仓库代码',
    `warehouse_name` VARCHAR (128) DEFAULT '' COMMENT '仓库名称',
    `status` TINYINT (1) DEFAULT '1' COMMENT '状态：0-停用，1-启用',
    `desc` VARCHAR (255) DEFAULT '' COMMENT '描述',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`warehouse_id`),
    UNIQUE KEY `idxu_key` (`warehouse_code`),
    KEY `ix_name` (`warehouse_name`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '唯品会JIT仓库';
