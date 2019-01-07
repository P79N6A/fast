<?php render_control('PageHead', 'head1',
array('title'=>'产品补丁列表',
	'links'=>array(
        array('url'=>'products/productpatch/detail&app_scene=add', 'title'=>'新建补丁', 'is_pop'=>false, 'pop_size'=>'500,400'),
               array('url'=>'upgrade/upgrade/do_list', 'title'=>'补丁升级',),
            array('url'=>'patch/patch/do_list', 'title'=>'产品补丁升级',),
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
            'title' => '版本编号/补丁编号',
            'type' => 'input',
            'id' => 'keyword' 
        )
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
                'title' => '产品名称',
                'field' => 'cp_id_name',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '版本编号',
                'field' => 'version_no',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '补丁编号',
                'field' => 'version_patch',
                'width' => '150',
                'align' => '' 
            ),
            
//            array (
//                'type' => 'checkbox',
//                'show' => 1,
//                'title' => '包含SQL',
//                'field' => 'is_sql',
//                'width' => '80',
//                'align' => '' 
//            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '补丁包路径',
//                'field' => 'version_file_path',
//                'width' => '100',
//                'align' => '' 
//            ),
//            array (
//                'type' => 'text',
//                'show' => 1,
//                'title' => '基础补丁路径',
//                'field' => 'upgrade_patch',
//                'width' => '100',
//                'align' => '' 
//            ),
            
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'is_exec',
                'width' => '100',
                'align' => '',
                'format'=>array('type'=>'map', 'value'=>ds_get_field('patch_status'))
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
                		'act'=>'products/productpatch/detail&app_scene=view', 'show_name'=>'补丁详情'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'products/productpatch/detail&app_scene=edit', 'show_name'=>'编辑补丁信息', 
                		'show_cond'=>'obj.is_buildin != 1'),
                ),
            )
        ) 
    ),
    'dataset' => 'products/ProductpatchModel::get_patch_info',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
