<?php
$u = array();
$u['307'] = array(
    "ALTER table op_gift_strategy_detail ADD COLUMN ranking_time_type TINYINT(3) NOT null DEFAULT '0' COMMENT '1:指定时间点 ;0: 循环整点'; ",
    "ALTER table op_gift_strategy_detail ADD COLUMN ranking_hour VARCHAR(50) NOT null DEFAULT  '' COMMENT '存指定时间点时'; "
);
$u['306'] = array(
    "INSERT INTO `sys_action` (`action_id`,`parent_id`,`type`,`action_name`,`action_code`,`sort_order`,`appid`,`other_priv_type`,`status`,`ui_entrance`)VALUES('3010103','3010100','act','导出','crm/customer/export_list','3','1','0','1','0');"
);
$u['304'] = array(
    "INSERT INTO `sys_action` VALUES ('9020300', '9020000', 'url', '网络订单应收统计', 'acc/retail_settlement/do_list', '1', '1', '0', '1', '0');",
    "UPDATE `sys_action` SET sort_order = 2 WHERE action_id = 9020100;",
    "UPDATE `sys_action` SET sort_order = 3 WHERE action_id = 9020200;"
);

$u['320'] = array(
    "INSERT INTO `sys_params` (`param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `lastchanged`, `memo`)
 VALUES ('is_all_return_contain_gift', 'oms_property', 'S001_111    订单包含的赠品一并生成售后服务单（系统发货后整单申请退货）', 'radio', '[\"关闭\",\"开启\"]', '0', '13', '开启后，发货后整单退，订单包含的赠品一并生成售后服务单', '2016-03-08 18:38:38', '');

",
);


$u['301'] = array(
    "CREATE TABLE `base_store_type` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `type_code` varchar(128) DEFAULT '' COMMENT '仓库类别代码',
    `type_name` varchar(128) DEFAULT '' COMMENT '仓库类别名称',
    `remark` varchar(255) DEFAULT '' COMMENT '备注',
    `lastchanged` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `type_code` (`type_code`) USING BTREE
  ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COMMENT='仓库类别';",
   "INSERT INTO `sys_action` VALUES ('2020400', '2020000', 'url', '仓库类别', 'base/store_type/do_list', '0', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('2020401', '2020400', 'act', '添加仓库类别', 'base/store_type/detail#scene=add', '1', '1', '0', '1','0');",
"INSERT INTO `sys_action` VALUES ('2020402', '2020400', 'act', '编辑', 'base/store_type/detail#scene=edit', '2', '1', '0', '1','0');",
"INSERT INTO `sys_action` VALUES ('2020403', '2020400', 'act', '删除', 'base/store_type/delete', '3', '1', '0', '1','0');"

);

$u['303'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050000', '30000000', 'group', '门店档案', 'store_archives', '1', '1', '0', '1', '2');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('30050100', '30050000', 'url', '门店商品', 'prm/shop_goods/do_list', '2', '1', '0', '1', '2');",
    
    "CREATE TABLE base_shop_sku (
	`shop_sku_id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`shop_code` VARCHAR (128) DEFAULT '' COMMENT '门店代码',
	`goods_code` VARCHAR (64) DEFAULT '' COMMENT '商品代码',
	`sku` VARCHAR (30) DEFAULT '' COMMENT 'SKU',
	`goods_price` DECIMAL (20, 3) DEFAULT '0.000' COMMENT '商品级售价',
	`sku_price` DECIMAL (20, 3) DEFAULT '0.000' COMMENT '条码级售价',
        `status` TINYINT(1) DEFAULT '1' COMMENT '是否启用：0-停用 1-启用',
	`create_person` VARCHAR (24) NOT NULL DEFAULT '' COMMENT '创建人',
	`create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
	`lastchanged` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
	PRIMARY KEY (`shop_sku_id`),
	UNIQUE KEY `idxu_key` (`shop_code`,`goods_code`,`sku`),
	KEY `ix_shop_code` (`shop_code`),
	KEY `ix_goods_code` (`goods_code`),
	KEY `ix_sku` (`sku`),
	KEY `ix_lastchanged` (`lastchanged`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = '门店商品(SKU)关联表';"
);
$u['319'] = array(
    "ALTER TABLE api_order ADD COLUMN is_daixiao tinyint(2) DEFAULT '0' COMMENT '是否代销: 0否，1是';",
    "INSERT INTO `sys_action` (
	`action_id`,
	`parent_id`,
	`type`,
	`action_name`,
	`action_code`,
	`sort_order`,
	`appid`,
	`other_priv_type`,
	`status`,
	`ui_entrance`
)
VALUES
	(
		'4010110',
		'4010100',
		'act',
		'删除',
		'oms/sell_record/td_view/td_delete',
		'2',
		'1',
		'0',
		'1',
		'0'
	);",
);
$u['300'] = array(
    "INSERT INTO `base_sale_channel` VALUES ('42', 'fenxiao', 'fx', '淘分销', '1', '1', '', '2016-05-21 13:57:24');",
);
$u['311'] = array(
    "UPDATE pur_planned_record pr,
    (
           SELECT
                   record_code,
                   sum(num) as num,
                   sum(finish_num) as finish_num
           FROM
                   pur_planned_record_detail pd
           GROUP BY record_code
   ) as pd_num
   SET pr.num = pd_num.num,
    pr.finish_num = pd_num.finish_num
   WHERE pr.record_code = pd_num.record_code",
    "ALTER TABLE pur_purchaser_record ADD COLUMN num INT DEFAULT '0' COMMENT '数量';",
    "ALTER TABLE pur_purchaser_record ADD COLUMN finish_num INT DEFAULT '0' COMMENT '实际入库数';",
    "UPDATE pur_purchaser_record p,
    (
           SELECT
                   record_code,
                   sum(num) AS total_finish_num,
                   SUM(notice_num) AS total_notice_num
           FROM
                   pur_purchaser_record_detail
           GROUP BY
                   record_code
   ) AS tmp
   SET p.num = tmp.total_notice_num,
    p.finish_num = tmp.total_finish_num
   WHERE
           p.record_code = tmp.record_code",
);
$u['282'] = array(
    "INSERT INTO `sys_action` VALUES ('9050000', '9000000', 'group', '物流费用对账', 'express_cost_manage', '4', '1', '0', '1', '0');",
    "UPDATE `sys_action` SET `sort_order`='6' WHERE (`action_id`='9040000');",
    "INSERT INTO `sys_action` VALUES ('9050100', '9050000', 'url', '订单运费核销明细', 'acc/order_express_detail/do_list', '1', '1', '0', '1', '0');"
);
$u['305'] = array(
            "CREATE TABLE `order_express_dz` (
                `express_dz_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `dz_code` varchar(20) NOT NULL COMMENT '对账编号',
                `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
                `dz_month` varchar(7) NOT NULL DEFAULT '0000-00' COMMENT '账期',
                `express_cost` decimal(10,2) DEFAULT '0.00' COMMENT '系统运费合计',
                `create_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '对账单创建时间',
                `lastchangedtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后修改时间',
                PRIMARY KEY (`express_dz_id`),
                UNIQUE KEY `dz_code` (`dz_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单运费对账';",
    
            "CREATE TABLE `order_express_dz_detail` (
                  `detail_dz_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `dz_code` varchar(20) NOT NULL COMMENT '对账编号',
                  `store_code` varchar(20) NOT NULL DEFAULT '' COMMENT '仓库代码',
                  `sell_record_code` varchar(20) NOT NULL DEFAULT '' COMMENT '单据编号(订单号)',
                  `express_code` varchar(20) NOT NULL DEFAULT '' COMMENT '配送方式CODE',
                  `express_no` varchar(40) NOT NULL DEFAULT '' COMMENT '快递单号',
                  `real_weigh` decimal(10,3) DEFAULT '0.000' COMMENT '实际总重量-千克',
                  `weigh_express_money` decimal(10,2) DEFAULT '0.00' COMMENT '称重后计算的快递费用',
                  `express_money` decimal(10,2) DEFAULT '0.00' COMMENT '快递公司运费',
                  `receiver_address` varchar(100) NOT NULL DEFAULT '' COMMENT '收货人地址(包含省市区)',
                  `hx_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '核销状态',
                  `hx_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '对账单核销时间',
                  `delivery_time` datetime NOT NULL COMMENT '发货时间',
                  PRIMARY KEY (`detail_dz_id`),
                  UNIQUE KEY `express_no` (`express_no`),
                  KEY `idxu_record_code` (`sell_record_code`) USING BTREE,
                  KEY `express_code` (`express_code`)
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='订单运费对账明细';"
    );