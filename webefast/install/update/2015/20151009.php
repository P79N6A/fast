<?php

$u = array();
$u['FSF-1696'] = array(
	"INSERT INTO `sys_action` VALUES ('6030600', '6030000', 'url', '采购统计分析', 'pur/purchase_analyse/do_list', '10', '1', '0', '1','0');",
);
$u['FSF-1697'] = array(
	"INSERT INTO `sys_action` VALUES ('8020500', '8020000','url','批发统计分析','wbm/wbm_report/do_list','5','1','0','1','0');",
    "ALTER TABLE `oms_deliver_record`
MODIFY COLUMN `express_data`  text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '云栈获取数据' AFTER `express_no`;",
    //刷新批发数据
    "update wbm_return_record_detail set money=price*rebate*num",
    "update wbm_return_record,( select sum(num) as num ,sum(money) as money,record_code from  wbm_return_record_detail GROUP BY record_code  ) d
    set wbm_return_record.num = d.num, wbm_return_record.money = d.money
    where wbm_return_record.record_code = d.record_code AND wbm_return_record.is_sure=1 and wbm_return_record.is_store_in=1",
);


$u['TEST-18896'] = array(
	"ALTER TABLE `wms_goods_inv_log`
MODIFY COLUMN `after_num`  int(11) NOT NULL DEFAULT 0 COMMENT '更新后的库存数量' AFTER `prev_num`;",
);

$u['FSF-1715'] = array(
		"INSERT INTO `sys_action` VALUES ('5020204', '5020200', 'act', '导出', 'prm/goods/export_list', '4', '1', '0', '1','0');",
);
$u['FSF-1720'] = array(
		"update sys_schedule set loop_time=300 where code='inv_upload_cmd';",
		"update sys_schedule set loop_time=600 where code='cli_batch_remove_short';",

);
$u['FSF-1717'] = array(
		"ALTER TABLE api_weipinhuijit_delivery ADD COLUMN brand_code varchar(100) NOT NULL COMMENT '品牌';",
		"ALTER TABLE api_weipinhuijit_delivery_detail ADD COLUMN `delivery_id` varchar(50) DEFAULT NULL COMMENT '出库单Id';",
		"ALTER TABLE api_weipinhuijit_delivery_detail ADD KEY `delivery_id` (`delivery_id`);",
);




