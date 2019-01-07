<?php render_control('PageHead', 'head1',array('title'=>'平台列表',
    	'links'=>array(
        array('url'=>'basedata/platform/detail&app_scene=add', 'title'=>'新建平台',  'pop_size'=>'500,400'),
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
            'label' => '关键字',
            'title' => '平台代码/平台名称',
            'type' => 'input',
            'id' => 'keyword' 
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
                'title' => '平台LOGO',
                'field' => 'pt_logo',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台代码',
                'field' => 'pt_code',
                'width' => '80',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台名称',
                'field' => 'pt_name',
                'width' => '100',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '平台官网URL',
                'field' => 'pt_offurl',
                'width' => '180',
                'align' => ''    
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '技术平台URL',
                'field' => 'pt_techurl',
                'width' => '180',
                'align' => ''    
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '服务市场URL',
                'field' => 'pt_serurl',
                'width' => '180',
                'align' => ''    
            ),
            array (
                'type' => 'checkbox',
                'show' => 1,
                'title' => '支持状态',
                'field' => 'pt_state',
                'width' => '80',
                'align' => '',
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (
                        array('id'=>'view', 'title' => '查看', 
                		'act'=>'basedata/platform/detail&app_scene=view', 'show_name'=>'查看平台'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'basedata/platform/detail&app_scene=edit', 'show_name'=>'编辑平台'),
                ),
            )
        ) 
    ),
    'dataset' => 'basedata/PlatformModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pt_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
