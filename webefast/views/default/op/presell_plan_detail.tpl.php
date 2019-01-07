<?php echo load_js("baison.js,record_table.js", true); ?>
<style>
    .panel-body{ padding:0;}
    #panel_html{ margin-top:-13px;;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 5px 8px;}
    p{ margin:0;}
    b{ vertical-align:middle;}
    .bui-pagingbar{margin:10px}
    .bui-grid-body{border: none;}
    .bui-grid-header{border-left: none;}
    table{overflow: hidden;}
    .bui-grid-width .bui-grid-body{overflow: hidden;}
    .custom-dialog .bui-stdmod-header,.custom-dialog .bui-stdmod-footer{display: none;}
    .icon-edit{cursor: pointer;margin-left: 5px;}

    .check_custom{visibility: hidden}
    .check_custom + label{
        background-color: white;
        border-radius: 5px;
        border:1px solid #d3d3d3;
        width:20px;
        height:20px;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        line-height: 20px;
    }
    .check_custom:checked + label{
        background-color: #eee;
    }
    .check_custom:checked + label:after{
        content:"\2714";
    }
</style>
<input type='checkbox' id='myCheck'><label for='myCheck'></label>
<?php
$links = array();
if ($response['data']['sync_num'] > 0) {
    $links[] = array('url' => 'op/presell/plan_sync_log_view&plan_code=' . $response['data']['plan_code'], 'title' => '库存同步日志', 'is_pop' => TRUE, 'pop_size' => '800,550');
}
$curr_time = time();
if ($response['data']['start_time'] <= $curr_time && $response['data']['end_time'] >= $curr_time && $response['data']['exit_status'] == 0 && load_model('sys/PrivilegeModel')->check_priv('op/presell/plan_exit_check')) {
    $links[]=array('type' => 'js', 'js' => 'exit_now()', 'title' => '立即终止');
}
render_control('PageHead', 'head1', array('title' => '预售计划详情',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<div class="panel record_table" id="panel_html">
</div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body" style="overflow:hidden;border: 1px solid #dddddd;">
        <div class="row">
            <input type="text" class=" control-text" placeholder="商品编码/条形码" value="" id="goods_search"/>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <?php if ($response['scene'] != 'view' && $response['data']['exit_status'] == 0): ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="EXCEL导入" id="btnImport" ><i class="icon-plus-sign icon-white"></i>EXCEL导入</button>&nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" style ="float:right;"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php endif; ?>
        </div>
        <?php
        $buttons = array();
        if ($response['scene'] != 'view' && $response['delete_priv'] == true && $response['data']['exit_status'] == 0) {
            $buttons = array(
                array('id' => 'cancel', 'title' => '删除', 'callback' => 'plan_goods_delete', 'priv' => '', 'show_cond' => "obj.is_allow_delete==1")
            );
        }
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '50',
                        'align' => '',
                        'buttons' => $buttons,
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条形码',
                        'field' => 'barcode',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品编码',
                        'field' => 'goods_code',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '210',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '系统规格',
                        'field' => 'spec',
                        'width' => '200',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['scene'] != 'view' && $response['data']['exit_status'] == 0 ? '预售数量<i class="icon-edit" id="edit_presell_num"></i>' : '预售数量',
                        'field' => 'presell_num',
                        'width' => '100',
                        'align' => 'center',
                        'editor' => $response['scene'] != 'view' && $response['data']['exit_status'] == 0 ? "{xtype:'number'}" : '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['scene'] != 'view' && $response['data']['exit_status'] == 0 ? '计划发货时间<i class="icon-edit" id="edit_plan_send_time"></i>' : '计划发货时间',
                        'field' => 'plan_send_time',
                        'width' => '160',
                        'align' => 'center',
                        'editor' => $response['scene'] != 'view' && $response['data']['exit_status'] == 0 ? "{xtype : 'date',datePicker : {showTime : true}}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '销售数量',
                        'field' => 'sell_num',
                        'width' => '100',
                        'align' => 'center'
                    ),
                )
            ),
            'dataset' => 'op/presell/PresellDetailModel::get_plan_detail_by_page',
            'params' => array('filter' => array('plan_code' => $response['data']['plan_code'])),
            'idField' => 'id',
            'CellEditing' => ($response['scene'] == 'view') ? false : true,
            'CascadeTable' => array(
                'list' => array(
                    array('title' => '选择商品', 'type' => 'text', 'width' => '70', 'field' => 'id', 'format_js' => array('type' => 'function', 'value' => 'get_select_html')),
                    array('title' => '商品状态', 'type' => 'text', 'width' => '80', 'field' => 'goods_status'),
                    array('title' => '平台SKUID', 'type' => 'text', 'width' => '120', 'field' => 'sku_id'),
                    array('title' => '平台规格编码', 'type' => 'text', 'width' => '150', 'field' => 'goods_barcode'),
                    array('title' => '平台商品名称', 'type' => 'text', 'width' => '500', 'field' => 'goods_name', 'format_js' => array('type' => 'function', 'value' => 'get_goods_link')),
                    array('title' => '平台商品属性', 'type' => 'text', 'width' => '230', 'field' => 'sku_properties_name'),
                ),
                'page_size' => 10,
                'url' => get_app_url("op/presell/get_pt_goods"),
                'params' => 'plan_code,barcode',
            ),
        ));
        ?>
    </div>
</div>
<div class="panel" style="border: 1px solid #dddddd">
    <div class="panel-header" style="border:none;">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body" style="overflow: hidden;">
        <?php
        render_control('DataTable', 'table_log', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作者',
                        'field' => 'user_name',
                        'width' => '150',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作名称',
                        'field' => 'action_name',
                        'width' => '200',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '操作时间',
                        'field' => 'action_time',
                        'width' => '200',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '备注',
                        'field' => 'action_desc',
                        'width' => '600',
                        'align' => ''
                    ),
                )
            ),
            'dataset' => 'op/presell/PresellLogModel::get_log_by_page',
            'idField' => 'id',
            'params' => array('filter' => array('plan_code' => $response['data']['plan_code'])),
        ));
        ?>
    </div>
</div>
<?php if ($response['scene'] != 'view'): ?>
    <ul id="ToolBar1" class="toolbar frontool frontool_center">
        <?php if ($response['sync_priv'] == TRUE && $response['data']['is_allow_sync'] == 1 && $response['data']['exit_status'] == 0): ?>
            <li class="li_btns" style="margin-right: 10px;"><button class="button button-primary sync_presell_inv">同步预售库存</button></li>
        <?php endif; ?>
        <?php if ($response['delete_priv'] == TRUE && $response['data']['is_allow_delete'] == 1 && $response['data']['exit_status'] == 0): ?>
            <li class="li_btns"><button class="button button-primary plan_delete">删除计划</button></li>
        <?php endif; ?>
    </ul>
<?php endif; ?>
<div id="plan_detail_1" style="visibility: hidden">
    <div class="row">
        <div class="control-group span8" style="margin-top: 20px;">
            <label class="control-label">预售数量：<b style="color:red"> *</b></label>
            <input type="text" id="presell_num" class="control-text" style="width:170px;"  data-rules="{required:true}" data-messages="{required:'请输入预售数量'}">
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 15px;">
        <button class="button button-primary" id="btn_edit_presell_num">一键更新</button>
    </div>
</div>
<div id="plan_detail_2" style="visibility: hidden" >
    <form id="plan_send_time_form" action="#" method="post">
        <div class="row">
            <div class="control-group span8" style="margin-top: 20px;">
                <label class="control-label" style="width:105px;">计划发货时间：<b style="color:red"> *</b></label>
                <input id="plan_send_time" type="text" class="input-normal calendar calendar-time bui-form-field-time bui-form-field">
            </div>
        </div>
    </form>
    <div class="clearfix" style="text-align: center;margin-top: 15px;">
        <button class="button button-primary" id="btn_edit_plan_send_time">一键更新</button>
    </div>
</div>
<?php echo load_js("jquery.md5.js", true); ?>
<script type="text/javascript">
    var scene = '<?php echo $response['scene']; ?>',
            sync_num = '<?php echo $response['data']['sync_num']; ?>',
            plan_code = '<?php echo $response['data']['plan_code'] ?>',
            plan_end_time = '<?php echo $response['data']['end_time'] ?>',
            presell_status = '<?php echo $response['data']['presell_status'] ?>',
            exit_status = '<?php echo $response['data']['exit_status'] ?>';//终止状态
    var dataRecord = [
        {'title': '预售编码', 'type': 'input', 'name': 'plan_code', 'value': '<?php echo $response['data']['plan_code']
?>'},
        {'title': '预售名称', 'type': 'input', 'name': 'plan_name', 'value': '<?php echo $response['data']['plan_name'] ?>'},
        {'title': '预售开始时间', 'type': 'datetime', 'name': 'plan_start_time', 'value': '<?php echo $response['data']['plan_start_time'] ?>', edit: true},
        {'title': '预售结束时间', 'type': 'datetime', 'name': 'plan_end_time', 'value': "<?php echo $response['data']['plan_end_time'] ?>", edit: true},
        {'title': '预售店铺', 'type': 'input', 'name': 'plan_shop_name', 'value': '<?php echo $response['data']['plan_shop'] ?>'},
        {'title': '创建时间', 'type': 'input', 'name': 'create_time', 'value': '<?php echo $response['data']['create_time'] ?>'},
        {'title': '手动终止状态', 'type': 'input', 'name': 'exit_status', 'value': "<?php echo $response['data']['exit_status_src'] ?>"},
    ];
    $(function () {
        //加载基本信息面板
        var r = new record_table();
        var is_edit = scene != 'view' && sync_num < 1 && presell_status != 1 && exit_status == 0 ? true : false;
        r.init({
            "id": "panel_html",
            "data": dataRecord,
            "is_edit": is_edit,
            "edit_url": "?app_act=op/presell/do_edit",
            'td_num': 2,
            'load_url': "?app_act=op/presell/get_presell_plan_info&plan_code=" + plan_code,
            'load_callback': function () {
                table_logStore.load();
            }
        });

        //面板展开和隐藏
        $('.toggle').click(function () {
            $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
            return false;
        });

        tools();

        //条码/编码查询
        $('#btnSearchGoods').click(function () {
            tableStore.load({'goods_search': $('#goods_search').val()});
        });

        if (scene != 'view' && exit_status == 0) {
            if (typeof tableCellEditing != "undefined") {
                tableCellEditing.on('accept', function (record, editor) {
                    var editValue = record.editor.__attrVals.editValue,
                            editId = record.editor.__attrVals.id,
                            _record = record.record;
                    var params = {};
                    if (editId == 'editor1') {
                        if (!isPositiveNum(_record.presell_num)) {
                            BUI.Message.Tip('数量必须为正整数', 'error');
                            tableStore.load();
                            return;
                        }
                        if (_record.presell_num == editValue) {
                            BUI.Message.Tip('数据未变更', 'warning');
                            tableStore.load();
                            return;
                        }
                        params.presell_num = _record.presell_num;
                    }
                    if (editId == 'editor2') {
                        if (_record.plan_send_time == null) {
                            BUI.Message.Tip('计划发货时间未设置', 'warning');
                            tableStore.load();
                            return;
                        }
                        var time = _record.plan_send_time.toString().substr(0, 10);
                        if (time < plan_end_time) {
                            BUI.Message.Tip('计划发货时间不能早于预售结束时间', 'error');
                            tableStore.load();
                            return;
                        }
                        var plan_send_time = getFormatTime(_record.plan_send_time);
                        if (plan_send_time == editValue) {
                            BUI.Message.Tip('数据未变更', 'warning');
                            tableStore.load();
                            return;
                        }

                        params.plan_send_time = plan_send_time;
                    }
                    params.id = _record.id;
                    params.barcode = _record.barcode;

                    $.post('?app_act=op/presell/do_edit_detail', params, function (ret) {
                        if (ret.status == 1) {
                            BUI.Message.Tip(ret.message, 'success');
                            tableStore.load();
                            table_logStore.load();
                        } else if (ret.status == 2) {
                            BUI.Message.Tip(ret.message, 'warning');
                            tableStore.load();
                        } else {
                            BUI.Message.Tip(ret.message, 'error');
                        }
                    }, 'json');
                });
            }

            //新增商品
            $("#btnSelectGoods").click(function () {
                show_select_goods();
            });
            //同步预售库存
            $(".sync_presell_inv").click(function () {
                plan_sync_check();
            });
            //删除预售计划
            $(".plan_delete").click(function () {
                plan_delete();
            });
            //导入明细
            $('#btnImport').click(function () {
                url = "?app_act=op/presell/plan_import_detail&plan_code=" + plan_code;
                new ESUI.PopWindow(url, {
                    title: "导入预售明细",
                    width: 500,
                    height: 300,
                    onBeforeClosed: function () {
                        tableStore.load();
                        table_logStore.load();
                    },
                    onClosed: function () {
                    }
                }).show();
            });
        }
    });

    //获取平台商品链接
    function get_goods_link(index, row) {
        if (row.source == 'taobao') {
            return '<a href="http://item.taobao.com/item.htm?id=' + row.goods_from_id + '" target="_blank">' + row.goods_name + '</a>';
        } else {
            return row.goods_name;
        }
    }

    //获取选择平台商品html
    function get_select_html(index, row) {
        var pt_data = {goods_barcode: row.goods_barcode, pid: row.pid, id: row.pt_id, sku: row.sku, sku_id: row.sku_id, shop_code: row.shop_code};
        pt_data = JSON.stringify(pt_data).toString();

        var checkbox_id = $.md5(row.shop_code + row.sku_id),
                check = row.is_presell == 1 ? " checked='checked' " : ' ',
                disabled = (scene == 'view' || row.is_edit_pt_goods == 0 || exit_status != 0) ? " disabled='disabled'" : '',
                onclick = (scene == 'view' || row.is_edit_pt_goods == 0 || exit_status != 0) ? '' : " onclick='up_goods_presell_status(this," + pt_data + ")' ";
        return '<input type="checkbox" class="check_custom" id="' + checkbox_id + '" ' + check + disabled + '><label for="' + checkbox_id + '" ' + onclick + disabled + '></label>';
    }

    if (scene != 'view') {
        //一键修改预售数量
        BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
            var dialog1 = new Overlay.Dialog({
                title: '',
                width: 340,
                height: 200,
                elCls: 'custom-dialog',
                contentId: 'plan_detail_1'
            });
            $('#edit_presell_num').on('click', function () {
                dialog1.show();
            });
            $('#btn_edit_presell_num').on('click', function () {
                var presell_num = $('#presell_num').val();
                if (presell_num == '') {
                    BUI.Message.Tip('请输入数量', 'warning');
                    return;
                }
                if (!isPositiveNum(presell_num)) {
                    BUI.Message.Tip('数量必须为正整数', 'warning');
                    return;
                }
                one_key_edit(presell_num, 'presell_num');
                dialog1.close();
            });
        });
        //一键修改计划发货时间
        BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
            var form = new Form.Form({
                srcNode: '#plan_send_time_form'
            }).render();
            var dialog2 = new Overlay.Dialog({
                title: '',
                width: 340,
                height: 200,
                elCls: 'custom-dialog',
                contentId: 'plan_detail_2'
            });
            $('#edit_plan_send_time').on('click', function () {
                dialog2.show();
            });
            $('#btn_edit_plan_send_time').on('click', function () {
                var plan_send_time = $('#plan_send_time').val();
                if (plan_send_time == '') {
                    BUI.Message.Tip('请选择时间', 'warning');
                    return;
                }

                var time = Date.parse(plan_send_time).toString().substr(0, 10);
                if (time < plan_end_time) {
                    BUI.Message.Tip('计划发货时间不能早于预售结束时间', 'error');
                    return;
                }
                one_key_edit(plan_send_time, 'plan_send_time');
                dialog2.close();
            });
        });
        //一键修改明细信息
        function one_key_edit(value, field) {
            $.post("?app_act=op/presell/one_key_edit", {value: value, field: field, plan_code: plan_code}, function (data) {
                if (data.status == 1) {
                    tableStore.load();
                    table_logStore.load();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                    return;
                }
            }, "json");
        }

        //选中/反选平台商品，更新平台商品预售状态
        function up_goods_presell_status(_this, pt_data) {
            var check_id = $(_this).attr('for'),
                    check_status = $('#' + check_id).attr('checked'),
                    sale_mode = check_status == undefined ? 'presale' : 'stock';
            var params = {plan_code: plan_code, sale_mode: sale_mode, shop_code: pt_data.shop_code, sku_id: pt_data.sku_id, sku: pt_data.sku, barcode: pt_data.goods_barcode, pid: pt_data.pid, id: pt_data.id};

            $.post("?app_act=op/presell/up_goods_presell_status", {params: params}, function (data) {
                if (data.status == 1) {
                    table_logStore.load();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    $('#' + check_id).attr('checked', check_status);
                    BUI.Message.Tip(data.message, 'error');
                }
            }, "json");
        }

        //选择预售明细商品
        function show_select_goods() {
            var param = {};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=prm/goods/goods_select_tpl_presell';
            var buttons = [
                {
                    text: '保存继续',
                    elCls: 'button button-primary',
                    handler: function () {
                        addgoods(this);
                    }
                },
                {
                    text: '保存退出',
                    elCls: 'button button-primary',
                    handler: function () {
                        addgoods(this);
                        this.close();
                    }
                }, {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
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
                    tableStore.load();
                });
                top.dialog.show();
            });
        }

        //添加预售明细商品
        function addgoods(obj) {
            var data = top.skuSelectorStore.getResult(),
                    select_data = {},
                    di = 0;
            $.each(data, function (key, value) {
                if (top.$("input[name='num_" + value.sku + "']").val() != '' && top.$("input[name='num_" + value.sku + "']").val() != undefined) {
                    var d = {};
                    d.sku = value.sku;
                    d.barcode = value.barcode;
                    d.presell_num = top.$("input[name='num_" + value.sku + "']").val();
                    d.plan_send_time = top.$("input[name='time_" + value.sku + "']").val();
                    select_data[di] = d;
                    di++;
                }
            });
            var _thisDialog = obj;
            if (di == 0) {
                _thisDialog.close();
                return false;
            }
            $.post('?app_act=op/presell/do_add_detail', {plan_code: plan_code, data: select_data}, function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Tip(ret.message, 'success');
                    tableStore.load();
                    table_logStore.load();
                } else if (ret.status == 2) {
                    table_logStore.load();
                    BUI.Message.Tip(ret.message, 'error');
                } else {
                    BUI.Message.Tip(ret.message, 'error');
                }
                if (typeof _thisDialog.callback == "function") {
                    _thisDialog.callback(this);
                }
            }, 'json');
        }

        //预售商品明细删除
        function plan_goods_delete(index, row) {
            BUI.Message.Confirm('确认要删除吗？', function () {
                $.post("?app_act=op/presell/do_delete_detail", {id: row.id, barcode: row.barcode}, function (data) {
                    if (data.status == 1) {
                        tableStore.load();
                        table_logStore.load();
                        BUI.Message.Tip(data.message, 'success');
                    } else {
                        BUI.Message.Tip(data.message, 'error');
                    }
                }, "json");
            }, 'question');
        }

        //同步预售库存检查
        function plan_sync_check() {
            $.post("?app_act=op/presell/plan_sync_check", {plan_code: plan_code}, function (ret) {
                var txt;
                if (ret.status == -1) {
                    BUI.Message.Tip(ret.message, 'error');
                    return;
                } else if (ret.status == 2) {
                    txt = plan_code + ' 计划将于 ' + ret.data + ' 开始预售，确认要同步吗？';
                } else {
                    txt = '确认要同步预售库存吗？';
                }
                plan_sync(txt);
            }, "json");
        }

        //同步预售库存
        function plan_sync(txt) {
            BUI.Message.Confirm(txt + '<br><span style="color:red;">注意：该操作会同时禁止预售关联的平台商品的库存同步，并在预售结束后更新为允许同步</span>', function () {
                $.post("?app_act=op/presell/plan_sync", {plan_code: plan_code}, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'success');
                        window.location.href = '?app_act=op/presell/plan_detail&plan_code=' + plan_code;
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            }, 'question');
        }

        //预售计划删除
        function plan_delete() {
            BUI.Message.Confirm('确认要删除吗？', function () {
                $.post("?app_act=op/presell/do_delete", {plan_code: plan_code}, function (data) {
                    if (data.status == 1) {
                        tableStore.load();
                        BUI.Message.Tip(data.message, 'success');
                        ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
                    } else {
                        BUI.Message.Tip(data.message, 'error');
                    }
                }, "json");
            }, 'question');
        }
    }

    //将时间对象格式化为Y-m-d H:i:s
    function getFormatTime(timestamp) {
        var d = new Date(timestamp);
        var date = (d.getFullYear()) + "-" +
                (d.getMonth() + 1) + "-" +
                (d.getDate()) + " " +
                (d.getHours()) + ":" +
                (d.getMinutes()) + ":" +
                (d.getSeconds());
        return date;
    }

    //判断是否为正整数  
    function isPositiveNum(s) {
        var re = /^[0-9]*[1-9][0-9]*$/;
        return re.test(s);
    }

    function isNumber(s) {
        var regu = "^[0-9]+$";
        var re = new RegExp(regu);
        if (s.search(re) != -1) {
            return true;
        } else {
            return false;
        }
    }
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


    //立即终止
    function exit_now() {
        BUI.Message.Confirm('预售计划一旦手动终止，不允许重新启用或是编辑，请确认是否要立即终止？', function () {
            $.post("?app_act=op/presell/exit_now", {plan_code: plan_code}, function (data) {
                if (data.status == 1) {
                    location.reload();
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
            }, "json");
        }, 'question');
    }
</script>