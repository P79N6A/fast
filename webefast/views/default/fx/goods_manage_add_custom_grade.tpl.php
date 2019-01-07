<style type="text/css">
    .form-horizontal .control-label {
        display: inline-block;
        float: left;
        line-height: 30px;
        text-align: left;
        width: 130px;
    }
    #container{
        padding: 0 1% 10px;
    }
</style>
<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '分销商分类', 'type' => 'select', 'field' => 'grade_code', 'data' => $response['grade_code']),
            array('title' => '折扣（基于吊牌价）', 'type' => 'input', 'field' => 'rebates', 'value' => '1.0', 'remark' => "<br><span style='color: #F00'>输入值需小于等于1</span>",),
        ),
        'hidden_fields' => array(array('field' => 'goods_line_code','value' => $request['goods_line_code'])),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    //  'act_edit' => 'fx/goods_manage/do_edit', //edit,add,view
    'act_add' => 'fx/goods_manage/do_add_custom_grade',
    'data' => $response['data'],
    'callback' => 'after_add',
    'rules' => array(
        array('grade_code', 'require'),
        array('rebates', 'require'),
    ),
));
?>


<!--<input id="detail_data" name="detail_data"  type="hidden" value="" />-->

<script type="text/javascript">
    $("#rebates").keyup(function() {
        this.value = this.value.replace(/[^0-9.]/g, '');
        if (this.value > 1 || this.value < 0) {
            BUI.Message.Alert('折扣只能输入【0-1】之间的数值');
            this.value = '1.0';
        }
    });
    function after_add(data, ES_frmId) {
        BUI.Message.Alert(data.message);
    }
//    $(function() {
//        $('#detail_data').val(detail_data);
//        $('#form1').append($('#detail_data'));
//    });
//
//
//    form.on('beforesubmit', function() {
//        $("#record_code").attr("disabled", false);
//    });
</script>


