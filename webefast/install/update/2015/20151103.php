<?php
$u = array();
  $u['FSF-1813'] = array(
      "ALTER TABLE `goods_inv_record`
        ADD INDEX `index1` (`goods_code`) ,
        ADD INDEX `index2` (`sku`) ,
        ADD INDEX `index3` (`store_code`) ,
        ADD INDEX `index4` (`relation_code`) ;
",
      "ALTER TABLE `api_order_send`
ADD INDEX `index1` (`sell_record_code`, `tid`) USING BTREE ,
ADD INDEX `index2` (`status`) USING BTREE ,
ADD INDEX `index3` (`shop_code`) USING BTREE ,
ADD INDEX `index4` (`status`) USING BTREE ;
",
      "ALTER TABLE `api_order`
ADD INDEX `index1` (`status`, `is_change`) USING BTREE ,
ADD INDEX `index2` (`pay_type`) USING BTREE ,
ADD INDEX `index3` (`pay_time`) USING BTREE ;
",
      "ALTER TABLE `api_order_detail`
ADD INDEX `goods_barcode` (`goods_barcode`) USING BTREE ;
",
      "ALTER TABLE `api_refund`
ADD INDEX `index1` (`status`, `is_change`) USING BTREE ;",
      
      "ALTER TABLE `oms_deliver_record`
ADD INDEX `index1` (`is_deliver`) USING BTREE ,
ADD INDEX `index2` (`is_cancel`) USING BTREE ;
",
     "ALTER TABLE `oms_waves_record`
ADD INDEX `index1` (`is_accept`) USING BTREE ,
ADD INDEX `index2` (`store_code`) USING BTREE ,
ADD INDEX `index3` (`is_cancel`) USING BTREE ,
ADD INDEX `index4` (`is_deliver`) USING BTREE ,
ADD INDEX `index5` (`is_print_express`) USING BTREE ;
" ,
      "ALTER TABLE `oms_sell_settlement_detail`
ADD INDEX `index1` (`deal_code`) USING BTREE ;
",
  "ALTER TABLE `oms_deliver_record_detail`
ADD INDEX `_waves_record_id_` (`waves_record_id`) USING BTREE ;
" ,
      "ALTER TABLE `api_goods`
ADD INDEX `index1` (`shop_code`) USING BTREE ,
ADD INDEX `index2` (`is_allow_sync_inv`) USING BTREE ,
ADD INDEX `index3` (`is_sync_inv`) USING BTREE ,
ADD INDEX `index4` (`invalid_status`) USING BTREE ;",
      
     "ALTER TABLE `oms_sell_record`
ADD INDEX `index1` (`order_status`) USING BTREE ,
ADD INDEX `index2` (`shipping_status`) USING BTREE ,
ADD INDEX `index3` (`shop_code`) USING BTREE ,
ADD INDEX `index4` (`store_code`) USING BTREE ,
ADD INDEX `index5` (`order_status`, `shipping_status`, `pay_status`, `is_fenxiao`) USING BTREE ;" ,
      
      "ALTER TABLE `api_goods_sku`
ADD INDEX `_shop_code` (`shop_code`) USING BTREE ,
ADD INDEX `index1` (`goods_from_id`) USING BTREE ;
",
  );

