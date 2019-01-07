<style>
    .table_init{
        width: 100%;
        height: 30px;
        border-left: 1px solid #dddddd;
        border-top: 1px solid #dddddd;

    }
    .table_init td{
        text-align: center;
    }
    .td{border-right: 1px solid #dddddd;width:1077px}
    .alert{color: red;font:italic bold 20px/30px arial,sans-serif;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品初始化',
    'links' => array(
//        array('url' => 'prm/goods_init/stock_init&type=one_key', 'title' => '一键库存初始化', 'is_pop' => true, 'pop_size' => '565,369')
    ),
    'ref_table' => 'table'));

$keyword_types = array();
$keyword_types['goods_code'] = '商品编码';
$keyword_types['goods_barcode'] = 'SKU规格编码';
$keyword_type = array_from_dict($keyword_types);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '平台商品状态',
            'type' => 'select',
            'id' => 'platform_status',
            'data' => ds_get_select_by_field('platform_goods_status', 2),
        ),
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop_by_sale_channel_code('taobao'),
        ),
        array(
            'label' => '商品初始化',
            'type' => 'select',
            'id' => 'is_goods_init',
            'data' => ds_get_select_by_field('boolstatus', 2),
        ),
        array(
            'label' => '库存初始化',
            'type' => 'select',
            'id' => 'is_stock_init',
            'data' => ds_get_select_by_field('boolstatus', 2),
        ),
        array(
            'label' => '状态',
            'type' => 'select',
            'id' => 'status',
            'data' => ds_get_select_by_field('api_goods_status', 2),
        ),
    )
        )
);
?>

<!--<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary btn_opt_init_create">批量生成系统商品</button></li>
    <li class="li_btns"><button class="button button-primary btn_opt_init_stock">批量库存初始化</button></li>
    <li class="front_close">&lt;</li>
</ul>-->
<table class="table_init">
    <tr>
        <td class="td"><strong>平台商品信息</strong></td>
        <td ><strong>系统商品信息</strong></td>
    </tr>
</table>
<!--<script>
    $(function () {
        function tools() {
            $(".frontool").animate({left: '0px'}, 1000);
            $(".front_close").click(function () {
                if ($(this).html() === "&lt;") {
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
</script>-->
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '标识符numiid',
                'field' => 'goods_from_id',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品标题',
                'field' => 'goods_name',
                'width' => '200',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '50',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '规格描述',
                'field' => 'sku_properties_name',
                'width' => '220',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'SKU规格编码',
                'field' => 'goods_barcode',
                'width' => '180',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品库存',
                'field' => 'num',
                'width' => '80',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'sys_goods_barcode',
                'width' => '180',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品初始化',
                'field' => 'is_goods_init',
                'width' => '80',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库存初始化',
                'field' => 'is_stock_init',
                'width' => '80',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
        )
    ),
    'dataset' => 'prm/GoodsInitModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'api_goods_sku_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_init_list', 'name' => '商品初始化数据列表', 'export_type' => 'file'),
    'CheckSelection' => true,
));
?>
<script>
    $(function () {
        var default_opts = ['opt_init_create', 'opt_init_stock'];
        for (var i in default_opts) {
            var f = default_opts[i];
            btn_init_opt("ToolBar1", f);
        }
        var custom_opts = $.parseJSON('');
        for (var j in custom_opts) {
            var g = custom_opts[j];
            $("#ToolBar1 .btn_" + g['id']).click(eval(g['custom']));
        }
    });
    function checkit(isChecked) {
        if (isChecked) {
            $("#btn-search").click();
        } else {

        }
    }
    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function () {
            get_checked($(this), function (ids) {
                if (id === 'opt_init_create') {
                    $("#btn_opt_init_create").attr('disabled', 'disabled');
                    show_alert(ids, id);
                }
                if (id === 'opt_init_stock') {
                    $("#btn_opt_init_stock").attr('disabled', 'disabled');
                    do_init_stock(ids, id);
                }
            })
        });
    }
    function show_alert(ids, id) {
        var params = {"api_goods_sku_id": ids};
        $.post("?app_act=prm/goods_init/check_goods_code", params, function (data) {
            if (data.status == 1) {
                var params = {"api_goods_sku_id": ids, "type": id, "batch": "批量操作"};
                do_init_create(params);
            } else {
                show_blank_goods_code(ids, data.data, id);
            }
        }, "json");
    }

    function show_blank_goods_code(ids, codes, id) {
        BUI.Message.Show({
            title: '批量生成系统商品',
            msg: '选中商品中有<span class="alert">' + codes + '</span>处不存在商品编码，<br/>不可批量生成系统商品！',
            icon: 'question',
            buttons: [
                {
                    text: '取消',
                    elCls: 'button',
                    handler: function () {
                        this.close();
                    }
                }
            ]
        });
    }

    function do_init_create(params) {
        $.post("?app_act=prm/goods_init/opt_batch", params, function (data) {
            if (data.status == 1) {
                BUI.Message.Alert(data.message, 'info')
                //刷新
                tableStore.load()
            } else {
                BUI.Message.Alert(data.message, 'error')
            }
        }, "json");
    }

    function do_init_stock(ids) {
        new ESUI.PopWindow("?app_act=prm/goods_init/stock_init&api_goods_sku_id=" + ids + "&type=batch", {
            title: "批量库存初始化",
            width: 565,
            height: 370,
            onBeforeClosed: function () {
            },
            onClosed: function () {
                //刷新数据
                tableStore.load()
            }
        }).show()
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择商品", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.api_goods_sku_id);
        }
        ids.join(',');
        if (obj.text() == '批量库存初始化') {
            func.apply(null, [ids]);
        } else {
            BUI.Message.Show({
                title: '批量初始化',
                msg: '是否执行' + obj.text() + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids]);
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
        }
    }
</script>
