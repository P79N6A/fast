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
$title = "仓库调整单";
$url = "stm/stock_adjust_record/do_list";
if ($response['data']['is_entity_shop'] == 1) {
    $title = "门店库存调整单";
    $url = "stm/stock_adjust_record/entity_shop";
}
//$result = load_model('sys/GoodsRuleModel')->get_by_ids(array(1, 2));
render_control('PageHead', 'head1', array('title' => $title,
    'links' => array(
        array('url' => $url, 'is_pop' => false, 'target' => '_self', 'title' => $title)
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if (0 == $response['data']['is_check_and_accept']): ?><button type="button" class="button button-primary" value="验收" id="btnRecordCheckin"><i class="icon-ok icon-white"></i> 验收</button><?php endif; ?>

    </li>
    <button type="button" class="button button-info" style="background-color: #1695ca;"  onclick="export_excel()"  value="导出" id="btn-csv">导出</button>
    <!--<li class="li_btns"> <button type="button" class="button button-primary" value="返回" onclick="javascript:history.go(-1);"><i class="icon-backward icon-white"></i> 返回</button></li>-->
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
    var id = "<?php echo $response['data']['stock_adjust_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var type = 1;
    var priv_size_layer = "<?php echo $response['priv_size_layer']; ?>";
    var is_edit = true;
<?php if (1 == $response['data']['is_check_and_accept']) { ?>
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
            "name": "adjust_type",
            "title": "调整类型",
            "value": "<?php echo $response['data']['adjust_type'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['adjust_type'] ?>
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
            "name": "is_add_time",
            "title": "下单日期",
            "value": "<?php echo $response['data']['is_add_time'] ?>",
            "type": "input",

        },
        {
            "name": "record_time",
            "title": "业务日期",
            "value": "<?php echo $response['data']['record_time'] ?>",
            "type": "time",
            "edit": true
        },
        {
            "title": "数量",
            "value": "<?php echo $response['data']['num'] ?>"
        },
        {
            "title": "金额",
            "value": "<?php echo number_format($response['data']['money'], 2); ?>"
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
        var rt = new record_table();
        rt.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=stm/stock_adjust_record/do_edit",
            //'load_url':"?app_act=stm/stock_adjust_record/detail&app_fmt=json&_id=<?php echo $response['data']['stock_adjust_record_id'] ?>",
//            'load_callback':function(){
//                logStore.load();
//                var params = {};
//                params.store_code =$('#store_code_hide').val();
//                params.lof_status = 1;
//              //  update_panel_params(params);
//            }
        });
        
        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'stm_stock_adjust', record_id: id}
                });
            } else {
                get_adjust_goods_sku_panel({
                    "id": "btnSelectGoods",
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'type': 'adjust', 'is_entity': '<?php echo $response['is_entity'] ?>'},
                    "callback": addgoods
                });
            }
        }
        
        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'code_name': $('#goods_code').val()});
            table_lof_listStore.load({'code_name': $('#goods_code').val()});
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
    })

    function addgoods(obj) {

        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                select_data[di] = value;
                di++;
            }
        });
        var _thisDialog = obj;
        if (di == 0) {
            _thisDialog.close();
            return;
        }
        var _thisDialog = obj;
        $.post('?app_act=stm/stock_adjust_record/do_add_detail&id=' + id, {data: select_data}, function (result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, 'error');
            } else {
                BUI.Message.Alert('增加成功', function () {
                    location.reload();
                }, 'info');
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
            <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>

            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <!--  <button type="button" class="button button-info" value="重置" id="btnSearchReset"><i class="icon-repeat icon-white"></i> 重置</button>-->


            <?php if ($response['lof_status'] == 1): ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php endif; ?>
            <?php if (0 == $response['data']['is_check_and_accept']) { ?>
                <div style ="float:right;">
                    <div id="J_Uploader" style="display:none;" >
                    </div>
                    <!--                       <a class="button button-primary" target="_blank" href="?app_act=sys/file/get_file&type=1&name=stock_adjust_record.csv">模版下载</a> -->
                    <?php if ($response['lof_status'] == 0) { ?>
                        <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>
                    <?php } ?>
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods"  ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                    <button type="button" class="button button-success" value="新增商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>

                </div>
            <?php } ?>
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
                        'title' => '成本价',
                        'field' => 'cost_price',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '吊牌价',
                        'field' => 'price',
                        'width' => '80',
                        'align' => ''
                    ),
//                    array(
//                    		'type' => 'text',
//                    		'show' => 1,
//                    		'title' => '批次',
//                    		'field' => 'lof_no',
//                    		'width' => '80',
//                    		'align' => ''
//                    ),
//                    array(
//                    		'type' => 'text',
//                    		'show' => 1,
//                    		'title' => '生产日期',
//                    		'field' => 'production_date',
//                    		'width' => '80',
//                    		'align' => ''
//                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '调整数量',
                        'field' => 'num',
                        'width' => '80',
                        'align' => '',
                    // 'editor'=> "{xtype:'number'}"
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '调整金额',
                        'field' => 'money',
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
                                'show_cond' => 'obj.is_check_and_accept == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'stm/StmStockAdjustRecordDetailModel::get_by_page',
            //'queryBy' => 'searchForm',
            'idField' => 'stock_adjust_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                //'RowNumber'=>true,
                //'CheckSelection' => true,
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
                            'title' => '成本价',
                            'field' => 'cost_price',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '吊牌价',
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
                            'title' => '调整数量',
                            'field' => 'num',
                            'width' => '80',
                            'align' => '',
                        //'editor'=> "{xtype:'number'}"
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '调整金额',
                            'field' => 'money',
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
                                    'show_cond' => 'obj.is_check_and_accept == 0'
                                ),
                            ),
                        )
                    )
                ),
                'dataset' => 'stm/StmStockAdjustRecordDetailModel::get_by_page_lof',
                //'queryBy' => 'searchForm',
                'idField' => 'id',
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
                            'width' => '240',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['stock_adjust_record_id'], 'module' => 'stock_adjust_record')),
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
                url: '?app_act=stm/stock_adjust_record/do_delete_detail_lof',
                data: {id: row.id, pid: row.pid},
                success: function (ret) {
                    // tableStore.load({'code_name': ''});
                    // table1Store.load({'code_name': ''});
                    var type = (ret.status == 1) ? 'success' : 'error';
                    if (type != 'success') {
                        BUI.Message.Alert(ret.message, type);
                    } else {
                        BUI.Message.Tip('删除成功！', 'info');
                        reload_page()
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
            url: '?app_act=stm/stock_adjust_record/do_delete_detail',
            data: {stock_adjust_record_detail_id: row.stock_adjust_record_detail_id, pid: row.pid, sku: row.sku},
            success: function (ret) {
                //  tableStore.load({'code_name': ''});
                // table1Store.load({'code_name': ''});
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    BUI.Message.Alert('删除成功', function () {
                        location.reload();
                    }, 'info');
                }
            }
        });
    }
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
    function reload_page() {

        if (typeof (rt) != 'undefined') {
            rt.load_data();
        }
        if (typeof (table_listStore) != 'undefined') {
            table_listStore.load();
        }
        if (typeof (table_lof_listStore) != 'undefined') {
            table_lof_listStore.load();
        }
        logStore.load();
    }
    //面板展开和隐藏
    $('.toggle').click(function () {
        $(this).parents('.panel-header').siblings('.panel-body').slideToggle('fast');
        return false;
    });

    //验收按钮++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    $('#btnRecordCheckin').on('click', function () {
        var login_type = '<?php echo $response['login_type'] ?>';
        var action = login_type > 0 ? 'do_entity_checkin' : 'do_checkin';
        $.post('?app_act=stm/stock_adjust_record/' + action + '&app_fmt=json', {id: id}, function (result) {
            if (result.status == 1) {
                BUI.Message.Alert(result.message, function () {
                    location.reload();
                }, 'success');
            } else {
                BUI.Message.Alert(result.message, function () {
                    location.reload();
                }, 'error');
            }
        }, 'json');

    });


    $(function () {
        $('#btnimport').on('click', function () {
            url = "?app_act=stm/stock_adjust_record/importGoods&id=" + id;
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
    var record_code = "<?php echo $response['data']['record_code'] ?>";
    var lof_status = "<?php echo $response['lof_status'] ?>";
    function export_excel() {
        var param = "";
        var goods_code = $('#goods_code').val();
        param = param + "&record_code=" + record_code + "&lof_status=" + lof_status + "&goods_code=" + goods_code + "&app_fmt=json";
        url = "?app_act=stm/stock_adjust_record/export_csv_list" + param;
        window.location.href = url;
    }

    //扫描商品
    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan_common/view_scan&dj_type=adjust&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    })

</script>
