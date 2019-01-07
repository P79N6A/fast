<?php render_control('PageHead', 'head1',
array('title'=>'参数列表',
	'links'=>array(
		'sys/config/detail&app_scene=add'=>'新增参数'
	)
));?>
<?php
render_control ( 'SearchForm', 'searchForm', array (
		'cmd' => array (
				'label' => '查询',
				'id' => 'btn-search' 
		),
		'fields' => array (
				array (
						'label' => '关键字',
						'title' => '编码/名称',
						'type' => 'input',
						'id' => 'keyword' 
				),
				array (
						'label' => '分组',
						'type' => 'select',
						'id' => 'group',
						'data' => $response['group_list']
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
								'title' => '编号',
								'field' => 'code',
								'width' => '150',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '名称',
								'field' => 'title',
								'width' => '200',
								'align' => '' 
						),
						array (
								'type' => 'text',
								'show' => 1,
								'title' => '描述',
								'field' => 'remarks',
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
						array (
								'type' => 'button',
								'show' => 1,
								'title' => '操作',
								'field' => '_operate',
								'width' => '150',
								'align' => '',
								'buttons' => array (
									//array('id'=>'audit', 'title' => '审核', ),
									array('id'=>'view', 'title' => '查看', 'act'=>'sys/sysconfig/detail&app_scene=view', 'show_name'=>'查看参数'),
									array('id'=>'edit', 'title' => '编辑', 'act'=>'sys/sysconfig/detail&app_scene=edit', 'show_name'=>'编辑参数', 'show_cond'=>'obj.is_buildin != 1'),
								),
						)
				) 
		),
		'dataset' => 'sys/ConfigModel::get_by_page',
		'queryBy' => 'searchForm',
		'idField' => 'config_id',
) );
?>