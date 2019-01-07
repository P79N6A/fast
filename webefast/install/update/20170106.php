<?php

$u = array();

$u['970'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('', 'fx_automatic_settlement', 'finance', 'S005_003 代销订单转单自动结算', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '', '默认不开启，开启后，代销订单转单自动分销结算，扣减资金账户余额');",    "ALTER TABLE `fx_income_pay` MODIFY COLUMN `detail_type` tinyint(1) DEFAULT NULL COMMENT '明细类型:0-资金账户生成;1-业务单据生成;2-分销结算自动生成';",
);

$u['bug_838'] = array(
    " DROP TABLE IF EXISTS `api_open_key`; ",
);
$u['bug_876'] = array(
    "ALTER TABLE `sap_sell_record_detail` ADD COLUMN `order_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '单据状态.0-未上传；1-已上传';",
    "ALTER TABLE `sap_sell_record_detail` ADD COLUMN `upload_date` datetime DEFAULT NULL COMMENT '上传时间';",
    "ALTER TABLE sap_sell_record_detail ADD COLUMN `is_gift` tinyint(4) NOT NULL DEFAULT '0' COMMENT '礼品标识：0-普通商品1-礼品';",
    "ALTER TABLE sap_sell_record_detail DROP INDEX idxu_key1;",
    "ALTER TABLE sap_sell_record_detail ADD UNIQUE KEY `idxu_key1` (`record_code_type`,`deal_code`,`sku`,`order_type`,`is_gift`);",
    "ALTER TABLE sap_sell_record ADD COLUMN `record_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '发(收)货时间';",
    "UPDATE `sys_schedule` SET `name`='sap中间表数据更新', `request`='{\"app_act\":\"sys/sap_sell_record/insert_automatism\",\"app_fmt\":\"json\"}' WHERE (`code`='sap_record_data');",
);

$u['bug_877'] = array(
	"CREATE TEMPORARY TABLE code_table SELECT `shelf_code`, `store_code` FROM (select * from `base_shelf`) a GROUP BY `shelf_code`, `store_code` HAVING COUNT(1) > 1;",
	"CREATE TEMPORARY TABLE min_table SELECT MIN(`shelf_id`) as id FROM (select * from `base_shelf`) b GROUP BY `shelf_code`, `store_code` HAVING COUNT(1) > 1;",
	"DELETE FROM `base_shelf` WHERE (`shelf_code`, `store_code`) IN (select `shelf_code`, `store_code` from `code_table`) and `shelf_id` NOT IN (select `id` from `min_table`);",
	"ALTER TABLE `base_shelf` ADD UNIQUE KEY store_shelf ( `store_code`,`shelf_code` ) USING BTREE",
);

$u['964'] = array(
	"ALTER TABLE wbm_notice_record ADD `address` varchar(100) NOT NULL DEFAULT '' COMMENT '地址(包含省市区)';",
	"ALTER TABLE wbm_notice_record ADD `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '电话';",
	"ALTER TABLE wbm_notice_record ADD `name` varchar(20) NOT NULL DEFAULT '' COMMENT '联系人';",
	"ALTER TABLE wbm_notice_record ADD `country` bigint(20) NOT NULL DEFAULT '0';",
	"ALTER TABLE wbm_notice_record ADD `province` bigint(20) NOT NULL DEFAULT '0';",
	"ALTER TABLE wbm_notice_record ADD `city` bigint(20) NOT NULL DEFAULT '0';",
	"ALTER TABLE wbm_notice_record ADD `district` bigint(20) NOT NULL DEFAULT '0';",
	"ALTER TABLE wbm_notice_record ADD `street` bigint(20) NOT NULL DEFAULT '0';",

	"update wbm_notice_record r1 inner join base_custom r2 on r1.distributor_code = r2.custom_code set r1.address=r2.address,r1.tel=r2.tel,r1.`name`=r2.contact_person;",

	"update wbm_notice_record r1 inner join base_custom r2 on r1.distributor_code = r2.custom_code set r1.tel=r2.mobile;",
	"update wbm_store_out_record r1 inner join base_custom r2 on r1.distributor_code = r2.custom_code set r1.tel=r2.mobile;"
);
$u['bug_862'] = array(
    "UPDATE `sys_action` SET `status`='0' WHERE (`action_id`='9060300');",
    "UPDATE `sys_action` SET `status`='0' WHERE (`action_id`='9060200');",
);
$u['981'] = array(
	"insert into sys_action values('4020505','4020500','act','批量作废','oms/sell_record/cancel_all_one',1,1,0,1,0);",
);
$u['983'] = array(
	"update sys_params set value='1' where param_code='send_check_refund' and parent_code='oms_taobao';",
);
$u['949'] = array(
    "ALTER TABLE base_custom ADD COLUMN `arrears_money` decimal(10,2) DEFAULT '0.00' COMMENT '分销欠款';",
    "ALTER TABLE base_store CHANGE COLUMN `is_enable_cusom` `is_enable_custom` tinyint(3) DEFAULT '0' COMMENT '是否允许分销商查看,0:未启用;1:启用';",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8080000);",
    "INSERT INTO sys_role_action (role_id,action_id) VALUES (100,8080300);",
);
$u['976'] = array(
	"alter table base_shop modify column stock_source_store_code text NOT NULL COMMENT '库存来源仓库,多个仓以逗号分开';",
);
