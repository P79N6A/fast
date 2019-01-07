<?php
$u['1250']=array(
"ALTER TABLE stm_stock_lock_record MODIFY COLUMN `lock_obj` tinyint(4) NOT NULL DEFAULT '0' COMMENT '锁定对象： 0:无，1:网络店铺，2:分销商';"
);

$u['1248']=array(
    "CREATE TABLE `api_weipinhuijit_order_detail` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`occupied_order_sn` varchar(255) DEFAULT '' COMMENT '库存占用单号',
`barcode` varchar(128) DEFAULT '' COMMENT '商品条码',
`amount` int(8) DEFAULT '0' COMMENT '商品数量',
`brand_id` varchar(64) DEFAULT '' COMMENT '品牌id',
`cooperation_no` varchar(255) DEFAULT '' COMMENT '供应商常态合作编码',
`warehouse` varchar(128) DEFAULT '' COMMENT '唯品会仓库编码',
`sales_source_indicator` int(4) DEFAULT NULL COMMENT '销售来源标示 1:旗舰店0:运营专场 ',
`sales_no` varchar(255) DEFAULT '' COMMENT '销售编号',
`status` int(4) DEFAULT '1' COMMENT '状态 0：删除 1：正常',
`lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
PRIMARY KEY (`id`)
UNIQUE KEY `sn_and_barcode` (`occupied_order_sn`,`barcode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='唯品会jit已成交销售订单商品表';",
    "alter table api_goods_sku add circuit_break_value int(8) DEFAULT NULL COMMENT '熔断值';",
    "INSERT INTO `sys_schedule` (`code`, `name`, `sale_channel_code`, `status`, `type`, `request`, `loop_time`, `task_type`, `task_module`) VALUES('weipinhuijit_getOccupiedOrders_cmd', '唯品会jit已成交销售订单查询','weipinhui','0', '1','{\"action\":\"api/order/weipinhuijit_getOccupiedOrders_cmd\"}', '900', '0', 'api');"
);

$u['1273'] = array(
	"ALTER TABLE oms_waves_record ADD sell_num_type tinyint(3) COMMENT '1：一单一品，2：一单多品';",
	"update oms_waves_record set sell_num_type = 2 where sell_record_count!=goods_count;",
	"update oms_waves_record set sell_num_type = 1 where sell_record_count=goods_count;",
	"ALTER TABLE oms_waves_record ADD pick_cart_no varchar(128) NOT NULL COMMENT'拣货车编号';",
);