<?php

return array(
    'template' => 'report',
    'data_source'=>array(
        'model'=> 'oms/WavesRecordModel',
        'method'=> 'print_data_default_clothing',
    ),
    'record' => array(
        '波次号' => 'record_code',
        '仓库' => 'store_name',
        '商品总数量' => 'goods_count',
        '商品总金额' => 'total_amount',
        '打印时间' => 'print_time',
        '打印人' => 'print_user_name',
        '拣货员' => 'picker_name',
    ),
    'detail' => array(
        '商品名称' => 'goods_name',
        '商品编码' => 'goods_code',
        '分类' => 'category_name',
        '生产周期' => 'goods_days',
        '颜色' => 'spec1_name',
        '尺码' => 'spec2_name',
        '数量' => 'num',
        '库位名称(库位代码)' => 'shelf_name',
    ),
);




