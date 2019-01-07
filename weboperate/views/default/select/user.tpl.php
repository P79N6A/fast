<?php render_control('PageHead', 'head1', array('title'=>'用户列表',));?>
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
                'title' => '登录名/真实名',
                'type' => 'input',
                'id' => 'keyword' 
            ),
            array (
                'label' => '有效',
                'title' => '是否有效',
                'type' => 'select',
                'id' => 'user_active',
                'data'=>ds_get_select_by_field('boolstatus')
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
                            'title' => '登录名',
                            'field' => 'user_code',
                            'width' => '100',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '真实名',
                            'field' => 'user_name',
                            'width' => '200',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '所属机构',
                            'field' => 'user_org_code_name',
                            'width' => '200',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'text',
                            'show' => 1,
                            'title' => '直属上级',
                            'field' => 'user_highedrup_name',
                            'width' => '200',
                            'align' => '' 
                        ),
                        array (
                            'type' => 'checkbox',
                            'show' => 1,
                            'title' => '是否有效',
                            'field' => 'user_active',
                            'width' => '100',
                            'align' => '' 
                        ),
                    ) 
		),
		'dataset' => 'sys/UserModel_ex::get_by_page',
		'queryBy' => 'searchForm',
		'idField' => 'user_id',
		'CheckSelection'=>isset($request['multi']) && $request['multi']= 1 ? true : false,
) );
?>
<?php echo_selectwindow_js($request, 'table', array('id'=>'user_id', 'code'=>'user_code', 'name'=>'user_name')) ?>
