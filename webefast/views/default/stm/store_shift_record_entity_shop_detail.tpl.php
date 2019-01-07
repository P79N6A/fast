<style type="text/css">
    .form-horizontal .control-label {
        width: 110px;
    }
</style>

<?php
if ($response['user_type'] == 1) {
    $fields = array(
        array('title' => '请选择调拨类型', 'type' => 'select', 'field' => 'shift_type', 'data' => $response['shift_type']),
        array('title' => '调出仓(移出)', 'type' => 'select', 'field' => 'store_out_code', 'data' => load_model('base/StoreModel')->get_entity_store()),
        array('title' => '调入仓(移入)', 'type' => 'select', 'field' => 'store_in_code', 'data' => load_model('base/StoreModel')->get_entity_store()),
        array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
    );
} else {
    $fields = array(
        array('title' => '调拨类型', 'type' => 'select', 'field' => 'shift_type', 'data' => $response['shift_type']),
        array('title' => '选择仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_entity_store()),
        array('title' => '业务日期', 'type' => 'date', 'field' => 'record_time', 'value' => date('Y-m-d')),
        array('title' => '备注', 'type' => 'textarea', 'field' => 'remark'),
    );
}

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(array('field' => 'shift_record_id'), array('field' => 'type', 'value' => 'entity_shop'))
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'stm/store_shift_record/do_edit', //edit,add,view
    'act_add' => 'stm/store_shift_record/do_add',
    'data' => $response['data'],
    'callback' => 'after_submit',
    'rules' => array(
        array('shift_type', 'require'),
        array('store_out_time', 'require'),
        array('store_in_time', 'require'),
        array('store_out_code', 'require'),
        array('store_in_code', 'require'),
        array('rebate', 'require'),
    ),
));
?>

<script type="text/javascript">
    var user_type = "<?php echo $response['user_type']; ?>";
    var shift_type = $("#shift_type").val();
    get_store_info(shift_type);
    $(function () {
        $('#shift_type').change(function () {
            var shift_type = $(this).val();
            get_store_info(shift_type);
//            $("#ship_area_code").val(parent_id);
        });


        if (user_type == 1) {
            var html1 = "<span id='store_out_code_desc'>(总部仓)</span>";
            var html2 = "<span id='store_in_code_desc'>(门店仓)</span>";
            $("#store_out_code").after(html1);
            $("#store_in_code").after(html2);
        } else {
            var html3 = "<span id='shop_store_code_desc'>(总部仓库)</span>";
            $("#store_code").after(html3);
        }
    });

    function after_submit(result, ES_frmId) {
        var url = '?app_act=stm/store_shift_record/entity_shop_view&is_entity=1&shift_record_id=' + result.data;
        openPage(window.btoa(url), url, '门店库存调拨单');
        ui_closePopWindow(ES_frmId);
    }


    function get_store_info(shift_type) {
        $.ajax({type: 'POST',
            dataType: 'json',
            url: '?app_act=stm/store_shift_record/get_store_info',
            data: {shift_type: shift_type},
            success: function (data) {
                var store_out_code = data['store_out_code'];
                var store_in_code = data['store_in_code'];
                var html1 = '<option value="">请选择</option>';
                var html2 = '<option value="">请选择</option>';
                var html3 = '<option value="">请选择</option>';
                if (shift_type == "general_to_shop_user" || shift_type == "shop_to_general_user" || shift_type == "shop_to_shop_user") {
                    for (var i = 0; i < store_out_code.length; i++) {
                        html1 += "<option value='" + store_out_code[i].store_code + "'  >" + store_out_code[i].store_name + "</option>";
                    }
                    $("#store_out_code").html(html1);
                    for (var i = 0; i < store_in_code.length; i++) {
                        html2 += "<option value='" + store_in_code[i].store_code + "'  >" + store_in_code[i].store_name + "</option>";
                    }
                    $("#store_in_code").html(html2);
                }

                switch (shift_type) {
                    case "general_to_shop_user":
                        $("#store_out_code_desc").html("(总部仓)");
                        $("#store_in_code_desc").html("(门店仓)");
                        break;
                    case "shop_to_general_user":
                        $("#store_out_code_desc").html("(门店仓)");
                        $("#store_in_code_desc").html("(总部仓)");
                        break;
                    case "shop_to_shop_user":
                        $("#store_out_code_desc").html("(门店仓)");
                        $("#store_in_code_desc").html("(门店仓)");
                        break;

                    case "general_to_shop_shop":
                        for (var i = 0; i < store_out_code.length; i++) {
                            html3 += "<option value='" + store_out_code[i].store_code + "'  >" + store_out_code[i].store_name + "</option>";
                        }
                        $("#store_code").html(html3);
                        $("#shop_store_code_desc").html("(总部仓库)");

                        break;
                    case "next_to_shop_shop":
                        for (var i = 0; i < store_out_code.length; i++) {
                            html3 += "<option value='" + store_out_code[i].store_code + "'  >" + store_out_code[i].store_name + "</option>";
                        }
                        $("#store_code").html(html3);
                        $("#shop_store_code_desc").html("(邻店仓库)");

                        break;
                    case "shop_to_general_shop":
                        for (var i = 0; i < store_in_code.length; i++) {
                            html3 += "<option value='" + store_in_code[i].store_code + "'  >" + store_in_code[i].store_name + "</option>";
                        }
                        $("#store_code").html(html3);
                        $("#shop_store_code_desc").html("(总部仓库)");
                        break;
                    case "shop_to_next_shop":
                        for (var i = 0; i < store_in_code.length; i++) {
                            html3 += "<option value='" + store_in_code[i].store_code + "'  >" + store_in_code[i].store_name + "</option>";
                        }
                        $("#store_code").html(html3);
                        $("#shop_store_code_desc").html("(邻店仓库)");
                        break;
                }
            }
        });
    }

</script>