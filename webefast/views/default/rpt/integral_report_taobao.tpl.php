<?php
render_control('DataTable', 'taobao_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'tid',
                'width' => '180',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易结束时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应收款',
                'field' => 'payment',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '红包',
                'field' => 'coupon_fee_percent',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '集分宝',
                'field' => 'alipay_point_percent',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '购物券',
                'field' => 'discount_fee',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/SellReportModel::get_taobao_integral_data',
    //'params' => array('filter' => array('user_id' => $response['user_id'])),
    //  'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    // 'export' => array('id' => 'exprot_list', 'conf' => 'purchase_analyse_view_list', 'name' => '采购分析明细'),
    'init' => 'nodata',
));
?>
