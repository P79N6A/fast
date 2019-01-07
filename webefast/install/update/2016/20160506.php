<?php

$u = array();
$u['255'] = array(
    "delete FROM sys_user_pref where iid='sell_record_fh_list/table';",
);
$u['252'] = array(
    "ALTER TABLE pur_planned_record MODIFY COLUMN record_time datetime;",
);
$u['219'] = array(
    "ALTER TABLE `oms_shop_sell_record` ADD  `lock_inv_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '库存状态：0-未占用 1-实物锁定 2-实物部分锁定' AFTER `address`;",
);
$u['215'] = array(
    "ALTER TABLE sys_api_shop_store ADD COLUMN `o2o_store` tinyint(3) DEFAULT '0' COMMENT '是否门店发货 0为否 1为门店发货'",
);

$u['248'] = array(
    "INSERT INTO `sys_action` VALUES ('3030500', '3030000', 'url', '月度销售分析', 'crm/monthly_analysis/do_list', '50', '1', '0', '1','0');",
    "update sys_action set sort_order = 100 where action_code = 'op/pur_advise/do_list'"
);
$u['bug_206'] = array(
    "ALTER TABLE `pur_purchaser_record` ADD INDEX ix_record_time ( `record_time` ) USING BTREE;",
    "ALTER TABLE `pur_purchaser_record` ADD INDEX ix_is_check_and_accept ( `is_check_and_accept` ) USING BTREE;",
    "ALTER TABLE `pur_purchaser_record` ADD INDEX ix_store_code ( `store_code` ) USING BTREE;",

    "ALTER TABLE `b2b_lof_datail` ADD INDEX ix_occupy_type ( `occupy_type` ) USING BTREE;",
    "ALTER TABLE `b2b_lof_datail` ADD INDEX ix_store_code ( `store_code` ) USING BTREE;",
    "ALTER TABLE `b2b_lof_datail` ADD INDEX ix_order_date ( `order_date` ) USING BTREE;",

    "ALTER TABLE `oms_sell_record_lof` ADD INDEX ix_order_date ( `order_date` ) USING BTREE;",
    "ALTER TABLE `oms_sell_record_lof` ADD INDEX ix_store_code ( `store_code` ) USING BTREE;"
);
