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
    .mssg{color:red;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '商品成本月结单明细',
    'links' => array(
        array('url' => 'acc/cost_month/do_list', 'title' => '商品成本月结单')
    ),
    'ref_table' => 'table'
));
?>
<input type="hidden" id="cost_month_view_remark" value="<?php echo $response['data']['remark'] ?>">
<ul id="tool" class="toolbar frontool frontool_center">
    <?php if ($response['data']['is_check'] != 1): ?>
        <li class="li_btns">
            <?php if ($response['data']['is_sure'] != 1) : ?>
                <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['cost_month_id']; ?>','enable')">确认</a>
            <?php else : ?>
                <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['cost_month_id']; ?>','disable')">取消确认</a>
            <?php endif; ?>
        </li>
        <li class="li_btns">
            <?php if ($response['data']['is_sure'] == 1) : ?>
                <a class="button button-primary" href="javascript:do_check(this, '<?php echo $response['data']['cost_month_id']; ?>')">审核</a>
            <?php endif; ?>
        </li>
    <?php endif; ?>
    <li class="li_btns">
        <a class="button button-primary" onclick="report_excel()" id="btn-csv">导出</a>
    </li>		
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
    var id = "<?php echo $response['data']['cost_month_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var ymonth = "<?php echo $response['data']['ymonth']; ?>";
    var type = 1;
    var is_edit = true;

    var data = [
        {
            "name": "record_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input",
        },
        {
            "name": "ymonth",
            "title": "月结月份",
            "value": "<?php echo $response['data']['ymonth'] ?>",
        },
        {
            "title": "审核状态",
            "value": "<?php echo $response['data']['is_check_src'] ?>",
        },
        {
            "name": "store_name",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_name'] ?>",
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "name": "",
            "title": "",
            "value": "",
        },
        {
            "name": "begin_amount",
            "title": "期初成本总金额",
            "value": "<?php echo $response['data']['begin_amount'] ?>",
            "type": "time",
            // "edit":true
        },
        {
            "name": "end_amount",
            "title": "期末成本总金额",
            "value": "<?php echo $response['data']['end_amount'] ?>",
        },
        {
            "name": "purchase_amount",
            "title": "月采购总金额",
            "value": "<?php echo $response['data']['purchase_amount'] ?>",
        },
        {
            "name": "begin_total",
            "title": "期初库存总数",
            "value": "<?php echo $response['data']['begin_total'] ?>",
        },
        {
            "name": "end_total",
            "title": "期末库存总数",
            "value": "<?php echo $response['data']['end_total'] ?>",
        },
        {
            "name": "purchase_total",
            "title": "月采购总数",
            "value": "<?php echo $response['data']['purchase_total'] ?>",
        },
    ];
    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=acc/cost_month/do_edit"
        });

        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'goods_code': $('#goods_code').val(), 'adjust_status': $('#adjust_status').val()});
        });

    });
</script>

<div class="panel record_table" id="panel_html">

</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>商品编码</b>
            <input type="text" placeholder="商品编码" class="input" value="" id="goods_code"/>&nbsp;
            <b>调价商品</b>
            <select id="adjust_status">
                <option value="">全部</option>
                <option value="1">已调价</option>
                <option value="0">未调价</option>
            </select>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <div style ="float:right;">
                <?php if (1 == $response['data']['is_check']) : ?>
                    <button type="button" class="button button-success" value="销售单成本维护" id="cost_oam">销售单成本维护</button>
                <?php endif; ?>
                <?php if (0 == $response['data']['is_check']) : ?>
                    <button type="button" class="button button-success" value="商品数据刷新" id="data_refresh">商品数据刷新</button>
                    <button type="button" class="button button-success" value="导入商品成本调整" id="import_adjust" ><i class="icon-plus-sign icon-white"></i> 导入商品成本调整</button>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php
    render_control('DataTable', 'table_list', array(
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
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期初数量',
                    'field' => 'begin_num',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期初成本单价',
                    'field' => 'begin_cost',
                    'width' => '90',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期初成本金额',
                    'field' => 'begin_amount',
                    'width' => '90',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '入库数',
                    'field' => 'purchase',
                    'width' => '80',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '入库金额',
                    'field' => 'pur_money',
                    'width' => '80',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期末数量',
                    'field' => 'end_num',
                    'width' => '80',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期末成本单价',
                    'field' => 'end_cost',
                    'width' => '90',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '调整成本',
                    'field' => 'adjust_cost',
                    'width' => '80',
                    'align' => '',
                    'editor' => "{xtype:'number'}"
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '最终成本单价',
                    'field' => 'end_adjust_cost',
                    'width' => '90',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '期末成本金额',
                    'field' => 'end_amount',
                    'width' => '100',
                    'align' => '',
                ),
            )
        ),
        'dataset' => 'acc/CostMonthDetailModel::get_by_page',
        'idField' => 'cost_yj_mx_id',
        'params' => array('filter' => array('record_code' => $response['data']['record_code'], 'ymonth' => $response['data']['ymonth'])),
        'CellEditing' => (1 == $response['data']['is_check']) ? false : true,
        'CascadeTable' => array(
            'list' => array(
                array('title' => '期初数量', 'type' => 'text', 'width' => '100', 'field' => 'begin_num'),
                array('title' => '采购进货', 'type' => 'text', 'width' => '100', 'field' => 'purchase'),
                array('title' => '采购退货', 'type' => 'text', 'width' => '100', 'field' => 'pur_return'),
                array('title' => '网络销售', 'type' => 'text', 'width' => '100', 'field' => 'oms_sell_record'),
                array('title' => '网络退货', 'type' => 'text', 'width' => '100', 'field' => 'oms_sell_return'),
                array('title' => '批发销货', 'type' => 'text', 'width' => '100', 'field' => 'wbm_store_out'),
                array('title' => '批发退货', 'type' => 'text', 'width' => '100', 'field' => 'wbm_return'),
                array('title' => '库存调整', 'type' => 'text', 'width' => '100', 'field' => 'adjust'),
                array('title' => '移仓入库', 'type' => 'text', 'width' => '100', 'field' => 'shift_in'),
                array('title' => '移仓出库', 'type' => 'text', 'width' => '100', 'field' => 'shift_out'),
                array('title' => '期末数量', 'type' => 'text', 'width' => '100', 'field' => 'end_num'),
            ),
            'page_size' => 10,
            'url' => get_app_url("acc/cost_month/get_detail_list_by_id&app_fmt=json&ymonth={$response['data']['ymonth']}"),
            'params' => 'cost_yj_mx_id'
        ),
    ));
    ?>
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
                            'field' => 'user_name',
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
                            'field' => 'action_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '状态',
                            'field' => 'status',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_desc',
                            'width' => '200',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'acc/CostMonthLogModel::get_by_page',
                'queryBy' => 'searchForm',
                'idField' => 'log_id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            ));
            ?>
        </div>
    </div>
</div>
<div class="mssg">
        说明：<br/>
        期末成本单价=（期初成本金额[期初成本单价*期初数量]+本月入库总金额）/ （期初数量+本月入库数）<br/>
        最终成本单价=期末成本单价+调整成本<br/>
        期末成本金额=最终成本单价*期末数量<br/>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    //单据确认
    function  do_sure(_index, cost_month_id, status) {
        url = '?app_act=acc/cost_month/update_sure';
        data = {id: cost_month_id, type: status};
        _do_operate(url, data, 'flush');
    }
    //单据审核
    function  do_check(_index, cost_month_id) {
        url = '?app_act=acc/cost_month/update_check';
        data = {id: cost_month_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
</script>

<script type="text/javascript">
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

    //调整成本
    if (typeof table_listCellEditing != "undefined") {
        //列表区域,调整成本修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_listCellEditing.on('accept', function (record, editor) {
            $.post('?app_act=acc/cost_month/do_edit_detail',
                    {cost_yj_mx_id: record.record.cost_yj_mx_id, record_code: record_code, goods_code: record.record.goods_code, adjust_cost: record.record.adjust_cost, ymonth: ymonth},
                    function (result) {
                        window.location.reload();
                    }, 'json');
        });
    }

    //导出月结单详情
    function report_excel() {
        var param = "";
        param = param + "&id=" + id + "&record_code=" + record_code + "&ymonth=" + ymonth + "&app_fmt=json";
        url = "?app_act=acc/cost_month/export_csv_list" + param;
        window.location.href = url;
    }

    //销售成本维护
    jQuery(function () {
        $('#cost_oam').on('click', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('acc/cost_month/cost_oam'); ?>',
                data: {record_code: record_code, ymonth: ymonth, id: id, store_code: store_code},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('销售单成本维护成功', type);
                        window.location.reload();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        });
    });

    //商品数据刷新
    jQuery(function () {
        $('#data_refresh').on('click', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '<?php echo get_app_url('acc/cost_month/data_refresh'); ?>',
                data: {record_code: record_code, ymonth: ymonth, id: id, store_code: store_code},
//                beforeSend: function (XMLHttpRequest)
//                {
//                    //Upload progress
//                    XMLHttpRequest.upload.addEventListener("progress", function (evt) {
//                        if (evt.lengthComputable) {
//                            var percentComplete = evt.loaded / evt.total;
//                            console.log(percentComplete);
//                            //Do something with upload progress
//                        }
//                    }, false);
//                    //Download progress
//                    XMLHttpRequest.addEventListener("progress", function (evt) {
//                        if (evt.lengthComputable) {
//                            var percentComplete = evt.loaded / evt.total;
//                            console.log(percentComplete);
//                            //Do something with download progress
//                        }
//                    }, false);
//                },
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('刷新数据成功', type);
                        window.location.reload();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });


//            $.post('?app_act=acc/cost_month/data_refresh', {record_code: record_code, ymonth: ymonth, id: id, store_code: store_code},
//                    function (result) {
//                        window.location.reload();
//                    }, 'json');
        });
    });

    //导入商品成本调整
    jQuery(function () {
        $('#import_adjust').on('click', function () {
            url = "?app_act=acc/cost_month/import_cost&record_code=" + record_code + "&ymonth=" + ymonth;
            new ESUI.PopWindow(url, {
                title: "导入成本",
                width: 500,
                height: 350,
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
    });
    //按钮提示
    BUI.use('bui/tooltip', function (Tooltip) {
        var t1 = new Tooltip.Tip({
            trigger: '#cost_oam',
            alignType: 'left',
            offset: 10,
            title: '<font color="red">月结单审核后，点击此按钮可将商品期末成本更新<br/>到销售订单明细中（根据月结月份和仓库），便于<br/>后续销售分析。目前使用到的报表：销售商品毛利<br/>分析。</font>',
            elCls: 'tips tips-no-icon',
            titleTpl: '<div class="tips-content">{title}</div>'
        });
        t1.render();
    });
</script>   

