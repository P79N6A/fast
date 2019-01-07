<?php render_control('PageHead', 'head1', array('title'=>'管理主机列表',));?>
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
                'title' => '客户名称',
                'type' => 'input',
                'id' => 'client_name' 
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
                            'title' => '主机IP地址',
                            'field' => 'vm_id_name',
                            'width' => '200',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '到期时间',
                            'field' => 'vm_endtime',
                            'width' => '200',
                            'align' => '' 
                        ),
                    ) 
		),
		'dataset' => 'products/VmanageModel::get_vm_main',
		'queryBy' => 'searchForm',
		'idField' => 'vm_id',
		'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'vm_id', 'code'=>'vm_id', 'name'=>'vm_id_name')) ?>
