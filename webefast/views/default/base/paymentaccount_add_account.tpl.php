<?php 
    $fields = array(
        array('title' => '账户名称', 'type' => 'input', 'field' => 'account_name'),
        array('title' => '开户银行', 'type' => 'input', 'field' => 'account_bank'),
        array('title' => '银行账号', 'type' => 'input', 'field' => 'bank_code'),
    );
    $hidden_fields = array(array('field' => 'id'));
    render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ), //edit,add,view
    'act_add' => 'base/paymentaccount/add',
    'act_edit' => 'base/paymentaccount/do_edit', //edit,add,view
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('record_time', 'require'),
        array('store_code', 'require'),
    ),
    ));   
    ?>
<script type="text/javascript">
    function after_submit(result,ES_frmId){     
        var type = (result.status == 1) ? 'success' : 'error';
        if (type != 'success') {
                   BUI.Message.Alert(result.message, type);
               }else{
            BUI.Message.Alert('添加成功', 'success');
            ui_closePopWindow(ES_frmId); 
          }
    }
</script>