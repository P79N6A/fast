<?php echo load_js('jquery.cookie.js') ?>

<style>
    .offset3 .span6{ font-size:16px; font-weight:bold;}
    .offset3 .span6 span{ color:#DC0404;}
    .green{color: #090; font-weight: bold; }
</style>
<style>
    .panel{height: 40px; line-height: 40px;}
    #deliver_detail {padding: 0}
    .panel-body{padding-top: 1px;}
    .panel-body table {margin: 0; }
    form.form-horizontal {
        position: relative;
        padding: 5px 0px 18px;
        overflow: hidden;
    }
    .form-horizontal .control-label {width: auto;}
    .span8 { width: auto; }
    .button.active{color:#FFF;background-color:#ec6d3a;border-color:#ec6d3a;}
    #express_no{width: 320px;height: 30px;}
    #barcode{width:320px;height: 30px; }
    .control-text{font-size: 25px;}
    .spant{margin-right: 9px;}
    #scan_num,#diff_num,#count_num{font-size: 20px;}
    .result{height: 48px;}
</style>

<?php
render_control('PageHead', 'head1', array('title' => '扫描验货',
    'links' => array(
        array('url' => 'sys/params/do_list', 'title' => '扫描声音设置', 'is_pop' => false, 'pop_size' => '500,400',),
    ),
    'ref_table' => 'table'
));
?>

<bgsound loop="false" autostart="false" id="bgsound_ie" src="" />
<audio controls="controls" id="bgsound_others" style="display:none;" src=""></audio>

<form class="form-horizontal">
    <div class="panel">
        <div class="panel-body">
            <div id="searchAdv">
                <div class="row">
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:1px">扫描物流单后直接发货（受系统参数控制）</label>
                        <div class="controls">
                            <div class="button-group" id="b1" style="margin: 1px 0;">
                            </div>
                        </div>
                    </div>
                    <div class="control-group span8">
                        <label class="control-label" style="padding-top:1px">校验物流单是否重复</label>
                        <div class="controls">
                            <div class="button-group" id="b2" style="margin: 1px 0;">
                            </div>
                        </div>
                    </div>
<?php if ($response['lof_status'] == 1): ?>
                        <div class="control-group span8">
                            <label class="control-label" style="padding-top:1px">批次选择</label>
                            <div class="controls">
                                <div class="button-group" id="b3" style="margin: 1px 0;">
                                </div>
                            </div>
                        </div>
<?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-body">
            <div class="row">
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">物流单号:</label>
                    <div class="controls">
                        <input type="text" class="control-text" style="height:40px;font-weight:bold;font-size:30px;" id="express_no">
                    </div>
                </div>
                <div class="control-group span8 spant">
                    <label class="control-label" style="height: 46px;line-height: 46px;">商品条码:</label>
                    <div class="controls">
                        <input type="text" class="control-text" style="height:40px;font-weight:bold;font-size:30px;" id="barcode">
                    </div>
                </div>
                <div class="control-group span8">
                    <div class="controls result" style=" height: 50px">
                        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/deliver_record/check#check_button')): // 检查权限配置 ?>
                            <button type="button" class="button" id="btn-submit">直接发货</button>
<?php endif; ?>
                        <button type="button" class="button" id="btn-clear">清除扫描记录</button>
                        <div id="msg" style="color: #ff0000; font-weight: bold;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>
<input id="sell_record_code_input" name="sell_record_code" type="hidden" />

<div class="panel">
    <div class="panel-body" id="deliver_detail">
        <br>
    </div>
</div>

<script type="text/javascript">
    var noScanSku = 0;
    var noIsDuplicate = 0;
    var lof_no_select = 0;
    var dialog;
    var barcode_scan_ok = '';
    var sounds = {
        "error": "<?php echo $response['sound']['error'] ?>",
        "success": "<?php echo $response['sound']['success'] ?>"
    };
    //var unique_barcode = new Array();
    var unique_status = "<?php echo $response['unique_status']; ?>";
    var selectable = "<?php echo $response['selectable']; ?>";//控制扫描后直接发货
    var deliver_record_direct_ship = "<?php echo $response['deliver_record_direct_ship']; ?>";//控制扫描后直接发货
    var unique_flag = 0
    //播放提示音
    function play_sound(typ) {
        var wav = "<?php echo CTX()->get_app_conf('common_http_url'); ?>js/sound/" + sounds[typ] + ".wav";
        if (navigator.userAgent.indexOf('MSIE') >= 0) {//IE
            document.getElementById('bgsound_ie').src = wav;
        } else {// Other borwses (firefox, chrome)
            var obj = document.getElementById('bgsound_others');
            obj.src = wav;
            obj.play();
        }
    }
    var g1, g2;
    $(document).ready(function () {
        $("#barcode").attr("disabled", true)
        $("#btn-submit").attr("disabled", true)
        $("#btn-clear").attr("disabled", true)
        $("#express_no").focus()

        BUI.use('bui/toolbar', function (Toolbar) {
            //不扫条码直接发货
            g1 = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: selectable //允许选中
                },
                children: [
                    {content: '是', id: '1',},
                    {content: '否', id: '0', selected: true,}
                ],

                render: '#b1'
            })



            g1.render();
            g1.on('itemclick', function (ev) {
                noScanSku = ev.item.get('id')
            })




            //校验物流单是否重复
            g2 = new Toolbar.Bar({
                elCls: 'button-group',
                itemStatusCls: {
                    selected: 'active' //选中时应用的样式
                },
                defaultChildCfg: {
                    elCls: 'button button-small',
                    selectable: true //允许选中
                },
                children: [
                    {content: '校验', id: '1', selected: true},
                    {content: '不校验', id: '0'}
                ],
                render: '#b2'
            })

            g2.render();

            g2.on('itemclick', function (ev) {
                noIsDuplicate = ev.item.get('id')
            })
<?php if ($response['lof_status'] == 1): ?>
                //是否允许批次选择
                g3 = new Toolbar.Bar({
                    elCls: 'button-group',
                    itemStatusCls: {
                        selected: 'active' //选中时应用的样式
                    },
                    defaultChildCfg: {
                        elCls: 'button button-small',
                        selectable: true //允许选中
                    },
                    children: [
                        {content: '允许', id: '1', selected: true},
                        {content: '不允许', id: '0'}
                    ],
                    render: '#b3'
                })
                lof_no_select = 1;
                g3.render();

                g3.on('itemclick', function (ev) {
                    lof_no_select = ev.item.get('id')
                })
<?php endif; ?>
        })

        // 自动扫描发货
        $("#express_no").keyup(function (event) {
            if (event.keyCode == 13) {
                unique_flag = 0;
                set_bar_status(false);
                //unique_barcode.splice(0,unique_barcode.length);
                var p = {express_no: $(this).val(), no_is_duplicate: noIsDuplicate}
                $.post("?app_act=oms/deliver_record/check_detail", p, function (data) {
                    if (data.status == 1) {

                        document.getElementById('sell_record_code_input').value = $(data.data).find('#sell_record_code').html();
                        $("#deliver_detail").html(data.data)
                        $("#express_no").attr("disabled", true)
                        $("#btn-submit").removeAttr("disabled")
                        $("#btn-clear").removeAttr("disabled")
                        play_sound("success")
                        //$("#msg").html("")
                        if (noScanSku == 1) {
                            var check = false;
                            submit_it(check);
                        } else {
                            $("#barcode").removeAttr("disabled")
                            $("#barcode").focus()
                        }

                        //返回订单明细

                    } else {
                        messageBox(data.message)
                        $("#express_no").val("")
                    }
                }, "json")
            }
        })



        //get_sku_by_sub_barcode
        $("#barcode").keyup(function (event) {
            if (event.keyCode == 13) {
                var b = $(this).val().trim();
                var deliverRecordDetailID = barcode_check(b)
                if (deliverRecordDetailID == 0) {
                    // try to find sub barcode.
                    $.post("?app_act=oms/deliver_record/get_sku_by_sub_barcode", {deliver_record_id: $("#deliver_record_id").val(), sub_barcode: b}, function (data) {
                        if (data.status == 1) {
                            $.each(data.data, function (i, val) {
                                deliverRecordDetailID = barcode_check(val.sku, i, data.length, data.unique_flag, b)
                                if (deliverRecordDetailID > 0) {
                                    return false;
                                }
                            });
                            if (deliverRecordDetailID > 0) {
                                _submit_it(deliverRecordDetailID, b)
                            }
                        } else {
                            messageBox(data.message, 'error')
                        }
                    }, "json")
                } else {
                    if (deliverRecordDetailID > 0) {
                        _submit_it(deliverRecordDetailID, b)
                    }
                }
            }
        })

        //
        $("#btn-clear").click({is_record: 1}, scan_clear_func);

        function scan_clear_func(event) {
            var params = {is_record: event.data.is_record, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
            $.post("?app_act=oms/deliver_record/scan_clear", params, function (data) {
                if (data.status == -1) {
                    BUI.Message.Alert('清除失败！','error');
                }
            }, "json");
            set_bar_status(true);
            $("#deliver_detail").html("")
            $("#barcode").val("")
            $("#express_no").val("")
            $("#express_no").removeAttr("disabled")

            $("#barcode").attr("disabled", true)
            $("#btn-submit").attr("disabled", true)
            $("#btn-clear").attr("disabled", true)
            $("#express_no").focus();
        }

        //
        $("#btn-submit").click(function () {
            var check = true;
            submit_it(check, 1);
        })


        function _submit_it(deliverRecordDetailID, b) {
            var params = {deliver_record_id: $("#deliver_record_id").val(), deliver_record_detail_id: deliverRecordDetailID, barcode: b}
            $.post("?app_act=oms/deliver_record/scan_detail", params, function (data) {
                if (data.status != 1) {
                    messageBox(data.message)
                } else {
                    var check = false;
                    if (lof_no_select == 1 && barcode_scan_ok != '') {
                        check_lof_data(barcode_scan_ok);
//                    if ($('#count_num').text() === $('#scan_num').text() && $('#count_num').text() != 1) {
//                        submit_it(check);
//                    }
                    } else {
                        submit_it(check);
                    }
                }
            }, "json");
        }

        function insert_unique_barcode(unique_code) {
            var sell_record_code = $('#sell_record_code').text();
            var url = "?app_act=oms/deliver_record/insert_unique_barcode";
            var param = {app_fmt: 'json', record_code: sell_record_code, unique_code: unique_code};
            $.ajax({
                type: "GET",
                url: url,
                async: false,
                data: param,
                success: function (json_data) {
                }
            });
        }

        function barcode_check(iSku, i = 0, length = 0, flag = 0, b = ''){
            var deliverRecordDetailID = 0;
            var isFind = false;
            var isOK = false;
            $("#table1").find("tbody").find("tr").find(".scan_num").removeClass("green");
            $("#table1").find("tbody").find("tr").each(function (index, item) {
                var vSku = $(item).find(".sku").text();
                var vBarcode = $(item).find(".barcode").text();
                var deliver_record_detail_id = $(item).find(".deliver_record_detail_id").text();
                var vNum = parseInt($(item).find(".num").text());
                var vScanNum = parseInt($(item).find(".scan_num").text());
                if (vBarcode == iSku || vSku == iSku) {
                    isFind = true;
                    if (vScanNum < vNum) {
                        if (flag == 1) {
                            unique_flag = 1;//标记为 唯一码
                            insert_unique_barcode(b);
                        }
                        var new_vScanNum = vScanNum + 1
                        // $(item).find(".scan_num").text(vScanNum+1);
                        $("#sku_num_" + deliver_record_detail_id).parent().html("<span style='color:green;font-weight: bold' onclick=update_num('" + deliver_record_detail_id + "') class='sku_num' id='sku_num_" + deliver_record_detail_id + "'>" + new_vScanNum + "</span>");
                        $(item).find(".scan_num").addClass("green");
                        $("#barcode").val("");
                        $("#scan_num").html(parseInt($("#scan_num").html()) + 1);
                        $("#diff_num").html(parseInt($("#diff_num").html()) - 1);
                        $("#msg").html("扫描成功");
                        deliverRecordDetailID = $(item).find(".deliver_record_detail_id").text();
                        $("#barcode").focus();
                        barcode_scan_ok = '';
                        if (lof_no_select == 1 && (vScanNum + 1) == vNum) {
                            barcode_scan_ok = vBarcode;
                        }
                        //判断是否需要弹出窗口
                        isOK = true;
                        return false;//Breaking loop.
                    }
                }
            })
            if (length > 0) {
                if ((i + 1) == length) {
                    if (!isFind) {
                        messageBox("条码不存在")
                        return -1;
                    }
                    if (!isOK && isFind) {
                        messageBox("超出商品数量")
                        return -1;
                    }
                }
//         	if (isOK && isFind) {
//                 return false;
//             }
                //  return true;
            } else {
                if (!isOK && isFind) {
                    messageBox("超出商品数量")
                    return -1;
                }
            }

            play_sound("success")
            return deliverRecordDetailID
        }

        function submit_it(check, is_record) {
            if (is_record === undefined) {
                is_record = 0;
            }

            var isOK = false
            var obj = $("#table1").find("tbody").find("tr")
            if (obj.length > 0) {
                isOK = true
            }

            obj.each(function (index, item) {
                var vNum = parseInt($(item).find(".num").text())
                var vScanNum = parseInt($(item).find(".scan_num").text())
                if (vScanNum != vNum) {
                    isOK = false
                }
            })
            if (noScanSku == 1 || isOK || check) {
                var params = {is_record: is_record, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
                $.post("?app_act=oms/deliver_record/check_action", params, function (data) {
                    if (data.status != 1) {
                        messageBox(data.message)
                    } else {
                        if (unique_status == 1 && unique_flag == 1 && parseInt($("#scan_num").html()) == parseInt($("#count_num").html())) {
                            unique_code_log();
                        }

                        params = {is_record: 0, deliver_record_id: $("#deliver_record_id").val(), sell_record_code: $('#sell_record_code_input').val()}
                        $.post("?app_act=oms/deliver_record/scan_clear", params, function (data) {
                        }, "json");
                        set_bar_status(true);
                        $("#deliver_detail").html("")
                        $("#barcode").val("")
                        $("#express_no").val("")
                        $("#express_no").removeAttr("disabled")

                        $("#barcode").attr("disabled", true)
                        $("#btn-submit").attr("disabled", true)
                        $("#btn-clear").attr("disabled", true)
                        $("#express_no").focus();

                        $("#msg").html("发货成功, 请继续");
                        play_sound("success")
                    }
                }, "json");
            }

            //if(noScanSku == 0 && !isOK){
            //    messageBox("未完成商品扫描")
            //}
        }

        function messageErr() {
            var msgUrl = "?app_act=base/error_confirm_code/do_list";
            openPage(window.btoa(msgUrl), msgUrl, "错误确认码")
        }

        function messageBox(m) {
            BUI.use('bui/overlay', function (Overlay) {
                var msg = '<div style="text-align: center"><h2>' + m + '</h2><p class="auxiliary-text" style="padding-top:10px;"><input type="text" class="msg_code" value="" style="width:240px;" placeholder="请扫描错误确认码，如CONFIRM，以确认此错误"></p><p style="padding-top:10px;">提示：如没有错误确认码，请到<a href="javascript:openPage(window.btoa('+"'?app_act=base/error_confirm_code/do_list'"+'),'+"'?app_act=base/error_confirm_code/do_list'"+','+"'错误确认码'"+')">错误确认码</a>中打印以供扫描</p></div>';

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

                play_sound("error")

                dialog.on("closed", function (event) {
                    if ($("#barcode").attr("disabled") == 'disabled') {
                        $("#express_no").val("");
                        $("#express_no").focus();
                    } else {
                        $("#barcode").val("");
                        $("#barcode").focus();
                    }
                    dialog.close();
                })

                $(".msg_code").val("");
                $(".msg_code").focus();
                $(".msg_code").keyup(function (event) {
                    if (event.keyCode == 13) {
                        var len = $(this).val().length;
                        if ($(this).val() == 'CONFIRM' || len == 0) {
                            if ($("#barcode").attr("disabled") == 'disabled') {
                                $("#express_no").val("");
                                $("#express_no").focus();
                            } else {
                                $("#barcode").val("");
                                $("#barcode").focus();
                            }
                            dialog.close();
                        }
                    }
                });
            });
        }

        function check_lof_data(barcode) {
            var sell_record_code = $('#sell_record_code').text();
            var url = '?app_act=oms/deliver_record/get_oms_selll_recode_lof_info&app_fmt=json';
            var param = {};
            param.record_code = $('#sell_record_code').text();
            param.barcode = barcode;
            $.post(url, param, function (ret) {
                if (ret.data.results > 0) {
                    lof_select_box(ret.data.record, barcode);
                } else if (ret.data.results == 0) {
                    var check = false;
                    submit_it(check);
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, 'json');
        }


        function lof_select_box(record, barcode) {
            //(['bui/grid', 'bui/data', 'bui/form','bui/tooltip']
            BUI.use(['bui/grid', 'bui/data', 'bui/overlay'], function (Grid, Data, Overlay) {
                var title = '商品批次确认(商品条形码：' + barcode;
                $('#lof_select_box').html('<div id="result_grid"></div>');
                var dialog = new Overlay.Dialog({
                    title: title,
                    width: 500,
                    height: 250,
                    contentId: 'lof_select_box',
                    buttons: [{
                            text: '扫描下一个商品',
                            elCls: 'button button-primary',
                            handler: function () {
                                var production_date = $('input[name="select_lof"]:checked').val();
                                if (production_date != '') {
                                    var param = {};
                                    param.lof_no = $('input[name="select_lof"]:checked').attr('title');
                                    param.production_date = production_date;
                                    param.record_code = $('#sell_record_code').text();
                                    param.barcode = barcode;
                                    set_select_barcode_lof(param, this);
                                } else {
                                    this.close();
                                }
                            }
                        }
                    ]
                });
                dialog.on('closed', function (ex) {
                    store.remove();
                    grid.remove();
                    dialog.remove();
                    var check = false;
                    submit_it(check);
                });


                var columns = [
                    {title: '', dataIndex: 'lofselect', visible: 1, width: '80', renderer: function (value, obj) {
                            if (obj.select == 1) {
                                return '<input type="radio" name="select_lof" title="' + obj.lof_no + '"   value="' + obj.production_date + '" checked="checked"/>';
                            } else {
                                return '<input type="radio" name="select_lof" title="' + obj.lof_no + '"   value="' + obj.production_date + '"   value=""/>';
                            }
                        }},
                    {title: '批次号', dataIndex: 'lof_no', visible: 1, width: '100'},
                    {title: '有效期', dataIndex: 'production_date', visible: 1, width: '100'},
                    {title: '可用库存', dataIndex: 'num', visible: 1, width: '80'}
                ];

                var store = new Data.Store({
                    data: record,
                    autoLoad: true
                }),
                        grid = new Grid.Grid({
                            render: '#result_grid',
                            width: '100%', //如果表格使用百分比，这个属性一定要设置
                            height: 100,
                            columns: columns,
                            idField: 'goods_inv_id',
                            store: store
                        });

                grid.render();
                dialog.show();

            });
        }
        function set_select_barcode_lof(param, obj) {
            var url = '?app_act=oms/deliver_record/set_select_barcode_lof&app_fmt=json';
            $.post(url, param, function (ret) {
                if (ret.status > 0) {
                    $('#' + param.barcode + '_lof_no').html(param.lof_no);
                    obj.close();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, 'json');
        }

    });

    function set_bar_status(status) {
        if (deliver_record_direct_ship == 1) {
            BUI.each(g1.get('children'), function (elememt, index) {
                elememt.set('selectable', status);
            });
        }
        BUI.each(g2.get('children'), function (elememt, index) {
            elememt.set('selectable', status);
        });
    }

    function unique_code_log() {
        var sell_record_code = $('#sell_record_code').text();
        var params = {record_code: sell_record_code, record_type: 'sell_record', action_name: 'sell_out'}
        $.post("?app_act=prm/goods_unique_code/unique_code_log", params, function (data) {
            if (data.status != 1) {
                BUI.Message.Alert(data.message, 'error');
                return;
            }
        }, "json");

        return;
    }


</script>

<div id="lof_select_box"   class="hide">

</div>