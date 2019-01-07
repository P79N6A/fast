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
$links[] = array('url' => 'pur/planned_record/do_list', 'target' => '_self', 'title' => '采购订单');
if ($response['data']['is_check'] == 1) {
    $links[] = array('url' => "pur/planned_record/importGoods&import_type=0&id={$response['data']['planned_record_id']}", 'pop_size' => '500,350', 'is_pop' => 'true', 'title' => '导入完成数');
}
render_control('PageHead', 'head1', array('title' => '采购订单',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_check')) { ?>
        <li class="li_btns"><?php if (0 == $response['data']['is_check']) { ?>
                <a class="button button-primary" href="javascript:do_check(this, '<?php echo $response['data']['planned_record_id']; ?>')"> 确认</a>
            <?php } ?>
        </li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_check')) { ?>
        <li class="li_btns">
            <?php if (1 == $response['data']['is_check'] && $response['data']['is_finish'] != 1 && $response['data']['is_execute'] == 0 && $response['data']['is_notify_payment'] == 0) { ?>
                <a class="button button-primary" href="javascript:do_re_check(this, '<?php echo $response['data']['planned_record_id']; ?>')">取消确认</a>
            <?php } ?>
        </li>
    <?php } ?>
    <?php if (1 == $response['data']['is_check'] && $response['data']['is_finish'] != 1) { ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_execute')) { ?>
            <li class="li_btns">
                <a class="button button-primary" href="javascript:do_execute(this, '<?php echo $response['data']['planned_record_id']; ?>')">生成通知单</a>
            </li>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_notify_payment') && $response['data']['is_notify_payment'] == 0 && $response['is_notify_code'] == 0 && $response['is_pur_payment'] == 1) { ?>
            <li class="li_btns">
                <a class="button button-primary" href="javascript:do_notify_payment(this, '<?php echo $response['data']['planned_record_id']; ?>')">通知财务付款</a>
            </li>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_cancel_payment') && $response['data']['is_notify_payment'] == 1 && $response['is_pur_payment'] == 1) { ?>
            <li class="li_btns">
                <a class="button button-primary" href="javascript:do_cancel_payment(this, '<?php echo $response['data']['planned_record_id']; ?>')">取消通知财务付款</a>
            </li>
        <?php } ?>
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/planned_record/do_finish')) { ?>
            <li class="li_btns">
                <a class="button button-primary" href="javascript:do_finish(this, '<?php echo $response['data']['record_code']; ?>')">完成</a>
            </li>
        <?php } ?>
    <?php } ?>
    <li class="li_btns">
        <a class="button button-primary" style="background-color: #1695ca;"  onclick="report_excel()" id="btn-csv">导出</a>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $response['data']['planned_record_id']; ?>')">打印</a>
    </li>
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
    var id = "<?php echo $response['data']['planned_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
    var rebate = <?php echo $response['data']['rebate'] ?>;
<?php if (1 == $response['data']['is_check']) { ?>
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
                //"edit":true
            },
            {
                "name": "store_code",
                "title": "仓库",
                "value": "<?php echo $response['data']['store_code'] ?>",
                "type": "select",
                //"edit":true,
                "data":<?php echo $response['selection']['store'] ?>,
            },
            {
                "name": "supplier_code",
                "title": "供应商",
                "value": "<?php echo $response['data']['supplier_code'] ?>",
                "type": "select",
                //"edit":true,
                "data":<?php echo $response['selection']['supplier'] ?>,
            },
            {
                "name": "planned_time",
                "title": "计划日期",
                "value": "<?php echo $response['data']['planned_time'] ?>",
                "type": "time",
                // "edit":true,
            },
            {
                "name": "in_time",
                "title": "入库期限",
                "value": "<?php echo $response['data']['in_time'] ?>",
                "type": "time",
                // "edit":true
            },
            {
                "name": "pur_type_code",
                "title": "采购类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                // "edit":true,
                "data":<?php echo $response['selection']['record_type'] ?>,
            },
            {
                "title": "数量",
                "value": "<?php echo $response['data']['num'] ?>",
            },
            {
                "title": "金额",
                "value": "<?php echo $response['data']['status'] == 1 ? number_format($response['data']['money'], 2) : '****'; ?>",
            },
            {
                "title": "完成金额",
                "name": "finish_money",
                "value": "<?php echo $response['data']['status'] == 1 ? number_format($response['data']['finish_money'], 3) : '****'; ?>",
                "edit": true,
            },
            {
                "title": "备注",
                "name": "remark",
                "type": "input",
                "value": "<?php echo $response['data']['remark'] ?>",
                "edit": true,
            },
            {
                "title": "确认",
                "value": "<?php echo $response['data']['is_check_src'] ?>",
            },
            {
                "title": "生成订单",
                "value": "<?php echo $response['data']['is_execute_src'] ?>",
            },
            {
                "title": "完成",
                "value": "<?php echo $response['data']['is_finish_src'] ?>",
            },
            {
                "title": "下单时间",
                "value": "<?php echo $response['data']['record_time'] ?>"
            },
            {
                "name": "rebate",
                "title": "折扣",
                "value": "<?php echo $response['data']['rebate'] ?>",
                "type": "input",
                //"edit": true,
            },
        ];
<?php } else { ?>
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
                "name": "store_code",
                "title": "仓库",
                "value": "<?php echo $response['data']['store_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['store'] ?>,
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
                "name": "planned_time",
                "title": "计划日期",
                "value": "<?php echo $response['data']['planned_time'] ?>",
                "type": "time",
                "edit": true,
            },
            {
                "name": "in_time",
                "title": "入库期限",
                "value": "<?php echo $response['data']['in_time'] ?>",
                "type": "time",
                "edit": true
            },
            {
                "name": "pur_type_code",
                "title": "采购类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['record_type'] ?>,
            },
            {
                "title": "数量",
                "value": "<?php echo $response['data']['num'] ?>",
            },
            {
                "title": "金额",
                "value": "<?php echo $response['data']['status'] == 1 ? number_format($response['data']['money'], 2) : '****'; ?>",
            },
            {
                "title": "完成金额",
                "name": "finish_money",
                "value": "<?php echo $response['data']['status'] == 1 ? number_format($response['data']['finish_money'], 3) : '****'; ?>",
                "edit": true,
            },
            {
                "title": "备注",
                "name": "remark",
                "type": "input",
                "value": "<?php echo $response['data']['remark'] ?>",
                "edit": true,
            },
            {
                "title": "确认",
                "value": "<?php echo $response['data']['is_check_src'] ?>",
            },
            {
                "title": "生成订单",
                "value": "<?php echo $response['data']['is_execute_src'] ?>",
            },
            {
                "title": "完成",
                "value": "<?php echo $response['data']['is_finish_src'] ?>",
            },
            {
                "title": "下单时间",
                "value": "<?php echo $response['data']['record_time'] ?>"
            },
            {
                "name": "rebate",
                "title": "折扣",
                "value": "<?php echo $response['data']['rebate'] ?>",
                "type": "input",
                "edit": true,
            },
        ];
<?php } ?>

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=pur/planned_record/do_edit&record_code=" + record_code
        });

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'pur_planned', record_id: id}
                });
            } else {
                get_goods_panel({
                    "id": "btnSelectGoods",
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'diy': '0'},
                    "callback": addgoods
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
        $.post('?app_act=pur/planned_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                    //   _thisDialog.remove(true);
                }, 'error');
            } else {
                // top.BUI.Message.Tip('添加成功','info');
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
                <!--  <button type="button" class="button button-info" value="重置" id="btnSearchReset"><i class="icon-repeat icon-white"></i> 重置</button>-->

            </div>
            <div style='float:right'>
                <?php if (0 == $response['data']['is_check']) { ?>
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
                        'editor' => $response['price_status'] == 1 && (1 != $response['data']['is_check']) ? "{xtype:'number'}" : ''
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
                        'editor' => (1 == $response['data']['is_check']) ? '' : "{xtype:'number'}",
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
                        'editor' => (1 == $response['data']['is_check']) ? "{xtype:'number'}" : '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成金额',
                        'field' => 'finish_money',
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
            'dataset' => 'pur/PlannedRecordDetailModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'planned_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'], 'rebate' => $response['data']['rebate'])),
            //'RowNumber'=>true,
            //'CheckSelection'=>true,
            //'CellEditing'=>(1==$response['data']['is_check'])?false:true,
            'CellEditing' => true,
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
                            'width' => '250',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['planned_record_id'], 'module' => 'planned_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var new_clodop_print = '<?php echo $response['new_clodop_print']; ?>';
    function do_execute(_index, planned_record_id) {
        url = "?app_act=pur/planned_record/execute&planned_record_id=" + planned_record_id.toString();
        _do_execute(url, 'flush');
    }
    function  do_re_check(_index, planned_record_id) {
        url = '?app_act=pur/planned_record/do_check';
        data = {id: planned_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    function do_notify_payment(_index, planned_record_id) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=pur/planned_record/do_notify_payment',
            data: {planned_record_id: planned_record_id, 'type': 'payment'},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    location.reload();
                }
            }
        });
    }
    function do_cancel_payment(_index, planned_record_id) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=pur/planned_record/do_notify_payment',
            data: {planned_record_id: planned_record_id, 'type': 'cancel_payment'},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    location.reload();
                }
            }
        });
    }
    function  do_check(_index, planned_record_id) {
        url = '?app_act=pur/planned_record/do_check';
        data = {id: planned_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
    //打印
    function  do_print(_index, planned_record_id) {
        var check_url = "?app_act=pur/planned_record/check_is_print&app_fmt=json";
        $.post(check_url, {planned_record_id: planned_record_id}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_sellrecord(_index, planned_record_id);
                }, 'question');
            } else {
                btn_init_opt_print_sellrecord(_index, planned_record_id);
            }
        }, 'json');
    }
    function  btn_init_opt_print_sellrecord(_index, planned_record_id) {
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=pur_planned_record&record_ids=" + planned_record_id, {
                title: "采购单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=pur_planned_record&record_ids=' + planned_record_id;
            //$("#print_iframe").attr('src',u);
            var iframe = $('<iframe id="" width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', u);
        }
    }
    function do_finish(_index, planned_record_id) {
        url = '?app_act=pur/planned_record/do_finish';
        data = {record_code: planned_record_id};
        BUI.Message.Show({
            title: '',
            msg: '确定要完成吗',
            icon: 'question',
            buttons: [
                {
                    text: '确定',
                    elCls: 'button button-primary',
                    handler: function () {
                        _do_operate(url, data, 'flush');
                    }
                },
                {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }

            ]
        });

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
            url: '?app_act=pur/planned_record/do_delete_detail',
            data: {planned_record_detail_id: row.planned_record_detail_id, pid: row.pid, sku: row.sku},
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
            //console.log(record.record);
            if (parseInt(record.record.price) < 0 || parseInt(record.record.num) < 0 || parseInt(record.record.finish_num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
            $.post('?app_act=pur/planned_record/do_edit_detail',
                    {finish_num: record.record.finish_num, pid: record.record.pid, num: record.record.num, sku: record.record.sku, sell_price: record.record.price, barcode: record.record.barcode},
                    function (result) {
                        var _res = result.res;
                        tableStore.load();
                        $("#base_table tr").eq(2).find("td").eq(1).html(_res.num);
                        $("#base_table tr").eq(2).find("td").eq(2).html(_res.money);
                        location.reload();
                    }, 'json');
        });
    }

    //导出明细
    function report_excel() {
        var param = "";
        param = param + "&id=" + id + "&record_code=" + record_code + "&goods_code=" + $('#goods_code').val() + "&type=view_export&app_fmt=json";
        url = "?app_act=pur/planned_record/exprot_detail" + param;

        window.location.href = url;
    }

    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=pur/planned_record/importGoods&import_type=1&id=" + id;
            new ESUI.PopWindow(url, {
                title: "导入商品",
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
    })

</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>
