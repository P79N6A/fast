<?php
render_control('PageHead', 'head1', array(
    'title' => isset($app['title']) ? $app['title'] : '编辑产品订购',
    'links' => array(
        array('url' => 'market/valueorder/do_list_new', 'title' => '增值订购列表')
    )
));
?>
<?php
$view_fields = array(
    array('title' => '订购编号', 'type' => 'input', 'field' => 'order_code', 'edit_scene' => 'add', 'show_scene' => 'add'),
    array('title' => '销售渠道', 'type' => 'select_pop', 'field' => 'val_channel_id', 'select' => 'basedata/sellchannel', 'selecttype' => 'tree', 'show_scene' => 'add,edit'),
    array('title' => '销售渠道', 'type' => 'input', 'field' => 'val_channel_id_name', 'show_scene' => 'view'),
    array('title' => '客户名称', 'type' => 'select_pop', 'field' => 'kh_id', 'select' => 'clients/clientinfo', 'show_scene' => 'add,edit'),
    array('title' => '产品名称', 'type' => 'select', 'field' => 'val_cp_id', 'data' => ds_get_select('chanpin', 2)),
    array('title' => '描述', 'type' => 'textarea', 'field' => 'val_desc',),
);
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $view_fields,
//        'hidden_fields' => array(array('field' => 'val_num'),),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 1,
    'act_edit' => 'market/valueorder/valorders_edit', //edit,add,view
    'act_add' => 'market/valueorder/valorders_add_new',
    'data' => $response['data'],
    'callback' => 'after_submit',
    'rules' => array(
        array('val_channel_id', 'require'),
        array('kh_id', 'require'),
        array('val_cp_id', 'require'),
    )
));
?>
<script type="text/javascript">
    $("#order_code").attr('disabled', true);
    form.on('beforesubmit', function () {
        $("#order_code").attr("disabled", false);
    });

    function after_submit(result, ES_frmId) {
        var url = '?app_act=market/valueorder/view_new&id=' + result.data
        openPage(window.btoa(url), url, '订购详情');
        //  ui_closeTabPage(ES_frmId);关闭页面
        ui_closePopWindow(ES_frmId);//关闭弹窗
    }

    //产品默认为365，渠道默认为自营
    $(function () {
        $("#val_cp_id").val(21);
        $("#val_channel_id").val(86);
        $("#val_channel_id_select_pop").val("86[自营]");
    })

</script>