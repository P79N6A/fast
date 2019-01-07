<?php render_control('PageHead', 'head1', array('title'=>'产品模块列表',));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
        'cmd' => array (
            'label' => '查询',
            'label' => '查询',
            'id' => 'btn-search' 
        ),
        'fields' => array (
            array (
                'label' => '关键字',
                'title' => '模块名称',
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
                            'title' => '模块名称',
                            'field' => 'pm_name',
                            'width' => '100',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '英文名称',
                            'field' => 'pm_en_name',
                            'width' => '150',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '简称',
                            'field' => 'pm_jc',
                            'width' => '150',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '描述',
                            'field' => 'pm_memo',
                            'width' => '150',
                            'align' => '' 
                        ),
                    ) 
		),
                'dataset' => 'products/ProductmkModel::get_by_page',
                'params' => array('filter'=>array('cpid'=>$request['cpid'])),
                'queryBy' => 'searchForm',
                'idField' => 'pm_id',
		'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'pm_id', 'code'=>'pm_id', 'name'=>'pm_name')) ?>
