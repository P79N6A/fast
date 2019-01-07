<?php
$links = array(array('url' => 'base/shop/detail&app_scene=add', 'title' => '添加店铺', 'is_pop' => true, 'pop_size' => '700,600'),);
if (load_model('sys/PrivilegeModel')->check_priv('base/shop/detail&app_scene=add')) {
    render_control('PageHead', 'head1', array('title' => '店铺列表',
        'links' => $links,
        'ref_table' => 'table'
    ));
} else {
    render_control('PageHead', 'head1', array('title' => '店铺列表',
        'ref_table' => 'table'
    ));
}
?>

<?php
$keyword_type = array();
$keyword_type['shop_code'] = '店铺代码';
$keyword_type['shop_name'] = '店铺名称';
$keyword_type['shop_user_nick'] = '店铺昵称';
$keyword_type['shop_id'] = '店铺ID';
$keyword_type = array_from_dict($keyword_type);
$fields = array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => $response['sale_channel'],
        ),
        array(
            'label' => '启用',
            'title' => '是否启用此商店',
            'type' => 'select',
            'id' => 'is_active',
            'data' => ds_get_select_by_field('boolstatus')
        ),
    );
if($response['login_type'] != 2) {
        $fields[] = array(
            'label' => '启用淘分销',
            'type' => 'select',
            'id' => 'is_fenxiao',
            'data' => ds_get_select_by_field('is_fenxiao')
        );
        $fields[] = 
        array(
            'label' => '店铺性质',
            'type' => 'select',
            'id' => 'entity_type',
            'data' => ds_get_select_by_field('entity_type'),
        );
    if($response['service_custom'] == TRUE) {
        $fields[] = array('label' => '分销商', 'type' => 'select_pop', 'id' => 'custom_code', 'select' => 'base/custom_multi');
    }
}
render_control('SearchForm', 'searchForm', array(
    'buttons'=>array(
        array(
            'label' => '查询',
            'type' => 'submit',
            'id' => 'btn-search',
        ),
        array(
            'label'=>'导出',
            'id'=>'exprot_list'
        )
    ),
    'fields' => $fields,
));
?>
<?php
$list = array(
    array('type' => 'button',
        'show' => 1,
        'title' => '操作',
        'field' => '_operate',
        'width' => '100',
        'align' => '',
        'buttons' => array(
            array('id' => 'edit', 'title' => '编辑','priv' => 'base/shop/detail&app_scene=edit',
                'act' => 'pop:base/shop/detail&app_scene=edit','pop_size'=> '700,600', 'show_name' => '编辑',
                'show_cond' => 'obj.is_buildin != 1'),
            array('id' => 'edit_stock_source', 'title' => '库存来源仓库','priv' => 'base/shop/stock_source',
                'act' => 'pop:base/shop/stock_source', 'show_name' => '库存来源仓库'),
            array('id' => 'enable', 'title' => '启用',
                'callback' => 'do_enable', 'show_cond' => 'obj.is_active != 1'),
            array('id' => 'disable', 'title' => '停用',
                'callback' => 'do_disable', 'show_cond' => 'obj.is_active == 1',
                'confirm' => '<div style="text-align:center">确认要停用吗？<br><p style="color:red">店铺已存在订单数据，停用后将无法查询到对应店铺的数据！</p></div>'),
            array('id' => 'delete', 'title' => '删除','priv' => 'base/shop/do_delete', 'callback' => 'do_delete', 'show_name' => '删除', 'show_cond' => 'obj.is_active != 1'),
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '启用',
        'field' => 'is_active_text',
        'width' => '40',
        'align' => '',
        'format' => array('type' => 'map_checked'),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售平台',
        'field' => 'sale_channel_name',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺ID',
        'field' => 'shop_id',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺代码',
        'field' => 'shop_code',
        'width' => '120',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺名称',
        'field' => 'shop_name',
        'width' => '130',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺性质',
        'field' => 'entity_name',
        'width' => '80',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺昵称',
        'field' => 'shop_user_nick',
        'width' => '130',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '承诺发货天数',
        'field' => 'days',
        'width' => '90',
        'align' => '',
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '<span style="color:red;" title="授权失效，未授权">授权状态</span>',
        'field' => 'authorize_state',//授权失效，未授权
        'width' => '120',
        'align' => '',
//        'format_js' => array(
//            'type' => 'html',
//            'value' => '<a href="javascript:authorize()">{authorize_state}</a>',
//        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '授权截止时间',
        'field' => 'authorize_date',
        'width' => '90',
        'align' => '',
    ),
);
if ($response['add_service_authority'] == true) {
    $list[] = array(
        'type' => 'text',
        'show' => 0,
        'title' => '增值服务',
        'field' => 'alipay_order',
        'width' => '160',
        'align' => '',
    );
}
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'base/ShopModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'shop_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'shop_list', 'name' => '网络店铺','export_type' => 'file'),//
        //'RowNumber'=>true,
        //'CheckSelection' => true,
));
?>
<input type="hidden" id="sel_shop_code"/>
<input type="hidden" id="alipay_order_app_key"/>
<script type="text/javascript">
    var selectPopWindowcustom_code = {
        dialog: null,
        callback: function (value) {
            var custom_code = [];
            var custom_name = [];
            $.each(value, function (i, v) {
                custom_code.push(v['custom_code']);
                custom_name.push(v['custom_name']);
            });
            $('#custom_code_select_pop').val(custom_name.join());
            $('#custom_code').val(custom_code.join());
            if (selectPopWindowcustom_code.dialog != null) {
                selectPopWindowcustom_code.dialog.close();
            }
        }
    };
    function authorize(_id) {
        new ESUI.PopWindow("?app_act=base/shop/detail&app_scene=edit&active=2&_id=" + _id, {
            title: "重新授权",
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load();
            }
        }).show()
    }
    var shop_channel_code = "<?php echo isset($request['shop_channel_code']) ? $request['shop_channel_code'] : ''; ?>";

    $(function () {
        if (shop_channel_code == 'B')
        {
            setTimeout(function () {
                var obj = searchFormForm.serializeToObject();
                obj.shop_channel_code = 'B';
                tableStore.load(obj);
            }, 100);
        }
    });


    function alipay_order(app_key, shop_code) {
        $("#sel_shop_code").val(shop_code);
        var url = 'https://openapi.alipay.com/subscribe.htm?id=top.' + app_key;
        window.open(url);

        //弹出页面
        BUI.use(['bui/overlay', 'bui/mask'], function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: '店铺订购',
                width: '50%',
                height: 200,
                buttons: [
                    {text: '订购成功', elCls: 'button button-primary', handler: function () {
                            alipay_order_succ();
                            this.hide();
                        }}
                    ,
                    {text: '我已订购，重试一次', elCls: 'button button-danger', handler: function () {
                            this.hide();
                            alipay_order();
                        }}
                ],
                bodyContent: '请选择订购结果？（注：未订购应用请先完成订购，之后授权）',
                mask: true
            });
            dialog.show();
        });
    }

//订购成功
    function alipay_order_succ()
    {
        var shop_code = $("#sel_shop_code").val();
        var url = '?app_act=base/shop/alipay_order_succ';
        $.ajax({
            type: "POST",
            url: url,
            data: {'shop_code': shop_code},
            dataType: "json",
            async: true,
            success: function (data) {
                if (data.status == 1) {
                    var obj = searchFormForm.serializeToObject();
                    obj.start = 1; //返回第一页
                    tableStore.load(obj);
                } else {
                    BUI.Message.Alert(data.message);
                }
            }
        });
    }

    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/shop/update_active'); ?>',
            data: {id: row.shop_id, type: active},
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
    
    function do_delete(_index, row) {
        BUI.Message.Confirm('确认要删除吗？',function(){
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('base/shop/do_delete'); ?>',
                data: {shop_id: row.shop_id},
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
        },'question');
    }

    function pre_authorize(shop_code) {
        $("#sel_shop_code").val(shop_code);
        var url = "?app_act=base/shop/pre_authorize&shop_code=" + shop_code;
        window.open(url);

        //弹出页面
        BUI.use(['bui/overlay', 'bui/mask'], function (Overlay) {
            var dialog = new Overlay.Dialog({
                title: '店铺授权',
                width: '50%',
                height: 200,
                buttons: [
                    {text: '授权成功', elCls: 'button button-primary', handler: function () {
                            authorize_succ();
                            this.hide();
                        }}
                    ,
                    {text: '我已订购，重试一次', elCls: 'button button-danger', handler: function () {
                            this.hide();
                            pre_authorize();
                        }}
                ],
                bodyContent: '请选择授权结果？（注：未订购应用请先完成订购，之后授权）',
                mask: true
            });

            dialog.show();
        });

    }

    //授权成功
    function authorize_succ() {
        var shop_code = $("#sel_shop_code").val();

        var url = '?app_act=base/shop/shop_authorize_success';

        $.ajax({
            type: "POST",
            url: url,
            data: {'shop_code': shop_code},
            dataType: "json",
            async: true,
            success: function (data) {
                if (data.status == 1) {
                    var obj = searchFormForm.serializeToObject();
                    obj.start = 1; //返回第一页
                    tableStore.load(obj);
                } else {
                    BUI.Message.Alert(data.message);
                }
            }
        });
    }

</script>
