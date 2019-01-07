 <?php render_control('PageHead', 'head1',
array('title'=>'RDS信息',
	'links'=>array(
        array('url'=>'clients/alirds/detail&app_scene=add', 'title'=>'新建RDS',  'pop_size'=>'500,400'),
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
            'label' => 'RDS实例名',
            'type' => 'input',
            'id' => 'dbname',
            'title' => '实例名',
        ),
        array (
            'label' => '云服务商',
            'type' => 'select',
            'id' => 'dbtype',
            'data'=>ds_get_select('host_cloud')
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
                'title' => 'RDS用户',
                'field' => 'rds_user',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS连接',
                'field' => 'rds_link',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => 'RDS实例',
                'field' => 'rds_dbname',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '云服务商',
                'field' => 'rds_dbtype_name',
                'width' => '80',
                'align' => '', 
            ),
            array (
                'type' => 'input',
                'show' => 1,
                'title' => '到期时间',
                'field' => 'rds_endtime',
                'width' => '90',
                'align' => '' 
            ),
        ) 
    ),
    'dataset' => 'clients/ClientModel::get_rds_info',
    'queryBy' => 'searchForm',
    'params' => array('filter'=>array('page_size'=>5,'kh_id'=>$request['_id'])),
    'idField' => 'rds_id',
    'CheckSelection'=>false,
) );
?>
