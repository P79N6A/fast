<style>
    .sear_ico{
        margin-right: 15px;
    }
</style>
<?php
$fields = array(
    array('title' => '单据编号', 'type' => 'input', 'field' => 'return_record_code',),
    array('title' => '关联单号', 'type' => 'input', 'field' => 'init_code'),
    array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
);
if($response['login_type'] == 2){
    $fields[] = array('title'=>'分销商', 'type'=>'input','field' => 'custom_code', 'value' => $response['custom']['custom_code']);
} else {
    $fields[] = array('title'=>'分销商', 'type'=>'select_pop', 'id'=>'p_code', 'select'=>'base/custom','remark'=>"<span style='color:red;'>*</span>");
}
 $fields[] = array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']);
 $fields[] = array('title' => '备注', 'type' => 'textarea', 'field' => 'remark');
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(array('field' => 'fx_purchaser_return_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'fx/purchase_return_record/do_edit', //edit,add,view
    'act_add' => 'fx/purchase_return_record/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('record_time', 'require'),
        array('store_code', 'require'),
        array('rebate', 'require'),
    ),
    'callback' => 'parent_return',
));
?>

<script type="text/javascript">
    $("#return_record_code").attr("disabled", "disabled");
    $("#custom_code").attr("disabled", "disabled");
    $("#rebate").attr("disabled", "disabled");

    form.on('beforesubmit', function() {
        $("#return_record_code").attr("disabled", false);
        $("#rebate").attr("disabled", false);
    });
    $("#rebate").blur(function() {
        if (this.value > 1 || this.value < 0) {
            alert('折扣只能输入【0-1】之间的数值');
            this.value = '1.0'
        }
    });
    function parent_return(data, Esfrom_Id) {
        if(data.status == 1) {
            parent.do_detail_return(data.data);
            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
        } else {
            BUI.Message.Alert(data.message,'error');
            ui_closePopWindow('<?php echo CTX()->request['ES_frmId'] ?>');
        }
    }
    $(document).ready(function(){
        var html = '';
        html += '<input type="hidden" name="custom_code" value="" id="custom_code">';
        $("#_select_pop").parent().append(html);
    });
    form.on('beforesubmit', function () {
        var custom_code = $("#custom_code").val();
        if(custom_code.length <= 0){
             BUI.Message.Alert('分销商不能为空', 'error');
             return false;
        }
    });
    var selectPopWindow = {
        dialog: null,
        callback: function(value) {
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

