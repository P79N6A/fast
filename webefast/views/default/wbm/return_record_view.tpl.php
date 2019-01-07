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
render_control('PageHead', 'head1', array('title' => '批发退货单',
    'links' => array(
        array('url' => 'wbm/return_record/do_list', 'target' => '_self', 'title' => '批发退货单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">

    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/return_record/do_shift_in')) { ?>
            <?php if ($response['data']['is_store_in'] != 1) { ?>
                <a class="button button-primary" href="javascript:check_diff_num(this, '<?php echo $response['data']['record_code']; ?>')">验收</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <button type="button" class="button button-info" style="background-color: #1695ca;"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $request['return_record_id']; ?>')">打印</a>
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
    });
</script>
<script>
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['return_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var is_lof = "<?php echo $response['data']['lof_status']; ?>";
    var custom_code = "<?php echo $response['is_fenxiao'] == 1 ? $response['data']['distributor_code'] : ''; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_store_in']) { ?>
        var data = [
            {
                "name": "record_code",
                "title": "单据编号",
                "value": "<?php echo $response['data']['record_code'] ?>",
                "type": "input"
            },
            {
                "name": "order_time",
                "title": "下单时间",
                "value": "<?php echo $response['data']['order_time'] ?>"
            },
            {
                "title": "验收状态",
                "value": "<?php echo $response['data']['is_store_in_src'] ?>"
            },
            {
                "name": "distributor_code",
                "title": "分销商",
                "value": "<?php echo $response['data']['distributor_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['custom'] ?>
            },
            {
                "name": "rebate",
                "title": "折扣",
                "value": "<?php echo $response['data']['rebate'] ?>"
            },
            {
                "name": "store_code",
                "title": "仓库",
                "value": "<?php echo $response['data']['store_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['store'] ?>
            },
            {
                "name": "record_time",
                "title": "业务日期",
                "value": "<?php echo $response['data']['record_time'] ?>",
                "type": "time"
            },
            {
                "name": "record_type_code",
                "title": "业务类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['record_type'] ?>,
            },
            {
                "name": "num",
                "title": "总退货通知数",
                "value": "<?php echo $response['data']['enotice_num_all'] ?>"
            },
            {
                "name": "num",
                "title": "总退货数",
                "value": "<?php echo $response['data']['num'] ?>"
            },
            {
                "name": "money",
                "title": "总金额",
                "value": "<?php echo $response['data']['money'] ?>"
            },
            {
                "name": "init_code",
                "title": "通知单号",
                "value": "<?php echo $response['data']['relation_code'] ?>",
                "type": "input"
            },
            {
                "name": "init_code",
                "title": "原单号",
                "value": "<?php echo $response['data']['init_code'] ?>",
                "type": "input"
            },
            {
                "name": "remark",
                "title": "备注",
                "value": "<?php echo $response['data']['remark'] ?>",
                "type": "input",
                "edit": true
            },
        ];
<?php } else { ?>
        var data = [
            {
                "name": "record_code",
                "title": "单据编号",
                "value": "<?php echo $response['data']['record_code'] ?>",
                "type": "input"
            },
            {
                "name": "order_time",
                "title": "下单时间",
                "value": "<?php echo $response['data']['order_time'] ?>"
            },
            {
                "title": "验收状态",
                "value": "<?php echo $response['data']['is_store_in_src'] ?>"
            },
            {
                "name": "distributor_code",
                "title": "分销商",
                "value": "<?php echo $response['data']['distributor_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['custom'] ?>
            },
            {
                "name": "rebate",
                "title": "折扣",
                "value": "<?php echo $response['data']['rebate'] ?>"
            },
            {
                "name": "store_code",
                "title": "仓库",
                "value": "<?php echo $response['data']['store_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['store'] ?>
            },
            {
                "name": "record_time",
                "title": "业务日期",
                "value": "<?php echo $response['data']['record_time'] ?>",
                "type": "time",
                "edit": true
            },
            {
                "name": "record_type_code",
                "title": "业务类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['record_type'] ?>,
            },
            {
                "name": "num",
                "title": "总退货通知数",
                "value": "<?php echo $response['data']['enotice_num_all'] ?>"
            },
            {
                "name": "num",
                "title": "总退货数",
                "value": "<?php echo $response['data']['num'] ?>"
            },
            {
                "name": "money",
                "title": "总金额",
                "value": "<?php echo $response['data']['money'] ?>"
            },
            {
                "name": "init_code",
                "title": "通知单号",
                "value": "<?php echo $response['data']['relation_code'] ?>",
                "type": "input"
            },
            {
                "name": "init_code",
                "title": "原单号",
                "value": "<?php echo $response['data']['init_code'] ?>",
                "type": "input"
            },
            {
                "name": "remark",
                "title": "备注",
                "value": "<?php echo $response['data']['remark'] ?>",
                "type": "input",
                "edit": true
            }
        ];
<?php } ?>
    $(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/return_record/do_edit"
        });

        $("#showbatch").bind("click", showbatch);
        $("#shownobatch").bind("click", shownobatch);

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'wbm_return', record_id: id}
                });
            } else {
                get_goods_panel({
                    "id": "btnSelectGoods",
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'custom_code': custom_code},
                    "callback": addgoods
                });
            }
        }

        $('#btnSearchGoods').on('click', function () {
            if (is_lof == 1) {
                table_lof_listStore.load({'code_name': $('#goods_code').val()});
                table_listStore.load({'code_name': $('#goods_code').val()});
            } else {
                table_listStore.load({'code_name': $('#goods_code').val()});
            }
        });
    });

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.sku + "']").val() != '' && top.$("input[name='num_" + value.sku + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.sku + "']").val();
                value.lof_no = top.$("input[name='lof_no_" + value.sku + "']").val();
                value.production_date = top.$("input[name='production_date_" + value.sku + "']").val();
                select_data[di] = value;
                di++;
            }
        });
        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        $.post('?app_act=wbm/return_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                    //   _thisDialog.remove(true);
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
    function shownobatch() {
        type = 1;
        $('#batch tr').find('td:eq(5)').hide();
        $('#batch tr').find('th:eq(5)').hide();
        $('#batch tr').find('td:eq(6)').hide();
        $('#batch tr').find('th:eq(6)').hide();
        $('#showbatch').addClass("curr");
        $('#shownobatch').addClass("curr");
    }
    function showbatch() {
        type = 2;
        $('#batch tr').find('td:eq(5)').show();
        $('#batch tr').find('th:eq(5)').show();
        $('#batch tr').find('td:eq(6)').show();
        $('#batch tr').find('th:eq(6)').show();
        $('#shownobatch').removeClass("curr");
        $('#showbatch').removeClass("curr");
    }
</script>

<div class="panel record_table" id="panel_html"></div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>请输入</b>
            <input type="text" class="input" value="" placeholder="商品编码/商品条形码" id="goods_code"/>
            <?php if (0 == $response['data']['is_store_in']) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>&nbsp;
                    <button type="button" class="button button-success" value="新增商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>&nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php } ?>
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
        </div>
    </div>
    <?php if ($response['lof_status'] != 1): ?>
        <?php
        render_control('DataTable', 'table_list', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '180',
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
                        'width' => '150',
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
                        'width' => '100',
                        'align' => '',
                        'id' => 'barcode'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品库位',
                        'field' => 'shelf_name',
                        'width' => '100',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                        'editor' => !isset($response['is_fenxiao']) && empty($response['is_fenxiao']) ? "{xtype:'number'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
                        'field' => 'price1',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际退货数',
                        'field' => 'num',
                        'width' => '80',
                        'align' => '',
                        'editor' => $response['lof_status'] != 1 ? "{xtype:'number'}" : ''
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
                        'title' => '通知数',
                        'field' => 'enotice_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数',
                        'field' => 'different_num',
                        'width' => '80',
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
                                'show_cond' => 'obj.is_store_in == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/ReturnRecordDetailModel::get_by_page',
            'idField' => 'return_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_in']) ? false : true,
        ));
        ?>
    <?php endif; ?>
    <?php if ($response['lof_status'] == 1): ?>       
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
                        'title' => '商品品牌',
                        'field' => 'brand_name',
                        'width' => '120'
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
                        'width' => '100',
                        'align' => '',
                        'id' => 'barcode'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品库位',
                        'field' => 'shelf_name',
                        'width' => '100',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
                        'field' => 'price1',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际退货数',
                        'field' => 'num',
                        'width' => '80',
                        'align' => ''
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
                        'title' => '通知数',
                        'field' => 'enotice_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数',
                        'field' => 'different_num',
                        'width' => '80',
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
                                'show_cond' => 'obj.is_store_in == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/ReturnRecordDetailModel::get_by_page',
            'idField' => 'return_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_in']) ? false : true,
        ));
        render_control('DataTable', 'table_lof_list', array(
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
                        'align' => '',
                        'id' => 'barcode'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批次号',
                        'field' => 'lof_no',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品库位',
                        'field' => 'shelf_name',
                        'width' => '100',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '生产日期',
                        'field' => 'production_date',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                        'editor' => !isset($response['is_fenxiao']) && empty($response['is_fenxiao']) ? "{xtype:'number'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
                        'field' => 'price1',
                        'width' => '120',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际退货数',
                        'field' => 'num',
                        'width' => '120',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '总金额',
                        'field' => 'money',
                        'width' => '80',
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
                                'callback' => 'do_delete_detail_lof',
                                'show_cond' => 'obj.is_store_in == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/ReturnRecordDetailModel::get_by_page_lof',
            'idField' => 'return_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_in']) ? false : true,
        ));
        ?>
    <?php endif; ?>
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
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['return_record_id'], 'module' => 'wbm_return_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var new_clodop_print = "<?php echo $response['new_clodop_print'] ?>";
    //取消确认
    function  do_re_sure(_index, return_record_id) {
        url = '?app_act=wbm/return_record/do_sure';
        data = {id: return_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //确认
    function  do_sure(_index, return_record_id) {
        url = '?app_act=wbm/return_record/do_sure';
        data = {id: return_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }

    function check_diff_num(_index, record_code) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/return_record/check_diff_num',
            data: {record_code: record_code},
            success: function (ret) {
                var sta = ret.status;
                if (sta == 1) {
                    BUI.Message.Confirm('是否确认验收？', function () {
                        do_shift_in(_index, record_code);
                    }, 'question');
                    tableStore.load();
                } else if (sta == 2) {
                    BUI.Message.Confirm(ret.message, function () {
                        do_shift_in(_index, record_code);
                    }, 'question');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    //出库
    function do_shift_in(_index, record_code) {
        url = '?app_act=wbm/return_record/do_shift_in';
        data = {id: id, record_code: record_code};
        _do_operate(url, data, 'flush');
    }
    //打印
    function  do_print(_index, return_record_id) {
        var check_url = "?app_act=wbm/return_record/check_is_print&app_fmt=json";
        $.post(check_url, {return_record_id: return_record_id}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_sellrecord(_index, return_record_id);
                }, 'question');
            } else {
                btn_init_opt_print_sellrecord(_index, return_record_id);
            }
        }, 'json');
    }
    function  btn_init_opt_print_sellrecord(_index, return_record_id) {
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=wbm_return_new&record_ids=" + return_record_id, {
                title: "批发退货单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            var u = '?app_act=sys/flash_print/do_print';
            u += '&template_code=wbm_return&model=wbm/ReturnRecordModel&typ=default&record_ids=' + return_record_id;
            window.open(u);
        }

    }
</script>
<script type="text/javascript">
    is_lof = '<?php echo $response['lof_status'] ?>';
    if (is_lof == 1) {
        $(function () {
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
            $("#showbatch").click();
        });


        function do_delete_detail_lof(_index, row) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=wbm/return_record/do_delete_detail_lof',
                data: {id: row.id},
                success: function (ret) {
                    // tableStore.load({'code_name': ''});
                    // table1Store.load({'code_name': ''});
                    var type = (ret.status == 1) ? 'success' : 'error';
                    if (type != 'success') {
                        BUI.Message.Alert(ret.message, type);
                    } else {
                        location.reload();
                    }
                }
            });
        }
    }
</script>
<script type="text/javascript">
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

    //删除单据明细
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/return_record/do_delete_detail',
            data: {return_record_detail_id: row.return_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                //batchStore.load({'code_name': ''});
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    location.reload();
                }
            }
        });
    }
    if (typeof table_listCellEditing != "undefined") {
        //列表区域,数量修改回调操作
        table_listCellEditing.on('accept', function (record, editor) {
            if (record.record.num < 0 || record.record.price < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                table_listStore.load();
                return;
            }
            var _record = record.record;
            $.post('?app_act=wbm/return_record/do_edit_detail',
                    {pid: _record.pid, record_code: _record.record_code, sku: _record.sku, rebate: _record.rebate, num: _record.num, price: _record.price},
                    function (result) {
                        if (result.status == -1) {
                            BUI.Message.Alert(result.message, function () {
                                location.reload();
                            }, 'error');
                        }
                        var _res = result.res;
                        table_listStore.load();
                        $("#base_table tr").eq(3).find("td").eq(0).html(_res.num);
                        $("#base_table tr").eq(3).find("td").eq(1).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }

    if (is_lof == 1) {
        if (typeof table_lof_listCellEditing != "undefined") {
            //列表区域,批次明细数量修改回调操作
            table_lof_listCellEditing.on('accept', function (record, editor) {
                if (record.record.num < 0 || record.record.price < 0) {
                    BUI.Message.Alert('不能为负数', 'error');
                    table_listStore.load();
                    return;
                }
                var _record = record.record;
                $.post('?app_act=wbm/return_record/do_edit_detail_lof',
                        {pid: _record.pid, record_code: _record.record_code, sku: _record.sku, rebate: _record.rebate, num: _record.num, price: _record.price, lof_no: _record.lof_no, production_date: _record.production_date},
                        function (result) {
                            var _res = result.res;
                            table_listStore.load();
                            $("#base_table tr").eq(3).find("td").eq(0).html(_res.num);
                            $("#base_table tr").eq(3).find("td").eq(1).html(_res.money);
                            logStore.load();
                        }, 'json');
            });
        }
    }
    $(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=wbm/return_record/importGoods&id=" + id;
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
    });
    //导出
    function report_excel() {
        var param = "";
        var goods_code = $('#goods_code').val();
        param = param + "&id=" + id + "&record_code=" + record_code + "&code_name=" + $('#goods_code').val() + "&app_fmt=json&goods_code=" + goods_code;
        url = "?app_act=wbm/return_record/export_csv_list" + param;
        window.location.href = url;
    }

    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan/view_scan&dj_type=wbm_return&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    });
</script>
