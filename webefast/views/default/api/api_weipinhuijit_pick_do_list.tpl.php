<style>
    #insert_time_start{width:85px;}
    #insert_time_end{width:85px;}
</style>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js("pur.js", true); ?>
<?php
render_control('PageHead', 'head1', array('title' => '唯品会JIT拣货单管理',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type['pick_no'] = '拣货单号';
$keyword_type['store_out_record_no'] = '批发销货单号';
$keyword_type['delivery_id'] = '出库单号';
$keyword_type['po_no'] = '档期号';
$keyword_type = array_from_dict($keyword_type);
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '唯品会仓库',
            'type' => 'select_multi',
            'id' => 'warehouse',
            'data' => load_model('api/WeipinhuijitWarehouseModel')->get_warehouse_select(),
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => $response['shop'],
        ),
        array(
            'label' => '生成销货单',
            'type' => 'select',
            'id' => 'is_execute',
            'data' => ds_get_select_by_field('is_build', 1),
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
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    //array('id'=>'create_out_record', 'title' => '生成销货单', 'callback'=>'create_wbm_store_out_record'),
                    array('id' => 'create_out_record', 'title' => '生成销货单', 'callback' => 'create_delivery', 'show_cond' => 'obj.no_delivery_num>0'),
                    array('id' => 'view', 'title' => '查看', 'callback' => 'showDetail'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此拣货单？','show_cond' => 'obj.delete_status==0','priv'=>'api/api_weipinhuijit_po_pick/delete'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已生成',
                'field' => 'is_execute',
                'width' => '80',
                'align' => '',
                'format' => array('type' => 'map_checked'),
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
                'title' => '拣货单号',
                'field' => 'pick_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '档期号',
                'field' => 'po_no',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '拣货数',
                'field' => 'pick_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待发货数',
                'field' => 'no_delivery_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已发货数量',
                'field' => 'delivery_num',
                'width' => '70',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '送货仓库',
                'field' => 'warehouse_name',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'insert_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'api/WeipinhuijitPickModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'params' => array('filter' => array('jit_version' => 1)),
    'export' => array('id' => 'exprot_list', 'conf' => 'api_weipinhuijit_pick_do_list', 'name' => '唯品会JIT拣货单管理', 'export_type' => 'file'),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '关联批发销货单', 'width' => '150', 'field' => 'record_code', 'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view_store_record({store_out_record_id})">{record_code}</a>',
                ),
            ),
            array('title' => '出库单号', 'width' => '150', 'field' => 'delivery_id'),
            array('title' => 'eFAST仓库', 'field' => 'store_code'),
            array('title' => '已通知数量', 'field' => 'enotice_num'),
            array('title' => '已发货数', 'field' => 'num'),
            array('title' => '创建时间', 'width' => '150', 'field' => 'order_time'),
            array('title' => '销货单验收状态', 'field' => 'is_store_out', 'format_js' => array('type' => 'map_checked')),
        ),
        'page_size' => 50,
        'url' => get_app_url('api/api_weipinhuijit_pick/get_out_store_record_by_pick&app_fmt=json'),
        'params' => 'pick_no',
    ),
));
?>

<ul id="ToolBar1" class="toolbar frontool">
    <li class="li_btns"><button class="button button-primary create_wbm_out_record">生成销货单</button></li>
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
<script type="text/javascript">
    $("#tid").css('border', '1px solid red');
    //转单
    function create_pick(index, row) {
        var d = {"po_no": row.po_no, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_po/create_pick'); ?>', d, function (data) {

            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
    //数据行双击打开新页面显示详情
    function showDetail(index, row) {
        var url = '?app_act=api/api_weipinhuijit_pick/view&pick_no=' + row.pick_no;
        openPage(window.btoa(url), url, '唯品会JIT拣货单详情');
    }
    //批发销货单详情
    function view_store_record(store_out_record_id) {
        var url = '?app_act=wbm/store_out_record/view&store_out_record_id=' + store_out_record_id;
        openPage(window.btoa(url), url, '批发销货单');
    }
    //生成批发销货单
    $(".create_wbm_out_record").click(function () {
        get_checked($(this), function (ids, shop_code) {
            //校验是否绑定批发通知单
            var d = {"pick_ids": ids.toString(), 'app_fmt': 'json'};
            $.post("?app_act=api/api_weipinhuijit_pick/check_pick_more", d, function (data) {
                var type = data.status == 1 ? 'success' : 'error';
                if (type == 'error') {
                    BUI.Message.Alert(data.message, type);
                } else {
                    var url = "?app_act=api/api_weipinhuijit_pick/create_view&jit_version=1&pick_id=" + ids.toString() + "&shop_code=" + shop_code;
                    _do_execute(url, 'table', '生成批发销货单', 650, 500);
                }
            }, "json");
        });
    });

    //绑定批发销货单
    $(".relation_wbm_out_record").click(function () {
        get_checked($(this), function (ids) {
            url = "?app_act=api/api_weipinhuijit_pick/relation_wbm_out_record&pick_ids=" + ids.toString();
            _do_execute(url, 'table', '绑定销货单', 550, 600);
        });
    });

    //生成批发销货单
    function create_wbm_store_out_record(index, row) {
        var d = {"pick_id": row.id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_pick/do_check'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';

            if (type == 'error') {
                BUI.Message.Alert(data.message, type);
            } else {
                if (data.data['noice_record'] != '') {
                    //直接生成批发销货单
                    var d = {"pick_id": row.id, "notice_record": data.data['noice_record'], 'app_fmt': 'json'};
                    $.post("?app_act=api/api_weipinhuijit_pick/do_create_out_record", d, function (data) {
                        var type = data.status == 1 ? 'success' : 'error';
                        BUI.Message.Alert(data.message, type);
                        tableStore.load();
                    }, "json");

                } else {
                    //生成通知单
                    url = "?app_act=api/api_weipinhuijit_pick/notice_record_view&pick_id=" + row.id;
                    _do_execute(url, 'table', '生成通知单、销货单', 650, 400);
                }
            }

        }, "json");
    }
    function create_delivery(index, row) {
        var d = {"pick_id": row.id, 'app_fmt': 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_pick/do_check'); ?>', d, function (data) {
            var type = data.status == 1 ? 'success' : 'error';

            if (type == 'error') {
                BUI.Message.Alert(data.message, type);
            } else {
                var url = "?app_act=api/api_weipinhuijit_pick/create_view&jit_version=1&pick_id=" + row.id + "&shop_code=" + row.shop_code;
                _do_execute(url, 'table', '生成批发销货单', 670, 480);
            }

        }, "json");
    }

    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        var shop_code = rows[0].shop_code;
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.id);
        }
        ids.join(',');
        func.apply(null, [ids, shop_code]);
    }


    //删除
    function do_delete(index, row) {
        var params = {"pick_no": row.pick_no, "app_fmt": 'json'};
        $.post('<?php echo get_app_url('api/api_weipinhuijit_multi_po_pick/do_delete'); ?>', params, function (data) {
            var type = data.status == 1 ? 'success' : 'error';
            BUI.Message.Alert(data.message, type);
            tableStore.load();
        }, "json");
    }
</script>



