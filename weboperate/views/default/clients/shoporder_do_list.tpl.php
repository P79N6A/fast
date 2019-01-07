<?php render_control('PageHead', 'head1',array('title'=>'店铺单量',));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
    'cmd' => array (
        'label' => '查询',
        'title' => '查询',
        'id' => 'btn-search' 
    ),
    
    'fields' => array (
        array (
            'label' => '店铺名称',
            'title' => '店铺',
            'type' => 'input',
            'id' => 'shopname' 
        ),
        array (
            'label' => '所属客户',
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
                'title' => '店铺名称',
                'field' => 'sd_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '所属客户',
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
                'title' => '总单量',
                'field' => 'oday_order',
                'width' => '200',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'clients/ShoporderModel::get_shop_order_info',
    'queryBy' => 'searchForm',
    'idField' => 'sd_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
