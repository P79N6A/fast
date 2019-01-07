<style>
    #sales_st_start,#sales_st_end,#last_sync_time_start,#last_sync_time_end{width: 120px;}
</style>
<?php
render_control('PageHead', 'head1', array('title' => '专场商品列表',
    'links' => array(
        array('type' => 'js', 'js' => 'adjust_inv()', 'title' => '调整平台库存', 'priv' => 'api/wph/sales_sku/adjust_inv'),
    ),
    'ref_table' => 'table'
));
?>

<?php
$keyword_goods['barcode'] = '平台商品条码';
$keyword_goods['product_name'] = '商品名称';
$keyword_goods['brand_name'] = '商品品牌';
$keyword_goods = array_from_dict($keyword_goods);
$keyword_sales['sales_no'] = '专场ID';
$keyword_sales['name'] = '专场名称';
$keyword_sales = array_from_dict($keyword_sales);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_goods', 'type' => 'select', 'data' => $keyword_goods),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_goods,
            'id' => 'keyword_goods_value',
        ),
        array(
            'label' => '最后同步时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'last_sync_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'last_sync_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => array('id' => 'keyword_sales', 'type' => 'select', 'data' => $keyword_sales),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_sales,
            'id' => 'keyword_sales_value',
            'value' => $response['sales_no']
        ),
        array(
            'label' => '商品上线时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'sales_st_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'sales_st_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '是否允许同步',
            'type' => 'select',
            'id' => 'is_allow_sync',
            'data' => ds_get_select_by_field('boolstatus')
        ),
    )
));
?>
<?php
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'all'),
        array('title' => '待同步', 'active' => true, 'id' => 'no_sync'),
//        array('title' => '已同步', 'active' => false, 'id' => 'synced'),
        array('title' => '同步失败', 'active' => false, 'id' => 'sync_fail'),
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view_goods', 'title' => '库存同步', 'callback' => 'inv_sync_one', 'priv' => 'api/wph/sales_sku/inv_sync', 'confirm' => '确认要同步吗？', 'show_cond' => 'obj.is_allow_sync==1'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '允许同步',
                'field' => 'is_allow_sync',
                'width' => '70',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品条码',
                'field' => 'barcode',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '对照码',
                'field' => 'sku_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品名称',
                'field' => 'product_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台商品品牌',
                'field' => 'brand_name',
                'width' => '100',
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
                'title' => '初始化库存',
                'field' => 'init_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台可售库存',
                'field' => 'pt_sale_num',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '增减库存数',
                'field' => 'diff_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最后同步库存数',
                'field' => 'last_sync_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '最后同步时间',
                'field' => 'last_sync_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '关联专场ID',
                'field' => 'sale_no_txt',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '上线时间',
                'field' => 'sales_st',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '失败信息',
                'field' => 'fail_info',
                'width' => '120',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/wph/WphSalesSkuModel::get_by_page',
    'queryBy' => 'searchForm',
    'params' => array('filter' => array('keyword_sales' => 'sales_no', 'keyword_sales_value' => $response['sales_no'], 'sync_status' => 'no_sync')),
    'customFieldTable' => 'wph/sales_sku',
    'CheckSelection' => true,
    'idField' => 'id'
));
?>
<div class="tips tips-small tips-info" style="margin: 60px 0 0 10px;width:78%;">
    <span class="x-icon x-icon-small x-icon-info"><i class="icon icon-white icon-info"></i></span>
    <div class="tips-content">友情提示：不允许同步库存，即表示该商品在其他专场中已开始售卖，共用其他专场库存。但是允许调整平台库存，调整成功会将多个专场库存一并调整。</div>
</div>
<!--<div id="TabPage1Contents">-->
<?php if ($response['inv_sync'] == TRUE) : ?>
    <!--<div></div>-->
    <div>
        <ul class="toolbar frontool">
            <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="inv_sync()">批量库存同步</button></li>
            <li class="front_close">&lt;</li>
        </ul>
    </div>
    <!--        <div></div>
            <div>
                <ul class="toolbar frontool">
                    <li class="li_btns"><button class="button button-primary btn-opt-store_in" onclick="inv_sync()">批量库存同步</button></li>
                    <li class="front_close">&lt;</li>
                </ul>
            </div>-->
<?php endif; ?>
<!--</div>-->
<script type="text/javascript">
    $(function () {
        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
    });

    tableStore.on('beforeload', function (e) {
        e.params.sync_status = $("#TabPage1").find(".active").find("a").attr("id");
        tableStore.set("params", e.params);
    });

    function adjust_inv() {
        url = "?app_act=api/wph/sales_sku/adjust_inv";
        new ESUI.PopWindow(url, {
            title: "导入调整库存",
            width: 500,
            height: 400,
            onBeforeClosed: function () {
                tableStore.load();
            },
            onClosed: function () {
            }
        }).show();
    }

    //单个库存同步
    function inv_sync_one(index, row) {
        if (row.sku == '') {
            BUI.Message.Tip(row.barcode + "条码在系统中不存在", 'error');
            return;
        }
        $.post("?app_act=api/wph/sales_sku/inv_sync", {id: row.id}, function (data) {
            if (data.status == 1) {
                BUI.Message.Tip(data.message, 'success');
            } else {
                BUI.Message.Tip(data.message, 'error');
            }
            tableStore.load();
        }, "json");
    }

    //批量库存同步
    function inv_sync() {
        get_checked(true, '库存同步', function (ids) {
            var params = {ids: ids};
            $.post("?app_act=api/wph/sales_sku/inv_sync", params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Tip(data.message, 'success');
                } else {
                    BUI.Message.Tip(data.message, 'error');
                }
                tableStore.load();
            }, "json");
        });
    }

    //读取已选中项
    function get_checked(isConfirm, opt_name, func) {
        var ids = {};
        var selecteds = tableGrid.getSelection();
        if (selecteds.length == 0) {
            BUI.Message.Tip("请选择待同步商品", 'error');
            return;
        }
        var shop_code = selecteds[0].shop_code;
        for (var i in selecteds) {
            if (selecteds[i].shop_code != shop_code) {
                BUI.Message.Tip("所选商品店铺不一致，请重新选择", 'error');
                return;
            }
            if (selecteds[i].sku == '') {
                BUI.Message.Tip(selecteds[i].barcode + "条码在系统中不存在", 'error');
                return;
            }
            ids[i] = selecteds[i].id;
//            barcode[selecteds[i].id] = selecteds[i].barcode;
        }
//        var params = {shop_code: shop_code, barcode: barcode};
        if (isConfirm) {
            BUI.Message.Show({
                title: '',
                msg: '是否执行批量' + opt_name + '?',
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
        } else {
            func.apply(null, [ids]);
        }
    }

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
</script>