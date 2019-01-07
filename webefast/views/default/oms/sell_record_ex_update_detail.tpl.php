
<style>
    .panel-body {padding: 0;}
    .edit_goods_info,.edit_shipping_info,.edit_inv_info,.edit_invoice_info {display:none;}
    #panel_goods_detail { margin-bottom: 5px;}
    #panel_shipping_info {margin:5px 0px;}
    #panel_shipping_info #shipping_type .controls{ margin-left:0; line-height:25px; display:inline-block; width:17%;}
    #panel_inv_info { margin:5px 0px;}
</style>

<div id="tabs_nav_sell_record_view">
    <ul class="nav-tabs">
        <li class="" data-tabs='edit_goods_info'><a href="javascript:void(0)">修改商品信息</a></li>
        <li class="" data-tabs='edit_shipping_info'><a href="javascript:void(0)">修改送货信息</a></li>
        <li class="" data-tabs='edit_inv_info'><a href="javascript:void(0)">修改留言及备注</a></li>
        <li class="" data-tabs='edit_invoice_info'><a href="javascript:void(0)">修改发票信息</a></li>
    </ul>

</div>
<div class="edit_goods_info" id="edit_goods_info">
    <div class="panel-header clearfix" style="padding:5px 0 3px;">
        <div class="pull-right">
            交易号：
            <select id="goods_deal_code">
                <?php
                $deal_code_list = explode(",", $response['record']['deal_code_list']);
                foreach ($deal_code_list as $list):
                    ?>
                    <option value="<?php echo $list; ?>"><?php echo $list; ?></option>
                <?php endforeach; ?>
            </select>
            <button class="button button-small" id="btn_add_goods" onclick="btn_add_goods();"  <?php if ($response['record']['order_status'] != 0) { ?>
                        disabled="disabled"
                    <?php } ?>><i class="icon-plus"></i>新增商品</button>
                    <?php if ($response['record']['is_fenxiao'] != 2 ) { ?>
                <button class="button button-small" id="btn_add_combo_goods" onclick="btn_add_combo_goods();"  <?php if ($response['record']['order_status'] != 0) { ?>
                            disabled="disabled"
                        <?php } ?>><i class="icon-plus"></i>新增套餐</button>
                    <?php } ?>
        </div>
    </div>
    <div class="panel-body" id="panel_goods_detail">
    </div>
</div>

<div class="edit_shipping_info" id="edit_shipping_info">
    <div class="panel-body" id="panel_shipping_info">
    </div>
</div>

<div class="edit_inv_info" id="edit_inv_info">
    <div class="panel-body" id="panel_inv_info">
    </div>
</div>

<div class="edit_invoice_info" id="edit_invoice_info">
    <div class="panel-body" id="panel_invoice_info">
    </div>
</div>

<?php echo load_js('comm_util.js') ?>
<script>
    var tabs_name = "<?php echo $response['tabs_name'] ?>";
    var sell_record_code = '<?php echo $request['sell_record_code'] ?>';
    var store_code = "<?php echo $response['record']['store_code'] ?>";
    var components = ['goods_detail', 'shipping_info', 'inv_info', 'invoice_info'];

    $("#tabs_nav_sell_record_view ul li").on("click", function (e) {
        $(this).addClass('active');
        $(this).siblings('li').removeClass('active'); // 去除所有同胞元素的样式
        var tabs_name = $(this).attr("data-tabs");

        if (tabs_name == 'edit_goods_info') {
            $("#edit_shipping_info").hide();
            $("#edit_inv_info").hide();
            $("#edit_invoice_info").hide();
            $("#edit_goods_info").show();
        }
        if (tabs_name == 'edit_shipping_info') {
            $("#edit_goods_info").hide();
            $("#edit_inv_info").hide();
            $("#edit_invoice_info").hide();
            $("#edit_shipping_info").show();
        }
        if (tabs_name == 'edit_inv_info') {
            $("#edit_goods_info").hide();
            $("#edit_shipping_info").hide();
            $("#edit_invoice_info").hide();
            $("#edit_inv_info").show();
        }
          if (tabs_name == 'edit_invoice_info') {
            $("#edit_goods_info").hide();
            $("#edit_shipping_info").hide();
            $("#edit_inv_info").hide();
            $("#edit_invoice_info").show();
        }
    });

    $(document).ready(function () {
        //默认显示第一个页签
        $("#tabs_nav_sell_record_view").find('li:eq(0)').addClass('active');
        $("#" + tabs_name).show();
        //初始化数据
        component("all", "view");
    });

    //读取各部分详情
    function component(id, opt) {
        var comp;
        var params = {"sell_record_code": sell_record_code, "type": id, "opt": opt, "components": components, ES_frmId: '<?php echo $request['ES_frmId']; ?>'};
        $.post("?app_act=oms/sell_record/component", params, function (data) {
            if (id != "all") {
                comp = id.split(',');
            } else {
                comp = components;
            }
            store_code = data['record']['store_code'];
            for (var i in comp) {
                $("#panel_" + comp[i]).html(data[comp[i]]);
            }
        }, "json");
    }

    function change_goods_add(sell_record_code, deal_code, goods_name, goods_code, sku, barcode, store_code, spec1_name, spec2_name, avg_money, sell_record_detail_id, num) {
        var url = "?app_act=oms/sell_record/add_change_goods_view&sell_record_code=" + sell_record_code + '&deal_code=' + deal_code + '&goods_name=' + goods_name + '&goods_code=' + goods_code + '&store_code=' + store_code + '&spec1_name=' + spec1_name + '&sku=' + sku + '&barcode' + barcode + '&spec2_name=' + spec2_name + '&sell_record_detail_id=' + sell_record_detail_id + '&num=' + num + '&avg_money=' + avg_money;
        new ESUI.PopWindow(url, {
            title: '等价换货',
            width: 750,
            height: 500,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                window.location.reload();
            }
        }).show();
    }
    //新增明细按钮
    function btn_add_goods() {
        var custom_code = '<?php echo $response['record']['fenxiao_code']; ?>';
        var is_fenxiao = '<?php echo $response['record']['is_fenxiao']; ?>';
        var param;
        if (is_fenxiao == 2) {
            param = {'store_code': store_code, 'tags_name': 'edit_goods_info', 'custom_code': custom_code};
        } else {
            param = {'store_code': store_code, 'tags_name': 'edit_goods_info'};
        }
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=prm/goods/goods_select_tpl_inv&is_combo=0&dingd=1';
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
                location.reload();
            });
            top.dialog.show();

        });
    }

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
        $.post('?app_act=oms/sell_record/opt_new_multi_detail&sell_record_code=' + sell_record_code + '&store_code=' + store_code, {data: select_data, deal_code: $("#goods_deal_code").val()}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    $("div .panel").hide();
                    $("#edit_shipping_info").hide();
                    $("#edit_inv_info").hide();
                    $("#edit_goods_info").show();
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

    //新增套餐
    function btn_add_combo_goods() {
        var param = {'store_code': store_code};
        if (typeof (top.dialog) != 'undefined') {
            top.dialog.remove(true);
        }
        var url = '?app_act=prm/goods/goods_combo_select_inv';
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods_combo(this, 1);
                    top.save_up();
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    addgoods_combo(this, 0);
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
                title: '选择套餐商品',
                width: '60%',
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
                location.reload();
            });
            top.dialog.show();

        });
    }
    function addgoods_combo(obj, type) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.barcode + "']").val() != '' && top.$("input[name='num_" + value.barcode + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.barcode + "']").val();
                if (value.num > 0) {
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
        $.post('?app_act=oms/sell_record/opt_new_combo_detail&sell_record_code=' + sell_record_code + '&store_code=' + store_code, {data: select_data, deal_code: $("#goods_deal_code").val()}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    $("div .panel").hide();
                    $("#edit_shipping_info").hide();
                    $("#edit_inv_info").hide();
                    $("#edit_goods_info").show();
                    $("#tag_name_type").html("edit_goods_info");
                }, 'error');
            } else {
                if (parseFloat(result.record.payable_money) > parseFloat(result.record.paid_money)) {
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
