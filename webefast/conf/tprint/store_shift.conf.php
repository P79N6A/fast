<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'stm/StoreShiftRecordModel',
       'method'=> 'print_data_default',
        ),
    'record' => array(
        '单据编号' => 'record_code',
        '移出仓库' => 'shift_out_store_code',
        '移入仓库' => 'shift_in_store_code',
        '商品总数量' => 'out_num',
        '打印人' => 'print_user',
        '打印时间' => 'print_time',
        '备注' => 'remark'     
    ),
    'detail' => array(
        '商品名称' => 'goods_name',
        '商品编码' => 'goods_code',
        '商品简称' => 'goods_short_name',
        '规格1' => 'spec1_name',
        '规格2' => 'spec2_name',
        '单价' => 'price',
        '移出数量' => 'out_num',
        '移入数量' => 'in_num',
        '库位' => 'shelf_code',
        '批次号' => 'lof_no',
        '生产日期' => 'production_date',
        '条形码' => 'barcode',
        '商品分类' => 'category_name'
    ),
);

