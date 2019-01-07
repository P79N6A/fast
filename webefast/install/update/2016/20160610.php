<?php

$u = array();
$u['343'] = array(
    "ALTER table base_custom add COLUMN custom_nick VARCHAR(128) DEFAULT null COMMENT '分销商昵称';",
    "ALTER table base_custom add COLUMN custom_type VARCHAR(128) DEFAULT null COMMENT 'tb_fx:淘宝分销；pt_fx:普通分销';",
    "ALTER table base_custom add COLUMN shop_code VARCHAR(128) DEFAULT null COMMENT '店铺';",
    "ALTER table base_custom add COLUMN custom_user VARCHAR(128) DEFAULT null COMMENT '分销专员';",
    "ALTER table base_custom add COLUMN custom_grade tinyint(3) DEFAULT null COMMENT '分销商等级';",
    "ALTER table base_custom add COLUMN custom_price_type VARCHAR(128) DEFAULT null COMMENT '结算价格';",
    "ALTER table base_custom add COLUMN custom_rebate VARCHAR(128) DEFAULT null COMMENT '结算折扣';",
    "ALTER table base_custom add COLUMN is_effective tinyint(3) DEFAULT '1' COMMENT '是否有效';",
    "ALTER table base_custom add COLUMN pay_account VARCHAR(128) DEFAULT null COMMENT '支付宝帐号';",
    "ALTER table base_custom add COLUMN credit_lines decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '信用额度';",
    "ALTER table base_custom add COLUMN account_money decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '账户金额';",
    "ALTER table base_custom add COLUMN frozen_money decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '冻结金额';",
    "ALTER table base_custom add COLUMN settlement_amount decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '信用额度';",
    "ALTER table base_custom add COLUMN credit_lines decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '可结算金额';",
    "ALTER table base_custom add COLUMN fixed_money decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '固定费用';",
    "ALTER table base_custom add COLUMN settlement_method decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '费用结算方式';",
    "CREATE TABLE `fx_account` (
      `account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `account_code` varchar(128) DEFAULT '' COMMENT '流水号',
      `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
      `pay_type` varchar(128) DEFAULT '' COMMENT '支付方式',
      `account_money` decimal(10,3) DEFAULT '0.000' COMMENT '充值金额',
      `create_time` datetime NOT NULL COMMENT '创建时间',
      `confirm_time` datetime NOT NULL COMMENT '到款确认时间',
      `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
      `status` tinyint(3) DEFAULT '0' COMMENT '是否确认',
      `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      PRIMARY KEY (`account_id`),
      UNIQUE KEY `account_code` (`account_code`) USING BTREE,
      KEY `custom_code` (`custom_code`)
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商预存款';",
    "CREATE TABLE `fx_running_account` (
      `running_account_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `record_code` varchar(128) DEFAULT '' COMMENT '流水号',
      `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
      `record_type` varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款',
      `account_money_start` decimal(10,3) DEFAULT '0.000' COMMENT '变更前余额',
      `account_money_end` decimal(10,3) DEFAULT '0.000' COMMENT '变更后余额',
      `account_money` decimal(10,3) DEFAULT '0.000' COMMENT '金额',
      `change_time` datetime NOT NULL COMMENT '变更时间',
      `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
      `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      PRIMARY KEY (`running_account_id`),
      KEY `account_code` (`record_code`),
      KEY `custom_code` (`custom_code`)
    ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='分销商往来流水账';",
    "INSERT INTO `sys_action` VALUES ('9060000', '9000000', 'group', '分销账务管理', 'fx_manage', '1', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('9060100', '9060000','url','分销商预存款','fx/account/do_list','10','1','0','1','0');",
    "INSERT INTO `sys_action` VALUES ('9060101', '9060100', 'act', '充值确认', 'fx/account/confirm', '10', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('9060200', '9060000','url','分销商往来流水账','fx/running_account/do_list','15','1','0','1','0');"
);
$u['361'] = array(
    "DELETE FROM sys_user_pref WHERE iid = 'oms/sell_record_short_list';",
    "DELETE FROM sys_user_pref WHERE iid = 'oms/sell_record_question_list';"
);

$u['342'] = array(
    "ALTER TABLE base_store add COLUMN custom_code VARCHAR(128) DEFAULT '' COMMENT '分销商';"
);

$u['363'] = array(
    "UPDATE `sys_params` SET param_name='回写天猫退款退货',memo='开启后，如果包裹单收货，则将收货信息回流到天猫，仅支持天猫订单；如果财务同意退款，且订单为天猫订单，将同意退款状态回流到天猫' WHERE param_code='tmall_return';",
);

$u['360'] = array(
    "UPDATE `sys_params`
        SET `param_name` = 'S001_002  订单确认操作后，系统自动通知配货 ',
            `memo` = '开启后：<br>确认后的订单，系统自动通知配货，无需客服作'
        WHERE (`id` = '42');",
    "UPDATE `sys_params`
        SET `remark` = 'S001_003    系统自动通知截止发货时间',
            `memo` = '开启后：<br>\r\n1.自动通知截止发货时间假定设置了3天，那么所有计划发货时间在3天内的订单将自动通知配货，超过3天的订单不会自动通知配货。适用预售场景<br>\r\n2.通知配货操作后，系统将自动解锁相应的订单'
        WHERE (`id` = '57');",
    "ALTER TABLE oms_sell_record MODIFY lock_inv_status TINYINT (4) NOT NULL DEFAULT '0' COMMENT '库存状态：0-未占用 1-实物锁定 2-实物部分锁定 3-完全缺货';",
    "INSERT INTO `sys_schedule` (
	`code`,
	`name`,
	`status`,
	`type`,
	`desc`,
	`request`,
	`path`,
	`loop_time`,
	`task_module`,
	`plan_exec_time`)
    VALUES(
        'auto_notice',
        '订单自动通知配货',
        '1',
        '11',
        '此服务默认隐藏',
        '{\"app_act\":\"oms\/sell_record\/auto_notice\",\"app_fmt\":\"json\"}',
        'webefast/web/index.php',
        '99999',
        'sys',
        '1475204601');"
);
$u['bug_242'] = array(
    "alter table api_order modify column gift_coupon_money DECIMAL(10,2);",
    "alter table api_order modify column gift_money DECIMAL(10,2);,",
    "alter table api_order modify column integral_change_money DECIMAL(10,2);",
    "alter table api_order modify column coupon_change_money DECIMAL(10,2);",
    "alter table api_order modify column balance_change_money DECIMAL(10,2);",
    "alter table api_order_detail modify column price DECIMAL(10,2);",
    "alter table api_order_detail modify column total_fee DECIMAL(10,2);",
    "alter table api_order_detail modify column payment DECIMAL(10,2);",
    "alter table api_order_detail modify column avg_money DECIMAL(10,2);",
    "alter table api_order modify column goods_weight DECIMAL(10,2);",
    "alter table api_order modify column express_money DECIMAL(10,2);",
    "alter table api_order modify column commission_fee DECIMAL(10,2);",
    "alter table api_order_detail modify column discount_fee DECIMAL(10,2);",
    "alter table api_order_detail modify column adjust_fee DECIMAL(10,2);",
);

$u['278'] = array(
    "ALTER TABLE `base_goods` ADD COLUMN `goods_thumb_img` varchar(255) DEFAULT '' COMMENT '缩略图地址' AFTER goods_img;",
);

$u['358'] = array(
    "UPDATE `sys_action` SET `action_id`='8050000', `parent_id`='8000000', `sort_order`='3' WHERE (`action_id`='7020001');",
    "UPDATE `sys_action` SET `action_id`='8050001', `parent_id`='8050000', `sort_order`='1' WHERE (`action_id`='7020101');",
    "UPDATE `sys_action` SET `action_id`='8050002', `parent_id`='8050000', `sort_order`='2' WHERE (`action_id`='7020102');",
);