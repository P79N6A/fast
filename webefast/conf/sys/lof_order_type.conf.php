<?php

return array(
    'oms' => array(
        'name' => '网络订单',
        'url' => '?app_act=oms/sell_record/view&ref=do&sell_record_code=',
    ),
    'oms_return' => array(
        'name' => '网络退单',
        'url' => '?app_act=oms/sell_return/after_service_detail&sell_return_code=',
    ),
    'oms_change' => array(
        'name' => '换货单',
        'url' => '?app_act=oms/sell_return/after_service_detail&sell_return_code=',
    ),
    'adjust' => array(
        'name' => '库存调整单',
        'url' => '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=',
    ),
    'purchase' => array(
        'name' => '采购入库单',
        'url' => '?app_act=pur/purchase_record/view&purchaser_record_id='
    ),
    'pur_return_notice' => array(
        'name' => '采购退货通知单',
        'url' => '?app_act=pur/return_notice_record/view&return_notice_record_id=',
    ),
    'pur_return' => array(
        'name' => '采购退货单',
        'url' => '?app_act=pur/return_record/view&return_record_id=',
    ),
//     'pur_return_notice'=>array(
//         'name' => '采购退货单通知单',
//         'url' =>''// '?app_act=wbm/return_record/view&return_record_code=',
//     ), 
    'wbm_store_out' => array(
        'name' => '批发销货单',
        'url' => '?app_act=wbm/store_out_record/view&store_out_record_id='//'?app_act=wbm/store_out_record/view&store_out_record_code=',
    ),
    'wbm_return_notice' => array(
        'name' => '批发退货通知单',
        'url' => '?app_act=wbm/return_notice_record/view&return_notice_record_id=',
    ),
    'wbm_return' => array(
        'name' => '批发退货单',
        'url' => '?app_act=wbm/return_record/view&return_record_id='// '?app_act=wbm/return_record/view&return_record_code=',
    ),
    'wbm_notice' => array(
        'name' => '批发通知单',
        'url' => '?app_act=wbm/notice_record/view&notice_record_id=',
    ),
    'shift_in' => array(
        'name' => '移仓单-移入',
        'url' => '?app_act=stm/store_shift_record/view&shift_record_id=',
    ),
    'shift_out' => array(
        'name' => '移仓单-移出',
        'url' => '?app_act=stm/store_shift_record/view&shift_record_id=',
    ),
    'safe_inv' => array(
        'name' => '商品库存变动',
        'url' => ''
    ),
    'stm_stock_lock' => array(
        'name' => '库存锁定单',
        'url' => '?app_act=stm/stock_lock_record/view&stock_lock_record_id='
    ),
    'weipinhui_occupy' => array(
        'name' => '唯品会订单',
        'url' => ''
    ),
);
