<?php render_control('PageHead', 'head1', array('title'=>'客户列表',));?>
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
<br /><br />
<?php
render_control ( 'DataTable', 'table', array (
		'conf' => array (
                    'list' => array (
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '客户名称',
                            'field' => 'kh_name',
                            'width' => '200',
                            'align' => '' 
                        ),
//                        array (
//                            'type' => 'text',
//                            'show' => 1,
//                            'title' => '所属机构',
//                            'field' => 'user_org_code_name',
//                            'width' => '200',
//                            'align' => '' 
//                        ),
//                        array (
//                            'type' => 'text',
//                            'show' => 1,
//                            'title' => '直属上级',
//                            'field' => 'user_highedrup_name',
//                            'width' => '200',
//                            'align' => '' 
//                        ),
                    ) 
		),
		'dataset' => 'clients/ClientModel::get_clients_info',
                'params' => array('filter'=>array('is_auth'=>$request['is_auth'])),
		'queryBy' => 'searchForm',
		'idField' => 'kh_id',
		'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'kh_id', 'code'=>'kh_id', 'name'=>'kh_name')) ?>
