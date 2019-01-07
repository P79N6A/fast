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

    .show_scan_mode a{background-color: #5bc0de;border-color: #46b8da;color: #ffffff;display:block;width:100px;height:50px;float:left;margin:20px;line-height:50px;text-align:center;font-size:18px;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '批发销货单',
    'links' => array(
        array('url' => 'wbm/store_out_record/do_list', 'target' => '_self', 'title' => '批发销货单列表')
    ),
    'ref_table' => 'table'
));
?>
<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if ($response['data']['is_store_out'] != 1 && $response['data']['is_cancel'] == 0) { ?>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_checkin')) { ?>
                <a class="button button-primary" href="javascript:check_diff_num(this, '<?php echo $response['data']['record_code']; ?>',0)">验收</a>
            <?php } ?>
            <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/do_checkin_time')) { ?>
                <a class="button button-primary" href="javascript:check_diff_num(this, '<?php echo $response['data']['record_code']; ?>',1)">按业务日期验收</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/store_out_record/export_list')) { ?>
            <a class="button button-primary" href="javascript:report_excel()"> 导出</a>
        <?php } ?>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $response['data']['store_out_record_id']; ?>')">打印</a>
    </li>
    <?php if (load_model('sys/SysParamsModel')->get_val_by_code(array('character_print'))['character_print']) { ?>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $response['data']['store_out_record_id']; ?>',2)">打印(服装行业)</a>
    </li>
    <?php } ?>
    <li class="li_btns">
        <?php if ($response['pur_express_print'] == 1) { ?>
            <a class="button button-primary" href="javascript:do_print_express();">打印快递单</a>
        <?php } ?>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:print_aggr_box();">打印汇总单</a>
    </li>
    <?php if($response['wbm_barcode_print']): ?>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print_barcode(this, '<?php echo $response['data']['store_out_record_id']; ?>')">打印条码</a>
    </li>
    <?php endif; ?>
    <li class="li_btns">
        <?php if ($response['is_JIT'] == 1 && $response['add_service_status'] == 1) { ?>
            <a class="button button-primary" href="javascript:print_goods();">打印商品</a>
        <?php } ?>
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
    var num = "<?php echo $response['data']['num']; ?>";
    var enotice_num = "<?php echo $response['data']['enotice_num']; ?>";
    var id = "<?php echo $response['data']['store_out_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var is_notice = "<?php echo empty($response['data']['relation_code']) ? 0 : 1 ?>";
    var type = 1;
    var is_edit = true;
    var custom_code = '<?php echo $response['custom_code']; ?>';
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
<?php if (1 == $response['data']['is_sure'] || $response['data']['is_cancel'] == 1) { ?>
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
                "title": "验收",
                "value": "<?php echo $response['data']['is_store_out_src'] ?>"
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
                "name": "num",
                "title": "总出库数",
                "value": "<?php echo $response['data']['num'] ?>"
            },
            {
                "name": "money",
                "title": "总金额",
                "value": "<?php echo $response['data']['money'] ?>"
            },
            {
                "name": "num",
                "title": "总通知数",
                "value": "<?php echo $response['data']['enotice_num'] ?>"
            },
            {
                "name": "init_code",
                "title": "批发通知单号",
                "value": "<?php echo $response['data']['relation_code'] ?>",
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
<?php } else if (!empty($response['data']['relation_code'])) { ?>
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
                "title": "验收",
                "value": "<?php echo $response['data']['is_store_out_src'] ?>"
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
                //  "edit": true,
                "data":<?php echo $response['selection']['store'] ?>
            },
            {
                "name": "record_time",
                "title": "业务日期",
                "value": "<?php echo $response['data']['record_time'] ?>",
                "type": "time",
                "edit": true
            },
            {"name": "record_type_code",
                "title": "业务类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['record_type'] ?>,
                "edit": true
            },
            {
                "name": "num",
                "title": "总出库数",
                "value": "<?php echo $response['data']['num'] ?>"
            },
            {
                "name": "money",
                "title": "总金额",
                "value": "<?php echo $response['data']['money'] ?>"
            },
            {
                "name": "num",
                "title": "总通知数",
                "value": "<?php echo $response['data']['enotice_num'] ?>"
            },
            {
                "name": "init_code",
                "title": "批发通知单号",
                "value": "<?php echo $response['data']['relation_code'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "remark",
                "title": "备注",
                "value": "<?php echo $response['data']['remark'] ?>",
                "type": "input",
                "edit": true
            }
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
                "title": "验收",
                "value": "<?php echo $response['data']['is_store_out_src'] ?>"
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
            {"name": "record_type_code",
                "title": "业务类型",
                "value": "<?php echo $response['data']['record_type_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['record_type'] ?>,
                "edit": true
            },
            {
                "name": "num",
                "title": "总出库数",
                "value": "<?php echo $response['data']['num'] ?>"
            },
            {
                "name": "money",
                "title": "总金额",
                "value": "<?php echo $response['data']['money'] ?>"
            },
            {
                "name": "num",
                "title": "总通知数",
                "value": "<?php echo $response['data']['enotice_num'] ?>"
            },
            {
                "name": "init_code",
                "title": "批发通知单号",
                "value": "<?php echo $response['data']['relation_code'] ?>",
                "type": "input",
                "edit": true
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
<?php $edit_bool = $response['data']['is_cancel'] == 1 ? 'false' : 'true'; ?>
    var delivery_data = [
        {
            "name": "express_code",
            "title": "配送方式",
            "value": "<?php echo $response['data']['express_code'] ?>",
            "type": "select",
            "edit": <?php echo $edit_bool; ?>,
            "data":<?php echo $response['selection']['express_code'] ?>
        },
        {
            "name": "express",
            "title": "快递单号",
            "value": "<?php echo $response['data']['express'] ?>",
            "type": "input",
            "edit": <?php echo $edit_bool; ?>
        },
        {
            "name": "express_money",
            "title": "运费",
            "value": "<?php echo $response['data']['express_money'] ?>",
            "type": "input",
            "edit": <?php echo $edit_bool; ?>
        },
        {
            "name": "name",
            "title": "联系人",
            "value": "<?php echo $response['data']['name'] ?>",
            "type": "input",
            "edit": <?php echo $edit_bool; ?>
        },
        {
            "name": "addr",
            "title": "地址",
            "value": "<?php echo $response['data']['addr'] ?>"
        },
        {
            "name": "address",
            "title": "详细地址",
            "value": "<?php echo $response['data']['address'] ?>",
            "type": "input",
            "edit": <?php echo $edit_bool; ?>
        },
        {
            "name": "tel",
            "title": "联系电话",
            "value": "<?php echo $response['data']['tel'] ?>",
            "type": "input",
            "edit": <?php echo $edit_bool; ?>
        }
    ];
    $(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "title": "基本信息",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/store_out_record/do_edit"
        });

        var delivery_record = new record_table();
        delivery_record.init({
            "id": "panel_deliverty_html",
            "title": "配送信息",
            "data": delivery_data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/store_out_record/do_edit"
        });

        $("#showbatch").bind("click", showbatch);
        $("#shownobatch").bind("click", shownobatch);

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'wbm_store_out', record_id: id}
                });
            } else {
                get_goods_inv_panel({
                    "id": "btnSelectGoods",
                    "callback": addgoods,
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'status': 1, 'custom_code': custom_code}
                });
            }
        }

        $('#btnSearchGoods').on('click', function () {
            if (is_lof == 1) {
                table_lof_listStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
                table_listStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
            } else {
                table_listStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
            }
        });
    });

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        var allow_negative_inv = '<?php echo $response['data']['allow_negative_inv'] ?>'
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num > 0) {
//                    if ((parseInt(value.num) > parseInt(value.available_mum))&&allow_negative_inv!=1) {
//                        value.num = value.available_mum;
//                    }
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
        $.post('?app_act=wbm/store_out_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            //console.log(result);return;
            if (true != result.status) {
                //添加商品的界面关闭
                _thisDialog.close();
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                    // _thisDialog.remove(true);

                }, 'error');
            } else {
                if (typeof _thisDialog.callback == "function") {
                    _thisDialog.callback(this);
                }
                //_thisDialog.close();
                //_thisDialog.remove(true);
                //tableStore.load();
                //form.submit();
            }
//      if(typeof _thisDialog.callback == "function"){
//          _thisDialog.callback(this);
//      }
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
<div class="panel record_table" id="panel_deliverty_html"></div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>请输入</b>
            <input type="text"  placeholder="商品编码/商品条形码"  class="input" value="" id="goods_code"/>
            <b>差异款</b>
            <select name="difference_models" id="difference_models">
                <option value="">全部</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select>
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <?php if (0 == $response['data']['is_sure'] && $response['data']['is_cancel'] == 0) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>
                    <button type="button" class="button button-success" value="新增商品导入" id="btnimport"><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                    &nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" style ="float:right;"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php } ?>
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
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                        'editor' => empty($response['data']['jx_code']) ? "{xtype:'number'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '吊牌价',
                        'field' => 'sell_price',
                        'width' => '80',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
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
                        'title' => '实际出库数',
                        'field' => 'num',
                        'width' => '70',
                        'align' => '',
                        'editor' => $response['lof_status'] != 1 && $response['scan_type'] != 'scan_box' && $response['data']['is_cancel'] == 0 ? "{xtype:'number'}" : ''
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
                        'field' => 'num_differ',
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
                                'callback' => 'check_box_record',
                                'show_cond' => ($response['data']['is_cancel'] == 0) ? 'obj.is_sure == 0' : 'false',
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/StoreOutRecordDetailModel::get_by_page',
            'idField' => 'store_out_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_out']) ? false : true,
        ));
        ?>
    <?php endif; ?>
    <?php if ($response['lof_status'] == 1): ?>
        <?php
        render_control('DataTable', 'table_lof_list', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_name',
                        'width' => '100',
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
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec1_rename'],
                        'field' => 'spec1_code_name',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec2_rename'],
                        'field' => 'spec2_code_name',
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
                        'title' => '批次号',
                        'field' => 'lof_no',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '生产日期',
                        'field' => 'production_date',
                        'width' => '100',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '单价',
                        'field' => 'price1',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => '',
                        'editor' => empty($response['data']['jx_code']) ? "{xtype:'number'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际出库数',
                        'field' => 'num',
                        'width' => '70',
                        'align' => '',
                        'editor' => $response['scan_type'] != 'scan_box' ? "{xtype:'number'}" : ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '通知数',
                        'field' => 'init_num',
                        'width' => '70',
                        'align' => '',
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
                                'callback' => 'check_box_record_lof',
                                'show_cond' => 'obj.is_sure == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/StoreOutRecordDetailModel::get_by_page_lof',
            'idField' => 'store_out_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_out']) ? false : true,
        ));
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
                        'title' => '批发价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '吊牌价',
                        'field' => 'sell_price',
                        'width' => '80',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
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
                        'title' => '实际出库数',
                        'field' => 'num',
                        'width' => '70',
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
                        'field' => 'num_differ',
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
                                'callback' => 'check_box_record',
                                'show_cond' => ($response['data']['is_cancel'] == 0) ? 'obj.is_sure == 0' : 'false',
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/StoreOutRecordDetailModel::get_by_page',
            'idField' => 'store_out_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_store_out']) ? false : true,
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
                            'width' => '250',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['store_out_record_id'], 'module' => 'store_out_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var new_clodop_print = '<?php echo $response['new_clodop_print']; ?>';
    var barcode_template = '<?php echo $response['barcode_template']; ?>';
    var notice_record = '<?php echo $response['data']['relation_code'] ?>';
    //取消确认
    function  do_re_sure(_index, store_out_record_id) {
        url = '?app_act=wbm/store_out_record/do_sure';
        data = {id: store_out_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //确认
    function  do_sure(_index, store_out_record_id) {
        url = '?app_act=wbm/store_out_record/do_sure';
        data = {id: store_out_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
    //打印
    function  do_print(_index, store_out_record_id,print_type=1) {
        var check_url = "?app_act=wbm/store_out_record/check_is_print&app_fmt=json";
        $.post(check_url, {store_out_record_id: store_out_record_id, type: 'record',print_type:print_type}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_sellrecord(_index, store_out_record_id,print_type);
                }, 'question');
            } else if(ret.status == -2 && print_type == 2){
                BUI.Message.Alert(ret.message,'error');
            }else {
                btn_init_opt_print_sellrecord(_index, store_out_record_id,print_type);
            }
        }, 'json');
    }
    function  btn_init_opt_print_sellrecord(_index, store_out_record_id,print_type) {
        if (new_clodop_print == 1 || print_type == 2) {
            var code = '';
            if(print_type == 1){
                code = 'wbm_store_out_new';
            }else{
                code = 'wbm_store_out_clothing';
            }
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code="+code+"&record_ids=" + store_out_record_id, {
                title: "批发销货单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        } else {
            var u = '?app_act=sys/flash_print/do_print';
            u += '&template_id=15&model=wbm/StoreOutRecordModel&typ=default&record_ids=' + store_out_record_id;
            window.open(u);
        }
    }

    //差异校验
    function check_diff_num(_index, record_code, type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/store_out_record/check_diff_num',
            data: {record_code: record_code},
            success: function (ret) {
                var sta = ret.status;
                if (sta == 1) {
                    BUI.Message.Confirm('是否确认验收？ ', function () {
                        do_shift_out(_index, record_code, type);
                    }, 'question');
                    tableStore.load();
                } else if (sta == 2) {
                    BUI.Message.Confirm(ret.message, function () {
                        do_shift_out(_index, record_code, type);
                    }, 'question');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }

    //出库
    function do_shift_out(_index, record_code, accept_type) {
        //唯品会jit生成的销货单如果差异化出库 需要进行警告
        var url = '<?php echo get_app_url('wbm/store_out_record/do_shift_out_weipinhui_check'); ?>';
        var params = {record_code: record_code};
        $.post(url, params, function (data) {
            if (data.status == -1) {
                BUI.Message.Confirm(data.message, function () {
                    do_shift_out_action(record_code, accept_type);
                });
            } else if (data.status == 0) {
                BUI.Message.Alert(data.message, 'error');
            } else {
                do_shift_out_action(record_code, accept_type);
            }
        }, "json");
    }

    function do_shift_out_action(record_code, accept_type) {
        var url = (accept_type == 0) ? '?app_act=wbm/store_out_record/do_shift_out' : '?app_act=wbm/store_out_record/do_shift_out_by_record_date';
        data = {record_code: record_code};
        $.ajax({
            type: 'POST', dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, function () {
                        var handle_type = $('input[name="handle_type"]:checked').val();
                        if (handle_type == 'delete_short_inv' || handle_type == 'import_available_inv') {
                            $.ajax({
                                type: 'POST', dataType: 'json',
                                url: '?app_act=wbm/store_out_record/err_handle_type',
                                data: {handle_type: handle_type, record_code: record_code},
                                success: function (ret) {
                                    var type = ret.status == 1 ? 'success' : 'error';
                                    if (type == 'success') {
                                        BUI.Message.Alert(ret.message, type);
                                        location.reload();
                                    } else {
                                        BUI.Message.Alert(ret.message, type);
                                    }
                                }
                            });
                        }
                    }, type);
                }
            }
        });
    }




    $(".bui-stdmod-footer .button-primary").click(function () {
        if ($(".bui-stdmod-footer .button-primary").text() == '确定') {
            return;
        }

    });

    $("#scan_goods").click(function () {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=common/record_scan_box/get_scan_mode',
            data: {app_fmt: 'json', record_code: '<?php echo $response['data']['record_code']; ?>', record_type: 'wbm_store_out'},
            success: function (ret) {
                if (ret.data == 'scan') {
                    window.open("?app_act=common/record_scan/view_scan&dj_type=wbm_store_out&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
                    return;
                }
                if (ret.data == 'scan_box') {
                    window.open("?app_act=common/record_scan_box/view_scan&dj_type=wbm_store_out&type=add_goods&record_code=<?php echo $response['data']['record_code'] ?>");
                    return;
                }
                if (ret.data == 'select') {
                    _show_scan_mode();
                }
            }
        });
    });


    function _show_scan_mode() {
        BUI.use('bui/overlay', function (Overlay) {
            var html_str = '<div class="show_scan_mode"><a href ="?app_act=common/record_scan/view_scan&record_code=<?php echo $response['data']['record_code']; ?>&dj_type=wbm_store_out" target="_blank">普通扫描</a><a href ="?app_act=common/record_scan_box/view_scan&record_code=<?php echo $response['data']['record_code']; ?>&dj_type=wbm_store_out" target="_blank">装箱扫描</a></div>';
            var dialog = new Overlay.Dialog({
                title: '选择扫描模式',
                width: 500,
                height: 220,
                mask: false,
                buttons: [],
                bodyContent: html_str
            });
            dialog.show();
        });

        $(".show_scan_mode").click(function () {
            $(".bui-ext-close").click();
        });
    }
</script>
<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var is_lof = "<?PHP echo $response['lof_status'] ?>";
    $(function () {
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
            $("#showbatch").click();
        }
        $("#panel_deliverty_html .btnFormEdit").click(function () {
            var html = '';
            html += '<select id="country" name="country" onChange= "change(this,0);" data-rules="{required : true}">';
            html += '<option value ="">请选择国家</option>';
<?php foreach ($response['area']['country'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['country']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';
            html += '<select id="province" name="province"  onChange= "change(this,1);" data-rules="{required : true}">';
            html += '<option value ="">请选择省</option>';
<?php foreach ($response['area']['province'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['province']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';
            html += '<select id="city" name="city"  onChange= "change(this,2);" data-rules="{required : true}">';
            html += '<option value ="">请选择市</option>';
<?php foreach ($response['area']['city'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['city']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';
            html += '<select id="district" name="district"   data-rules="{required : true}">';
            html += '<option value ="">请选择区县</option>';
<?php foreach ($response['area']['district'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['district']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';
<?php if ($response['data']['is_cancel'] == 0): ?>
                $("#addr").html(html);
<?php endif; ?>
        });
        $("#panel_deliverty_html .btnFormCancel").click(function () {
            location.reload();
        });
    });
    function change(obj, level) {
        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        var parent_id = $(obj).val();
        areaChange(parent_id, level, url);
    }

    function check_box_record_lof(_index, row) {
        BUI.Message.Confirm('确认要删除吗？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=wbm/store_out_record/check_box_record_lof',
                data: {id: row.id},
                success: function (ret) {
                    //batchStore.load({'code_name': ''});
                    var type = (ret.status == 1) ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Show({
                            title: '商品已装箱，不建议删除',
                            msg: '若确认删除该商品需要重新装箱，是否确认删除？',
                            icon: 'question',
                            buttons: [
                                {
                                    text: '确认',
                                    elCls: 'button button-primary',
                                    handler: function () {
                                        do_delete_detail_lof(_index, row, 1);
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
                    } else if (ret.status == 2) {
                        do_delete_detail_lof(_index, row, 0);
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, 'question');
    }

    function do_delete_detail_lof(_index, row, is_box_task) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/store_out_record/do_delete_detail_lof',
            data: {id: row.id, is_box_task: is_box_task},
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
</script>
<script type="text/javascript">
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });
    //删除单据明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    function check_box_record(_index, row) {
        BUI.Message.Confirm('确认要删除吗？', function () {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=wbm/store_out_record/check_box_record',
                data: {store_out_record_detail_id: row.store_out_record_detail_id, pid: row.pid, sku: row.sku},
                success: function (ret) {
                    //batchStore.load({'code_name': ''});
                    var type = (ret.status == 1) ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Show({
                            title: '商品已装箱，不建议删除',
                            msg: '若确认删除该商品需要重新装箱，是否确认删除？',
                            icon: 'question',
                            buttons: [
                                {
                                    text: '确认',
                                    elCls: 'button button-primary',
                                    handler: function () {
                                        do_delete_detail(_index, row, 1);
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
                    } else if (ret.status == 2) {
                        do_delete_detail(_index, row, 0);
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }, 'question');
    }

    function do_delete_detail(_index, row, is_box_task) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=wbm/store_out_record/get_num_by_detail_id',
            data: {store_out_record_detail_id: row.store_out_record_detail_id},
            success: function (result) {
                if (result.data.num > 0) {
                    BUI.Message.Confirm('有出库数据，确认要删除吗？', function () {
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: '?app_act=wbm/store_out_record/do_delete_detail',
                            data: {store_out_record_detail_id: row.store_out_record_detail_id, pid: row.pid, sku: row.sku, is_box_task: is_box_task},
                            success: function (ret) {
                                var type = (ret.status == 1) ? 'success' : 'error';
                                if (type != 'success') {
                                    BUI.Message.Alert(ret.message, type);
                                } else {
                                    location.reload();
                                }
                            }
                        });
                    }, 'question');
                } else {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: '?app_act=wbm/store_out_record/do_delete_detail',
                        data: {store_out_record_detail_id: row.store_out_record_detail_id, pid: row.pid, sku: row.sku, is_box_task: is_box_task},
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
            }
        });
    }
    if (typeof table_listCellEditing != "undefined") {
        //列表区域,数量、价格修改回调操作
        table_listCellEditing.on('accept', function (record, editor) {
            if (record.record.num < 0 || record.record.price < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                table_listStore.load();
                return;
            }
            if (parseInt(record.record.num) > parseInt(record.record.enotice_num) && is_notice == 1) {
                //  if (record.record.num > record.record.enotice_num && is_notice == 1) {
                BUI.Message.Alert('实际出库数不能大于通知数', 'error');
                table_listStore.load();
                return;
            }
            var _record = record.record;
            $.post('?app_act=wbm/store_out_record/do_edit_detail',
                    {pid: _record.pid, record_code: _record.record_code, barcode: _record.barcode, sku: _record.sku, rebate: _record.rebate, num: _record.num, price: _record.price},
                    function (result) {
                        if (result.status < 1) {
                            BUI.Message.Alert(result.message, function () {
                                location.reload();
                            }, 'error');
                        } else {
                            var _res = result.res;
                            table_listStore.load();
                            $("#base_table tr").eq(2).find("td").eq(2).html(_res.num);
                            $("#base_table tr").eq(3).find("td").eq(0).html(_res.money);
                            logStore.load();
                        }
                    }, 'json');
        });
    }
    if (is_lof == 1) {
        if (typeof table_lof_listCellEditing != "undefined") {
            //列表区域,数量修改回调操作
            table_lof_listCellEditing.on('accept', function (record, editor) {
                if (record.record.num < 0 || record.record.price < 0) {
                    BUI.Message.Alert('不能为负数', 'error');
                    table_listStore.load();
                    return;
                }
                if (record.record.num > record.record.init_num && is_notice == 1) {
                    BUI.Message.Alert('实际出库数不能大于通知数', 'error');
                    table_listStore.load();
                    return;
                }
                var _record = record.record;
                $.post('?app_act=wbm/store_out_record/do_edit_detail_lof',
                        {pid: _record.pid, record_code: _record.record_code, barcode: _record.barcode, sku: _record.sku, rebate: _record.rebate, num: _record.num, price: _record.price, lof_no: _record.lof_no, production_date: _record.production_date},
                        function (result) {
                            var _res = result.res;
                            table_listStore.load();
                            $("#base_table tr").eq(2).find("td").eq(2).html(_res.num);
                            $("#base_table tr").eq(3).find("td").eq(0).html(_res.money);
                            logStore.load();
                        }, 'json');
            });
        }
    }

    //导出
    function report_excel() {
        var id = "<?php echo $response['data']['store_out_record_id']; ?>";
        var goods_code = $('#goods_code').val();
        url = "?app_act=wbm/store_out_record/export_csv_list&app_fmt=json&id=" + id + "&is_lof=" + is_lof + "&goods_code=" + goods_code;
        window.location.href = url;
    }

    $(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=wbm/store_out_record/import_goods&id=" + id;
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



    //打印快递单
    function do_print_express() {
        var express_code = "<?php echo $response['data']['express_code'] ?>";
        var express = "<?php echo $response['data']['express'] ?>";
        if ('' === express_code) {
            BUI.Message.Alert("请填写配送方式", 'warning');
            return;
        }
        if ('' === express) {
            BUI.Message.Alert("请填写快递单号", 'warning');
            return;
        }
        $.post('?app_act=wbm/store_out_record/check_template', {express_code: express_code}, function (ret) {
            if (ret.status === -1) {
                BUI.Message.Alert(ret.message, 'warning');
            } else {
                var id = "print_express";
                var record_code = "<?php echo $response['data']['record_code'] ?>";
                if (new_clodop_print == 1) {
                    new ESUI.PopWindow("?app_act=wbm/store_out_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&express_code=" + express_code + "&iframe_id=" + id + "&record_code=" + record_code + "&is_print_express=1" + "&frame_id=" + id, {
                        title: "快递单打印",
                        width: 500,
                        height: 220,
                        onBeforeClosed: function () {
                        },
                        onClosed: function () {
                        }
                    }).show();
                } else {
                    var url = "?app_act=wbm/store_out_record/print_express_view&express_code=" + express_code + "&iframe_id=" + id + "&record_code=" + record_code;
                    var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
                    iframe.attr('src', url);
                }
            }
        }, 'json');
    }

    //打印汇总单
    function print_aggr_box() {
        var check_url = "?app_act=wbm/store_out_record/check_is_print&app_fmt=json";
        var store_out_record_id = '<?php echo $response['data']['store_out_record_id']; ?>';
        $.post(check_url, {store_out_record_id: store_out_record_id, type: 'box'}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_aggr_box();
                }, 'question');
            } else {
                btn_init_opt_print_aggr_box();
            }
        }, 'json');
    }
    function btn_init_opt_print_aggr_box() {
        var id = "print_aggr_box";
        var record_code = "<?php echo $response['data']['record_code'] ?>";
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=wbm/store_out_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&record_code=" + record_code + "&frame_id=" + id + "&print_templates_code=aggr_box&type=hz", {
                title: "汇总单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            var url = "?app_act=tprint/tprint/do_print&print_templates_code=aggr_box&&record_code=" + record_code;
            var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', url);
        }
    }
    var i = 0;
    function  do_print_barcode(_index, store_out_record_id) {
        if (notice_record == '') {
            BUI.Message.Alert("并未关联通知单", 'warning');
            return;
        }
        var iframe_id = 'print_express' + i;
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=prm/goods_barcode/print_barcode_clodop&new_clodop_print=" + new_clodop_print + "&record_ids=" + store_out_record_id + "&frame_id=" + iframe_id + "&list_type=3", {
                title: "条码打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show()
        } else {
            if (barcode_template == 0) {
                var u = '?app_act=sys/flash_print/do_print_barcode';
                u += '&template_id=11&model=prm/GoodsBarcodeModel&typ=default&store_out_record_id=' + store_out_record_id;
                window.open(u);
            } else {
                var url = "?app_act=sys/record_templates/print_barcode&iframe_id=" + iframe_id + "&record_ids=" + store_out_record_id + "&list_type=3";
                var iframe = $('<iframe id="' + iframe_id + ' width="0" height="0"></iframe>').appendTo('body');
                iframe.attr('src', url);
                i++;
            }
        }
    }
    function print_goods() {
        var id = 'wbm_store_out_record_goods';
        var params = {record_code: record_code};
        //如果开启了clodop云打印参数，不处理新旧发货单模板参数
        $.post("?app_act=wbm/store_out_record/get_record_goods_ids", params, function (data) {
            if (new_clodop_print == 1) {
                new ESUI.PopWindow("?app_act=wbm/store_out_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=wbm_store_out_record_goods&record_ids=" + data.id.toString() + "&sku=" + data.sku.toString() + "&frame_id=" + id + "&type=goods", {
                    title: "商品打印",
                    width: 500,
                    height: 220,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                    }
                }).show()
            } else {
                var url = "?app_act=tprint/tprint/do_print&print_templates_code=wbm_store_out_record_goods" + "&record_ids=" + data.id.toString() + '&sku=' + data.sku.toString();
                var iframe = $('<iframe id="' + id + ' width="0" height="0"></iframe>').appendTo('body');
                iframe.attr('src', url);
            }

        }, "json");
    }

</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>