<?php
render_control ( 'SearchForm', 'searchForm', array (
		'cmd' => array (
				'label' => '查询',
				'id' => 'btn-search' 
		),
		'fields' => array (
				array (
						'label' => '关键字',
						'title' => '代码/名称',
						'type' => 'input',
						'id' => 'keyword' 
				),
		) 
) );
?>
<table>
	<tr>
		<td style="width: 200px; height: 300px; overflow: auto">
<?php
render_control ( 'DataTable', 'table', array (
		'conf' => array (
				'list' => array (
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '代码',
								'field' => 'role_code',
								'width' => '100',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '名称',
								'field' => 'role_name',
								'width' => '100',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '描述',
								'field' => 'role_desc',
								'width' => '200',
								'align' => '',
								'format' => array (
										'type' => 'truncate',
										'value' => 20 
								) 
						),
						array (
								'type' => 'checkbox',
								'show' => 1,
								'title' => '内置',
								'field' => 'is_buildin',
								'width' => '50',
								'align' => '',
								'format' => array (
										'type' => 'map_checked' 
								) 
						),
				) 
		),
		'dataset' => array('sys/RoleModel::get_by_page'),
		'queryBy' => 'searchForm',
		'idField' => 'role_id',
) );
?>
</td>
<td>
	<input type="button" value="->" /> <br />
	<input type="button" value="<-" />
</td>
<td>
<?php
render_control ( 'DataTable', 'DataTable2', array (
		'conf' => array (
				'list' => array (
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '代码',
								'field' => 'role_code',
								'width' => '100',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '名称',
								'field' => 'role_name',
								'width' => '100',
								'align' => '' 
						),
				) 
		),
		'dataset' => array('sys/UserModel::get_role_list', array($request['user_id'])),
		'queryBy' => 'searchForm',
		'idField' => 'user_role_id',
) );
?>
</td>
</tr>
</table>