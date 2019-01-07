
<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '编辑短信供应商',
        'links' => array('sys/sms_supplier/do_list' => '短信供应商列表',
        )
    ));
?>
<?php
$form_conf = require_conf('sys/sms');
$form_rule = $form_conf['form_rule_sms_supplier'];

render_control('Form', 'payment_form', array('conf' => array('fields' => array(
    array('title' => '供应商代码', 'type' => 'input', 'field' => 'supplier_code','edit_scene'=>'add'),
    array('title' => '供应商名称', 'type' => 'input', 'field' => 'supplier_name'),
    array('title' => '结算单价', 'type' => 'input', 'field' => 'unit_price', 'remark' => '（元/条，默认0元/条）'),
    array('title' => '发送服务器地址', 'type' => 'input', 'field' => 'server_ip'),
    array('title' => '发送端口', 'type' => 'input', 'field' => 'server_port'),
    array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'supplier_id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'sys/sms_supplier/do_edit', // edit,add,view
    'act_add' => 'sys/sms_supplier/do_add',
    'data' => $response['data'],
    'rules' => array(
		array('supplier_code', 'require'),
		array('supplier_name', 'require'),
		array('server_ip', 'require'),
		array('server_port', 'require'),
	)
));

?>

<script>

$(function(){
	$(".control-label").css("width","110px");
	$("#unit_price").keyup(function(){
		if(isNaN(this.value)){
			alert('只能输入数字！');this.value=''
		}
	});
	
})

</script>