<style>
    #store_code_select_multi .bui-select-input{width:125px;}
</style>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '月结单号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '月结月份', 'type' => 'input', 'field' => 'ymonth', 'value' => date('Y-m')),
            array('title' => '汇总仓库', 'type' => 'select_multi', 'field' => 'store_code', 'data' => $response['store']),
            array('title' => '备　　注', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'cost_month_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_add' => 'acc/cost_month/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('ymonth', 'require'),
        array('store_code', 'require'),
    ),
));
?>

<script type="text/javascript">
    //月结单号自动生成，不可编辑
    $("#record_code").attr("disabled", "disabled");
    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });

    //选择年月
    BUI.use('bui/calendar', function (Calendar) {
        var inputEl = $('#ymonth'), monthpicker = new BUI.Calendar.MonthPicker({
            trigger: inputEl,
            // month:1, //月份从0开始，11结束
            autoHide: true,
            align: {
                points: ['bl', 'tl']
            },
            //year:2000,
            success: function () {
                var month = String(this.get('month') + 1),
                        year = this.get('year');
                var date = year + '-' + month;
                if (month.length < 2) {
                    date = year + '-0' + month;
                }
                inputEl.val(date);//月份从0开始，11结束
                this.hide();
            }
        });
        monthpicker.render();
        monthpicker.on('show', function (ev) {
            var val = inputEl.val(),
                    arr, month, year;
            if (val) {
                arr = val.split('-'); //分割年月
                year = parseInt(arr[0]);
                month = parseInt(arr[1]);
                monthpicker.set('year', year);
                monthpicker.set('month', month - 1);
            }
        });
    });
</script>

