DROP TABLE IF EXISTS `op_express_by_goods`;
CREATE TABLE `op_express_by_goods` (
    `op_express_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `sku` varchar(30) NOT NULL COMMENT 'sku',
    `express_code` varchar(128) NOT NULL COMMENT '快递代码',
    `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`op_express_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='快递策略-指定商品匹配指定快递';