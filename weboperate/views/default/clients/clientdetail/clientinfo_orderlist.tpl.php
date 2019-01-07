<?php
render_control('PageHead', 'head1', array('title' => '主机信息',
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '产品',
            'title' => '订购产品',
             'type' => 'select',
            'id' => 'cp_id',
             'data'=>ds_get_select('chanpin',1)
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '客户名称',
//                'field' => 'pro_kh_id_name',
//                'width' => '90',
//                'align' => ''
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售渠道',
                'field' => 'pro_channel_id_name',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pro_cp_id_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '营销类型',
                'field' => 'pro_st_id_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'pro_pay_status',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '审核状态',
                'field' => 'pro_check_status',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'market/ProductorderModel::get_porder_info',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5,'kh_id'=>$request['_id'])),
    'idField' => 'pro_num',
    'CheckSelection' => false,
));
?>
