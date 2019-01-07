<?php echo load_js("baison.js,record_table.js,pur.js", true); ?>
<?php require_lib('util/oms_util', true); ?>
<?php echo load_js('xlodop.js'); ?>
<?php echo load_js('lodop.js'); ?>
<style>
    .panel-body {padding: 0;}
    .panel-body table {margin: 0; }
    .panel-content {padding: 0;}
    .panel-content table {margin: 0; }
    .form-panel .panel-content { padding: 0;}
    .form-panel .checkbox input {width: 15px;}
    .form-panel label {margin-left: 10px;}
    .bui-grid-row-read {color:red;font-weight:bold;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .like_link{text-decoration:underline;color:#428bca;cursor:pointer;}
    .bui-select .bui-select-input {width: 105px}
</style>

<?php
$links = array(
    array('type' => 'js', 'js' => 'distribute_pick_member()', 'title' => '分配拣货员'),
    array('type' => 'js', 'js' => 'waves_record_export()', 'title' => '导出波次订单'),
    array('type' => 'js', 'js' => 'import_express_no()', 'title' => '导入快递单号')
);
render_control('PageHead', 'head1', array('title' => '查看订单波次',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<div class="panel record_table" id="panel_html">
</div>

<div id='toolbar_middle' style='margin-top: 10px; height: auto; line-height: 200%;'>
    <ul class="toolbar" style="margin-top: 10px; height: auto; line-height: 200%;">
        <li><button class="button button-primary btn_edit_express_no">自动匹配物流单号</button></li>
        <li><button class="button button-primary btn_opt_print_express">打印所有快递单</button></li>
        <li><button class="button button-primary btn_opt_print_express_selected">打印部分快递单<span class="x-caret x-caret-down"></span></button></li>
        <li><button class="button button-primary btn_opt_print_sellrecord">打印所有发货单</button></li>
        <li><button class="button button-primary btn_opt_print_sellrecord_selected">打印部分发货单<span class="x-caret x-caret-down"></span></button></li>
        <li><button class="button button-primary btn_opt_print_goods">打印波次单</button></li>
        <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
            <li><button class="button button-primary btn_opt_print_goods_clothing">打印波次单（服装行业）</button></li>
        <?php } ?>
        <?php if ($response['data']['auth_print_invoice']) { ?>
            <li><button class="button button-primary btn_opt_print_invoice">发票打印（套打）</button></li>
        <?php } ?>
        <li><button class="button button-primary btn_opt_thermal_no_103">获取云栈热敏物流</button></li>
        <li><button class="button button-primary btn_opt_thermal_no_sf">获取顺丰热敏物流</button></li>
        <li><button class="button button-primary btn_opt_thermal_no_jd">获取京东热敏物流</button></li>
        <li><button class="button button-primary btn_opt_thermal_no_alpha">获取无界热敏物流</button></li>
        <li><button class="button button-primary btn_edit_express_code">修改配送方式</button></li>
        <li><button class="button button-primary btn_edit_express_code_all">一键修改配送方式</button></li>
        <li><button class="button button-primary btn_opt_thermal_all">一键获取云栈热敏物流</button></li>
    </ul>
</div>


<ul class="toolbar frontool frontool_center" id="tool">
    <?php
    if ($response['data']['is_accept'] == 1 || $response['data']['is_cancel'] == 1) {

    } else {
        ?>
        <li class="li_btns"><button class="button button-primary " onclick="do_accept('accept')">验收</button></li>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_cancel')) { ?>
            <li class="li_btns"><button class="button button-primary " onclick="do_cancel()">整单取消</button></li>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/waves_record/do_accept_and_send')) { ?>
            <li class="li_btns"><button class="button button-primary " onclick="do_accept('send')">验收且发货</button></li>
        <?php } ?>
        <?php
    }
    if ($response['data']['is_accept'] == 1 && $response['data']['is_deliver'] != 1 && $response['data']['is_cancel'] == 0) {
        ?>
        <li class="li_btns"><button id="btn_waves_send" class="button button-primary " onclick="waves_send(<?php echo $response['data']['record_code']; ?>)">整单发货</button></li>
        <li class="li_btns"><button id="btn_waves_batch_send" class="button button-primary " onclick="waves_batch_send()">批量发货</button></li>
    <?php } ?>
    <?php if ($response['data']['is_deliver'] != 0) { ?>
        <li class="li_btns"><button id="btn_waves_weight" class="button button-primary " onclick="waves_weight(<?php echo $response['data']['record_code']; ?>)">整单称重</button></li>
    <?php } ?>
    <?php if ($response['data']['is_deliver'] == 1) { ?>
        <li class="li_btns"><button  class="button button-primary " onclick="waves_back(<?php echo $response['data']['record_code']; ?>)">整单回写</button></li>
    <?php } ?>
    <li class="li_btns"><button id="btn_waves_back" class="button button-primary " onclick="openPage(window.btoa('?app_act=oms/waves_record/do_list'), '? app_act=oms/waves_record/do_list', '订单波次打印')">返回</button></li>
    <div class="front_close">&lt;</div>
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

<?php $expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 1); ?>
<form class="form-panel" action="post" style="margin-top: 10px;">
    <div class="panel-title">
        <span>
            波次订单列表
        </span>
    </div>
    <ul class="panel-content">
        <li>
            <label>配送方式:</label>
            <select name="express_code" id="express_code">
                <?php foreach ($expressList as $k => $v) { ?>
                    <option value="<?php echo $k ?>"><?php echo $v ?></option>
                <?php } ?>
            </select>
            <label>快递单打印:</label>
            <select name="is_print_express" id="is_print_express">
                <option value="">全部</option>
                <option value="1">已打印</option>
                <option value="0">未打印</option>
            </select>
            <label>发货单打印:</label>
            <select name="is_print_sellrecord" id="is_print_sellrecord">
                <option value="">全部</option>
                <option value="1">已打印</option>
                <option value="0">未打印</option>
            </select>
            <label>发货状态:</label>
            <select name="is_deliver" id="is_deliver">
                <option value="">全部</option>
                <option value="1">已发货</option>
                <option value="0">未发货</option>
            </select>
            <span id="is_back">回写状态:</span>
            <input type="hidden" id="hide" value="" name="hide">
            <script type="text/javascript">
                BUI.use('bui/select', function (Select) {
                    var items = [
                        {text: '未回写', value: '0,-1'},
                        {text: '已平台回写', value: '1'},
                        {text: '已本地回写', value: '2'}
                    ],
                            select = new Select.Select({
                                valueField: '#hide',
                                render: '#is_back',
                                multipleSelect: true,
                                items: items
                            });
                    select.render();
                });
            </script>
            <?php if ($response['data']['auth_print_invoice']) { ?>
                <label>发票打印:</label>
                <select name="is_print_invoice" id="is_print_invoice">
                    <option value="">全部</option>
                    <option value="1">已打印</option>
                    <option value="0">未打印</option>
                </select>
            <?php } ?>
        </li>
        <li>
            &nbsp; <input type="text" name="sell_record_code" style="width:150px" id="sell_record_code" value="" placeholder="订单号/交易号/买家昵称/收货人/快递单号/货号" title="订单号/交易号/买家昵称/收货人/快递单号/货号" /><img height="25" width="25" title="以下字段支持查询：订单号、交易号、买家昵称、收货人、快递单号、货号" alt="" src="assets/images/tip.png">
            <button type="button" class="button button-primary" onclick="do_search()">搜索订单</button>
        </li>
        <li style="overflow: visible; height: auto;">
            <div style="clear: both;"></div>
            <div id="panel_shipping">
                <?php
                $expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
                $storeList = oms_opts2_by_tb('base_store', 'store_code', 'store_name', array('status' => 1), 2);
                $list = array();
                $list[] = array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '操作',
                    'field' => '_operate',
                    'width' => '100',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'pe', 'title' => '打印快递单', 'callback' => 'do_print_express', 'confirm' => '确认要打印快递单吗？', 'show_cond' => 'obj.is_cancel == 0 && (' . $response['data']['is_accept'] . ' == 0 && ' . $response['data']['is_cancel'] . ' == 0)'),
                        array('id' => 'edit_receiver', 'title' => '修改收货地址', 'callback' => 'do_edit_receiver', 'show_cond' => 'obj.is_cancel == 0 && (' . $response['data']['is_accept'] . ' == 0 && ' . $response['data']['is_cancel'] . ' == 0)'),
                        array('id' => 'cancel', 'title' => '取消发货', 'callback' => 'do_cancel_detail', 'show_cond' => 'obj.is_cancel == 0 && (' . $response['data']['is_accept'] . ' == 0 && ' . $response['data']['is_cancel'] . ' == 0 && ' . $response['is_allow_do_cancel'] . ' == 1)'),
                        array('id' => 'cancel_waves', 'title' => '取消波次单', 'callback' => 'do_cancel_waves', 'show_cond' => 'obj.is_cancel == 0 && (' . $response['data']['is_accept'] . ' == 0 && ' . $response['data']['is_cancel'] . ' == 0 && ' . $response['is_allow_do_cancel_waves'] . ' == 1)'),
//                        array('id' => 'cancel_bind', 'title' => '解绑运单号', 'callback' => 'do_unbind_express', 'show_cond' => 'obj.print_type == 3 && obj.is_cancel == 0 && obj.express_no !="" && (' . $response['data']['is_cancel'] . ' == 0 && ' . $response['is_allow_do_cancel_waves'] . ' == 1)'),
                    ),
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '序号',
                    'field' => 'sort_no',
                    'width' => '40',
                    'align' => '',
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单编号',
                    'field' => 'sell_record_code_href',
                    'width' => '120',
                    'align' => ''
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '交易号',
                    'field' => 'deal_code_list',
                    'width' => '120',
                    'align' => ''
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品数量',
                    'field' => 'goods_num',
                    'width' => '120',
                    'align' => ''
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_code',
                    'format_js' => array('type' => 'map', 'value' => $expressList),
                    'width' => '80',
                    'align' => '',
                    'editor' => "{xtype : 'select', items: " . json_encode($expressList) . "}"
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '物流单号',
                    'field' => 'express_no',
                    'width' => '160',
                    'align' => '',
                    'editor' => "{xtype : 'text'}",
                );
                //
                //判断包裹
                if ($response['is_more_deliver_package'] == 1) {
                    $list[] = array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '选择包裹',
                        'field' => 'package_no',
                        'width' => '120',
                        'align' => '',
                        'format_js' => array('type' => 'html', 'value' => '<select code="{sell_record_code}" name="{package_no}"  class="package_select"></select>'),
                    );
                }
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '买家昵称',
                    'field' => 'buyer_name',
                    'width' => '80',
                    'align' => '',
                    'format_js' => array(
                        'type' => 'function',
                        'value' => 'check_buyer_name')
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收货人',
                    'field' => 'receiver_name',
                    'width' => '80',
                    'align' => '',
                    'format_js' => array(
                        'type' => 'function',
                        'value' => 'check_name',
                    ),
                );

                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收货地址',
                    'field' => 'receiver_address',
                    'width' => '120',
                    'align' => '',
                    'format_js' => array(
                        'type' => 'function',
                        'value' => 'check_address',
                    ),
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '打印状态',
                    'field' => 'print_status',
                    'width' => '130',
                    'align' => '',
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '发货状态',
                    'field' => 'deliver_status',
                    'width' => '80',
                    'align' => '',
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '下单时间',
                    'field' => 'record_time',
                    'width' => '90',
                    'align' => ''
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '店铺',
                    'field' => 'shop_name',
                    'width' => '100',
                    'align' => ''
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '买家留言',
                    'field' => 'buyer_remark',
                    'width' => '120',
                    'align' => 'center'
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '商家留言',
                    'field' => 'seller_remark',
                    'width' => '120',
                    'align' => 'center'
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '订单备注',
                    'field' => 'order_remark',
                    'width' => '120',
                    'align' => 'center'
                );
                $list[] = array(
                    'type' => 'text',
                    'show' => 0,
                    'title' => '仓库留言',
                    'field' => 'store_remark',
                    'width' => '120',
                    'align' => 'center'
                );
                render_control('DataTableForWavesRecord', 'table', array(
                    'conf' => array(
                        'list' => $list,
                    ),
                    'dataset' => 'oms/DeliverRecordModel::get_by_page',
                    'params' => array('filter' => array('waves_record_id' => $request['waves_record_id'], 'page_size' => 100)),
                    'cookie_page_size' => 'waves_view_page_size',
                    //'queryBy' => 'searchForm',
                    'idField' => 'deliver_record_id',
                    'itemStatusFields' => '{read:"readed"}',
                    'CellEditing' => true,
                    'customFieldTable' => 'wave_record_view/table',
                    //'RowNumber'=>true,
                    'CheckSelection' => true,
                    'events' => array(
                    //'rowdblclick' => 'showDetail',
                    ),
                ));
                ?>
            </div>
        </li>
    </ul>
</form>

<!--无界顺丰运单号获取配置-->
<div id="wujie_sf_set" style="display: none;">
    <div  class="control-group">
        <label class="control-label span3">快递费付款方式：</label>
        <select id="express_pay_method">
            <option value="1">寄方付</option>
            <option value="2">收方付</option>
            <option value="3">第三方付</option>
        </select>
    </div>
    <div  class="control-group" style="margin-top: 10px;">
        <label class="control-label span3">快件产品类别：</label>
        <select id="express_type">
            <option value="1">顺丰次日</option>
            <option value="2">顺丰隔日</option>
        </select>
    </div>
</div>

<script type="text/javascript">
    var wavesRecordId = <?php echo $request['waves_record_id'] ?>;
    var record_code = <?php echo $response['data']['record_code'] ?>;
    var wave_check = <?php echo $response['wave_check'] ?>;
    var wave_match_express = "<?php echo $response['wave_match_express'] ?>";
    var deliver_ids = "<?php echo $response['deliver_ids']; ?>";
    var no_exist_express_no = "<?php echo $response['no_exist_express_no']; ?>";
    var no_print_express = "<?php echo $response['no_print_express']; ?>";
    var print_record_template = "<?php echo $response['print_delivery_record_template']; ?>";
    var new_clodop_print = "<?php echo $response['new_clodop_print']; ?>";
    var is_valuation = "<?php echo $response['is_valuation']; ?>";
    var is_more_package = "<?php echo $response['is_more_deliver_package'] ?>";
    var opts = [
        'edit_express_code', 'edit_express_no', 'opt_print_goods', 'opt_print_goods_clothing',
        'opt_print_express', 'opt_print_express_selected', 'opt_print_sellrecord', 'opt_print_sellrecord_selected',
        'opt_accept', 'opt_print_invoice',
        'opt_thermal_no_102', 'opt_thermal_no_103', 'opt_thermal_print', 'opt_thermal_update'
    ];

    var dataRecord = [
        {'title': '波次号', 'type': 'input', 'name': 'record_code', 'value': '<?php echo $response['data']['record_code'] ?>'},
        {'title': '业务日期', 'type': 'input', 'name': 'record_time', 'value': '<?php echo $response['data']['record_time'] ?>'},
        {'title': '仓库', 'type': 'input', 'name': 'store_code', 'value': '<?php echo oms_tb_val('base_store', 'store_name', array('store_code' => $response['data']['store_code'])) ?>'},
        {'title': '有效订单数量', 'type': 'input', 'name': 'total_amount', 'value': '<?php echo $response['data']['sell_record_count'] . " (<strong>订单总数量：" . $response['data']['sell_record_count_all'] . "</strong>)" ?>'},
        {'title': '总金额', 'type': 'input', 'name': 'total_amount', 'value': '<?php echo round($response['data']['total_amount'], 2) . '（<strong>有效订单汇总金额）</strong>' ?>'},
        {'title': '商品有效数量', 'type': 'input', 'name': 'goods_count', 'value': '<?php echo $response['data']['valide_goods_count'] . " (<strong>总商品数量：" . $response['data']['goods_count'] . "</strong>)" ?>'},
        {'title': '验收', 'type': 'input', 'name': 'sell_record_count', 'value': '<?php echo $response['data']['is_accept'] == 1 ? "<img src=\'assets/images/ok.png\'/>" : "<img src=\'assets/images/no.gif\'/>" ?>'},
        {'title': '波次单打印', 'type': 'input', 'name': 'is_print_waves', 'value': '<?php echo $response['data']['is_print_waves'] == 1 ? "<img src=\'assets/images/ok.png\'/>" : "<img src=\'assets/images/no.gif\' />" ?>'},
        {'title': '拣货员', 'type': 'input', 'name': 'pick_member_name', 'value': '<?php echo $response['data']['pick_member_name'] ?>'},
        {'title': '波次备注', 'type': 'input', 'name': 'sell_num_type', 'value': '<?php echo $response['data']['sell_num_type'] == 1 ? '一单一品' : '一单多品' ?>'}
    ];

    $(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": false,
            "edit_url": "?app_act=oms/waves_record/do_edit"
        });

        //初始化按钮
        btn_init();
        tableCellEditing.on('accept', function (record) {
            var params = {
                "sell_record_code": record.record.sell_record_code,
                "express_code": record.record.express_code,
                "express_no": record.record.express_no.trim(),
                "is_force": 1
            };
            var str = params.express_no;
            if (str != '') {
                var reg = new RegExp(/^[0-9A-Za-z]+$/);
                if (!reg.test(str)) {
                    BUI.Message.Alert("快递单号必须为数字或者字母", 'error');
                    return false;
                }
            }
            $.post("?app_act=oms/deliver_record/edit_express", params, function (data) {
                if (data.status < 0) {
                    BUI.Message.Alert(data.message, 'error');
                } else if (data.status == 1) {
                    BUI.Message.Tip(data.message, 'success');
                }
                no_exist_express_no = false;
                tableStore.load();
            }, "json");
        });

        //京东热敏
        $(".btn_opt_thermal_no_jd").click(function () {
            if (is_valuation == 1) {
                var url = "?app_act=oms/waves_record/is_guarantee";
                new ESUI.PopWindow(url, {
                    title: "是否保价",
                    width: 400,
                    height: 300,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                    }
                }).show();
            } else {
                get_jd_express_no();
            }
        });

        //获取京东热敏，并保存保价金额
        parent.get_jd_express_code = function (guarantee_money) {
            get_jd_express_no(guarantee_money);
        };

        function get_jd_express_no(guarantee_money) {
            var a = arguments[0] ? arguments[0] : '';
            get_checked(false, $(this), function (ids) {
                ids = ids.toString();
                $.post('?app_act=oms/deliver_record/jd_etms_waybillcode_get', {waves_record_id: wavesRecordId, record_ids: ids, guarantee_money: guarantee_money}, function (data) {
                    if (data.status == 1) {
                        tableStore.load();
                    } else {
                        var d_msg = '';
                        if (typeof data.data != "undefined") {
                            d_msg = data.data.toString();
                        }
                        BUI.Message.Alert(data.message + "<br>" + d_msg, 'error');
                    }
                }, "json");
            });
        }

        //顺丰热敏
        $(".btn_opt_thermal_no_sf").click(function () {
            get_checked(false, $(this), function (ids) {
                ids = ids.toString();
                new ESUI.PopWindow("?app_act=remin/shunfeng/get_express_no&waves_record_id=" + wavesRecordId + "&record_ids=" + ids, {
                    title: "获取快递单号",
                    width: 600,
                    height: 500,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load();
                    }
                }).show();
            });
        });

        if (is_more_package == 1) {
            tableStore.on('load', function (e) {
                setTimeout(function () {
                    set_select_package();
                }, 100);
            });
        }
    });

    function set_data_record_express_no(sell_record_code, package_no, express_no) {
        var records = tableStore.getResult();
        $.each(records, function (index, row) {
            if (row.sell_record_code == sell_record_code) {
                row.express_no = express_no;
                row.package_no = package_no;
                records[index] = row;
                $('#table td[data-column-field="express_no"]').eq(index).find('.bui-grid-cell-text').text(express_no);

            }
        });
        tableStore.setResult(records);
    }

    function set_select_package() {
        if (is_more_package == 1) {
            var package_data = <?php echo json_encode($response['package_data']); ?>;
            $('.package_select option').remove();
            $.each(package_data, function (val, name) {
                $('.package_select').append('<option value="' + val + '">' + name + '</option>');
            });

            $('.package_select').each(function () {
                var select = $(this).attr('name');
                $(this).find('option[value="' + select + '"]').attr("selected", true);
            });
            $('.package_select').change(function () {
                var url = "?app_act=oms/deliver_record/get_package_express_no&app_fmt=json";
                var data = {};
                data.package_no = $(this).val();
                data.sell_record_code = $(this).attr('code');
                $(this).attr('name', data.package_no);
                var _this = this;
                $.post(url, data, function (ret) {
                    if (ret.status < 0) {
                        $(_this).find('option[value="1"]').attr("selected", true);
                        BUI.Message.Alert(ret.message, 'error');
                    } else {
                        set_data_record_express_no(data.sell_record_code, data.package_no, ret.data);
                    }
                }, "json");
            });
        }
    }

    function do_edit_receiver(_index, row) {
        new ESUI.PopWindow("?app_act=oms/deliver_record/edit_receiver&deliver_record_id=" + row.deliver_record_id, {
            title: "修改收货地址",
            width: 800,
            height: 350,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load();
            }
        }).show();
    }

    function do_search() {
        tableStore.on('beforeload', function (e) {
            e.params.express_code = $("#express_code").val();
            e.params.is_print_express = $("#is_print_express").val();
            e.params.is_print_sellrecord = $("#is_print_sellrecord").val();
            e.params.is_print_invoice = $("#is_print_invoice").val();
            e.params.is_deliver = $("#is_deliver").val();
            e.params.sell_record_code = $("#sell_record_code").val();
            e.params.is_back = $("#hide").val();
        });
        tableStore.load();
    }

    function getCheckboxValue(cname) {
        var arr = [];
        $("input[name=" + cname + "]:checked").each(function () {
            arr.push(this.value);
        });
        return arr.toString();
    }

    //初始化批量操作按钮
    function btn_init() {
        for (var i in opts) {
            var f = opts[i];
            switch (f) {
                case "edit_express_code":
                    btn_init_edit_express_code();
                    break
                case "edit_express_no":
                    btn_init_edit_express_no();
                    break
                case "opt_print_goods":
                    btn_init_opt_print_goods();
                    break
                case "opt_print_goods_clothing":
                    btn_init_opt_print_goods_clothing();
                    break
                case "opt_print_express":
                    btn_init_opt_print_express();
                    break
                case "opt_print_sellrecord":
                    btn_init_opt_print_sellrecord();
                    break
                case "opt_print_invoice":
                    btn_init_opt_print_invoice();
                    break
                case "opt_thermal_no_102":
                    btn_init_opt_thermal_no_102();
                    break
                case "opt_thermal_no_103":
                    btn_init_opt_thermal_no_103();
                    break
                case "opt_thermal_print":
                    btn_init_opt_thermal_print();
                    break
                case "opt_thermal_update":
                    btn_init_opt_thermal_update();
                    break
            }
        }
    }

    //读取已选中项
    function get_checked(isConfirm, obj, func) {
        _get_checked("selected", isConfirm, obj, func);
    }

    //读所有项
    function get_all(isConfirm, obj, func) {
        _get_checked("all", isConfirm, obj, func);
    }

    //读取已选中项
    function _get_checked(typ, isConfirm, obj, func) {
        var ids = [];
        var selecteds;

        if (typ == "all") {
            selecteds = tableGrid.getItems();
        } else {
            selecteds = tableGrid.getSelection();
        }

        for (var i in selecteds) {
            ids.push(selecteds[i].deliver_record_id);
            express_arr.push(selecteds[i].express_code);
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }

        if (isConfirm) {
            BUI.Message.Show({
                title: '订单操作',
                msg: '是否执行订单' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids]);
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
        } else {
            func.apply(null, [ids]);
        }
    }

    //批量修改配送方式
    function btn_init_edit_express_code() {
        $(".btn_edit_express_code").click(function () {
            get_checked(false, $(this), function (ids) {
                new ESUI.PopWindow("?app_act=oms/deliver_record/edit_express_code&type=0&record_code=" + record_code + "&deliver_record_id_list=" + ids.toString(), {
                    title: "批量修改配送方式",
                    width: 500,
                    height: 250,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load();
                    }
                }).show();
            });
        });

        $(".btn_edit_express_code_all").click(function () {
            new ESUI.PopWindow("?app_act=oms/deliver_record/edit_express_code&record_code=" + record_code + "&type=1", {
                title: "一键修改配送方式",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    tableStore.load();
                }
            }).show();

        });
    }

    //自动匹配物流单号
    function btn_init_edit_express_no() {
        $(".btn_edit_express_no").click(function () {
            get_checked(false, $(this), function (ids) {
                new ESUI.PopWindow("?app_act=oms/deliver_record/edit_express_no&deliver_record_id_list=" + ids.toString(), {
                    title: "自动匹配物流单号",
                    width: 800,
                    height: 600,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load();
                    }
                }).show();
            });
        });
    }

    //自动匹配物流单号（匹配波次单所有的订单）
    function edit_express_no_all() {
        var param = "&express_code=" + $("#express_code").val();
        param += "&express_no=" + '';
        param += "&is_print_express=" + $("#is_print_express").val();
        param += "&is_print_sellrecord=" + $("#is_print_sellrecord").val();
        param += "&is_deliver=" + $("#is_deliver").val();
        param += "&is_back=" + $("#is_back").val();
        param += "&sell_record_code=" + $("#sell_record_code").val();
        new ESUI.PopWindow("?app_act=oms/deliver_record/edit_express_no_all&deliver_record_id_list=" + deliver_ids + "&waves_record_ids=" + wavesRecordId + param, {
            title: "自动匹配物流单号",
            width: 800,
            height: 600,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load();
            }
        }).show();

    }

    function print_default(t, ids, print_type = 1) {
        if (new_clodop_print == 1 || print_type == 2) {
            var template = '';
            if (print_type == 1) {
                template = 'oms_waves_record_new';
            } else {
                template = 'oms_waves_record_clothing';
            }
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=" + template + "&record_ids=" + ids.toString(), {
                title: "波次单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        } else {
            var url = '?app_act=sys/flash_print/do_print&template_id=27&model=oms/WavesRecordModel&typ=default&record_ids=' + ids;
            var window_is_block = window.open(url);
            if (null == window_is_block) {
                alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
            }
    }
    }

    //打印波次单(商品)
    function btn_init_opt_print_goods() {
        $(".btn_opt_print_goods").click(function () {
            $.post('?app_act=oms/waves_record/mark_print', {wave_record_ids: wavesRecordId}, function (data) {
                print_default("oms_waves_record", wavesRecordId);
            });
        });
    }
    function btn_init_opt_print_goods_clothing() {
        $(".btn_opt_print_goods_clothing").click(function () {
            var check_url = "?app_act=oms/waves_record/check_is_print_record&app_fmt=json";
            $.post(check_url, {wave_record_ids: wavesRecordId}, function (ret) {
                if (ret.status == 1) {
                    $.post('?app_act=oms/waves_record/mark_print', {wave_record_ids: wavesRecordId}, function (data) {
                        print_default("oms_waves_record", wavesRecordId, 2);
                    });
                } else if (ret.status == -2) {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }, 'json')
        })
    }

    //打印所有快递单
    function btn_init_opt_print_express() {
        $(".btn_opt_print_express").click(function () {
            print_express("", 0, 0, 'print_all_express');
        });
    }

    //打印所有发货单(订单)
    function btn_init_opt_print_sellrecord() {
        $(".btn_opt_print_sellrecord").click(function () {
            print_sellrecord("", 0, 0, 'print_all_sellrecord');
        });
    }
    //发票打印
    function btn_init_opt_print_invoice() {
        $(".btn_opt_print_invoice").click(function () {
            get_checked(false, $(this), function (ids) {
                if (new_clodop_print == 1) {
                    new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=invoice_record&record_ids=" + ids.toString(), {
                        title: "发票打印",
                        width: 500,
                        height: 220,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                        }
                    }).show();
                } else {
//                    if (print_record_template == 1) {
//                        var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&record_ids=' + ids.toString();
////                        $("#print_iframe").attr('src',u);
//                        var id = 'print_iframe';
//                        var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
//                        iframe.attr('src', u);
//                    } else {
                    var u = '?app_act=sys/flash_print/do_print_td'
                    u += '&template_id=31&model=oms/InvoiceRecordModel&typ=default&record_ids=' + ids.toString();
                    window.open(u);
                    //}
                }
            });
        });
    }


    function btn_init_opt_thermal_no_102() {


    }

    var dialog_sf;
    BUI.use('bui/overlay', function (Overlay) {
        dialog_sf = new Overlay.Dialog({
            title: '获取顺丰无界热敏物流',
            width: 400,
//            height: 150,
            contentId: 'wujie_sf_set',
            success: function () {
                dialog_sf.hide();
                check_status(wujie_ids, 'alpha');
            }
        });
    });

    //获取无界热敏物流单号
    var express_arr = new Array();
    var wujie_ids;
    $(".btn_opt_thermal_no_alpha").click(function () {
        get_checked(false, $(this), function (ids) {
            wujie_ids = ids.toString();
            if ($.inArray('SF', express_arr) !== -1) {
                dialog_sf.show();
            } else {
                check_status(wujie_ids, 'alpha');
            }
        });
    });

    function getLogistics(ids, print_type) {
        var param = {print_type: print_type, is_all: 0, waves_record_id: wavesRecordId, record_ids: ids, express_pay_method: $("#express_pay_method").val(), express_type: $("#express_type").val()};
        $.post('?app_act=oms/deliver_record/get_logistics', param, function (data) {
            parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
            parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
            var msg = data.message;
            if (data.status == 1) {
                BUI.Message.Alert(msg, 'success');
            } else {
                BUI.Message.Alert(msg, 'error');
            }
            tableStore.load();
        }, "json");
    }


    //获取云栈热敏物流单号
    function btn_init_opt_thermal_no_103() {
        $(".btn_opt_thermal_no_103").click(function () {
            get_checked(false, $(this), function (ids) {
                ids = ids.toString();
                check_status(ids, 'yz');
            });
        });

        $(".btn_opt_thermal_all").click(function () {
            parent.BUI.use('bui/overlay', function (Overlay) {
                var dialog = new Overlay.Dialog({
                    title: '一键获取云栈热敏物流',
                    width: 300,
                    height: 130,
                    mask: true,
                    buttons: [
                        {
                            text: '',
                            elCls: 'bui-grid-cascade-collapse',
                            handler: function () {
                                this.close();
                            }
                        }
                    ],
                    bodyContent: '获取热敏物流数据中，请稍后...'
                });
                dialog.show();
            });
            check_all_status();
        });
    }

    function check_status(ids, print_type) {
        var txt = print_type === 'yz' ? "云栈" : '无界';
        parent.BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: '获取' + txt + '热敏物流',
                width: 300,
                height: 130,
                mask: true,
                buttons: [
                    {
                        text: '',
                        elCls: 'bui-grid-cascade-collapse',
                        handler: function () {
                            this.close();
                        }
                    }
                ],
                bodyContent: '获取热敏物流数据中，请稍后...'
            });
            dialog.show();
        });

        if (print_type === 'yz') {
            $.post('?app_act=oms/deliver_record/check_status', {record_ids: ids}, function (data) {
                if (data.status == '1') {
                    check_express_type(ids, 0);
                } else {
                    show_status(data.data, ids, 0);
                }
            }, 'json');
        } else {
            getLogistics(ids, print_type);
        }
    }

    function check_all_status() {
        $.post('?app_act=oms/deliver_record/check_all_status', {waves_record_id: wavesRecordId}, function (data) {
            if (data.status == '1') {
                check_express_type(wavesRecordId, 1);
            } else {
                show_status(data.data, wavesRecordId, 1);
            }
        }, 'json');
    }

    function check_express_type(ids, id_type) {
        var params = {record_ids: ids, id_type: id_type};
        $.post('?app_act=oms/deliver_record/check_express_type', params, function (data) {
            if (data.status != 1) {
                //普通云栈获取提示更新信息
                show_change_msg(ids, id_type, 1);
            } else {
                //云打印
                get_waybill(ids, id_type, 2);
            }
        }, "json");
    }

    function get_waybill(ids, type, print_type) {
        $.post('?app_act=oms/deliver_record/tb_wlb_waybill_get', {waves_record_id: wavesRecordId, record_ids: ids, type: type, print_type: print_type}, function (data) {
            parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
            parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
            if (data.status == 1) {
                tableStore.load();
            } else {
                var msg = data.message;
                $.each(data.data, function (i, k) {
                    msg += k;
                });
                BUI.Message.Alert(msg, 'error');
            }
        }, "json");
    }

    function show_status(data, ids, type) {
        parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        BUI.Message.Show({
            title: '获取云栈热敏',
            msg: "订单号<br/>" + data + "<br/>已获取云栈热敏，是否再次获取？",
            icon: 'question',
            buttons: [
                {
                    text: '是',
                    elCls: 'button button-primary',
                    handler: function () {
                        check_express_type(ids, type);
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

    function btn_init_opt_thermal_print() {

    }

    function btn_init_opt_thermal_update() {

    }

    //验收
    function btn_init_opt_accept() {
        $(".btn_opt_accept").click(function () {
            do_accept();
        });
    }

    function do_accept(type) {
        var msg = '系统检测到';
        if (no_exist_express_no == true) {
            msg += '单据未匹配快递单号，';
        }
        if (no_print_express == true) {
            msg += '波次单中存在单据快递单未打印，';
        }
        if (no_print_express == true || no_exist_express_no == true) {
            !BUI.Message.Confirm(msg + '请确认是否验收波次单？', function () {
                do_confirm_action(type);
            }, 'warning');
        } else {
            do_confirm_action(type);
        }

    }

    function do_confirm_action(type) {
        if (wave_check == '1') {
            new ESUI.PopWindow("?app_act=oms/waves_record/accept&waves_record_id=<?php echo $request['waves_record_id'] ?>", {
                title: "验收 - <?php echo $response['data']['record_code'] ?>",
                width: 800,
                height: 600,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据

                    //tableStore.load();
                    //location.reload();
                    if (type == 'send') {
                        waves_send(<?php echo $response['data']['record_code'] ?>);
                    } else {
                        location.reload();
                    }
                }
            }).show();

        } else {
            //强制验收
            var params = {waves_record_id: "<?php echo $request['waves_record_id'] ?>", is_scan: 0};
            $.post("?app_act=oms/waves_record/accept_action", params, function (data) {
                if (data.status != 1) {
                    BUI.Message.Alert(data.message, function () {
                        location.reload();
                    }, 'error');
                } else {

                    BUI.Message.Alert('强制验收成功', function () {
                        //location.reload();
                        if (type == 'send') {
                            waves_send(<?php echo $response['data']['record_code'] ?>);
                        } else {
                            location.reload();
                        }
                    }, 'info');

                }
            }, "json");
        }
    }

    function do_print_express(_index, row) {
        print_express(row.deliver_record_id);
    }


    //取消
    function do_cancel() {
        new ESUI.PopWindow("?app_act=oms/waves_record/edit_remark&waves_record_id=" + wavesRecordId, {
            title: "取消原因",
            width: 500,
            height: 225,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load();
            }
        }).show();
    }

    //取消
    function do_cancel_detail(_index, row) {
        new ESUI.PopWindow("?app_act=oms/waves_record/edit_remark&deliver_record_id=" + row.deliver_record_id + "&waves_record_id=" + wavesRecordId, {
            title: "取消原因",
            width: 500,
            height: 225,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                //tableStore.load();
                location.reload();
            }
        }).show();

    }

    //取消波次单
    function do_cancel_waves(_index, row) {
        BUI.Message.Confirm('确认要取消波次单吗？', function () {
            $.post('?app_act=oms/waves_record/opt_cancel_waves', {waves_record_id: wavesRecordId, sell_record_code: row.sell_record_code, do_type: 'one'}, function (data) {
                if (data.status == 1) {
                    location.reload();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        }, 'question');

    }

    //缺货
    function do_outofstock(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('oms/deliver_record/do_outofstock'); ?>', data: {deliver_record_id: row.deliver_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('取消成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

</script>

<script type="text/javascript">
    BUI.use('bui/menu', function (Menu) {
        var dropMenu1 = new Menu.PopMenu({
            trigger: '.btn_opt_print_express_selected',
            autoRender: true,
            width: 140,
            children: [
                {id: 'selected', content: "打印选中快递单"},
                {id: 'range', content: "打印区间快递单"}
            ]
        });

        dropMenu1.on('itemclick', function () {
            //alert(dropMenu1.getSelectedText() + '：' + dropMenu1.getSelectedValue());
            if (dropMenu1.getSelectedValue() == "selected") {
                get_checked(false, $(this), function (ids) {
                    var ids = ids.toString();
                    print_express(ids, 0, 0);
                });
            } else {
                //弹出区间选择框
                new ESUI.PopWindow("?app_act=oms/deliver_record/print_range&print_type=express&waves_record_id=" + wavesRecordId, {
                    title: "设置打印范围",
                    width: 500,
                    height: 250,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load();
                    }
                }).show();
            }
        });

        var dropMenu2 = new Menu.PopMenu({
            trigger: '.btn_opt_print_sellrecord_selected',
            autoRender: true,
            width: 140,
            children: [
                {id: 'selected', content: "打印选中发货单"},
                {id: 'range', content: "打印区间发货单"}
            ]
        });

        dropMenu2.on('itemclick', function () {
            if (dropMenu2.getSelectedValue() == "selected") {
                get_checked(false, $(this), function (ids) {
                    var id = ids.toString();
                    print_sellrecord(id, 0, 0);
                });
            } else {
                //弹出区间选择框
                new ESUI.PopWindow("?app_act=oms/deliver_record/print_range&print_type=deliver&waves_record_id=" + wavesRecordId, {
                    title: "设置打印范围",
                    width: 500,
                    height: 250,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        //刷新数据
                        tableStore.load();
                    }
                }).show();
            }
            ;
        });
    });

    // 下拉选单的定位（打印部分快递单）
    $(".btn_opt_print_express_selected").click(function () {
        $(".bui-pop-menu").css("z-index", "9999");
    });
    // 下拉选单的定位（打印部分发货单）
    $(".btn_opt_print_sellrecord_selected").click(function () {
        $(".bui-pop-menu").css("z-index", "9999");
    });

    var p_time = 0;
    function print_express(v, min, max, type = '') {
        var param = "&express_code=" + $("#express_code").val();
        param += "&is_print_express=" + $("#is_print_express").val();
        param += "&is_print_sellrecord=" + $("#is_print_sellrecord").val();
        param += "&is_deliver=" + $("#is_deliver").val();
        param += "&is_back=" + $("#is_back").val();
        //param += "&sell_record_code="+$("#sell_record_code").val();
        if (type) {
            param += "&type=" + type;
        }



        param += "&waves_record_ids=" + wavesRecordId;
        var check_param = param;
        if (min > 0 && max > 0) {
            check_param += "&min=" + min + "&max=" + max;
        }
        if (v != "") {
            check_param += "&deliver_record_ids=" + v;
        }

        var check_url = "?app_act=oms/deliver_record/check_is_print_express" + check_param + "&app_fmt=json";
        $.post(check_url, {}, function (ret) {
            if (ret.status == -2) {
                BUI.Message.Alert('单据异常，可打印单据为0', function () {
                    window.location.reload();
                }, 'error');
            } else if (ret.status == -1) {
                BUI.Message.Confirm('存在重复打印快递单，' + ret.data.print_data + ",是否继续打印？", function () {
                    param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                    check_action_print_express(param, type, ret.data.deliver_record_ids);

                }, 'question');
            } else {
                param += "&deliver_record_ids=" + ret.data.deliver_record_ids;
                check_action_print_express(param, type, ret.data.deliver_record_ids);
            }
        }, 'json');

    }

    function print_sellrecord(v, min, max, type = '') {
        var param = "&express_code=" + $("#express_code").val();
        param += "&is_print_express=" + $("#is_print_express").val();
        param += "&is_print_sellrecord=" + $("#is_print_sellrecord").val();
        param += "&is_deliver=" + $("#is_deliver").val();
        param += "&is_back=" + $("#is_back").val();
        param += "&waves_record_ids=" + wavesRecordId;
        var check_param = param;
        if (min > 0 && max > 0) {
            check_param += "&min=" + min + "&max=" + max;
        }
        if (v != "") {
            check_param += "&deliver_record_ids=" + v;
        } else if (v == "" && type == "") {
            check_param += "&deliver_record_ids=";
        }
        var check_url = "?app_act=oms/deliver_record/check_is_print_sellrecord" + check_param + "&app_fmt=json";
        $.post(check_url, {}, function (ret) {
            if (ret.status == -2) {
                BUI.Message.Alert('单据异常，可打印单据为0', function () {
                    window.location.reload();
                }, 'error');
            } else if (ret.status == -1) {
                BUI.Message.Confirm('存在重复打印发货单，' + ret.data.print_data + ",是否继续打印？", function () {
                    action_print_sellrecord(ret.data.deliver_record_ids, ret.data.sell_record_code);
                }, 'question');
            } else {
                action_print_sellrecord(ret.data.deliver_record_ids, ret.data.sell_record_code);
            }
        }, 'json');

    }

    function action_print_sellrecord(deliver_record_ids, sell_record_code) {
        $.post('?app_act=oms/sell_record/mark_sell_record_print', {record_ids: sell_record_code}, function (data) {

        }, "json");
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=deliver_record&record_ids=" + deliver_record_ids, {
                title: "发货单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            if (print_record_template == 1) {
                var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&record_ids=' + deliver_record_ids;
                var id = 'print_iframe';
                var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
                iframe.attr('src', u);
            } else {
                var u = '?app_act=sys/flash_print/do_print'
                u += '&template_id=5&model=oms/DeliverRecordModel&typ=default&record_ids=' + deliver_record_ids;
                window.open(u)
            }
        }
    }

    function check_action_print_express(param, type, deliver_record_ids) {
        var check_url = "?app_act=oms/deliver_record/check_express_type";
        $.post(check_url, {record_ids: deliver_record_ids, id_type: 0}, function (ret) {
            var result = JSON.parse(ret);
            action_print_express(param, type, result.data, deliver_record_ids);
        });
    }

    function action_print_express(param, type, print_type, deliver_record_ids) {
        if (print_type == 'cloud') {
            param = param + '&print_type=cainiao_print';
        }
        var id = "print_express" + p_time;
        if (new_clodop_print == 1 && print_type != 'cloud' && print_type != 'oldcloud') {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&record_ids=" + deliver_record_ids + "&waves_record_ids=" + wavesRecordId + "&is_print_express=1" + "&frame_id=" + id, {
                title: "快递单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        } else {
            var url = "?app_act=oms/deliver_record/print_express&iframe_id=" + id + param;
            var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', url);
        }
        p_time++;
        if (wave_match_express == 1 && type == 'print_all_express' && print_type != 'cloud') {
            //当S002_005 快递单打印完成后，弹出自动匹配物流单号页面 开启时 自动弹出 自动匹配物流
            //$("#panel_shipping .x-grid-checkbox").first().click()
            //setTimeout("$('.btn_edit_express_no').trigger('click')", 3000);
            setTimeout("edit_express_no_all()", 3000);
        }
    }




    var pd_time = 0;
    function print_deliver(v, min, max) {
        var id = "print_deliver" + pd_time;
        var url = "?app_act=oms/deliver_record/print_deliver&iframe_id=" + id + "&waves_record_ids=" + wavesRecordId;
        if (v != "") {
            url += "&deliver_record_ids=" + v;
        }
        if (min > 0 && max > 0) {
            url += "&min=" + min + "&max=" + max;
        }
        var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
        iframe.attr('src', url);
        pd_time++;
    }

    // 供区间打印使用
    parent.print_express = print_express;
    parent.print_sellrecord = print_sellrecord;

    function waves_batch_send() {
        get_checked(false, $(this), function (ids) {
            $("#btn_waves_batch_send").attr('disabled', 'true');
            var url = "?app_fmt=json&app_act=oms/waves_record/get_waves_batch_status&record_ids=" + ids.toString();
            $.get(url, function (json_data) {
                try {
                    var result = eval('(' + json_data + ')');
                } catch (e) {
                }
                if (result == undefined || result.status == undefined) {
                    alert('批量发货出错： ' + json_data);
                    $("#btn_waves_send").removeAttr('disabled');
                    return;
                }
                if (result.status <= 0) {
                    alert('批量发货出错： ' + result.message);
                    $("#btn_waves_send").removeAttr('disabled');
                    return;
                }
                var act = 'app_act=oms/waves_record/waves_batch_send_sell_record';
                process_batch_task(act, '批量发货', '批量发货', 'sell_record_code', 0, result.data, '此操作会把波次单的已匹配物流单号，勾选的未发货有效订单发货，请确认你要进行此操作吗？', 'btn_waves_batch_send');

            });
        });
    }


    function waves_send(waves_record_code) {
        $("#btn_waves_send").attr('disabled', 'true');
        var url = "?app_fmt=json&app_act=oms/waves_record/get_waves_send_sell_record&waves_record_code=" + waves_record_code;
        $.get(url, function (json_data) {
            try {
                var result = eval('(' + json_data + ')');
            } catch (e) {
            }
            if (result == undefined || result.status == undefined) {
                alert('整单发货出错： ' + json_data);
                $("#btn_waves_send").removeAttr('disabled');
                return;
            }
            if (result.status <= 0) {
                alert('整单发货出错： ' + result.message);
                $("#btn_waves_send").removeAttr('disabled');
                return;
            }
            var act = 'app_act=oms/waves_record/waves_send_sell_record';
            process_batch_task(act, '整单发货', '整单发货', 'sell_record_code', 0, result.data, '此操作会把波次单的已匹配物流单号，未发货的有效订单全部发货，请确认你要进行此操作吗？', 'btn_waves_send');

        });
    }

    function waves_back(waves_record_code) {
        $("#btn_waves_send").attr('disabled', 'true');
        var url = "?app_fmt=json&app_act=oms/waves_record/get_waves_back_sell_record&waves_record_code=" + waves_record_code;
        $.get(url, function (json_data) {
            try {
                var result = eval('(' + json_data + ')');

            } catch (e) {
            }
            if (result == undefined || result.status == undefined) {
                alert('整单回写出错： ' + json_data);
                $("#btn_waves_back").removeAttr('disabled');
                return;
            }
            if (result.status <= 0) {
                alert('整单回写出错： ' + result.message);
                $("#btn_waves_back").removeAttr('disabled');
                return;
            }
            var act = 'app_act=oms/waves_record/waves_back_sell_record';
            process_batch_task(act, '整单回写', '整单回写', 'sell_record_code', 0, result.data, '此操作会把发货的有效订单全部回写，请确认你要进行此操作吗？', 'btn_waves_send');

        });
    }

    function view_send_print_link(deliver_record_id) {
        if (print_record_template == 1) {
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=deliver_record&view=1&record_ids=' + deliver_record_id;
            var id = 'print_iframe';
            var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', u);

        } else {
            var u = '?app_act=sys/flash_print/do_print';
            u += '&template_id=5&model=oms/DeliverRecordModel&typ=default&view=1&record_ids=' + deliver_record_id;
            window.open(u);
        }
    }

    function waves_record_export() {
        var goods_sub_barcode = '<?php echo $response['goods_sub_barcode'] ?>';
        var url = '?app_act=sys/export_csv/export_show';
        params = tableStore.get('params');

        params.ctl_type = 'export';
        params.export_type = 'file';
        //判断是否开启子条码导出参数
        if (goods_sub_barcode == 1) {
            params.ctl_export_conf = 'waves_record_view_list';
        } else {
            params.ctl_export_conf = 'waves_record_view_list_notopen';
        }
        params.ctl_export_name = '波次订单详情';
        params.express_code = $("#express_code").val();
        params.is_print_express = $("#is_print_express").val();
        params.is_print_sellrecord = $("#is_print_sellrecord").val();
        params.is_back = $("#hide").val();
        params.sell_record_code = $("#sell_record_code").val();
        params.waves_record_ids = wavesRecordId;
<?php echo create_export_token_js('oms/DeliverRecordModel::get_by_page'); ?>
        for (var key in params) {
            url += "&" + key + "=" + params[key];
        }

        window.open(url);
    }

    function import_express_no() {
        url = "?app_act=oms/deliver_record/import_express_no&waves_record_id=" + wavesRecordId;
        new ESUI.PopWindow(url, {
            title: "导入快递单号",
            width: 470,
            height: 390,
            onBeforeClosed: function () {
                location.reload();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }

    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=pur/planned_record/importGoods&id=" + id;
            new ESUI.PopWindow(url, {
                title: "导入商品",
                width: 880,
                height: 400,
                onBeforeClosed: function () {
                    location.reload();
                },
                onClosed: function () {
                    //刷新数据

                }
            }).show();
        });
    });

    //整单称重
    function waves_weight(record_code) {
        //检验是否单款sku
        $.post('?app_act=oms/waves_record/wave_weight_check', {wave_record_id: wavesRecordId}, function (data) {
            if (data.status == 1) {
                var url = "?app_act=oms/waves_record/weight_view&wave_record_id=" + wavesRecordId + "&waves_record_code=" + record_code;
                _do_execute(url, 'table', '整单称重', 500, 360);
            } else {
                BUI.Message.Alert(data.message, 'error');
            }

        }, 'json');

    }

    /**
     * 分配拣货员
     */
    function distribute_pick_member() {
        url = "?app_act=oms/waves_record/distribute_pick_member&waves_record_id=" + wavesRecordId;
        new ESUI.PopWindow(url, {
            title: "分配拣货员",
            width: 500,
            height: 250,
            onBeforeClosed: function () {
                location.reload();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }

    function show_change_msg(ids, id_type, get_type) {
        parent.$(".bui-dialog, .bui-message").attr("style", "display:none");
        parent.$(".bui-ext-mask").removeClass("bui-ext-mask");
        parent.BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: '温馨提示',
                width: 450,
                height: 150,
                mask: true,
                icon: 'warning',
                buttons: [
                    {
                        text: '继续获取',
                        elCls: 'button button-primary ',
                        handler: function () {
                            get_waybill(ids, id_type, get_type);
                        }
                    },
                    {
                        text: '去了解菜鸟云打印',
                        elCls: 'button',
                        handler: function () {
                            window.open('http://operate.baotayun.com:8080/efast365-help/?p=3588');
                            this.close();
                        }
                    }
                ],
                bodyContent: "<p style='font-size: 16px;text-align:center;color:red'>您现在使用的已下线的面单获取模式，请尽快切换到全新的菜鸟云打印！</p>"
            });
            dialog.show();
        });
    }

    function check_address(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'receiver_address');
        } else {
            return value;
        }
    }

    function check_name(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'receiver_name');
        } else {
            return value;
        }
    }

    function check_buyer_name(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'buyer_name');
        } else {
            return value;
        }
    }

    function set_show_text(row, value, type) {
        return '<span class="like_link" onclick =\"show_info(this,\'' + row.sell_record_code + '\',\'' + type + '\');\">' + value + '</span>';
    }

    function show_info(obj, sell_record_code, key) {
        var url = "?app_act=oms/sell_record/get_record_key_data&app_fmt=json";
        $.post(url, {'sell_record_code': sell_record_code, key: key}, function (ret) {
            if (ret[key] == null) {
                BUI.Message.Tip('解密出现异常！', 'error');
                return;
            }
            $(obj).html(ret[key]);
            $(obj).attr('onclick', '');

            $(obj).removeClass('like_link');
        }, 'json');
    }

    //无界热敏解绑运单号
    function do_unbind_express(_index, row) {
        BUI.Message.Confirm('确认要解绑运单号吗？', function () {
            var dialog;
            parent.BUI.use('bui/overlay', function (Overlay) {
                dialog = new Overlay.Dialog({
                    title: '解绑无界热敏运单号',
                    width: 300,
                    height: 130,
                    mask: true,
                    buttons: [
                        {
                            text: '',
                            elCls: 'bui-grid-cascade-collapse',
                            handler: function () {
                                this.close();
                            }
                        }
                    ],
                    bodyContent: '正在请求解绑无界热敏运单号，请稍后...'
                });
                dialog.show();
            });

            $.post('?app_act=oms/waves_record/opt_unbind_express', {waves_record_id: wavesRecordId, sell_record_code: row.sell_record_code}, function (data) {
                dialog.hide();
                if (data.status == 1) {
                    tableStore.load();
                    BUI.Message.Tip('解绑成功', 'success');
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        }, 'question');
    }
</script>
<?php echo_print_plugin() ?>
<?php include_once (get_tpl_path('process_batch_task')); ?>