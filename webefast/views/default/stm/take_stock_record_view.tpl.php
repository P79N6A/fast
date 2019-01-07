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
render_control('PageHead', 'head1', array('title' => '盘点单',
    'links' => array(
        array('url' => 'stm/take_stock_record/do_list', 'title' => '盘点单')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if (0 == $response['data']['is_sure']) { ?>
            <a class="button button-primary" href="javascript:do_sure('<?php echo $response['data']['take_stock_record_id']; ?>')"> 确认</a>
        <?php } ?>
    </li>
</li>
<button type="button" class="button button-info" style="background-color: #1695ca;"  value="导出" id="btn-csv">导出</button>
<li class="li_btns">
    <?php if (1 == $response['data']['is_sure'] && 1 != $response['data']['is_stop'] && 0 == $response['data']['is_pre_profit_and_loss']) { ?>
        <a class="button button-primary" href="javascript:do_stop('<?php echo $response['data']['take_stock_record_id']; ?>')"> 终止</a>
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
    })
</script>

<script>
    function do_sure(take_stock_record_id) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_sure",
            data: {id: take_stock_record_id, is_detail_sure: 1},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.status == "1") {
                    //tableStore.load();
                    location.reload();
                }
            }
        })
    }
    function do_stop(take_stock_record_id) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_stop",
            data: {id: take_stock_record_id},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Alert(data.message, type);
                if (data.status == "1") {
                    //tableStore.load();
                    location.reload();
                }

            }
        })
    }
</script>
<script>
    var type = 1;
    var rt;
    var id = "<?php echo $response['data']['take_stock_record_id'] ?>";
    var store_code = "<?php echo $response['data']['store_code'] ?>";
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
            "name": "take_stock_time",
            "title": "盘点日期",
            "value": "<?php echo date('Y-m-d', strtotime($response['data']['take_stock_time'])) ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "take_stock_pd_status",
            "title": "单据状态",
            "value": "<?php echo $response['data']['take_stock_pd_status'] ?>",
            "type": "input",
        },

        {
            "title": "盘点仓库",
            "value": "<?php echo get_store_name_by_code($response['data']['store_code']) ?>"
        },
        {
            "name": "num",
            "title": "数量",
            "value": "<?php echo $response['data']['num'] ?>",
            "type": "input",
        },
        /* {
         "title":"关联盈亏单",
         "value":"<?php echo $response['data']['relation_code'] ?>",
         },
         */
        /*
         {
         "name":"user_code",
         "title":"业务员",
         "value":"<?php //echo $response['data']['user_code']  ?>",
         "type":"select",
         "edit":true,
         "data":<?php //echo format_bui(get_user(1))  ?>,
         },
         */
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
    ];

    jQuery(function () {
        var rt = new record_table();
        rt.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=stm/take_stock_record/do_edit",
            'load_url': "?app_act=stm/take_stock_record/get_stock_info&app_fmt=json&id=<?php echo $response['data']['take_stock_record_id'] ?>",
            'load_callback': function () {
                logStore.load();
            }
        });
        //
<?php if ($response['lof_status'] == 1): ?>
            jQuery("#showbatch").bind("click", showbatch);
            jQuery("#shownobatch").bind("click", shownobatch);
<?php endif; ?>

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'stm_take_stock', record_id: id}
                });
            } else {
                get_goods_sku_panel({
                    "id": "btnSelectGoods",
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'type': 'minus'},
                    "callback": add_detail
                });
            }
        }

        $('#btnSearchGoods').on('click', function () {
            no_batchStore.load({'code_name': $('#goods_code').val()});
            batchStore.load({'code_name': $('#goods_code').val()});
            /*
             batchStore.load({'goods_code': $('#goods_code').val()},function(data){
             if(type == 1){
             shownobatch();
             }
             if(type == 2){
             showbatch();
             }
             });
             */
        });
    })

    function add_detail(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num >= 0) {
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
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_add_detail",
            data: {data: select_data, pid:<?php echo $response['data']['take_stock_record_id']; ?>, store_code: '<?php echo $response['data']['store_code'] ?>'},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';

                if (data.status == "1") {
                    BUI.Message.Tip(data.message, type);
                } else {
                    BUI.Message.Alert(data.message, type);
                }
                if (typeof _thisDialog.callback == "function") {
                    _thisDialog.callback(this);
                }
                reload_page();
            }
        })
    }

    function do_delete_detail(_index, row) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_delete_detail",
            data: {id: row.take_stock_record_detail_id, pid: row.pid},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Tip(data.message, type);
                if (data.status == "1") {
//                no_batchStore.load();
//                batchStore.load();
                    reload_page();
                }
            }
        })

    }

    function do_delete_detail_lof(_index, row) {
        ajax_post({
            url: "?app_act=stm/take_stock_record/do_delete_detail_lof",
            data: {id: row.id, pid: row.pid},
            async: false,
            alert: false,
            callback: function (data) {
                var type = data.status == "1" ? 'success' : 'error';
                BUI.Message.Tip(data.message, type);
                if (data.status == "1") {
//            	no_batchStore.load();
//            	batchStore.load();
                    reload_page();
                }
            }
        })
    }

</script>

<style>
    #no_batch_datatable{
        display:block;
    }
    #batch_datatable{
        display:none;
    }
</style>
<div class="panel record_table" id="panel_html">

</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>请输入</b>
            <input type="text" class="input" value="" placeholder="商品编码/商品条码"  id="goods_code"  aria-disabled="false" aria-pressed="false"/>
            <?php if (0 == $response['data']['is_sure']) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button> &nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"><i class="icon-plus-sign icon-white"></i> 新增商品</button> &nbsp;
                    <button type="button" class="button button-success" value="" id="btnimport" ><i class="icon-plus-sign icon-white"></i>  商品导入</button>
                </div>
            <?php } ?>
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
        </div>
    </div>

    <?php
    render_control('DataTable', 'no_batch', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品名称',
                    'field' => 'goods_code',
                    'width' => '200',
                    'align' => '',
                    'phpfun' => 'get_goods_name_by_code'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '150',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec1_rename'],
                    'field' => 'spec1_code',
                    'width' => '80',
                    'align' => '',
                    'phpfun' => 'get_spec1_name_by_code'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => $response['goods_spec2_rename'],
                    'field' => 'spec2_code',
                    'width' => '80',
                    'align' => '',
                    'phpfun' => 'get_spec2_name_by_code'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'barcode',
                    'width' => '150',
                    'align' => '',
                // 'phpfun'=>'get_barcode_by_sku'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '盘点数量',
                    'field' => 'num',
                    'width' => '100',
                    'align' => ''
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
                            'show_cond' => 'obj.is_sure == 0',
                        ),
                    ),
                )
            )
        ),
        'dataset' => 'stm/TakeStockRecordModel::get_detail_by_page',
        'idField' => 'purchaser_record_detail_id',
        'params' => array('filter' => array('pid' => $request['_id'])),
            //'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
    ));
    ?>
    <?php if (isset($response['lof_status']) && $response['lof_status'] == 1): ?>
        <?php
        render_control('DataTable', 'batch', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品名称',
                        'field' => 'goods_code',
                        'width' => '120',
                        'align' => '',
                        'phpfun' => 'get_goods_name_by_code'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品编码',
                        'field' => 'goods_code',
                        'width' => '120',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec1_rename'],
                        'field' => 'spec1_code',
                        'width' => '80',
                        'align' => '',
                        'phpfun' => 'get_spec1_name_by_code'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => $response['goods_spec2_rename'],
                        'field' => 'spec2_code',
                        'width' => '80',
                        'align' => '',
                        'phpfun' => 'get_spec2_name_by_code'
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品条形码',
                        'field' => 'barcode',
                        'width' => '120',
                        'align' => '',
                    //'phpfun'=>'get_barcode_by_sku'
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
                        'title' => '盘点数量',
                        'field' => 'num',
                        'width' => '120',
                        'align' => ''
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
                                'show_cond' => 'obj.is_sure == 0',
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'stm/TakeStockRecordModel::get_by_detail_page_lof',
            'idField' => 'purchaser_record_detail_id',
            'params' => array('filter' => array('pid' => $request['_id'])),
                //'CellEditing'=>(1==$response['data']['is_check_and_accept'])?false:true,
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
                            'field' => 'finish_status',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '备注',
                            'field' => 'action_note',
                            'width' => '240',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['take_stock_record_id'], 'module' => 'take_stock_record')),
            ));
            ?>
        </div>
    </div>
</div>
<script>
    function reload_page() {

        if (typeof (rt) != 'undefined') {
            rt.load_data();
        }
        if (typeof (no_batchStore) != 'undefined') {
            no_batchStore.load();
        }
        if (typeof (batchStore) != 'undefined') {
            batchStore.load();
        }
        logStore.load();
    }
<?php if ($response['lof_status'] == 1): ?>
        function shownobatch() {
            jQuery("#no_batch_datatable").show();
            jQuery("#batch_datatable").hide();
            jQuery('#showbatch').addClass("curr");
            jQuery('#shownobatch').addClass("curr");
        }
        function showbatch() {
            jQuery("#batch_datatable").show();
            jQuery("#no_batch_datatable").hide();
            jQuery('#showbatch').removeClass("curr");
            jQuery('#shownobatch').removeClass("curr");
        }
        showbatch();
<?php endif; ?>
    jQuery(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=stm/take_stock_record/importGoods&id=" +<?php echo $response['data']['take_stock_record_id'] ?>;
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

    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan_common/view_scan&dj_type=take_stock&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    })
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var lof_status = "<?php echo $response['lof_status']; ?>";
    var pid = "<?php echo $request['_id']; ?>";

//    function export_excel(){
//        var param="";
//        var goods_code = $('#goods_code').val();
//        param=param+"&record_code="+record_code+"&lof_status="+lof_status+"&goods_code="+goods_code+"&app_fmt=json";
//        url="?app_act=stm/take_stock_record/export_csv_list"+param;
//    	window.location.href=url;
//    }

    $('#btn-csv').click(function () {
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        if (lof_status == 1) {
            params = batchStore.get('params');
            params.ctl_export_conf = 'take_stock_record_list_lof_detail';
        } else {
            params = no_batchStore.get('params');
            params.ctl_export_conf = 'take_stock_record_list_detail';
        }
        params.ctl_type = 'export';
        params.ctl_export_name = '盘点单详情导出';
        params.pid = pid;
        params.code_name = $("#goods_code").val();
        if (lof_status == 1) {
<?php echo create_export_token_js('stm/TakeStockRecordModel::get_by_detail_page_lof'); ?>
        } else {
<?php echo create_export_token_js('stm/TakeStockRecordModel::get_detail_by_page'); ?>
        }

//            var obj = searchFormForm.serializeToObject();
//            for(var key in obj){
//                params[key] =  obj[key];
//            }

        for (var key in params) {
            url += "&" + key + "=" + params[key];
        }
        params.ctl_type = 'view';
        //window.location.href = url;
        window.open(url);

    });

</script>