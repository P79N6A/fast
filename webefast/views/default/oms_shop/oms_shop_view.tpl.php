<?php echo load_js("record_table.js", true); ?>
<?php echo load_js("tan.js", true); ?>
<style>
    .panel_wrap{ padding-top:5px;}
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .is_stock_out_row{color:red;}
    .panel-header h3.pull-left{ color:#1695ca;}
    .panel-header h3.pull-left img{ vertical-align:text-top; margin-right:7px;}
</style>

<?php
$title = "订单详情-<font color='red'>订单号：" . $response['record']['record_code'] . "</font>";
render_control('PageHead', 'head1', array('title' => $title));
?>
<div id="tag_name_type" style="display: none"></div>
<div  id="panel_status_info">

</div>

<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/fhxx_icon.png"/>基本信息</h3>
        <div class="pull-right">
            <button class="button button-small" id="btn_edit_baseinfo" disabled="disabled"><i class="icon-edit"></i>编辑</button>
            <button class="button button-small hide" id="btn_save_baseinfo"><i class="icon-ok"></i>保存</button>
            <button class="button button-small hide" id="btn_cancel_baseinfo"><i class="icon-ban-circle"></i>取消</button>
        </div>
    </div>
    <div class="panel-body" id="panel_baseinfo">

    </div>
</div>

<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/spxx_icon.png"/>顾客信息</h3>
    </div>
    <div class="panel-body" id="panel_customer">

    </div>
</div>

<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/spxx_icon.png"/>商品信息</h3>
        <div class="pull-right">
            <button class="button button-small" onclick="btn_add_goods();" id="btn_add_detail" disabled="disabled"><i class="icon-plus"></i>新增商品</button>
        </div>
    </div>
    <div class="panel-body" id="panel_detail">

    </div>
    <div class="panel-body hide">
        <table id="panel_add_detail">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><input style="width: 30px; text-align: center;" type="text" value=""></td>
                <td>0.00</td>
                <td><input style="width: 60px;" type="text" value=""></td>
                <td style="width: 10%">
                    <button class="button button-small save" title="保存"><i class="icon-ok"></i></button>
                    <button class="button button-small cancel" title="取消"><i class="icon-ban-circle"></i></button>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="panel">
    <div class="panel-header clearfix">
        <h3 class="pull-left"><img src="assets/img/sys/czrz_icon.png"/>操作日志</h3>
        <div class="pull-right">
        </div>
    </div>
    <div class="panel-body" id="panel_action">

    </div>
</div>
<!--<div class="clearfix" id="tools" style="text-align: center;position: fixed;bottom: 0px;left:-100%;width:100%;">
    <p class="p_btns">
        <button class="button button-primary" id="btn_opt_pay">付款</button>
        <button class="button button-primary" id="btn_opt_cancel_pay">取消付款</button>
        <button class="button button-primary" id="btn_opt_send">发货/自提</button>

        <button class="button button-primary" id="btn_opt_cancel">作废</button>
        <button class="button button-primary" id="btn_opt_print_ticket">打印小票</button>

        <button class="button button-primary" id="btn_opt_communicate">沟通</button>
    </p>
    <div id="close_tools">&lt;</div>
</div>-->


<?php echo load_js('comm_util.js') ?>
<script>
    var record_code = '<?php echo $response['record']['record_code'] ?>';
    var store_code = '<?php echo $response['record']['send_store_code'] ?>';
    var send_way = '<?php echo $response['record']['send_way'] ?>';
    var components = ['baseinfo', 'customer', 'detail', 'action', 'status_info'];
    var componentBtns = ['baseinfo'];
    var opts = ['pay', 'cancel_pay', 'send', 'cancel', 'print_ticket', 'communicate'];

    function tools() {
        $("#tools").animate({left: '0px'}, 1000);
        $("#close_tools").click(function () {
            if ($(this).html() == "&lt;") {
                $("#tools").animate({left: '-100%'}, 1000);
                $(this).html(">");
                $(this).addClass("tools_02").animate({right: '-10px'}, 1000);
            } else {
                $("#tools").animate({left: '0px'}, 1000);
                $(this).html("<");
                $(this).removeClass("tools_02").animate({right: '0'}, 1000);
            }
        });
    }

    $(document).ready(function () {
        //初始化按钮
        btn_init();
        //初始化数据
        component("all", "view");
        //初始化工具条
        tools();
    });
    //新增明细按钮
    function btn_add_goods() {
        var param = {store_code: store_code, record_code: record_code};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=prm/goods/goods_select_tpl_inv';
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 1);
                    top.save_up();
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods(this, 0);
                }
            }, {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    //location.reload();
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: '选择商品',
                width: '80%',
                //height: 400,
                loader: {
                    url: url,
                    autoLoad: true, //不自动加载
                    params: param, //附加的参数
                    lazyLoad: false, //不延迟加载
                    dataType: 'text'   //加载的数据类型
                },
                align: {
                    //node : '#t1',//对齐的节点
                    points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                    offset: [0, 20] //偏移
                },
                mask: true,
                buttons: buttons
            });
            top.dialog.on('closed', function (ev) {
                component('baseinfo,customer,detail,action', "view");
            });
            top.dialog.show();
        });
    }

    //初始化按钮
    function btn_init() {
        //编辑按钮
        for (var i in componentBtns) {
            btn_init_component(componentBtns[i]);
        }
        //操作按钮
        for (var i in opts) {
            var f = opts[i];
            switch (f) {
                case "pay":
                    btn_init_opt_pay();
                    break;
                case "send":
                    btn_init_opt_send();
                    break;
                case "print_ticket":
                    btn_init_opt_print_ticket();
                    break;
                case "communicate":
                    btn_init_opt_communicate();
                    break;
                default:
                    btn_init_opt(f);
                    break;
            }
        }
    }

    //初始化操作按钮事件
    function btn_init_opt(id) {
        $("#btn_opt_" + id).click(function () {
            var params = {type: id, record_code: record_code};
            $.post("?app_act=oms_shop/oms_shop/opt", params, function (ret) {
                if (ret.status == 1) {
                    ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                    component("status_info,baseinfo,detail,action", "view");
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, "json");
        });
    }

    //付款
    function btn_init_opt_pay() {
        $("#btn_opt_pay").click(function () {
            new ESUI.PopWindow("?app_act=oms_shop/oms_shop/pay&record_code=" + record_code, {
                title: "付款",
                width: 500,
                height: 350,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    component("status_info,baseinfo,detail,action", "view");
                }
            }).show();
        });
    }

    //发货|自提
    function btn_init_opt_send() {
        $("#btn_opt_send").click(function () {
            if (send_way == 0) {
                new ESUI.PopWindow("?app_act=oms_shop/oms_shop/send&record_code=" + record_code, {
                    title: "发货",
                    width: 500,
                    height: 300,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        component("baseinfo,action,status_info", "view");
                    }
                }).show();
            } else {
                var params = {type: 'send', record_code: record_code};
                $.post("?app_act=oms_shop/oms_shop/opt", params, function (ret) {
                    if (ret.status == 1) {
                        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
                        component("status_info,baseinfo,detail,action", "view");
                    } else {
                        BUI.Message.Alert(ret.message);
                    }
                }, "json");
            }
        });
    }

    //打印小票
    function btn_init_opt_print_ticket() {
        $("#btn_opt_print_ticket").click(function () {
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=cashier_ticket&record_ids=' + record_code;
            $("#print_iframe").attr('src', u);
        });
    }

    //沟通
    function btn_init_opt_communicate() {
        $("#btn_opt_communicate").click(function () {
            new ESUI.PopWindow("?app_act=oms_shop/oms_shop/communicate&record_code=" + record_code, {
                title: "沟通日志",
                width: 450,
                height: 350,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    component("action", "view");
                }
            }).show();
        });
    }

    //部件操作按钮
    function btn_init_component(id) {
        btn_init_component_edit(id);
        btn_init_component_cancel(id);
        btn_init_component_save(id);
    }

    //初始化编辑按钮
    function btn_init_component_edit(id) {
        $("#btn_edit_" + id).click(function () {
            $("#btn_edit_" + id).hide();
            $("#btn_cancel_" + id).show();
            $("#btn_save_" + id).show();
            component(id, "edit");
        });
    }

    //初始化保存按钮
    function btn_init_component_save(id) {
        $("#btn_save_" + id).click(function () {
            //保存数据
            if (!save_component(id)) {
                return false;
            }
            //更新按钮状态
            $("#btn_edit_" + id).show();
            $("#btn_cancel_" + id).hide();
            $("#btn_save_" + id).hide();
            //刷新数据
            component(id, "view");
            if (id == 'shipping') {
                component('baseinfo,customer,detail,action', "view");
            }
        });
    }

    //初始化取消按钮
    function btn_init_component_cancel(id) {
        $("#btn_cancel_" + id).click(function () {
            $("#btn_edit_" + id).show();
            $("#btn_cancel_" + id).hide();
            $("#btn_save_" + id).hide();

            //刷新数据
            component(id, "view");
        });
    }

    //检查所有按钮权限
    function btn_check() {
        var params = {"record_code": record_code, "opt_priv": 'cancel,cancel_pay,pay,send'};
        $.post("?app_act=oms_shop/oms_shop/btn_check", params, function (data) {
//            $("#tools .current_btn").removeClass('current_btn');
            var k;
            for (k in data) {
                btn_check_item(k, data[k]);
            }
//            if (data['next_opt'] != '') {
//                $("#btn_opt_" + data['next_opt']).addClass('current_btn');
//            }
        }, "json");
    }

    //检查按钮权限
    function btn_check_item(id, status) {
        var b = $("#btn_opt_" + id);
        if (id == 'edit_detail') {
            b = $("#panel_detail table tbody button");
        }

        if (status == 1) {
            b.removeAttr("disabled");
        } else {
            b.attr("disabled", true);
        }
    }

    //读取各部分详情
    function component(id, opt, callback) {
        if (typeof callback == 'undefined') {
            callback = btn_check;
        }
        var comp;
        var params = {"record_code": record_code, "type": id, "opt": opt, "components": components, ES_frmId: '<?php echo $request['ES_frmId']; ?>'};
        $.post("?app_act=oms_shop/oms_shop/component", params, function (data) {
            if (id != "all") {
                //comp = [id];
                comp = id.split(',');
            } else {
                comp = components;
            }
            store_code = data['record']['send_store_code'];
            for (var i in comp) {
                // alert(comp[i]);
                $("#panel_" + comp[i]).html(data[comp[i]]);
            }

            callback();
        }, "json");
    }

    //保存各部分详情
    function save_component(id) {
        var params = {record_code: record_code, "type": id, "data": {}};

        switch (id) {
            case "baseinfo":
                params.data["remark"] = $("#remark").val();
                break;
        }
        $.ajax({
            type: "post",
            url: "?app_act=oms_shop/oms_shop/save_component",
            data: params,
            dataType: "json",
            async: false,
            success: function (data) {
                if (data.status != 1) {
                    BUI.Message.Alert(data.message, 'error');
                }
                component('action', "view");
            }
        });
        return true;
    }

    //明细编辑
    function detail_edit(id) {
        var item = $("#panel_detail table tbody").find(".detail_" + id);
        item.find(".edit").hide();
        item.find(".delete").hide();
        item.find(".save").show();
        item.find(".cancel").show();
        item.find("td[name=num]").find("input").show();
        item.find("td[name=num]").find("div").hide();
        //item.find("td").eq(8).find("input").removeAttr("disabled")
    }

    //明细保存
    function detail_save(id) {
        var item = $("#panel_detail table tbody").find(".detail_" + id)
        var params = {
            "record_code": record_code,
            "sell_goods_id": id,
            "num": item.find("td[name=num]").find("input").val()
        };

        $.post("?app_act=oms_shop/oms_shop/opt_save_detail", params, function (data) {
            if (data.status == 1) {
                if (parseFloat(data.record.payable_money) > parseFloat(data.record.paid_money) && data.record.pay_status == 2 && data.record.order_status == 0) {
                    var msg = '订单已付款小于应付款，订单需进行付款操作';
                    BUI.Message.Alert(msg, function () {
                        location.reload();
                    }, 'warning');
                } else {
                    component('baseinfo,customer,detail,action', "view");
                }
                //刷新按钮权限
                //btn_check();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }

    //明细删除
    function detail_delete(id) {
        var params = {"record_code": record_code, "sell_goods_id": id};
        $.post("?app_act=oms_shop/oms_shop/opt_delete_detail", params, function (data) {
            if (data.status == 1) {
                component('baseinfo,customer,detail,action', "view");
                //刷新按钮权限
                // btn_check();
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");
    }

    //明细取消保存
    function detail_cancel(id) {
        var item = $("#panel_detail table tbody").find(".detail_" + id)
        item.find(".edit").show();
        item.find(".delete").show();
        item.find(".save").hide();
        item.find(".cancel").hide();
        item.find("td[name=num]").find("input").hide();
        item.find("td[name=num]").find("div").show();
    }

    //添加商品
    function addgoods(obj, type) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num > 0) {
                    //                 if(parseInt(value.num) > parseInt(value.available_mum)){
                    //                        value.num = value.available_mum;
                    //                    }
                    select_data[di] = value;
                    di++;
                }
            }
        });

        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        $.post('?app_act=oms_shop/oms_shop/opt_new_multi_detail&record_code=' + record_code + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    $("div .panel").hide();
                    $("#tag_name_type").html("edit_goods_info");
                }, 'error');
            } else {
                if (parseFloat(result.record.payable_money) > parseFloat(result.record.paid_money)) {
                    //var msg = '订单已付款小于应付款，订单需进行付款操作';
                    //                 	BUI.Message.Alert( msg, function(){

                    //                     }, 'warning');
                }
            }

            if (type == 0) {
                _thisDialog.close();
            }

            /*
             if(typeof _thisDialog.callback == "function"){
             _thisDialog.callback(this);
             }*/
        }, 'json');
    }
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>