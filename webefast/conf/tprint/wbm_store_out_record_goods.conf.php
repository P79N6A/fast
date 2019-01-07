<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'wbm/StoreOutRecordModel',
       'method'=> 'print_data_goods_new',
        ),
    'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '通知单号' => 'relation_code',
            '分销商' => 'distributor_name',
            '仓库' => 'store_name',
            '业务日期' => 'record_time',
            '总金额' => 'money',
            '备注' => 'remark',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            'PO号' => 'PO_record',
            '送货仓库' => 'warehouse_name',
            '拣货单号' => 'pick_no_total',
        ),
        'detail' => array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '规格1' => 'spec1_name',
                '规格1代码' => 'spec1_code',
                '规格2' => 'spec2_name',
                '规格2代码' => 'spec2_code',
                '实际出库数' => 'num',
                '通知数' => 'enotice_num',
                '条码' => 'barcode',
                '库位' => 'shelf_code',
                '下单日期' => 'order_time',            
            ),
);

