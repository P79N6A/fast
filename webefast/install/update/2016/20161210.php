<?php

$u = array();
$u['bug_788'] = array(
    "ALTER TABLE `api_weipinhuijit_wms_info`
ADD COLUMN `delivery_method`  varchar(50) NULL DEFAULT '' COMMENT '配送模式' AFTER `express`,
ADD COLUMN `arrival_time`  date NULL DEFAULT NULL COMMENT '要求到货时间' AFTER `delivery_method`,
ADD COLUMN `jit_version`  varchar(50) NULL AFTER `arrival_time`,
ADD COLUMN `price_type`  varchar(50) NULL AFTER `jit_version`,
ADD COLUMN `distributor_code`  varchar(50) NULL AFTER `price_type`,
ADD COLUMN `status`  tinyint(3) NULL DEFAULT 0 COMMENT '0未执行，1已经执行处理' AFTER `distributor_code`;
",
);

$u['876_1'] = array(
    "update oms_sell_record rl inner join crm_customer r2 on rl.buyer_name = r2.customer_name set rl.customer_code = r2.customer_code where rl.is_handwork = 1;",
    "UPDATE crm_customer e INNER JOIN (SELECT customer_code,count(1) as num,sum(payable_money) as money FROM oms_sell_record  WHERE shipping_status = 4 group by customer_code) d  ON e.customer_code = d.customer_code SET e.consume_money = d.money,e.consume_num = d.num;"
);