<style type="text/css">
    .well { min-height: 100px; }
    #supplier_name{ width:185px; }
    #order_time_start{ width:100px; }
    #order_time_end{ width:100px; }
    #exprot_list{ width:120px; }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '通知单列表',
    'links' => array(
        array('url' => 'pur/order_record/detail&app_scene=add', 'title' => '添加通知单', 'is_pop' => true, 'pop_size' => '500,550'),
    //array('url' => 'pur/order_record/import&app_scene=add', 'title' => '导入', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['relation_code'] = '关联采购订单号';
$keyword_type['init_code'] = '原单号';
$keyword_type['remark'] = '备注';

$keyword_type = array_from_dict($keyword_type);

$supplier = load_model('base/SupplierModel')->get_purview_supplier();
$order_supplier = load_model('base/CustomModel')->array_order($supplier,'supplier_name');

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出明细',
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
            'help' => '以下字段支持查询：单据编号、商品编码、商品条形码、关联采购订单号、原单号、备注',
        ),
        array(
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier',
            'child' => array(
                    array('type' => 'select_multi','field'=>'supplier_code','data' => $order_supplier,'readonly'=>1,'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
                ),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_time_end', 'remark' => ''),
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
            'label' => '差异款',
            'type' => 'select',
            'id' => 'difference_models',
            'data' => ds_get_select_by_field('is_rush'),
        ),
        array(
            'label' => '确认状态',
            'title' => '',
            'type' => 'select',
            'id' => 'check_status',
            'data' => ds_get_select_by_field('is_sure',1)
        ),
        array(
            'label' => '是否有入库单',
            'title' => '',
            'type' => 'select',
            'id' => 'execute_status',
            'data' => ds_get_select_by_field('is_build',1)
        ),
        array(
            'label' => '完成状态',
            'title' => '',
            'type' => 'select',
            'id' => 'finish_status',
            'data' => ds_get_select_by_field('finish_status',1)
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
                        'priv' => 'pur/order_record/do_check',
                        'show_cond' => 'obj.is_check == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_check',
                        'priv' => 'pur/order_record/do_check',
                        'show_cond' => 'obj.is_finish != 1 && obj.is_check == 1 && obj.is_execute == 0 '
                    ),
                    array(
                        'id' => 'execute',
                        'title' => '生成入库单',
                        'callback' => 'do_execute',
                        'priv' => 'pur/order_record/do_execute',
                        'show_cond' => 'obj.is_finish != 1 && obj.is_wms != 1 && obj.is_check == 1'
                    ),
                    array(
                        'id' => 'finish',
                        'title' => '完成',
                        'callback' => 'do_finish',
                        'priv' => 'pur/order_record/do_finish',
                        'show_cond' => 'obj.is_finish != 1&& obj.is_wms != 1 && obj.is_check == 1',
                        'confirm' => '确认要完成吗？'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'pur/order_record/do_delete',
                        'show_cond' => 'obj.is_check == 0',
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
                'title' => '生成入库单',
                'field' => 'is_execute',
                'width' => '80',
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
//                    'value' => '<a href="' . get_app_url('pur/order_record/view') . '&order_record_id={order_record_id}">{record_code}</a>',
                    'value' => '<a href="javascript:view({order_record_id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购订单',
                'field' => 'relation_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单日期',
                'field' => 'order_time',
                'width' => '150',
                'align' => '',
            //'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库期限',
                'field' => 'in_time',
                'width' => '100',
                'align' => '',
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
                'field' => 'different_num',
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
                'title' => '备注',
                'field' => 'remark',
                'width' => '100',
                'align' => ''
            ),
        ),
    ),
    'dataset' => 'pur/OrderRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'order_record_id',
    'params' => array('filter' => array('user_id' => $response['user_id'], 'record_code' => isset($response['data']['record_code']) ? $response['data']['record_code'] : '')),
    'export'=> array('id'=>'exprot_list','conf'=>'order_record_list','name'=>'采购通知单','export_type'=>'file'),
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
            $("#supplier_name").attr("value","");
            $("#supplier_code").attr("value","");
    });
    $("#base_supplier").click(function () {
            show_select('supplier');
        });
    function show_select(_type) {
            var param = {};
            if (typeof (top.dialog) != 'undefined') {
                top.dialog.remove(true);
            }
            var url = '?app_act=pur/order_record/select_supplier';
            var buttons = [
                   {
                    text:'保存继续',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#supplier_code');
                    }
                  },
                  {
                    text:'保存退出',
                    elCls : 'button button-primary',
                    handler: function () {
                        var data = top.SelectoGrid.getSelection();
                        if (data.length > 0) {
                            deal_data_1(data, _type);
                        }
                        auto_enter('#supplier_code');
                        this.close();
                    }
                  },
                  {
                    text:'重置',
                    elCls : 'button',
                    handler: function () {
                        reset_supplier();
                    }
                  }
                ];
            top.BUI.use('bui/overlay', function (Overlay) {
                top.dialog = new Overlay.Dialog({
                    title: '选择供应商',
                    width: '700',
                    height: '550',
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
        function reset_supplier(){
            $("#supplier_code").attr("value","");
            $("#supplier_code_select_multi .bui-select-input").attr("value","");
        }
        function deal_data_1(obj, _type) {
            var supplier_name = new Array();
            var supplier_code = new Array();
            var string_code = "";
            var string_name = "";
            string_code = $("#supplier_code").val();
            string_name = $("#supplier_code_select_multi .bui-select-input").val();
            $.each(obj, function (i, val) {
                supplier_name[i] = val[_type + '_name'];
                supplier_code[i] = val[_type + '_code'];
            });
                supplier_name = supplier_name.join(',');
                supplier_code = supplier_code.join(',');
            if(string_code == ""){   
                string_code =  supplier_code;
                $("#supplier_code").val(string_code);
            }else{
                string_code = string_code + ','+ supplier_code;
                $("#supplier_code").val(string_code);
            }
            if(string_name == ""){
                string_name =  supplier_name;
                $("#supplier_code_select_multi .bui-select-input").val(string_name);
//                $('#supplier_name').find('option[value="'+string_name+'"]').attr('selected',true);
//                $('#supplier_name').parent().find('.valid-text').html('');
            }else{
                string_name = string_name + ','+ supplier_name;
                $("#supplier_code_select_multi .bui-select-input").val(string_name);
//                $('#supplier_name').find('option[value="'+string_name+'"]').attr('selected',true);
//                $('#supplier_name').parent().find('.valid-text').html('');
            }
            
        }
        function auto_enter(_id) {
            var e = jQuery.Event("keyup");//模拟一个键盘事件
            e.keyCode = 13;//keyCode=13是回车
            $(_id).trigger(e);
        }
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/order_record/do_delete'); ?>',
            data: {order_record_id: row.order_record_id},
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
    var order_record_id = row.order_record_id.toString();
        //判断有没有未入库的入库单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/order_record/out_relation'); ?>',
            data: {id: order_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act=pur/order_record/execute&order_record_id=" + row.order_record_id.toString();
                    _do_execute(url, 'table');
                } else {
                    if (ret.status == '-1') {
                        if (confirm("存在未入库的采购入库单，是否继续？")) {
                            url = "?app_act=pur/order_record/execute&order_record_id=" + row.order_record_id.toString();
                            _do_execute(url, 'table');
                        }
                    }
                }
            }
        })

    }
    function  do_re_check(_index, row) {
        url = '?app_act=pur/order_record/do_check';
        data = {id: row.order_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_check(_index, row) {
        url = '?app_act=pur/order_record/do_check';
        data = {id: row.order_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    function do_finish(_index, row) {
        url = '?app_act=pur/order_record/do_finish';
        data = {id: row.order_record_id, record_code: row.record_code};
        _do_operate(url, data, 'table');
    }

    /**
     * 查看通知单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.order_record_id);
    }
    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.order_record_id);
    }

    function view(order_record_id) {
        var url = '?app_act=pur/order_record/view&order_record_id=' + order_record_id
        openPage(window.btoa(url), url, '通知单详情');
    }

</script>

