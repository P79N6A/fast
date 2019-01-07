<?php

$u = array();
$u['486'] = array(
    "CREATE TABLE fx_shopping_cart
(
	shopping_id INT unsigned NOT NULL PRIMARY KEY AUTO_INCREMENT,
	barcode varchar(255) DEFAULT '' COMMENT '条码',
	sku varchar(30) DEFAULT '' COMMENT 'sku',
	goods_code varchar(64) DEFAULT '' COMMENT '商品代码',
	goods_name varchar(128) DEFAULT '' COMMENT '商品名称',
	store_code varchar(128) DEFAULT '' COMMENT '仓库代码',
	store_name varchar(128) DEFAULT '' COMMENT '仓库名称',
	lof_no varchar(58) NOT NULL DEFAULT '' COMMENT '批次',
	lof_price decimal(10,3) NOT NULL DEFAULT '0.000' COMMENT '批次价格',
	spec_info varchar(128) DEFAULT '' COMMENT '规格信息',
	purchase_num INT NOT NULL DEFAULT 0 COMMENT '采购数量',
	effec_num	INT NOT NULL DEFAULT 0 COMMENT '库存',
	custom_code varchar(128) DEFAULT '' COMMENT '客户代码',
	goods_thumb_img varchar(255) DEFAULT '' COMMENT '缩略图地址'
)ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分销商待采购商品临时表';",
    "ALTER TABLE fx_shopping_cart ADD UNIQUE KEY (sku,custom_code,lof_no);"
);
$u['511'] = array("ALTER TABLE api_goods_sku ADD is_goods_init TINYINT (1) DEFAULT 0 COMMENT '是否已商品初始化, 1:已初始化 0:未初始化' AFTER is_sync_inv;",
    "ALTER TABLE api_goods_sku ADD is_stock_init TINYINT (1) DEFAULT 0 COMMENT '是否已库存初始化, 1:已初始化 0:未初始化' AFTER is_sync_inv;",
    " DELETE FROM `sys_schedule` WHERE `code` = 'auto_good_init';",
    "INSERT `sys_schedule` (
                        `code`,
                        `name`,
                        `status`,
                        `type`,
                        `desc`,
                        `request`,
                        `path`,
                        `loop_time`,
                        `task_module`
                    )
                    VALUES
                    (
                        'auto_goods_init',
                        '自动商品初始化',
                        '0',
                        '4',
                        '开启后，系统自动将未初始化的商品初始化到商品列表，默认1小时执行一次',
                        '{\"app_act\":\"prm/goods_init/auto_goods_init\",\"app_fmt\":\"json\"}',
                        'webefast/web/index.php',
                        '3600',
                        'sys'
                    );"
);
$u['bug_407'] = array(
    "ALTER TABLE api_order_detail MODIFY COLUMN `goods_barcode` varchar(255) DEFAULT NULL COMMENT '平台SKU外部编码, 淘宝平台：outer_iid';",
);
$u['bug_414'] = array(
    "INSERT INTO `sys_action` VALUES ('8040500','8040000','url','唯品会退货管理','api/api_weipinhuijit_return/do_list','4','1','0','1','0');",
    "update sys_action set sort_order=5 where action_name='唯品会仓库管理'",
    "INSERT INTO `api_weipinhuijit_warehouse` (`warehouse_no`,`warehouse_code`,`warehouse_name`,`status`,`desc`,`create_time`) VALUES (30,'PJ_ZJHZ','杭州仓',0,'杭州仓',NOW());",
);

$u['538'] = array(
    "update sys_schedule set `loop_time`=60,`desc`='此服务默认约1分钟执行一次' where code='auto_notice';",
);

$u['522'] = array(
    "CREATE TABLE `sys_api_fx` (
        `api_fx_id` INT (10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `p_id` INT (11) NOT NULL DEFAULT '0' COMMENT '关联对接ID',
        `custom_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '分销商代码',
        `outside_code` VARCHAR (128) NOT NULL DEFAULT '' COMMENT '对接外部分销商代码',
        PRIMARY KEY (`api_fx_id`),
        UNIQUE KEY `id` (`custom_code`,`p_id`) USING BTREE
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = 'ERP分销商关联表';");

$u['516'] = array(
    "INSERT INTO `sys_params` (`param_id`, `param_code`, `parent_code`, `param_name`, `type`, `form_desc`, `value`, `sort`, `remark`, `memo`) VALUES ('', 'sum_weight', 'oms_property', 'S001_112 转单计算订单理论重量', 'radio', '[\"关闭\",\"开启\"]', '0', '15', '1-开启 0-关闭', '开启后，根据商品列表中商品重量计算出订单理论重量');",
    "DELETE FROM sys_user_pref WHERE iid = 'oms/sell_record_combine_ex_list';",
);

$u['489'] = array(
    "INSERT INTO `sys_role` VALUES (101, 'oms_shop', '门店用户', 0, 1,11, '门店内置用户', '', '2016-8-6 14:47:07');",
    "    INSERT INTO `sys_role_action` VALUES (101, 1000000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 1010000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 1011000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 1040000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 1041100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 2000000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 2050000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 2050200);",
    "  INSERT INTO `sys_role_action` VALUES (101, 2050300);",
    "  INSERT INTO `sys_role_action` VALUES (101, 6000000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 6020000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 6020100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 6020200);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30000000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30010000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30010100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30010200);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30020000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30020100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30030000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30030100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30040000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30040100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30050000);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30050100);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30050102);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30050103);",
    "  INSERT INTO `sys_role_action` VALUES (101, 30050104);",
);


$u['536'] = array(
    "DELETE from base_area where type=1 AND id<>1 AND id<>250",
    "insert into base_area(id,type,name,parent_id) VALUES (250,1,'海外',0);",
    "insert into base_area(id,type,name,parent_id) VALUES (250000,2,'海外',250);",
    "insert into base_area(id,type,name,parent_id) VALUES (25000000,3,'海外',250000);",
);
$u['bug_418'] = array(
 "ALTER TABLE oms_sell_return ADD COLUMN `note_num` int(11) NOT NULL DEFAULT '0' COMMENT '通知数量';",
 "ALTER TABLE oms_sell_return ADD COLUMN `recv_num` int(11) NOT NULL DEFAULT '0' COMMENT '完成数量';",
"UPDATE oms_sell_return sr,
(
	SELECT
		sum(note_num) AS note_num,
		sum(recv_num) AS recv_num,
		sell_return_code
	FROM
		oms_sell_return_detail
	GROUP BY
		sell_return_code
   ) AS std
   SET sr.note_num = std.note_num,
       sr.recv_num = std.recv_num
   WHERE sr.sell_return_code = std.sell_return_code",

);


$u['516'] = array(
"ALTER TABLE `oms_sell_record` MODIFY COLUMN `goods_weigh` decimal(10,3) NOT NULL DEFAULT '0.00' COMMENT '商品总重量-千克';"
);