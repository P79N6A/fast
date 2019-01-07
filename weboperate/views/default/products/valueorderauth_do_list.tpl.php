<?php render_control('PageHead', 'head1',
array('title'=>'增值服务授权列表',
	'ref_table'=>'table'
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '产品',
            'title' => '产品',
            'type' => 'select',
            'id' => 'cp_id',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '客户名称',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'kh_name',
        ),
        array(
            'label' => '增值服务',
            'type' => 'select_pop',
            'field' => 'vra_server_id',
            'select' => 'market/valueserver',
        ),
    ) 
) );
?>
<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'vra_kh_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品名称',
                'field' => 'vra_cp_id_name',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'vra_pt_version',
                'width' => '120',
                'align' => '',
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '增值服务',
                'field' => 'vra_server_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '开始时间',
                'field' => 'vra_startdate',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '结束时间',
                'field' => 'vra_enddate',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '授权状态',
                'field' => 'vra_state',
                'width' => '70',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                        array('id'=>'view', 'title' => '查看', 
                		'act'=>'products/valueorderauth/detail&app_scene=view', 'show_name'=>'查看增值授权',), 
                ),
            )
        ) 
    ),
    'dataset' => 'products/ValueorderauthModel::get_value_auth',
    'queryBy' => 'searchForm',
    'idField' => 'vra_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
