<?php
$links = array();
if ($response['priv']['add_goods'] == TRUE) {
    $links[] = array('type' => 'js', 'js' => 'show_select_shop()', 'title' => '添加商品', 'is_pop' => false);
}
if ($response['priv']['import_goods'] == TRUE) {
    $links[] = array('url' => "prm/shop_goods/import_goods", 'pop_size' => '500,350', 'is_pop' => 'true', 'title' => '导入商品');
}

render_control('PageHead', 'head1', array('title' => '门店商品',
    'links' => $links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
    array(
        'label' => '导出',
        'id' => 'exprot_list',
    )
);

render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '支持模糊查询',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '启用状态',
            'type' => 'select',
            'id' => 'status',
            'data' => ds_get_select_by_field('clerkstatus', 1)
        ),
        array(
            'label' => '门店',
            'type' => $response['login_type'] > 0 ? 'select' : 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_select_entity($type),
            'value' => $response['oms_shop_code'],
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => $response['priv']['update_status'] == TRUE ? 1 : 0,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
//array('id' => 'delete', 'title' => '删除', 'priv' => 'prm/shop_goods/delete_goods',
//    'callback' => 'do_delete', 'show_cond' => 'obj.status == 0', 'confirm' => '确认要删除吗？'),
                    array('id' => 'disable', 'title' => '停用', 'priv' => 'prm/shop_goods/update_active',
                        'callback' => 'do_disable', 'show_cond' => 'obj.status != 0', 'confirm' => '确认要停用吗？'),
                    array('id' => 'enable', 'title' => '启用', 'priv' => 'prm/shop_goods/update_active',
                        'callback' => 'do_enable', 'show_cond' => 'obj.status != 1',
                        'confirm' => '确认要启用吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'title' => '启用状态',
                'field' => "is_active",
                'width' => '70',
                'align' => '',
                'format' => array('type' => 'map_checked'),
            ),
            array(
                'type' => 'text',
                'title' => '商品图片',
                'field' => "pic_path",
                'width' => '100',
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
                'title' => '分类',
                'field' => 'category_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '季节',
                'field' => 'season_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '年份',
                'field' => 'year_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '吊牌价',
                'field' => 'sell_price',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属门店',
                'field' => 'shop_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '门店售价',
                'field' => 'goods_price',
                'width' => '80',
                'align' => '',
                'editor' => $response['priv']['update_price'] == TRUE ? "{xtype:'number'}" : ''
            ),
        )
    ),
    'dataset' => 'prm/ShopGoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'shop_sku_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'shop_goods_list', 'name' => '门店商品列表', 'export_type' => 'file'), //, 'export_type' => 'file'
    'CellEditing' => true,
    'CheckSelection' => true,
    'init' => $response['login_type'] ? '' : 'nodata',
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品编码', 'type' => 'text', 'width' => '180', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '180', 'field' => 'barcode'),
            array('title' => $response['spec']['goods_spec1'] . '编码', 'type' => 'text', 'width' => '160', 'field' => 'spec1_code'),
            array('title' => '商品' . $response['spec']['goods_spec1'], 'type' => 'text', 'width' => '160', 'field' => 'spec1_name'),
            array('title' => $response['spec']['goods_spec2'] . '编码', 'type' => 'text', 'width' => '160', 'field' => 'spec2_code'),
            array('title' => '商品' . $response['spec']['goods_spec2'], 'type' => 'text', 'width' => '160', 'field' => 'spec2_name'),
            array('title' => '门店售价', 'type' => 'text', 'width' => '160', 'field' => 'sku_price', 'editor' => $response['priv']['update_price'] == TRUE ? "{xtype:'number'}" : '')
        ),
        'page_size' => 10,
        'url' => get_app_url('prm/shop_goods/get_detail_by_code'),
        'params' => 'goods_code,shop_code'
    ),
));
?>
<?php if ($response['priv']['batch_update_status'] == TRUE): ?>
    <ul class="toolbar frontool" id="tool">
        <li class="li_btns"><button class="button button-primary " onclick="batck_enable()">批量启用</button></li>
        <li class="li_btns"><button class="button button-primary " onclick="batck_disable()">批量停用</button></li>
    </ul>
<?php endif; ?>
<script>
    shop_code = '';
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
<?php if ($response['priv']['update_price'] == TRUE): ?>
            if (typeof tableCellEditing != "undefined") {
                tableCellEditing.on('accept', function (record, editor) {
                    if (record.record.goods_price == record.record.compare_price) {
                        return;
                    }
                    var params = {};
                    params.id = record.record.shop_sku_id;
                    params.goods_price = record.record.goods_price;
                    params.goods_code = record.record.goods_code;
                    params.type = 1;
                    $.post('?app_act=prm/shop_goods/update_price', params, function (ret) {
                        alert_msg(ret.status);
                        tableStore.load();
                    }, 'json');
                });
            }
<?php endif; ?>
    });
<?php if ($response['priv']['update_price'] == TRUE): ?>
        function change_sku_price(_this) {
            var sku_price = $(_this).text();
            var priceid = $(_this).attr('priceid');
            $(_this).parent().html("<input id='new_sku_price' onblur='update_sku_price(this.value," + priceid + "," + sku_price + ")'  style='width:80px;' type='text' value='" + sku_price + "'>");
            $('#new_sku_price').focus();
        }

        function update_sku_price(new_price, priceid, compare_sku_price) {
            if (new_price != compare_sku_price) {
                var params = {id: priceid, sku_price: new_price, type: 0};
                $.post('?app_act=prm/shop_goods/update_price', params, function (ret) {
                    alert_msg(ret.status);
                }, 'json');
            }
            $('#new_sku_price').parent().html("<a id='price' priceid=" + priceid + " onclick='change_sku_price(this)'>" + new_price + "</a>");
        }
<?php endif; ?>
<?php if ($response['priv']['update_status'] == TRUE): ?>
        function do_enable(_index, row) {
            _do_set_active(_index, row, 'enable');
        }
        function do_disable(_index, row) {
            _do_set_active(_index, row, 'disable');
        }
        function _do_set_active(_index, row, active) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/shop_goods/update_active'); ?>',
                data: {id: row.shop_sku_id, goods_code: row.goods_code, active: active},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert(ret.message, type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });
        }
<?php endif; ?>
    /*
     * 商品删除
     */
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/shop_goods/do_delete'); ?>',
            data: {id: row.shop_sku_id},
            success: function (ret) {
                if (ret.status == 1) {
                    BUI.Message.Alert(ret.message);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message);
                }
            }
        });
    }
<?php if ($response['priv']['add_goods'] == TRUE): ?>
        function show_select_shop() {
            selectPopWindow.dialog = new ESUI.PopSelectWindow('?app_act=common/select/shop&shop_type=1', 'selectPopWindow.callback', {title: '选择店铺', width: 560, height: 400, ES_pFrmId: '<?php echo $request['ES_frmId']; ?>'}).show();
        }

        //选择店铺
        var selectPopWindow = {
            dialog: null,
            callback: function (value) {
                shop_code = value[0]['shop_code'];
                show_select_goods();
                if (selectPopWindow.dialog != null) {
                    selectPopWindow.dialog.close();
                }
            }
        };
        //添加门店商品
        function show_select_goods(shop_code) {
            var param = {};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=prm/goods/goods_select_tpl_shop';
            var buttons = [
                {
                    text: '保存继续',
                    elCls: 'button button-primary',
                    handler: function () {
                        addgoods(this, 1);
                        top.save_up();
                    }
                },
                {
                    text: '保存退出',
                    elCls: 'button button-primary',
                    handler: function () {
                        addgoods(this, 0);
                    }
                }, {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: '选择商品',
                    width: '80%',
                    //height: 400,
                    loader: {
                        url: url,
                        autoLoad: true, //不自动加载
                        params: param, //附加的参数
                        lazyLoad: false, //不延迟加载
                        dataType: 'text'   //加载的数据类型
                    },
                    align: {
                        //node : '#t1',//对齐的节点
                        points: ['tc', 'tc'], //对齐参考：http://dxq613.github.io/#positon
                        offset: [0, 20] //偏移
                    },
                    mask: true,
                    buttons: buttons
                });
                top.dialog.on('closed', function (ev) {
                    tableStore.load();
                });
                top.dialog.show();
            });
        }

        function addgoods(obj, type) {
            var select_data = top.SelectoGrid.getSelection();
            var _thisDialog = obj;
            $.post('?app_act=prm/shop_goods/add_shop_goods&shop_code=' + shop_code, {data: select_data}, function (result) {
                if (result.status != 1) {
                    //添加失败
                    top.BUI.Message.Alert(result.message, function () {
                        // _thisDialog.close();
                    }, 'error');
                } else {
                    if (type == 1) {
                        top.skuSelectorStore.load();
                    } else {
                        _thisDialog.close();
                    }
                }
            }, 'json');
        }
<?php endif; ?>
<?php if ($response['priv']['batch_update_status'] == TRUE): ?>
        //批量停用
        function batck_disable() {
            get_checked(true, '停用', function (ids) {
                var params = {id: ids, active: 'disable'};
                $.post("?app_act=prm/shop_goods/batch_update_active", params, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'info');
                        //刷新
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }
        //批量启用
        function batck_enable() {
            get_checked(true, '启用', function (ids) {
                var params = {id: ids, active: 'enable'};
                $.post("?app_act=prm/shop_goods/batch_update_active", params, function (data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'success');
                        //刷新
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(data.message, 'error');
                    }
                }, "json");
            });
        }

        //读取已选中项
        function get_checked(isConfirm, opt_name, func) {
            var ids = {};
            var selecteds = tableGrid.getSelection();
            if (selecteds.length == 0) {
                BUI.Message.Alert("请选择条码", 'error');
                return;
            }

            for (var i in selecteds) {
                ids[selecteds[i].shop_sku_id] = selecteds[i].goods_code;
            }

            if (isConfirm) {
                BUI.Message.Show({
                    title: '批量' + opt_name,
                    msg: '是否执行批量' + opt_name + '?',
                    icon: 'question',
                    buttons: [
                        {
                            text: '是',
                            elCls: 'button button-primary',
                            handler: function () {
                                func.apply(null, [ids])
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
                func.apply(null, [ids])
            }
        }
<?php endif; ?>

    function alert_msg(_status) {
        if (_status == 1) {
            BUI.Message.Show({
                msg: '更新成功',
                icon: 'success',
                buttons: [],
                autoHide: true,
                autoHideDelay: 1000
            });
        } else {
            BUI.Message.Show({
                msg: '更新失败 ',
                icon: 'error',
                buttons: [],
                autoHide: true,
                autoHideDelay: 1000
            });
        }
    }
</script>
