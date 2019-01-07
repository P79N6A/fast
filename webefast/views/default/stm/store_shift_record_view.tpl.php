<style>
    .panel-body{ padding:0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:10%; text-align:center;}
    .table td{ width:20%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px; border: 1px solid #ddd;}
    .bui-grid-header{ border-top:none;}
    p{ margin:0;}
    b{ vertical-align:middle;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
$title = '商品移仓单';
$links = array();
if ($response['is_entity'] == 1) {
    $links = array();
    $title = '调拨单详情';
} else {
    $links[] = array('url' => 'stm/store_shift_record/do_list', 'is_pop' => false, 'target' => '_self', 'title' => $title);
}
render_control('PageHead', 'head1', array('title' => $title,
    'links' => $links,
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if ($response['power']['confirm'] && 0 == $response['data']['is_sure'] && $response['login_type'] == 0) { ?>
            <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 确认</a>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if ($response['power']['confirm'] && 1 == $response['data']['is_sure'] && 1 != $response['data']['is_shift_out'] && $response['login_type'] == 0) { ?>
            <a class="button button-primary" href="javascript:do_re_sure(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 取消确认</a>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if ($response['power']['output'] && 1 == $response['data']['is_sure'] && 1 != $response['data']['is_shift_out'] && in_array($response['data']['shift_out_store_code'], $response['purview_store']) && ($response['is_wms_out'] == 0 || $response['is_same_outside_code'] == 1) && $response['login_type'] == 0) { ?>
            <a class="button button-primary" href="javascript:do_shift_out(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 强制出库</a>
            <a class="button button-primary" href="javascript:do_scan_shift_out(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 扫描出库</a>
        <?php } ?>
    </li>
    <div class="front_close">&lt;</div>
    <li class="li_btns">
        <?php if ($response['power']['force_input'] && 1 == $response['data']['is_shift_out'] && 1 != $response['data']['is_shift_in'] && in_array($response['data']['shift_in_store_code'], $response['purview_store']) && ($response['is_wms_in'] == 0 || $response['is_same_outside_code'] == 1)) { ?>
            <a class="button button-primary" href="javascript:do_shift_in(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 强制入库</a>

        <?php } ?>

    </li>
    <li class="li_btns">
        <?php if ($response['power']['scan_input'] && 1 == $response['data']['is_shift_out'] && 1 != $response['data']['is_shift_in'] && in_array($response['data']['shift_in_store_code'], $response['purview_store']) && ($response['is_wms_in'] == 0 || $response['is_same_outside_code'] == 1)) { ?><button type="button" class="button button-primary" value="验收" id="btnRecordCheckin"><i class="icon-ok icon-white"></i> 扫描入库</button><?php } ?>

    </li>
    <li class="li_byns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('stm/store_shift_record/export_list')) { ?>
            <a class="button button-primary" href="javascript:export_excel()">导出</a>
        <?php } ?>
    </li>
    <li class="li_btns">
        <a class="button button-primary" href="javascript:shift_print(this, '<?php echo $response['data']['shift_record_id']; ?>')"> 打印</a>
    </li>
  <!--  <li class="li_btns"> <button type="button" class="button button-primary" value="返回" onclick="javascript:history.go(-1);"><i class="icon-backward icon-white"></i> 返回</button></li>-->
    <div class="front_close">&lt;</div>
</ul>
<?php echo load_js("pur.js", true); ?>
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
    var id = "<?php echo $response['data']['shift_record_id']; ?>";
    var store_code = "<?php echo $response['data']['shift_out_store_code']; ?>";
    var shift_in_store_code = "<?php echo $response['data']['shift_in_store_code']; ?>";
    var shift_out_store_name;
    var shift_in_store_name;
    var is_shift_out_edit = 'select';
    var is_shift_in_edit = 'select';
<?php if (!in_array($response['data']['shift_out_store_code'], $response['purview_store'])): ?>
        shift_out_store_name = "<?php echo $response['data']['shift_out_store_name']; ?>";
        is_shift_out_edit = 'label';
<?php endif; ?>
<?php if (!in_array($response['data']['shift_in_store_code'], $response['purview_store'])): ?>
        shift_in_store_name = "<?php echo $response['data']['shift_in_store_name']; ?>";
        is_shift_in_edit = 'label';
<?php endif; ?>
<?php if ($response['is_entity'] == 1): ?>
        shift_in_store_name = "<?php echo $response['data']['shift_in_store_name']; ?>";
        shift_out_store_name = "<?php echo $response['data']['shift_out_store_name']; ?>";
        is_shift_in_edit = 'label';
        is_shift_out_edit = 'label';
<?php endif; ?>

    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_sure'] || $response['login_type'] > 0) { ?>
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
            "name": "shift_out_store_code",
            "title": "移出仓库",
            "value": "<?php echo $response['data']['shift_out_store_code'] ?>",
            "type": is_shift_out_edit,
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>,
        },
        {
            "name": "record_time",
            "title": "业务日期（出库）",
            "value": "<?php echo $response['data']['record_time'] ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "is_shift_in_time",
            "title": "业务日期（入库）",
            "value": "<?php echo $response['data']['shift_in_time']; ?>",
            "type": "date",
//            "edit": true
        },
        {
            "name": "shift_in_store_code",
            "title": "移入仓库",
            "value": "<?php echo $response['data']['shift_in_store_code'] ?>",
            "type": is_shift_in_edit,
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>,
        },
        {
            "name": "is_shift_out_time",
            "title": "出库日期",
            "value": "<?php echo $response['data']['is_shift_out_time']; ?>",
            "type": "date",
            "edit": true
        },
        {
            "name": "is_shift_in_time",
            "title": "入库日期",
            "value": "<?php echo $response['data']['is_shift_in_time']; ?>",
            "type": "date",
            "edit": true
        },
        {
            "title": "移出数量",
            "value": "<?php echo $response['data']['out_num'] ?>",
        },
        {
            "title": "移出金额",
            //"value": "<?php //echo number_format($response['data']['out_money'], 2);  ?>",
            "value": "<?php echo $response['data']['out_money']; ?>",
        },
        {
            "title": "移入数量",
            "value": "<?php echo $response['data']['in_num'] ?>",
        },
        {
            "title": "移入金额",
            "value": "<?php echo $response['data']['in_money']; ?>",
        },
        {
            "name": "rebate",
            "title": "折扣",
            "value": "<?php echo $response['data']['rebate'] ?>",
            "type": "input",
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
            "edit_url": "?app_act=stm/store_shift_record/do_edit"
        });
        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'stm_store_shift', record_id: id}
                });
            } else {
                get_goods_inv_panel({
                    "id": "btnSelectGoods",
                    "callback": addgoods,
                    'param': {'store_code': '<?php echo $response['data']['shift_out_store_code'] ?>', 'lof_status': <?php echo $response['lof_status'] ?>, 'status': 1, 'is_entity':<?php echo $response['is_entity'] ?>}
                });
            }
        }

        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'code_name': $('#goods_code').val(), 'difference_sku': $('#difference_sku').val()});
            table_lof_listStore.load({'code_name': $('#goods_code').val(), 'difference_sku': $('#difference_sku').val()});
            /*
             tableStore.load({'code_name': $('#goods_code').val()},function(data){
             if(type == 1){
             shownobatch();
             }
             if(type == 2){
             showbatch();
             }
             });
             */
        });

        $('#shift_out_store_code').html(shift_out_store_name);
        $('#shift_in_store_code').html(shift_in_store_name);
    })

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num > 0) {
                    if (parseInt(value.num) > parseInt(value.available_mum)) {
                        value.num = value.available_mum;
                    }
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
        $.post('?app_act=stm/store_shift_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
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
            <b>请输入</b>
            <input type="text" class="input" placeholder="商品编码/商品条形码" value="" id="goods_code"/>
            <b>差异款</b>
            <select name="difference_sku" id="difference_sku">
                <option value="">全部</option>
                <option value="1">是</option>
                <option value="0">否</option>
            </select> 
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <!--  <button type="button" class="button button-info" value="重置" id="btnSearchReset"><i class="icon-repeat icon-white"></i> 重置</button>-->
            <?php if ($response['lof_status'] == 1): ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php endif; ?>
            <div style ="float:right;">
                <?php if (0 == $response['data']['is_sure'] && $response['login_type'] == 0) { ?>
                    <?php if ($response['lof_status'] == 0) { ?>
                        <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>
                    <?php } ?>
                <?php } ?>
                <?php if (1 == $response['data']['is_sure']) : ?>
                    <?php //if (0 == $response['data']['is_shift_out']): ?>
                                 <!--<button type="button" class="button button-success" value="" id="btnimport" ><i class="icon-plus-sign icon-white"></i>导入移入商品</button>-->
                    <?php //endif; ?>
                <?php elseif ($response['login_type'] == 0): ?>
                    <button type="button" class="button button-success" value="" id="btnimport" ><i class="icon-plus-sign icon-white"></i>导入移出商品</button>
                <?php endif; ?>
                <?php if (0 == $response['data']['is_sure'] && $response['login_type'] == 0) : ?>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"  ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                <?php endif; ?>
            </div>
            <input id="lof_status" name="lof_status" type="hidden" value="<?php echo $response['lof_status'] ?>" />
            <!--
            <div class="span12">
                <b>扫描条码加入单据 </b>
                <input type="text" class="input" value=""/>
            </div>
            -->
        </div>
        <?php
        // 'dataset' => '',
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
                        'title' => '采购价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '移出数量',
                        'field' => 'out_num',
                        'width' => '80',
                        'align' => '',
                    // 'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '移出金额',
                        'field' => 'out_money',
                        'width' => '120',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '移入数量',
                        'field' => 'in_num',
                        'width' => '80',
                        'align' => '',
                    // 'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '移入金额',
                        'field' => 'in_money',
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
                                'show_cond' => 'obj.is_sure == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'stm/StoreShiftRecordDetailModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'shift_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                //'RowNumber'=>true,
                //'CheckSelection'=>true,
                // 'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
        ));
        ?>
        <?php if ($response['lof_status'] == 1): ?>
            <?php
            // 'dataset' => '',
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
                            'title' => '采购价',
                            'field' => 'price',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '批次',
                            'field' => 'lof_no',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '生产日期',
                            'field' => 'production_date',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '移出数量',
                            'field' => 'shift_out_num',
                            'width' => '80',
                            'align' => '',
                        //'editor'=> "{xtype:'number'}"
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '移出金额',
                            'field' => 'shift_out_money',
                            'width' => '120',
                            'align' => '',
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '移入数量',
                            'field' => 'shift_in_num',
                            'width' => '80',
                            'align' => '',
                        //'editor'=> "{xtype:'number'}"
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '移入金额',
                            'field' => 'shift_in_money',
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
                                    'callback' => 'do_delete_detail_lof',
                                    'show_cond' => 'obj.is_sure == 0'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'stm/StoreShiftRecordDetailModel::get_by_page_lof',
                //'queryBy' => 'searchForm',
                'idField' => 'shift_record_detail_id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                    //'RowNumber'=>true,
                    //'CheckSelection'=>true,
                    //'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
            ));
            ?>
        <?php endif; ?>
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
                            'title' => '验收状态',
                            'field' => 'finish_status',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '600',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['shift_record_id'], 'module' => 'store_shift_record')),
            ));
            ?>
        </div>
    </div>
</div>
<script type="text/javascript">
<?php if ($response['lof_status'] == 1): ?>
        jQuery(function () {
            jQuery("#showbatch").click(function () {
                $('#table_list_datatable').hide();
                $('#table_lof_list_datatable').show();
                jQuery('#showbatch').removeClass("curr");
                jQuery('#shownobatch').removeClass("curr");
            });
            jQuery("#shownobatch").click(function () {
                $('#table_lof_list_datatable').hide();
                $('#table_list_datatable').show();
                jQuery('#showbatch').addClass("curr");
                jQuery('#shownobatch').addClass("curr");
            });
            jQuery("#showbatch").click();
        });
        function do_delete_detail_lof(_index, row) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=stm/store_shift_record/do_delete_detail_lof',
                data: {id: row.id, pid: row.pid},
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
<?php endif; ?>
    //删除单据明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=stm/store_shift_record/do_delete_detail',
            data: {detail_id: row.shift_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                //  tableStore.load({'code_name': ''});
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
    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=stm/store_shift_record/importGoods&id=" + id;
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
    /*
     if(typeof tableCellEditing != "undefined"){
     //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
     if(record.record.lof_no== "undefined"){
     tableCellEditing.on('accept', function (record, editor) {
     $.post('?app_act=stm/stock_adjust_record/do_edit_detail',
     {pid: record.record.pid, num: record.record.num, sku: record.record.sku, sell_price: record.record.price},
     function (result) {
     window.location.reload();
     }, 'json');
     });
     }else{
     tableCellEditing.on('accept', function (record, editor) {
     $.post('?app_act=stm/stock_adjust_record/do_edit_detail_lof',
     {pid: record.record.pid, num: record.record.num, sku: record.record.sku,lof_no: record.record.lof_no,production_date: record.record.production_date, sell_price: record.record.price},
     function (result) {
     window.location.reload();
     }, 'json');
     });

     }
     }
     */
</script>

<script type="text/javascript">
    $("#table_datatable").show();
    $("#table1_datatable").hide();

    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

    //验收按钮++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $('#btnRecordCheckin').on('click', function () {
        var dj_type = 'shift_in';
        url = "?app_act=stm/store_shift_record/shift_in&pid=" + id + "&in_store=" + shift_in_store_code + "&record_code=" + record_code + "&dj_type=" + dj_type;
        new ESUI.PopWindow(url, {
            title: "扫描入库",
            width: 900,
            height: 550,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                location.reload();
            }
        }).show()
        /*
         $.post('?app_act=stm/stock_adjust_record/do_checkin&app_fmt=json',{id:id},function(result){
         if(result.status==1){
         location.reload();
         }else{
         BUI.Message.Alert(result.message, function(){
         location.reload();
         },'error');
         }
         },'json');
         */
    });
</script>
<script type="text/javascript">
    //是否开启clodop打印: 1开启
    var new_clodop_print = '<?php echo $response['new_clodop_print']; ?>';
    //取消确认
    function  do_re_sure(_index, shift_record_id) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: shift_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //确认
    function  do_sure(_index, shift_record_id) {
        url = '?app_act=stm/store_shift_record/do_sure';
        data = {id: shift_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
    function  do_shift_out(_index, shift_record_id) {
        url = '?app_act=stm/store_shift_record/do_shift_out';
        data = {id: shift_record_id};
        _do_operate(url, data, 'flush');
    }
    //扫描出库
    function do_scan_shift_out() {
        var dj_type = 'shift_out';
        url = "?app_act=stm/store_shift_record/shift_out&pid=" + id + "&in_store=" + shift_in_store_code + "&record_code=" + record_code + "&dj_type=" + dj_type;
        new ESUI.PopWindow(url, {
            title: "扫描出库",
            width: 900,
            height: 550,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                location.reload();
            }
        }).show()
    }
    //强制入库
    function  do_shift_in(_index, shift_record_id) {
        url = '?app_act=stm/store_shift_record/do_qz_shift_in';
        data = {id: shift_record_id};
        _do_operate(url, data, 'flush');
    }

    //打印
    function  shift_print(_index, shift_record_id) {
        var check_url = "?app_act=stm/store_shift_record/check_is_print&app_fmt=json";
        $.post(check_url, {shift_record_id: shift_record_id}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Confirm(ret.message, function () {
                    btn_init_opt_print_sellrecord(_index, shift_record_id);
                }, 'question');
            } else {
                btn_init_opt_print_sellrecord(_index, shift_record_id);
            }
        }, 'json');
    }
    function  btn_init_opt_print_sellrecord(_index, shift_record_id) {
        if (new_clodop_print == 1) {
            new ESUI.PopWindow("?app_act=oms/deliver_record/choose_clodop_printer&new_clodop_print=" + new_clodop_print + "&print_templates_code=store_shift&record_ids=" + shift_record_id, {
                title: "移仓单打印",
                width: 500,
                height: 220,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                }
            }).show();
        } else {
            var u = '?app_act=tprint/tprint/do_print&print_templates_code=store_shift&record_ids=' + shift_record_id;
            // $("#print_iframe").attr('src', u);

            var iframe = $('<iframe id="" width="0" height="0"></iframe>').appendTo('body');
            iframe.attr('src', u);
        }
    }
    //移仓单详情打印
    function export_excel() {
        var id = "<?php echo $response['data']['shift_record_id']; ?>";
        url = "?app_act=stm/store_shift_record/export_csv_list&app_fmt=json&id=" + id;
        window.location.href = url;
    }

    //扫描商品
    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan_common/view_scan&dj_type=shift_out&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    })
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>