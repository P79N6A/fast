<?php
$fields = array(
    array('title' => '客户名称', 'type' => 'select_pop', 'field' => 'kh_id', 'select' => 'clients/clientinfo', 'show_scene' => 'add,edit'),
    array('title' => '奇门ID', 'type' => 'input', 'field' => 'qimen_id',),
);
$hidden_fields = array(array('field' => 'wms_config_id'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'servicenter/wms_qimen_config/do_edit', //edit,add,view
    'act_add' => 'servicenter/wms_qimen_config/do_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('kh_id', 'require'),
        array('qimen_id', 'require'),
        // array('shop_code', 'require'),
    ),
));
?>
<script type="text/javascript">
    //表单提交前操作
    form.on('beforesubmit', function () {

    });

    //回调函数
    function after_submit(result, ES_frmId) {
        if (result.status == 1) {
            ui_closePopWindow(ES_frmId);//弹窗
        } else {
            BUI.Message.Alert(result.message, 'error');
        }
    }
</script>

