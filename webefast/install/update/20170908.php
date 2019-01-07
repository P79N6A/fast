<?php
$u['1621'] = array(
    "alter table base_shop modify column days VARCHAR(10);",
);
$u['bug_1663'] = array(
    "ALTER TABLE `pur_payment` MODIFY COLUMN `money` decimal(10,3) NOT NULL DEFAULT '0.00' COMMENT '金额';",
);
$u['bug_1679'] = array(
    "ALTER TABLE  `api_taobao_order` add sub_order_tax_fee varchar(50) default 0 comment '天猫国际官网直供子订单关税税费';",
);

?>