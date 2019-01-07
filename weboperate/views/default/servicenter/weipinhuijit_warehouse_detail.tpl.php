<?php
$fields = array(
    array('title' => '序号', 'type' => 'input', 'field' => 'warehouse_no',),
    array('title' => '仓库编码', 'type' => 'input', 'field' => 'warehouse_code',),
    array('title' => '仓库名称', 'type' => 'input', 'field' => 'warehouse_name',),
    array('title' => '描述', 'type' => 'input', 'field' => 'desc',),
);
$hidden_fields = array(array('field' => 'warehouse_id'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
  //  'act_edit' => 'stm/stock_lock_record/do_edit', //edit,add,view
    'act_add' => 'servicenter/weipinhuijit_warehouse/do_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('warehouse_code', 'require'),
        array('warehouse_name', 'require'),
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
            BUI.Message.Alert(result.message,'error');
        }
    }



</script>

