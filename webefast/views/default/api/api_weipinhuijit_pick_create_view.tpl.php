<style>
    .page_container{padding:0;}
    #weipinhui .t_right{text-align: right;}
    #custom_name{width:160px};
</style>
<table cellspacing="0" class="table" style="border:solid 1px #dddddd;" id="weipinhui">
    <tr><td colspan="4"><strong>唯品会信息</strong></td></tr>
    <tr>
        <td width="16%" class="t_right">JIT出库单：</td>
        <td width="34%">
            <select name="delivery_id" id="delivery_id">
                <?php foreach ($response['delivery'] as $k => $v) { ?>
                    <option value="<?php echo $v['delivery_id']; ?>"><?php echo $v['delivery_id'] ?></option>
                <?php } ?>
                <option value="">新建JIT出库单</option>
            </select>
        </td>
        <td width="14%" class="t_right">送货仓库：</td>
        <td width="34%">
            <input type="hidden" name="warehouse_code" id="warehouse_code" value="<?php echo $response['pick']['warehouse_code']; ?>">
            <input type="text" id="warehouse_name" value="<?php echo $response['pick']['warehouse_name']; ?>" disabled="disabled">
        </td>
    </tr>
    <tr>
        <td class="t_right">配送模式：</td>
        <td>
            <select name="delivery_method" id="delivery_method">
                <option value="">请选择</option>
                <option value="1" <?php echo $response['delivery'][0]['delivery_method'] == 1 ? 'selected' : '' ?>>汽运</option>
                <option value="2" <?php echo $response['delivery'][0]['delivery_method'] == 2 ? 'selected' : '' ?>>空运</option>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">品牌：</td>
        <td>
            <select name="brand_code" id="brand_code">
                <option value="">请选择</option>
                <?php foreach ($response['brand'] as $k => $v) { ?>
                    <option value="<?php echo $v['brand_code'] ?>" <?php echo $response['delivery'][0]['brand_code'] == $v['brand_code'] ? 'selected' : ''; ?>><?php echo $v['brand_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">要求到货时间：</td>
        <td >
            <input  id="arrival_time"  style="width:100px;" type="text" value="<?php echo isset($response['delivery'][0]['arrival_time']) ? $response['delivery'][0]['arrival_time'] : date('Y-m-d', strtotime('2day')); ?>" name="arrival_time" data-rules="{required : true}" class="calendar">
            <select name="time_slot" id="time_slot" style="display:none;width:80px;">

            </select>
            <span style="color:red">*</span>
            <label id="history_time"></label>
        </td>
        <td></td>
        <td></td>
    </tr>
    <tr><td colspan="4"><strong>批发信息</strong></td></tr>
    <tr>
        <td class="t_right">批发通知单：</td>
        <td >
            <select name="notice_code" id="notice_code">
                <?php foreach ($response['notice'] as $k => $v) { ?>
                    <option value="<?php echo $v; ?>"><?php echo $v ?></option>
                <?php } ?>
                <option value="">新建批发通知单</option>
            </select>
        </td>
        <td class="t_right">分销商：</td>
        <td>
            <input type="text" id="custom_name" name="custom_name" readonly="readonly" value="">
            <input type="hidden" id="distributor_code" name="distributor_code" value="">
            <a href="#"><img src="assets/img/search.png" id="custom_search"></a>
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr>
        <td class="t_right">出库仓库：</td>
        <td>
            <select name="store_code" id="store_code">
                <option value="" class="choosecheck">请选择</option>
                <?php
                $list = oms_tb_all('base_store', array());
                foreach ($list as $k => $v) {
                    ?>
                    <option value="<?php echo $v['store_code'] ?>" <?php echo!empty($response['shop']['store_code']) && $response['shop']['store_code'] === $v['store_code'] ? 'selected' : '' ?> ><?php echo $v['store_name'] ?></option>
                <?php } ?>
            </select>
            <span style="color:red">*</span>
        </td>
        <td class="t_right">配送方式：</td>
        <td>
            <select name="express_code" id="express_code">
                <option value="">请选择</option>
                <?php
                $list = oms_tb_all('base_express', array('status' => 1));
                foreach ($list as $k => $v) {
                    ?>
                    <option value="<?php echo $v['express_code'] ?>" <?php echo!empty($response['shop']['express_code']) && $response['shop']['express_code'] === $v['express_code'] ? 'selected' : '' ?> ><?php echo $v['express_name'] ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td class="t_right">快递单号：</td>
        <td>
            <input type="text" name="express" id="express"/>
        </td>
        <td class="t_right">手机号：</td>
        <td>
            <input id="tel" type="text"  name="tel" data-rules="{required : true}">
            <span style="color:red">*</span>
        </td>
    </tr>
    <tr><td colspan="4"><strong>价格信息</strong></td></tr>
    <tr>
        <td class="t_right">销货商品价格：</td>
        <td colspan="3" id="price">
            <label class="radio" ><input type="radio" <?php if($response['supply_price']==0){?>checked="checked" <?php }?>name="price" value="actual_unit_price" id="actual_unit_price">供货价（不含税，默认）</label>
            <label class="radio" style="margin-left:20px;"><input type="radio" name="price" <?php if($response['supply_price']==1){?>checked="checked" <?php }?> value="actual_market_price" id="actual_market_price">供货价（含税）</label>
        </td>
    </tr>
</table>
<div class="clearfix" style="text-align: center;">
    <button class="button button-primary" id="ok">确定</button>
    <input type="hidden" name = "weipinhui_v" id="weipinhui_v" value=""/>
</div>

<script>
    var field = ['delivery_method', 'arrival_time', 'brand_code', 'time_slot'];
    var jit_version = "<?php echo isset($request['jit_version']) ? $request['jit_version'] : 1 ?>";
    var default_custom_code="<?php echo $response['pick']['custom_code'] ?>";
    var default_custom_name="<?php echo $response['custom_name'] ?>";
    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function(value) {
            var custom_code = value[0]['custom_code'];
            var custom_name = value[0]['custom_name'];
            var mobile= value[0]['mobile'];
            $('#custom_name').val(custom_name);
            $('#tel').val(mobile);
            $('#distributor_code').val(custom_code);
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };
    BUI.use('bui/calendar', function (Calendar) {
        var datepicker = new Calendar.DatePicker({
            trigger: '.calendar',
            showTime: false,
            autoRender: true
        });
    });
    $(document).ready(function () {
        get_info();
        get_notice_info();
        get_tel(1);
        $("#ok").click(function () {
            $("#ok").html("正在生成，请稍后。。。");
            $("#ok").attr("disabled", "disalbed");
            var store_code = $("#store_code").val();
            var delivery_method = $("#delivery_method").val();
            var express = $("#express").val();
            var express_code = $("#express_code").val();
            var distributor_code = $("#distributor_code").val();
            var warehouse = $("#warehouse_code").val();
            var brand_code = $("#brand_code").val();
            var arrival_time = $("#arrival_time").val();
            var time_slot = $('#time_slot').val();
            var tel = $("#tel").val();
            if (!store_code || !delivery_method || !distributor_code || !warehouse || !brand_code || !tel || !arrival_time) {
                BUI.Message.Alert('请将必填项填写完整', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            } else if (!time_slot && $("#delivery_id").val() == '') {
                BUI.Message.Alert('请将必填项填写完整', 'error');
                $("#ok").html("确定");
                $("#ok").removeAttr("disabled");
            } else {
                var params = {
                    "pick_id": '<?php echo $request['pick_id']; ?>',
                    "store_code": store_code,
                    "delivery_method": delivery_method,
                    "notice_code": $("#notice_code").val(), //通知单
                    "express": express, //快递单号
                    "express_code": express_code, //配送方式
                    "distributor_code": distributor_code,
                    "delivery_id": $("#delivery_id").val(),
                    "warehouse": warehouse,
                    "brand_code": brand_code,
                    "arrival_time": arrival_time + ' ' + time_slot,
                    "tel": tel,
                    "price_type": $('#price input[name="price"]:checked ').val(),
                    "app_fmt": "json",
                    "jit_version": jit_version
                };

                $.post("?app_act=api/api_weipinhuijit_pick/do_create", params, function (data) {
                    var type = data.status == 1 ? 'success' : 'error';
                    if (type == 'error') {
                        $("#ok").html("确定");
                        $("#ok").removeAttr("disabled");
                        BUI.Message.Alert(data.message, 'error');
                    } else {
                        $("#ok").html("确定");
                        $("#ok").removeAttr("disabled");
                        BUI.Message.Alert(data.message, function () {
                            ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        }, type);
                    }
                }, "json");
            }
        });
    });

    $('#notice_code').change(function () {
        if ($('#notice_code').val() == '') {
            $("#notice_code option").each(function (i, o) {
                if (i == 1) {
                    $("#distributor_code option[value='']").attr('selected', 'selected');
                    $("#store_code").val('<?php echo $response['shop']['store_code']; ?>');
                }
            });
        }
    });
    $('#custom_search').click(function(){
        selectPopWindowcustom_code.dialog = new ESUI.PopSelectWindow('?app_act=api/api_weipinhuijit_pick/custom_search', 'selectPopWindowcustom_code.callback', {title: '选择分销商', width: 680, height:480 ,ES_pFrmId:'<?php echo $request['ES_frmId'];?>' }).show();
    })

    $('#delivery_id').change(function () {
        get_info();
    });
    $("#notice_code").change(function () {
        get_notice_info();
    });
    $('#distributor_code').change(function () {
        get_tel(0);
    });

    function get_info() {
        var delivery_id = $("#delivery_id").val();
        if (delivery_id != '') {
            $('#chose_po_no').css('display', 'none');
            $('#clean').css('display', 'none');
            var params = {"delivery_id": delivery_id, "app_fmt": "json"};
            $.post("?app_act=api/api_weipinhuijit_delivery/get_info", params, function (data) {
                $('#delivery_method').val(data['delivery_method']);
                $('#delivery_method').change();
                $.each(field, function (key, val) {
                    $("#" + val).val(data[val]);
                    $("#" + val).attr('disabled', 'disabled');
                });
                $('#time_slot').css('display', '');

                $("#express").val(data.express);
                $("#tel").val(data.driver_tel);
                $("#express_code").find("option[value='" + data.express_code + "']").attr("selected", true);
            }, "json");

        } else {
            if (jit_version == 2) {
                $('#chose_po_no').css('display', '');
                $('#clean').css('display', '');
            } else {
                $('#chose_po_no').css('display', 'none');
                $('#clean').css('display', 'none');
            }

            $.each(field, function (key, val) {
                $("#" + val).removeAttr('disabled');
                $("#" + val).val('');
            });
            $('#time_slot').css('display', 'none');
            $('#arrival_time').val(get_date());
            $('#express_code').val('');
            $('#express').val('');
            $('#tel').val('');

        }
    }

    function get_date() {
        var datetime = new Date();
        var year = datetime.getFullYear();
        var month = datetime.getMonth() + 1 < 10 ? "0" + (datetime.getMonth() + 1) : datetime.getMonth() + 1;
        var day = datetime.getDate() < 10 ? "0" + datetime.getDate() : datetime.getDate();
        return year + "-" + month + "-" + day;
    }

    function get_notice_info() {
        var notice_code = $("#notice_code").val();
        if (notice_code != '') {
            var params = {"notice_code": notice_code,
                "app_fmt": "json"};
            $.post("?app_act=api/api_weipinhuijit_pick/get_notice_info", params, function (data) {
                $("#custom_name").val( data.custom_name );
                $("#distributor_code").val( data.distributor_code );
                $("#store_code").find("option[value='" + data.store_code + "']").attr("selected", true);
                $("#tel").val(data.tel);
            }, "json");
        }else{
            $("#custom_name").val( default_custom_name );
            $("#distributor_code").val( default_custom_code );
            if(default_custom_name!=''){
                get_tel(0);
            }else{
                $("#tel").val('');
            }
        }
    }

    //联系人电话默认显示分销商中维护的电话
    //_type=1,加载页面时触发，=0，更改分销商触发
    function get_tel(_type) {
        var distributor_code = $('#distributor_code').val();
        var tel = $('#tel').val();
        if (distributor_code == '' || tel != '' && _type == 1) {
            return;
        }
        $.post('?app_act=base/custom/get_custom_by_code', {custom_code: distributor_code}, function (ret) {
            if (ret.status == 1) {
                $('#tel').val(ret.data['mobile']);
            }
        }, 'json');
    }

    //更改配送模式加载其对应的到货时间
    $('#delivery_method').change(function () {
        if ($(this).val() == '') {
            $('#time_slot').css('display', 'none');
            return;
        }
        var obj = $('#time_slot');
        if ($(this).val() == 1) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="10:00:00">10:00:00</option><option value="16:00:00">16:00:00</option><option value="20:00:00">20:00:00</option><option value="22:00:00">22:00:00</option><option value="23:59:00">23:59:00</option>');
        }
        if ($(this).val() == 2) {
            obj.html('<option value="">请选择</option><option value="09:00:00">09:00:00</option><option value="16:00:00">16:00:00</option><option value="18:00:00">18:00:00</option><option value="20:00:00">20:00:00</option><option value="23:59:00">23:59:00</option>');
        }
        $('#time_slot').css('display', '');
    });

    //更新系统参数
    $("#price").change(function () {
        var check_id = $('#price input[name="price"]:checked ').attr('id');
        var supply_price = (check_id == 'actual_unit_price') ? 0 : 1;
        update_sys_params(supply_price);
    });

    function update_sys_params(supply_price) {
        var params = {
            "value": supply_price,
            "app_fmt": "json"
        };
        $.post("?app_act=api/api_weipinhuijit_pick/update_price_params", params, function (data) {
            if (data.status != 1) {
                BUI.Message.Alert('更新系统参数失败!', 'error');
            }
        }, "json");
    }
</script>