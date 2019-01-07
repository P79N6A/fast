<?php

$u['1211'] = array(
    "ALTER TABLE `pur_return_record` ADD COLUMN `out_time` datetime DEFAULT NULL COMMENT '出库时间';",
		"UPDATE pur_return_record pr,pur_stm_log pl SET pr.out_time = pl.add_time WHERE pl.pid=pr.return_record_id
and pl.module='return_record' AND pl.finish_status='验收' AND pr.out_time is null AND pr.is_store_out='1';",
);


$u['1213'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'supply_price', 'api_weipinhui', '销货商品价格', 'radio', '[\"供货价（不含税，默认）\",\"供货价（含税）\"]', '0', '0.00', '1-供货价（含税） 0-供货价（不含税，默认）', '2016-12-15 10:31:05', '唯品会拣货单生成批发销货单，销货商品价格选择');",
    "ALTER TABLE api_weipinhuijit_delivery ADD COLUMN `import_detail_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '接口导入状态 0:初始状态 1:正在导入 '",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040310', '8040300', 'act', '修改', 'api/api_weipinhuijit_delivery/edit_info', '1', '1', '0', '1', '0');"
);


$u['1218']=array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('8040800', '8040000', 'url', '唯品会商品管理', 'api/api_weipinhuijit_goods/do_list', '1', '1', '0', '1', '0');",
    "DELETE FROM sys_action WHERE parent_id='8040000' and action_name='专场商品管理';",
    "UPDATE sys_action  SET sort_order = 6 WHERE action_id='8040400';",
    "UPDATE sys_action  SET sort_order = 5 WHERE action_id='8040800'",
);

$u['bug_1068']=array(
    "ALTER TABLE `oms_sell_record` ADD INDEX is_fenxiao ( `is_fenxiao`) USING BTREE;",
    "ALTER TABLE `oms_sell_record` ADD INDEX fenxiao_name ( `fenxiao_name`) USING BTREE;",
);

$u['1195'] = array(
    "ALTER TABLE oms_sell_settlement ADD COLUMN `fx_refund_money` decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '分销退款' AFTER `real_point_fee`;"
);

$u['1221'] = array(
	"insert into sys_action values('5020104','5020100','act','生成条码','prm/goods_barcode_rule/create_barcode',0,1,0,1,0);",
);

$u['1220']=array(
		"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030104', '6030100', 'act', '验收后修改金额', 'pur/purchase_record/do_edit_detail_money', '1', '1', '0', '1', '0');",
		"UPDATE sys_action  SET action_name = '验收后修改进货价' WHERE action_id='6030103';",
);

$u['1230'] = array(
		"ALTER TABLE crm_goods ADD goods_code varchar(64) DEFAULT '' COMMENT '商品代码';",
		"ALTER TABLE crm_goods ADD goods_from_id varchar(64) DEFAULT '' COMMENT '商品ID, 淘宝平台：num_iid';",
		"update crm_goods r1 inner join goods_sku r2 on r1.sku=r2.sku set r1.goods_code=r2.goods_code;",
		"update crm_goods r1 inner join goods_combo_barcode r2 on r1.sku=r2.sku set r1.goods_code=r2.goods_code;",
		"update crm_goods r1 inner join api_goods r2 on r1.goods_code=r2.goods_code set r1.goods_from_id=r2.goods_from_id where r1.shop_code=r2.shop_code;"
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
PRIMARY KEY (`id`),
UNIQUE KEY `sn_and_barcode` (`occupied_order_sn`,`barcode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='唯品会jit已成交销售订单商品表';",
    "alter table api_goods_sku add circuit_break_value int(8) DEFAULT NULL COMMENT '熔断值';",
    "INSERT INTO `sys_schedule` (`code`, `name`, `sale_channel_code`, `status`, `type`, `request`, `loop_time`, `task_type`, `task_module`) VALUES('weipinhuijit_getOccupiedOrders_cmd', '获取唯品会订单占用库存','weipinhui','0', '1','{\"action\":\"api/order/weipinhuijit_getOccupiedOrders_cmd\"}', '60', '0', 'api');"
);