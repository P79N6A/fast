<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台交易号',
                'field' => 'tid',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '350',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_nick',
                'width' => '350',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'order_money',
                'width' => '80',
                'align' => ''
            ),

        ),
    ),
    'dataset' => 'oms/ApiOrderModel::get_by_test_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'api_order_list', 'name' => '平台订单', 'export_type' => 'file'),
    'idField' => 'tid',
    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '200', 'field' => 'title'),
            array('title' => '商品属性', 'type' => 'text', 'width' => '200', 'field' => 'sku_properties'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'goods_barcode'),
            array('title' => '数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
            array('title' => '金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift' ,'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_tid&app_fmt=json'),
        'params' => 'tid',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_td_list_cascade_data'),//查询展开详情的方法
            'detail_param' => 'tid',//查询展开详情的使用的参数
        ),
    ),
    'params'=>array('filter'=>array('search_type'=>'test_gift_stategy')),
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'close_window',
    )
));