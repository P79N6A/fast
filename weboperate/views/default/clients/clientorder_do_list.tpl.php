<?php render_control('PageHead', 'head1',array('title'=>'客户单量',));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    'fields' => array (
        array (
            'label' => '客户名称',
            'title' => '客户',
            'type' => 'input',
            'id' => 'clientname' 
        ),
        array (
            'label' => '日期',
            'title' => '日期',
            'type' => 'date',
            'id' => 'datestart',
        ),
        array (
            'type' => 'date',
            'id' => 'dateend',
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
                'field' => 'kh_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'date',
                'show' => 1,
                'title' => '日期',
                'field' => 'oRder_date',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '单量',
                'field' => 'totalnum',
                'width' => '200',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'clients/ClientorderModel::get_client_order_info',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
