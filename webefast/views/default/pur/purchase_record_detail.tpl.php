<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '单据编号', 'type' => 'input', 'field' => 'record_code',),
            //array('title' => '通知单号', 'type' => 'input', 'field' => 'relation_code'),
            array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
            array('title' => '采购类型', 'type' => 'select', 'field' => 'record_type_code', 'data' => ds_get_select('record_type', 2, array('record_type_property' => 0))),
            array('title' => '供应商', 'type' => 'select', 'field' => 'supplier_code', 'data' => $response['supplier'], 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            array('title' => '仓库', 'type' => 'select', 'field' => 'store_code', 'data' => $response['store']),
            // array('title' => '业务员', 'type' => 'select', 'field' => 'user_code', 'data' => ds_get_select('sys_user',2)),
            array('title' => '折扣', 'type' => 'input', 'field' => 'rebate', 'value' => '1.00'),
            // array('title' => '预售', 'type' => 'checkbox', 'field' => 'is_pre_sale'),
            array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
        ),
        'hidden_fields' => array(array('field' => 'purchaser_record_id')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'pur/purchase_record/do_edit', //edit,add,view
    'act_add' => 'pur/purchase_record/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('record_time', 'require'),
        array('record_time', 'require'),
        array('store_code', 'require'),
        array('supplier_code', 'require'),
        array('rebate', 'require'),
    ),
));
?>

<script type="text/javascript">
    $("#record_code").attr("disabled", "disabled");

    form.on('beforesubmit', function () {
        $("#record_code").attr("disabled", false);
    });
    $("#rebate").blur(function () {
        if (this.value > 1 || this.value < 0) {
            alert('折扣只能输入【0-1】之间的数值');
            this.value = '1.0'
        }
    });
    parent.add_c = function (custom_code) {
        $('#supplier_code').find('option[value="' + custom_code + '"]').attr('selected', true);
        $('#supplier_code').parent().find('.valid-text').html('');
    };

    $('#base_supplier').click(function () {
        new ESUI.PopWindow("?app_act=pur/order_record/detail_supplier", {
            title: "选择供应商",
            width: 960,
            height: 500,
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    })
</script>

