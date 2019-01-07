<?php

$u['1200'] = array(
    "INSERT INTO `sys_params` (`id`,`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`remark`,`memo`)   VALUES ('613', 'property_power', 'sys_set','商品扩展属性', 'radio', '[\"关闭\",\"开启\"]', '0','1-开启 0-关闭', '默认关闭，开启后，商品列表/商品库存查询显示扩展属性并支持导出');"
);

$u['1185'] = array(
    "UPDATE base_question_label SET question_label_name='平台换货订单',remark='平台转单，识别到换货单，订单将自动设问' WHERE question_label_code='CHANGE_TRADE_JD';",
);

$u['123'] = array(
    "CREATE TABLE
    IF NOT EXISTS `inv_sync_log` (
	`log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sync_code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '策略代码',
	`user_code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '用户代码',
	`user_ip` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '用户IP',
	`tab_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '标签类型',
	`log_info` text COMMENT '日志内容',
	`log_time` datetime DEFAULT NULL COMMENT '日志时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '时间戳',
	PRIMARY KEY (`log_id`),
	KEY `tab_type` (`tab_type`)
) ENGINE = INNODB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8 COMMENT = '库存同步策略日志';",
);
$u['1196'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6030306', '6030300', 'act', '通知财务付款', 'pur/planned_record/do_notify_payment', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('', 'procurement_accounts', 'finance', 'S005_004 启用采购账务', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '', '', '默认不开启 开启后，增加采购账务模块，实现采购账务管理。详情点击查看');",
    "UPDATE sys_action SET `status` = 0 WHERE action_code = '9070000';",
    "ALTER TABLE pur_purchaser_record ADD COLUMN `money` DECIMAL (20, 3) NOT NULL DEFAULT 0.000 COMMENT '金额' AFTER finish_num;",
    "ALTER TABLE pur_purchaser_record ADD COLUMN `planned_record_code` varchar(64) DEFAULT '' COMMENT '采购订单编号';",
    "ALTER TABLE pur_planned_record ADD COLUMN `purchaser_record_code` varchar(64) DEFAULT '' COMMENT '采购入库单编号';",
    "UPDATE pur_purchaser_record p,(SELECT record_code,sum(num) as total_finish_num,SUM(notice_num) as total_notice_num,sum(money) AS money from  pur_purchaser_record_detail GROUP BY record_code) as tmp set p.num=tmp.total_notice_num,p.finish_num=tmp.total_finish_num,p.money = tmp.money WHERE p.record_code=tmp.record_code and p.money = 0;",
);

$u['1188'] = array(
    "ALTER TABLE api_order_detail ADD COLUMN is_filter tinyint(4) NOT NULL DEFAULT '0' COMMENT '分销商过滤品牌标识：0-未过滤 1-已过滤'",
    "ALTER TABLE api_taobao_fx_order ADD COLUMN is_filter tinyint(4) NOT NULL DEFAULT '0' COMMENT '分销商过滤品牌标识：0-未过滤 1-已过滤'",
    "CREATE TABLE `api_order_fx_filter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filter_obj` varchar(128) NOT NULL COMMENT '过滤对象 品牌:brand',
  `filter_code` varchar(128) DEFAULT '' COMMENT '过滤代码',
  `filter_type` int(3) DEFAULT '0' COMMENT '类型',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `filter` (`filter_obj`,`filter_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='分销订单过滤品牌表';"
);

$u['1194'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3020300', '3020000', 'url', '快递适配策略(新)', 'op/ploy/express_ploy/do_list', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3020301', '3020300', 'act', '新增/编辑', 'op/ploy/express_ploy/ploy_detail', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3020302', '3020300', 'act', '启用/停用', 'op/ploy/express_ploy/update_ploy_active', '5', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('3020303', '3020300', 'act', '删除', 'op/ploy/express_ploy/ploy_delete', '10', '1', '0', '1', '0');",
    "CREATE TABLE `op_express_ploy` (
	`ploy_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ploy_code` VARCHAR (32) NOT NULL COMMENT '快递策略编码',
	`ploy_name` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '快递策略名称',
	`ploy_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '策略启用状态, 1-启用;0-停用',
	`send_store` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '发货仓库',
	`order_pay_type` VARCHAR (10) NOT NULL DEFAULT '' COMMENT '订单支付类型, 1-款到发货/担保交易;2-货到付款, 多选',
	`default_express` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '默认配送方式代码',
	`min_freight_judge` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '最低运费判断, 1-启用;0-停用',
	`send_adapt_ratio` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '配送适配比例, 1-启用;0-停用',
	`adapt_days` INT(10) NOT NULL DEFAULT '-1' COMMENT '配送适配比例天数，启用配送适配比例后生效',
	`order_status` TINYINT (1) NOT NULL DEFAULT '-1' COMMENT '配送适配比例订单状态, 1-已付款;2-已付款未发货，启用配送适配比例后生效',
	`order_num` INT (10) NOT NULL DEFAULT '-1' COMMENT '配送适配比例订单数量, 启用配送适配比例后生效',
	`create_time` INT (11) NOT NULL DEFAULT '0' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`ploy_id`),
	UNIQUE KEY `uni_ploy_code` (`ploy_code`) USING BTREE,
	KEY `ind_ploy_status` (`ploy_status`) USING BTREE,
	KEY `ind_create_time` (`create_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略表';",
    "CREATE TABLE `op_express_ploy_shop` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ploy_code` VARCHAR (64) NOT NULL COMMENT '快递策略编码',
	`shop_code` VARCHAR (128) NOT NULL COMMENT '店铺代码',
	PRIMARY KEY (`id`),
	UNIQUE KEY `uni_code` (`ploy_code`, `shop_code`) USING BTREE,
	KEY `ind_ploy_code` (`ploy_code`) USING BTREE,
	KEY `ind_shop_code` (`shop_code`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略适配店铺表';",
    "CREATE TABLE `op_express_ploy_express` (
	`ploy_express_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ploy_code` VARCHAR (32) NOT NULL COMMENT '快递策略编码',
	`express_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '配送方式代码',
	`express_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '快递启用状态, 1-启用;0-停用',
	`express_level` TINYINT (3) NOT NULL DEFAULT '1' COMMENT '优先级',
	`express_ratio` FLOAT (6, 4) NOT NULL DEFAULT '0.00' COMMENT '适配比例',
	`insert_time` INT (11) NOT NULL DEFAULT '0' COMMENT '快递添加时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`ploy_express_id`),
	UNIQUE KEY `uni_code` (`ploy_code`, `express_code`) USING BTREE,
	KEY `uni_ploy_code` (`ploy_code`) USING BTREE,
	KEY `uni_express_code` (`express_code`) USING BTREE,
	KEY `uni_express_status` (`express_status`) USING BTREE,
	KEY `uni_express_level` (`express_level`) USING BTREE,
	KEY `uni_express_ratio` (`express_ratio`) USING BTREE,
	KEY `ind_insert_time` (`insert_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略关联快递表';",
    "CREATE TABLE `op_express_ploy_express_set` (
	`express_set_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `express_set_name` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '配置名称',
	`pid` INT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父表ID-策略关联快递表',
	`ploy_code` VARCHAR (32) NOT NULL COMMENT '快递策略编码',
	`express_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '配送方式代码',
	`is_set` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '是否配置运费公式, 1-已配置;0-未配置',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`express_set_id`),
	KEY `uni_pid` (`pid`) USING BTREE,
	KEY `uni_ploy_code` (`ploy_code`) USING BTREE,
	KEY `uni_express_code` (`express_code`) USING BTREE,
	KEY `uni_is_set` (`is_set`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略快递配置表';",
    "CREATE TABLE `op_express_ploy_area` (
	`ploy_area_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `ploy_express_id` INT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '策略快递id',
	`pid` INT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父表ID-快递配置表',
	`area_id` BIGINT (20) UNSIGNED NOT NULL COMMENT '目的地',
	PRIMARY KEY (`ploy_area_id`),
	UNIQUE KEY `uni_code` (`pid`, `area_id`) USING BTREE,
	KEY `uni_pid` (`pid`) USING BTREE,
	KEY `uni_area_id` (`area_id`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略快递关联区域表';",
    "CREATE TABLE `op_express_ploy_freight` (
	`ploy_freight_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`pid` INT (11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父表ID-快递配置表',
	`first_weight` DECIMAL (10, 2) UNSIGNED NOT NULL COMMENT '首重-千克',
	`first_weight_price` DECIMAL (10, 2) UNSIGNED NOT NULL COMMENT '首重单价',
	`added_weight` DECIMAL (10, 2) UNSIGNED NOT NULL COMMENT '续重-千克',
	`added_weight_price` DECIMAL (10, 2) UNSIGNED NOT NULL COMMENT '续重单价',
	`added_weight_rule` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '续重规则:0-实重;1-半重;2-过重',
	`free_quota` DECIMAL (20, 2) UNSIGNED NOT NULL COMMENT '免费额度',
	`rebate` DECIMAL (20, 2) UNSIGNED NOT NULL COMMENT '折扣',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`ploy_freight_id`),
	KEY `uni_pid` (`pid`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略区域运费配置表';",
    "CREATE TABLE `op_express_ploy_log` (
	`log_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`ploy_code` VARCHAR (32) NOT NULL COMMENT '快递策略编码',
	`user_code` VARCHAR (30) NOT NULL COMMENT '用户代码',
	`user_name` VARCHAR (30) NOT NULL COMMENT '用户名称',
	`action_name` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '操作名称',
	`action_time` INT (11) NOT NULL DEFAULT '0' COMMENT '操作时间',
	`action_desc` VARCHAR (255) DEFAULT '' COMMENT '操作描述',
	PRIMARY KEY (`log_id`),
	KEY `ind_ploy_code` (`ploy_code`) USING BTREE,
	KEY `ind_action_time` (`action_time`) USING BTREE
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '企业版快递策略表日志表';",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`) VALUES ('express_ploy', 'op', '快递策略（新）', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', now(), '开启后，请至运营->策略管理->快递适配策略中进行配置。');",
    "INSERT INTO `sysdb`.`sys_action_extend` (`action_id`, `extend_code`) VALUES ('3020300', 'efast5_Standard') ON DUPLICATE KEY UPDATE extend_code=VALUES(extend_code),extend_code=VALUES(extend_code);",
);


$u['1206'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020404', '4020400', 'act', '导出', 'oms/sell_record/export_question_list', '0', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('4020802', '4020800', 'act', '导出', 'oms/sell_record/export_shipped_list', '0', '1', '0', '1', '0');",
);

$U['1202'] = array(
    "CREATE TABLE `bsapi_trade` (
        `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `record_type` varchar(32) NOT NULL DEFAULT '' COMMENT '单据类型:sell_record-销售订单,sell_return-销售退单',
        `api_type` varchar(16) NOT NULL DEFAULT '' COMMENT '对接接口名称 bserp2 bserp3 bs3000j',
        `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '商店代码',
        `store_code` varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
        `is_fenxiao` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否分销单 0 不是 1 是',
        `quantity` int(10) NOT NULL DEFAULT '0' COMMENT '总数量',
        `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额',
        `express_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总运费',
        `record_date` date NOT NULL DEFAULT '0000-00-00' COMMENT '业务日期',
        `create_time` int(11) NOT NULL DEFAULT '0' COMMENT '生成时间',
        `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注(摘要)',
        PRIMARY KEY (`id`),
        UNIQUE KEY `uni_record_code_type` (`record_code`,`record_type`) USING BTREE,
        KEY `ix_shop_code` (`shop_code`) USING BTREE,
        KEY `ix_store_code` (`store_code`) USING BTREE
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='百胜api零售日报主信息';",
    "CREATE TABLE `bsapi_trade_detail` (
        `detail_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `detail_no` int(11) DEFAULT NULL COMMENT '序号',
        `record_code` varchar(32) NOT NULL DEFAULT '' COMMENT '单据编号',
        `record_type` varchar(32) NOT NULL DEFAULT '' COMMENT '单据类型:sell_record-销售订单,sell_return-销售退单',
        `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
        `sku` varchar(32) NOT NULL DEFAULT '' COMMENT 'sku',
        `num` int(10) NOT NULL DEFAULT '0' COMMENT '数量',
        `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
        PRIMARY KEY (`detail_id`),
        UNIQUE KEY `record_code_no` (`record_code`,`detail_no`) USING BTREE,
        KEY `ix_record_code` (`record_code`) USING BTREE,
        KEY `ix_goods_code` (`goods_code`) USING BTREE,
        KEY `ix_sku` (`sku`) USING BTREE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='百胜api零售日报明细';"
);

$u['1206'] = array(
    "UPDATE wms_archive AS wa,sys_api_shop_store AS ss SET wa.wms_config_id=ss.p_id WHERE ss.p_type=1 AND ss.shop_store_type=1 AND wa.efast_store_code=ss.shop_store_code AND (wa.wms_config_id IS NULL or wa.wms_config_id='' or wa.wms_config_id=0)"
);