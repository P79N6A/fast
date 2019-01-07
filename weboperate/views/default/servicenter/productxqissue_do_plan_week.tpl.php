<?php
$fields = array(
    array('title' => '提单编号', 'type' => 'input', 'field' => 'xqsue_number', 'edit_scene' => 'add'),
    array('title' => '计划周次', 'type' => 'input', 'field' => 'xqsue_plan_week', 'edit_scene' => 'edit'),
);
$hidden_fields = array(array('field' => 'xqsue_number'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'servicenter/productxqissue/do_xqissue_edit_plan_week', //edit,add,view
    //'act_add' => 'servicenter/productxqissue/do_xqissue_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        //array('xqsue_plan_week', 'require'),
    ),
));
?>

<script type="text/javascript">
    //表单提交前操作
    form.on('beforesubmit', function () {
    });

    //回调函数
    function after_submit(result, ES_frmId) {
        //关闭弹窗
        ui_closePopWindow(ES_frmId);
    }


</script>

