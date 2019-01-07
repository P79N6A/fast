
<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'代码', 'type'=>'input', 'field'=>'supplier_type_code', 'remark'=>'一旦保存不能修改!', 'edit_scene'=>'add'),
			array('title'=>'名称', 'type'=>'input', 'field'=>'supplier_type_name'),
		),
		'hidden_fields'=>array(array('field'=>'supplier_type_id')),
	),
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'act_edit'=>'base/supplier_type/do_edit', //edit,add,view
	'act_add'=>'base/supplier_type/do_add',
	'data'=>$response['data'],

	'rules'=>array(
		array('supplier_type_code', 'require'),
		array('supplier_type_name','require'),
	),
)); ?>



