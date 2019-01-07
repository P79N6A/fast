<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'oms/WavesRecordModel',
       'method'=> 'print_oms_waves_goods',
        ),
    'record' => array(
            '买家留言' => 'buyer_remark',
            '商家留言' => 'seller_remark',
            '仓库留言' => 'store_remark',
            '快递单号' => 'express_no',
            '交易号' => 'deal_code_list',
            '订单号' => 'sell_record_code',
            '下单时间' => 'record_time',
            '付款时间' => 'pay_time',
            '打印时间' => 'print_time',
    		'打印人' => 'print_user',
            '商品总数量' => 'goods_num',
            '商品总重量' => 'goods_weigh',
            '商品总金额' => 'goods_money',
            '应收金额' => 'payable_money',
            '实付金额' => 'paid_money',
            '运费' => 'express_money',
    		'蓝位号' => 'sort_no',
            '波次单号' => 'waves_record_code',
            '买家昵称' => 'buyer_name',
            '收货人姓名' => 'receiver_name',
            '收货人手机' => 'receiver_mobile',
            '收货人电话' => 'receiver_phone',
            '收货省' => 'receiver_province',
            '收货市' => 'receiver_city',
            '收货区/县' => 'receiver_district',
            '收货街道' => 'receiver_street',
            '收货地址（无省市区）' => 'receiver_addr',
            '收货地址（含省市区）' => 'receiver_address',
            '收收货邮编' => 'receiver_zip_code',
            '收货目的地' => 'receiver_top_address',
            '发货仓库' => 'store_code_name',
            '发货商店' => 'sender_shop_name',
            '发货方联系人' => 'sender',
            '发货方联系手机' => 'sender_mobile', //
            '发货方联系电话' => 'sender_phone', //
            '发货地址（无省市区）' => 'sender_addr',
            '发货地址（含省市区）' => 'sender_address',
            '发货邮编' => 'sender_zip',
            '发货街道' => 'senderr_street',
            '发货区/县' => 'senderr_district',
            '发货市' => 'sender_city', //新增
            '发货省' => 'sender_province', //新增
            //'发货时间' => 'sender_date',//新增
            '发货打单员' => 'sender_operprint', //新增
            '支付方式' => 'pay_code',
        ),
        'detail' => array(
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '规格1' => 'spec1_name',
                '规格2' => 'spec2_name',
                '单价' => 'goods_price',
                '数量' => 'num',
                '均摊金额' => 'avg_money',
                '库位' => 'shelf_code',
                '条形码' => 'barcode',
                '平台规格' => 'platform_spec',
                '批次号' => 'lof_no',
                '商品分类' => 'category_name',
        ),
);

