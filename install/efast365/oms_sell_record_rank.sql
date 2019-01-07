DROP TABLE IF EXISTS `oms_sell_record_rank`;
CREATE TABLE `oms_sell_record_rank` (
    `sell_record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
    `deal_code_list` varchar(200) NOT NULL DEFAULT '' COMMENT '交易号',  
    `sale_channel_code` varchar(20) NOT NULL,
    `buyer_name` varchar(30) DEFAULT NULL,
    `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
    `shop_code` varchar(20) NOT NULL DEFAULT '' COMMENT '商店代码',
    `order_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '订单总额,商品总额+运费+配送手续费',
    `record_time` datetime NOT NULL COMMENT '下单时间',
    `op_gift_strategy_detail_id` int(11) DEFAULT NULL COMMENT '规则id',
    `pay_time` datetime NOT NULL COMMENT '支付时间',
    `ranking_hour` datetime NOT NULL COMMENT '指定时间点',
    `is_has_given` int(11) NOT NULL DEFAULT '0',
    `rank_start` int(11) NOT NULL DEFAULT '0',
    `rank_end` int(11) NOT NULL DEFAULT '0',
    `customer_code` varchar(128) DEFAULT '' COMMENT '用户ID',
    `strategy_code` varchar(128) DEFAULT '' COMMENT '策略code',
    PRIMARY KEY (`sell_record_id`),
    UNIQUE KEY `idxu_record_code` (`sell_record_code`,`shop_code`,`op_gift_strategy_detail_id`,`ranking_hour`,`rank_start`,`rank_end`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='排名送订单列表';
