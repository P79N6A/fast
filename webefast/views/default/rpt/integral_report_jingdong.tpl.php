<?php
render_control('DataTable', 'jingdong_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'order_id',
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
                'width' => '160',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应收款',
                'field' => 'order_payment',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '京东券',
                'field' => 'jingdong_coupon',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '京豆',
                'field' => 'jingdong_bean',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付有礼',
                'field' => 'pay_courtesy',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '京东余额',
                'field' => 'balance_used',
                'width' => '120',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/SellReportModel::get_jingdong_integral_data',
    //'params' => array('filter' => array('user_id' => $response['user_id'])),
 //   'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
   // 'export' => array('id' => 'exprot_list', 'conf' => 'purchase_analyse_view_list', 'name' => '采购分析明细'),
    'init'=>'nodata',
));
?>
