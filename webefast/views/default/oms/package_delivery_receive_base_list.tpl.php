<?php
$deliver_date = date("Y-m-d");
render_control('DataTable', 'base_list_table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货日期',
                'field' => 'delivery_date',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货包裹数',
                'field' => 'deliver_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货商品数量',
                'field' => 'goods_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交接成功包裹数',
                'field' => 'success_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交接失败包裹数',
                'field' => 'fail_num',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未交接包裹数',
                'field' => 'blank_num',
                'width' => '120',
                'align' => ''
            ),

        )
    ),
    'dataset' => 'oms/PackageDeliveryReceivedModel::get_count_data',
    'idField' => 'sell_record_id',
//    'init' => 'nodata',
    'export'=> array('id'=>'export_list','conf'=>'package_delivery_receive_list','name'=>'未交接包裹明细','export_type'=>'file'),
    'params' => array(
        'filter' => array("deliver_date_start" => $deliver_date)
    ),
));
?>
