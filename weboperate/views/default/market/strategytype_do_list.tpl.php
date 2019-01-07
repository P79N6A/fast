<?php render_control('PageHead', 'head1',
array('title'=>'营销策略类型',
	'links'=>array(
        array('url'=>'market/strategytype/detail&app_scene=add', 'title'=>'新增类型', 'is_pop'=>true, 'pop_size'=>'500,400'),
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
            'title' => '类型代码/名称',
            'type' => 'input',
            'id' => 'keyword' 
        ),
        array (
            'label' => '内置类型',
            'type' => 'select',
            'id' => 'is_buildin',
            'data' => array_from_dict ( array (
                            '' => '请选择',
                            '1' => '是',
                            '0' => '否' 
            ) )
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
                'title' => '策略类型代码',
                'field' => 'st_code',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '策略类型名称',
                'field' => 'st_name',
                'width' => '200',
                'align' => '' 
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'st_remark',
                'width' => '300',
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
                        array('id'=>'view', 'title' => '查看', 'act'=>'pop:market/strategytype/detail&app_scene=view', 'show_name'=>'查看营销策略类型'),
                	array('id'=>'edit', 'title' => '编辑', 
                		'act'=>'pop:market/strategytype/detail&app_scene=edit', 'show_name'=>'编辑营销策略类型', 
                		'show_cond'=>'obj.is_buildin != 1'),
                ),
            )
        ) 
    ),
    'dataset' => 'market/StrategyTypeModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'st_id',
    //'RowNumber'=>true,
    'CheckSelection'=>true,
) );
?>
<script type="text/javascript">

</script>