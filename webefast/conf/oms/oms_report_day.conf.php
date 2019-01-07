<?php
return array(
//     'sale_data'=>array(
//         'select'=>' count(1) as sell_num,sum(payable_money) as sell_money ',
//         'filer'=>array(),
//         'key' =>array('sell_num','sell_money'),
//     ),
    //待确认订单
    'wait_confirm'=>array(
         'select'=>'count(1) as  wait_confirm',
         'filer'=>array('order_status'=>0),
     ),
    //待拣货
    'wait_create_waves' =>array(
           'select'=>'count(1) as  wait_create_waves',
        'filer'=>array('order_status'=>1,'shipping_status'=>1)
    ),
    //待扫描
    'wait_scan' =>array(
        'select'=>'count(1) as  wait_scan',
        'filer'=>array('order_status'=>1,'shipping_status'=>3),
       // 'where'=>'waves_record_id>0 ',
    ),
    //已发货
         'oms_send'=>array(
         'select'=>'count(1) as  oms_send ',
         'filer'=>array('shipping_status'=>4),
     ),
);