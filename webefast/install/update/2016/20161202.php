<?php

$u = array();

$u['842'] = array(
    "ALTER TABLE api_order_detail ADD COLUMN is_gift tinyint(4) NOT NULL DEFAULT '0' COMMENT '礼品标识：0-普通商品1-礼品';"
);
$u['853'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060400', '9060000', 'url', '待收款列表', 'fx/pending_payment/do_list', '11', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060401', '9060400', 'act', '添加收款记录', 'fx/pending_payment/add_receive', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('9060402', '9060400', 'act', '查看收款记录', 'fx/pending_payment/view_receive', '2', '1', '0', '1', '0');"
);
$u['855'] = array(
    "ALTER TABLE oms_sell_return ADD COLUMN `is_fenxiao` tinyint(3) NOT NULL DEFAULT '0' COMMENT '是否是分销退单：0 否，1是';",
    "ALTER TABLE oms_sell_return ADD COLUMN `fenxiao_name` varchar(200) DEFAULT '' COMMENT '分销商名称';",
    "ALTER TABLE oms_sell_return ADD COLUMN `fenxiao_code` varchar(128) DEFAULT '' COMMENT '分销商code';",
    "ALTER TABLE oms_sell_return ADD COLUMN `fx_payable_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销结算金额';",
    "ALTER TABLE oms_sell_return ADD COLUMN `fx_express_money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销运费金额';",
);

$u['830'] = array(
    "INSERT INTO `sys_params` (
        `param_code`,
        `parent_code`,
        `param_name`,
        `type`,
        `form_desc`,
        `value`,
        `memo`
    ) VALUES(
        'fx_finance_manage',
        'finance',
        'S005_001 启用分销账务',
        'radio',
        '[\"关闭\",\"开启\"]',
        '0',
        '默认不开启，开启后，增加分销账务模块，实现分销账务管理'
    );",
    "INSERT INTO `sys_params` (
        `param_code`,
        `parent_code`,
        `param_name`,
        `type`,
        `form_desc`,
        `value`,
        `memo`
      ) VALUES(
        'fx_finance_account_manage',
        'finance',
        'S005_002 启用资金账户',
        'radio',
        '[\"关闭\",\"开启\"]',
        '0',
        '默认不开启，开启后，增加资金账户管理以及相应充值功能'
      );",
    "ALTER TABLE wbm_store_out_record ADD pay_status TINYINT(1) DEFAULT 0 NOT NULL COMMENT '付款状态 0 未付款 1 已付款' AFTER adjust_money;"
);

$u['808'] = array(
	"CREATE TABLE `payment_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_code` varchar(20) NOT NULL COMMENT'支付方式代码',
	`payment_name` varchar(50) NOT NULL COMMENT'支付方式名字',
	`payment_account` varchar(128) NOT NULL COMMENT'账户',
	PRIMARY KEY (`id`),
	CONSTRAINT FOREIGN KEY (payment_code) REFERENCES base_pay_type(pay_type_code),
	KEY `payment_account` (`payment_account`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT'线上支付账户';",

	"CREATE TABLE `payment_account` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
		`account_code` varchar(255) NOT NULL COMMENT'收款账户代码',
	  `account_name` varchar(255) NOT NULL COMMENT'收款账户名称',
		`account_bank` varchar(555) NOT NULL COMMENT'开户银行',
		`bank_code` varchar(128) NOT NULL COMMENT'银行账号',
		PRIMARY KEY (`id`),
		UNIQUE KEY `_key` (`account_code`) USING BTREE,
		KEY `account_name` (`account_name`) USING BTREE,
		KEY `bank_code` (`bank_code`) USING BTREE,
		KEY `account_bank` (`account_bank`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT'线下收款账户表';"
);

$u['861'] = array(
	"CREATE TABLE `mid_order` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `in_out_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '单据出入库类型 1出库 2入库 移仓单根据这个标识区分出入库',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
  `order_status` varchar(20) NOT NULL DEFAULT '' COMMENT 'wms订单状态',
  `upload_request_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '上传请求时间',
  `upload_request_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '上传请示是否发送成功的标识，10 表示上传成功',
  `upload_response_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '回传订单是否接单的时间',
  `upload_response_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '回传订单是否接单，0 没回传 10 表示接单成功 20 表示接单失败 30表示主动查询接单成功',
  `upload_response_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT '回传接单失败的信息',
  `cancel_request_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '取消请求发出时间',
  `cancel_request_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '取消请求是否发送成功的标识，0表示没有取消请求 10表示是取消请求发出成功',
  `cancel_response_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '回传取消订单是否成功的时间',
  `cancel_response_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '回传取消订单是否成功，0 没回传 10 表示取消成功 20 表示取消失败 30表示主动查询取消成功',
  `cancel_response_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT '回传取消订单失败的信息',
  `process_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '处理回传信息的时间',
  `process_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '处理回传信息成功的标识, 0 表示待处理 10 解析成功 20 处理出错 30 处理结束',
  `process_err_msg` varchar(100) NOT NULL DEFAULT '' COMMENT '处理回传信息失败的原因',
  `order_from_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '回传信息来源, 10 来源于回传 20 来源于查询接口',
  `process_fail_num` tinyint(4) DEFAULT '0' COMMENT '处理失败次数',
  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '发货的物流公司CODE',
  `express_no` varchar(30) NOT NULL DEFAULT '' COMMENT '发货的物流单号',
  `order_time` int(11) NOT NULL COMMENT '发/收货的时间',
  `order_flow_end_flag` tinyint(4) NOT NULL DEFAULT '0' COMMENT '订单流转结束 取消成功 已发货/收货/关闭订单',
  `efast_store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
  `api_product` varchar(20) NOT NULL DEFAULT '' COMMENT '对接产品名称 iwms bswl',
  `mid_code` varchar(50) NOT NULL,
  `api_record_code` varchar(50) NOT NULL DEFAULT '' COMMENT '单据编号',
  `deal_code` varchar(100) NOT NULL DEFAULT '' COMMENT '订单交易号',
  `return_json_data` longtext NOT NULL COMMENT '订单/退单 主信息数据',
  `create_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  `cancel_flag` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1取消，0未取消',
  `new_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '新单号',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_order_sn_type` (`record_code`,`record_type`,`api_product`) USING BTREE,
  KEY `record_type` (`record_type`) USING BTREE,
  KEY `order_status` (`order_status`) USING BTREE,
  KEY `upload_request_time` (`upload_request_time`) USING BTREE,
  KEY `upload_request_flag` (`upload_request_flag`) USING BTREE,
  KEY `upload_response_time` (`upload_response_time`) USING BTREE,
  KEY `upload_response_flag` (`upload_response_flag`) USING BTREE,
  KEY `cancel_request_time` (`cancel_request_time`) USING BTREE,
  KEY `cancel_request_flag` (`cancel_request_flag`) USING BTREE,
  KEY `cancel_response_time` (`cancel_response_time`) USING BTREE,
  KEY `cancel_response_flag` (`cancel_response_flag`) USING BTREE,
  KEY `process_time` (`process_time`) USING BTREE,
  KEY `process_flag` (`process_flag`) USING BTREE,
  KEY `order_from_flag` (`order_from_flag`) USING BTREE,
  KEY `express_no` (`express_no`) USING BTREE,
  KEY `order_time` (`order_time`) USING BTREE,
  KEY `order_flow_end_flag` (`order_flow_end_flag`) USING BTREE,
  KEY `in_out_flag` (`in_out_flag`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `_new_record_code` (`record_type`,`new_record_code`) USING BTREE,
  KEY `_mid_code` (`mid_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

"CREATE TABLE `mid_api_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `api_product` varchar(50) DEFAULT '' COMMENT '接口代码',
  `mid_code` varchar(50) DEFAULT '' COMMENT '中间配置代码：唯一性处理',
  `api_name` varchar(128) DEFAULT '' COMMENT '接口名称',
  `api_param_json` text COMMENT '接口参数JOSN格式',
  `param_value1` varchar(128) DEFAULT NULL,
  `param_value2` varchar(128) DEFAULT NULL,
  `param_value3` varchar(128) DEFAULT NULL,
  `param_value4` varchar(128) DEFAULT NULL,
  `param_value5` varchar(128) DEFAULT NULL,
  `param_value6` varchar(128) DEFAULT NULL,
  `param_value7` varchar(128) DEFAULT NULL,
  `param_value8` varchar(128) DEFAULT NULL,
  `param_value9` varchar(128) DEFAULT NULL,
  `notes` text,
 `online_time` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`mid_code`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",


"CREATE TABLE `mid_api_join` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mid_code` int(11) NOT NULL DEFAULT '0' COMMENT '关联对接ID',
  `join_sys_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库商店代码',
  `join_sys_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0 店铺, 1 仓库',
  `outside_type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '类型: 0店铺，1仓库',
  `outside_code` varchar(128) NOT NULL DEFAULT '0' COMMENT '对接外部仓库code',
  `param_val1` varchar(128) NOT NULL DEFAULT '0' COMMENT '其他参数1',
  `param_val2` varchar(128) NOT NULL DEFAULT '0' COMMENT '其他参数2',
  `param_val3` varchar(128) NOT NULL DEFAULT '0' COMMENT '其他参数3',
  `param_val4` varchar(128) NOT NULL DEFAULT '0' COMMENT '其他参数4',
  `param_val5` varchar(128) NOT NULL DEFAULT '0' COMMENT '其他参数5',
  PRIMARY KEY (`id`),
  KEY `_index` (`mid_code`) USING BTREE,
  KEY `_index2` (`join_sys_code`,`join_sys_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
);




$u['845'] = array(

    "CREATE TABLE `mid_order_detail` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '表的主键,自增',
  `record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '订单号',
  `record_type` varchar(20) NOT NULL DEFAULT '' COMMENT '订单类型',
  `api_product` varchar(20) NOT NULL DEFAULT '' COMMENT '对接产品名称 iwms bswl',
  `barcode` varchar(20) NOT NULL DEFAULT '' COMMENT '商品条码',
  `sys_sl` int(11) NOT NULL DEFAULT '-1' COMMENT '系统商品数量',
  `api_sl` int(11) NOT NULL DEFAULT '-1' COMMENT '接口商品数量',
  `item_type` tinyint(3) DEFAULT '1' COMMENT '1是正品，0次品',
  `new_record_code` varchar(64) NOT NULL DEFAULT '' COMMENT '自动生存单号',
  `status` smallint(2) NOT NULL DEFAULT '0' COMMENT '处理状态0为处理，1已经处理',
  `lastchanged` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_record_code_type` (`record_code`,`record_type`,`api_product`,`barcode`,`item_type`) USING BTREE,
  KEY `barcode` (`barcode`) USING BTREE,
  KEY `record_code` (`record_code`) USING BTREE,
  KEY `record_type` (`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
    "CREATE TABLE `mid_process_flow` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `record_type` varchar(50) DEFAULT '' COMMENT '单据类型',
  `api_product` varchar(128) DEFAULT '' COMMENT '中间接口类型',
  `record_mid_type` varchar(128) DEFAULT '' COMMENT '单据处理类型scan,shipping',
  `check_type` tinyint(3) DEFAULT '1' COMMENT '1仓库，0店铺',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`record_type`,`record_mid_type`) USING BTREE,
  KEY `_index` (`api_product`) USING BTREE
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
",
    
 " INSERT INTO `sys_action` VALUES (15000000, 0, 'cote', '接口集成管理', 'api-mes-manage', 4, 1, 0, 1, 1);",
 " INSERT INTO `sys_action` VALUES (15010000, 15000000, 'group', '基础配置', 'mes-base', 1, 1, 0, 1, 1);",
 " INSERT INTO `sys_action` VALUES (15010100, 15010000, 'url', '授权配置', 'mid/api_config/do_list', 1, 1, 0, 1, 1);",
 " INSERT INTO `sys_action` VALUES (15020000, 15000000, 'group', '集成接口', 'mes-api', 2, 1, 0, 1, 1);",
 " INSERT INTO `sys_action` VALUES (15020100, 15020000, 'url', '单据管理', 'mid/mid/do_list', 1, 1, 0, 1, 1);",

    "

INSERT INTO `sys_schedule` (
`code`,
`name`,
task_type_code,
sale_channel_code,
`status`,
type,
`desc`,
request,
path,
max_num,
add_time,
last_time,
loop_time,
task_type,
task_module,
exec_ip,
plan_exec_time,
plan_exec_data,
update_time) VALUES ( 'cli_upload_mid', '集成单据上传', 'cli_upload_mid', '', 0, 2, '', '{\"app_act\":\"mid\\/mid\\/cli_upload\"}', 'webefast/web/index.php', 0, 0, 0, 120, 0, 'sys', '', 0, NULL, 0);

",
    "
INSERT INTO `sys_schedule` (
`code`,
`name`,
task_type_code,
sale_channel_code,
`status`,
type,
`desc`,
request,
path,
max_num,
add_time,
last_time,
loop_time,
task_type,
task_module,
exec_ip,
plan_exec_time,
plan_exec_data,
update_time)  VALUES ('cli_order_shipping_mid', '集成单据处理', 'cli_order_shipping_mid', '', 0, 2, '', '{\"app_act\":\"mid\\/mid\\/cli_order_shipping\"}', 'webefast/web/index.php', 0, 0, 0, 900, 0, 'sys', '', 0, NULL, 0);

",
);

$u['bug_693'] = array(
	"ALTER TABLE crm_activity modify column `update_inv_time` datetime NOT NULL COMMENT '库存同步时间';"
);

$u['861_1'] = array(
	"alter table mid_api_join modify column mid_code varchar(50) DEFAULT '' COMMENT '关联对接ID';",
	"ALTER TABLE mid_api_config ADD `online_time` date NOT NULL COMMENT '应用上线时间';",

);





$u['bug_746'] = array(
    "ALTER TABLE oms_return_package ADD COLUMN `receive_person` varchar(20) NOT NULL COMMENT '验收入库人';",
    "ALTER TABLE oms_return_package ADD COLUMN `receive_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '验收入库时间';",
    "UPDATE oms_return_package AS rp , oms_sell_return AS sr SET rp.receive_person = sr.receive_person , rp.receive_time = sr.receive_time
    WHERE rp.sell_return_code = sr.sell_return_code AND rp.return_order_status = 1;",
    "UPDATE oms_return_package AS rp, oms_return_package_action AS pa SET rp.receive_person = pa.user_name , rp.receive_time = pa.create_time
    WHERE rp.return_package_code = pa.return_package_code AND rp.return_order_status = 1 AND rp.receive_person = '' AND pa.action_note = '验收入库';"

);
$u['834'] = array(
    "ALTER TABLE base_custom ADD COLUMN `province` bigint(20) DEFAULT NULL COMMENT '省份';",
    "ALTER TABLE base_custom ADD COLUMN `city` bigint(20) DEFAULT NULL COMMENT '市';",
    "ALTER TABLE base_custom ADD COLUMN `district` bigint(20) DEFAULT NULL COMMENT '区域';",
    "ALTER TABLE base_custom MODIFY COLUMN `custom_grade` VARCHAR(50) DEFAULT NULL COMMENT '分销商等级';"
);


$u['884'] = array("INSERT INTO `base_sale_channel` (`sale_channel_code`, `short_code`, `sale_channel_name`, `is_system`, `is_active`) VALUES ('biyao', 'biyao', '必要', '1', '1');");

$u['808_1'] = array(
	"insert into sys_action values('2055000','2000000','group','账户档案','payment_account',4,1,0,1,0);",
	"insert into sys_action values('2055100','2055000','url','收款账户','base/paymentaccount/account_list',0,1,0,1,0);",
	"insert into payment_type(payment_code,payment_name) values('alipay','支付宝');",
	"insert into payment_type(payment_code,payment_name) values('weixinpay','微信支付');"
);

$u['bug_756'] = array("ALTER TABLE wbm_store_out_record MODIFY `pay_status` tinyint(1) DEFAULT '0' COMMENT '0 未付款 1 部分付款 2已付款';");

$u['854'] = array(
    "ALTER TABLE base_custom ADD COLUMN `yck_account_capital` decimal(10,2) DEFAULT 0 COMMENT '预存款账户金额';",
);

$u['876'] = array(
	"ALTER TABLE crm_customer modify column `consume_num` int(64) DEFAULT '0' COMMENT '消费数量';",
);

$u['874'] = array(
	"ALTER TABLE op_gift_strategy ADD `set_gifts_num` tinyint(3) NOT NULL DEFAULT '0' COMMENT '赠品指定数量赠送,0否，1是';",

	"ALTER TABLE op_gift_strategy_goods ADD `gifts_num` int(11) NOT NULL DEFAULT '0' COMMENT '限量赠送数量';",

	"ALTER TABLE op_gift_strategy_goods ADD `send_gifts_num` int(11) NOT NULL DEFAULT '0' COMMENT '已送数量';"
);

$u['758'] = array(

	"ALTER TABLE goods_inv_record ADD KEY `index5` (`remark`) USING BTREE;",
	"ALTER TABLE goods_inv_record modify column `remark` varchar(100) DEFAULT '' COMMENT '备注';"
);