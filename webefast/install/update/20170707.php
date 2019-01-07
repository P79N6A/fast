<?php
$u['1412']=array(
    "update sys_action set action_name='导出明细' WHERE action_id='4020802';"
);

$u['1445'] = array(
    "ALTER TABLE stm_store_shift_record ADD COLUMN `in_num` int(11) DEFAULT '0' COMMENT '移入数量' AFTER out_money;",
    "ALTER TABLE stm_store_shift_record ADD COLUMN `in_money` decimal(20,3) DEFAULT '0.000' COMMENT '移入金额' AFTER in_num;",
    "UPDATE stm_store_shift_record AS r1,(SELECT record_code,sum(in_num) AS det_in_num,sum(in_money) AS det_in_money FROM stm_store_shift_record_detail GROUP BY record_code) AS r2 SET r1.in_num = r2.det_in_num , r1.in_money = r2.det_in_money WHERE r1.record_code = r2.record_code;"
);


$u['1406']=array(
    "UPDATE oms_waves_record  AS r1,(SELECT count(1) as deliver_sum,waves_record_id from oms_deliver_record WHERE is_cancel=0 AND is_deliver=1 GROUP BY waves_record_id ) as r2 SET r1.is_deliver=1 WHERE r1.waves_record_id=r2.waves_record_id AND r1.sell_record_count=r2.deliver_sum AND r1.is_deliver=2",
);

$u['1454']=array(
    "alter TABLE order_express_dz_detail ADD `receiver_province` bigint(11) NOT NULL DEFAULT '0' COMMENT '收货人省'"

);

$u['1467'] = array(
    "ALTER TABLE `mid_api_config`
MODIFY COLUMN `online_time`  datetime NULL DEFAULT NULL AFTER `notes`;"

);

$u['1424']=array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'refund_finish_cancel_return', 'oms_taobao', 'TB010 平台退单关闭作废售后服务单', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '', '开启后，平台申请退单，（系统订单已发货）系统自动生成售后服务单，若平台退单关闭，自动作废售后服务单。');",

);

$u['bug_1468']=array(
    "ALTER TABLE `api_weipinhuijit_delivery` MODIFY COLUMN `import_detail_status` int(11) NOT NULL DEFAULT '0' COMMENT '接口导入明细状态 0:初始状态';",
);