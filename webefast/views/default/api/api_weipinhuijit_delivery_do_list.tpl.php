<style>
    #insert_time_start,#insert_time_end,#delivery_time_start,#delivery_time_end{width:100px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT出库单管理',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type['delivery_id'] = '出库单号';
$keyword_type['pick_no'] = '拣货单号';
$keyword_type['record_code'] = '批发销货单号';
$keyword_type['express'] = '快递单号';
$keyword_type['storage_no'] = '入库单号';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_detail',
        ),
    ),
    'fields' => array(
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
            'data' => $response['shop'],
        ),
        array(
            'label' => '唯品会仓库',
            'type' => 'select_multi',
            'id' => 'warehouse',
            'data' => load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select(),
        ),
        array(
            'label' => '出库状态',
            'type' => 'select',
            'id' => 'is_delivery',
            'data' => array(
                array('content' => '', 'id' => '全部'),
                array('content' => '0', 'id' => '未出库'),
                array('content' => '1', 'id' => '已出库'),
            )
        ),
        array(
            'label' => '创建时间',
            'type' => 'group',
            'field' => 'insert_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'insert_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'insert_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '出库时间',
            'type' => 'group',
            'field' => 'delivery_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'delivery_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'delivery_time_end', 'remark' => ''),
            )
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
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '110',
                'align' => '',
                'buttons' => array(
                    array('id' => 'confirm', 'title' => '确认出库', 'callback' => 'confirm_delivery', 'show_cond' => "obj.is_delivery==0"),
                    array('id' => 'view', 'title' => '查看', 'callback' => 'showDetail'),
                    array('id' => 'edit_info', 'title' => '修改', 'callback' => 'edit_info', 'show_cond' => "obj.is_delivery==0", 'priv' => 'api/api_weipinhuijit_delivery/edit_info'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库单号',
                'field' => 'delivery_id',
                'width' => '100',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{delivery_id}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库单号',
                'field' => 'storage_no',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({id})">{storage_no}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送模式',
                'field' => 'delivery_method',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预计到货时间',
                'field' => 'arrival_time',
                'width' => '90',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '唯品会仓库',
                'field' => 'warehouse_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '品牌',
                'field' => 'brand_code_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品总数量',
                'field' => 'amount',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库单状态',
                'field' => 'is_delivery',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '出库时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitDeliveryModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'record_code',
    'export' => array('id' => 'exprot_detail', 'conf' => 'weipinhuijit_delivery', 'name' => '唯品会JIT出库单', 'export_type' => 'file'),
    'CheckSelection' => true,
    'customFieldTable' => 'api_weipinhuijit_delivery/table',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<div>
    <span style="color:red;">温馨提示：JIT出库单由JIT拣货单在操作生成批发销货时，自动生成。</span>
</div>
<?php if($response['batch_confirm_delivery']) :?>
<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary opt_batch_delivery ">批量出库</button></li>
    <div class="front_close">&lt;</div>
</ul>
<?php endif;?>

<script>
    $(function () {
        tools();
    });

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


    //生成批发销货单
    $(".create_delivery").click(function () {
        url = "?app_act=api/api_weipinhuijit_delivery/create_view&is_store_out=1&have_delivery=0";
        _do_execute(url, 'table', '生成出库单', 500, 550);

    });

    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=api/api_weipinhuijit_delivery/view&id=' + row.id;
        openPage(window.btoa(url), url, '唯品会JIT出库单详情');
    }

    function view(id) {
        var url = '?app_act=api/api_weipinhuijit_delivery/view&id=' + id
        openPage(window.btoa(url), url, '唯品会JIT出库单详情');
    }

    //确认出库
    function confirm_delivery(index, row) {
        BUI.use('bui/overlay', function (Overlay) {
            var dialog = new Overlay.Dialog({
                width: 450,
                height: 120,
                elCls: 'custom-dialog',
                bodyContent: '<p style="font-size:15px">正在出库，请稍后...</p>',
                buttons: []
            });
            dialog.show();
        });
        var data = {delivery_id: row.delivery_id, type: 'enable', 'app_fmt': 'json'};
        url = '?app_act=api/api_weipinhuijit_delivery/confirm_delivery';
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: url,
            data: data,
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(ret.message, type);
                    location.reload();
                } else {
                    $(".bui-ext-close .bui-ext-close-x").click();
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = [];
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择出库单", "warning");
            return false;
        }
        for (var i in rows) {
            var row = rows[i];
            if(row.is_delivery === '0'){
                ids.push(row.delivery_id);
            }
        }
        if(ids.length === 0){
            BUI.Message.Tip("请选择可操作出库的出库单","warning");
            return false;
        }

        func.apply(null, [ids]);
    }

    function edit_info(index, row) {
        url = "?app_act=api/api_weipinhuijit_delivery/edit_deliver_view&id=" + row.id;
        new ESUI.PopWindow(url, {
            title: "修改出库单信息",
            width: 500,
            height: 350,
            onBeforeClosed: function () {
                tableStore.load();
                //location.reload();
            },
            onClosed: function () {
                //刷新数据
                tableStore.load();
            }
        }).show();
    }

    //批量出库
    $(".opt_batch_delivery").on('click', function () {
        get_checked($(this), function (ids) {
            var params = [];
            $.each(ids, function (_key,_code) {
                var p = {};
                p.delivery_id = _code;
                params.push(p);
            });
            var act = 'api/api_weipinhuijit_delivery/batch_confirm_delivery';
            process_batch_task(act, '批量出库', params, 'delivery_id', 0, 'opt_batch_delivery');
        });
    });

</script>
<?php include_once (get_tpl_path('common/process_batch_task')); ?>


