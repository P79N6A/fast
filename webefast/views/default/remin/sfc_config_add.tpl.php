<style type="text/css">
    .form-horizontal .control-label{width:100px;}
    body{overflow-y: hidden;}
</style>
<?php
render_control('Form', 'form16', array(
    'conf' => array(
        'fields' => array(
            array('title' => 'appKey', 'type' => 'input', 'field' => 'sfckey',),
            array('title' => 'token', 'type' => 'input', 'field' => 'token'),
            array('title' => 'userId', 'type' => 'input', 'field' => 'sfcid'),
        ),
        'hidden_fields' => array(array('field' => 'express_id', 'value' => $request['express_id']), array('field' => 'express_code', 'value' => $request['express_code']), array('field'=>'pid', 'value' => $request['_id']))
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'remin/sfc/do_edit',
    'act_add' => 'remin/sfc/do_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('sfckey', 'require'),
        array('token', 'require'),
        array('sfcid', 'require'),
    ),
));
?>
<script>
    function after_submit(result, ES_frmId) {
        var type = result.status == 1 ? 'success' : 'error';
        BUI.Message.Alert(result.message, function () {
            if (result.status == 1) {
                parent._sfcreload_page();
                ui_closePopWindow(ES_frmId);
            }
        }, type);
    }
</script>