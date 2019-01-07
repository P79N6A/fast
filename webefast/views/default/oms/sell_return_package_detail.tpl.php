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
    .like_link{text-decoration:underline;color:#428bca;cursor:pointer;}
</style>
<?php echo load_js("baison.js,record_table.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '退货包裹单',
    'links' => array(
        array('url' => 'oms/sell_return/package_list', 'target' => '_self', 'title' => '退货包裹单列表')
    ),
    'ref_table' => 'table'
));
?>

<ul id="tool" class="toolbar frontool">
    <?php if ($response['data']['return_order_status'] == 0 && $response['is_wms'] === FALSE && load_model('sys/PrivilegeModel')->check_priv('oms/sell_return/return_shipping')) : ?>
        <li class="li_btns"><button class="button button-primary" id="btn_opt_return_shipping">确认入库</button></li>
    <?php endif; ?>
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
    var return_package_code = "<?php echo $response['data']['return_package_code']; ?>";
    var id = "<?php echo $response['data']['return_package_id']; ?>";
    var store_code = "<?php echo $response['data']['store_code']; ?>";
    var is_lof = "<?php echo $response['lof_status']; ?>";
    var type = 1;
    var is_edit = true;
<?php if ($response['data']['return_order_status'] != 0) { ?>
        is_edit = false;
<?php } ?>
    var sell_return_code = "<?php echo $response['data']['sell_return_code'] ?>";
    var buyer_name = "<?php echo $response['data']['buyer_name'] ?>";
    var return_name = "<?php echo $response['data']['return_name'] ?>";
    var return_mobile = "<?php echo $response['data']['return_mobile'] ?>";
    var return_address = "<?php echo $response['data']['return_address'] ?>";
    var buyer_name_jiami;
    var return_name_jiami;
    var return_mobile_jiami;
    var return_address_jiami;
    if (buyer_name.indexOf('***') > -1) {
        buyer_name_jiami = set_show_text(return_package_code, buyer_name, 'buyer_name');
    } else {
        buyer_name_jiami = '<span>' + buyer_name + '</span>';
    }
    if (return_name.indexOf('***') > -1) {
        return_name_jiami = set_show_text(return_package_code, return_name, 'return_name');
    } else {
        return_name_jiami = '<span>' + return_name + '</span>';
    }
    if (return_mobile.indexOf('***') > -1) {
        return_mobile_jiami = set_show_text(return_package_code, return_mobile, 'return_mobile');
    } else {
        return_mobile_jiami = '<span>' + return_mobile + '</span>';
    }
    if (return_address.indexOf('***') > -1) {
        return_address_jiami = set_show_text(return_package_code, return_address, 'return_address');
    } else {
        return_address_jiami = '<span>' + return_address + '</span>';
    }

    function set_show_text(return_package_code, value, type) {
        return '<span class="like_link" onclick =\"show_safe_info(this,\'' + return_package_code + '\',\'' + type + '\');\">' + value + '</span>';
    }
    var data = [
        {
            "name": "return_package_code",
            "title": "单据编号",
            "value": "<?php echo $response['data']['return_package_code'] ?>",
            "type": "input"
        },
        {
            "name": "stock_date",
            "title": "业务日期",
            "value": "<?php echo $response['data']['stock_date'] ?>",
            "type": "time",
            "edit": true
        },
        {
            "name": "store_code",
            "title": "退货仓库",
            "value": "<?php echo $response['data']['store_code'] ?>",
            "type": "select",
            "edit": true,
            "data":<?php echo $response['selection']['store'] ?>
        },
        {
            "name": "buyer_name",
            "title": "买家昵称",
            "value": buyer_name_jiami,
            "type": "input"
        },
        {
            "name": "return_name",
            "title": "退货人姓名",
            "value": return_name_jiami,
            "type": "input",
            "edit": true
        },
        {
            "name": "return_mobile",
            "title": "退货人手机",
            "value": return_mobile_jiami,
            "type": "input",
            "edit": true
        },
        {
            "name": "addr",
            "title": "退货地址",
            "value": return_address_jiami
        }
    ];
    if (sell_return_code != '') {
        data.push(
                {
                    "name": "shop_code",
                    "title": "店铺",
                    "value": "<?php echo $response['data']['shop_code'] ?>",
                    "type": "select",
                    "data":<?php echo $response['selection']['shop'] ?>
                }
        );
    } else {
        data.push(
                {
                    "name": "shop_code",
                    "title": "店铺",
                    "value": "<?php echo $response['data']['shop_code'] ?>",
                    "type": "select",
                    "edit": true,
                    "data":<?php echo $response['selection']['shop'] ?>
                }
        );
    }
    data.push(
            {
                "name": "return_express_code",
                "title": "配送方式",
                "value": "<?php echo $response['data']['return_express_code'] ?>",
                "type": "select",
                "edit": true,
                "data":<?php echo $response['selection']['express_code'] ?>
            },
            {
                "name": "return_express_no",
                "title": "快递单号",
                "value": "<?php echo $response['data']['return_express_no'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "sell_return_code",
                "title": "关联退单号",
                "value": "<?php echo $response['data']['sell_return_code'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "sell_record_code",
                "title": "关联订单号",
                "value": "<?php echo $response['data']['sell_record_code'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "deal_code",
                "title": "交易号",
                "value": "<?php echo $response['data']['deal_code'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "remark",
                "title": "备注",
                "value": "<?php echo $response['data']['remark'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "return_buyer_memo",
                "title": "买家退单说明",
                "value": "<?php echo $response['data']['return_buyer_memo'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "return_remark",
                "title": "卖家退单备注",
                "value": "<?php echo $response['data']['return_remark'] ?>",
                "type": "input",
                "edit": true
            },
            {
                "name": "is_exchange_goods",
                "title": "是否换货",
                "value": "<?php echo $response['data']['is_exchange_goods'] == 1 ? '是' : '否'; ?>",
                "type": "input"
            }
    );
    jQuery(function () {
        var r = new record_table();
        r.init({
            "id": "panel_html",
            "data": data,
            "is_edit": is_edit,
            "edit_url": "?app_act=oms/sell_return/package_edit",
            "check_url": "?app_act=oms/sell_return/check_express_code_and_no",
            "check_params": "<?php echo $response['data']['return_express_no'] ?>"
        });


        get_goods_panel({
            "id": "btnSelectGoods",
            'param': {'store_code': '<?php echo $response['data']['store_code'] ?>', 'lof_status': 1, 'return_package_code': return_package_code},
            "callback": addgoods
        });


        $('#btnSearchGoods').on('click', function () {
            tableStore.load({'code_name': $('#goods_code').val()});
        });
        $('#btnSearchGoods').on('click', function () {
            if (is_lof == 1) {
                table_lofStore.load({'code_name': $('#goods_code').val()});
                tableStore.load({'code_name': $('#goods_code').val()});
            } else {
                tableStore.load({'code_name': $('#goods_code').val()});
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
        $.post('?app_act=oms/sell_return/do_add_detail&id=' + id + '&store_code=' + store_code, {data: select_data}, function (result) {
            if (1 != result.status) {
                //添加失败
                top.BUI.Message.Alert(result.message, function () {

                }, 'error');
            }
            if (typeof _thisDialog.callback == "function") {
                _thisDialog.callback(this);
            }
        }, 'json');

    }

</script>

<div class="panel record_table" id="panel_html"></div>
<div class="panel">
    <div class="panel-header">
        <h3 class="">详细信息 <i class="icon-folder-open toggle"></i></h3>
    </div>
    <div class="panel-body">
        <div class="row">
            <b>请输入商品编码</b>
            <input type="text" class="input" value="" id="goods_code"/>
            <button type="button" class="button button-info" value="搜索商品" id="btnSearchGoods"><i class="icon-search icon-white"></i> 搜索商品</button>
            <?php if ($response['data']['return_order_status'] == 0) { ?>
                <div style ="float:right;">
                    <button type="button" class="button button-success" value="新增商品" id="btnSelectGoods" ><i class="icon-plus-sign icon-white"></i> 新增商品</button>
                </div>
            <?php } ?>
            <?php if ($response['lof_status'] == 1) { ?>
                <div id="showbatch"></div>
                <div id="shownobatch"></div>
            <?php } ?>
        </div>
    </div>
    <?php
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品图片',
                    'field' => 'goods_thumb_img_src',
                    'width' => '120',
                    'align' => ''
                ),
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
                    'title' => '规格1',
                    'field' => 'spec1_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格2',
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
                    'title' => '买家申请退货数量',
                    'field' => 'apply_num',
                    'width' => '120',
                    'align' => '',
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '实际退货数(入库数)',
                    'field' => 'num',
                    'width' => '130',
                    'align' => '',
                    'editor' => $response['data']['return_order_status'] == 0 ? "{xtype:'number'}" : ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '库位',
                    'field' => 'shelf_name',
                    'width' => '120',
                    'align' => 'center',
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
                            'show_cond' => $response['data']['return_order_status'] == 0 ? '1' : '0'
                        ),
                    ),
                )
            )
        ),
        'dataset' => 'oms/ReturnPackageModel::get_detail_by_page',
        'idField' => 'return_package_detail_id',
        'params' => array('filter' => array('return_package_code' => $response['data']['return_package_code'])),
        'CellEditing' => (0 == $response['data']['return_order_status']) ? TRUE : false,
    ));
    ?>
    <?php
    if ($response['lof_status'] == 1) {
        render_control('DataTable', 'table_lof', array(
            'conf' => array(
                'list' => array(
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '商品图片',
                        'field' => 'goods_thumb_img_src',
                        'width' => '120',
                        'align' => ''
                    ),
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
                        'title' => '规格1',
                        'field' => 'spec1_name',
                        'width' => '80',
                        'align' => ''
                    ),
                    array(
                        'type' => 'text',
                        'show' => 1,
                        'title' => '规格2',
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
                        'title' => '实际退货数(入库数)',
                        'field' => 'num',
                        'width' => '130',
                        'align' => '',
                    ),
                )
            ),
            'dataset' => 'oms/ReturnPackageModel::get_lof_detail_by_page',
            'idField' => 'return_package_detail_id',
            'params' => array('filter' => array('return_package_code' => $response['data']['return_package_code'])),
        ));
    }
    ?>
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
                            'field' => 'user_name',
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
                            'field' => 'create_time',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '状态',
                            'field' => 'status_name',
                            'width' => '80',
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
                'dataset' => 'oms/ReturnPackageModel::get_package_action_by_page',
                //'queryBy' => 'searchForm',
                'idField' => 'return_package_action_id',
                'params' => array('filter' => array('return_package_code' => $response['data']['return_package_code'])),
            ));
            ?>
        </div>
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
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

</script>

<?php echo load_js('comm_util.js') ?>
<script type="text/javascript">
    var return_package_code = "<?php echo $response['data']['return_package_code']; ?>";
    var sell_return_code = "<?php echo $response['data']['sell_return_code']; ?>";
    var sell_return_scanning = "<?php echo $response['sell_return_scanning']; ?>";
    var sell_return_code = '<?php echo $response['data']['sell_return_code'] ?>';
    var sell_record_code = '<?php echo $response['data']['sell_record_code'] ?>';
    var is_allowed_exceed = '<?php echo $response['is_allowed_exceed']; ?>';
    $(function () {
        jQuery("#panel_html .btnFormEdit").click(function () {
            //$("#addr").show();
            var html = '';
            html += '<select id="country" name="country" onChange= "change(this,0);" data-rules="{required : true}">';
            html += '<option value ="">请选择国家</option>';
<?php foreach ($response['area']['country'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['return_country']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';

            html += '<select id="province" name="province"  onChange= "change(this,1);" data-rules="{required : true}">';
            html += '<option value ="">请选择省</option>';
<?php foreach ($response['area']['province'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['return_province']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';

            html += '<select id="city" name="city"  onChange= "change(this,2);" data-rules="{required : true}">';
            html += '<option value ="">请选择市</option>';
<?php foreach ($response['area']['city'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['return_city']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';

            html += '<select id="district" name="district"   data-rules="{required : true}">';
            html += '<option value ="">请选择区县</option>';
<?php foreach ($response['area']['district'] as $k => $v) { ?>
                html += '<option  value ="<?php echo $v['id']; ?>" <?php if ($v['id'] == $response['data']['return_district']) { ?> selected <?php } ?> ><?php echo $v['name']; ?></option>';
<?php } ?>
            html += '</select>';
            html += '<input type="text" name="return_addr" value="<?php echo $response['data']['return_addr'] ?>" id="return_addr" />';
            $("#addr").html(html);
            var url = "?app_act=oms/sell_return/get_package_key_data&app_fmt=json";
            var param = {return_package_code: return_package_code};
            $('#return_name>input').val('');
            $('#return_mobile>input').val('');
            $('#return_phone>input').val('');
            $('#return_addr').val('');
            $.post(url, param, function (ret) {
                $('#return_name>input').val(ret.return_name);
                $('#return_mobile>input').val(ret.return_mobile);
                $('#return_phone>input').val(ret.return_phone);
                $('#return_addr').val(ret.return_addr);
            }, 'json');

        });

        jQuery("#panel_html .btnFormCancel").click(function () {
            location.reload();
        });
    });

    if (is_lof == 1) {
        $(function () {
            $("#showbatch").click(function () {
                $('#table_datatable').hide();
                $('#table_lof_datatable').show();
                $('#showbatch').removeClass("curr");
                $('#shownobatch').removeClass("curr");

            });
            $("#shownobatch").click(function () {
                $('#table_lof_datatable').hide();
                $('#table_datatable').show();
                $('#showbatch').addClass("curr");
                $('#shownobatch').addClass("curr");
            });
            $("#showbatch").click();
        });
    }

    function do_delete_detail(_index, row) {
        $.post('?app_act=oms/sell_return/check_num', {return_package_code: row.return_package_code, sku: row.sku}, function (ret) {
            if (ret.status == -1) {
                BUI.Message.Show({
                    title: '提示',
                    msg: ret.message,
                    icon: 'question',
                    buttons: [
                        {
                            text: '是',
                            elCls: 'button button-primary',
                            handler: function () {
                                delete_detail(row.return_package_detail_id, row.return_package_code);
                                this.close();
                            }
                        },
                        {
                            text: '否',
                            elCls: 'button',
                            handler: function () {
                                this.close();
                            }
                        }
                    ]
                });
            } else {
                delete_detail(row.return_package_detail_id, row.return_package_code);
            }
        }, 'json');
    }

    function delete_detail(_return_package_detail_id, _return_package_code) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('oms/sell_return/do_delete_package_detail'); ?>',
            data: {return_package_detail_id: _return_package_detail_id, return_package_code: _return_package_code},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    location.reload();
                } else {
                    BUI.Message.Alert(ret.message, function () {
                        location.reload();
                    }, type);
                }
            }
        });
    }



    function change(obj, level) {
        var url = '<?php echo get_app_url('base/store/get_area'); ?>';
        var parent_id = $(obj).val();
        areaChange(parent_id, level, url);
    }

    $("#btn_opt_return_shipping").click(function () {
        if (sell_return_scanning == 1) {
            var url = '?app_act=oms/sell_return/sell_return_scanning_view&type=package&sell_return_code=' + sell_return_code + "&return_package_code=" + return_package_code;
            openPage(window.btoa(url), url, '收货服务单收货扫描');

        } else {
            var params = {"sell_return_code": sell_return_code};
            params.return_package_code = return_package_code;
            $.post("?app_act=oms/sell_return/opt_return_shipping", params, function (data) {
                if (data.status == 1) {
                    location.reload();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        }
    });
    if (typeof tableCellEditing != "undefined") {
        //列表区域,数量修改回调操作 +++++++++++++++++++++++++++++++++++++++++++
        tableCellEditing.on('accept', function (record, editor) {
            //console.log(record.record);

            if (parseInt(record.record.num) < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
//        if(record.record.num > record.record.apply_num && is_allowed_exceed == 1) {
//            BUI.Message.Alert(record.record.barcode+'条码，实际入库数不能大于通知数', 'error');
//            tableStore.load();
//            return;
//        }
            $.post('?app_act=oms/sell_return/do_edit_detail',
                    {return_package_code: return_package_code, num: record.record.num, sku: record.record.sku, barcode: record.record.barcode, sell_return_code: sell_return_code},
                    function (result) {
                        if (result.status < 0) {
                            BUI.Message.Alert(result.message, 'error');
                            tableStore.load();
                        } else {
                            window.location.reload();
                        }
                    }, 'json');
        });
    }
//        function show_safe_info(return_package_code,key){
//         var url = "?app_act=oms/sell_return/get_package_key_data&app_fmt=json";
//        $.post(url,{'return_package_code':return_package_code,key:key},function(ret){
//            BUI.Message.Alert(ret[key],'info');
//        },'json');
//    }

    //解密
    function show_safe_info(obj, return_package_code, key) {
        var url = "?app_act=oms/sell_return/get_package_key_data&app_fmt=json";
        $.post(url, {'return_package_code': return_package_code, key: key}, function (ret) {
            if (ret[key] == null) {
                BUI.Message.Tip('解密出现异常！', 'error');
                return;
            }
            $(obj).html(ret[key]);
            $(obj).attr('onclick', '');
            $(obj).removeClass('like_link');
        }, 'json');
    }
</script>
<iframe src="" id="print_iframe" style="width:0px;height:0px;" ></iframe>