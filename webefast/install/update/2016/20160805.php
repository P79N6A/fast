<?php

$u = array();
$u['489'] = array(
    "INSERT INTO `sys_role` VALUES (100, 'distribution', '分销用户', 0, 1, 12, '分销内置用户12', '', '2016-7-25 10:54:27');",
    "INSERT INTO `sys_role_action` VALUES (100, 8000000);",
    "INSERT INTO `sys_role_action` VALUES (100, 8070000);",
    "INSERT INTO `sys_role_action` VALUES (100, 8070100);",
    "INSERT INTO `sys_role_action` VALUES (100, 8070200);",
    "INSERT INTO `sys_role_action` VALUES (100, 8070300);",
    "INSERT INTO `sys_role_action` VALUES (100, 8070400);",
    "INSERT INTO `sys_role_action` VALUES (100, 8080000);",
    "INSERT INTO `sys_role_action` VALUES (100, 8080100);",
    "INSERT INTO `sys_role_action` VALUES (100, 8080300);",
    "INSERT INTO `sys_role_action` VALUES (100, 9000000);",
    "INSERT INTO `sys_role_action` VALUES (100, 9060000);",
    "INSERT INTO `sys_role_action` VALUES (100, 9060100);",
    "INSERT INTO `sys_role_action` VALUES (100, 9060101);",
    "INSERT INTO `sys_role_action` VALUES (100, 9060200);",
    "INSERT INTO `sys_role_action` VALUES (100, 9060300);",
    "insert IGNORE into sys_user_role (user_id,role_id)
select user_id,100 as role_id from sys_user where login_type=2",
);
$u['496'] = array(
    "INSERT INTO `base_return_label` (`return_label_code`,`return_label_name`,`return_label_img`,`remark`,`is_sys_html`) VALUES ('SYS002','申请仅退款（已发货）',NULL,'客户申请仅退款，但系统已发货','1');",
    "DELETE FROM sys_user_pref WHERE iid = 'sell_return_after_service/table';"
);
$u['486'] = array(
    "ALTER TABLE goods_lof add COLUMN lof_price decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '批次价格';"
);
$u['498'] = array(
    "INSERT IGNORE INTO `sys_action` VALUES (1050000, 1000000, 'group', '历史数据结转', 'sys_carry', 4, 1, 0, 1, 0);",
    "INSERT IGNORE INTO `sys_action` VALUES (1051000, 1050000, 'url', '结转记录查询', 'sys/carry/do_list', 1, 1, 0, 1, 0);",
    "INSERT IGNORE INTO `sys_action` VALUES (1052000, 1050000, 'url', '结转设置', 'sys/carry/set_carry', 2, 1, 0, 1, 0);",
    "INSERT IGNORE INTO `sys_action` VALUES (1060000, 1000000, 'group', '历史数据查询', 'sys_carry_data', 4, 1, 0, 1, 0);", "
INSERT IGNORE INTO `sys_action` VALUES (1061000, 1060000, 'url', '历史发货订单查询', 'sys/carry_data/sell_record_list', 0, 1, 0, 1, 0);",
    "INSERT IGNORE INTO `sys_action` VALUES (1062000, 1060000, 'url', '历史退货订单查询', 'sys/carry_data/sell_return_list', 1, 1, 0, 1, 0);",
    "CREATE TABLE `sys_carry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '任务编号',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0,自动， 1手动',
  `start_date` date DEFAULT NULL COMMENT '开始日期',
  `end_date` date NOT NULL COMMENT '结束日期',
  `record_num` int(11) DEFAULT '0' COMMENT '订单数',
  `return_num` int(11) DEFAULT '0' COMMENT '退单数',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `task_start_time` datetime DEFAULT NULL COMMENT '任务执行开始时间',
  `task_end_time` datetime DEFAULT NULL COMMENT '任务执行结束时间',
  `msg` varchar(500) DEFAULT NULL,
  `state` tinyint(2) DEFAULT '0' COMMENT '任务状态0，9',
  `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_sn` (`task_sn`) USING BTREE,
  KEY `_state` (`state`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='结转任务主表';",
    "CREATE TABLE `sys_carry_task` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主任务编号',
  `task_type` varchar(30) NOT NULL DEFAULT '' COMMENT '任务类型',
  `task_code` varchar(50) NOT NULL DEFAULT '' COMMENT '任务代码',
  `parent_task_code` varchar(50) DEFAULT '',
  `task_data` longtext,
  `task_param` longtext NOT NULL COMMENT '任务参数',
  `status` tinyint(1) DEFAULT '0' COMMENT '任务状态0未开始，1执行中，2结束',
  `sys_task_id` int(11) DEFAULT '0' COMMENT '关联系统任务ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `task_code` (`task_code`,`task_type`, `parent_task_code`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='结转执行任务';",
    "
CREATE TABLE `sys_carry_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_sn` varchar(50) NOT NULL DEFAULT '' COMMENT '结转任务编号',
  `task_tb` varchar(50) NOT NULL DEFAULT '' COMMENT '结转表',
  `sys_num` int(11) DEFAULT '0' COMMENT '系统数量',
  `move_num` int(11) DEFAULT '0' COMMENT '结转数量',
  `del_num` int(11) DEFAULT '0' COMMENT '删除数量',
  PRIMARY KEY (`id`),
  UNIQUE KEY `_key` (`task_sn`,`task_tb`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='结转数据记录';
",
);

$u['494'] = array(
    "INSERT INTO `order_combine_strategy` (`rule_code`, `rule_status_value`, `rule_desc`, `rule_scene_value`) VALUES ('order_combine_is_houtai', '1', '销售平台为[后台]订单参与合并', '0');",
    "UPDATE `sys_schedule` SET `desc`='仅支持同收货人同店铺订单合并，订单已打印快递单/换货单/拆分单/后台订单是否合并，请至运营->订单合并规则中配置' WHERE `code`='auto_record_combine';"
);

$u['493'] = array("ALTER TABLE base_record_type ADD UNIQUE INDEX record_type_code (record_type_code);",
    "INSERT INTO `base_record_type` (`record_type_code`,`record_type_name`,`record_type_property`,`sys`,`remark`) VALUES('803','拆组装调整','8','1','系统内置档案，不允许删除');");
$u['bug_400'] = array(
    "ALTER TABLE api_taobao_fx_trade ADD COLUMN `order_message` varchar(255) DEFAULT '' COMMENT '采购单留言列表'",
    "ALTER TABLE api_taobao_fx_trade ADD COLUMN `supplier_memo` varchar(255) DEFAULT '' COMMENT '供应商备注'",
);

$u['507'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('22020000', '22000000', 'group', '淘宝商品发布', 'tb_goods_issue', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('22020200', '22020000', 'url', '宝贝列表', 'api/tb_issue/do_list', '2', '1', '0', '1', '0');",
    "CREATE TABLE `api_tb_goods_issue` (
	`issue_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`num_iid`  VARCHAR (128) NOT NULL COMMENT '商品数字ID',
	`goods_code` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '系统商品代码',
	`shop_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '系统店铺代码',
	`title` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '宝贝标题',
	`category_id` VARCHAR (50) NOT NULL DEFAULT '' COMMENT '宝贝类目ID',
	`sub_title` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '宝贝卖点',
	`price` DECIMAL (20, 3) NOT NULL DEFAULT '0.000' COMMENT '一口价',
	`quantity` INT NOT NULL DEFAULT '0' COMMENT '数量',
        `barcode` varchar(255) NOT NULL DEFAULT '' COMMENT '条形码',
	`outer_id` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '商家编码(系统商品编码)',
	`pic_url` TEXT NOT NULL DEFAULT '' COMMENT '宝贝图片地址',
	`item_prop` LONGTEXT NOT NULL DEFAULT '' COMMENT '类目属性,json格式',
	`shelf_time` VARCHAR(20) NOT NULL DEFAULT 'setted' COMMENT '定时上架：stock-进仓库;setted-定时上架;now-立刻上架',
	`timing` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '定时上架时间',
	`prov` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '省',
	`city` VARCHAR (64) NOT NULL DEFAULT '' COMMENT '市',
	`postage_template` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '运费模板ID',
	`weight` DECIMAL (10, 3) NOT NULL DEFAULT '0.000' COMMENT '物流重量，用于按重量计费的运费模板。注意：单位为kg',
	`cubage` DECIMAL (10, 3) NOT NULL DEFAULT '0.000' COMMENT '物流体积，单位为立方米',
	`desc` TEXT NOT NULL DEFAULT '' COMMENT '宝贝描述',
	`issue_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '商品发布状态：0-未发布;1-已发布;2-发布中',
	`success_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '商品发布是否成功：0-失败;1-成功',
	`fail_reason` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '发布失败原因',
	`create_person` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '创建人',
	`create_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`issue_id`),
	UNIQUE KEY `idxu_code` (`goods_code`,`shop_code`),
	KEY `ix_num_iid` (`num_iid`),
	KEY `ix_goods_code` (`goods_code`),
	KEY `ix_shop_code` (`shop_code`),
	KEY `ix_outer_id` (`outer_id`),
	KEY `ix_on_shelf_status` (`shelf_time`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '商品发布-宝贝数据表';",
    "CREATE TABLE `api_tb_goods_sell_prop` (
	`sell_prop_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`num_iid`  VARCHAR (128) NOT NULL COMMENT '商品数字ID',
        `goods_code` varchar(64) NOT NULL DEFAULT '' COMMENT '系统商品代码',
        `shop_code` varchar(128) NOT NULL DEFAULT '' COMMENT '系统店铺代码',
	`sku` VARCHAR (128) NOT NULL DEFAULT '' COMMENT 'SKU编码',
	`spec1_code` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '颜色编码',
	`spec2_code` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '尺码编码',
	`sku_outer_id`  VARCHAR (128) NOT NULL COMMENT '商家编码',
	`sku_barcode` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '条形码',
	`sku_price` DECIMAL (20, 3) NOT NULL DEFAULT '0.000' COMMENT '一口价',
	`sku_quantity` INT (11) NOT NULL DEFAULT '0' COMMENT '数量',
	`sku_pic_url` VARCHAR (255) NOT NULL DEFAULT '' COMMENT '图片地址',
	`create_time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`sell_prop_id`),
	UNIQUE KEY `idxu_code` (`num_iid`,`sku`)
) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '商品发布-宝贝销售属性';
",
);


$u['457'] = array(
    "ALTER TABLE `api_weipinhuijit_return_record` DROP COLUMN `shop_code`;",
    "ALTER TABLE `api_weipinhuijit_return_detail` DROP INDEX barcode;",
    "ALTER TABLE `api_weipinhuijit_return_detail` ADD INDEX return_sn (`return_sn`);",
    "ALTER TABLE `api_weipinhuijit_return_detail` ADD CONSTRAINT `return_sn_barcode` UNIQUE (`return_sn`,`barcode`,`po_no`,`box_no`);",
);

$u['bug_411'] = array(
    "ALTER TABLE oms_sell_return ADD COLUMN sell_return_package_code VARCHAR (20) NOT NULL DEFAULT '' COMMENT '退货包裹单号' AFTER deal_code_list;",
    "UPDATE oms_sell_return r,oms_return_package p SET r.sell_return_package_code=p.return_package_code WHERE r.sell_return_code=p.sell_return_code AND p.return_order_status != 2;"
);

$u['500'] = array(
    //门店菜单ui_entrance置为0
    "UPDATE sys_action SET ui_entrance=0 WHERE action_id in('30000000','30010000','30010104','30010108','30020000','30020100','30020101','30030000','30030100','30040000','30040100','30050000','30050100');",
    //更新门店部分菜单id
    "UPDATE sys_action SET action_id='30010100' WHERE action_id='30010104';",
    "UPDATE sys_action SET action_id='30010200' WHERE action_id='30010108';",
    "DELETE FROM sys_action WHERE action_id='30020101';",
    "DELETE FROM sys_role_action WHERE action_id='30020101';",
    //增加门店商品按钮权限控制
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050101', '30050100', 'url', '添加门店商品', 'prm/shop_goods/add_shop_goods', '1', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050102', '30050100', 'url', '更改启用状态', 'prm/shop_goods/update_active', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050103', '30050100', 'url', '批量更改状态', 'prm/shop_goods/batch_update_active', '3', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050104', '30050100', 'url', '修改门店售价', 'prm/shop_goods/update_price', '4', '1', '0', '1', '0');",
);


$u['491'] = array(
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21010600', '21010000', 'url', '销售订单分析', 'rpt/sell_record_statistic/analyse', '3', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21010700', '21010000', 'url', '销售商品分析', 'rpt/sell_record_goods/analyse', '4', '1', '0', '1', '0');",
);

$u['503'] = array(
    "CREATE TABLE fx_purchaser_return_record
(
	fx_purchaser_return_id INT unsigned PRIMARY KEY AUTO_INCREMENT,
	return_record_code varchar(64) NOT NULL DEFAULT '' COMMENT '单据编号',
	init_code varchar(64) NOT NULL DEFAULT '' COMMENT '原单号',
	custom_code varchar(128) NOT NULL DEFAULT '' COMMENT '分销商代码',
	store_code varchar(128) NOT NULL DEFAULT '' COMMENT '仓库代码',
	is_check tinyint(3) NOT NULL DEFAULT '0' COMMENT '0未确认  1确认',
  record_time date NOT NULL DEFAULT '0000-00-00' COMMENT '业务时间',
  order_time datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '下单日期',
  num int(11) NOT NULL DEFAULT '0' COMMENT '计划退货总数',
	express_money decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '运费',
  finish_num int(11) NOT NULL DEFAULT '0' COMMENT '实际入库总数',
  sum_money decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '总金额',
  is_add_person varchar(64) NOT NULL DEFAULT '' COMMENT '添加人',
  is_settlement tinyint(3) NOT NULL DEFAULT '0' COMMENT '分销结算，1:已结算，0:未结算',
  is_store_in tinyint(3) NOT NULL DEFAULT '0' COMMENT '入库，1:已入库，0:未入库',
  is_store_in_time datetime DEFAULT '0000-00-00 00:00:00' COMMENT '入库日期',
	remark varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  UNIQUE KEY `return_record_code` (`return_record_code`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经销采购退货单';",
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,`ui_entrance`) VALUES ('8090200','8090000','url','经销采购退货单','fx/purchase_return_record/do_list','1','1','0','1','0');
",
    "CREATE TABLE fx_purchaser_return_record_detail
(
	return_record_detail_id int(11) unsigned PRIMARY KEY AUTO_INCREMENT,
	pid int(11) DEFAULT '0',
	record_code varchar(128) NOT NULL DEFAULT '' COMMENT '单据编号',
	goods_code varchar(64) NOT NULL DEFAULT '' COMMENT '商品代码',
  goods_name varchar(255) NOT NULL DEFAULT '' COMMENT '商品名称',
	spec1_code varchar(64) NOT NULL DEFAULT '' COMMENT '颜色代码',
  spec2_code varchar(64) NOT NULL DEFAULT '' COMMENT '尺码代码',
  barcode varchar(128) NOT NULL DEFAULT '' COMMENT '商品条形码',
  sku varchar(128) NOT NULL DEFAULT '' COMMENT 'sku',
  price decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '采购单价',
  money decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '金额',
  finish_num int(11) NOT NULL DEFAULT '0' COMMENT '实际退货数',
  num int(11) NOT NULL DEFAULT '0' COMMENT '计划退货数',
  goods_property int(4) NOT NULL DEFAULT '0' COMMENT '商品性质 0-正常 1-回写',
  cost_price decimal(20,3) NOT NULL DEFAULT '0.000' COMMENT '成本单',
  remark varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  lastchanged timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='经销采购退货单明细';",
    "ALTER TABLE wbm_return_notice_record ADD COLUMN jx_return_code varchar(128) NOT NULL DEFAULT '' COMMENT '经销采购退货单编号';",
    "ALTER TABLE fx_settlement MODIFY record_type varchar(128) DEFAULT '' COMMENT '业务类型，pre_deposits：预存款；sales_settlement：销售结算;sales_refund:销售退款;purchase_settlement: 采购结算;purchase_refund：采购退款';"
);

$u['499'] = array(
    "ALTER TABLE base_custom ADD COLUMN company_name VARCHAR(128) DEFAULT '' COMMENT '公司名称';"
);