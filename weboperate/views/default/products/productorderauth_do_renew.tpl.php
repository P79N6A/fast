<?php
$fields = array(
    array('title' => '客户名称', 'type' => 'input', 'field' => 'kh_name', 'edit_scene' => 'add'),
    array('title'=>'续费时长', 'type'=>'input', 'field'=>'pro_hire_limit','data' => '', 'edit_scene' => 'edit','remark'=>'月'),
    array('title'=>'变动点数', 'type'=>'input', 'field'=>'pro_dot_num','data' => '', 'edit_scene' => 'edit'),
    array('title'=>'续费金额', 'type'=>'input', 'field'=>'pro_real_price','edit_scene' => 'edit'),
);
$hidden_fields = array(array('field' => 'pra_id'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'products/productorderauth/do_renew_save', //edit,add,view
    //'act_add' => 'servicenter/productxqissue/do_xqissue_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('pro_real_price', 'require'),
        //array('xqsue_difficulty', 'require'),
    ),
));
?>

<script type="text/javascript">
    //表单提交前操作
    form.on('beforesubmit', function () {
        var pro_hire_limit = $("#pro_hire_limit").val();
        var pro_dot_num = $("#pro_dot_num").val();
        if (pro_hire_limit.length < 1 && pro_dot_num.length < 1) {
            BUI.Message.Alert('续费时长，变动点数必须填写一个！', 'error');
            return false;
        }
        var re = /^\d+$/;
        if (!re.test(pro_hire_limit) && pro_hire_limit.length > 0) {
            BUI.Message.Alert('续费时长必须填写正整数！', 'error');
            return false;
        }
        var re_pro = /^\-?[1-9]{1}[0-9]*$|^[0]{1}$/;
        if (!re_pro.test(pro_dot_num) && pro_dot_num.length > 0) {
            BUI.Message.Alert('变动点数必须填写整数！', 'error');
            return false;
        }
        return true;
    });

    //回调函数
    function after_submit(result, ES_frmId) {
        if (result.status != 1) {
            BUI.Message.Alert(result.message, 'error');
        } else {
            //关闭弹窗
            ui_closePopWindow(ES_frmId);
        }
    }


</script>

