
DROP TABLE IF EXISTS `oms_amount_trade`;
CREATE TABLE `oms_amount_trade` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `deal_code` varchar(40) NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)',
  `sale_channel_code` varchar(20) NOT NULL,
  `alipay_no` varchar(30) NOT NULL DEFAULT '' COMMENT '支付宝交易号',
  `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
  `pay_type` varchar(20) NOT NULL DEFAULT 'secured' COMMENT 'secured 担保交易 cod货到付款 nosecured 非担保交易',
  `express_money` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '运费',
  `delivery_money` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '配送手续费',
  `point_fee` decimal(10,2) NOT NULL COMMENT '付款-积分',
  `alipay_point_fee` decimal(10,2) NOT NULL COMMENT '付款-集分宝',
  `coupon_fee` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '付款-抵用金额',
  `yfx_fee` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT  '运费险',
  `compensate_money` decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '赔付金额',
  `real_income` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '实际收入',
  `real_expend` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '实际支出',
  `send_num` int(11) NOT NULL COMMENT '实际发货数',
  `return_num` int(11) NOT NULL COMMENT '实际退货数',
  `send_avg_monty` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '发货商品均摊总金额',
  `return_avg_monty` decimal(10,3) NOT NULL DEFAULT '0.000'  COMMENT '退货商品均摊总金额',
  `dz_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0;未记账 1;记账/手工记账 2;部分到账',
  `dz_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '对账时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idxu` (`deal_code`) USING BTREE,
  KEY `lastchanged` (`lastchanged`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='零售结算单';