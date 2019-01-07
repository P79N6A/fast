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
render_control('PageHead', 'head1', array('title' => '采购退货通知单',
    'links' => array(
        array('url' => 'pur/return_notice_record/do_list', 'target' => '_self', 'title' => '采购退货通知单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_sure')) { ?>
            <?php if (0 == $response['data']['is_sure']) { ?>
                <a class="button button-primary" href="javascript:do_sure(this, '<?php echo $response['data']['return_notice_record_id']; ?>')"> 确认</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <a class="button button-primary" onclick = report_excel() > 导出</a>
    </li>
    <!--   <li class="li_btns">
         <a class="button button-primary" href="javascript:do_print(this, '<?php //echo $response['data']['return_notice_record_id'];    ?>')">打印</a>
     </li>-->
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_sure')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_execute'] == 0) { ?>
                <a class="button button-primary" href="javascript:do_re_sure(this, '<?php echo $response['data']['return_notice_record_id']; ?>')">取消确认</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_execute')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_wms'] != 1) { ?>
                <a class="button button-primary" href="javascript:do_execute(this, '<?php echo $response['data']['return_notice_record_id']; ?>')">生成退货单</a>
            <?php } ?>
        <?php } ?>
    </li>
    <li class="li_btns">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/do_stop')) { ?>
            <?php if (1 == $response['data']['is_sure'] && $response['data']['is_stop'] == 0 && $response['data']['is_finish'] == 0 && $response['data']['is_wms'] != 1) { ?>
                <a class="button button-primary" href="javascript:do_stop(this, '<?php echo $response['data']['return_notice_record_id']; ?>')">终止</a>
            <?php } ?>
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
    var record_code = "<?php echo $response['data']['record_code']; ?>";
    var id = "<?php echo $response['data']['return_notice_record_id']; ?>";
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
            "title": "下单时间",
            "value": "<?php echo $response['data']['order_time'] ?>",

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
            "name": "store_code",
            "title": "仓库",
            "value": "<?php echo $response['data']['store_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>,
        },
        {
            "name": "rebate",
            "title": "折扣",
            "value": "<?php echo $response['data']['rebate'] ?>",
        },

        {
            "name": "record_time",
            "title": "业务日期",
            "value": "<?php echo date('Y-m-d', strtotime($response['data']['record_time'])) ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "num",
            "title": "总数量",
            "value": "<?php echo $response['data']['num'] ?>",
        },
        {
            "name": "money",
            "title": "总金额",
            "value": "<?php echo $response['data']['money'] ?>",
        },
        {
            "name": "num",
            "title": "完成数量",
            "value": "<?php echo $response['data']['finish_num'] ?>",
        },
        {
            "name": "remark",
            "title": "备注",
            "value": "<?php echo $response['data']['remark'] ?>",
            "type": "input",
            "edit": true,
        },
        {
            "title": "确认",
            "value": "<?php echo $response['data']['is_check_src'] ?>",
        },
        {
            "title": "完成",
            "value": "<?php echo $response['data']['is_finish_src'] ?>",
        },
        {
            "name": "record_type_code",
            "title": "退货类型",
            "value": "<?php echo $response['data']['record_type_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['record_type'] ?>,
        },
    ];

    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=pur/return_notice_record/do_edit"
        });

        jQuery("#showbatch").bind("click", showbatch);
        jQuery("#shownobatch").bind("click", shownobatch);

        if (is_edit) {
            if (priv_size_layer == 1) {
                select_goods_panel({
                    "id": "btnSelectGoods",
                    "callback": function () {},
                    'param': {'store_code': store_code, 'model': 'pur_return_notice', record_id: id}
                });
            } else {
                get_goods_inv_panel({
                    "id": "btnSelectGoods",
                    "callback": addgoods,
                    'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'status': 1, 'diy': '0'}
                });
            }
        }


        $('#btnSearchGoods').on('click', function () {
            table_listStore.load({'code_name': $('#goods_code').val()});
            table_lof_listStore.load({'code_name': $('#goods_code').val()});

        });

        $('#btnimport').on('click', function () {
            url = "?app_act=pur/return_notice_record/import_goods&id=" + id;
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


    function addgoods(obj) {
        var allow_negative_inv = "<?php echo $response['data']['allow_negative_inv'] ?>";
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function (value, key) {
            //     if(top.$("input[name='num_"+value.sku+"']").val()!=''&&top.$("input[name='num_"+value.sku+"']").val()!=undefined){
            //      value.num = top.$("input[name='num_"+value.sku+"']").val();
            if (top.$("input[name='num_" + value.goods_inv_id + "']").val() != '' && top.$("input[name='num_" + value.goods_inv_id + "']").val() != undefined) {
                value.num = top.$("input[name='num_" + value.goods_inv_id + "']").val();
                if (value.num > 0) {
                    //判断是否允许负库存
                    if (allow_negative_inv == 0) {
                        if (parseInt(value.num) > parseInt(value.available_mum)) {
                            value.num = value.available_mum;
                        }
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
        $.post('?app_act=pur/return_notice_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
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

    function shownobatch() {
        type = 1;
        jQuery('#batch tr').find('td:eq(5)').hide();
        jQuery('#batch tr').find('th:eq(5)').hide();
        jQuery('#batch tr').find('td:eq(6)').hide();
        jQuery('#batch tr').find('th:eq(6)').hide();
        jQuery('#showbatch').addClass("curr");
        jQuery('#shownobatch').addClass("curr");
    }
    function showbatch() {
        type = 2;
        jQuery('#batch tr').find('td:eq(5)').show();
        jQuery('#batch tr').find('th:eq(5)').show();
        jQuery('#batch tr').find('td:eq(6)').show();
        jQuery('#batch tr').find('th:eq(6)').show();
        jQuery('#shownobatch').removeClass("curr");
        jQuery('#showbatch').removeClass("curr");
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
            <!--<button type="button" class="button button-info"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>-->
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>

            <?php if (0 == $response['data']['is_sure']) { ?>
                <div style ="float:right;">
                    <?php if (0 == $response['lof_status']) { ?>
                        <button type="button" class="button button-success" value="扫描商品" id="scan_goods"><i class="icon-plus-sign icon-white"></i> 扫描商品</button>&nbsp;
                    <?php } ?>
                    <button type="button" class="button button-success" value="商品导入" id="btnimport"  ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                    &nbsp;<button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php } ?>
        </div>
    </div>
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
                /*
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
                  ), */
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '标准进价',
                    'field' => 'price',
                    'width' => '80',
                    'align' => '',
                    'editor' => $response['price_status'] == 1 ? "{xtype:'number'}" : ''
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
                    'title' => '单价',
                    'field' => 'price1',
                    'width' => '120',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '数量',
                    'field' => 'num',
                    'width' => '120',
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
        'dataset' => 'pur/ReturnNoticeRecordDetailModel::get_by_page',
        'idField' => 'return_notice_record_detail_id',
        'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
        'CellEditing' => (1 == $response['data']['is_sure']) ? false : true,
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
                    /*
                      array(
                      'type' => 'text',
                      'show' => 1,
                      'title' => '单价',
                      'field' => 'price1',
                      'width' => '120',
                      'align' => ''
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
                      'title' => '批发价',
                      'field' => 'price',
                      'width' => '80',
                      'align' => '',
                      //'editor'=> "{xtype:'number'}"
                      ),
                     */
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '数量',
                        'field' => 'init_num',
                        'width' => '120',
                        'align' => '',
                    //'editor'=> "{xtype:'number'}"
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
            'dataset' => 'pur/ReturnNoticeRecordDetailModel::get_by_page_lof',
            'idField' => 'return_notice_record_detail_id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
                //'CellEditing'=>(1==$response['data']['is_sure'])?false:true,
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
                            'width' => '200',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'pur/PurStmLogModel::get_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'pur_stm_log_id',
                'params' => array('filter' => array('pid' => $response['data']['return_notice_record_id'], 'module' => 'pur_return_notice_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">

// 导出 
    function report_excel()
    {
        var param = "";
        param = param + "&id=" + id + "&return_notice_code=" + record_code + "&app_fmt=json";
        url = "?app_act=pur/return_notice_record/export_csv_list" + param;
        window.location.href = url;
    }

//取消确认
    function  do_re_sure(_index, return_notice_record_id) {
        url = '?app_act=pur/return_notice_record/do_sure';
        data = {id: return_notice_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }
    //确认
    function  do_sure(_index, return_notice_record_id) {
        url = '?app_act=pur/return_notice_record/do_sure';
        data = {id: return_notice_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
//打印
    function  do_print(_index, store_out_record_id) {
        var u = '?app_act=sys/flash_print/do_print'
        u += '&template_id=17&model=wbm/NoticeRecordModel&typ=default&record_ids=' + store_out_record_id
        window.open(u)
    }
//终止
    function do_stop(_index, return_notice_record_id) {

        url = '?app_act=pur/return_notice_record/do_stop';
        data = {id: return_notice_record_id};

        _do_operate(url, data, 'flush');
    }
//生成销货单
    function do_execute(_index, return_notice_record_id) {
        //判断是否有未入库销货单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/return_notice_record/out_relation'); ?>',
            data: {id: return_notice_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act==pur/return_notice_record/execute&return_notice_record_id=" + return_notice_record_id.toString();
                    _do_execute(url, 'table');

                } else {

                    if (ret.status == '-1') {
                        if (confirm("存在未出库的退货单，是否继续？")) {
                            url = "?app_act==pur/return_notice_record/execute&return_notice_record_id=" + return_notice_record_id.toString();
                            _do_execute(url, 'table');
                        }
                    }

                    // BUI.Message.Alert(ret.message, type);
                }
            }
        });

    }
</script>
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
                url: '?app_act=pur/return_notice_record/do_delete_detail_lof',
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
            url: '?app_act=pur/return_notice_record/do_delete_detail',
            data: {return_notice_record_detail_id: row.return_notice_record_detail_id, pid: row.pid, sku: row.sku},
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
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        table_listCellEditing.on('accept', function (record, editor) {
            if (parseInt(record.record.price) < 0 || parseInt(record.record.num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                location.reload();
                return;
            }
            $.post('?app_act=pur/return_notice_record/do_edit_detail',
                    {return_notice_record_id: record.record.pid, sku: record.record.sku, price: record.record.price, num: record.record.num, record_code: record_code, rebate: record.record.rebate},
                    function (result) {
                        var _res = result.res;
                        table_listStore.load();
                        $("#base_table tr").eq(2).find("td").eq(1).html(_res.num);
                        $("#base_table tr").eq(2).find("td").eq(2).html(_res.money);
                        logStore.load();
                    }, 'json');
        });
    }

    //扫描
    $("#scan_goods").click(function () {
        window.open("?app_act=common/record_scan/view_scan&dj_type=pur_return_notice&type=add_goods&record_code=<?php echo $response['data']['record_code']; ?>");
        return;
    });

</script>