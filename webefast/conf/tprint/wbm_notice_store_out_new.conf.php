<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'wbm/NoticeRecordModel',
       'method'=> 'print_data_default_new',
        ),
    'record' => array(
            '单据编号' => 'record_code',
            '关联单号' => 'relation_code',
            '原单号' => 'init_code',
            '下单时间' => 'order_time',
            '分销商' => 'distributor_name',
            '仓库' => 'store_name',
            '业务日期' => 'record_time',
            '总数' => 'num',
            '总金额' => 'money',
            '备注' => 'remark',
            '折扣' => 'rebate',
            '完成数' => 'finish_num',
            '快递单号' => 'express_no',
            '配送方式' => 'express_name',
            '添加人' => 'is_add_person',
            '添加时间' => 'is_add_time',
            '完成人' => 'is_finish_person',
            '完成时间' => 'is_finish_time',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
            '联系人' => 'name',
            '联系电话' => 'tel',
            '地址' => 'address',
        ),
        'detail' => array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '规格1' => 'spec1_name',
                 '规格1代码' => 'spec1_code',
                '规格2' => 'spec2_name',
                 '规格2代码' => 'spec2_code',
                '单价' => 'price',
                '参考价' => 'refer_price',
                '批发价' => 'pf_price',
                '折扣' => 'rebate',
                '数量' => 'num',
                '金额' => 'money',
                '完成数' => 'finish_num',
                '商品条形码' => 'barcode',
                '商品重量' => 'weight',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '吊牌价' => 'dp_price',
                '库位' => 'shelf_code',
                '批次' => 'lof_no',
            ),
        
);
