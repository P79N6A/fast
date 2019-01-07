<?php
$u = array();

$u['001'] = array(
    "ALTER TABLE api_order_send drop index tid_express_code;",
    "ALTER TABLE api_order_send ADD UNIQUE INDEX tid_express_code (`tid`(255),`express_no`,`sell_record_code`);",
    "ALTER TABLE op_gift_strategy_customer DROP KEY _index_key;",
    "ALTER TABLE op_gift_strategy_customer ADD UNIQUE KEY `_index_key` (`op_gift_strategy_detail_id`,`buyer_name`,`strategy_code`);",

 );


 
