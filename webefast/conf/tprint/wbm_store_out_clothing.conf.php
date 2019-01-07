<?php

return array(
    'template' => 'report',
    'data_source'=>array(
       'model'=> 'wbm/StoreOutRecordModel',
       'method'=> 'print_data_default_clothing',
        ),
    'record' => array(
            '单据编号' => 'record_code',
            '原单号' => 'init_code',
            '通知单号' => 'relation_code',
            '下单时间' => 'order_time',
            '分销商' => 'distributor_name',
            '仓库' => 'store_name',
            '折扣' => 'rebate',
            '业务日期' => 'record_time',
            '总出库数' => 'num',
            '总金额' => 'money',
            '备注' => 'remark',
            '总通知数' => 'enotice_num',
            '快递单号' => 'express',
            '配送方式' => 'express_name',
            '运费' => 'express_money',
            '联系人' => 'name',
            '联系电话' => 'tel',
            '地址' => 'address',
            //  '总通知金额' => 'enotice_money',
            '总差异数' => 'diff_num',
            '仓库寄件人' => 'shop_contact_person',
            '仓库联系人' => 'contact_person',
            '仓库联系电话' => 'sender_phone',
            '仓库店铺留言' => 'message',
            '仓库店铺留言2' => 'message2',
            '打印时间' => 'print_time',
            '打印人' => 'print_user_name',
             '页码' => 'page_no',


        ),
        'detail' => array(
                '商品名称' => 'goods_name',
                '商品编码' => 'goods_code',
                '颜色' => 'spec1_name',
                '尺码' => 'spec2_name',
                '库位名称(库位代码)' => 'shelf_name',
                '分类' => 'category_name',
            ),
);






