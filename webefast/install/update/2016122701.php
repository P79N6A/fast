<?php

$u = array();

$u['bug_861'] = array(
    "UPDATE base_express_company set rule='^(268|888|588|688|368|468|568|668|768|868|968)[0-9]{9}$|^(11|22|33|268|888|588|688|368|468|568|668|768|868|968)[0-9]{10}$|^(STO)[0-9]{10}$|^(33)[0-9]{11}$|^(44)[0-9]{11}$|^(55)[0-9]{11}$|^(66)[0-9]{11}$|^(77)[0-9]{11}$|^(88)[0-9]{11}$|^(99)[0-9]{11}$' WHERE company_code='STO';",
);
$u['937'] = array(
	"insert into sys_action values('22010300','22010000','url','我要贷款','sys/service/translation',3,1,0,1,0);",
);
$u['876_1'] = array(
    "update oms_sell_record rl inner join crm_customer r2 on rl.buyer_name = r2.customer_name set rl.customer_code = r2.customer_code where rl.is_handwork = 1;",
    "UPDATE crm_customer e INNER JOIN (SELECT customer_code,count(1) as num,sum(payable_money) as money FROM oms_sell_record  WHERE shipping_status = 4 group by customer_code) d  ON e.customer_code = d.customer_code SET e.consume_money = d.money,e.consume_num = d.num;"
);