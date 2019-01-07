<?php

$u = array();
$u['595'] = array(
    "CREATE TABLE IF NOT EXISTS `op_express_by_user` (
        `op_express_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
        `customer_name` varchar(30) DEFAULT NULL COMMENT '会员昵称',
        `mobile` char(11) DEFAULT NULL COMMENT '手机号',
        `express_code` varchar(128) DEFAULT NULL COMMENT '快递编码',
        `lastchanged` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP COMMENT '导入时间',
        PRIMARY KEY (`op_express_id`),
        UNIQUE KEY `customer_name` (`customer_name`) USING BTREE
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
);

$u['597'] = array(
    "INSERT INTO sys_action (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('7030200', '7030000', 'url', '发货超时订单', 'oms/sell_record/deliver_overtime_list', '0', '1', '0', '1', '0');",
    "UPDATE oms_sell_record sr,
(
	SELECT
    unix_timestamp(pay_time) AS pay_time,
		sell_record_code
	FROM
		oms_sell_record
   ) AS std
   SET sr.plan_send_time = from_unixtime(std.pay_time+86400)
   WHERE sr.sell_record_code = std.sell_record_code AND (sr.plan_send_time='0000-00-00 00:00:00' OR sr.plan_send_time='1970-01-02 08:00:00')",
    "UPDATE `sys_action` SET action_name='超时订单查询' WHERE action_id='7030000'"
);


$u['611'] = array(
    "ALTER TABLE `op_strategy_log`
ADD INDEX `_index` (`type`, `strategy_code`, `sell_record_code`, `customer_code`, `is_success`) USING BTREE ;",
);
$u['594'] = array(
    "ALTER TABLE op_gift_strategy_goods ADD diy tinyint;"
);

$u['589'] = array("INSERT INTO sys_params (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `remark`, `memo`) VALUES('minus_no_upload_stock', 'erp', '仅以ERP库存更新系统', 'radio', '[\"关闭\",\"开启\"]', '0', '1-开启 0-关闭', '默认关闭，库存更新系统会扣减3天内系统已发货未上传ERP的单据商品数量，开启参数，仅以ERP库存同步系统，不做任何处理');");

$u['590'] = array(
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010101','8010100','act','分销商注册','base/custom/register','1','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010102','8010100','act','添加分销商','base/custom/detail&app_scene=add','1','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010103','8010100','act','删除','base/custom/do_delete','3','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010104','8010100','act','编辑','base/custom/detail&app_scene=edit','4','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010105','8010100','act','登录设置','base/custom/add_user&app_scene=add','5','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010106','8010100','act','重设密码','base/custom/reset_pwd','6','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010401','8010400','act','添加分销商等级','base/custom_grades/detail&app_scene=add','1','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010402','8010400','act','编辑','base/custom_grades/detail&app_scene=edit','2','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010403','8010400','act','删除','base/custom_grades/do_delete','3','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010404','8010400','act','查看','base/custom_grades/detail&app_scene=show_custom','4','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8010601','8010600','act','通过/拒绝','base/custom/update_user_status','1','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8080201','8080200','act','新增产品线','fx/goods_manage/detail&app_scene=add','1','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8080202','8080200','act','查看','fx/goods_manage/detail&app_scene=show_view','2','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8080203','8080200','act','编辑','fx/goods_manage/detail&app_scene=edit','3','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8080204','8080200','act','删除','fx/goods_manage/do_delete','4','1','0','1','0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,STATUS,ui_entrance) VALUES ('8070301','8070300','act','批量结算','oms/sell_record/opt_fx_settlement','1','1','0','1','0');",
);

$u['500'] = array(
    "CREATE TABLE `api_deal_code` (
`id`  int(11) NOT NULL AUTO_INCREMENT ,
`deal_code`  varchar(128) NULL ,
`num`  int(11) NULL DEFAULT 0 ,
PRIMARY KEY (`id`),
UNIQUE INDEX `_key` (`deal_code`) USING BTREE ,
INDEX `_index` (`num`) USING BTREE 
);
",
);

$u['591'] = array(
    "INSERT INTO `goods_barcode_rule` (`barcode_rule_id`, `rule_code`, `rule_name`, `barcode_prefix`, `barcode_suffix`, `serial_num`, `serial_num_length`, `project1`, `split1`, `project2`, `split2`, `project3`, `split3`, `sys`, `status`, `is_main`, `remark`, `lastchanged`, `trd_id`, `trd_type`, `trd_time`) VALUES ('2', '001', '商品编码（仅支持单规格商品）', '', '', '', '0', '1', '', '0', '', '0', '', '0', '1', '0', '商品编码（仅支持单规格商品）', '2016-08-31 11:22:03', '', '', '') ON DUPLICATE KEY UPDATE rule_name = VALUES(rule_name), remark = VALUES(remark);
"
);

$u['629'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'oms/sell_record_shipped_list';",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,`status`, `ui_entrance`) VALUES ('4021000', '4020000', 'url', '签收超时订单', 'oms/sell_record/record_overtime_list', '10', '1', '0', '1', '0');",
    "INSERT INTO sys_action (action_id,parent_id,type,action_name,action_code,sort_order,appid,other_priv_type,`status`, `ui_entrance`) VALUES ('4020900', '4020000', 'url', '发货超时订单', 'oms/sell_record/record_deliver_overtime_list', '9', '1', '0', '1', '0');",
);

$u['509'] = array(
    "DELETE FROM sys_user_pref WHERE iid='sell_record_fh_list/table'",
);

$u['508'] = array(
    "DELETE FROM sys_user_pref WHERE iid='oms/sell_record_question_list'",
);

$u['630'] = array(
    "DELETE FROM `sys_user_pref` WHERE iid='oms/sell_record_pending_list'",
);

$u['628'] = array("UPDATE sys_schedule_record SET all_loop_time=43200 WHERE type_code='update_inv';");

$u['bug_516'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060102', '9060100', 'act', '新增', 'fx/account/add', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060103', '9060100', 'act', '删除', 'fx/account/delete', '1', '1', '0', '1', '0');"
);

$u['bug_518'] = array(
    "DELETE FROM `sys_user_pref` WHERE iid='oms/sell_record_pending_list'",
);

$u['613'] = array(
    " ALTER TABLE oms_sell_record ADD INDEX IDX_IS_PENDING (IS_PENDING) ",
    " ALTER TABLE oms_deliver_record ADD INDEX IDX_DEAL_CODE_LIST (DEAL_CODE_LIST) ",
    " 	ALTER TABLE `api_order`
ADD INDEX `index4` (`status`, `is_change`, `order_first_insert_time_int`) ",
    "ALTER TABLE `oms_sell_record_notice`
MODIFY COLUMN `deal_code`  varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '平台交易号(交易号)' AFTER `sell_record_code`;

",
);

$u['bug_531'] = array(
    "ALTER TABLE `crm_customer`
MODIFY COLUMN `customer_code`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '顾客代码' AFTER `customer_id`,
MODIFY COLUMN `customer_name`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '' COMMENT '顾客名称' AFTER `customer_code`;
",
    "ALTER TABLE `crm_customer_address`
MODIFY COLUMN `customer_code`  varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '顾客代码' AFTER `customer_address_id`;

",
);

$u['bug_526']=array(
    "UPDATE base_express_company SET rule='^[A-Za-z0-9]{2}[0-9]{10}$|^[A-Za-z0-9]{2}[0-9]{8}$|^(8)[0-9]{17}$' WHERE company_code='YTO';"
);

$u['827_1'] = array(
	"ALTER TABLE crm_goods ADD `lock_num` int(10) NOT NULL DEFAULT '0' COMMENT '活动锁定库存';",
);