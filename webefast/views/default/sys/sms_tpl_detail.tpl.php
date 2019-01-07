<?php render_control('PageHead', 'head1',
    array('title' => isset($app['title']) ? $app['title'] : '短信发送详情',
        'links' => array(
            'sys/sms_tpl/do_list' => '短信发送列表',
        )
    ));
    //echo '<hr/>$response<xmp>'.var_export($response,true).'</xmp>';
    ?>

<?php
//$form_conf = require_conf('sys/sms');
//$form_rule = $form_conf['form_rule_sms_queue'];

render_control('Form', 'payment_form', array('conf' => array('fields' => array(
    array('title' => '短信模板类型', 'type' => 'select', 'field' => 'tpl_type','data'=>array(
    array('确认订单短信通知','确认订单短信通知'),array('通知配货短信通知','通知配货短信通知'),array('发货成功短信通知','发货成功短信通知'),
    array('客户确认收货短信通知','客户确认收货短信通知'),array('派件短信通知','派件短信通知'),array('签收短信通知','签收短信通知'),
    array('会员群发短信模板','会员群发短信模板'),array('','请选择')
    ),'edit_scene'=>'add'),

    array('title' => '短信模板名称', 'type' => 'input', 'field' => 'tpl_name'),
    array('title' => '是否启用', 'type' => 'checkbox', 'field' => 'is_active'),
    array('title' => '模板内容', 'type' => 'textarea', 'field' => 'sms_info',
    'remark' => '<br/><span id="text">（注：模板变量定义如下 {$user_id} - 会员登录名、{$user_name} - 会员昵称、{$order_sn} - 订单号、{$consignee} - 收货人、{$exmc} - 快递公司、{$invoice_no} - 快递单号、{$now_time} - 当前时间、{$goods_sns} - 商品货号串）</span>'),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
),
    'hidden_fields' => array(array('field' => 'id')),
),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'sys/sms_tpl/do_edit', // edit,add,view
    'act_add' => 'sys/sms_tpl/do_add',
    'data' => $response['data'],
    'rules' => array(
		array('tpl_type', 'require'),
		array('tpl_name', 'require'),
	)
));

?>

<script>

$(function(){
	$(".control-label").css("width","110px");
	$("textarea").css("width","400px");
})

</script>