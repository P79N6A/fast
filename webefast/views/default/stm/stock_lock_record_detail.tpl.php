<?php
$shop_code = load_model('base/ShopModel')->get_purview_shop();
array_unshift($shop_code, array("shop_code" => '', "shop_name" => '请选择'));
$lock_obj = array(array('0', '无'));
if ($response['params']['stm_record_lock_obj'] == 1) {
    array_push($lock_obj, array('1', '网络店铺'));
}
$fields = array(
    array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
    array('title' => '下单日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
    array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_select(2)),
    array('title' => '锁定对象', 'type' => 'radio_group', 'field' => 'lock_obj', 'data' => $lock_obj),//array('2', '分销商')
    array('title' => '店铺', 'type' => 'select', 'field' => 'shop_code', 'data' => $shop_code),
    array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
);
$hidden_fields = array(array('field' => 'stock_lock_record_id'));

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => $hidden_fields,
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'stm/stock_lock_record/do_edit', //edit,add,view
    'act_add' => 'stm/stock_lock_record/do_add',
    'callback' => 'after_submit',
    'data' => $response['data'],
    'rules' => array(
        array('order_time', 'require'),
        array('store_code', 'require'),
       // array('shop_code', 'require'),
    ),
));
?>

<script type="text/javascript">
    //锁定对象默认无
    $("input[name='lock_obj']").eq(0).attr("checked", "checked");
    //店铺默认隐藏
    $("#shop_code").parent().parent().parent().hide();
    $("#shop_code").parent().append("<b style='color:red'> *</b>");
    //单号不能编辑
    $("#record_code").attr("disabled", "disabled");

    //表单提交前操作
    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
        var lock_obj_checked = $("input[name='lock_obj']:checked").val();
        if (lock_obj_checked == 1 && $("#shop_code").val() == '') {
            $("#shop_code").parent().append("<span class='valid-text'><span class='estate error'><span class='x-icon x-icon-mini x-icon-error'>!</span><em>不能为空！</em></span></span>");
            return false;
        }
    });

    //回调函数
    function after_submit(result, ES_frmId) {
        var url = '?app_act=stm/stock_lock_record/view&stock_lock_record_id=' + result.data
        //打开新页面
        openPage(window.btoa(url), url, '锁定单详情');
        //关闭弹窗
        ui_closePopWindow(ES_frmId);
    }



    //选择绑定对象
    $("input[name='lock_obj']").change(function(){
        var lock_obj=$("input[name='lock_obj']:checked").val();
        if(lock_obj == 1) {
            $("#shop_code").parent().parent().parent().show();
        } else {
            $("#shop_code").val('');
            $("#shop_code").parent().parent().parent().hide();
        }
    })

</script>

