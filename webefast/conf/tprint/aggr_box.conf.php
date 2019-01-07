<?php

return array(
    'template' => 'report',
    'data_source' => array(
        'model' => 'b2b/BoxRecordModel',
        'method' => 'get_print_aggr_box_data',
    ),
    'record' => array(
        '批发单号' => 'record_code',
        '分销商' => 'custom_name',
        '发货仓' => 'store_name',
        '总数量' => 'num',
        '总金额' => 'money',
        'SKU总数' => 'sku_num',
        '总箱数' => 'box_num',
        '打印人' => 'print_user_name',
        '打印时间' => 'print_time'
    ),
    'detail' =>
    array(
        '箱序号' => 'box_order',
        '商品品牌' => 'brand_name',
        '商品名称' => 'goods_name',
        '商品编码' => 'goods_code',
        '商品条形码' => 'barcode',
        '规格1' => 'spec1_name',
        '规格1代码' => 'spec1_code',
        '规格2' => 'spec2_name',
        '规格2代码' => 'spec2_code',
        '数量' => 'num',
        '单价' => 'price',
        '金额' => 'money',
    ),
);

