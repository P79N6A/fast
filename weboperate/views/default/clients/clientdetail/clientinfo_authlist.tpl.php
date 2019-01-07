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
            'title' => '授权产品',
             'type' => 'select',
            'id' => 'product',
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
                'type' => 'input',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'pra_cp_id_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '授权点数',
                'field' => 'pra_authnum',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'pra_startdate',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'input',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'pra_enddate',
                'width' => '100',
                'align' => ''
            ),
            
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '授权状态',
                'field' => 'pra_state',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'products/ProductorderauthModel::get_by_page',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5,'kh_id'=>$request['_id'])),
    'idField' => 'pro_num',
    'CheckSelection' => false,
));
?>
