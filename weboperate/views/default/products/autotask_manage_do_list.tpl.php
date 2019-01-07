<?php render_control('PageHead', 'head1',
array('title'=>'自动服务',
	'links'=>array(
        array('url'=>'products/autotask_manage/detail&app_scene=add', 'title'=>'新建自动服务', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'label' => '服务IP',
            'title' => 'IP地址服务',
            'type' => 'input',
            'id' => 'ipaddr' 
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
                'title' => '所属主机IP',
                'field' => 'asa_vm_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '所属RDS地址',
                'field' => 'asa_rds_id_name',
                'width' => '200',
                'align' => '' 
            ),
           array (
                'type' => 'text',
                'show' => 1,
                'title' => '关联产品',
                'field' => 'asa_cp_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品版本',
                'field' => 'asa_product_version',
                'width' => '150',
                'align' => '' ,
                'format_js'=>array('type'=>'map', 'value'=>ds_get_field('product_version'))
            ), 
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '系统版本',
                'field' => 'asa_cp_version_id_name',
                'width' => '150',
                'align' => '',
            ),
            
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '详细', 
                		'act'=>'products/autotask_manage/detail&app_scene=view', 'show_name'=>'详细信息'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'products/autotask_manage/detail&app_scene=edit', 'show_name'=>'编辑主机', 
                		'show_cond'=>'obj.is_buildin != 1'),
                ),
            )
        ) 
    ),
    'dataset' => 'products/AutomanageModel::get_autotask_info',
    'queryBy' => 'searchForm',
    'idField' => 'asa_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
