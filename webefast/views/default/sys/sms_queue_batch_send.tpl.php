<?php render_control('PageHead', 'head1',
    array('title' => '批量发送短信',
        'links' => array(
    	   array('url' => 'sys/sms_queue/do_list', 'title' => '导入号码'),	
           array('url' => 'sys/sms_queue/do_list', 'title' => '短信发送状态'),
        )
    ));
    ?>

<?php
//$form_conf = require_conf('sys/sms');
//$form_rule = $form_conf['form_rule_sms_queue'];

render_control('Form', 'payment_form', array('conf' => array('fields' => array(

    array('title' => '信息接收人', 'type' => 'textarea', 'field' => 'tel_list',
    'remark' => '<br/><span style = "color:red;">多个号码请用“，”分割！</span>'),
    array('title' => '信息内容', 'type' => 'textarea', 'field' => 'msg_content'),
    )),
    'buttons' => array(
        array('label' => '发送', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_add' => 'sys/sms_queue/do_batch_send',
//    'data' => $response['data'],
//    'rules' => array(
//		array('tpl_type', 'require'),
//		array('tpl_name', 'require'),
//	)
));

?>

<script>

$(function(){
	$(".control-label").css("width","110px");
	$("textarea").css("width","400px");
	$("#submit").css("margin-left","200px");
})

</script>
