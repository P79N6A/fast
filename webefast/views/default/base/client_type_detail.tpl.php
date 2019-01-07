<?php

render_control('PageHead', 'head1', array(
    'title' => isset($app['title']) ? $app['title'] : '编辑客户类型',
    'links' => array('base/client_type/do_list' => '客户类型列表',
    )
));
?>
<?php
render_control('Form', 'record_type_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '类型代码', 'type' => 'input', 'field' => 'record_type_code','edit_scene'=>'add','remark' => '一旦保存不能修改！'),
            array('title' => '类型名称', 'type' => 'input', 'field' => 'record_type_name'),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(
            array('field' => 'record_type_id'),
            array('field' => 'record_type_property'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/client_type/do_edit', // edit,add,view
    'act_add' => 'base/client_type/do_add',
    'data' => $response['data'],
    'rules'=>array(
		array('record_type_code', 'require'),
		array('record_type_name', 'require'),
	),
));
?>