<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '套餐子商品条码',
                'field' => 'barcode',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '可用库存',
                'field' => 'inv_num',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '参与套餐条形码',
                'field' => 'name_list',
                'width' => '500',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'crm/ActivityGoodsModel::get_child_barcode',
    'idField' => 'activity_id',
    'params' => array(
        'filter' => array('activity_code' => $response['activity_code'], 'shop_code' => $response['shop']),
    ),
));
?>