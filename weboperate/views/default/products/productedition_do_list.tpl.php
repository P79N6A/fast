<?php render_control('PageHead', 'head1',
array('title'=>'产品系统版本信息',
	'links'=>array(
        array('url'=>'products/productedition/detail&app_scene=add', 'title'=>'新建产品系统版本', 'is_pop'=>false, 'pop_size'=>'500,400'),
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
            'title' => '版本代码/版本名称',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '所属产品',
            'title' => '所属产品',
            'type' => 'select',
            'id' => 'pv_cp_id',
            'data'=>ds_get_select('chanpin',1)
        ),
        array (
            'label' => '版本类型',
            'title' => '版本类型',
            'type' => 'select',
            'id' => 'pv_type',
            'data'=>ds_get_select_by_field('cpversion')
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
                'title' => '版本代码',
                'field' => 'pv_code',
                'width' => '150',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '版本名称',
                'field' => 'pv_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '产品',
                'field' => 'pv_cp_id_name',
                'width' => '120',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '版本号',
                'field' => 'pv_bh',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '版本日期',
                'field' => 'pv_rq',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '发布人',
                'field' => 'pv_fbr_name',
                'width' => '100',
                'align' => '' 
            ),
            array (
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array (
                	array('id'=>'view', 'title' => '详细', 
                		'act'=>'products/productedition/detail&app_scene=view', 'show_name'=>'产品系统版本详情'),
                        array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'products/productedition/detail&app_scene=edit', 'show_name'=>'编辑产品系统版本', 
                		'show_cond'=>'obj.is_buildin != 1'),
                ),
            )
        ) 
    ),
    'dataset' => 'products/ProductEditionModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'pv_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
