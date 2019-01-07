<?php

$u = array();

$u['853'] = array(
    //收支明细表
    "CREATE TABLE `fx_income_pay` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `serial_number` varchar(128) NOT NULL COMMENT '流水号',
        `record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '单据编号(业务流水)',
        `detail_type` tinyint(1) NOT NULL COMMENT '明细类型:0-资金流水;1-业务流水',
        `capital_type` tinyint(1) DEFAULT NULL COMMENT '明细类型:0-扣款;1-充值(资金流水)',
        `capital_account` varchar(32) NOT NULL DEFAULT '' COMMENT '资金帐户(资金流水)',
        `income_account` varchar(128) NOT NULL DEFAULT '' COMMENT '收款账户(业务流水)',
        `pay_type_code` varchar(128) NOT NULL DEFAULT '' COMMENT '支付方式代码',
        `distributor`  varchar(128) NOT NULL DEFAULT '' COMMENT '客户代码',
        `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
        `balance_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销商账户余额(资金流水)',
        `state` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1-正常;2-作废',
        `abstract` varchar(64) NOT NULL DEFAULT '' COMMENT '摘要',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
        `operator` varchar(255) NOT NULL DEFAULT '' COMMENT '操作人',
        `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '创建时间',
        `record_time` int(11) NOT NULL DEFAULT '0' COMMENT '业务时间',
        `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `uni_serial_number` (`serial_number`) USING BTREE,
        KEY `ind_record_code` (`record_code`) USING BTREE,
        KEY `ind_detail_type` (`detail_type`) USING BTREE,
        KEY `ind_pay_type_code` (`pay_type_code`) USING BTREE,
        KEY `ind_abstract` (`abstract`) USING BTREE,
        KEY `ind_distributor` (`distributor`) USING BTREE,
        KEY `ind_record_time` (`record_time`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='收支明细';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060403', '9060400', 'act', '作废', 'fx/pending_payment/cancellation', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060404', '9060400', 'act', '删除', 'fx/pending_payment/do_delete', '4', '1', '0', '1', '0');"
);



$u['866'] = array(
    "ALTER TABLE `wms_oms_trade`
ADD COLUMN `upload_fail_num`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '上传失败次数' AFTER `upload_request_flag`;",
    "ALTER TABLE `wms_b2b_trade`
ADD COLUMN `upload_fail_num`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '上传失败次数' AFTER `upload_request_flag`;",
);

$u['882'] = array(
    //修改订单表回写状态标识说明
    " ALTER TABLE oms_sell_record MODIFY `is_back` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0-未回写;1-回写成功;2-本地回写;-1-回写失败';"
);
$u['804'] = array(
    "UPDATE sys_action SET status=1 WHERE action_id IN('13020000','13020100');",
    "ALTER TABLE kisdee_config CHANGE `kis_params` `kis_auth_token` varchar(64) NOT NULL DEFAULT '' COMMENT '访问口令';",
    "ALTER TABLE kisdee_config DROP `kis_method`;"
);
$u['873'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060500', '9060000', 'url', '收支明细', 'fx/balance_of_payments/do_list', '12', '1', '0', '1', '0');",
    "ALTER TABLE fx_income_pay ADD COLUMN `record_type` tinyint(1) DEFAULT NULL COMMENT '业务单据类型:0-收款单;1-退款单（业务流水）' AFTER `capital_type`;",
    "ALTER TABLE fx_income_pay ADD COLUMN `income_type` TINYINT(1) DEFAULT NULL COMMENT '收款方式:1线下，2账户余额（业务流水）' AFTER `income_account`;",
    "ALTER TABLE base_custom ADD COLUMN `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间';",
    "ALTER TABLE fx_income_pay ADD COLUMN `balance_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '账户余额(资金流水)' AFTER `money`;"
);
$u['bug_590'] = array(
    "alter table api_order  MODIFY column `receiver_mobile` varchar(20) DEFAULT '' COMMENT '平台电话';",
);


$u['910'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`) VALUES ('xiaomizhijia', 'xmzj', '小米之家', '1', '1');");

$u['892'] = array(
	"ALTER TABLE sys_login_log ADD `server_ip` varchar(128) DEFAULT '' COMMENT '服务器ip';",
);

$u['891'] = array(
	"update sys_params set value = 1 where param_code = 'psw_strong';",
);

