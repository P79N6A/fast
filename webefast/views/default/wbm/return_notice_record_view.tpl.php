<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    .button-primary[disabled] {
        background-color: #f1f1f1;
        border-color: #f1f1f1;
        color: #c7c7c7;
    }
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>

<?php
render_control('PageHead', 'head1', array('title' => '批发退货通知单',
    'links' => array(
        // array('type' => 'js', 'js' => 'report_excel()', 'title' => '导出'), 
        array('url' => 'wbm/return_notice_record/do_list', 'target' => '_self', 'title' => '批发退货通知单列表'),
    ),
    'ref_table' => 'table'
));
?>
<ul id="tool" class="toolbar frontool frontool_center" style="text-align:left;">        
    <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_sure')) { ?>
        <li class="li_btns" style="margin-left:10px">
            <a class="button button-primary" <?php if ($response['data']['is_check'] == 1 || $response['data']['is_return'] == 1 || $response['data']['is_finish'] == 1) echo 'disabled="disabled"'; ?> href="javascript:do_check(this, '<?php echo $response['data']['return_notice_code']; ?>','<?php echo $response['data']['return_notice_record_id']; ?>','enable')"> 确认</a>
        </li>
        <li class="li_btns">
            <a class="button button-primary" <?php if ($response['data']['is_check'] == 0 || $response['data']['is_return'] == 1 || $response['data']['is_finish'] == 1) echo 'disabled="disabled"'; ?> href="javascript:do_check(this, '<?php echo $response['data']['return_notice_code']; ?>','<?php echo $response['data']['return_notice_record_id']; ?>','disable')">取消确认</a>
        </li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_return')) { ?>
        <li class="li_btns"><a class="button button-primary" <?php if ($response['data']['is_check'] == 0 || $response['data']['is_finish'] == 1 || $response['is_wms'] == 1) echo 'disabled="disabled"'; ?> href="javascript:do_return(this, '<?php echo $response['data']['return_notice_record_id']; ?>')">生成退货单</a></li>
    <?php } ?>
    <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_notice_record/do_finish')) { ?>
        <li class="li_btns"><a class="button button-primary" <?php if ($response['data']['is_check'] == 0 || $response['data']['is_finish'] == 1 || $response['is_wms'] == 1) echo 'disabled="disabled"'; ?> href="javascript:do_finish(this,'<?php echo $response['data']['return_notice_code']; ?>','<?php echo $response['data']['return_notice_record_id']; ?>')">完成</a></li>
    <?php } ?>
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
    });
</script>

<script>
    var return_notice_code = "<?php echo $response['data']['return_notice_code']; ?>";
    var return_notice_record_id = "<?php echo $response['data']['return_notice_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_check']) { ?>
        is_edit = false;
<?php } ?>

    var data = [

        {
            "name": "return_notice_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['return_notice_code'] ?>",
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
            "name": "order_time",
            "title": "下单日期",
            "value": "<?php echo $response['data']['order_time']; ?>",
            "type": "time",

        },
        {
            "name": "custom_code",
            "title": "分销商",
            "value": "<?php echo $response['data']['custom_code_name'] ?>",
            "type": "input",
        },
        {
            "name": "store_code",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code_name'] ?>",
            "type": "input",
        },
        {
            "name": "rebate",
            "title": "折扣",
            "value": "<?php echo $response['data']['rebate'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "name": "return_type_code",
            "title": "退单类型",
            "value": "<?php echo $response['data']['return_type_code'] ?>",
//                "type":"input",
            "type": "select",
            "data":<?php echo $response['selection']['record_type'] ?>,
            "edit": true
        },

        {
            "title": "总数量",
            "value": "<?php echo $response['data']['num'] ?>",
        },
        {
            "title": "总金额",
            "value": "<?php echo number_format($response['data']['money'], 3); ?>",
        },

        {
            "title": "确认",
            "value": "<?php echo $response['data']['is_check_src'] ?>",
        },
        {
            "title": "生成退单",
            "value": "<?php echo $response['data']['is_return_src'] ?>",
        },
        {
            "title": "完成",
            "value": "<?php echo $response['data']['is_finish_src'] ?>",
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
    ];

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/return_notice_record/do_edit"
        });

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code,  'model': 'wbm_return_notice', record_id: return_notice_record_id}
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
            tableStore.load({'code_name': $('#goods_code').val()}, function (data) {

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
                value.num = top.$("input[name='" + num_name + "']").val();
                select_data[di] = value;
                di++;
            }
        });
        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        $.post('?app_act=wbm/return_notice_record/do_add_detail&id=' + return_notice_record_id + '&store_code=' + store_code, {data: select_data}, function (result) {
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

                <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
                <button type="button" class="button button-info"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>
                <!--  <button type="button" class="button button-info" value="重置" id="btnSearchReset"><i class="icon-repeat icon-white"></i> 重置</button>-->

            </div>
            <div style='float:right'>
                <?php if (0 == $response['data']['is_check']) { ?>
                    <button type="button" class="button button-success" value="商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
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
                        'width' => '130',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品品牌',
                        'field' => 'brand_name',
                        'width' => '120'
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
                        'width' => '90',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec2_rename'],
                        'field' => 'spec2_name',
                        'width' => '90',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条形码',
                        'field' => 'barcode',
                        'width' => '130',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发价',
                        'field' => 'trade_price',
                        'width' => '80',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '折扣',
                        'field' => 'rebate',
                        'width' => '60',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => ''
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
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成数',
                        'field' => 'finish_num',
                        'width' => '60',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数',
                        'field' => 'difference_num',
                        'width' => '60',
                        'align' => '',
                    ),
                    array(
                        'type' => 'button',
                        'show' => 1,
                        'title' => '操作',
                        'field' => '_operate',
                        'width' => '70',
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
            'dataset' => 'wbm/ReturnNoticeDetailRecordModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'return_notice_record_detail_id',
            'params' => array('filter' => array('return_notice_code' => $response['data']['return_notice_code'])),
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
                            'width' => '250',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['return_notice_record_id'], 'module' => 'wbm_return_notice_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    function  do_check(_index, return_notice_code, id, type) {
        url = '?app_act=wbm/return_notice_record/do_check';
        data = {return_notice_code: return_notice_code, type: type, id: id};
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
    function do_return(_index, return_notice_record_id) {
        //判断是否有未入库退货单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/return_notice_record/out_relation'); ?>',
            data: {id: return_notice_record_id},
            success: function (ret) {
                // alert(ret);
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act=wbm/return_notice_record/do_return&return_notice_record_id=" + return_notice_record_id.toString();
                    _do_execute(url, 'table');
                } else {
                    if (ret.status == '-1') {
                        BUI.Message.Confirm('存在未验收的批发退货单，是否继续？', function () {
                            url = "?app_act=wbm/return_notice_record/do_return&return_notice_record_id=" + return_notice_record_id.toString();
                            _do_execute(url, 'table');
                        });
                    }
                }
            }
        });


    }

    function do_finish(_index, return_notice_code, id) {
        url = '?app_act=wbm/return_notice_record/do_finish';
        data = {return_notice_code: return_notice_code, id: id};
        _do_operate(url, data, 'flush');
    }


    function report_excel()
    {
        var param = "";
        param = param + "&id=" + return_notice_record_id + "&return_notice_code=" + return_notice_code + "&app_fmt=json";
        url = "?app_act=wbm/return_notice_record/export_csv_list" + param;
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
            url: '?app_act=wbm/return_notice_record/do_delete_detail',
            data: {return_notice_code: row.return_notice_code, return_notice_record_detail_id: row.return_notice_record_detail_id, id: row.return_notice_record_id},
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

    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=wbm/return_notice_record/importGoods&id=" + return_notice_record_id;
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


    /**
     * 生成退单
     * @param _index
     * @param row
     * @param active
     * @private
     */
    function _do_return(url, ref, title = '生成退单', width = '600', height = '500') {
        new ESUI.PopWindow(url, {
            title: title,
            width: width,
            height: height,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                if (ref == 'table') {
                    tableStore.load();
                } else {
                    location.reload();
                }

            }
        }).show()

    }
    if (typeof tableCellEditing != "undefined") {
        tableCellEditing.on('accept', function (record, editor) {
            if (parseInt(record.record.trade_price) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            } else {
                console.log(record);
            }
            $.post('?app_act=wbm/return_notice_record/do_edit_detail',
                    {pid: record.record.return_notice_record_id, num: record.record.num, sku: record.record.sku, rebate: record.record.rebate, trade_price: record.record.trade_price},
                    function (result) {
                        var _res = result.res;
                        tableStore.load();
                        $("#base_table tr").eq(2).find("td").eq(1).html(_res.num);
                        $("#base_table tr").eq(2).find("td").eq(2).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }

</script>

