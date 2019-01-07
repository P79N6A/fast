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
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>

<?php
render_control('PageHead', 'head1', array('title' => '通知单',
    'links' => array(
        // array('type' => 'js', 'js' => 'report_excel()', 'title' => '导出'), 
        array('url' => 'pur/order_record/do_list', 'target' => '_self', 'title' => '通知单列表'),
    ),
    'ref_table' => 'table'
));
?>
<ul id="tool" class="toolbar frontool frontool_center">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/order_record/do_check')) { ?>
        <li class="li_btns">
            <?php if (0 == $response['data']['is_check']) { ?>
                <a class="button button-primary" href="javascript:do_check(this, '<?php echo $response['data']['order_record_id']; ?>')"> 确认</a>
            <?php } ?>
        </li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/order_record/do_check')) { ?>
        <li class="li_btns">
            <?php if (1 == $response['data']['is_check'] && $response['data']['is_finish'] != 1 && $response['data']['is_execute'] == 0) { ?>
                <a class="button button-primary" href="javascript:do_re_check(this, '<?php echo $response['data']['order_record_id']; ?>')">取消确认</a>
            <?php } ?>
        </li class="li_btns">
    <?php } ?>
    <?php if (1 == $response['data']['is_check'] && $response['data']['is_finish'] != 1 && $response['data']['is_wms'] != 1) { ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/order_record/do_execute')) { ?>
            <li class="li_btns"><a class="button button-primary" href="javascript:do_execute(this, '<?php echo $response['data']['order_record_id']; ?>')">生成入库单</a></li>
        <?php } ?>
    <?php } ?>
    <?php if (1 == $response['data']['is_check'] && $response['data']['is_finish'] != 1) { ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/order_record/do_finish')) { ?>
            <li class="li_btns"><a class="button button-primary" href="javascript:do_finish(this, '<?php echo $response['data']['order_record_id']; ?>','<?php echo $response['data']['record_code']; ?>')">完成</a></li>
        <?php } ?>
    <?php } ?>
    <li class="li_btns"></li>
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
<script>

    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['order_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var lof_status = "<?php echo $response['lof_status']; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_check']) { ?>
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
            "name": "init_code",
            "title": "原单号",
            "value": "<?php echo $response['data']['init_code'] ?>",
            "type": "input",
            "edit": true
        },
        {
            "name": "supplier_code",
            "title": "供应商",
            "value": "<?php echo $response['data']['supplier_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['supplier'] ?>,
        },
        {
            "name": "store_code",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>,
        },
        {
            "name": "rebate",
            "title": "折扣",
            "value": "<?php echo $response['data']['rebate'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "name": "order_time",
            "title": "下单日期",
            "value": "<?php echo $response['data']['order_time']; ?>",
            "type": "time",
        },
        {
            "name": "in_time",
            "title": "入库期限",
            "value": "<?php echo date('Y-m-d', strtotime($response['data']['in_time'])); ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "pur_type_code",
            "title": "采购类型",
            "value": "<?php echo $response['data']['pur_type_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['pur_type'] ?>,
        },
        {
            "name": "relation_code",
            "title": "采购订单号",
            "value": "<?php echo $response['data']['relation_code'] ?>",
        },
        {
            "title": "数量",
            "value": "<?php echo $response['data']['num'] ?>",
        },
        {
            "title": "完成数量",
            "value": "<?php echo $response['data']['finish_num'] ?>",
        },
        {
            "title": "差异总数量",
            "value": "<?php echo $response['data']['diff_num'] ?>",
        },
        {
            "title": "金额",
            "value": "<?php echo number_format($response['data']['money'], 3); ?>",
        },
        {
            "name": "remark",
            "title": "备注",
            "type": "input",
            "value": "<?php echo $response['data']['remark'] ?>",
            "edit": true,
        },
        {
            "title": "确认",
            "value": "<?php echo $response['data']['is_check_src'] ?>",
        },
        {
            "title": "生成入库单",
            "value": "<?php echo $response['data']['is_execute_src'] ?>",
        },
        {
            "title": "完成",
            "value": "<?php echo $response['data']['is_finish_src'] ?>",
        },
    ];

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=pur/order_record/do_edit"
        });

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'pur_order', record_id: id}
                });
            } else {
                get_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": addgoods,
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'diy': '0'}
                });
            }
        }

        $('#btnSearchGoods').on('click', function () {
            tableStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()}, function (data) {

            });
        });
    })

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            var num_name = 'num_' + value.sku;
            if (top.$("input[name='" + num_name + "']").val() != '' && top.$("input[name='" + num_name + "']").val() != undefined) {
                if (top.$("input[name='" + num_name + "']").val() > 0) {
                    value.num = top.$("input[name='" + num_name + "']").val();
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
        $.post('?app_act=pur/order_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                    // _thisDialog.remove(true);
                }, 'error');
            } else {
                //_thisDialog.close();
                //_thisDialog.remove(true);
                //tableStore.load();
                //form.submit();
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
                <b>差异款</b>
                <select name="difference_models" id="difference_models">
                    <option value="">全部</option>
                    <option value="1">是</option>
                    <option value="0">否</option>
                </select> 
                <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
                <button type="button" class="button button-info"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>
                <!--  <button type="button" class="button button-info" value="重置" id="btnSearchReset"><i class="icon-repeat icon-white"></i> 重置</button>-->

            </div>
            <div style='float:right'>
                <?php if (0 == $response['data']['is_check']) { ?>
                    <?php if (0 == $response['lof_status']) { ?>
                        <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>&nbsp;
                    <?php } ?>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i class="icon-plus-sign icon-white"></i> 新增商品</button>

                    <button type="button" class="button button-success" value="商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                <?php } ?>
            </div>

            <!--
            <div class="span12">
                <b>扫描条码加入单据 </b>
                <input type="text" class="input" value=""/>
            </div>
            -->
        </div>
        <?php
        render_control('DataTable', 'table', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品编码',
                        'field' => 'goods_code',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec1_rename'],
                        'field' => 'spec1_name',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec2_rename'],
                        'field' => 'spec2_name',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条形码',
                        'field' => 'barcode',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '进货价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                        'editor' => $response['price_status'] == 1 ? "{xtype:'text'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '进货单价',
                        'field' => 'price1',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '数量',
                        'field' => 'num',
                        'width' => '80',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '金额',
                        'field' => 'money',
                        'width' => '120',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成数',
                        'field' => 'finish_num',
                        'width' => '120',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数',
                        'field' => 'difference_num',
                        'width' => '120',
                        'align' => '',
                    ),
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
                                'show_cond' => 'obj.is_check == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'pur/OrderRecordDetailModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'order_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            //'RowNumber'=>true,
            //'CheckSelection'=>true,
            'CellEditing' => (1 == $response['data']['is_check']) ? false : true,
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
                            'title' => '确认状态',
                            'field' => 'sure_status',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '完成状态',
                            'field' => 'finish_status',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '400',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['order_record_id'], 'module' => 'order_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">

    function do_execute(_index, order_record_id) {
        //判断有没有未入库的入库单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/order_record/out_relation'); ?>',
            data: {id: order_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act=pur/order_record/execute&order_record_id=" + order_record_id.toString();
                    _do_execute(url, 'flush');
                } else {
                    if (ret.status == '-1') {
                        if (confirm("存在未入库的采购入库单，是否继续？")) {
                            url = "?app_act=pur/order_record/execute&order_record_id=" + order_record_id.toString();
                            _do_execute(url, 'flush');
                        }
                    }
                }
            }
        })
    }
    function  do_re_check(_index, order_record_id) {
        url = '?app_act=pur/order_record/do_check';
        data = {id: order_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    function  do_check(_index, order_record_id) {
        url = '?app_act=pur/order_record/do_check';
        data = {id: order_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }


    function do_finish(_index, order_record_id) {
        BUI.Message.Confirm('确认要完成吗？', function () {
            url = '?app_act=pur/order_record/do_finish';
            data = {id: order_record_id, record_code: record_code};
            _do_operate(url, data, 'flush');
        }, 'warning');
    }

    function report_excel()
    {
        var param = "";
        param = param + "&id=" + id + "&record_code=" + record_code + "&app_fmt=json";
        url = "?app_act=pur/order_record/export_csv_list" + param;
        window.location.href = url;
    }
</script>

<script type="text/javascript">

    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });



    //删除单据明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=pur/order_record/do_delete_detail',
            data: {order_record_detail_id: row.order_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                tableStore.load({'code_name': ''});

                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    location.reload();
                }
            }
        });
    }
    if (typeof tableCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        tableCellEditing.on('accept', function (record, editor) {

            if (!Number(record.record.price) && Number(record.record.price) != 0 && record.record.price != '****') {
                BUI.Message.Alert('不能为非数值型', 'error');
                tableStore.load();
                return;
            }

            if (parseInt(record.record.price) < 0 || parseInt(record.record.num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }

            $.post('?app_act=pur/order_record/do_edit_detail',
                    {pid: record.record.pid, num: record.record.num, sku: record.record.sku, rebate: record.record.rebate, sell_price: record.record.price},
                    function (result) {
                        var _res = result.res;
                        tableStore.load();
                        $("#base_table tr").eq(3).find("td").eq(0).html(_res.num);
                        $("#base_table tr").eq(4).find("td").eq(0).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }

    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=pur/order_record/importGoods&id=" + id;
            new ESUI.PopWindow(url, {
                title: "导入商品",
                width: 880,
                height: 400,
                onBeforeClosed: function () {
                    location.reload();
                    //table_listStore.load();
                    //table_lof_listStore.load();
                },
                onClosed: function () {
                    //刷新数据

                }
            }).show();
        });
    })

    //扫描
    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan/view_scan&dj_type=pur_notice&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    });
</script>

