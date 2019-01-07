<style type="text/css">
   .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
</style>
<!--<table cellspacing="0" class="table table-bordered">
    <tr>
        <td width="12%" align="right">收货人：</td>
        <td width="38%"><?php // echo $response['record']['receiver_name']; ?></td>
        <td width="12%" align="right">手机：</td>
        <td width="38%"><?php // echo $response['record']['receiver_mobile']; ?></td>
    </tr>
    <tr>
        <td align="right">收货地址：</td>
        <td><?php // echo $response['record']['receiver_address']; ?></td>
        <td align="right">固定电话：</td>
        <td><?php // echo $response['record']['receiver_phone']; ?></td>
    </tr>
    <tr>
        <td align="right">发货仓库：</td>
        <td><?php // echo $response['record']['store_name']; ?></td>
        <td align="right">邮编：</td>
        <td><?php // echo $response['record']['receiver_zip_code']; ?></td>
    </tr>
    <tr>
        <td align="right">配送方式：</td>
        <td><?php // echo $response['record']['express_name']; ?></td>
        <td align="right">快递单号：</td>
        <td><?php // echo $response['record']['express_no']; ?></td>
    </tr>
    <tr>
        <td align="right">有无发票：</td>
        <td><?php // echo empty($response['record']['invoice_title'])?'无':'有'; ?></td>
        <td align="right">订单重量：</td>
        <td><?php // echo $response['record']['goods_weigh']; ?></td>
    </tr>
    <tr>
        <td align="right">订单备注：</td>
        <td colspan="3"><?php // echo $response['record']['order_remark']; ?></td>
    </tr>
</table>-->
<style>
    .addr{ margin-bottom:1px;}
</style>
<?php
//$editHtml = ($app['scene'] == 'edit') ? '<span class="xaddress" style="color:blue;">修改</span>' : '';
if($response['record']['express_no'] != '' && $response['record']['shipping_status'] == 4){
    $trace = '<a href="###" onclick=logistic_trace('."'".$response['record']['sell_record_code']."'".') style="margin-left:30px">物流跟踪</a>';
}else{
    $trace = '';
}
if ($response['record']['shipping_status'] < 4) {
    $editHtml = '<span class="xaddress" style="color:red; cursor:pointer;">修改</span>';
} else {
    $editHtml = '';
}
$addr_select = '<select name="country" id="country" class="addr">
                <option value ="">国家</option>';
$list = oms_tb_all('base_area', array('type' => '1'));
foreach ($list as $k => $v) {
    $addr_select .= '<option value="' . $v['id'] . '">' . $v['name'] . '</option>';
}
$addr_select .='</select>
<select class="addr" name="province" id="province"><option>省</option></select>
            <select class="addr" name="city" id="city">
                <option>市</option>
            </select>
            <select class="addr" name="district" id="district">
                <option>区</option>
            </select>
            <select class="addr" name="street" id="street">
                <option>街道</option>
            </select>
			<input class="addr" id="receiver_addr" name="receiver_addr" type="text" value="' . $response['record']['receiver_addr'] . '">';
$fields = array();
$record_data = $response['record'];

if($request['opt']!='edit'){
    safe_data($record_data);
}

if ($response['add_his'] == '1') {

    $fields = array(
        array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_name', 'edit_scene' => 'view'),
        array('title' => '手机', 'type' => 'input', 'field' => 'receiver_mobile', 'edit_scene' => 'view'),
        array('title' => '收货地址   ' . $editHtml, 'type' => 'label', 'field' => 'receiver_address'),
        array('title' => '固定电话', 'type' => 'input', 'field' => 'receiver_phone', 'edit_scene' => 'view'),
        array('title' => '发货仓库', 'type' => 'select', 'field' => 'store_code', 'edit_scene' => 'view', 'data' => ds_get_select("store")),
        array('title' => '邮编', 'type' => 'input', 'field' => 'receiver_zip_code', 'edit_scene' => 'view'),
        array('title' => '配送方式', 'type' => 'select', 'field' => 'express_code', 'edit_scene' => 'view', 'data' => (($app['scene'] == 'edit') ? ds_get_select("express", 0, array('status' => 1)) : ds_get_select("express"))),
        array('title' => '快递单号', 'type' => 'input', 'field' => 'express_no', 'edit_scene' => 'view'),
        array('title' => '有无发票', 'type' => 'html', 'field' => 'invoice_title', 'html' => (empty($response['record']['invoice_title']) ? '无' : '有')),
        array('title' => '订单重量', 'type' => 'label', 'field' => 'real_weigh'),
        array('title' => '订单理论重量', 'type' => 'label', 'field' => 'goods_weigh'),
    );
    /*
      $fields = array(
      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_name_1'),
      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_mobile_1'),
      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_phone_1'),
      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_addr_1'),
      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_zip_code_1'),

      array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_name'),
      array('title' => '手机', 'type' => 'input', 'field' => 'receiver_mobile'),
      array('title' => '收货地址     '.$editHtml, 'type' => 'html', 'field' => 'receiver_address', 'html' => ($app['scene'] == 'edit') ? $addr_select : $response['record']['receiver_address']),
      array('title' => '固定电话', 'type' => 'input', 'field' => 'receiver_phone'),
      array('title' => '发货仓库', 'type' => 'select', 'field' => 'store_code', 'data' => ds_get_select("store")),
      array('title' => '邮编', 'type' => 'input', 'field' => 'receiver_zip_code'),
      array('title' => '配送方式', 'type' => 'select', 'field' => 'express_code', 'data' => (($app['scene'] == 'edit') ? ds_get_select("express", 0, array('status' => 1)) : ds_get_select("express"))),
      array('title' => '快递单号', 'type' => 'input', 'field' => 'express_no'),
      array('title' => '有无发票', 'type' => 'html', 'field' => 'invoice_title', 'html' => (empty($response['record']['invoice_title']) ? '无' : '有')),
      array('title' => '订单重量', 'type' => 'label', 'field' => 'goods_weigh'),
      array('title' => '订单留言', 'type' => 'textarea', 'field' => 'order_remark'),
      array('title' => '仓库留言', 'type' => 'textarea', 'field' => 'store_remark'),
      );
     */
} else {
    if ($response['record']['order_status'] == '1' || $response['record']['shipping_status'] >= 1 || (($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2) && $response['record']['is_fx_settlement'] == 1)) {

            $fields = array(
            array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_name', 'edit_scene' => 'view'),
            array('title' => '手机', 'type' => 'input', 'field' => 'receiver_mobile', 'edit_scene' => 'view'),
            array('title' => '收货地址   ' . $editHtml, 'type' => 'label', 'field' => 'receiver_address'),
            array('title' => '固定电话', 'type' => 'input', 'field' => 'receiver_phone', 'edit_scene' => 'view'),
            array('title' => '发货仓库', 'type' => 'select', 'field' => 'store_code', 'edit_scene' => 'view', 'data' => load_model('base/StoreModel')->get_purview_store()),
            array('title' => '邮编', 'type' => 'input', 'field' => 'receiver_zip_code', 'edit_scene' => 'view'),
            array('title' => '配送方式', 'type' => 'select', 'field' => 'express_code', 'edit_scene' => 'view', 'data' => (($app['scene'] == 'edit') ? ds_get_select("express", 0, array('status' => 1)) : ds_get_select("express"))),
            array('title' => '快递单号', 'type' => 'html', 'field' => 'express_no','html'=>$response['record']['express_no'].$trace),
            array('title' => '有无发票', 'type' => 'html', 'field' => 'invoice_status', 'html' => ($response['record']['invoice_status'] == 0 ? '无' : '有')),
            array('title' => '订单重量', 'type' => 'label', 'field' => 'real_weigh'),
            array('title' => '订单理论重量', 'type' => 'label', 'field' => 'goods_weigh'),
            array('title' => '订单备注', 'type' => 'textarea', 'field' => 'order_remark'),
            array('title' => '仓库留言', 'type' => 'textarea', 'field' => 'store_remark'),
            );
            $fields[] = array('title' => '', 'type' => '', 'field' => '');
        }else{
            $fields = array(
            array('title' => '收货人', 'type' => 'input', 'field' => 'receiver_name'),
            array('title' => '手机', 'type' => 'input', 'field' => 'receiver_mobile'),
            array('title' => '收货地址     ' . $editHtml, 'type' => 'html', 'field' => 'receiver_address', 'html' => ($app['scene'] == 'edit') ? $addr_select : $record_data['receiver_address']),
            array('title' => '固定电话', 'type' => 'input', 'field' => 'receiver_phone'),
            array('title' => '发货仓库', 'type' => 'select', 'field' => 'store_code', 'data' => load_model('base/StoreModel')->get_purview_store()),
            array('title' => '邮编', 'type' => 'input', 'field' => 'receiver_zip_code'),
            array('title' => '配送方式', 'type' => 'select', 'field' => 'express_code', 'data' => (($app['scene'] == 'edit') ? ds_get_select("express", 0, array('status' => 1)) : ds_get_select("express"))),
            array('title' => '快递单号', 'type' => 'input', 'field' => 'express_no'),
            array('title' => '有无发票', 'type' => 'html', 'field' => 'invoice_title', 'html' => ($response['record']['invoice_status'] == 0 ? '无' : '有')),
            array('title' => '订单重量', 'type' => 'label', 'field' => 'real_weigh'),
            array('title' => '订单理论重量', 'type' => 'label', 'field' => 'goods_weigh'),
            array('title' => '订单备注', 'type' => 'textarea', 'field' => 'order_remark'),
            array('title' => '仓库留言', 'type' => 'textarea', 'field' => 'store_remark'),
            );
        }
        $fields[] = array('title' => '', 'type' => '', 'field' => '');
    }

render_control('FormTable', 'form2', array(
    'conf' => array(
        'fields' => $fields,
        'hidden_fields' => array(
            array('field' => 'sell_record_code', 'value' => $response['record']['sell_record_code']),
        ),
    ),
    'act_edit' => '',
    'col' => 2,
    'per' => '0.3',
    'buttons' => array(),
    'data' => $record_data,
));
?>
<?php //if($app['scene']=='edit'):?>
<script>
    var customer_code = "<?php echo $response['record']['customer_code']; ?>";
    var buyer_name = "<?php echo $response['record']['buyer_name']; ?>";
    var selectPopWindowshelf_code1 = {
        //dialog: null,
        callback: function (value, id, code, name) {
            //alert(value[0]['district']);

            $("#receiver_name_1").val(value[0]['name']);
            $("#receiver_mobile_1").val(value[0]['tel']);
            $("#receiver_phone_1").val(value[0]['home_tel']);
            $("#receiver_addr_1").val(value[0]['address']);
            $("#receiver_zip_code_1").val(value[0]['zipcode']);

            $("#country_1").val(value[0]['country']);
            $("#province_1").val(value[0]['province']);
            $("#city_1").val(value[0]['city']);
            $("#district_1").val(value[0]['district']);
            $("#street_1").val(value[0]['street']);

            var params = {sell_record_code: sell_record_code, "type": 'shipping', "data": {}};


            // params.data["express_code"] = $("#express_code").val();
            //  params.data["express_no"] = $("#express_no").val();
            params.data["receiver_name"] = value[0]['name'];
            params.data["receiver_mobile"] = value[0]['tel'];
            params.data["receiver_country"] = value[0]['country'];
            params.data["receiver_province"] = value[0]['province'];
            params.data["receiver_city"] = value[0]['city'];
            params.data["receiver_district"] = value[0]['district'];
            params.data["receiver_street"] = value[0]['street'];
            params.data["receiver_addr"] = value[0]['address'];
            params.data["receiver_phone"] = value[0]['home_tel'];
            params.data["customer_address_id"] = value[0]['customer_address_id'];

            // params.data["store_code"] = $("#store_code").val();
            //  params.data["receiver_zip_code"] = $("#receiver_zip_code").val();
            //  params.data["order_remark"] = $("#order_remark").val();
            //   params.data["store_remark"] = $("#store_remark").val();


            $.ajax({
                type: "post",
                url: "?app_act=oms/sell_record/save_component_ship",
                data: params,
                dataType: "json",
                async: false,
                success: function (data) {
                    if (data.status != "1") {
                        BUI.Message.Alert(data.message, 'error');

                    } else {
                        if (id == "shipping") {
                            var p = {};
                            p.store_code = params.data["store_code"];
                            update_panel_params(p);
                        }
                    }
                }
            });




            $.ajaxSetup({async: false});

            // fill_select(change_after,'country',value[0]['country']);
            //  fill_select(change_after,'province',value[0]['province']);
            // fill_select(change_after,'city',value[0]['city']);
            // fill_select(change_after,'district',value[0]['district']);
            // fill_select(change_after,'street',value[0]['street']);

            // $.ajaxSetup({async:true});

            $("#buyer_code").val(value[0]['customer_code']);
            $("#buyer_name").val(value[0]['customer_name']);

            //刷新数据
            component('shipping', "view");
            component("action", "view");
            //console.info(selectPopWindowshelf_code.dialog);
            if (selectPopWindowshelf_code1.dialog != null) {
                selectPopWindowshelf_code1.dialog.close();

            }

            // address_save('shipping');
        }

    };
    //component('shipping', "view");
    function change_after(param) {
        $("#" + param).change();
    }
    function fill_select(callback, param, value) {
        $("#" + param).val(value);
        callback(param);
    }
    function logistic_trace(sell_record_code){
        var title = "物流轨迹跟踪记录(订单:" + sell_record_code + ")";
//        if(kdniao_enable == 1){
//            title += "，数据来源于快递鸟";
//        }
        new ESUI.PopWindow("?app_act=oms/sell_record/logistic_trace&order_code=" +  sell_record_code, {
                title: title,
                width: 650,
                height: 550,
                onBeforeClosed: function () {
                },
                onClosed: function () {

                }
            }).show();
    }
    $('.xaddress').click(function () {
        address_edit('shipping');
        //var start = new Date().getTime();
        //while(true)  if(new Date().getTime()-start > 13000) break;
        //setTimeout("address_edit('shipping')",550000) //1秒=1000，这里是3秒

        var _opts = {title: '修改地址', width: 900, height: 500, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'};
        //console.info(_opts);
        window.selectPopWindowshelf_code1.dialog = new ESUI.PopSelectWindow(
                '?app_act=common/select/customer_address&customer_code=' + customer_code,
                'selectPopWindowshelf_code1.callback',
                _opts).show();
 //       console.info(window.selectPopWindowshelf_code1.dialog);
        //console.info(window.selectPopWindowshelf_code1.callback);
    });
    //保存
    function address_save(id) {
        //更新按钮状态
        $("#btn_edit_" + id).show();
        $("#btn_cancel_" + id).hide();
        $("#btn_save_" + id).hide();
        //保存数据
        save_component_ship(id);

        //刷新数据
        component(id, "view");
    }
    //初始化编辑按钮
    function address_edit(id) {
        // $("#btn_edit_"+id).hide();
        //  $("#btn_cancel_"+id).show();
        //  $("#btn_save_"+id).show();
        component_ship(id, "edit");

    }
    //读取各部分详情
    function component_ship(id, opt, callback) {
        if (typeof callback == 'undefined') {
            callback = btn_check;
        }
        var comp;
        var params = {"sell_record_code": sell_record_code, "type": id, "add_his": "1", "opt": opt, "components": components};
        $.post("?app_act=oms/sell_record/component", params, function (data) {
            if (id != "all") {
                comp = [id];
            } else {
                comp = components;
            }
            for (var i in comp) {
                $("#panel_" + comp[i]).html(data[comp[i]]);
            }
            callback();
        }, "json");
    }
    function save_component_ship(id) {
        var params = {sell_record_code: sell_record_code, "type": id, "data": {}};


        // params.data["express_code"] = $("#express_code").val();
        //  params.data["express_no"] = $("#express_no").val();
        params.data["receiver_name"] = $("#receiver_name_1").val();
        params.data["receiver_mobile"] = $("#receiver_mobile_1").val();
        params.data["receiver_country"] = $("#country_1").val();
        params.data["receiver_province"] = $("#province_1").val();
        params.data["receiver_city"] = $("#city_1").val();
        params.data["receiver_district"] = $("#district_1").val();
        params.data["receiver_street"] = $("#street_1").val();
        params.data["receiver_addr"] = $("#receiver_addr_1").val();
        params.data["receiver_phone"] = $("#receiver_phone_1").val();
        // params.data["store_code"] = $("#store_code").val();
        //  params.data["receiver_zip_code"] = $("#receiver_zip_code").val();
        //  params.data["order_remark"] = $("#order_remark").val();
        //   params.data["store_remark"] = $("#store_remark").val();


        $.ajax({
            type: "post",
            url: "?app_act=oms/sell_record/save_component_ship",
            data: params,
            dataType: "json",
            async: false,
            success: function (data) {
                if (data.status != "1") {
                    BUI.Message.Alert(data.message, 'error');

                } else {
                    if (id == "shipping") {
                        var p = {};
                        p.store_code = params.data["store_code"];
                        update_panel_params(p);
                    }
                }
            }
        });
    }


</script>


<script type="text/javascript">
    $(document).ready(function () {
        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        $('#country').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 0, url);
        });
        $('#province').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, url);
        });
        $('#city').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });

        $("#country").val("<?php echo $response['record']['receiver_country']; ?>");
        areaChange($("#country").val(), 0, url, function () {
            $("#province").val("<?php echo $response['record']['receiver_province']; ?>");
            areaChange($("#province").val(), 1, url, function () {
                $('#city').val("<?php echo $response['record']['receiver_city']; ?>");
                areaChange($("#city").val(), 2, url, function () {
                    $('#district').val("<?php echo $response['record']['receiver_district']; ?>");
                    areaChange($("#district").val(), 3, url, function () {
                        $('#street').val("<?php echo $response['record']['receiver_street']; ?>");
                    });
                });
            });
        });


    });
//        function show_safe_info(sell_record_code,key){
//         var url = "?app_act=oms/sell_record/get_record_key_data&app_fmt=json";
//        $.post(url,{'sell_record_code':sell_record_code,key:key},function(ret){
//            BUI.Message.Alert(ret[key],'info');
//        },'json');
//    }
        //解密
    function show_safe_info(obj,sell_record_code,key){
       var url = "?app_act=oms/sell_record/get_record_key_data&app_fmt=json";
        $.post(url,{'sell_record_code':sell_record_code,key:key},function(ret){
            if(ret[key]==null){
                 BUI.Message.Tip('解密出现异常！', 'error');
                return ;
            }
            $(obj).html(ret[key]);
            $(obj).attr('onclick','');
            $(obj).removeClass('like_link');
       },'json');
}
</script>
<?php //endif;  ?>

