<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'pur/PlannedRecordModel',
       'method'=> 'print_data_default_new',
        ),
    'record' => array(
            '采购单号' => 'record_code',
            '原单号' => 'init_code',
            '计划日期' => 'planned_time',
            '供应商' => 'supplier_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '金额' => 'money',
            '备注' => 'remark',
            '完成数量' => 'finish_num',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '采购类型' => 'pur_type_code',
        ),
        'detail' => array(
                '图片地址' => 'goods_img',
                '客户货号' => 'sku',
                '数量' => 'num',
                '货品描述' => 'remark',
                '商品编码' => 'goods_code',
                '商品名称' => 'goods_name',
                '商品条形码' => 'barcode',
                '编号' => 'id',
        ),
);


