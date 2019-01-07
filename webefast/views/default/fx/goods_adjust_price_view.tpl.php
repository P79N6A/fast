<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
    .icon-edit{cursor: pointer;margin-left: 5px;}
    .custom-dialog .bui-stdmod-header,.custom-dialog .bui-stdmod-footer{display: none;}
</style>

<?php echo load_js("baison.js,record_table.js", true); ?>

<?php
render_control('PageHead', 'head1', array('title' => '商品调价单',
    'links' => array(
        array('url' => 'fx/goods_adjust_price/do_list', 'target' => '_self', 'title' => '调价单列表'),
    ),
    'ref_table' => 'table'
));
?>

<?php if ($response['data']['record_status'] == 0) { ?>
    <ul id="tool" class="toolbar frontool frontool_center">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('fx/goods_adjust_price/do_enable')) { ?>
        <li class="li_btns">
            <a class="button button-primary" href="javascript:update_active('<?php echo $response['data']['adjust_price_record_id']; ?>', 'enable')">启用</a>
        </li>   
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('fx/goods_adjust_price/do_delete')) { ?>
        <li class="li_btns">
            <a class="button button-primary" href="javascript:do_delete('<?php echo $response['data']['adjust_price_record_id']; ?>')">删除</a>
        </li>
        <?php } ?>
        <li class="li_btns"></li>
        <div class="front_close">&lt;</div>
    </ul>
<?php } ?>
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
    var id = '<?php echo $response['data']['adjust_price_record_id']; ?>';
    var is_edit = false;
<?php if (1 == $response['data']['record_status']) { ?>
        is_edit = false;
<?php } ?>

    var data = [
        {
            "name": "record_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input",
        },
        {
            "name": "object_name",
            "title": "调价对象",
            "value": "<?php echo $response['data']['object_name'] ?>",
            "type": "input"
        },
        {
            "name": "start_time",
            "title": "调价开始时间",
            "value": "<?php echo date('Y-m-d H:i:s', $response['data']['start_time']); ?>",
            "type": "time"
        },
        {
            "name": "end_time",
            "title": "调价结束时间",
            "value": "<?php echo date('Y-m-d H:i:s', $response['data']['end_time']); ?>",
            "type": "time"
        },
        {
            "name": "settlement_price",
            "title": "结算价格",
            "value": "<?php echo $response['data']['settlement_price_name']; ?>"
        },
        {
            "name": "settlement_rebate",
            "title": "结算折扣",
            "value": "<?php echo $response['data']['settlement_rebate']; ?>"
        },
        {
            "name": "add_time",
            "title": "创建时间",
            "value": "<?php echo date('Y-m-d H:i:s', $response['data']['add_time']); ?>",
            "type": "time"
        },
        {
            "name": "user_name",
            "title": "创建人",
            "value": "<?php echo $response['data']['user_name'] ?>"
        }
    ];

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
//            "edit_url": "?app_act=fx/order_record/do_edit"
        });
        get_goods_panel({
            "id": "btnSelectGoods",
            "callback": addgoods,
            'param': {'store_code': '', 'list_type': 'fx_goods_adjust_price', 'is_select': 1, 'custom_code': 'all_fx_goods'}
        });
        $('#btnSearchGoods').on('click', function () {
            tableStore.load({'code_name': $('#goods_code').val()}, function (data) {
            });
        });
    })

    function addgoods(obj) {
        var select_data = top.SelectoGrid.getSelection();
        var _thisDialog = obj;
        var arr = Object.keys(select_data);
        if (arr.length == 0) {
            _thisDialog.close();
            return;
        }

        $.post('?app_act=fx/goods_adjust_price/do_add_detail&id=' + id, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                }, 'error');
            }
            if (typeof _thisDialog.callback == "function") {
                _thisDialog.callback(this);
            }
        }, 'json');

    }

</script>
<div class="panel record_table" id="panel_html">
</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="span18">
                <b>请输入</b>
                <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>
                <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
                <!--<button type="button" class="button button-info"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>-->
            </div>
            <div style='float:right'>
                <?php if (0 == $response['data']['record_status']) { ?>
                    <button type="button" class="button button-success" value="按商品编码导入" id="btncodeimport" onclick="import_adjust_data(2)"><i class="icon-plus-sign icon-white"></i> 按商品编码导入</button>
                    <button type="button" class="button button-success" value="按条形码导入" id="btnimport"><i class="icon-plus-sign icon-white"></i> 按条形码导入</button>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                <?php } ?>
            </div>
        </div>
        <?php
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '80',
                        'align' => '',
                        'buttons' => array(
                            array(
                                'id' => 'del',
                                'title' => '删除',
                                'callback' => 'do_delete_detail',
                                'show_cond' => 'obj.record_status == 0'
                            ),
                        ),
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
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '200',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '系统规格',
                        'field' => 'spec_str',
                        'width' => '200',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '结算价',
                        'field' => 'settlement_price',
                        'width' => '120',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['data']['record_status'] == 1 ? '结算折扣' : '结算折扣<i class="icon-edit" id="settlement_rebate_button"></i>',
                        'field' => 'settlement_rebate',
                        'width' => '100',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['data']['record_status'] == 1 ? '结算金额' : '结算金额<i class="icon-edit" id="settlement_money_button"></i>',
                        'field' => 'settlement_money',
                        'width' => '120',
                        'align' => '',
                        'editor' => "{xtype:'text'}"
                    ),
                )
            ),
            'dataset' => 'fx/GoodsAdjustPriceDetailModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'order_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            //'RowNumber'=>true,
            //'CheckSelection'=>true,
            'CellEditing' => (1 == $response['data']['record_status']) ? false : true,
        ));
        ?>
    </div>
</div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php
            render_control('DataTable', 'log', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作者',
                            'field' => 'user_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作名称',
                            'field' => 'action_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'add_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据状态',
                            'field' => 'record_status_str',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_remark',
                            'width' => '400',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'fx/GoodsAdjustPriceDetailModel::get_by_page_log',
                //'queryBy' => 'searchForm',
                'idField' => 'fx_goods_adjust_log_id',
                'params' => array('filter' => array('pid' => $response['data']['adjust_price_record_id'], 'page_size' => 10)),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<div id="plan_detail_1" style="visibility: hidden">
    <div class="rows">
        <div class="control-group span8" style="margin-top: 35px;">
            <label class="control-label">结算折扣：<b style="color:red"> *</b></label>
            <input type="text" id="edit_settlement_rebate" class="control-text" style="width:130px;"  data-rules="{required:true}">
            <button class="button button-primary" id="btn_edit_settlement_rebate">一键更新</button>
        </div>
    </div>
</div>
<div id="plan_detail_2" style="visibility: hidden" >
    <div class="rows">
        <div class="control-group span8" style="margin-top: 35px;">
            <label class="control-label">结算金额：<b style="color:red"> *</b></label>
            <input id="edit_settlement_money" type="text"  style="width:130px;" class="input-normal">
            <button class="button button-primary" id="btn_edit_settlement_money">一键更新</button>
        </div>
    </div>
</div>
<script type="text/javascript">
    var sql_settlement_rebate = '<?php echo $response['data']['settlement_rebate']; ?>';
    var sql_settlement_money = '<?php echo $response['data']['settlement_money']; ?>';
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/do_delete_detail'); ?>',
            data: {detail_id: row.adjust_price_detail_id, id: id, barcode: row.barcode},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
                    logStore.load();
                    tableStore.load();
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    $(function () {
        //修改折扣
        BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
            var dialog1 = new Overlay.Dialog({
                title: '',
                width: 340,
                height: 200,
                elCls: 'custom-dialog',
                contentId: 'plan_detail_1'
            });
            $('#settlement_rebate_button').on('click', function () {
                dialog1.show();
                $('#edit_settlement_rebate').val(sql_settlement_rebate);
            });
            $('#btn_edit_settlement_rebate').on('click', function () {
                var edit_settlement_rebate = $('#edit_settlement_rebate').val();
                if (edit_settlement_rebate == '') {
                    BUI.Message.Tip('请输入结算折扣', 'error');
                    tableStore.load();
                    return;
                }
//                if (isNaN(edit_settlement_rebate) || edit_settlement_rebate < 0 || edit_settlement_rebate > 1) {
//                    BUI.Message.Tip('折扣必须大于0小于1', 'error');
//                    return;
//                }
                var params = [];
                params = {'rebate': edit_settlement_rebate, 'pid': id};
                edit_rebate_or_money(params, 'rebate');
                dialog1.close();
            });
        });

        //修改金额
        BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
            var dialog2 = new Overlay.Dialog({
                title: '',
                width: 340,
                height: 200,
                elCls: 'custom-dialog',
                contentId: 'plan_detail_2'
            });
            $('#settlement_money_button').on('click', function () {
                dialog2.show();
                $('#edit_settlement_money').val(sql_settlement_money);
            });
            $('#btn_edit_settlement_money').on('click', function () {
                var edit_settlement_money = $('#edit_settlement_money').val();
                if (edit_settlement_money == '') {
                    BUI.Message.Tip('请输入结算金额', 'error');
                    tableStore.load();
                    return;
                }
                if (isNaN(edit_settlement_money)) {
                    BUI.Message.Tip('请输入正确的数字', 'error');
                    tableStore.load();
                    return;
                }
                var params = [];
                params = {'money': edit_settlement_money, 'pid': id};
                edit_rebate_or_money(params, 'money');
                dialog2.close();
            });
        });
        $('#btnimport').on('click', function () {
            import_adjust_data(1);
        });

    })
    function edit_rebate_or_money(params, settlement_type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/edit_rebate_or_money'); ?>',
            data: {params: params, settlement_type: settlement_type},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
//                        logStore.load();
//                        tableStore.load();
                    location.reload();
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    if (typeof tableCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        tableCellEditing.on('accept', function (record, editor) {
            var rebate = record.record.settlement_rebate;
            if (rebate == '') {
                BUI.Message.Tip('请输入结算折扣', 'error');
                tableStore.load();
                return;
            }
//            if (isNaN(rebate) || rebate < 0 || rebate > 1) {
//                BUI.Message.Tip('折扣必须大于0小于1', 'error');
//                return;
//            }
            var money = record.record.settlement_money;
            if (money == '') {
                BUI.Message.Tip('请输入结算金额', 'error');
                tableStore.load();
                return;
            }
            if (isNaN(money)) {
                BUI.Message.Tip('请输入正确的数字', 'error');
                tableStore.load();
                return;
            }
            var params = {pid: record.record.pid, adjust_price_detail_id: record.record.adjust_price_detail_id, money: money, rebate: rebate};
            $.post('?app_act=fx/goods_adjust_price/do_edit_detail', params, function (result) {
                if(result.status < 0) {
                    BUI.Message.Tip(result.message, 'error');
                    tableStore.load();
                } else {
                    BUI.Message.Tip(result.message, 'success');
                    tableStore.load();
                    logStore.load();
                }
            }, 'json');
        });
    }
    function update_active(id, active) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/update_active'); ?>',
            data: {id: id, type: active},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
                    location.reload();
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    function do_delete(id) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/goods_adjust_price/do_delete'); ?>',
            data: {id: id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Tip(ret.message);
                    parent.tableload();
                    ui_closeTabPage('<?php echo $request['ES_frmId'] ?>');
                } else {
                    BUI.Message.Tip(ret.message);
                }
            }
        });
    }
    function import_adjust_data(op_type){
        url = "?app_act=fx/goods_adjust_price/importGoods&id=" + id+"&op_type=" + op_type;
        new ESUI.PopWindow(url, {
            title: "导入商品",
            width: 600,
            height: 350,
            onBeforeClosed: function () {
                location.reload();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }
</script>