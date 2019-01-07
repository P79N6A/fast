<?php render_control('Form', 'form1', array(
	'conf'=>array(
		'fields'=>array(
			array('title'=>'机构ID', 'type'=>'input', 'field'=>'org_id'),
                        array('title'=>'机构代码', 'type'=>'input', 'field'=>'org_code'),
			array('title'=>'机构名称', 'type'=>'input', 'field'=>'org_name'),
                        array('title'=>'上级机构', 'type'=>'input', 'field'=>'org_parent_id_name'),
                        array('title'=>'机构级别', 'type'=>'select', 'field'=>'org_level','data'=>ds_get_select_by_field('orglevel')),
			array('title'=>'是否有效', 'type'=>'checkbox', 'field'=>'org_active', 'show_scene'=>'view'),
		), 
		'hidden_fields'=>array(array('field'=>'org_id'), array('field'=>'org_code'),), 
	), 
	'buttons'=>array(
			array('label'=>'提交', 'type'=>'submit'),
			array('label'=>'重置', 'type'=>'reset'),
	),
	'data'=>$response['data'],
)); ?>