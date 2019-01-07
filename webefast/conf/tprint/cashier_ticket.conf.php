<?php

return array(
    'template' => 'report',
    'data_source' => array(
        'model' => 'oms_shop/CashierModel',
        'method' => 'print_data_default',
    ),
    'record' => array(
        '店铺名称' => 'shop_code_name',
        '订单号' => 'record_code',
        '日期' => 'create_date',
        '订单实收' => 'payable_amount',
        '结算方式' => 'pay_code_name',
        '收银员' => 'cashier_name',
        '打印时间' => 'print_time',
        '规格1名' => 'goods_spec1',
        '店铺地址' => 'shop_address',
        '店铺电话' => 'shop_phone',
    ),
    'detail' => array(
        '商品编码' => 'goods_code',
        '规格1值' => 'spec1_name',
        '规格2值' => 'spec2_name',
        '数量' => 'num',
        '金额' => 'goods_amount',
    ),
);

