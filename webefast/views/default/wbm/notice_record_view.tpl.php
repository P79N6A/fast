<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:2px 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '批发销货通知单',
    'links' => array(
        array('url' => 'wbm/notice_record/do_list', 'target' => '_self', 'title' => '批发销货通知单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <button type="button" class="button button-info" style="background-color: #1695ca;"  onclick="export_excel()"  value="导出" id="btn-csv">导出</button>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_sure')) { ?>
            <?php if (0 == $response['data']['is_sure']) { ?>
                <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['notice_record_id']; ?>')"> 确认</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:do_print(this, '<?php echo $response['data']['notice_record_id']; ?>')">打印</a>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_sure')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_execute'] == 0) { ?>
                <a class="button button-primary" href="javascript:do_re_sure(this, '<?php echo $response['data']['notice_record_id']; ?>')">取消确认</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_execute')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_wms'] != 1) { ?>
                <a class="button button-primary" href="javascript:do_execute_before(this, '<?php echo $response['data']['notice_record_id']; ?>','<?php echo $response['data']['record_code']; ?>')">生成销货单</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('wbm/notice_record/do_stop')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_wms'] != 1) { ?>
                <a class="button button-primary" href="javascript:do_stop(this, '<?php echo $response['data']['notice_record_id']; ?>')">终止</a>
            <?php } ?>
        <?php } ?>
    </li>
    <div class="front_close">&lt;</div>
</ul>
<script  type="text/javascript">
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['notice_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_sure']) { ?>
        is_edit = false;
<?php } ?>
    var data = [
        {
            "name": "record_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input"
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
            "title": "下单时间",
            "value": "<?php echo $response['data']['order_time'] ?>"
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
            "name": "store_code",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>
        },
        {
            "name": "rebate",
            "title": "折扣",
            "value": "<?php echo $response['data']['rebate'] ?>"
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
            "data":<?php echo $response['selection']['record_type'] ?>,
            "edit": true
        },
        {
            "name": "num",
            "title": "总数量",
            "value": "<?php echo $response['data']['num'] ?>"
        },
        {
            "name": "money",
            "title": "总金额",
            "value": "<?php echo $response['data']['money'] ?>"
        },
        {
            "name": "num",
            "title": "完成数量",
            "value": "<?php echo $response['data']['finish_num'] ?>"
        },
        {
            "name": "num",
            "title": "差异总数量",
            "value": "<?php echo $response['data']['diff_num'] ?>"
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true
        },
        {
            "title": "确认",
            "value": "<?php echo $response['data']['is_check_src'] ?>"
        },
        {
            "title": "完成",
            "value": "<?php echo $response['data']['is_finish_src'] ?>"
        }
    ];

    var delivery_data = [
        {
            "name": "name",
            "title": "联系人",
            "value": "<?php echo $response['data']['name'] ?>",
            "type": "input",
            "edit": is_edit
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
            "edit": is_edit
        },
        {
            "name": "tel",
            "title": "联系电话",
            "value": "<?php echo $response['data']['tel'] ?>",
            "type": "input",
            "edit": is_edit
        },
        {
            "title": "",
            "value": "",
        },
        {
            "title": "",
            "value": "",
        }
    ];
    $(function () {
        tools();

        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/notice_record/do_edit"
        });
        var delivery_record = new record_table();
        delivery_record.init({
            "id": "panel_deliverty_html",
            "title": "配送信息",
            "data": delivery_data,
            "is_edit": is_edit,
            "edit_url": "?app_act=wbm/notice_record/do_edit"
        });
        $("#showbatch").bind("click", showbatch);
        $("#shownobatch").bind("click", shownobatch);

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'wbm_notice', record_id: id}
                });
            } else {
                get_goods_inv_panel({
                    "id": "btnSelectGoods",
                    "callback": addgoods,
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'status': 1, 'model': 'notice_record'}
                });
            }
            import_goods_recode('btnimport', id, 0);
        }
        $('#btnSearchGoods').on('click', function () {
            if (<?php echo $response['lof_status'] ?> == 1) {
                table_lof_listStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
                tableStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
            } else {
                tableStore.load({'code_name': $('#goods_code').val(), 'difference_models': $('#difference_models').val()});
            }
        });
    });

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num > 0) {
//                    if (parseInt(value.num) > parseInt(value.available_mum)) {
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
        $.post('?app_act=wbm/notice_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {
                    //_thisDialog.close();
                    // _thisDialog.remove(true);
                }, 'error');
            }
//            else {
//                //_thisDialog.close();
//                //_thisDialog.remove(true);
//                //tableStore.load();
//                //form.submit();
//            }
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
            <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>
            <b>差异款</b>
            <select name="difference_models" id="difference_models">
                <option value="">全部</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>
            <?php if (0 == $response['data']['is_sure']) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>&nbsp;
                    <button type="button" class="button button-success" value="商品导入" id="btnimport"  ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                    &nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php if ($response['lof_status'] != 1): ?>
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
                        'width' => '130',
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
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '折扣',
                        'field' => 'rebate',
                        'width' => '80',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
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
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
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
                        'title' => '总金额',
                        'field' => 'money',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成数量',
                        'field' => 'finish_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数量',
                        'field' => 'diff_num',
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
                                'show_cond' => 'obj.is_sure == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/NoticeRecordDetailModel::get_by_page',
            'idField' => 'notice_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_sure']) ? false : true,
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
                        'width' => '130',
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
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '折扣',
                        'field' => 'rebate',
                        'width' => '80',
                        'align' => '',
                    // 'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
                        'field' => 'price1',
                        'width' => '80',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '数量',
                        'field' => 'init_num',
                        'width' => '120',
                        'align' => '',
                        'editor' => "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成数量',
                        'field' => 'fill_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数量',
                        'field' => 'diff_num',
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
                                'show_cond' => 'obj.is_sure == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/NoticeRecordDetailModel::get_by_page_lof',
            'idField' => 'notice_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_sure']) ? false : true,
        ));
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
                        'width' => '130',
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
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '折扣',
                        'field' => 'rebate',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '批发单价',
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
                        'type' => 'text',
                        'show' => 1,
                        'title' => '完成数量',
                        'field' => 'finish_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '差异数量',
                        'field' => 'diff_num',
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
                                'show_cond' => 'obj.is_sure == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'wbm/NoticeRecordDetailModel::get_by_page',
            'idField' => 'notice_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
            'CellEditing' => (1 == $response['data']['is_sure']) ? false : true,
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
                            'width' => '300',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['notice_record_id'], 'module' => 'wbm_notice_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var new_clodop_print = "<?php echo $response['new_clodop_print']; ?>";
    //取消确认
    function  do_re_sure(_index, notice_record_id) {
        url = '?app_act=wbm/notice_record/do_sure';
        data = {id: notice_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //确认
    function  do_sure(_index, notice_record_id) {
        url = '?app_act=wbm/notice_record/do_sure';
        data = {id: notice_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }

    //打印
    function  do_print(_index, notice_record_id) {
        var check_url = "?app_act=wbm/notice_record/check_is_print_record&app_fmt=json";
        $.post(check_url, {notice_record_id: notice_record_id}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_sellrecord(_index, notice_record_id);
                }, 'question');
            } else {
                btn_init_opt_print_sellrecord(_index, notice_record_id);
            }
        }, 'json');
    }

    function  btn_init_opt_print_sellrecord(_index, notice_record_id) {
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=wbm_notice_store_out_new&record_ids=" + notice_record_id, {
                title: "销货通知单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        } else {
            var u = '?app_act=sys/flash_print/do_print';
            u += '&template_code=wbm_notice_store_out&model=wbm/NoticeRecordModel&typ=default&record_ids=' + notice_record_id;
            window.open(u);
        }
    }


//终止
    function do_stop(_index, notice_record_id) {
        url = '?app_act=wbm/notice_record/do_stop';
        data = {id: notice_record_id};
        _do_operate(url, data, 'flush');
    }
//生成销货单
    function do_execute_before(_index, notice_record_id, record_code) {
        //判断是否唯品会通知单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/notice_record/weipinhui_notice_record'); ?>',
            data: {notice_record_no: record_code},
            success: function (ret) {
                if (ret.status == 1) {
                    var tips = '此批发销货通知单是唯品会jit业务生成，创建时已自动创建对应销货单（' + ret.data.store_out_record_no + '），无需重新生成销货单！';
                    BUI.Message.Alert(tips, 'error');
                } else {
                    do_execute(_index, notice_record_id);
                }
            }
        });
    }
    function do_execute(_index, notice_record_id) {
        //判断是否有未入库销货单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('wbm/notice_record/out_relation'); ?>',
            data: {id: notice_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act==wbm/notice_record/execute&notice_record_id=" + notice_record_id.toString();
                    _do_execute(url, 'table');
                } else {
                    if (ret.status == '-1') {
                        BUI.Message.Confirm('存在未出库的批发销货单，是否继续？', function () {
                            url = "?app_act==wbm/notice_record/execute&notice_record_id=" + notice_record_id.toString();
                            _do_execute(url, 'table');
                        });
                    }
                    // BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

<?php if ($response['lof_status'] == 1): ?>
        $(function () {
            $("#showbatch").click(function () {
                $('#table_datatable').hide();
                $('#table_lof_list_datatable').show();
                $('#showbatch').removeClass("curr");
                $('#shownobatch').removeClass("curr");
            });
            $("#shownobatch").click(function () {
                $('#table_lof_list_datatable').hide();
                $('#table_datatable').show();
                $('#showbatch').addClass("curr");
                $('#shownobatch').addClass("curr");
            });
            $("#showbatch").click();
        });
        function do_delete_detail_lof(_index, row) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=wbm/notice_record/do_delete_detail_lof',
                data: {id: row.id},
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
<?php endif; ?>
    $(function () {
        $("#panel_deliverty_html .btnFormEdit").click(function () {
            var edit_addr = '';
            edit_addr += '<select id="country" name="country" onChange= "change(this,0);" data-rules="{required : true}">';
            edit_addr += '<option value ="">请选择国家</option>';
<?php foreach ($response['area']['country'] as $k => $v) { ?>
                edit_addr += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['country']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            edit_addr += '</select>';
            edit_addr += '<select id="province" name="province"  onChange= "change(this,1);" data-rules="{required : true}">';
            edit_addr += '<option value ="">请选择省</option>';
<?php foreach ($response['area']['province'] as $k => $v) { ?>
                edit_addr += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['province']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            edit_addr += '</select>';
            edit_addr += '<select id="city" name="city"  onChange= "change(this,2);" data-rules="{required : true}">';
            edit_addr += '<option value ="">请选择市</option>';
<?php foreach ($response['area']['city'] as $k => $v) { ?>
                edit_addr += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['city']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            edit_addr += '</select>';
            edit_addr += '<select id="district" name="district"   data-rules="{required : true}">';
            edit_addr += '<option value ="">请选择区县</option>';
<?php foreach ($response['area']['district'] as $k => $v) { ?>
                edit_addr += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['district']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            edit_addr += '</select>';
            $("#addr").html(edit_addr);
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
            url: '?app_act=wbm/notice_record/do_delete_detail',
            data: {notice_record_detail_id: row.notice_record_detail_id, pid: row.pid, sku: row.sku},
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
    if (typeof tableCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        tableCellEditing.on('accept', function (record, editor) {
            if (parseInt(record.record.price1) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
            $.post('?app_act=wbm/notice_record/do_edit_detail',
                    {pid: record.record.pid, num: record.record.num, sku: record.record.sku, rebate: record.record.rebate, price: record.record.price},
                    function (result) {
                        var _res = result.res;
                        tableStore.load();
                        $("#base_table tr").eq(2).find("td").eq(2).html(_res.num);
                        $("#base_table tr").eq(3).find("td").eq(0).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }
    if (typeof table_lof_listCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_lof_listCellEditing.on('accept', function (record, editor) {
            if (parseInt(record.record.price1) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                table_lof_listStore.load();
                return;
            }
            $.post('?app_act=wbm/notice_record/do_edit_detail',
                    {pid: record.record.pid, num: record.record.init_num, sku: record.record.sku, rebate: record.record.rebate, price: record.record.price, lof_no:record.record.lof_no},
                    function (result) {
                        var _res = result.res;
                        table_lof_listStore.load();
                        $("#base_table tr").eq(2).find("td").eq(2).html(_res.num);
                        $("#base_table tr").eq(3).find("td").eq(0).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }


    //扫描
    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan/view_scan&dj_type=wbm_notice&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    });
    //导出
    function export_excel() {
        var param = tableStore.get('params');
        param.page_size = "<?php echo $response['data']['num'] ?>";
        var lof_status = "<?php echo $response['lof_status']; ?>";
        var goods_code_barcode = $.trim($('#goods_code').val());
        var difference_models = $('#difference_models option:selected').val();
        var notice_record_id = "<?php echo $response['notice_record_id']; ?>";
        var param_url = '';
        for (var key in param) {
            param_url += "&" + key + "=" + param[key];
        }
        param_url = param_url + "&notice_record_id=" + notice_record_id + "&lof_status=" + lof_status + "&goods_code_barcode=" + goods_code_barcode + "&difference_models=" + difference_models + "&app_fmt=json";
        url = "?app_act=wbm/notice_record/export_csv_list" + param_url;
        window.location.href = url;
    }

</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>