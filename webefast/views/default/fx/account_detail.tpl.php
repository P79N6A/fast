
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '流水号', 'type' => 'input', 'field' => 'account_code', 'edit_scene' => 'add'),
            array('title' => '分销商', 'type' => 'select_pop', 'id' => 'p_code', 'select' => 'base/custom'),
            array('title' => '充值金额', 'type' => 'input', 'field' => 'account_money'),
            array('title' => '支付方式', 'type' => 'select', 'field' => 'pay_type', 'data' => $response['pay_type_code']),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'account_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'fx/account/do_edit',
    'act_add' => 'fx/account/do_add',
    'data' => $response['data'],
    'callback' => 'dd',
    'rules' => array(
        array('account_money', 'require'),
//        array('custom_code', 'require'),
    ),
));
?>
<script>
    $("#account_code").attr("disabled", "disabled");
    form.on('beforesubmit', function () {
        $("#account_code").attr("disabled", false);
        var custom_code = $("#custom_code").val();
        if (custom_code.length <= 0) {
            BUI.Message.Alert('供应商不能为空', 'error');
            return;
        }
        var account_money = $('#account_money').val();
        var a = /^[0-9]*(\.[0-9]{1,2})?$/;
        if (!a.test(account_money))
        {
            BUI.Message.Alert('金额格式不正确', 'error');
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
            window.open(data.data);
            BUI.Message.Show({
                title: '提示',
                msg: '是否充值成功?',
                icon: 'question',
                buttons: [
                    {
                        text: '充值成功',
                        elCls: 'button button-primary',
                        handler: function () {
                            var account_code = $("#account_code").val();
                            check_pay_status(account_code,this);
                        }
                    },
                    {
                        text: '充值失败',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
                        }
                    }
                ]
            });
        }
        $("#account_code").attr("disabled", false);
    }
    function check_pay_status(_account_code,_this){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('fx/account/check_pay_status'); ?>', data: {account_code: _account_code},
            success: function (ret) {
                _this.close();
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
                } else {
                    $("#account_code").attr("disabled", false);
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    $(document).ready(function () {
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#_select_pop").parent().append(html);
    });
    var selectPopWindow = {
        dialog: null,
        callback: function (value) {
            var custom_code = value[0]['custom_code'];
            var custom_name = value[0]['custom_name'];
            $('#_select_pop').val(custom_name);
            $('#custom_code').val(custom_code);
            if (selectPopWindow.dialog != null) {
                selectPopWindow.dialog.close();
            }
        }
    };
</script>


