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
render_control('PageHead', 'head1', array('title' => '经销采购单详情',
    'links' => array(
        array('url' => 'fx/purchase_record/do_list', 'target' => '_self', 'title' => '经销采购单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool frontool_center">
    <li class="li_btns">
        <?php if ($response['data']['is_check'] != 1 && $response['data']['is_settlement'] != 1 && $response['data']['is_deliver'] != 1) { ?>
            <a class="button button-primary" href="javascript:do_check(this, '<?php echo $response['data']['purchaser_record_id']; ?>')">分销商确认</a>
        <?php } ?>
        <?php if ($response['data']['is_check'] == 1 && $response['data']['is_settlement'] != 1 && $response['data']['is_deliver'] != 1) { ?>
            <a class="button button-primary" href="javascript:do_un_check(this,'<?php echo $response['data']['purchaser_record_id']; ?>')">分销商取消确认</a>
        <?php } ?>

        <?php if ($response['data']['is_check'] == 1 && $response['data']['is_settlement'] != 1 && $response['data']['is_deliver'] != 1) { ?>
            <a class="button button-primary" href="javascript:do_settlement(this, '<?php echo $response['data']['record_code']; ?>','sales_settlement')">分销商结算</a>
        <?php } ?>
        <?php if ($response['data']['is_check'] == 1 && $response['data']['is_settlement'] == 1 && $response['data']['is_deliver'] == 0) { ?>
            <a class="button button-primary" href="javascript:do_unsettlement(this, '<?php echo $response['data']['record_code']; ?>','un_sales_settlement')">分销商取消结算</a>
        <?php } ?>
        <?php #if ($response['data']['is_check'] == 1 && $response['data']['is_settlement'] == 1 && $response['data']['is_deliver'] != 1) { ?>
        <!--<a class="button button-primary" href="javascript:do_delivery(this, '<?php // echo $response['data']['record_code']; ?>')">供应商出库</a>-->
        <?php #}?>
    </li>
    <button type="button" class="button button-info" style="background-color: #1695ca;"  onclick="report_excel()"  value="导出" id="btn-csv">导出</button>
    <div class="front_close">&lt;</div>      
</ul>
<script>
    $(function() {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function() {
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
    var id = "<?php echo $response['data']['purchaser_record_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var login_type = "<?php echo $response['login_type']; ?>";
    var custom_code = "<?php echo $response['data']['custom_code'] ?>";
    var type = 1;
    var is_edit = true;
    <?php if($response['data']['is_settlement']) { ?>
        var is_edit = false;
    <?php } ?>
<?php if (1 == $response['data']['is_check']) { ?>
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
                "title": "单据状态",
                "value": "<?php echo $response['data']['record_status']?>"
            },
            {
                "name": "custom_code",
                "title": "分销商",
                "value": "<?php echo $response['data']['custom_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['custom'] ?>
            },
            {
                "name": "deliver_time",
                "title": "出库时间",
                "value": "<?php echo $response['data']['deliver_time'] ?>",
                "type": "time"
            },
            {
                "name": "store_code",
                "title": "仓库",
                "value": "<?php echo $response['data']['store_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['store'] ?>,
            },
            {
                "name": "record_time",
                "title": "业务日期",
                "value": "<?php echo $response['data']['record_time'] ?>",
                "type": "time"
            },
            {
                "name": "num",
                "title": "计划采购总数",
                "value": "<?php echo $response['data']['num'] ?>",
            },
            {
                "name": "finish_num",
                "title": "实际出库总数",
                "value": "<?php echo $response['data']['finish_num'] ?>",
            },
            {
                "name": "sum_money",
                "title": "采购总金额",
                "value": "<?php echo $response['data']['sum_money'] ?>",
            },
            {
                "name": "express_money",
                "title": "运费",
                "value": "<?php echo $response['data']['express_money'] ?>"
            },
            {
                "name": "remark",
                "title": "备注",
                "value": "<?php echo $response['data']['remark'] ?>",
                "type": "input",
                "edit": true,
            },
        ];
        var delivery_data = [
            {
                "name": "contact_person",
                "title": "联系人",
                "value": "<?php echo!empty($response['data']['contact_person']) ? $response['data']['contact_person'] : $response['data']['custom_info']['contact_person']; ?>",
                "type": "input",
            },
            {
                "name": "mobile",
                "title": "收货人手机",
                "value": "<?php echo!empty($response['data']['mobile']) ? $response['data']['mobile'] : $response['data']['custom_info']['mobile']; ?>",
                "type": "input",
            },
            {
                "name": "addr",
                "title": "地址",
                "value": "<?php echo!empty($response['data']['addr']) ? $response['data']['addr'] : $response['data']['custom_info']['address'] ?>",
            },
            {
                "name": "express_code",
                "title": "配送方式",
                "value": "<?php echo $response['data']['express_code'] ?>",
                "type": "select",
                "data":<?php echo $response['selection']['express_code'] ?>,
            },
            {
                "name": "express_no",
                "title": "快递单号",
                "value": "<?php echo $response['data']['express_no'] ?>",
                "type": "input",
            }

        ];
<?php } else { ?>
        if (login_type == 2) {
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
                    "value": "<?php echo $response['data']['order_time'] ?>",
                },
                {
                    "title": "单据状态",
                    "value": "<?php echo $response['data']['record_status']?>"
                },
                {
                    "name": "custom_code",
                    "title": "分销商",
                    "value": "<?php echo $response['data']['custom_name'] ?>",
                },
                {
                    "name": "deliver_time",
                    "title": "出库时间",
                    "value": "<?php echo $response['data']['deliver_time'] ?>",
                    "type": "time"
                },
                {
                    "name": "store_code",
                    "title": "仓库",
                    "value": "<?php echo $response['data']['store_code'] ?>",
                    "type": "select",
                    "data":<?php echo $response['selection']['store'] ?>,
                    //"edit": true,
                },
                {
                    "name": "record_time",
                    "title": "业务日期",
                    "value": "<?php echo $response['data']['record_time'] ?>",
                    "type": "time",
                    //"edit": true,
                },
                {
                    "name": "num",
                    "title": "计划采购总数",
                    "value": "<?php echo $response['data']['num'] ?>",
                },
                {
                    "name": "finish_num",
                    "title": "实际出库总数",
                    "value": "<?php echo $response['data']['finish_num'] ?>",
                },
                {
                    "name": "sum_money",
                    "title": "采购总金额",
                    "value": "<?php echo $response['data']['sum_money'] ?>",
                },
                {
                    "name": "express_money",
                    "title": "运费",
                    "value": "<?php echo $response['data']['express_money'] ?>",
                    "type": "input",
                    //"edit": true,
                },
                {
                    "name": "remark",
                    "title": "备注",
                    "value": "<?php echo $response['data']['remark'] ?>",
                    "type": "input",
                    "edit": true,
                },
            ];
        } else {
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
                    "value": "<?php echo $response['data']['order_time'] ?>",
                },
                {
                    "title": "单据状态",
                    "value": "<?php echo $response['data']['record_status']?>"
                },
                {
                    "name": "custom_code",
                    "title": "分销商",
                    "value": "<?php echo $response['data']['custom_code'] ?>",
                    "type": "select",
                    //"edit": true,
                    "data":<?php echo $response['selection']['custom'] ?>
                },
                {
                    "name": "deliver_time",
                    "title": "出库时间",
                    "value": "<?php echo $response['data']['deliver_time'] ?>",
                    "type": "time"
                },
                {
                    "name": "store_code",
                    "title": "仓库",
                    "value": "<?php echo $response['data']['store_code'] ?>",
                    "type": "select",
                    "data":<?php echo $response['selection']['store'] ?>,
                    "edit": true,
                },
                {
                    "name": "record_time",
                    "title": "业务日期",
                    "value": "<?php echo $response['data']['record_time'] ?>",
                    "type": "time",
                    "edit": true,
                },
                {
                    "name": "num",
                    "title": "计划采购总数",
                    "value": "<?php echo $response['data']['num'] ?>",
                },
                {
                    "name": "finish_num",
                    "title": "实际出库总数",
                    "value": "<?php echo $response['data']['finish_num'] ?>",
                },
                {
                    "name": "sum_money",
                    "title": "采购总金额",
                    "value": "<?php echo $response['data']['sum_money'] ?>",
                },
                {
                    "name": "express_money",
                    "title": "运费",
                    "value": "<?php echo $response['data']['express_money'] ?>",
                    "type": "input",
                    "edit": true,
                },
                {
                    "name": "remark",
                    "title": "备注",
                    "value": "<?php echo $response['data']['remark'] ?>",
                    "type": "input",
                    "edit": true,
                },
            ];

        }

        var delivery_data = [
            {
                "name": "contact_person",
                "title": "联系人",
                "value": "<?php echo!empty($response['data']['contact_person']) ? $response['data']['contact_person'] : $response['data']['custom_info']['contact_person']; ?>",
                "type": "input",
            },
            {
                "name": "mobile",
                "title": "收货人手机",
                "value": "<?php echo!empty($response['data']['mobile']) ? $response['data']['mobile'] : $response['data']['custom_info']['mobile']; ?>",
                "type": "input",
                "edit": true,
            },
            {
                "name": "addr",
                "title": "地址",
                "value": "<?php echo!empty($response['data']['addr']) ? $response['data']['addr'] : $response['data']['custom_info']['address'] ?>",
                "edit": true,
            },
            {
                "name": "express_code",
                "title": "配送方式",
                "value": "<?php echo $response['data']['express_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['express_code'] ?>,
            },
            {
                "name": "express_no",
                "title": "快递单号",
                "value": "<?php echo $response['data']['express_no'] ?>",
                "type": "input",
                "edit": true
            }
        ];
<?php } ?>
    jQuery(function() {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=fx/purchase_record/do_edit"
        });
        var delivery_record = new record_table();
        delivery_record.init({
            "id": "panel_deliverty_html",
            "title": "配送信息",
            "data": delivery_data,
            "is_edit": is_edit,
            "edit_url": "?app_act=fx/purchase_record/do_edit"
        });

        jQuery("#showbatch").bind("click", showbatch);
        jQuery("#shownobatch").bind("click", shownobatch);

        get_goods_panel({
            "id": "btnSelectGoods",
            'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'custom_code' : custom_code},
            "callback": addgoods
        });

        $('#btnSearchGoods').on('click', function() {
            table_listStore.load({'code_name': $('#goods_code').val()});
            //table_listStore.load({'difference_models': $('#difference_models').val()});
            if (<?php echo $response['lof_status'] ?> == 1) {
                table_lof_listStore.load({'code_name': $('#goods_code').val()});
                //table_lof_listStore.load({'difference_models': $('#difference_models').val()});
            }
        });
        jQuery("#panel_deliverty_html .btnFormEdit").click(function() {
            //$("#addr").show();
            var html = '';
            html += '<select id="country" name="country"  onChange= "change(this,0);" data-rules="{required : true}">';
            html += '<option value ="">请选择国家</option>';
<?php foreach ($response['area']['country'] as $k => $v) {  ?>
                html += '<option  value ="<?php echo $v['id'];  ?>" <?php if ($v['id'] == $response['data']['country']) {  ?> selected <?php }  ?> ><?php echo $v['name'];  ?></option>';
<?php }  ?>
            html += '</select>';
            
            html += '<select id="province" name="province"  onChange= "change(this,1);" data-rules="{required : true}">';
            html += '<option value ="">请选择省</option>';
<?php foreach ($response['area']['province'] as $k => $v) {  ?>
                html += '<option  value ="<?php echo $v['id'];  ?>" <?php if ($v['id'] == $response['data']['province']) {  ?> selected <?php }  ?> ><?php echo $v['name'];  ?></option>';
<?php }  ?>
            html += '</select>';

            html += '<select id="city" name="city"  onChange= "change(this,2);" data-rules="{required : true}">';
            html += '<option value ="">请选择市</option>';
<?php foreach ($response['area']['city'] as $k => $v) {  ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['city']) {  ?> selected <?php }  ?> ><?php echo $v['name'];  ?></option>';
<?php }  ?>
            html += '</select>';

            html += '<select id="district" name="district"   data-rules="{required : true}">';
            html += '<option value ="">请选择区县</option>';
<?php foreach ($response['area']['district'] as $k => $v) {  ?>
                html += '<option  value ="<?php echo $v['id'];  ?>" <?php if ($v['id'] == $response['data']['district']) {  ?> selected <?php }  ?> ><?php echo $v['name'];  ?></option>';
<?php }  ?>
            html += '</select>';
            html += '<br /><input type="text" id="address" name="address"   data-rules="{required : true}" style="width: 250px;" value="<?php echo $response['data']['address']  ?>">';
            $("#addr").html(html);
        });

        jQuery("#panel_deliverty_html .btnFormCancel").click(function() {
            location.reload();
        });
    })

    function change(obj, level) {
        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        var parent_id = $(obj).val();
        areaChange(parent_id, level, url);
    }

    function addgoods(obj) {
        var data = top.skuSelectorStore.getResult();
        var select_data = {};
        var di = 0;
        BUI.each(data, function(value, key) {
            if (top.$("input[name='num_" + value.sku + "']").val() != '' && top.$("input[name='num_" + value.sku + "']").val() != undefined) {
                if (top.$("input[name='num_" + value.sku + "']").val() > 0) {
                    value.num = top.$("input[name='num_" + value.sku + "']").val();
                    value.lof_no = top.$("input[name='lof_no_" + value.sku + "']").val();
                    value.production_date = top.$("input[name='production_date_" + value.sku + "']").val();
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
        $.post('?app_act=fx/purchase_record/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function(result) {
            if (true != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function() {
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

<div class="panel record_table" id="panel_deliverty_html"> 

</div>

<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>请输入商品编码</b>
            <input type="text" placeholder="商品编码/商品条形码" class="input" value="" id="goods_code"/>
            <?php if (0 == $response['data']['is_check']) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="新增商品导入" id="btnimport" ><i class="icon-plus-sign icon-white"></i> 商品导入</button>
                    &nbsp;
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" style ="float:right;"><i class="icon-plus-sign icon-white"></i> 新增商品</button>
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
                    'width' => '100',
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
                    'width' => '150',
                    'align' => '',
                    'id' => 'barcode'
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '计划采购数',
                    'field' => 'num',
                    'width' => '80',
                    'align' => '',
                    'editor' => $response['lof_status'] == 0 ? "{xtype:'number'}" : ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '实际出库数',
                    'field' => 'finish_num',
                    'width' => '80',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '差异数',
                    'field' => 'num_differ',
                    'width' => '60',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '采购单价',
                    'field' => 'price',
                    'width' => '80',
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
                    'width' => '50',
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
        'dataset' => 'fx/PurchaseRecordDetailModel::get_by_page',
        'idField' => 'purchase_record_detail_id',
        'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
        'CellEditing' => (1 == $response['data']['is_check']) ? false : true,
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
                        'width' => '150',
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
                        'title' => '计划采购数',
                        'field' => 'lof_num',
                        'width' => '80',
                        'align' => '',
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '实际出库数',
                        'field' => 'fill_num',
                        'width' => '80',
                        'align' => '',
                        'editor' => $response['lof_status'] == 0 ? "{xtype:'number'}" : ''
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
                        'type' => 'text',
                        'show' => 1,
                        'title' => '采购单价',
                        'field' => 'price',
                        'width' => '80',
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
                        'width' => '60',
                        'align' => '',
                        'buttons' => array(
                            array(
                                'id' => 'del',
                                'title' => '删除',
                                'callback' => 'do_delete_detail_lof',
                                'show_cond' => 'obj.is_check == 0'
                            ),
                        ),
                    )
                )
            ),
            'dataset' => 'fx/PurchaseRecordDetailModel::get_by_page_lof',
            'idField' => 'id',
            'params' => array('filter' => array('record_code' => $response['data']['record_code'])),
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
                'params' => array('filter' => array('pid' => $response['data']['purchaser_record_id'], 'module' => 'fx_purchase_record')),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<?php echo load_js('comm_util.js') ?>

<script type="text/javascript">
    function  do_check(_index, purchaser_record_id) {
        url = '?app_act=fx/purchase_record/do_check';
        data = {id: purchaser_record_id, type: 'enable'};
        _do_operate(url, data, 'flush');
    }
    function  do_un_check(_index, purchaser_record_id) {
        url = '?app_act=fx/purchase_record/do_check';
        data = {id: purchaser_record_id, type: 'disable'};
        _do_operate(url, data, 'flush');
    }

    //打印
    function  do_print(_index, purchaser_record_id) {
        var u = '?app_act=sys/flash_print/do_print';
        u += '&template_id=17&model=pur/PurchaseRecordModel&typ=default&record_ids=' + purchaser_record_id;
        window.open(u)
    }
</script>
<script type="text/javascript">
<?php if ($response['lof_status'] == 1): ?>
        jQuery(function() {
            jQuery("#showbatch").click(function() {
                $('#table_list_datatable').hide();
                $('#table_lof_list_datatable').show();
                jQuery('#showbatch').removeClass("curr");
                jQuery('#shownobatch').removeClass("curr");

            });
            jQuery("#shownobatch").click(function() {
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
                url: '?app_act=fx/purchase_record/do_delete_detail_lof',
                data: {id: row.id},
                success: function(ret) {
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
    var url = "<?php echo get_app_url('prm/goods/detail&action=do_edit'); ?>";
    var id = '<?php echo $response['data']['purchaser_record_id'];?>';
    //删除单据明细++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    function do_delete_detail(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=fx/purchase_record/do_delete_detail',
            data: {purchaser_record_detail_id: row.purchaser_record_detail_id, pid: row.pid, sku: row.sku},
            success: function(ret) {
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
        table_listCellEditing.on('accept', function(record, editor) {
            if (record.record.num < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                table_listStore.load();
                return;
            }
            $.post(
                '?app_act=fx/purchase_record/do_edit_detail',
                {pid: record.record.pid, record_code: record_code, sku: record.record.sku, rebate: record.record.rebate, num: record.record.num,price:record.record.price},
                function(result) {
                    window.location.reload();
                },
            'json');
        });
    }

    var is_lof = <?php echo $response['lof_status'] ?>;
    function report_excel()
    {
        var param = "";
        param = param + "&id=" + id + "&record_code=" + record_code + "&code_name=" + $('#goods_code').val() + "&app_fmt=json&is_lof=" + is_lof;
        url = "?app_act=fx/purchase_record/export_csv_list" + param;

        window.location.href = url;
    }


    jQuery(function() {
        $('#btnimport').on('click', function() {
            url = "?app_act=fx/purchase_record/importGoods&id=" + id;
            new ESUI.PopWindow(url, {
                title: "导入商品",
                width: 880,
                height: 400,
                onBeforeClosed: function() {
                    location.reload();
                    //table_listStore.load();
                    //table_lof_listStore.load();
                },
                onClosed: function() {
                    //刷新数据

                }
            }).show();
        });
    });

    function do_settlement(_index, record_code, type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=fx/purchase_record/do_settlement',
            data: {record_code: record_code, type: type},
            success: function(ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type == 'success') {
                    if(login_type != 2) {
                        var notice_record_id = ret.data;
                        if (confirm("生成批发通知单，是否打开批发通知单？")) {
                            url = "?app_act==wbm/notice_record/execute&notice_record_id=" + notice_record_id.toString();
                            openPage('<?php echo base64_encode('?app_act=wbm/notice_record/view&notice_record_id') ?>' + notice_record_id, '?app_act=wbm/notice_record/view&notice_record_id=' + notice_record_id, '销货通知单详情');
                        }
                    } else {
                        BUI.Message.Alert('结算成功',type);
                    }
                    setTimeout(location.reload(),4000);
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_unsettlement(_index, record_code, type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=fx/purchase_record/do_unsettlement',
            data: {record_code: record_code, type: type},
            success: function(ret) {
                var type = (ret.status == 1) ? 'success' : 'error';
                if (type != 'success') {
                    BUI.Message.Alert(ret.message, type);
                } else {
                    location.reload();
                }
            }
        });
    }

    function do_delivery() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=fx/purchase_record/do_delivery',
            data: {record_code: record_code, type: type},
            success: function(ret) {
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

