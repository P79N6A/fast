<?php

$u['2095'] = array(
    "CREATE TABLE `api_akucun_order` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `adorderid` varchar(120) DEFAULT '' COMMENT '平台交易号',
  `code` varchar(30) DEFAULT '' COMMENT '平台标识',
  `liveid` varchar(120) DEFAULT '' COMMENT '活动号',
  `livename` varchar(120) DEFAULT '' COMMENT '活动名称',
  `logisticsstatus` varchar(10) DEFAULT '' COMMENT '物流状态 0代表未发货 1代表已发货',
  `orderstatus` varchar(10) DEFAULT '' COMMENT '订单状态 0代表无效 1代表有效 2已完成',
  `ordertime` datetime DEFAULT NULL COMMENT '下单时间',
  `paytime` datetime DEFAULT NULL COMMENT '支付时间',
  `sum` int(100) DEFAULT '0' COMMENT '订单总数',
  `total` decimal(7,2) DEFAULT NULL COMMENT '订单总金额',
  `deliverNo` varchar(120) DEFAULT '' COMMENT '运单号',
  `logisticsCompany` varchar(50) DEFAULT '' COMMENT '快递公司',
  `insuranceValue` varchar(50) DEFAULT '' COMMENT '保价',
  `codValue` varchar(50) DEFAULT '' COMMENT '代收',
  `receiverArea` varchar(50) DEFAULT '' COMMENT '接收地',
  `province` varchar(100) DEFAULT '' COMMENT '省',
  `city` varchar(120) DEFAULT '' COMMENT '市',
  `county` varchar(120) DEFAULT '' COMMENT '区',
  `receiver` varchar(10) DEFAULT '' COMMENT '接收人',
  `receiverTel` varchar(120) DEFAULT '' COMMENT '电话',
  `receiverAddress` varchar(120) DEFAULT '' COMMENT '平台固定电话',
  `sender` varchar(120) DEFAULT '' COMMENT '发送人',
  `sendTel` varchar(20) DEFAULT '' COMMENT '发货电话',
  `sendAddress` varchar(120) DEFAULT '' COMMENT '发货地址',
  `remark` varchar(120) DEFAULT '' COMMENT '备注',
  `backSignBill` varchar(50) DEFAULT '' COMMENT '签单返回',
  PRIMARY KEY (`id`),
  UNIQUE KEY `adorderid` (`adorderid`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='爱库存订单中间表'",

    "CREATE TABLE `api_akucun_order_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adorderid` varchar(120) DEFAULT '' COMMENT '平台交易号',
  `kuanhao` varchar(128) DEFAULT NULL COMMENT '款号',
  `pinpai` varchar(128) DEFAULT NULL COMMENT '品牌',
  `barcode` varchar(128) DEFAULT NULL COMMENT '条码',
  `num` int(11) DEFAULT '0' COMMENT '数量',
  `settlementprice` decimal(20,2) DEFAULT NULL COMMENT '售价',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`adorderid`,`barcode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=418 DEFAULT CHARSET=utf8 COMMENT='爱库存订单明细中间表'",

    "insert into `base_sale_channel` ( `remark`, `sale_channel_code`, `is_system`, `lastchanged`, `short_code`, `sale_channel_name`, `is_active`) values ( '', 'akucun', '1', '2017-06-16 10:08:36', 'akc', '爱库存', '1');"
);

$u['2066'] = array(
    "ALTER TABLE api_order ADD seller_remark_change_time INT(11) NULL DEFAULT 0 COMMENT '商家备注变更时间';",
    "ALTER TABLE api_order ADD INDEX `seller_remark_change_time` (`seller_remark_change_time`) USING BTREE;"
);
