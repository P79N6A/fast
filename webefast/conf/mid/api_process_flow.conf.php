<?php

return array(
    'mes' => array(
        'sell_record' => array(
            'record_mid_type' => 'scan', //扫描
            'check_type' => 1, //仓库
        ),
        'sell_return' => array(
            'record_mid_type' => 'return_shipping', //扫描
            'check_type' => 1, //仓库
        )
    ),
    'bserp' => array(
        'archive' => array(
            'record_mid_type' => 'to_sys', //同步到系统
            'check_type' => 1,
        ),
        'inv' => array(
            'record_mid_type' => 'to_sys', //同步到系统
            'check_type' => 1,
        ),
        'sell_record' => array(
            'record_mid_type' => 'send',//发货以后
            'check_type' => 1, //仓库
        ),
        'sell_return' => array(
            'record_mid_type' => 'receiving', //收货以后
            'check_type' => 1, //仓库
        )
    ),
);

