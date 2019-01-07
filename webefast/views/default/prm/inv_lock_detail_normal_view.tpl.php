<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据类型',
                'field' => 'order_type',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '140',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据相关信息',
                'field' => 'record_code_info',
                'width' => '190',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建日期',
                'field' => 'create_time',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实物锁定（占用数）',
                'field' => 'num',
                'width' => '65',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'prm/InvRecordModel::inv_lock_detail',
    'queryBy' => 'searchForm',
    'idField' => 'goods_inv_id',
    'init' => 'nodata',
    'ColumnResize' => true,
    'params' => array(
        'filter' => array(
            'mode' => 'normal_mode',
        ),
    ),
));
