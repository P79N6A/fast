<?php

$u = array();
$u['586'] = array(
        "ALTER TABLE `wms_inv_compare`
    DROP INDEX `_key` ,
    ADD UNIQUE INDEX `_key` (`compare_code`, `store_code`) USING BTREE ;",
);
