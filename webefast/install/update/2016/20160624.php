<?php

$u = array();
$u['388'] = array("DELETE FROM `sys_user_pref` WHERE iid = 'goods_do_list/table';");
$u['392'] = array(
    "ALTER TABLE `stm_goods_diy_record` ADD is_check TINYINT(1) DEFAULT 0 COMMENT '0未审核 1审核';",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010805', '6010800', 'act', '审核', 'stm/stm_goods_diy_record/do_check', '2', '1', '0', '1', '0');",
    "UPDATE `sys_action` SET action_name='确认调整' WHERE action_code='stm/stm_goods_diy_record/do_sure';");

$u['385'] = array(
    "INSERT INTO `base_express` (`company_code`, `express_code`, `express_name`) VALUES ('PINJUN', 'PINJUN', '品骏航空');",
    "INSERT INTO `base_express_company` (`company_code`, `company_name`) VALUES ('PINJUN', '品骏');");

$u['366'] = array(
    "ALTER TABLE `base_return_label` ADD `is_sys_html` TINYINT(1) DEFAULT 0 COMMENT '0-非系统内置 1-系统内置';",
    "INSERT INTO `base_return_label` (`return_label_code`, `return_label_name`, `remark`, `is_sys_html`) VALUES ('SYS001', '包含次品', '订单中包含次品', '1');"
);
$u['398'] = array(
    "CREATE TABLE `api_open_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_id` varchar(128) DEFAULT NULL COMMENT '接口请求唯一标识',
  `type` varchar(50) NOT NULL COMMENT '接口类别：taobao,jingdong,paipai',
  `method` varchar(150) DEFAULT NULL COMMENT '接口名称',
  `url` text NOT NULL COMMENT '请求地址',
  `params` mediumtext COMMENT '请求参数',
  `post_data` mediumtext COMMENT '请求业务参数',
  `return_data` text COMMENT '返回的数据',
  `add_time` datetime DEFAULT NULL COMMENT '记录时间',
  `is_err` tinyint(4) NOT NULL COMMENT 'http请求出错',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`key_id`) USING BTREE,
  KEY `type` (`type`) USING BTREE,
  KEY `method` (`method`) USING BTREE,
  KEY `add_time` (`add_time`) USING BTREE,
  KEY `is_err` (`is_err`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
",
);
$u['383'] = array(
    "CREATE TABLE `api_bserp_barcode` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `erp_config_id` int(10) NOT NULL COMMENT 'erp配置id',
        `SPTM` varchar(100) DEFAULT NULL COMMENT '商品条码',
        `SPDM` varchar(100) DEFAULT NULL COMMENT '商品代码',
        `BYZD1` varchar(255) DEFAULT NULL COMMENT 'SPGG1表Byzd1',
        `GG1DM` varchar(50) DEFAULT NULL COMMENT '颜色代码',
        `GG2DM` varchar(50) DEFAULT NULL COMMENT '尺码代码',
        `update_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0未更新，1已更新，2异常',
        `uptime` datetime NOT NULL,
        `sku` varchar(255) NOT NULL COMMENT 'sku',
        PRIMARY KEY (`id`),
        UNIQUE KEY `SPTM` (`SPTM`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='bserp条形码更新';",
);
$u['399'] = array(
    "INSERT INTO `sys_action` VALUES ('9060300', '9060000','url','分销结算单','fx/account_settlement/do_list','13','1','0','1','0');",
    "alter table oms_sell_record change fenxiao_account is_fx_settlement tinyint(3) DEFAULT '0' COMMENT '分销是否结算';",
    "alter table oms_sell_record add fx_payable_money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销结算金额';",
    "CREATE TABLE `fx_settlement` (
      `settlement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
      `record_type` varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款',
      `advance_payment` decimal(10,3) DEFAULT '0.000' COMMENT '预扣款',
      `status` tinyint(3) DEFAULT '0' COMMENT '金额状态，0:预扣款；1:已结算',
      `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
      `relation_code` varchar(128) DEFAULT '' COMMENT '关联单据编号',
      PRIMARY KEY (`settlement_id`),
      UNIQUE KEY `relation_code` (`relation_code`),
      KEY `custom_code` (`custom_code`)
    ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='分销商结算单';"
);
$u['390'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('inv_sync', 'op', '库存同步策略', 'radio', '[\"关闭\",\"开启\"]', '0', '0.00', '1-开启 0-关闭', '开启后，根据库存同步策略设置比例来计算库存并同步到各销售平台');",
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`,`value`, `remark`) VALUES('anti_oversold', 'op', '防超卖预警配置', 'radio', '[\"关闭\",\"开启\"]', '0', '1-启用 0-停用');",
    "UPDATE `sys_params` SET `parent_code`='op' WHERE (`param_code`='is_policy_store');",
    "CREATE TABLE `op_inv_sync` (
	`sync_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
	`sync_name` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略名称',
	`sync_mode` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '策略模式:1-全局;2-仓库',
	`warn_goods_val` INT NOT NULL DEFAULT '0' COMMENT '防超卖商品警戒值',
	`warn_goods_sell_shop` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '防超卖商品销售店铺',
	`warn_goods_deliver_day` INT NOT NULL DEFAULT '0' COMMENT '发货天数范围',
        `is_smart_select` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '智能选择店铺:0-关闭;1-开启',
	`is_road` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '启用在途库存:0-停用;1-启用',
	`is_safe` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '启用安全库存:0-停用;1-启用',
	`status` TINYINT (1) DEFAULT '0' COMMENT '状态:0-停用;1-启用',
	`create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
	`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`sync_id`),
	UNIQUE KEY `idxu_code` (`sync_code`),
	KEY `ix_sync_name` (`sync_name`),
	KEY `ix_status` (`status`),
	KEY `ix_lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '库存同步策略主表';",
    "CREATE TABLE `op_inv_sync_ss_relation` (
	`id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
	`code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '编码',
	`type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '编码类型:1-店铺编码;2-仓库编码',
	PRIMARY KEY (`id`),
	UNIQUE KEY `idxu_key` (`sync_code`,`code`,`type`),
	KEY `ix_sync_code` (`sync_code`),
	KEY `ix_code` (`code`),
	KEY `ix_type` (`type`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '库存策略主表店铺仓库关联表';",
    "CREATE TABLE `op_inv_sync_shop_ratio` (
	`ratio_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	`store_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '仓库代码',
	`sync_ratio` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '同步比例',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`ratio_id`),
	UNIQUE KEY `idxu_key` (`sync_code`,`shop_code`,`store_code`),
	KEY `ix_sync_code` (`sync_code`),
	KEY `ix_shop_code` (`shop_code`),
	KEY `ix_store_code` (`store_code`),
	KEY `ix_sync_ratio` (`sync_ratio`),
	KEY `ix_lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '店铺比例配置表';",
    "CREATE TABLE `op_inv_sync_goods_ratio` (
	`ratio_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '策略代码',
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '店铺代码',
	`store_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '仓库代码',
	`sku` VARCHAR (128) DEFAULT '' COMMENT 'sku',
	`sync_ratio` DECIMAL(4,2) NOT NULL DEFAULT '0.00' COMMENT '同步比例',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`ratio_id`),
	UNIQUE KEY `idxu_key` (`sync_code`,`shop_code`,`store_code`,`sku`),
	KEY `ix_sync_code` (`sync_code`),
	KEY `ix_shop_code` (`shop_code`),
	KEY `ix_store_code` (`store_code`),
	KEY `ix_sku` (`sku`),
	KEY `ix_sync_ratio` (`sync_ratio`),
	KEY `ix_lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '商品比例配置表';",
    "CREATE TABLE `op_inv_sync_warn_goods` (
	`warn_goods_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`goods_code` VARCHAR (64) DEFAULT '' COMMENT '商品代码',
	`sync_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '来源策略代码',
	`sku` VARCHAR (128) DEFAULT '' COMMENT 'sku',
	`lastchanged` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`warn_goods_id`),
	KEY `ix_sync_code` (`sync_code`),
	KEY `ix_goods_code` (`goods_code`),
	KEY `ix_sku` (`sku`),
	KEY `ix_lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '预警商品表';",
);
$u['bug_298'] = array(
    "ALTER TABLE oms_deliver_record_package ADD KEY `express_no` (`express_no`);",
);

$u['bug_286'] = array(
    "ALTER TABLE api_weipinhuijit_delivery ADD COLUMN `delivery_method` TINYINT(1) DEFAULT NULL COMMENT '配送模式:1-汽运;2-空运' AFTER `arrival_time`;",
);
$u['bug_303'] = array(
    "UPDATE `stm_goods_diy_record` SET `is_check` = 1 WHERE `is_sure` = 1;"
);

$u['bug_295'] = array(
    "ALTER TABLE goods_unique_code_log ADD UNIQUE KEY(unique_code,record_code,record_type); ",
    "ALTER TABLE goods_unique_code_log ADD COLUMN is_scan tinyint(2) DEFAULT '0' COMMENT '是否扫描: 0否，1是';"
);
$u['415'] = array(
    "create table base_express_temp  like base_express",
    "insert into base_express_temp select * from base_express",
    "DELETE from base_express
        where 
      express_code IN (
      select express_code from base_express_temp  GROUP BY express_code HAVING count(1)>1 ORDER BY express_id
      )

      AND express_id NOT IN( 
      select express_id from base_express_temp  GROUP BY express_code HAVING count(1)>1 ORDER BY express_id
       )  ",
    //"DROP table base_express_temp ",
    "ALTER TABLE `base_express`
    ADD UNIQUE INDEX `_key` (`express_code`) ;",
);
$u['400'] = array(
    "alter table oms_sell_record_detail add trade_price decimal(20,3) DEFAULT '0.000' COMMENT '批发价,即分销单价';",
    "alter table oms_sell_record_detail add fx_amount decimal(20,3) DEFAULT '0.000' COMMENT '结算金额（分销单价*num）,用于分销';",
    "INSERT INTO `sys_action` VALUES ('8070000', '8000000', 'group', '网络代销管理', 'net_fenxiao_manager', '4', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8070100', '8070000', 'url', '新增分销订单', 'fx/sell_record/add', '1', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8070200', '8070000', 'url', '分销订单查询', 'fx/sell_record/do_list', '5', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8070300', '8070000', 'url', '分销订单列表', 'fx/sell_record/ex_list', '10', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8070400', '8070000', 'url', '分销退单列表', 'fx/sell_return/after_service_list', '15', '1', '0', '1','0');",
    "CREATE TABLE `fx_pay_money_detail` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号',
    `custom_code` varchar(128) DEFAULT '' COMMENT '分销商代码',
    `record_time` datetime NOT NULL COMMENT '业务时间',
    `record_type` varchar(128) DEFAULT '' COMMENT '调整类型,sales_settlement:网单预扣款;un_sales_settlement:网单预扣款取消;sales_settlemented:网单扣款',
    `money` decimal(10,3) DEFAULT '0.000' COMMENT '发生金额',
    `frozen_money` decimal(10,3) DEFAULT '0.000' COMMENT '期末冻结金额',
    `remaining_money` decimal(10,3) DEFAULT '0.000' COMMENT '期末余额',
    `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
    `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`id`),
    KEY `custom_code` (`custom_code`)
  ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='分销结算金额详情';",
    "ALTER table base_custom add COLUMN user_code VARCHAR(128) DEFAULT null COMMENT '登录帐号';",
    "alter table oms_sell_record add fx_express_money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '分销运费金额';",
    "alter table sys_user change login_type login_type tinyint(3) DEFAULT '0' COMMENT '0：后台用户 1：门店用户 2：分销账户'"

);

$u['bug_205'] = array(
    "ALTER TABLE `wms_oms_trade`
MODIFY COLUMN `express_no`  varchar(200)  NOT NULL DEFAULT '' COMMENT 'wms发货的物流单号' AFTER `express_code`;"
);
$u['428'] = array(
    "ALTER TABLE `oms_sell_record`
ADD INDEX `delivery_date` (`delivery_date`) USING BTREE ;",
    "ALTER TABLE `base_express_company`
ADD UNIQUE INDEX `_key` (`company_code`) USING BTREE ;",
    
   "
ALTER TABLE `stm_stock_adjust_record`
MODIFY COLUMN `relation_code`  varchar(250) NULL DEFAULT '' COMMENT '关联单号' AFTER `record_code`;", 
    
    "
ALTER TABLE `stm_profit_loss_lof`
MODIFY COLUMN `take_stock_record_code`  varchar(250) NULL DEFAULT '' COMMENT '盘点单号' AFTER `record_code_list`;
"
);