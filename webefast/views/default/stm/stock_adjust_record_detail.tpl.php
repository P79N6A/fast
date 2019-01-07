<?php echo load_js('comm_util.js') ?>
<?php
if (isset($request['shop_type']) && $request['shop_type'] == 'entity_shop') {
    $fields = array(
        array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code'),
        array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
        array('title' => '调整类型', 'type' => 'select', 'field' => 'adjust_type', 'data' => $response['adjust_type']),
        array('title' => '调整原因', 'type' => 'input', 'field' => 'remark'),
        array('title' => '门店', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
    );
    $hidden_fields = array(array('field' => 'stock_adjust_record_id'), array('field' => 'shop_type', 'value' => 'entity_shop'));
} else {
    $fields = array(
        array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
        array('title' => '原单号', 'type' => 'input', 'field' => 'init_code'),
        array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
        array('title' => '调整类型', 'type' => 'select', 'field' => 'adjust_type', 'data' => $response['adjust_type']),
        array('title' => '调整原因', 'type' => 'input', 'field' => 'remark'),
        array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
    );
    $hidden_fields = array(array('field' => 'stock_adjust_record_id'));
}

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'stm/stock_adjust_record/do_edit', //edit,add,view
    'act_add' => 'stm/stock_adjust_record/do_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('record_time', 'require'),
        array('store_code', 'require'),
    ),
));
?>

<script type="text/javascript">

    $("#record_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });

    function after_submit(result, ES_frmId) {
        if(result.status < 0){
            $("#record_code").attr("disabled", "disabled");
            BUI.Message.Alert(result.message, 'error');
            return false;
        }
        var login_type = <?php echo $response['login_type'] ?>;
        if (login_type > 0) {
            var url = '?app_act=stm/stock_adjust_record/entity_view&stock_adjust_record_id=' + result.data
        } else {
            var url = '?app_act=stm/stock_adjust_record/view&stock_adjust_record_id=' + result.data
        }
        openPage(window.btoa(url), url, '调整单详情');
        ui_closePopWindow(ES_frmId);
        //setTimeout(function(){window.location.reload();},100);
    }

</script>

