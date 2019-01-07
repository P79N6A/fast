<?php

render_control('PageHead', 'head1', array(
    'title' => isset($app['title']) ? $app['title'] : '编辑库存调整类型',
    'links' => array('base/store_adjust_type/do_list' => '库存调整类型列表',
    )
));
?>
<?php
render_control('Form', 'record_type_form', array(
    'conf' => array(
        'fields' => array(
            array('title' => '类型代码', 'type' => 'input', 'field' => 'record_type_code','remark'=>'一旦保存不能修改!', 'edit_scene'=>'add'),
            array('title' => '类型名称', 'type' => 'input', 'field' => 'record_type_name'),
            array('title' => '单据类型','type' => 'select','field' => 'record_type_property','data' => array(array('','请选择'),array('0', '采购进货'),array('1','采购退货'),array('2','批发发货'),array('3','批发退货'),array('8','库存调整')),),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(
            array('field' => 'record_type_id'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'base/record_type/do_edit', // edit,add,view
    'act_add' => 'base/record_type/do_add',
    'data' => $response['data'],
    'rules'=>array(
		array('record_type_code', 'require'),
		array('record_type_name', 'require'),
	),
));
?>
<script type="text/javascript">
    if ($("#app_scene").val() == 'edit') {
        $("#record_type_property").attr("disabled", "disabled");
    }
</script>
