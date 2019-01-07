DROP TABLE IF EXISTS `op_inv_sync`;
CREATE TABLE `op_inv_sync` (
    `sync_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
    `sync_name` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略名称',
    `sync_mode` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '策略模式:1-全局;2-仓库',
    `warn_goods_val` INT NOT NULL DEFAULT '0' COMMENT '防超卖商品警戒值',
    `warn_goods_sell_shop` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '防超卖商品销售店铺',
    `warn_goods_deliver_day` INT NOT NULL DEFAULT '0' COMMENT '发货天数范围',
    `is_smart_select` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '智能选择店铺:0-关闭;1-开启',
    `is_road` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '启用在途库存:0-停用;1-启用',
    `is_safe` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '启用安全库存:0-停用;1-启用',
    `status` TINYINT (1) DEFAULT '0' COMMENT '状态:0-停用;1-启用',
    `create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`sync_id`),
    UNIQUE KEY `idxu_code` (`sync_code`),
    KEY `ix_sync_name` (`sync_name`),
    KEY `ix_status` (`status`),
    KEY `ix_lastchanged` (`lastchanged`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '库存同步策略主表';
