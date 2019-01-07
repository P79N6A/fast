<?php echo load_js('jquery.cookie.js') ?>
<style>
    .my-div {
        position: relative;
        padding: 10px 0px 0px 20px;
        width: auto;
        line-height: 55px;
    }
    .my-lab {
        font-size: 20px;
    }
    .my-lab-left {
        font-size: 20px;
        margin-left: 60px;
    }
    .my-input {
        font-weight: bold;
        font-size: 25px;
    }
</style>

<?php
render_control('PageHead', 'head1', array(
    'title' => '扫描验货(波次单)',
    'ref_table' => 'table'
));
?>

<div class="my-div">
    <div >
        <label class="my-lab">自动打印物流单</label>
        <input name="auto_print_express" id="auto_print_express" disabled="disabled" type="checkbox" checked >
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label class="my-lab" style="color: red">温馨提醒：只支持菜鸟4期</label>
    </div>
    <div >
        <label class="my-lab">波次单号:</label>
        <input style="height: 30px; width: 300px;" class="my-input" type="text" id="waves_record">
        <label class="my-lab-left" type="hidden" name="lab_num" id="lab_num">
    </div>
    <div>
        <label class="my-lab">商品条码:</label>
        <input style="height: 30px; width: 300px;" class="my-input" type="text" id="goods_barcode">
        <label class="my-lab-left" style="color: green; font-weight: bold;" type="hidden" name="sort_no" id="sort_no">
    </div>
</div>

<input id="deliver_record_id_input" name="deliver_record_id_input" type="hidden" />
<input id="waves_record_id_input" name="waves_record_id_input" type="hidden" />

<div class="my-div" id="detail">
    <br>
</div>

<div class="my-div" id="record">
    <br>
</div>

<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>

<iframe src="" id="print_iframe" style="width:100px;height:100px;border:0px red solid;visibility: hidden;" scrolling="no"></iframe>

<script type="text/javascript">
    var shipped_num = 0;
    var valid_num = 0;
    var refund_num = 0;
    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    //播放提示音
    function play_sound(typ) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[typ] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {
            document.getElementById('bgsound_ie').src = wav;
        } else {
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }

    $(document).ready(function () {
        $("#waves_record").focus();
        $("#goods_barcode").attr("disabled", true);

        //波次单号扫描
        $("#waves_record").keyup(function (event) {
            $("#lab_num").html("");
            clear_info();
            if (event.keyCode == 13) {
                var url = "?app_act=oms/waves_record_scan/check_waves_record";
                var param = {waves_record: $(this).val()}
                $.post(url, param, function (data) {
                    if (data.status == -1) {
                        messageBox("waves_record", data.message);
                        play_sound("error");
                    } else {
                        var result = data.data;
                        document.getElementById("lab_num").style.display = "inline";
                        shipped_num = result.shipped_num;
                        valid_num = result.valid_num;
                        refund_num = result.refund_num;
                        $("#lab_num").text("已发货订单数/有效订单数："+result.shipped_num+"/"+result.valid_num+" "+"退单数："+result.refund_num);

                        $("#waves_record").attr("disabled", true);
                        $("#goods_barcode").removeAttr("disabled");
                        $("#goods_barcode").focus();
                        play_sound("success");
                    }
                }, "json");
            }
        });
        //商品条码扫描
        $("#goods_barcode").keyup(function (event) {
            clear_info();
            if (event.keyCode == 13) {
                var url = "?app_act=oms/waves_record_scan/check_goods_barcode";
                var params = {goods_barcode: $(this).val(),waves_record: $("#waves_record").val()};
                $.post(url, params, function (data) {
                    if (data.status == -1) {
                        messageBox("goods_barcode", data.message);
                        play_sound("error");
                    } else {
                        document.getElementById("sort_no").style.display = "inline";
                        var result = data.record_info;
                        $("#sort_no").text("栏位号(序号)："+result.sort_no);
                        $("#deliver_record_id_input").val(result.deliver_record_id);
                        $("#waves_record_id_input").val(result.waves_record_id);

                        var result_detail = data.detail_info;
                        var str = "<table cellspacing='0' class='table table-bordered' ><tr><td>商品名称</td><td>商品编码</td><td>规格1</td><td>规格2</td><td>商品条码</td><td>商品数量</td></tr>";

                        for (var i = 0; i < result_detail.length; i++) {
                            str += "<tr><td>"+result_detail[i].goods_name+"</td><td>"+result_detail[i].goods_code+"</td><td>"+result_detail[i].spec1_name+"</td><td>"+result_detail[i].spec2_name+"</td><td>"+result_detail[i].barcode+"</td><td>"+result_detail[i].num+"</td></tr>";
                            if(parseInt(result_detail[i].num) != parseInt(result_detail[i].scan_num)){
                                var new_num = parseInt(result_detail[i].scan_num) + 1;
                                $("#sku_scan_num_"+result_detail[i].deliver_record_detail_id).html(new_num);
                                update_num(result_detail[i].deliver_record_detail_id);
                            }
                        }
                        str += "</table>";
                        $("#detail").html(str);

                        var str_record = "<table cellspacing='0' class='table table-bordered' ><tr><td width='10%' align='right'>订单编号：</td><td>"+result.sell_record_code+"</td><td width='10%' align='right'>下单时间：</td><td width='40%'>"+result.record_time+"</td></tr><tr><td width='10%' align='right'>收货人：</td><td width='40%'>"+result.receiver_name+"</td><td width='10%' align='right'>电话：</td><td width='40%'>"+result.receiver_phone+"</td></tr><tr><td width='10%' align='right'>手机：</td><td width='40%'>"+result.receiver_mobile+"</td><td width='10%' align='right'>邮编：</td><td width='40%'>"+result.receiver_zip_code+"</td></tr><tr><td width='10%' align='right'>配送方式：</td><td width='40%' id='express_name' >"+result.express_name+"</td><td width='10%' align='right'>物流单号：</td><td id='express_no_add' width='40%'>"+result.express_no+"</td></tr><tr><td width='10%' align='right'>地址：</td><td width='40%'>"+result.receiver_address+"</td><td width='10%' align='right'>订单备注：</td><td width='40%'>"+result.order_remark+"</td></tr><tr><td width='10%' align='right'>买家留言：</td><td width='40%'>"+result.buyer_remark+"</td><td width='10%' align='right'>商家备注：</td><td width='40%'>"+result.seller_remark+"</td></tr><tr><td width='10%' align='right'>仓库留言：</td><td width='40%' colspan='3'>"+result.store_remark+"</td></tr></table>";                                               
                        $("#record").html(str_record);

                        get_waybill(0, 2, result.sort_no);
                        play_sound("success");
                    }
                }, "json");
            }
        });

        //更新扫描数量
        function update_num(deliver_record_detail_id) {
            var scan_num = 1;
            var url = '?app_act=oms/deliver_record/update_goods_scan_num';
            var params = {app_fmt: 'json', deliver_record_detail_id: deliver_record_detail_id,scan_num:scan_num};
            $.post(url,params,function(data){
                if(data.status != 1) {
                    messageBox("sku_scan_num_"+deliver_record_detail_id, data.message);
                }
            },'json')
        }

        function get_waybill(type, print_type, sort_no){
            $.post('?app_act=oms/waves_record_scan/cancel_express_no', {deliver_record_id: $("#deliver_record_id_input").val()}, function(data) {
                if(data.status == 1){
                    $.post('?app_act=oms/deliver_record/tb_wlb_waybill_get', {waves_record_id: $("#waves_record_id_input").val(),record_ids: $("#deliver_record_id_input").val(), type: type, print_type: print_type}, function(data) {
                        if(data.status == 1) {
                            params = {is_record: 0, deliver_record_id: $("#deliver_record_id_input").val()}
                            $.post("?app_act=oms/deliver_record/check_action", params, function (data) {
                                if (data.status != 1) {
                                    messageBox("goods_barcode", data.message);
                                } else {
                                    shipped_num++;
                                    $("#sort_no").text("栏位号(序号)："+sort_no+" "+"扫描发货成功，请继续！");
                                    $("#lab_num").text("已发货订单数/有效订单数："+shipped_num+"/"+valid_num+" "+"退单数："+refund_num);

                                    params = {deliver_record_id: $("#deliver_record_id_input").val()}
                                    $.post("?app_act=oms/waves_record_scan/get_express_no", params, function (result) {
                                        $("#express_no_add").html(result.data);
                                        if($("#auto_print_express").attr("checked")){
                                            action_print_express();
                                        }
                                    }, "json");
                                    $.post('?app_act=oms/waves_record_scan/get_wave_record_is_shipped', {waves_record_id: $("#waves_record_id_input").val()}, function(ret) {
                                        if(ret.data == 1){
                                            $("#goods_barcode").val("");
                                            $("#goods_barcode").attr("disabled", true);
                                            if(valid_num == shipped_num) {
                                                $("#waves_record").removeAttr("disabled");
                                                $("#waves_record").val("");
                                                $("#waves_record").focus();
                                            }
                                        } else {
                                            $("#goods_barcode").val("");
                                            $("#goods_barcode").focus();
                                        }
                                    }, "json");
                                }
                            }, "json");
                        } else {
                            var msg = data.message;
                            $.each(data.data,function(i,k){
                                msg+=k;
                            });
                            messageBox("goods_barcode", msg);
                        }
                    }, "json");
                }

            }, "json");
        }

        var p_time = 0;
        var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
        function action_print_express(){
            var id = "print_express"+p_time;
            var deliver_record_id = $("#deliver_record_id_input").val();
            var waves_record_id = $("#waves_record_id_input").val();
            var print_express_name = $('#express_name').text(); //现在打印的快递名称
            var param = '&print_type=cainiao_print' + '&deliver_record_ids=' + deliver_record_id + '&waves_record_ids=' + waves_record_id + '&print_express_name=' + print_express_name + "&print_ty=HZ";

            /*if(new_clodop_print == 1){
                new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&deliver_record_ids=" + deliver_record_id + "&waves_record_ids="+waves_record_id + "&is_print_express=1" + "&frame_id=" + id, {
                    title: "快递单打印",
                    width: 500,
                    height: 220,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                    }
                }).show()
            } else {*/
                var url = "?app_act=oms/deliver_record/print_express&wave_print=0&iframe_id="+id+param;
                var iframe = $('<iframe id="'+id+' width="0" height="0"></iframe>').appendTo('body');
                iframe.attr('src',url);
//            }
        }

        function messageBox(input_code, m) {
            BUI.use('bui/overlay', function (Overlay) {
                var msg = '<div style="padding-left:15px; color: red"><h2>' + m + '</h2><p class="auxiliary-text" style="padding-top:10px; padding-left:10px;"><input type="text" class="msg_code" value="" style="padding-left:10px; width:300px;" placeholder="请扫描错误确认码，如CONFIRM，以确认此错误"></p><p style="padding-top:10px; padding-left:10px;">提示：如没有错误确认码，请到<a href="javascript:messageErr();">错误确认码</a>中打印以供扫描</p></div>';

                var dialog = new Overlay.Dialog({
                    title: '扫描错误',
                    width: 500,
                    height: 210,
                    bodyContent: msg, //配置DOM容器的编号
                    buttons: [{
                        text: '确定',
                        elCls: 'button button-primary',
                        handler: function () {
                            //do some thing
                            this.close();
                        }
                    }
                    ]
                });

                dialog.show();

                play_sound("error");

                dialog.on("closed", function (event) {
/*                    if ($("#goods_barcode").attr("disabled") == 'disabled') {
                        $("#waves_record").val("");
                        $("#waves_record").focus();
                    } else {
                        $("#goods_barcode").val("");
                        $("#goods_barcode").focus();
                    }*/
                    $("#"+input_code).val("");
                    $("#"+input_code).focus();
                    dialog.close();
                })

                $(".msg_code").val("");
                $(".msg_code").focus();
                $(".msg_code").keyup(function (event) {
                    if (event.keyCode == 13) {
                        if ($(this).val() == 'CONFIRM') {
/*                            if ($("#goods_barcode").attr("disabled") == 'disabled') {
                                $("#waves_record").val("");
                                $("#waves_record").focus();
                            } else {
                                $("#goods_barcode").val("");
                                $("#goods_barcode").focus();
                            }*/
                            $("#"+input_code).val("");
                            $("#"+input_code).focus();
                            dialog.close()
                        }
                    }
                });
            });
        }

        function clear_info() {
            $("#record").html("");
            $("#detail").html("");
            $("#sort_no").html("");
        }

    });
    function messageErr() {
        var msgUrl = "?app_act=base/error_confirm_code/do_list"
        openPage(window.btoa(msgUrl), msgUrl, "错误确认码")
    }
</script>
