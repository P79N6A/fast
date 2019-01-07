<?php render_control('PageHead', 'head1',
array('title'=>'店铺信息',
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
            'label' => '店铺名称',
            'title' => '店铺',
            'type' => 'input',
            'id' => 'shopname' 
        ),
        array (
            'label' => '平台类型',
            'title' => '店铺',
            'type' => 'select',
            'id' => 'platform' ,
            'data'=>ds_get_select('shop_platform',2)
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
                'title' => '平台类型',
                'field' => 'sd_pt_id_name',
                'width' => '100',
                'align' => '' 
            ), 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '代理名称',
                'field' => 'sd_agent',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务负责人',
                'field' => 'sd_servicer_name',
                'width' => '150',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'clients/ClientModel::get_shop_info',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5,'kh_id'=>$request['_id'])),
    'idField' => 'sd_id',
    'CheckSelection'=>false,
) );
?>
