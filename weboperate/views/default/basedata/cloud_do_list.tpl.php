<?php render_control('PageHead', 'head1',array('title'=>'供应商列表',
    	'links'=>array(
        array('url'=>'basedata/cloud/detail&app_scene=add', 'title'=>'新建供应商',  'pop_size'=>'500,400'),
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
            'label' => '名称',
            'title' => '云服务商名称',
            'type' => 'input',
            'id' => 'could_name' 
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
                'title' => '服务商名称',
                'field' => 'cd_name',
                'width' => '150',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务商官网',
                'field' => 'cd_official',
                'width' => '200',
                'align' => ''    
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务商描述',
                'field' => 'cd_note',
                'width' => '200',
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
                		'act'=>'basedata/cloud/detail&app_scene=view', 'show_name'=>'查看平台'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'basedata/cloud/detail&app_scene=edit', 'show_name'=>'编辑平台'),
                ),
            )
        ) 
    ),
    'dataset' => 'basedata/CloudModel::get_could_info',
    'queryBy' => 'searchForm',
    'idField' => 'cd_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
