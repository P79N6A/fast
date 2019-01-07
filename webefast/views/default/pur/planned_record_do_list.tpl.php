<style type="text/css">
    .well {
        min-height: 100px;
    }
    #record_time_start,#record_time_end{
        width: 100px;
    }
    #supplier_name{width:150px}
    #clear_supplier{
        position: absolute;
        right: 31px;
        top: 1px;
        border:none;
        border-left:1px solid rgba(128, 128, 128, 0.64);
        height: 24px;
    }
    .icon-remove{
        position: absolute;
        right: 4px;
        top: 4px;
    }
</style>
<?php
$links = [
    ['url' => 'pur/planned_record/multi_import', 'title' => '多采购单批量导入'],
    ['url' => 'pur/planned_record/detail&app_scene=add', 'title' => '添加采购订单', 'is_pop' => true, 'pop_size' => '500,580']
];
if ($response['layer_import_priv']) {
    array_unshift($links, ['url' => 'pur/planned_record/layer_import', 'title' => '二维表导入采购单']);
}
render_control('PageHead', 'head1', array('title' => '采购订单列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['init_code'] = '原单号';
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
            'label' => '导出',
            'id' => 'exprot_list',
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
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier',
            'child' => array(
                array('type' => 'input', 'field' => 'supplier_name', 'readonly' => 1, 'remark' => "<span class='x-icon x-icon-normal' id = 'clear_supplier' title='清除选中供应商' ><i class='icon-remove'></i></span><a href='#' id = 'base_supplier'><img src='assets/img/search.png' ></a><input type='hidden' id='supplier_code'>"),
            ),
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '计划日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'planned_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'planned_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '入库期限',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'in_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'in_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '备注',
            'type' => 'input',
            'id' => 'remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => '下单日期',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '差异款',
            'type' => 'select',
            'id' => 'difference_models',
            'data' => ds_get_select_by_field('is_rush'),
        ),
        array(
            'label' => '单据状态',
            'type' => 'select',
            'id' => 'record_status',
            'data' => ds_get_select_by_field('planned_record_status'),
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
                'width' => '120',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'check1',
                        'title' => '确认',
                        'callback' => 'do_check',
                        'priv' => 'pur/planned_record/do_check',
                        'show_cond' => 'obj.is_check == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_check',
                        'priv' => 'pur/planned_record/do_check',
                        'show_cond' => 'obj.is_finish != 1 && obj.is_check == 1 && obj.is_execute == 0 && obj.is_notify_payment == 0 '
                    ),
                    array(
                        'id' => 'execute',
                        'title' => '生成通知单',
                        'callback' => 'do_execute',
                        'priv' => 'pur/planned_record/do_execute',
                        'show_cond' => 'obj.is_finish != 1 && obj.is_check == 1'
                    ),
                    array(
                        'id' => 'finish',
                        'title' => '完成',
                        'callback' => 'do_finish',
                        'priv' => 'pur/planned_record/do_finish',
                        'show_cond' => 'obj.is_finish != 1 && obj.is_check == 1',
                        'confirm' => '确认要完成吗？'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'pur/planned_record/do_delete',
                        'show_cond' => 'obj.is_check == 0 && obj.is_notify_payment == 0',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认',
                'field' => 'is_check',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生成通知单',
                'field' => 'is_execute',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成',
                'field' => 'is_finish',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '150',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:view({planned_record_id})>{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '原单号',
                'field' => 'init_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '计划日期',
                'field' => 'planned_time',
                'width' => '120',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库期限',
                'field' => 'in_time',
                'width' => '120',
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商',
                'field' => 'supplier_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购类型',
                'field' => 'pur_type_code_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成数',
                'field' => 'finish_num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '差异数',
                'field' => 'difference_num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成金额',
                'field' => 'finish_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'pur/PlannedRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'planned_record_id',
    'customFieldTable' => 'pur/planned_record_do_list',
    'export' => array('id' => 'exprot_list', 'conf' => 'planned_record_list', 'name' => '采购计划单', 'export_type' => 'file'), //
    'params' => array('filter' => array('user_id' => $response['user_id'])),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    $(function () {
        $("#supplier_code").attr("value", "");
        $("#supplier_name").attr("value", "");
    });
    $("#base_supplier").click(function () {
        show_select('supplier');
    });
    $("#clear_supplier").click(function () {
        $("#supplier_code").attr("value", "");
        $("#supplier_name").attr("value", "");
    });
    function show_select(_type) {
        var param = {};
        var url = '?app_act=pur/planned_record/select_supplier';
        var title = '请选择供应商';

        if (typeof (top.dialog) !== 'undefined') {
            top.dialog.remove(true);
        }
        var buttons = [
            {
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#supplier_code');
                    top.tablesStore.load();
                    var supplier_name = $("#supplier_name").val();
                    var supplier_code = $("#supplier_code").val();
                    if (supplier_name !== '') {
                        d_supplier_name(supplier_name, 'name');
                        d_supplier_name(supplier_code, 'code');
                    }
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.tablesGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#supplier_code');
                    var supplier_name = $("#supplier_name").val();
                    var supplier_code = $("#supplier_code").val();
                    if (supplier_name !== '') {
                        d_supplier_name(supplier_name, 'name');
                        d_supplier_name(supplier_code, 'code');
                    }
                    this.close();
                }
            },
            {
                text: '取消',
                elCls: 'button',
                handler: function () {
                    this.close();
                }
            }
        ];
        top.BUI.use('bui/overlay', function (Overlay) {
            top.dialog = new Overlay.Dialog({
                title: title,
                width: '680',
                height: '500',
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

            });
            top.dialog.show();
        });
    }
    //去重
    function d_supplier_name(string_name, type) {
        string = string_name.split(',');
        var hash = [], arr = [];
        for (var i = 0, elem; (elem = string[i]) != null; i++) {
            if (!hash[elem]) {
                arr.push(elem);
                hash[elem] = true;
            }
        }
        if (type === 'code') {
            $("#supplier_code").val(arr.join(','));
        } else {
            $("#supplier_name").val(arr.join(','));
        }
    }
    function deal_data_1(obj, _type) {
        var supplier_code = new Array();
        var supplier_name = new Array();
        var string_code = "";
        var string_name = "";
        string_code = $("#supplier_code").val();
        string_name = $("#supplier_name").val();
        $.each(obj, function (i, val) {
            supplier_code[i] = val[_type + '_code'];
            supplier_name[i] = val[_type + '_name'];
        });
        supplier_code = supplier_code.join(',');
        supplier_name = supplier_name.join(',');
        if (string_code === "") {
            string_code = supplier_code;
            string_name = supplier_name;
            $("#supplier_code").val(string_code);
            $("#supplier_name").val(string_name);
        } else {
            string_code = string_code + ',' + supplier_code;
            string_name = string_name + ',' + supplier_name;
            $("#supplier_code").val(string_code);
            $("#supplier_name").val(string_name);
        }
    }
    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).trigger(e);
    }
    tableStore.on('beforeload', function (e) {
        e.params.supplier_code = $("#supplier_code").val();
        tableStore.set("params", e.params);
    });

    //导出明细
    $('#exprot_detail').click(function () {
        var url = '?app_act=sys/export_csv/export_show';
        params = tableStore.get('params');

        params.ctl_type = 'export';
        params.ctl_export_conf = 'planned_record_list_detail';
        params.ctl_export_name = '采购订单明细';
<?php echo create_export_token_js('pur/PlannedRecordModel::get_by_page'); ?>
        var obj = searchFormForm.serializeToObject();
        for (var key in obj) {
            params[key] = obj[key];
        }

        for (var key in params) {
            url += "&" + key + "=" + params[key];
        }
        window.open(url);
    });
    // tableStore.set('pageSize', 2);
    // tableStore.load();


    //var obj = {"type":"list","page_size":"2"};
    //obj.start = 1;
    //tableStore.set('pageSize', 2);
    //tableStore.load(obj);

    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/planned_record/do_delete'); ?>',
            data: {planned_record_id: row.planned_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功：', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_execute(_index, row) {
        url = "?app_act=pur/planned_record/execute&planned_record_id=" + row.planned_record_id.toString();
        _do_execute(url, 'table');
    }
    function  do_re_check(_index, row) {
        url = '?app_act=pur/planned_record/do_check';
        data = {id: row.planned_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_check(_index, row) {
        url = '?app_act=pur/planned_record/do_check';
        data = {id: row.planned_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    function do_finish(_index, row) {
        url = '?app_act=pur/planned_record/do_finish';
        data = {id: row.planned_record_id, record_code: row.record_code};
        _do_operate(url, data, 'table');
    }


    /**
     * 查看采购订单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.planned_record_id)
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.planned_record_id);
    }
    function view(planned_record_id) {
        openPage('<?php echo base64_encode('?app_act=pur/planned_record/view&planned_record_id') ?>' + planned_record_id, '?app_act=pur/planned_record/view&planned_record_id=' + planned_record_id, '采购订单详情');
    }
</script>
