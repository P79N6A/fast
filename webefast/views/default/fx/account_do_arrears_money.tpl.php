<style>
    .bui-stdmod-footer{margin-top: 0!important}
    #form2{padding-left: 0px;}
</style>
<?php
render_control('Form', 'form2', array(
    'conf' => array(
        'fields' => array(
            array('title' => '分销商', 'type' => 'label', 'field' => 'custom_name', 'value' => 'custom_name'),
            array('title' => '欠款额度', 'type' => 'input', 'field' => 'arrears_money'),
        ),
        'hidden_fields' => array(array('field' => 'custom_code')),
    ),
    'data' => $response['data'],
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset')
    ),
    'rules' => array(
    ),
    'act_edit' => 'fx/account/update_arrears',
    'callback' => 'dd',
));
?>
<span style="color: red;">说明：设置后，资金账户中无足够余额，业务单据也可先支付。</span>
<script>
    form.on('beforesubmit', function () {
        if ($("#arrears_money").val() < 0) {
            BUI.Message.Alert('欠款额度必须是正数', 'error');
            return false;
        }
    });
    function dd(data, Esfrom_Id) {
        ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
    }
</script>