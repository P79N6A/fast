<?php

$u = array();

$u['685'] = array(
    " ALTER TABLE `crm_client` MODIFY COLUMN `client_sex` tinyint(1) DEFAULT '0' COMMENT '性别:0-保密 1-男 2-女';"
);

$u['690'] = array(
    "insert into sys_action values('6030301','6030300','act','确认','pur/planned_record/do_check',1,1,0,1,0),
	('6030302','6030300','act','取消确认','pur/planned_record/do_re_check',2,1,0,1,0),
	('6030303','6030300','act','删除','pur/planned_record/do_delete',3,1,0,1,0),
	('6030304','6030300','act','生成通知单','pur/planned_record/do_execute',4,1,0,1,0),
	('6030305','6030300','act','完成','pur/planned_record/do_finish',5,1,0,1,0);",
	"insert into sys_action values('6030401','6030400','act','确认','pur/order_record/do_check',1,1,0,1,0),
	('6030402','6030400','act','取消确认','pur/order_record/do_re_check',2,1,0,1,0),
	('6030403','6030400','act','删除','pur/order_record/do_delete',3,1,0,1,0),
	('6030404','6030400','act','生成入库单','pur/order_record/do_execute',4,1,0,1,0),
	('6030405','6030400','act','完成','pur/order_record/do_finish',5,1,0,1,0);",
	"insert into sys_action values('6030201','6030200','act','验收','pur/return_record/do_checkin',1,1,0,1,0),
	('6030202','6030200','act','删除','pur/return_record/do_delete',2,1,0,1,0);"
);

$u['693'] = array(
    "update sys_action set action_name='待退款售后服务单' where action_id='9010100';",

	"delete from sys_user_pref where iid='sell_return_finance/do_list';"
);

$u['680'] = array(
    "DROP TABLE IF EXISTS `sys_log_clean_up_log`;",
    "CREATE TABLE `sys_log_clean_up_log` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
        `type` varchar(20) NOT NULL COMMENT '日志类型（ORDER_LOG：订单操作日志，STANDARD_LOG：标准操作日志，SYS_LOG：系统操作日志，LOGIN_LOG：登录操作日志）',
        `status` tinyint(1) NOT NULL COMMENT '操作是否成功，1：成功，0：失败',
        `remark` text NOT NULL COMMENT '备注',
        `lastchanged` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP COMMENT '操作时间',
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;"
);

$u['bug_555'] = array(
	"delete from sys_action where type = 'act' and parent_id in ('6030300','6030400','6030200');",
	"insert into sys_action values('6030301','6030300','act','确认/取消确认','pur/planned_record/do_check',0,1,0,1,0),
	('6030303','6030300','act','删除','pur/planned_record/do_delete',0,1,0,1,0),
	('6030304','6030300','act','生成通知单','pur/planned_record/do_execute',0,1,0,1,0),
	('6030305','6030300','act','完成','pur/planned_record/do_finish',0,1,0,1,0);",

	"insert into sys_action values('6030401','6030400','act','确认/取消确认','pur/order_record/do_check',1,1,0,1,0),
	('6030403','6030400','act','删除','pur/order_record/do_delete',1,1,0,1,0),
	('6030404','6030400','act','生成入库单','pur/order_record/do_execute',1,1,0,1,0),
	('6030405','6030400','act','完成','pur/order_record/do_finish',1,1,0,1,0);",

	"insert into sys_action values('6030201','6030200','act','验收','pur/return_record/do_checkin',2,1,0,1,0),
	('6030202','6030200','act','删除','pur/return_record/do_delete',2,1,0,1,0);"
);

$u['bug_570'] = array("UPDATE sys_schedule SET loop_time = 300 WHERE code = 'opt_record_by_seller_remark';");

$u['bug_537'] = array("ALTER TABLE api_taobao_trade ADD INDEX status ( `status` );");