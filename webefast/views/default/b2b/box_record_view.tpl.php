<style>
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin: 5px 8px;}
    .panel .panel-body{ padding:0;}
    .table_store{border: 1px solid #ddd;}
    #table_list,#table_lof_list,#log{width: 98.5% !important;}
    #bar3{margin: 0px;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '装箱单详情',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<script>
    var is_lof = "<?PHP echo $response['lof_status'] ?>";
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['store_out_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var type = 1;
    var is_edit = false;
    var new_clodop_print = "<?php echo $response['new_clodop_print'];?>";
<?php if (1 == $response['data']['is_sure']) { ?>
        is_edit = false;
<?php } ?>
    var data = [
        {
            "name": "box_order",
            "title": "箱序号",
            "value": "<?php echo $response['data']['box_order'] ?>",
            "type": "input"
        },
        {
            "name": "record_code",
            "title": "箱号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input"
        },
        {
            "name": "create_time",
            "title": "装箱时间",
            "value": "<?php echo $response['data']['create_time'] ?>"
        },
        {
            "name": "create_time",
            "title": "装箱任务号",
            "value": "<?php echo $response['data']['task_code'] ?>"
        },
        {
            "name": "relation_code",
            "title": "关联单号",
            "value": "<?php echo $response['data']['relation_code'] ?>"
        },
        {
            "name": "relation_code",
            "title": "关联单类型",
            "value": "<?php echo $response['data']['record_type_name'] ?>"
        },
        {
            "name": "relation_code",
            "title": "总数量",
            "value": "<?php echo $response['data']['num'] ?>"
        },
        {
            "name": "scan_user",
            "title": "扫描人",
            "value": "<?php echo $response['data']['scan_user'] ?>"
        },
        {
            "name": "",
            "title": "",
            "value": ""
        }
    ];

    $(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/store_out_record/do_edit"
        });
    });
</script>
<div class="panel record_table" id="panel_html"></div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body table_store">
        <div class="row">
            <input type="text" class="input" value="" id="code_name" placeholder="商品名称/编码/条形码"/>
            <?php if ($response['lof_status'] == 1) : ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php endif; ?>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i>搜索商品</button>
            <?php //if ($response['scene'] == 'edit'): ?>
<!--                <div style ="float:right;">
                    <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>&nbsp;
                </div>-->
            <?php //endif; ?>
        </div>
        <div class="row">
            <?php
            $buttons = array();
            if ($response['scene'] == 'edit') {
                $buttons = array(
                    array(
                        'id' => 'del',
                        'title' => '删除',
                        'callback' => 'do_delete_detail',
                        'confirm' => '确定要删除吗？'
                    ),
                );
            }
            render_control('DataTable', 'table_list', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品名称',
                            'field' => 'goods_name',
                            'width' => '20%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品编码',
                            'field' => 'goods_code',
                            'width' => '15%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec1_rename'],
                            'field' => 'spec1_name',
                            'width' => '15%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => $response['goods_spec2_rename'],
                            'field' => 'spec2_name',
                            'width' => '15%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品条形码',
                            'field' => 'barcode',
                            'width' => '20%',
                            'align' => '',
                            'id' => 'barcode'
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '数量',
                            'field' => 'num',
                            'width' => '5%',
                            'align' => '',
                            'editor' => $response['lof_status'] != 1 ? "{xtype:'number'}" : ''
                        ),
                        array(
                            'type' => 'button',
                            'show' => 1,
                            'title' => '操作',
                            'field' => '_operate',
                            'width' => '10%',
                            'align' => '',
                            'buttons' => $buttons
                        )
                    )
                ),
                'dataset' => 'b2b/BoxRecordDatailModel::get_by_page',
                'idField' => 'id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                'CellEditing' => $response['scene'] == 'edit' ? TRUE : FALSE,
            ));

            if ($response['lof_status'] == 1) {
                render_control('DataTable', 'table_lof_list', array(
                    'conf' => array(
                        'list' => array(
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品名称',
                                'field' => 'goods_code_name',
                                'width' => '20%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品编码',
                                'field' => 'goods_code',
                                'width' => '10%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => $response['goods_spec1_rename'],
                                'field' => 'spec1_name',
                                'width' => '10%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => $response['goods_spec2_rename'],
                                'field' => 'spec2_name',
                                'width' => '10%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '商品条形码',
                                'field' => 'barcode',
                                'width' => '15%',
                                'align' => '',
                                'id' => 'barcode'
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '批次号',
                                'field' => 'lof_no',
                                'width' => '10%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '生产日期',
                                'field' => 'production_date',
                                'width' => '10%',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '数量',
                                'field' => 'num',
                                'width' => '5%',
                                'align' => '',
                                'editor' => "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'button',
                                'show' => 1,
                                'title' => '操作',
                                'field' => '_operate',
                                'width' => '10%',
                                'align' => '',
                                'buttons' => $buttons
                            )
                        )
                    ),
                    'dataset' => 'b2b/BoxRecordDatailModel::get_by_page_lof',
                    'idField' => 'id',
                    'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                    'CellEditing' => $response['scene'] == 'edit' ? TRUE : FALSE,
                ));
            }
            ?>
        </div>
    </div>

</div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">日志操作 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body table_store">
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
                            'width' => '20%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作名称',
                            'field' => 'action_name',
                            'width' => '20%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '操作时间',
                            'field' => 'add_time',
                            'width' => '20%',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '40%',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['id'], 'module' => 'box_record')),
            ));
            ?>
        </div>
    </div>
</div>
<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $response['data']['id']; ?>')">打印装箱单</a>
    </li>
    <li class="front_close">&lt;</li>
</ul>
<script type="text/javascript">
    $(function () {
        tools();

        if (is_lof == 1) {
            $("#showbatch").click(function () {
                $('#table_list_datatable').hide();
                $('#table_lof_list_datatable').show();
                $('#showbatch').removeClass("curr");
                $('#shownobatch').removeClass("curr");
            });
            $("#shownobatch").click(function () {
                $('#table_lof_list_datatable').hide();
                $('#table_list_datatable').show();
                $('#showbatch').addClass("curr");
                $('#shownobatch').addClass("curr");
            });
        }
        $("#showbatch").click();

        //面板展开和隐藏
        $('.toggle').click(function () {
            $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
            return false;
        });
    });
    //加载工具条
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

    //商品检索
    $('#btnSearchGoods').on('click', function () {
        if (typeof table_listStore != 'undefined') {
            if (is_lof == 1) {
                table_lof_listStore.load({'code_name': $('#code_name').val()});
            }
            table_listStore.load({'code_name': $('#code_name').val()});
        }
    });

    //修改明细数量-未开批次
    if (typeof table_listCellEditing != "undefined" && is_lof != 1) {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_listCellEditing.on('accept', function (record, editor) {
            if (record.record.num <= 0) {
                BUI.Message.Alert('商品数量不能小于1', 'error');
                table_listStore.load();
                return;
            }

            var _record = record.record;
            $.post('?app_act=b2b/box_record/do_edit_detail', {record_code: _record.record_code, sku: _record.sku, barcode: _record.barcode, num: _record.num, is_lof: is_lof}, function (ret) {
                alert_msg(ret);
                if (ret.status == 1) {
                    window.location.reload();
                } else {
                    table_listStore.load();
                }
            }, 'json');
        });
    }

    //修改明细数量-开批次
    if (typeof table_lof_listCellEditing != "undefined" && is_lof == 1) {
        //列表区域,数量修改回调操作
        table_lof_listCellEditing.on('accept', function (record, editor) {
            if (record.record.num <= 0) {
                BUI.Message.Alert('商品数量不能小于1', 'error');
                table_lof_listStore.load();
                return;
            }
            var _record = record.record;
            $.post('?app_act=b2b/box_record/do_edit_detail',
                    {record_code: _record.record_code, sku: _record.sku, barcode: _record.barcode, num: _record.num, lof_no: _record.lof_no, production_date: _record.production_date, is_lof: is_lof},
                    function (ret) {
                        alert_msg(ret);
                        if (ret.status == 1) {
                            window.location.reload();
                        } else {
                            table_lof_listStore.load();
                        }
                    }, 'json');
        });
    }

    //删除明细
    function do_delete_detail(_index, row) {
        var params = {};
        if (is_lof == 1) {
            params = {record_code: row.record_code, sku: row.sku, barcode: row.barcode, lof_no: row.lof_no, production_date: row.production_date, is_lof: is_lof};
        } else {
            params = {record_code: row.record_code, sku: row.sku, barcode: row.barcode, is_lof: is_lof};
        }

        $.post('?app_act=b2b/box_record/do_delete_detail', params, function (ret) {
            alert_msg(ret);
            if (ret['status'] == 1) {
                window.location.reload();
            }
        }, 'json');
    }

    //打印
    function  do_print(_index, id) {
        if(new_clodop_print == 1){
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=b2b_box&record_ids="+id, {
                title: "装箱单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        }else{
        var url = "?app_act=tprint/tprint/do_print&print_templates_code=b2b_box&record_ids=" + id;
        //$("#print_iframe").attr('src', url);
            var iframe = $('<iframe id="" width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', url);
        //window.open(u);
        }
    }

    function alert_msg(ret) {
        var _type = '';
        switch (ret.status) {
            case 1:
                _type = 'success';
                break;
            case 2:
                _type = 'warning';
                break;
            case -1:
                _type = 'error';
                break;
        }

        BUI.Message.Show({
            msg: ret.message,
            icon: _type,
            buttons: [],
            autoHide: true
        });
    }
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>