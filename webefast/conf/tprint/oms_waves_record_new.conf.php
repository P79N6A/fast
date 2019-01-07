<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'oms/WavesRecordModel',
       'method'=> 'print_data_default_new',
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
                '交易号' => 'deal_code_total',
                '订单号' => 'sell_record_code_total',
                '序号' => 'sort_num',
                '蓝位号' => 'sort_no',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品属性' => 'goods_prop',
                '生产周期' => 'goods_days',
                '商品描述' => 'goods_desc',
                '颜色' => 'spec1_name',
                '规格1代码' => 'spec1_code',
                '尺码' => 'spec2_name',
                '规格2代码' => 'spec2_code',
                '条码' => 'barcode',
                '批次号' => 'lof_no',
                '均摊金额' => 'avg_money',
                '数量' => 'num_total',
                '库位代码' => 'shelf_code',
                '库位' => 'shelf_name',
                '扩展属性1' => 'property_val1',
                '扩展属性2' => 'property_val2',
                '扩展属性3' => 'property_val3',
                '扩展属性4' => 'property_val4',
                '扩展属性5' => 'property_val5',
                '扩展属性6' => 'property_val6',
                '扩展属性7' => 'property_val7',
                '扩展属性8' => 'property_val8',
                '扩展属性9' => 'property_val9',
                '扩展属性10' => 'property_val10',
            ),
);




