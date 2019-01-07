<?php
$u = array();
$u['FSF-1810'] = array(
"ALTER TABLE `stm_stock_adjust_record`
ADD UNIQUE INDEX `_key` (`record_code`) USING BTREE ,
ADD INDEX `_index1` (`relation_code`) USING BTREE ,
ADD INDEX `_index2` (`store_code`) USING BTREE ,
ADD INDEX `_index3` (`is_sure`) USING BTREE ;
",
    
"ALTER TABLE `stm_stock_adjust_record_detail`
ADD UNIQUE INDEX `_index_key2` (`sku`, `record_code`) USING BTREE ,
ADD INDEX `_index1` (`goods_code`) USING BTREE ;",
    
 "ALTER TABLE `stm_take_stock_record`
DROP INDEX `record_code` ,
ADD UNIQUE INDEX `record_code` (`record_code`) USING BTREE ;",
    
"ALTER TABLE `stm_take_stock_record_detail`
ADD INDEX `_index1` (`goods_code`) USING BTREE ;
",
    //新增
    "ALTER TABLE `stm_stock_adjust_record_detail`
ADD INDEX `_index2` (`record_code`) USING BTREE ,
ADD INDEX `index3` (`sku`) USING BTREE ;
",
    
);