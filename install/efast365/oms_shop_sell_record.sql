DROP TABLE IF EXISTS `oms_shop_sell_record`;
CREATE TABLE `oms_shop_sell_record` (
    `record_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单编号',
    `record_out_code` VARCHAR (50) DEFAULT '' COMMENT '订单外部编号',
    `record_type` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单类型:0-门店（默认）;1-电商',
    `record_source` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '订单来源：门店/各电商平台',
    `send_store_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '发货仓库代码',
    `send_way` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '发货方式：0-快递配送;1-买家自提（默认）',
    `express_code` VARCHAR (20) DEFAULT '' COMMENT '快递代码',
    `express_no` VARCHAR (20) DEFAULT '' COMMENT '快递单号',
    `online_shop_code` VARCHAR (20) DEFAULT '' COMMENT '网店代码',
    `offline_shop_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '门店代码',
    `cashier_code` VARCHAR (20) DEFAULT '' COMMENT '收银员代码',
    `guide_code` VARCHAR (20) DEFAULT '' COMMENT '导购员代码',
    `customer_code` VARCHAR (20) DEFAULT '' COMMENT '顾客代码',
    `buyer_name` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '购买人/会员昵称',
    `create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
    `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
    `pay_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单付款状态：0-未付款（默认）;1-部分付款（正在支付）;2-已付款',
    `pay_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '付款时间',
    `send_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单发货(或买家自提)状态：0-未发货（默认）;1-已发货',
    `send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发货(或买家自提)时间',
    `cancel_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单作废状态：0-未作废（默认）;1-已作废',
    `cancel_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单作废时间',
    `cancel_cause` VARCHAR (255) DEFAULT '' COMMENT '订单作废原因',
    `check_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '订单审核状态：0-未审核（默认） ;1-已审核（主要用于O2O订单，用于接单场景）',
    `check_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '订单审核时间',
    `receiver_name` VARCHAR (30) DEFAULT '' COMMENT '收货人姓名',
    `receiver_phone` VARCHAR (20) DEFAULT '' COMMENT '收货人手机号',
    `receiver_address` VARCHAR (100) NOT NULL DEFAULT '' COMMENT '收货人地址',
    `country` BIGINT (20) DEFAULT NULL COMMENT '国家',
    `province` BIGINT (20) DEFAULT NULL COMMENT '省',
    `city` BIGINT (20) DEFAULT NULL COMMENT '市',
    `district` BIGINT (20) DEFAULT NULL COMMENT '区/县',
    `street` BIGINT (20) DEFAULT NULL COMMENT '街道',
    `address` VARCHAR (100) NOT NULL DEFAULT '' COMMENT '详细地址(不包含省市区)',
    `lock_inv_status` TINYINT(4) NOT NULL DEFAULT '0' COMMENT '库存状态：0-未占用 1-实物锁定 2-实物部分锁定',
    `goods_num` SMALLINT (11) NOT NULL DEFAULT '0' COMMENT '商品数量',
    `sku_num` TINYINT (11) NOT NULL DEFAULT '0' COMMENT 'sku数量',
    `record_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单总金额',
    `express_money` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单运费',
    `buyer_real_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '买家实付总金额',
    `discount_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商家优惠总金额',
    `hand_adjust_money` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '商家手工调整金额',
    `payable_amount` DECIMAL (10, 2) NOT NULL DEFAULT '0.00' COMMENT '订单应收总金额',
    `plan_send_time` datetime DEFAULT '0000-00-00 00:00:00' COMMENT '订单计划发货时间',
    `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
    `lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    `remark` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '订单备注',
    PRIMARY KEY (`record_id`),
    UNIQUE KEY `idxu_code` (`record_code`) USING BTREE,
    KEY `offline_shop_code` (`offline_shop_code`),
    KEY `send_way` (`send_way`),
    KEY `pay_status` (`pay_status`),
    KEY `send_status` (`send_status`),
    KEY `cancel_status` (`cancel_status`),
    KEY `check_status` (`check_status`),
    KEY `lastchanged` (`lastchanged`),
    KEY `create_time` (`create_time`) USING BTREE,
    KEY `pay_time` (`pay_time`) USING BTREE,
    KEY `send_time` (`send_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店订单主单据';