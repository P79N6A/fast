<?php

$u = array();
$u['wms'] = array(
    "ALTER TABLE `wms_oms_trade`
ADD INDEX `_new_record_code` (`record_type`, `new_record_code`) USING BTREE ;",
    "ALTER TABLE `wms_b2b_trade`
ADD INDEX `_new_record_code` (`record_type`, `new_record_code`) USING BTREE ;
",
    "update wms_oms_trade set upload_request_flag=0 where upload_response_flag=20;
",
    "update wms_b2b_trade set upload_request_flag=0 where upload_response_flag=20;",
);







