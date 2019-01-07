<style>
    .bui-stdmod-footer{margin-top: 0!important}
    #form2{padding-left: 10px;}
</style>
<?php
$tag = $response['data']['capital_type'] == 1 ? '收款' : '扣款';
if ($response['data']['login_type'] == 2) {
    $fields = array(
        array('title' => '分销商', 'type' => 'label', 'field' => 'custom_name', 'value' => $response['data']['custom_name']),
        array('title' => $tag . '账户', 'type' => 'label', 'field' => 'capital_account', 'value' => '预存款账户'),
        array('title' => '充值金额', 'type' => 'input', 'field' => 'money'),
        array('title' => '日期', 'type' => 'time', 'field' => 'record_time', 'value' => date('Y-m-d H:i:s')),
        array('title' => '充值方式', 'type' => 'select', 'field' => 'pay_type_code', 'data' => array(array('alipay', '支付宝'))),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
    );
} else {
    $fields = array(
        array('title' => '分销商', 'type' => 'label', 'field' => 'custom_name', 'value' => $response['data']['custom_name']),
        array('title' => '分销商欠款', 'type' => 'label', 'field' => 'arrears_money', 'value' => $response['data']['arrears_money']),
        array('title' => $tag . '账户', 'type' => 'select', 'field' => 'capital_account', 'data' => array(array('yck', '预存款账户'))),
        array('title' => '金额', 'type' => 'input', 'field' => 'money'),
        array('title' => '日期', 'type' => 'time', 'field' => 'record_time', 'value' => date('Y-m-d H:i:s')),
        array('title' => $tag . '摘要', 'type' => 'select', 'field' => 'abstract', 'data' => $response['data']['abstract']),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
    );
}
render_control('Form', 'form2', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(array('field' => 'custom_code'), array('field' => 'capital_type'),array('field' => 'login_type')),
    ),
    'data' => $response['data'],
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset')
    ),
    'rules' => array(
        array('money', 'require'),
        array('record_time', 'require'),
        array('money', 'number'),
    ),
    'act_add' => 'fx/account/opt_balance',
    'callback' => 'dd',
));
?>
<script>
    $(function () {
        $("input").css('width', 140);
        $("select").css('width', 145);
    });
    form.on('beforesubmit', function () {
        if ($("#money").val() <= 0) {
            BUI.Message.Alert('金额必须是正数', 'error');
            return false;
        }
    });
    function dd(data, Esfrom_Id) {
        if (data.status < 0) {
            BUI.Message.Alert(data.message, 'error');
            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
        } else if (data.status == 1) {
            BUI.Message.Alert(data.message, 'success');
            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
        } else if (data.status == 2) {
            var result = data.data;
            window.open(result.url);
            BUI.Message.Show({
                title: '提示',
                msg: '是否充值成功?',
                icon: 'question',
                buttons: [
                    {
                        text: '充值成功',
                        elCls: 'button button-primary',
                        handler: function () {
                            check_pay_status(result.serial_number,this);
                        }
                    },
                    {
                        text: '充值失败',
                        elCls: 'button',
                        handler: function () {
                            if(result.serial_number != undefined){
                                check_pay_status(result.serial_number,this);
                            }else{
                                this.close();
                                ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
                            }
                        }
                    }
                ]
            });
        }
        $("#account_code").attr("disabled", false);
    }
    function check_pay_status(serial_number,_this){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/check_pay_status'); ?>', data: {serial_number: serial_number},
            success: function (ret) {
                _this.close();
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
                } else {
                    $("#account_code").attr("disabled", false);
                    BUI.Message.Tip(ret.message, type);
                }
            }
        });
    }
</script>