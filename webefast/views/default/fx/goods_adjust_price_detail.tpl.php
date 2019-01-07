<?php
$data = array(
    array(0, '吊牌价'), array(1, '成本价'), array(2, '批发价'), array(3, '进货价')
);
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            array('title' => '调价对象', 'type' => 'radio_group', 'field' => 'adjust_price_object', 'data' => array(array('1', '指定分销商'), array('2', '分销商分类'))),
            array('title' => '', 'type' => 'select_pop', 'id' => '_select_pop', 'select' => 'base/shop_custom'),
            array('title' => '', 'type' => 'select', 'field' => 'custom_grades', 'data' => $response['custom_grades']),
            array('title' => '开始时间', 'type' => 'time', 'field' => 'start_time',  /* 'value'=>date('Y-m-d H:i:s') */),
            array('title' => '结束时间', 'type' => 'time', 'field' => 'end_time',  /* 'value'=>date('Y-m-d H:i:s',strtotime("+1 month")) */),
            array('title' => '结算价格', 'type' => 'select', 'field' => 'settlement_price_type', 'data' => $data),
            array('title' => '结算折扣', 'type' => 'input', 'field' => 'settlement_rebate', 'value' => '1.00'),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'fx/goods_adjust_price/do_edit', //edit,add,view
    'act_add' => 'fx/goods_adjust_price/do_add',
    'data' => $response['data'],
    'callback' => 'after_submit',
    'rules' => array(
        array('start_time', 'require'),
        array('end_time', 'require'),
        array('settlement_rebate', 'require'),
    ),
));
?>
<?php // echo load_js('comm_util.js') ?>
<script>
    var type = "<?php echo $response['app_scene']; ?>";
    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
        var start_time = $('#start_time').val();
        var end_time = $('#end_time').val();
        var timestamp = Date.parse(new Date());
        if(start_time != '' && Date.parse(start_time) < timestamp) {
            BUI.Message.Tip('开始时间不能早于当前时间', 'error');
            return false;
        }
        if(start_time != '' && Date.parse(start_time) > Date.parse(end_time)) {
            BUI.Message.Tip('开始时间不能晚于结束时间', 'error');
            return false;
        }
        if($('#rd_adjust_price_object_0:checked').val()) {
            var custom_code = $('#custom_code').val();
            if(custom_code == '') {
                BUI.Message.Tip('请选择调价对象', 'error');
                return false;
            }
        } else {
            var custom_grades = $('#custom_grades').val();
            if(custom_grades == '') {
                BUI.Message.Tip('请选择调价对象', 'error');
                return false;
            }
        }
        var settlement_rebate = $('#settlement_rebate').val();
        if(isNaN(settlement_rebate) || settlement_rebate < 0 || settlement_rebate > 1) {
            BUI.Message.Tip('折扣格式不正确', 'error');
            return false;
        }
    });
    function after_submit(result,ES_frmId) {
        if(result.status == 1) {
            var url = '?app_act=fx/goods_adjust_price/view&id=' +result.data
            openPage(window.btoa(url),url,'调价单详情');
            ui_closePopWindow(ES_frmId);
        } else {
            BUI.Message.Tip(result.message, 'error');
        }
        
    }
    
    //转换时间戳
    function getTimestamp(_date) {
        var timestamp = Date.parse(_date);
        return timestamp / 1000;
    }
    $(function () {
        if (type == 'add') {
            $("#rd_adjust_price_object_0").attr("checked", true);
            $("#custom_grades").hide();
            $("#custom_grades").parent().prev().hide();
            $("#custom_grades").next().hide();
            $("#_select_pop").show();
            $("#_select_pop").parent().prev().show();
            $("#_select_pop").next().show();
        }
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#_select_pop").parent().append(html);
        
        $("#record_code").attr("disabled", "disabled");
        $('#end_time').after("<span style='color:red;margin-left:5px;'>*</span>");
        $('#start_time').after("<span style='color:red;margin-left:5px;'>*</span>");
        
        $("#rd_adjust_price_object_0").click(function () {
            $("#custom_grades").hide();
            $("#custom_grades").parent().prev().hide();
            $("#custom_grades").next().hide();
            $("#_select_pop").show();
            $("#_select_pop").parent().prev().show();
            $("#_select_pop").next().show();
        })
        $("#rd_adjust_price_object_1").click(function () {
            $("#_select_pop").hide();
            $("#_select_pop").parent().prev().hide();
            $("#_select_pop").next().hide();
            $("#custom_grades").show();
            $("#custom_grades").parent().prev().show();
            $("#custom_grades").next().show();
        })
    })
    var selectPopWindow = {
        dialog: null,
        callback: function (value) {
            if (value[0] != undefined) {
                var custom_code = value[0]['custom_code'];
                var custom_name = value[0]['custom_name'];
                $('#_select_pop').val(custom_name);
                $('#custom_code').val(custom_code);
            }
            if (selectPopWindow.dialog != null) {
                selectPopWindow.dialog.close();
            }
        }
    };
</script>