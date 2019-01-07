<?php render_control('PageHead', 'head1',
array('title'=>'客户KEY列表',
	'links'=>array(
            //array('url'=>'clients/shopinfo/detail&app_scene=add', 'title'=>'新建店铺', 'is_pop'=>false, 'pop_size'=>'500,400'),
	),
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
            'label' => '客户名称',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'client' 
        ),
        array (
            'label' => 'RDS连接地址',
            'title' => '连接地址',
            'type' => 'input',
            'id' => 'kh_IP' 
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
                'field' => 'kh_id_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '连接地址',
                'field' => 'kh_IP',
                'width' => '200',
                'align' => '' 
            ),    
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '数据库',
                'field' => 'kh_dbname',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '表名',
                'field' => 'kh_tbnme',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '字段名',
                'field' => 'kh_fieldname',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '旧密码',
                'field' => 'kh_oldvalue',
                'width' => '250',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '新密码',
                'field' => 'kh_newvalue',
                'width' => '250',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'clients/ClientopenapiModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
