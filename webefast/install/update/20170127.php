<?php

$u = array();

$u['1013'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060104', '9060100', 'act', '欠款设置', 'fx/account/do_arrears_money', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_role_action` (`role_id`, `action_id`) VALUES ('100', '8020301');",
    "ALTER TABLE fx_pay_temporary ADD COLUMN `operator` varchar(255) NOT NULL DEFAULT '' COMMENT '操作人';",
    "ALTER TABLE wbm_notice_record ADD COLUMN `is_settlement` tinyint(3) DEFAULT '0' COMMENT '分销结算，1:已结算，0:未结算';",
);

$u['bug_632']=array(
    "ALTER TABLE `base_store` ADD UNIQUE KEY store_code_index ( `store_code`) USING BTREE",
);

$u['bug_884'] = array(
	"alter table goods_shelf modify column sku varchar(128) DEFAULT '' COMMENT '系统sku码';",

	"alter table goods_shelf modify column shelf_code varchar(128) DEFAULT '' COMMENT '库位代码';",
);

$u['bug_865'] = array(
	"delete from sys_params where param_code='oms_notice' and parent_code='oms_property';",
);

$u['bug_867'] = array(
	"delete from sys_action where action_code='oms/sell_record/download';",
);

$u['bug_980'] = array(
	"update sys_action set action_code='oms/shipped_list/edit_express' where action_id='4020801';",
	"update sys_action set action_code='oms/shipped_list/edit_express_new' where action_id='7010121';"
);

$u['bug_926'] = array(
	"alter table goods_sku modify column sku varchar(128) DEFAULT '' COMMENT 'sku';",
);

$u['1020'] = array(
	"insert into base_record_type(record_type_code,record_type_name,record_type_property,sys,remark,lastchanged) values('inferior_return','次品退	货',3,0,'','2017-02-04 09:47:44');",
	"update wbm_return_record rl inner join wbm_return_notice_record r2 on rl.relation_code=r2.return_notice_code set rl.record_type_code='inferior_return' where r2.return_type_code='inferior_return';"
);

$u['bug_908'] = array(
    "UPDATE sys_params SET param_name = 'S001_105 退货商品入库数，不允许超过订单退货商品数', memo = '生成退单时，实际退货数量，必须 <= 订单商品数量 - 已退货数量 时，才允许生成退单
退货商品扫描入库时，商品实际退货数必须<=  售后服务单申请退货数' WHERE param_code = 'is_allowed_exceed';",
);