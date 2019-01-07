DROP TABLE IF EXISTS `cost_month`;
CREATE TABLE `cost_month` (
    `cost_month_id` INT (11) NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (64) DEFAULT '' COMMENT '单据编号',
    `ymonth` VARCHAR (7) NOT NULL DEFAULT '0000-00' COMMENT '月结月份',
    `store_code` VARCHAR (200) NOT NULL DEFAULT '' COMMENT '仓库代码，多个仓库以逗号隔开',
    `begin_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '期初成本总金额',
    `begin_total` INT (11) DEFAULT '0' COMMENT '期初库存总数',
    `end_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '期末成本总金额',
    `end_total` INT (11) DEFAULT '0' COMMENT '期末库存总数',
    `purchase_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '月采购总金额',
    `purchase_total` INT (11) DEFAULT '0' COMMENT '月采购总数',
    `is_sure` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '确认状态：0-未确认，1-已确认',
    `is_check` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '审核状态：0-未审核，1-已审核',
    `check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '审核时间',
    `record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '单据创建时间',
    `lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    `remark` VARCHAR (255) DEFAULT '' COMMENT '备注',
    PRIMARY KEY (`cost_month_id`),
    UNIQUE KEY `idxu_record_code` (`record_code`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '成本月结单主单据';