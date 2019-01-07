<?php
$u['1550'] = array(
    "INSERT INTO sys_action VALUES (4020203, 4020200, 'act', '修改', 'oms/sell_record/ex_update_detail', 1, 1, 0, 1, 0);",
);

$u['bug_1640'] = array(
    "ALTER TABLE api_weipinhuijit_order_detail ADD COLUMN `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '释放模式说明'",
);

$u['bug_1541'] = array(
    "ALTER table api_weipinhuijit_order_detail ADD COLUMN  `shop_code` varchar(100) NOT NULL DEFAULT '' COMMENT '店铺code'",
);