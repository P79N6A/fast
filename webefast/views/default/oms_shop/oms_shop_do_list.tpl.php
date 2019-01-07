<style>
    #record_date_start{width:85px;}
    #record_date_end{width:85px;}
    #tool2{ height:30px;}
    #tool2 input{ vertical-align:middle;}
    #tool2 label{ vertical-align:middle; margin-right:5px;}
</style>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '门店订单列表',
    'links' => array(
    //array('url' => 'oms_shop/oms_shop/add', 'title' => '新增订单', 'is_pop' => false, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '订单编号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_phone'] = '手机号码';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_detail',
        )
    ),
//    'show_row' => 2,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '支持多订单号查询，用逗号隔开；
以下字段支持模糊查询：订单号、买家昵称、手机号码',
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_date',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_date_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_date_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '门店名称',
            'type' => 'select_multi',
            'id' => 'offline_shop_code',
            'data' => load_model('base/ShopModel')->get_select_entity(0),
        ),
    )
));
?>

<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => true, 'id' => 'tabs_all'), //默认选中active=true的页签
        array('title' => '待付款', 'active' => false, 'id' => 'tabs_pay'),
        array('title' => '待发货', 'active' => false, 'id' => 'tabs_send'),
        array('title' => '待提货', 'active' => false, 'id' => 'tabs_pickup'),
        array('title' => '已发货', 'active' => false, 'id' => 'tabs_shipped'),
        array('title' => '已作废', 'active' => false, 'id' => 'tabs_cancel'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<div id="TabPage1Contents">
    <div>
        <!--<ul  class="toolbar frontool"  id="ToolBar1">
            </ul>
            <script>
                $(function () {
                    var default_opts = ['opt_intercept'];
                    for (var i in default_opts) {
                        var f = default_opts[i];
                        btn_init_opt("ToolBar1", f);
                    }
                    var custom_opts = $.parseJSON('');
                    for (var j in custom_opts) {
                        var g = custom_opts[j];
                        $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
                    }
                });
            </script>-->
    </div>
    <div>
        <!--        <ul  class="toolbar frontool"  id="ToolBar2">
                    <li class="li_btns"><button class="button button-primary btn_opt_pay ">批量付款</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_cancel">批量作废</button></li>
                </ul>
                <script>
                    $(function () {
                        var default_opts = ['pay', 'cancel'];
                        for (var i in default_opts) {
                            var f = default_opts[i];
                            btn_init_opt("ToolBar2", f);
                        }
                        var custom_opts = $.parseJSON('[{"id":"","custom":""}]');
                        for (var j in custom_opts) {
                            var g = custom_opts[j];
                            $("#ToolBar2 .btn_" + g['id']).click(eval(g['custom']));
                        }
                    });
                </script>-->

    </div>
    <div>
        <!--        <ul  class="toolbar frontool"  id="ToolBar3">
                    <li class="li_btns"><button class="button button-primary btn_opt_pay_cancel">批量取消付款</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_send">批量发货</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_print_ticket">打印小票</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_cancel">批量作废</button></li>
                </ul>
                <script>
                    $(function () {
                        function tools() {
                            $(".frontool").animate({left: '0px'}, 1000);
                            $(".front_close").click(function () {
                                if ($(this).html() == "&lt;") {
                                    $(".frontool").animate({left: '-100%'}, 1000);
                                    $(this).html(">");
                                    $(this).addClass("close_02").animate({right: '-10px'}, 1000);
                                } else {
                                    $(".frontool").animate({left: '0px'}, 1000);
                                    $(this).html("<");
                                    $(this).removeClass("close_02").animate({right: '0'}, 1000);
                                }
                            });
                        }
                        tools();
                    })
                </script>
                <script>
                    $(function () {
                        var default_opts = ['pay_cancel', 'send', 'print_ticket', 'cancel'];
                        for (var i in default_opts) {
                            var f = default_opts[i];
                            btn_init_opt("ToolBar3", f);
                        }
                        var custom_opts = $.parseJSON('[{"id":"","custom":""}]');
                        for (var j in custom_opts) {
                            var g = custom_opts[j];
                            $("#ToolBar3 .btn_" + g['id']).click(eval(g['custom']));
                        }
                    });
                </script>-->
    </div>
    <div>
        <!--        <ul  class="toolbar frontool"  id="ToolBar4">
                    <li class="li_btns"><button class="button button-primary btn_opt_pay_cancel">批量取消付款</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_send">批量提货</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_print_ticket">打印小票</button></li>
                    <li class="li_btns"><button class="button button-primary btn_opt_cancel">批量作废</button></li>
                </ul>
                <script>
                    $(function () {
                        var default_opts = ['pay_cancel', 'send', 'print_ticket', 'cancel'];
                        for (var i in default_opts) {
                            var f = default_opts[i];
                            btn_init_opt("ToolBar4", f);
                        }
                        var custom_opts = $.parseJSON('[{"id":"","custom":""}]');
                        for (var j in custom_opts) {
                            var g = custom_opts[j];
                            $("#ToolBar4 .btn_" + g['id']).click(eval(g['custom']));
                        }
                    });
                </script>-->
    </div>
    <div>
        <!--        <ul  class="toolbar frontool"  id="ToolBar5">
                    <li class="li_btns"><button class="button button-primary btn_opt_print_ticket">打印小票</button></li>
                </ul>
                <script>
                    $(function () {
                        var default_opts = ['print_ticket'];
                        for (var i in default_opts) {
                            var f = default_opts[i];
                            btn_init_opt("ToolBar5", f);
                        }
                        var custom_opts = $.parseJSON('[{"id":"","custom":""}]');
                        for (var j in custom_opts) {
                            var g = custom_opts[j];
                            $("#ToolBar5 .btn_" + g['id']).click(eval(g['custom']));
                        }
                    });
                </script>-->
    </div>
</div>
<!--<ul id="tool2" class="toolbar" style="margin-top: 10px;">
    <li style="float:left;">
        <label>排序类型：</label>
        <select id="sort" name="sort">
            <option value="" >请选择</option>
            <option value="paid_money">已付款金额</option>
        </select>
        <button type="button" class="button button-small" id="sort_btn" onclick = "sort()">排序</button>
        <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="排序所有页签"/>

    </li>
</ul>-->
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//            array(
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '70',
//                'align' => '',
//                'buttons' => array(
//                ),
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单编号',
                'field' => 'record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({record_code})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_date',
                'width' => '90',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '110',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号',
                'field' => 'receiver_phone',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'goods_num',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应收金额',
                'field' => 'payable_amount',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家实付金额',
                'field' => 'buyer_real_amount',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单备注',
                'field' => 'remark',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms_shop/OmsShopModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'record_id',
//    'customFieldTable' => 'oms/shop_sell_record_do_list',
    'export' => array('id' => 'exprot_detail', 'conf' => 'shop_sell_record_list', 'name' => '门店订单列表', 'export_type' => 'file'),
    'CellEditing' => true,
    'CascadeTable' => array(
        'list' => array(
//            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '销售数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
            array('title' => '退货数量', 'type' => 'text', 'width' => '100', 'field' => 'return_num'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'price'),
            array('title' => '商品总金额', 'type' => 'text', 'width' => '100', 'field' => 'goods_amount'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms_shop/oms_shop/get_detail_by_code&app_fmt=json'),
        'params' => 'record_code'
    ),
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<div id="msg_id"></div>
<script type="text/javascript">
    function sort() {
        tableStore.load();
    }
    function view(record_code) {
        var url = '?app_act=oms_shop/oms_shop/view&record_code=' + record_code;
        openPage(window.btoa(url), url, '订单详情');
    }
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(document).ready(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.ex_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            e.params.is_normal = $("input[name='is_normal']:checked").val();
            var sort_e = $("#sort  option:selected");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val()
            }
            tableStore.set("params", e.params);
        });
        tableStore.load();
    });

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.record_code);
        }
        ids.join(',');
        if (obj.text() == '批量发货') {
            func.apply(null, [ids]);
        } else {
            BUI.Message.Show({
                title: '自定义提示框',
                msg: '是否执行订单' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids]);
                            this.close();
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }
    }

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_opt_" + id).click(function () {

            get_checked($(this), function (ids) {
                if (id == 'print_ticket') {
                    var record_code_list = get_record_code_list(ids);
                    var u = '?app_act=tprint/tprint/do_print&print_templates_code=cashier_ticket&record_ids=' + record_code_list;
                    $("#print_iframe").attr('src', u);

                    return;
                }

                var params = {"record_code_list": ids, "type": id, "batch": "批量操作"};
                $.post("?app_act=oms_shop/oms_shop/opt_batch", params, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'info');
                        //刷新
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        });
    }
    function get_record_code_list(ids) {
        var record_code_list = '';
        for (var key in ids) {
            record_code_list += ids[key] + ",";
        }
        return  record_code_list.substring(0, record_code_list.length - 1);
    }

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code') ?>' + row.sell_record_id, '?app_act=oms/sell_record/view&ref=ex&sell_record_code=' + row.sell_record_code, '订单详情');
    }

    tableCellEditing.on('accept', function (record) {
        var params = {
            "sell_record_code": record.record.sell_record_code,
            "express_code": record.record.express_code,
            "express_no": record.record.express_no
        }
        $.post("?app_act=oms/sell_record/edit_express", params, function (data) {
            if (data.status != 1) {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json")
    });

    function a_key_confirm() {
        $.get("?app_act=oms/sell_record/a_key_confirm&app_fmt=json", function (data) {
            if (data.status < 1) {
                BUI.Message.Alert(data.message, 'error');
            } else {
                get_log(data.data, 0);
            }
            tableStore.load();
        }, "json")
    }

    function get_log(task_id, log_file_offset) {
        var request_data = {
            'task_id': task_id,
            'log_file_offset': log_file_offset,
            'timestamp': new Date().getTime()
        }
        //才页面功能已经实现，也可以跟进自己页面进行自行增加
        var ajax_url = '?app_act=sys/sys_schedule/get_task_log&app_fmt=json';
        $.post(ajax_url, request_data, function (data) {
            var result = eval('(' + data + ')')
            if (result == '') {
                return;
            }
            // msg_id 为存储信息的页面DOM ID
            $('#msg_id').prepend(result.msg);
            if (result.code == 0) {
                //2秒获取1次信息
                setTimeout(function () {
                    get_log(result.task_id, result.log_file_offset);
                }, 2000);
            }
        });
    }
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>
    <?php echo load_js('task.js', true); ?>

<?php include_once (get_tpl_path('process_batch_task')); ?>


