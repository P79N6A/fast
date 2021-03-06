DROP TABLE IF EXISTS `api_weipinhuijit_delivery`;
CREATE TABLE `api_weipinhuijit_delivery` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `shop_code` varchar(100) DEFAULT NULL COMMENT '店铺code',
    `po_no` varchar(50) DEFAULT NULL COMMENT 'po号',
    `delivery_no` varchar(50) DEFAULT NULL COMMENT '送货单编号',
    `warehouse` varchar(50) DEFAULT NULL COMMENT '送货仓库',
    `arrival_time` varchar(50) DEFAULT NULL COMMENT '预计到货时间',
    `delivery_method` TINYINT(1) DEFAULT NULL COMMENT '配送模式:1-汽运;2-空运',
    `express_code` varchar(50) DEFAULT NULL COMMENT '配送方式code',
    `carrier_name` varchar(50) DEFAULT NULL COMMENT '承运商名称',
    `driver_tel` varchar(50) DEFAULT NULL COMMENT '司机联系电话',
    `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id',
    `storage_no` varchar(50) DEFAULT NULL COMMENT '入库编号',
    `amount` int(10) DEFAULT NULL COMMENT '商品数量',
    `insert_time` varchar(50) DEFAULT NULL COMMENT '插入时间',
    `is_delivery` int(1) NOT NULL DEFAULT '0' COMMENT '是否确认出库(0:未出库；1:已出库)',
    `delivery_time` datetime DEFAULT NULL COMMENT '确认出库时间',
    `brand_code` varchar(100) NOT NULL COMMENT '品牌',
    `express` varchar(20) NOT NULL DEFAULT '' COMMENT '快递单号',
    PRIMARY KEY (`id`),
    UNIQUE KEY `delivery_id` (`delivery_id`),
    KEY `delivery_time` (`delivery_time`),
    KEY `insert_time` (`insert_time`),
    KEY `is_delivery` (`is_delivery`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;