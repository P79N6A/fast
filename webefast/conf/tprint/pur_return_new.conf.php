<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'pur/ReturnRecordModel',
       'method'=> 'print_data_default_new',
        ),
    'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '通知单号' => 'relation_code',
            '下单时间' => 'order_time',
            '供应商' => 'supplier_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '总退货数' => 'sum_num', //通知退货数、#实际退货数
            '总金额' => 'sum_money',
            '总退货通知数' => 'enotice_num',
            '备注' => 'remark',
            '差异数' => 'diff_num',
            '打印时间' => 'print_time',
            '打印人' => 'print_user',
            '供应商联系人' => 'contact_person',
            '供应商手机' => 'mobile',
            '供应商电话' => 'tel',
            '供应商地址' => 'address',
            '退货类型' => 'record_type_name',
        /* '发货商店' => 'sender_shop_name',

          '发货方联系手机' => 'sender_mobile',//
          '发货地址（无省市区）' => 'sender_addr',
          '发货地址（含省市区）' => 'sender_address',
          '发货邮编' => 'sender_zip',
          '发货街道' => 'senderr_street',
          '发货区/县' => 'senderr_district',
          '发货市' => 'sender_city',//新增
          '发货省' => 'sender_province',//新增
          '发货时间' => 'sender_date',//新增
          '发货打单员' => 'sender_operprint',//新增 */
        ),
        //#、#、#、#商品属性、#生成周期、#商品描述、#、#蓝位号、#库位代码、#库位、
        'detail' => array(
                '序号' => 'sort_num',
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '商品简称' => 'goods_short_name',
                '出厂名称' => 'goods_produce_name',
                '规格1' => 'spec1_name',
                '规格2' => 'spec2_name',
                '标准进价' => 'price',
                '单价' => 'price_1', //需要完善
                '折扣' => 'rebate',
                '批发价' => 'pf_price',
                '实际退货数' => 'num',
                '金额' => 'money',
                '退货通知数' => 'enotice_num',
                '差异数' => 'diff_num',
                '商品条形码' => 'barcode',
                '库位' => 'shelf_code',
                '商品分类' => 'category_name',
                '商品品牌' => 'brand_name',
                '商品季节' => 'season_name',
                '商品年份' => 'year_name',
                '商品重量' => 'weight',
                '吊牌价' => 'dp_price',
            ),
        
);

