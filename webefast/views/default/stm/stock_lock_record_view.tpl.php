<style>
    .panel-body { padding: 0;}
    .table{ margin-bottom:0;}
    .table tr{ padding:5px 0;}
    .table th, .table td{ border:1px solid #dddddd; padding:3px 0; vertical-align:middle;}
    .table th{ width:8.3%; text-align:center;}
    .table td{ width:23%; padding:0 1%;}
    .row{ margin-left:0; padding: 2px 8px;border: 1px solid #ddd;}
    .bui-grid-header {border-top: none;}
    p {margin: 0;}
    b {vertical-align: middle;}

    /*---选择框-begin--*/
    .check_custom{visibility: hidden;}
    .check_custom + label{
        cursor: pointer;
        margin: 3px 8px 4px -12px;
        background-color: white;
        border-radius: 5px;
        border:1px solid #d3d3d3;
        width:20px;
        height:20px;
        display: inline-block;
        text-align: center;
        vertical-align: middle;
        line-height: 20px;
    }
    .check_custom:checked + label{background-color: #eee;}
    .check_custom:checked + label:after{content:"\2714";}
    [type="radio"] + label{border-radius: 10px;}
    /*---选择框-end--*/
    .custom-dialog .bui-stdmod-header,.custom-dialog .bui-stdmod-footer{display: none;}
    .custom-dialog .row{border: none}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
$url = "stm/stock_lock_record/do_list";
render_control('PageHead', 'head1', array('title' => '库存锁定单详情',
    'links' => array(
        array('url' => $url, 'is_pop' => false, 'title' => '库存锁定单列表')
    ),
    'ref_table' => 'table'
));
?>
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
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['stock_lock_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var lof_status = "<?php echo $response['lof_status']; ?>";
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
    var data = [
        {
            "name": "record_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['record_code'] ?>",
            "type": "input"
        },
        {
            "name": "is_add_time",
            "title": "下单时间",
            "value": "<?php echo $response['data']['is_add_time'] ?>",
            "type": "input",
        },
        {
            "name": "status",
            "title": "单据状态",
            "value": "<?php echo $response['data']['status'] ?>",
            "type": "input",
            "edit": false
        },
        {
            "name": "store_code_name",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code_name'] ?>",
            "type": "input",
            "edit": false,
        },
        {
            "name": "lock_num",
            "title": "计划锁定数量",
            "value": "<?php echo $response['data']['lock_num'] ?>"
        },
        {
            "name": "release_num",
            "title": "已释放数量",
            "value": "<?php echo $response['data']['release_num'] ?>"
        },
        {
            "name": "available_num",
            "title": "实际锁定数量",
            "value": "<?php echo $response['data']['available_num'] ?>"
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "name": "lock_obj_type",
            "title": "锁定对象",
            "value": "<?php echo $response['data']['lock_obj_type'] ?>",
            "type": "input",
            "edit": false
        }
    ];
<?php if ($response['data']['lock_obj'] == 1) { ?>
        data.push({
            "name": "shop_name",
            "title": "网络店铺",
            "value": "<?php echo $response['data']['shop_name'] ?>",
            "type": "input",
            "edit": false
        });
<?php } ?>

    var rt = new record_table();
    jQuery(function () {
        rt.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=stm/stock_lock_record/do_edit",
            'load_url': "?app_act=stm/stock_lock_record/get_stock_info&app_fmt=json&id=" + id,
            'load_callback': function () {
                logStore.load();
            }
        });

        if (priv_size_layer == 1) {
            select_goods_panel({
                "id": "btnSelectGoods",
                "callback": function () {},
                'param': {'store_code': store_code, 'model': 'stm_stock_lock', record_id: id}
            });
        } else {
            get_goods_inv_panel({
                "id": "btnSelectGoods",
                'param': {
                    'store_code': '<?php echo $response['data']['store_code'] ?>',
                    'lof_status':<?php echo $response['lof_status']; ?>,
                    'type': 'lock',
                    'record_code': '<?php echo $response['data']['record_code']; ?>'
                },
                "callback": addgoods
            });
        }

        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'code_name': $('#goods_code').val()});
            table_lof_listStore.load({'code_name': $('#goods_code').val()});
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
        var _thisDialog = obj;
        $.post('?app_act=stm/stock_lock_record/do_add_detail&id=' + id, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, 'error');
            }
            if (typeof _thisDialog.callback == "function") {
                _thisDialog.callback(this);
            }
        }, 'json');
    }


</script>

<div class="panel record_table" id="panel_html"></div>
<?php
$tabs = array(
    array('title' => '商品库存锁定信息', 'active' => true, 'id' => 'inv_lock'),
    array('title' => '商品库存释放信息', 'active' => false, 'id' => 'inv_release'),
);
render_control('TabPage', 'TabPage1', array(
    'tabs' => $tabs,
    'for' => 'TabPage1Contents'
));
?>
<div id="TabPage1Contents">
    <div>
        <div class="panel-body">
            <div class="row">
                <b>请输入</b>
                <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>
                <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品
                </button>
                <button type = "button" class="button button-info" value = "导出" onclick="report_excel()" > 导出</button >
                <?php if ($response['lof_status'] == 1): ?>
                    <div id="showbatch"></div>
                    <div id="shownobatch"></div>
                <?php endif; ?>
                <div style="float:right;">
                    <div id="J_Uploader" style="display:none;"></div>
                    <?php if (0 == $response['data']['order_status'] || (1 == $response['data']['order_status'] && $response['data']['lock_obj'] == 0)) { ?>
                        <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i  class="icon-plus-sign icon-white"></i> 新增商品</button>
                    <?php }if (0 == $response['data']['order_status'] || ($response['data']['order_status'] == 1 && $response['data']['lock_obj'] == 0)) { ?><!--导入先不开放><-->
                        <button type="button" class="button button-success" value="新增商品导入" id="btnimport"><i class="icon-plus-sign icon-white"></i> 商品导入 </button>
                    <?php }if (1 == $response['data']['order_status']) { ?>
                        <button type="button" class="button button-success" value="批量追加/释放" id="multi_lock_add_inv"><i  class="icon-plus-sign icon-white"></i>批量追加/释放</button>
                    <?php } ?>
                </div>
                <input id="lof_status" name="lof_status" type="hidden" value="<?php echo $response['lof_status'] ?>"/>
            </div>
            <?php
            $list = array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_name',
                    'width' => '150',
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
                    'width' => '100',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'],
                    'field' => 'spec2_name',
                    'width' => '100',
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
                    'title' => '计划锁定数量',
                    'field' => 'lock_num',
                    'width' => '100',
                    'align' => '',
                    'editor' => ($response['lof_status'] == 0) ? "{xtype:'number'}" : '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '已释放数量',
                    'field' => 'release_num',
                    'width' => '80',
                    'align' => '',
                // 'editor'=> "{xtype:'number'}"
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '实际锁定数量',
                    'field' => 'available_num',
                    'width' => '120',
                    'align' => '',
                ),
            );
            if ($response['lof_status'] == 0) {
                $list[] = array(
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
                            'show_cond' => 'obj.order_status==0'
                        ),
                        array(
                            'id' => 'add_inv',
                            'title' => '追加/释放',
                            'callback' => 'lock_add_inv',
                            'show_cond' => 'obj.order_status==1'
                        ),
                    ),
                );
            } else {
                $list[] = array(
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
                            'show_cond' => 'obj.order_status==0'
                        ),
                    ),
                );
            }
            render_control('DataTable', 'table_list', array(
                'conf' => array(
                    'list' => $list,
                ),
                'dataset' => 'stm/StockLockRecordDetailModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'stock_lock_record_detail_id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                //'RowNumber'=>true,
                //'CheckSelection' => true,
                'CellEditing' => (0 == $response['data']['order_status']) ? true : false,
            ));
            ?>
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
                                'title' => '批次',
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
                                'title' => '计划锁定数量',
                                'field' => 'init_num',
                                'width' => '90',
                                'align' => '',
                            //'editor'=> "{xtype:'number'}"
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '已释放数量',
                                'field' => 'fill_num',
                                'width' => '90',
                                'align' => '',
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '已锁定数量',
                                'field' => 'num',
                                'width' => '90',
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
                                        'show_cond' => 'obj.order_status==0'
                                    ),
                                    array(
                                        'id' => 'add_inv',
                                        'title' => '追加/释放',
                                        'callback' => 'lock_add_inv',
                                        'show_cond' => 'obj.order_status==1'
                                    ),
                                ),
                            )
                        )
                    ),
                    'dataset' => 'stm/StockLockRecordDetailModel::get_by_page_lof',
                    //'queryBy' => 'searchForm',
                    'idField' => 'stock_lock_record_detail_id',
                    'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                        //'RowNumber'=>true,
                        //'CheckSelection'=>true,
                        //'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
                ));
                ?>
            <?php endif; ?>
        </div>
    </div>
    <div>
        <div class="panel-body">
            <?php
            render_control('DataTable', 'table_record_list', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据类型',
                            'field' => 'type_name',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '单据编号',
                            'field' => 'relation_code',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '锁定库存占用时间',
                            'field' => 'add_time',
                            'width' => '200',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '占用数量',
                            'field' => 'num',
                            'width' => '90',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '描述',
                            'field' => 'describe',
                            'width' => '320',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'stm/StockLockRecordModel::get_relation_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'id',
                'params' => array('filter' => array('record_code' => $response['data']['record_code'], 'inv_status' => 2)),
                    //'RowNumber'=>true,
                    //'CheckSelection'=>true,
                    //'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
            ));
            ?>
        </div>
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
                            'field' => 'finish_status',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '500',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['stock_lock_record_id'], 'module' => 'stock_lock_record')),
            ));
            ?>
        </div>
    </div>
</div>
<ul id="tool" class="toolbar frontool frontool_center">
    <?php if (in_array($response['data']['order_status'], array(0, 1))) { ?>

        <?php if (0 == $response['data']['order_status']) { ?>
            <li class="li_btns"><button class="button button-primary" id="record_lock">锁定</button></li>
        <?php } ?>
        <?php if (1 == $response['data']['order_status']) { ?>
            <li class="li_btns"><button class="button button-primary" id="record_unlock">释放</button></li>
        <?php } ?>
    <?php } ?>
    <!--    <li class="li_btns"><button class="button button-primary" id="export" onclick="report_excel()">导出</button></li>-->
</ul>
<div id="panel_lock_sync" style="visibility: hidden">
    <div class="row">
        <div class="control-group span10" style="margin-top: 20px;margin-left: 20px;">
            <label style="font-size:1.2em">系统支持2种模式来同步库存，请选择一种：</label><br>
            <input type="radio" name="lock_sync_mode" id="lock_sync_mode1" class="check_custom" value="1" checked="checked" style="margin-top: 10px;"><label class="radio" for="lock_sync_mode1"></label><label for="lock_sync_mode1" style="cursor: pointer;">以锁定库存同步</label><br>
            <input type="radio" name="lock_sync_mode" id="lock_sync_mode2" class="check_custom" value="2"><label class="radio" for="lock_sync_mode2"></label><label for="lock_sync_mode2"  style="cursor: pointer;">以锁定库存同步 + 剩余可用库存同步</label>
            <a href="javascript:lock_sync_mode_help()" style="margin-left: 10px;color: red">模式详解（必看）</a>
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 5px;">
        <button class="button button-primary" id="btn_lock_sync">确定</button>
    </div>
</div>
<div id="panel_unlock_sync" style="visibility: hidden">
    <div class="row">
        <div class="control-group span9" style="margin-top: 20px;margin-left: 20px;">
            <span>请必须保证锁定商品同步比例大于0，否则会有下架的风险</span><br><br>
            <span>一键更新锁定商品同步比例为</span>
            <input type="text" id="sync_ratio" style="width:50px;"> %<span style="color:red;">（必须为正整数）</span>
        </div>
    </div>
    <div class="clearfix" style="text-align: center;margin-top: 10px;">
        <button class="button button-primary" id="btn_unlock_sync">确定</button>
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

        //批次删除
        function do_delete_detail_lof(_index, row) {
            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: '?app_act=stm/stock_lock_record/do_delete_detail_lof',
                data: {id: row.id, pid: row.pid},
                success: function (ret) {
                    // tableStore.load({'code_name': ''});
                    // table1Store.load({'code_name': ''});
                    var type = (ret.status == 1) ? 'success' : 'error';
                    if (type != 'success') {
                        BUI.Message.Alert(ret.message, type);
                    } else {
                        BUI.Message.Tip('删除成功！', 'info');
                        //列表加载
                        reload_page();
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
            url: '?app_act=stm/stock_lock_record/do_delete_detail',
            data: {stock_lock_record_detail_id: row.stock_lock_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    BUI.Message.Tip('删除成功！', 'info');
                    reload_page();
                }
            }
        });
    }
    //修改数量
    if (typeof table_listCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_listCellEditing.on('accept', function (record, editor) {
            if (parseInt(record.record.lock_num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
            $.post('?app_act=stm/stock_lock_record/do_edit_detail',
                    {pid: record.record.pid, sku: record.record.sku, lock_num: record.record.lock_num},
                    function (result) {
                        if (result.status != 1) {
                            BUI.Message.Alert(result.message, 'error');
                        } else {
                            var _res = result.res;
                            table_listStore.load();
                            $("#base_table tr").eq(1).find("td").eq(1).html(_res.lock_num);
                            //    $("#base_table tr").eq(3).find("td").eq(0).html(_res.money);
                            logStore.load();
                        }

                    }, 'json');
        });
    }
</script>

<script type="text/javascript">
    $("#table_datatable").show();
    $("#table1_datatable").hide();
    function reload_page() {
        //基本信息加载
        if (typeof (rt) != 'undefined') {
            rt.load_data();
        }
        //未开启批次列表加载
        if (typeof (table_listStore) != 'undefined') {
            table_listStore.load();
        }
        //开启批次列表加载
        if (typeof (table_lof_listStore) != 'undefined') {
            table_lof_listStore.load();
        }
        //日志加载
        logStore.load();
    }
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

    /*----------锁定功能------START-----*/
    $('#record_lock').on('click', function () {
        record_lock();
    });

    var dialog1, dialog2, sync_code;
    BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
        dialog1 = new Overlay.Dialog({
            title: '',
            width: 430,
            height: 250,
            elCls: 'custom-dialog',
            contentId: 'panel_lock_sync'
        });

        $('#btn_lock_sync').on('click', function () {
            var lock_sync_mode = $("input[name='lock_sync_mode']:checked").val();
            opt_record_lock(id, lock_sync_mode, sync_code);
            dialog1.close();
        });
    });

    //锁定
    function record_lock() {
        //锁定对象不是网络店铺直接做锁定操作
        if ('<?php echo $response['data']['lock_obj']; ?>' != 1) {
            BUI.Message.Confirm('确定锁定库存吗？', function () {
                opt_record_lock(id, 2);
            });
            return false;
        }
        //锁定对象是网络店铺需要进行库存同步策略判断和设置
        var params = {shop_code: '<?php echo $response['data']['shop_code'] ?>', store_code: '<?php echo $response['data']['store_code'] ?>'};
        $.post('?app_act=stm/stock_lock_record/check_inv_sync', {params: params}, function (ret) {
            if (ret.status == 1) {
                sync_code = ret.data;
                dialog1.show();
                return false;
            }
            var msg, btn_ok, set_type;
            if (ret.status == -3) {
                msg = '为保证店铺以库存锁定单数量来同步，请为店铺设置并启用库存同步策略';
                btn_ok = '前往设置';
                set_type = 1;
            } else if (ret.status == -2) {
                msg = '为保证店铺以库存锁定单数量来同步，请开启并设置库存同步策略';
                btn_ok = '前往开启';
                set_type = 2;
            }
            BUI.Message.Show({title: '友情提示', msg: msg, icon: 'warning', buttons: [
                    {text: btn_ok, elCls: 'button button-primary', handler: function () {
                            open_inv_sync_page(set_type);
                            this.close();
                        }
                    },
                    {text: '考虑一下', elCls: 'button', handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }, 'json');
    }

    //打开库存同步策略
    function open_inv_sync_page(_type) {
        if (_type == 1) {
            openPage('<?php echo base64_encode('?app_act=op/inv_sync/do_list') ?>', '?app_act=op/inv_sync/do_list', '库存同步策略');
        } else {
            openPage('<?php echo base64_encode('?app_act=sys/params/do_list&page_no=op') ?>', '?app_act=sys/params/do_list&page_no=op', '系统参数设置');
        }
    }

    //锁定同步两种模式区别提示
    function lock_sync_mode_help() {
        openPage('<?php echo base64_encode('?app_act=stm/stock_lock_record/lock_mode_help') ?>', '?app_act=stm/stock_lock_record/lock_mode_help', '锁定模式详解');
    }

    function opt_record_lock(record_id, lock_sync_mode) {
        var params = {id: record_id, lock_sync_mode: lock_sync_mode, sync_code: sync_code};
        $.post('?app_act=stm/stock_lock_record/record_lock_action' + '&app_fmt=json', {params: params}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip(ret.message, 'success');
                location.reload();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }
    /*----------锁定功能------END----*/

    /*----------释放功能------START----*/
    $('#record_unlock').on('click', function () {
        record_unlock();
    });

    BUI.use(['bui/overlay', 'bui/form'], function (Overlay, Form) {
        dialog2 = new Overlay.Dialog({
            title: '',
            width: 430,
            height: 230,
            elCls: 'custom-dialog',
            contentId: 'panel_unlock_sync'
        });

        $('#btn_unlock_sync').on('click', function () {
            var sync_ratio = $("#sync_ratio").val();
            var re = /^[0-9]*[1-9][0-9]*$/;
            if (!re.test(sync_ratio)) {
                BUI.Message.Tip('同步比例必须为大于0的正整数', 'warning');
                return false;
            }
            opt_record_unlock(id, sync_ratio);
            dialog2.close();
        });
    });
    function record_unlock() {
        //锁定对象不是网络店铺直接做释放操作
        var lock_obj = '<?php echo $response['data']['lock_obj']; ?>';
        var sync_code = '<?php echo $response['data']['sync_code']; ?>';
        if (lock_obj != 1 || sync_code == '') {
            BUI.Message.Confirm('确定释放库存吗？', function () {
                opt_record_unlock(id, '');
            });
            return false;
        }
        var params = {sync_code: '<?php echo $response['data']['sync_code'] ?>', shop_code: '<?php echo $response['data']['shop_code'] ?>', store_code: '<?php echo $response['data']['store_code'] ?>'};
        $.post('?app_act=stm/stock_lock_record/get_sync_ratio', {params: params}, function (ret) {
            $("#sync_ratio").val(ret.data);
        }, 'json');
        dialog2.show();
    }

    function opt_record_unlock(record_id, sync_ratio) {
        var params = {id: record_id, sync_ratio: sync_ratio};
        $.post('?app_act=stm/stock_lock_record/record_unlock_action&app_fmt=json', {params: params}, function (ret) {
            if (ret.status == 1) {
                BUI.Message.Tip(ret.message, 'success');
                location.reload();
            } else {
                BUI.Message.Alert(ret.message, 'error');
            }
        }, 'json');
    }
    /*----------释放功能------END----*/

    $('#btnimport').on('click', function () {
        url = "?app_act=stm/stock_lock_record/importGoods&id=" + id + '&import_from=<?php echo $response['data']['status'] ?>';
        new ESUI.PopWindow(url, {
            title: "导入商品",
            width: 880,
            height: 400,
            onBeforeClosed: function () {
                //列表加载
                reload_page();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    });

    /**
     * 追加锁定
     * @param index
     * @param row
     */
    function lock_add_inv(index, row) {
        if (lof_status == 0) {
            var params = "&id=" + row.stock_lock_record_detail_id;
        } else {
            var params = "&id=" + row.id;
        }
        url = "?app_act=stm/stock_lock_record/lock_add_inv&lof_status=" + lof_status + params;
        new ESUI.PopWindow(url, {
            title: "锁定库存追加",
            width: 500,
            height: 380,
            onBeforeClosed: function () {
                //列表加载
                reload_page();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    }

    //加载单据的基本信息，搞成无页面刷新效果
    function reload_record() {
        $.post('?app_act=stm/stock_lock_record/get_stock_info' + '&app_fmt=json', {id: id}, function (_res) {
            $("#base_table tr").eq(1).find("td").eq(1).html(_res.data.lock_num);
            $("#base_table tr").eq(1).find("td").eq(2).html(_res.data.release_num);
            $("#base_table tr").eq(2).find("td").eq(0).html(_res.data.available_num);
        }, 'json');
    }


    //批量追加释放
    $('#multi_lock_add_inv').on('click', function () {
        url = "?app_act=stm/stock_lock_record/import_add_inv&id=" + id;
        new ESUI.PopWindow(url, {
            title: "批量追加/释放",
            width: 600,
            height: 400,
            onBeforeClosed: function () {
                //列表加载
                reload_page();
            },
            onClosed: function () {
                //刷新数据
            }
        }).show();
    });

    //导出
    function report_excel() {
        var stock_lock_record_id = id;
        var goods_code = $('#goods_code').val();
        var url = "?app_act=stm/stock_lock_record/export_csv_list&app_fmt=json&stock_lock_record_id=" + stock_lock_record_id + "&is_lof=" + lof_status + "&goods_code=" + goods_code;
        window.location.href = url;
    }



</script>
