<?php

$u = array();
$u['442'] = array(
    "ALTER TABLE oms_sell_return ADD COLUMN is_exchange_goods TINYINT(1) NOT NULL DEFAULT '0' COMMENT '是否换货: 0否，1是' AFTER change_avg_money;",
    "UPDATE oms_sell_return sr,(SELECT DISTINCT sc.sell_return_code FROM oms_sell_change_detail sc) AS tmp SET sr.is_exchange_goods = 1 WHERE sr.sell_return_code = tmp.sell_return_code;"
);
$u['436'] = array(
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21020000', '21000000', 'group', '售后统计', 'cs-reports', '2', '1', '0', '1', '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('21020100', '21020000', 'url', '售后退货数据分析', 'rpt/sell_return/after_analysis', '1', '1', '0', '1', '0');",
    "UPDATE `sys_action` SET `sort_order`=3 WHERE `action_id`='21010000';",
    "UPDATE `sys_action` SET `sort_order`=4 WHERE `action_id`='21002000';"
);
$u['437']=array(   
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6020101', '6020100', 'act', '安全库存导入', 'prm/inv/safe_import', '1', '1', '0', '1', '0'); " 
);
$u['438']=array(   
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010501', '6010500', 'act', '部分盘点(sku级)', 'stm/take_stock_record/stock_part', '1', '1', '0', '1', '0');",
"INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('6010502', '6010500', 'act', '全盘', 'stm/take_stock_record/stock_all', '2', '1', '0', '1', '0');"
);
$u['444'] = array(
    "INSERT INTO `sys_action` VALUES ('8010600', '8010000', 'url', '分销商审核', 'base/custom/review_list', '5', '1', '0', '1','0');",
);

$u['432'] = array(
    "CREATE TABLE `api_bserp_wbm_record` (
        `id` INT (11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `record_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '单据编号(批发销货单号/批发退货单号)',
        `order_type` TINYINT (1) NOT NULL DEFAULT '1' COMMENT '单据类型 1 批发销货单 2批发退货单',
        `store_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT '仓库代码',
        `erp_store_code` VARCHAR (20) NOT NULL DEFAULT '' COMMENT 'erp仓库代码',
        `upload_status` TINYINT (1) NOT NULL DEFAULT '0' COMMENT '上传状态 0未上传 1已上传 2上传失败',
        `upload_time` datetime NOT NULL COMMENT '上传时间',
        `upload_msg` VARCHAR (255) DEFAULT NULL COMMENT '上传失败原因',
        PRIMARY KEY (`id`),
        UNIQUE KEY `record_code` (`record_code`),
        KEY `upload_time` (`upload_time`),
        KEY `store_code` (`store_code`),
        KEY `erp_store_code` (`erp_store_code`),
        KEY `order_type` (`order_type`)
    ) ENGINE = INNODB DEFAULT CHARSET = utf8 COMMENT = 'erp上传批发单据';",
    "INSERT INTO `sys_schedule` (
        `code`,
        `name`,
        `status`,
        `type`,
        `desc`,
        `request`,
        `path`,
        `max_num`,
        `add_time`,
        `last_time`,
        `loop_time`,
        `task_type`,
        `task_module`,
        `plan_exec_time`,
        `plan_exec_data`,
        `update_time`
        )
    VALUES
        (
          'erp_wbm_record_upload_cmd',
          '批发单据同步',
          '0',
          '3',
          '仅支持BSERP2和BS3000J产品对接，系统的批发销货单和批发退货单，上传到ERP。此服务120分钟运行一次。',
          '{\"action\":\"sys/erp_config/wbm_record_upload_cmd\"}',
          '',
          '0',
          '0',
          '0',
          '7200',
          '0',
          'api',
          '0',
          NULL,
          '0');",
    "INSERT INTO `sys_action` (`action_id`, `parent_id`, `type`, `action_name`, `action_code`, `sort_order`, `appid`, `other_priv_type`, `status`, `ui_entrance`) VALUES ('12020200', '12020000', 'url', 'BSERP2批发单据', 'erp/bserp/wbm_list', '1', '1', '0', '1', '1');"
);

$u['451'] = array(
    "INSERT INTO `sys_params` (`param_id`,`param_code`,`parent_code`,`param_name`,`type`,`form_desc`,`value`,`sort`,`remark`,`memo`) VALUES ('','goods_sub_barcode','waves_property','波次订单导出商品子条码列','radio','[\"关闭\",\"开启\"]','0','0.00','1-开启 0-关闭','开启后，波次订单详情页‘导出波次订单’，增加商品子条码列数据');"
);

$u['444'] = array(
    "INSERT INTO `sys_action` VALUES ('8010600', '8010000', 'url', '分销商审核', 'base/custom/review_list', '5', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8090000', '8000000', 'group', '网络经销管理', 'net_jingxiao_manager', '6', '1', '0', '1','0');",
    "INSERT INTO `sys_action` VALUES ('8090100', '8090000', 'url', '经销采购订单', 'fx/purchase_record/do_list', '1', '1', '0', '1','0');",
    "ALTER TABLE fx_custom_grades_detail ADD  UNIQUE KEY `custom_grade_code` (`grade_code`,`custom_code`);"
);

$u['bug_355'] = array(
                "INSERT INTO `sys_schedule` (
                    `code`,
                    `name`,
                    `status`,
                    `type`,
                    `desc`,
                    `request`,
                    `path`,
                    `last_time`,
                    `loop_time`,
                    `task_type`,
                    `task_module`,
                    `plan_exec_time`)
                VALUES(
                    'auto_good_init',
                    '自动商品初始化',
                    '0',
                    '11',
                    '自动商品初始化',
                    '{\"action\":\"prm/goods_init/auto_goods_init\"}',
                    'webefast/web/index.php',
                    '1442302368',
                    '3600',
                    '0',
                    'sys',
                    '1552303297');"
    );