<?php

$u['1810'] = array(
    "ALTER TABLE `oms_sell_invoice` ADD COLUMN `invoice_time` datetime DEFAULT NULL COMMENT '开票日期';",
    "ALTER TABLE `oms_sell_invoice` ADD COLUMN `is_success` tinyint(3) DEFAULT '0' COMMENT '开票结果';",
);

$u['1886'] = array(
    "UPDATE `sys_action` SET  `action_code`='prm/create_store/opt_edit_warehouse' WHERE (`action_id`='5030404');",//批量更改仓库(生成移仓单)
    "UPDATE `sys_action` SET  `action_code`='prm/create_store/do_edit_one' WHERE (`action_id`='5030402');",//更改仓库(不影响库存)
);


$u['1811'] = array(
    //添加冲红原因字段 9.29  补漏11.30
    "ALTER TABLE `oms_sell_invoice_record` ADD COLUMN `chyy` varchar(255) DEFAULT '' COMMENT '冲红原因';",
);


