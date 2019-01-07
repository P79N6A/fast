<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '对账编号', 'type' => 'input', 'field' => 'dz_code'),
            array('title' => '选择仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '选择月份', 'type' => 'input', 'field' => 'dz_month', 'value' => date('Y-m')),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_add' => 'acc/order_express_detail/do_add',
    'data' => $response['data'],
    'callback' => 'after_submit',
    'rules' => array(
        array('user_code', 'require'),
        array('user_code', 'minlength', 'value' => 5),
    )
));
?>

<script type="text/javascript">
    //月结单号自动生成，不可编辑
    $("#dz_code").attr("disabled", "disabled");
    form.on('beforesubmit', function () {
        $("#dz_code").attr("disabled", false);
    });

    function after_submit(ret, ES_frmId) {
        var type = ret.status == 1 ? 'success' : 'error';
        if (type == 'success') {
           var url = '?app_act=acc/order_express_detail/view&dz_code=' + ret.data;
           openPage(window.btoa(url), url, '订单运费核销明细');
           ui_closePopWindow(ES_frmId);  
        }else{
           BUI.Message.Alert(ret.message, type);
        } 
    }

    BUI.use('bui/calendar', function (Calendar) {
        var dt = new Date();
        m = dt.getMonth();
        y = dt.getFullYear();
        var inputEl = $('#dz_month'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            year: y,
            month: m,
            success: function () {
                var month = String(this.get('month') + 1),
                        year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-');
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });

</script>