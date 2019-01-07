<?php echo load_js("record_table.js", true); ?>
<?php echo load_js("tan.js", true); ?>
<style>
    .panel_wrap{ padding-top:5px;}
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }

    .is_stock_out_row{color:red;}
    .panel-header h3.pull-left{ color:#1695ca;}
    .panel-header h3.pull-left img{ vertical-align:text-top; margin-right:7px;}
    /*#tabs_nav_sell_record_view .nav-tabs li { color:#ec6d3a;font-size:16px;}*/
    .edit_goods_info {display:none;}
    .edit_shipping_info {display:none;}
    .edit_inv_info {display:none;}
	.edit_invoice_info {display: none;}
    .edit_base_order_info {border:1px dashed #d8d8d8;}
    .edit_base_order_info #panel_base_order_info .detail-row{margin: 2px 0;}
    .edit_base_order_info #panel_base_order_info .detail-row .span9{ width:33.33%; }
    .edit_base_order_info #panel_base_order_info .detail-row .span9 .detail-name{ display:inline-block; min-width:100px; padding-left:20px; color:#666;}
    .edit_base_order_info #panel_base_order_info .detail-row .span9 .detail-text{ color:#999;}
    .edit_base_order_info #panel_base_order_info .detail-row .span18{ width:66.66%; }
    .edit_base_order_info #panel_base_order_info .detail-row .span18 .detail-text{ display:inline-block; width:80%; overflow:hidden; text-overflow:ellipsis; vertical-align:bottom;}
    #panel_goods_detail #goods_info_detail{ margin-bottom:5px;}
    #panel_shipping_info #exit_shipping_info_table{margin-bottom:5px;}
    #panel_shipping_info #shipping_type .control-label{}
    #panel_shipping_info #shipping_type .controls{ margin-left:0; line-height:25px; display:inline-block; width:17%;} 
</style>

<?php
$title = "分销订单详情";
render_control('PageHead', 'head1', array('title' => $title,
//        'ref_table' => 'table'
));
?>
<div id="tag_name_type" style="display: none"></div>
<div  id="panel_status_info">

</div>

<div class="edit_base_order_info">
    <div class="panel-body" id="panel_base_order_info">
    </div>
</div>
<div id="tabs_nav_sell_record_view">
    <ul class="nav-tabs">
        <li class="active" data-tabs='order_info'><a href="javascript:void(0)">订单信息</a></li>
        <li class="" data-tabs='edit_goods_info'><a href="javascript:void(0)">修改商品信息</a></li>
        <?php if($response['login_type'] != 2) { ?>
            <li class="" data-tabs='edit_shipping_info'><a href="javascript:void(0)">修改送货信息</a></li>
        <?php } ?>
        
        <li class="" data-tabs='edit_inv_info'><a href="javascript:void(0)">修改留言及备注</a></li>
        <li class="" data-tabs='edit_invoice_info'><a href="javascript:void(0)">修改发票信息</a></li>
    </ul>

</div>
<!--<div style="float: left;line-height: 20px;display: block;padding: 5px 15px;color:red;">提示：只有未确认状态的订单允许修改</div>-->
<div class="panel_wrap">
    <div class="panel">
        <div class="panel-header clearfix">
            <div class="pull-right">
                <button class="button button-small" id="btn_edit_baseinfo"><i class="icon-edit"></i>编辑</button>
                <button class="button button-small hide" id="btn_save_baseinfo"><i class="icon-ok"></i>保存</button>
                <button class="button button-small hide" id="btn_cancel_baseinfo"><i class="icon-ban-circle"></i>取消</button>
            </div>
        </div>
        <div class="panel-body" id="panel_baseinfo">

        </div>
    </div>

    <div class="edit_goods_info" id="edit_goods_info">
        <div class="panel-header clearfix" style="padding:0 0 3px;">
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
                <button class="button button-small" id="btn_add_goods" onclick="btn_add_goods();"  <?php if ($response['record']['order_status'] != 0 || ($response['record']['is_fenxiao'] == 2 && $response['record']['is_fx_settlement'] == 1)) { ?>
                            disabled="disabled" 
                        <?php } ?>><i class="icon-plus"></i>新增商品</button>
            </div>
        </div>
        <div class="panel-body" id="panel_goods_detail">
        </div>
    </div>

    <div class="edit_shipping_info" id="edit_shipping_info">
        <!--<div class="panel-header clearfix">
             <div class="pull-right">
            </div>
        </div>-->
        <div class="panel-body" id="panel_shipping_info">
        </div>
    </div>

    <div class="edit_inv_info" id="edit_inv_info">
        <!--<div class="panel-header clearfix">
             <div class="pull-right">
            </div>
        </div>-->
        <div class="panel-body" id="panel_inv_info">
        </div>
    </div>
    <div class="edit_invoice_info" id="edit_invoice_info">
        <!--<div class="panel-header clearfix">
             <div class="pull-right">
            </div>
        </div>-->
        <div class="panel-body" id="panel_invoice_info">
        </div>
    </div>

    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/fhxx_icon.png"/>发货信息</h3>
            <div class="pull-right">
                <button class="button button-small" id="btn_edit_shipping"><i class="icon-edit"></i>编辑</button>
                <button class="button button-small hide" id="btn_save_shipping"><i class="icon-ok"></i>保存</button>
                <button class="button button-small hide" id="btn_cancel_shipping"><i class="icon-ban-circle"></i>取消</button>
            </div>
        </div>
        <div class="panel-body" id="panel_shipping">

        </div>
    </div>

    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/ddje_icon.png"/>订单金额</h3>
            <div class="pull-right">
                <button class="button button-small" id="btn_edit_money"><i class="icon-edit"></i>编辑</button>
                <button class="button button-small hide" id="btn_save_money"><i class="icon-ok"></i>保存</button>
                <button class="button button-small hide" id="btn_cancel_money"><i class="icon-ban-circle"></i>取消</button>
            </div>
        </div>
        <div class="panel-body" id="panel_money">

        </div>
    </div>
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/ddje_icon.png"/>订单结算金额</h3>
            <div class="pull-right">
                <button class="button button-small" id="btn_edit_fx_money"><i class="icon-edit"></i>编辑</button>
                <button class="button button-small hide" id="btn_save_fx_money"><i class="icon-ok"></i>保存</button>
                <button class="button button-small hide" id="btn_cancel_fx_money"><i class="icon-ban-circle"></i>取消</button>
            </div>
        </div>
        <div class="panel-body" id="panel_fx_money">

        </div>
    </div>

    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/spxx_icon.png"/>商品信息</h3>
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
                <button class="button button-small" onclick="btn_add_goods();" id="btn_add_detail"><i class="icon-plus"></i>新增商品</button>
                <!--<button class="button button-small hide" id="btn_save_detail"><i class="icon-ok"></i>保存</button>
                <button class="button button-small hide" id="btn_cancel_detail"><i class="icon-ban-circle"></i>取消</button>-->
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

    <?php
    $ref = isset($request['ref']) ? $request['ref'] : 'ex';
    $tit = $ref == 'ex' ? '订单处理' : '订单查询';
    $url = "?app_act=oms/sell_record/{$ref}_list";
    $_url = base64_encode($url);
    $u = "javascript:openPage('{$_url}', '{$url}', '{$tit}')";
    ?>

    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left"><img src="assets/img/sys/czrz_icon.png"/>操作日志</h3>
            <div class="pull-right">
            </div>
        </div>
        <div class="panel-body" id="panel_action">

        </div>
    </div>
    <div class="clearfix" id="tools" style="text-align: center;position: fixed;bottom: 0px;left:-100%;width:100%;">
        <p class="p_btns">
            <button class="button button-primary" id="btn_opt_lock">锁定</button>
            <button class="button button-primary" id="btn_opt_unlock">解锁</button>

            <button class="button button-primary" id="btn_opt_pay">付款</button>
            <button class="button button-primary" id="btn_opt_unpay">取消付款</button>
           <?php if($response['record']['is_fenxiao'] == 1 || $response['record']['is_fenxiao'] == 2) {?>
            <button class="button button-primary" id="btn_opt_settlement">分销结算</button>
            <button class="button button-primary" id="btn_opt_unsettlement">取消结算</button>
           <?php }?>
            <button class="button button-primary" id="btn_opt_confirm">确认</button>
            <button class="button button-primary" id="btn_opt_unconfirm">取消确认</button>

            <button class="button button-primary" id="btn_opt_notice_shipping">通知配货</button>
            <button class="button button-primary" id="btn_opt_unnotice_shipping">取消通知配货</button>
            <button class="button button-primary" id="btn_opt_short_split">缺货拆单</button>
            <button class="button button-primary" id="btn_opt_set_rush">加急单</button>
            <button class="button button-primary" id="btn_opt_create_return">生成退单</button>
        </p>
        <p class="p_btns">
            <button class="button button-primary" id="btn_opt_intercept">订单拦截</button>
            <button class="button button-primary" id="btn_opt_pending">挂起</button>
            <button class="button button-primary" id="btn_opt_unpending">解挂</button>

            <!--<button class="button button-primary" id="btn_opt_problem">设为问题单</button>-->
            
                <button class="button button-primary" id="btn_opt_unproblem">返回正常单</button>
            
            <button class="button button-primary" id="btn_opt_split_order">拆单</button>
            <button class="button button-primary" id="btn_opt_cancel">作废</button>
            <button class="button button-primary" id="btn_opt_copy">复制订单</button>

            <button class="button button-primary" id="btn_opt_force_unlock">强制解锁</button>
            <button class="button button-primary" id="btn_opt_send">手工发货</button>

            <button class="button button-primary go_back_btn" id="btn_opt_return" onclick="javascript:window.location = '<?php echo $url; ?>';">返回</button>
            <button class="button button-primary" id="btn_opt_replenish">补单</button>
        </p>
        <div id="close_tools">&lt;</div>
    </div>


    <?php echo load_js('comm_util.js') ?>
    <script>
        var tabs_name = "<?php echo $response['tabs_name'] ?>";
        var sell_record_code = '<?php echo $request['sell_record_code'] ?>';
        var store_code = "<?php echo $response['record']['store_code'] ?>";
        var custom_code = "<?php echo $response['record']['fenxiao_code'] ?>";
        var fx_settlement = "<?php echo $response['record']['is_fx_settlement'] ?>";
        var record_type = "<?php echo $response['record_type'] ?>";
        var components = ['baseinfo', 'shipping', 'money','fx_money', 'detail', 'action', 'status_info', 'goods_detail', 'shipping_info', 'inv_info', 'base_order_info','invoice_info'];
        var componentBtns = ['baseinfo', 'shipping', 'money','fx_money'];
        var opts = [
            'opt_lock', 'opt_unlock', 'opt_pay', 'opt_unpay', 'opt_confirm', 'opt_unconfirm','opt_settlement','opt_unsettlement',
            'opt_notice_shipping', 'opt_unnotice_shipping', 'opt_send', 'opt_short_split', 'opt_set_rush', 'opt_split_order',
            'opt_problem', 'opt_unproblem', 'opt_pending', 'opt_unpending', 'opt_create_return', 'opt_cancel', 'opt_copy', 'opt_force_unlock', 'opt_intercept',
            'opt_replenish'
        ];
        var btns = {
            'edit_baseinfo': 0, 'edit_shipping': 0, 'edit_money': 0, 'add_detail': 0, 'edit_detail': 0,'edit_fx_money':0,
            'opt_lock': 0, 'opt_unlock': 0, 'opt_pay': 0, 'opt_unpay': 0, 'opt_confirm': 0, 'opt_unconfirm': 0, 'opt_settlement': 0, 'opt_unsettlement': 0,
            'opt_notice_shipping': 0, 'opt_unnotice_shipping': 0, 'opt_send': 0, 'opt_short_split': 0, 'opt_set_rush': 0, 'opt_split_order': 0,
            'opt_problem': 0, 'opt_unproblem': 0, 'opt_pending': 0, 'opt_unpending': 0, 'opt_create_return': 0, 'opt_cancel': 0, 'opt_copy': 0, 'opt_force_unlock': 0, 'opt_intercept': 0,
            'opt_replenish':0,
        };

        $("#tabs_nav_sell_record_view ul li").on("click", function (e) {
            var $this = $("#tabs_nav_sell_record_view");
            $(this).addClass('active');
            $(this).siblings('li').removeClass('active'); // 去除所有同胞元素的样式
            var tabs_name = $(this).attr("data-tabs");
            if (tabs_name == 'order_info') {
                $("#edit_goods_info").hide();
                $("#edit_shipping_info").hide();
                $("#edit_inv_info").hide();
                $('#edit_invoice_info').hide();
                $("#tag_name_type").html("");
                $("div .panel").show();
            }

            if (tabs_name == 'edit_goods_info') {
                $("div .panel").hide();
                $("#edit_shipping_info").hide();
                $("#edit_inv_info").hide();
                $('#edit_invoice_info').hide();
                $("#edit_goods_info").show();
                $("#tag_name_type").html("edit_goods_info");
            }

            if (tabs_name == 'edit_shipping_info') {
                $("div .panel").hide();
                $("#edit_goods_info").hide();
                $("#edit_inv_info").hide();
                $('#edit_invoice_info').hide();
                $("#tag_name_type").html("");
                $("#edit_shipping_info").show();
            }

            if (tabs_name == 'edit_inv_info') {
                $("div .panel").hide();
                $("#edit_goods_info").hide();
                $("#edit_shipping_info").hide();
                $("#tag_name_type").html("");
                $("#edit_inv_info").show();
                $('#edit_invoice_info').hide();
            }
            
            if(tabs_name == 'edit_invoice_info') {
                $("div .panel").hide();
                $("#edit_goods_info").hide();
                $("#edit_shipping_info").hide();
                $("#tag_name_type").html("");
                $("#edit_inv_info").hide();
                $('#edit_invoice_info').show();
            }

        });


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
            //新增明细按钮
            /*
             get_goods_inv_panel({
             "id":"btn_add_detail",
             'param':{'store_code':'//<?php //echo $response['record']['store_code']; ?>','tags_name':"edit_goods_info"},
             "callback":addgoods
             });
             
             get_goods_inv_panel({
             "id":"btn_add_goods",
             'param':{'store_code':'<?php //echo $response['record']['store_code']; ?>','tags_name':'edit_goods_info'},
             "callback":addgoods
             });*/
            //检查按钮权限
            //        btn_check();

            //初始化工具条
            tools();
        });
        //新增明细按钮
        function btn_add_goods() {
            var is_fenxiao = '<?php echo $response['record']['is_fenxiao'];?>';
            var param ;
            if(is_fenxiao == 2) {
                param = {'store_code': store_code, 'tags_name': 'edit_goods_info', 'custom_code' : custom_code};
            } else {
                param = {'store_code': store_code, 'tags_name': 'edit_goods_info'};
            }
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=prm/goods/goods_select_tpl_inv&dingd=1';//为1时允许缺货添加商品
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

        //初始化按钮
        function btn_init() {
            //编辑按钮
            for (var i in componentBtns) {
                btn_init_component(componentBtns[i]);
            }

            //操作按钮
            for (var i in opts) {
                var f = opts[i]
                switch (f) {
                    case "opt_pay":
                        btn_init_opt_pay();
                        break;
                    case "opt_pending":
                        btn_init_opt_pending();
                        break;
                    case "opt_problem":
                        btn_init_opt_problem();
                        break;
                    case "opt_print_send":
                        btn_init_opt_print_send();
                        break;
                    case "opt_print_express":
                        btn_init_opt_print_express();
                        break;
                    case "opt_send":
                        btn_init_opt_send();
                        break;
                    case "opt_copy":
                        btn_init_opt_copy();
                        break;
                    case "opt_short_split":
                        btn_init_opt_short_split();
                        break;
                    case "opt_split_order":
                        btn_init_opt_split_order();
                        break;
                    case "opt_set_rush":
                        btn_init_opt_set_rush();
                        break;
                    case "opt_create_return":
                        btn_init_opt_create_return();
                        break;
                    case "opt_confirm":
                        btn_init_opt_confirm(f);
                        break;
                    case "opt_settlement":
                        btn_int_opt_settlement(f);
                        break;
                    default:
                        btn_init_opt(f);
                        break;
                }
            }
        }

        //copy订单
        function btn_init_opt_copy() {
            $("#btn_opt_copy").click(function () {
                var params = {"sell_record_code": sell_record_code, "type": 'opt_copy'};
                $.post("?app_act=oms/sell_record/opt", params, function (data) {
                    if (data.status == 1) {
                        location.href = "?app_act=fx/sell_record/view&sell_record_code=" + data.data + "&ref=do";
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }

        //缺货拆单
        function btn_init_opt_short_split() {
            $("#btn_opt_short_split").click(function () {
                var params = {"sell_record_code": sell_record_code, "mode": 2, "app_fmt": 'json'};
                $.post("?app_act=oms/sell_record/split", params, function (data) {
                    if (data.status == 1) {
                        location.href = "?app_act=oms/sell_record/view&sell_record_code=" + sell_record_code + "&ref=do";
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }
        //拆单
        function btn_init_opt_split_order() {
            $("#btn_opt_split_order").click(function () {
               var url = "?app_act=oms/sell_record/split_order&sell_record_code=" + sell_record_code;
               openPage(window.btoa(url),url,"拆单");
            });
        }

        //加急单
        function btn_init_opt_set_rush() {
            $("#btn_opt_set_rush").click(function () {
                var params = {"sell_record_code": sell_record_code, "app_fmt": 'json'};
                $.post("?app_act=oms/sell_record/set_rush", params, function (data) {
                    if (data.status == 1) {
                        component("all", "view");
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }

        //初始化操作按钮
        function btn_init_opt(id) {
            $("#btn_" + id).click(function () {
                var params = {"sell_record_code": sell_record_code, "type": id,"fx": 1,};
                $.post("?app_act=oms/sell_record/opt", params, function (data) {
                    if (data.status == 1) {
                        //刷新按钮权限
                        //                    btn_check();
                        //  component("all", "view");
                        if(id === 'opt_replenish'){
                            location.href = "?app_act=fx/sell_record/view&sell_record_code=" + data.data + "&ref=do";
                        }else{
                            location.reload();
                        }
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }

        //付款
        function btn_init_opt_pay() {
            $("#btn_opt_pay").click(function () {
                new ESUI.PopWindow("?app_act=oms/sell_record/pay&sell_record_code=" + sell_record_code, {
                    title: "付款",
                    width: 500,
                    height: 250,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        component("status_info,baseinfo,money,detail,action", "view");
                        /*
                         component("baseinfo", "view");
                         component("money", "view");
                         component("detail", "view");
                         component("action", "view");*/

                        //刷新按钮权限
                        //                    btn_check()
                    }
                }).show()
            })
        }
        //挂起
        function btn_init_opt_pending() {
            $("#btn_opt_pending").click(function () {
                new ESUI.PopWindow("?app_act=oms/sell_record/pending&sell_record_code=" + sell_record_code, {
                    title: "挂起",
                    width: 450,
                    height: 480,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        component("all", "view");
                        //刷新按钮权限
                        //                    btn_check()
                    }
                }).show()
            })
        }

        //设为问提单
        function btn_init_opt_problem() {
            $("#btn_opt_problem").click(function () {
                new ESUI.PopWindow("?app_act=oms/sell_record/problem&sell_record_code=" + sell_record_code, {
                    title: "设为问提单",
                    width: 500,
                    height: 300,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        component("action,baseinfo", "view");
                        /*
                         component("action", "view");
                         component("baseinfo", "view");*/
                        //刷新按钮权限
                        //                    btn_check()
                    }
                }).show()
            })
        }
        //打印发货单
        function btn_init_opt_print_send() {

            var url = '?app_act=oms/sell_record/mark_sell_record_print';
            var params = {};
            params.record_ids = sell_record_code;
            $.post(url, params, function (data) {

            });


            $("#btn_opt_print_send").click(function () {
                var window_is_block = window.open('?app_act=sys/danju_print/do_print_record&app_page=null&print_data_type=order_sell_record&sell_record_codes=' + sell_record_code);
                if (null == window_is_block) {
                    alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                }
            })
        }

        //打印快递单
        function btn_init_opt_print_express() {
            $("#btn_opt_print_express").click(function () {
                print_express.print_express(sell_record_code);
            })
        }

        //手工发货
        function btn_init_opt_send() {
            $("#btn_opt_send").click(function () {
                new ESUI.PopWindow("?app_act=oms/sell_record/send&sell_record_code=" + sell_record_code, {
                    title: "手工发货",
                    width: 500,
                    height: 350,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        component("baseinfo,action,status_info", "view");
                        /*
                         component("baseinfo", "view");
                         component('action',"view");
                         component("status_info", "view")*/

                        //刷新按钮权限
                        //                    btn_check();
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
                    component('base_order_info,shipping_info,inv_info,detail,action', "view");
                    /*
                     component('base_order_info', "view");
                     component("shipping_info", "view");
                     component("inv_info", "view");
                     component('detail', "view");
                     component('action',"view");*/
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
            var params = {"sell_record_code": sell_record_code, "fields": btns,'is_fx':1};

            $.post("?app_act=oms/sell_record/btn_check", params, function (data) {
                $("#tools .current_btn").removeClass('current_btn');
                var k;
                for (k in data['comp']) {
                    btn_check_item(k, data['comp'][k]['status'], data['comp'][k]['message'])
                }
                if (data['next_opt'] != '') {
                    //console.log(data);
                    //console.log($("#btn_"+data['next_opt']));
                    $("#btn_" + data['next_opt']).addClass('current_btn');
                }
            }, "json");
        }

        //检查按钮权限
        function btn_check_item(id, status, $message) {
            var b = $("#btn_" + id) //edit_detail
            if (id == 'edit_detail') {
                b = $("#panel_detail table tbody button");
            }

            if (status == 1) {
                b.removeAttr("disabled");
                b.removeAttr("message");
            } else {
                b.attr("disabled", true);
                b.attr("message", $message);
            }
        }



        //读取各部分详情
        function component(id, opt, callback) {
            if (typeof callback == 'undefined') {
                callback = btn_check;
            }
            var comp;
            var params = {"sell_record_code": sell_record_code, "type": id, "opt": opt, "components": components, ES_frmId: '<?php echo $request['ES_frmId']; ?>',"record_type":record_type};
            $.post("?app_act=oms/sell_record/component", params, function (data) {
                if (id != "all") {
                    //comp = [id];
                    comp = id.split(',');
                } else {
                    comp = components;
                }
                store_code = data['record']['store_code'];
                for (var i in comp) {
                    // alert(comp[i]);
                    $("#panel_" + comp[i]).html(data[comp[i]]);
                }

                callback();
            }, "json");
        }

        //保存各部分详情
        function save_component(id) {
            var params = {sell_record_code: sell_record_code, "type": id, "data": {}};

            switch (id) {
                case "fx_money":
                    params.data["fx_express_money"] = $("#fx_express_money").val();
                    break;
                case "money":
                    params.data["express_money"] = $("#express_money").val();
                    break;
                case "baseinfo":
                    params.data["pay_code"] = $("#pay_code").val();
                    params.data["seller_remark"] = $("#seller_remark").val();
                    break;
                case "shipping":
                    if(fx_settlement == 1) {
                        params.data["order_remark"] = $("#order_remark").val();
                        params.data["store_remark"] = $("#store_remark").val();
                    } else if(fx_settlement == 0){
                        params.data["express_code"] = $("#express_code").val();
                        params.data["express_no"] = $.trim($("#express_no").val());
                        params.data["receiver_name"] = $.trim($("#receiver_name").val());
                        params.data["receiver_mobile"] = $.trim($("#receiver_mobile").val());
                        params.data["receiver_country"] = $("#country").val();
                        params.data["receiver_province"] = $("#province").val();
                        params.data["receiver_city"] = $("#city").val();
                        params.data["receiver_district"] = $("#district").val();
                        params.data["receiver_street"] = $("#street").val();
                        params.data["receiver_addr"] = $.trim($("#receiver_addr").val());
                        params.data["receiver_phone"] = $.trim($("#receiver_phone").val());
                        params.data["store_code"] = $("#store_code").val();
                        params.data["receiver_zip_code"] = $("#receiver_zip_code").val();
                        params.data["order_remark"] = $("#order_remark").val();
                        params.data["store_remark"] = $("#store_remark").val();
                        if (params.data.receiver_name == "") {
                            BUI.Message.Alert("收货人为必填项", 'error');
                            return false;
                        }
                        if (params.data.receiver_phone == "" && params.data.receiver_mobile == "") {
                            BUI.Message.Alert("手机号和固定电话必填其一", 'error');
                            return false;
                        }
                        if (params.data.receiver_country == "") {
                            BUI.Message.Alert("国家为必选项", 'error');
                            return false;
                        }
                        if (params.data.receiver_province == "") {
                            BUI.Message.Alert("省为必选项", 'error');
                            return false;
                        }
                        if (params.data.receiver_city == "") {
                            BUI.Message.Alert("市为必选项", 'error');
                            return false;
                        }
                        if (params.data.receiver_addr == "") {
                            BUI.Message.Alert("详细地址为必填项", 'error');
                            return false;
                        }
                        var str = params.data.express_no;
                        if (str != '') {
                            var reg = new RegExp(/^[0-9A-Za-z]+$/);
                            if (!reg.test(str)) {
                                BUI.Message.Alert("快递单号必须为数字或者字母", 'error');
                                return false;
                            }
                        }
                    }
                    break;
                    
            }
            $.ajax({
                type: "post",
                url: "?app_act=oms/sell_record/save_component",
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
                            //update_panel_params(p);
                        }
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
            item.find("td[name=avg_money]").find("input").show();
            item.find("td[name=avg_money]").find("span").hide();
            item.find("td[name=deal_code]").find("input").show();
            item.find("td[name=deal_code]").find("span").hide();
            item.find("td[name=fx_amount]").find("input").show();
            item.find("td[name=fx_amount]").find("span").hide();
            //item.find("td").eq(8).find("input").removeAttr("disabled")
        }

        //明细删除
        function detail_delete(id) {
            var params = {"sell_record_code": sell_record_code, "sell_record_detail_id": id}
            $.post("?app_act=oms/sell_record/opt_delete_detail", params, function (data) {
                if (data.status == 1) {
                    component("money,detail,action,fx_money", "view");
                    /*
                     component("money", "view");
                     component("detail", "view");
                     component("action", "view");*/
                    //刷新按钮权限
                    //                btn_check();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json")
        }
        //明细保存
        function detail_save(id) {
            var item = $("#panel_detail table tbody").find(".detail_" + id)

            var params = {
                "sell_record_code": sell_record_code,
                "sell_record_detail_id": id,
                "num": item.find("td[name=num]").find("input").val(),
                "deal_code": item.find("td[name=deal_code]").find("input").val(),
                "avg_money": item.find("td[name=avg_money]").find("input").val(),
                "fx_amount":item.find("td[name=fx_amount]").find("input").val(),
            }
            //console.log(params);return;
            $.post("?app_act=oms/sell_record/opt_save_detail", params, function (data) {
                if (data.status == 1) {
                    if (parseFloat(data.record.payable_money) > parseFloat(data.record.paid_money) && data.record.pay_status == 2 && data.record.order_status == 0) {
                        var msg = '订单已付款小于应付款，订单需进行付款操作';
                        BUI.Message.Alert(msg, function () {
                            location.reload();
                        }, 'warning');
                    } else {
                        location.reload();
                    }

                    //刷新按钮权限
                    //                btn_check();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json")
        }

        //明细取消保存
        function detail_cancel(id) {
            var item = $("#panel_detail table tbody").find(".detail_" + id)
            item.find(".edit").show()
            item.find(".delete").show()
            item.find(".save").hide()
            item.find(".cancel").hide()
            item.find("td[name=num]").find("input").hide()
            item.find("td[name=num]").find("div").show()
            item.find("td[name=avg_money]").find("input").hide()
            item.find("td[name=avg_money]").find("span").show()
            item.find("td[name=deal_code]").find("input").hide()
            item.find("td[name=deal_code]").find("span").show()
            item.find("td").eq(8).find("input").attr("disabled", true)
        }


        $("#btn_down_seller_remark").live("click", function () {
            var ajax_url = '?app_act=oms/sell_record/seller_remark_flush&record_code=' + $('#hid_record_code').val();
            $.get(ajax_url, function (return_str) {
                try {
                    var return_json = $.parseJSON(return_str);
                } catch (e) {
                    alert('JSON数据解析出错：' + return_str);
                    return;
                }
                if (return_json.status != 1) {
                    alert('刷新商家备注出错: ' + return_json.data);
                } else {
                    alert('刷新商家备注成功');
                    $("#seller_remark").val(return_json.data);
                }
            });
        })

        $("#btn_upload_seller_remark").live("click", function () {
            var ajax_url = '?app_act=oms/sell_record/seller_remark_upload&record_code=' + $('#hid_record_code').val();
            $.post(ajax_url, {'seller_remark': $("#seller_remark").val()}, function (return_str) {
                try {
                    var return_json = $.parseJSON(return_str);
                } catch (e) {
                    alert('JSON数据解析出错：' + return_str);
                    return;
                }
                if (return_json.status != 1) {
                    alert('更新淘宝后台商家备注出错: ' + return_json.data);
                } else {
                    alert('更新淘宝后台商家备注成功');
                    $("#seller_remark").val(return_json.data);
                }
            });
        })

        $("#btn_down_buyer_remark").live("click", function () {
            var ajax_url = '?app_act=oms/sell_record/buyer_remark_flush&record_code=' + $('#hid_record_code').val();
            $.get(ajax_url, function (return_str) {
                try {
                    var return_json = $.parseJSON(return_str);
                } catch (e) {
                    alert('JSON数据解析出错：' + return_str);
                    return;
                }
                if (return_json.status != 1) {
                    alert('刷新客户留言出错: ' + return_json.data);
                } else {
                    alert('刷新客户留言成功');
                    $("#buyer_remark").html(return_json.data);
                }
            });
        })

        function btn_init_opt_create_return() {
            $("#btn_opt_create_return").live("click", function () {
                openPage('<?php echo base64_encode('?app_act=fx/sell_record/create_return_form&app_scene=edit&sell_record_code='); ?>' + sell_record_code, '?app_act=fx/sell_record/create_return_form&app_scene=edit&sell_record_code=' + sell_record_code, '生成退单');
            });
        }
        //确认
        function btn_init_opt_confirm(id) {
            var params = {"sell_record_code": sell_record_code, "type": id};
            $("#btn_" + id).click(function () {
                $.post("?app_act=oms/sell_record/is_payment_gtr_payable", params, function (ret) {
                    if (ret.status == 1) {
                        if (ret.data == true){
                            BUI.Message.Confirm('该订单买家已付款大于订单应付款，是否继续操作？',function(){
                                $.post("?app_act=oms/sell_record/opt", params, function (data) {
                                    if (data.status == 1) {
                                        //刷新按钮权限
                                        location.reload();
                                    } else {
                                        BUI.Message.Alert(data.message, 'error');
                                    }
                                }, "json");
                            },'question');
                        }else{
                            $.post("?app_act=oms/sell_record/opt", params, function (data) {
                                    if (data.status == 1) {
                                        //刷新按钮权限
                                        location.reload();
                                    } else {
                                        BUI.Message.Alert(data.message, 'error');
                                    }
                                }, "json");
                        }
                    } else {
                        BUI.Message.Alert(ret.message, 'error');
                    }
                }, "json");
            });
        }
        //结算
        function btn_int_opt_settlement(id){
            $("#btn_" + id).click(function(){
                $.post('?app_act=fx/sell_record/have_out_goods',{ids:sell_record_code},function(data){
                    if(data.status == 1){
                        BUI.Message.Alert(data.message,'error');
                    }else if(data.status == 2){
                        BUI.Message.Confirm(data.message,function(){
                            var params = {"sell_record_code": sell_record_code, "type": id,"fx": 1,'allow_out':1};
                            $.post("?app_act=oms/sell_record/opt", params, function (data) {
                                if (data.status == 1) {
                                    location.reload();
                                } else {
                                    BUI.Message.Alert(data.message,'error');
                                }
                            }, "json");
                        });
                    }else{
                        var params = {"sell_record_code": sell_record_code, "type": id,"fx": 1,};
                        $.post("?app_act=oms/sell_record/opt", params, function (data) {
                            if (data.status == 1) {
                                //刷新按钮权限
                                //                    btn_check();
                                //  component("all", "view");
                                location.reload();
                            } else {
                                BUI.Message.Alert(data.message, 'error');
                            }
                        }, "json");
                    }
                },'json')
            })
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
            $.post('?app_act=oms/sell_record/opt_new_multi_detail&sell_record_code=' + sell_record_code + '&store_code=' + store_code + '&record_type='+record_type, {data: select_data, deal_code: $("#goods_deal_code").val()}, function (result) {
                if (true != result.status) {
                    //添加失败
                    _thisDialog.close();
                    top.top.BUI.Message.Alert(result.message,function () {
                        $("div .panel").hide();
                        $("#edit_shipping_info").hide();
                        $("#edit_inv_info").hide();
                        $('#edit_invoice_info').hide();
                        $("#edit_goods_info").show();
                        $("#tag_name_type").html("edit_goods_info");
                    },'error');
                } else if(result.status == 2){
                    _thisDialog.close();
                    BUI.Message.Alert(result.message, function () {
                        $("div .panel").hide();
                        $("#edit_shipping_info").hide();
                        $("#edit_inv_info").hide();
                        $('#edit_invoice_info').hide();
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

    </script>