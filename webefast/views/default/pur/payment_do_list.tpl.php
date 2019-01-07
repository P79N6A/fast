<style type="text/css">
    #supplier_name{
        width:185px;
    }
    #payment_time_start,#payment_time_end{
        width: 90px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '付款明细',
    'links' => array(
//        array('url' => 'pur/planned_record/multi_import', 'title' => '多采购单批量导入'),
//        array('url' => 'pur/planned_record/detail&app_scene=add', 'title' => '添加采购订单', 'is_pop' => true, 'pop_size' => '500,580'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$supplier = load_model('base/SupplierModel')->get_purview_supplier();
$order_supplier = load_model('base/CustomModel')->array_order($supplier, 'supplier_name');
$keyword_type = array();
$keyword_type['serial_number'] = '流水号';
$keyword_type['record_code'] = '单据编号';
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
//            'help' => '以下字段支持查询：单据编号、流水号',
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'payment_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'payment_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'payment_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier_code',
            'child' => array(
                array('type' => 'select_multi', 'field' => 'supplier_code', 'data' => $order_supplier, 'readonly' => 1, 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            ),
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
                'width' => '50',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'cancellation',
                        'title' => '作废',
                        'callback' => 'do_cancellation',
                        'priv' => 'pur/payment/do_cancellation',
                        'show_cond' => 'obj.status == 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
//                        'priv' => 'pur/payment/do_delete',
                        'show_cond' => 'obj.status == 2'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status_str',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '流水号',
                'field' => 'serial_number',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '130',
                'align' => '',
            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '采购入库单编号',
//                'field' => 'purchaser_record_code',
//                'width' => '120',
//                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href="javascript:purchaser_view({purchaser_record_id})">{purchaser_record_code}</a>',
//                ),
//            ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '采购订单编号',
//                'field' => 'planned_record_code',
//                'width' => '120',
//                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href=javascript:planned_view({planned_record_id})>{planned_record_code}</a>',
//                ),
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'payment_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商',
                'field' => 'supplier_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '金额',
                'field' => 'money',
                'width' => '130',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作人',
                'field' => 'operator',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'pur/PaymentModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'serial_number',
    'export' => array('id' => 'exprot_list', 'conf' => 'payment_do_list', 'name' => '付款明细', 'export_type' => 'file'),
//    'params' => array('filter' => array('user_id' => $response['user_id'])),
//    'CheckSelection' => true,
));
?>

<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    
    /*function planned_view(planned_record_id) {
        openPage('<?php // echo base64_encode('?app_act=pur/planned_record/view&planned_record_id') ?>' + planned_record_id, '?app_act=pur/planned_record/view&planned_record_id=' + planned_record_id, '采购订单详情');
    }
    function purchaser_view(purchaser_record_id) {
        var url = '?app_act=pur/purchase_record/view&purchaser_record_id=' + purchaser_record_id
        openPage(window.btoa(url), url, '入库单详情');
    }*/
    function do_cancellation(_index,row) {
        var url = "?app_act=pur/payment/do_cancellation";
        $.post(url, {serial_number: row.serial_number}, function (ret) {
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
            } else {
                BUI.Message.Alert(ret.message,'success');
            }
            tableStore.load();
        }, 'json');
    }
    function do_delete(_index,row) {
        var url = "?app_act=pur/payment/do_delete";
        $.post(url, {serial_number: row.serial_number}, function (ret) {
            if(ret.status < 0) {
                BUI.Message.Alert(ret.message,'error');
            } else {
                BUI.Message.Alert(ret.message,'success');
            }
            tableStore.load();
        }, 'json');
    }
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
                text: '保存继续',
                elCls: 'button button-primary',
                handler: function () {
                    var data = top.SelectoGrid.getSelection();
                    if (data.length > 0) {
                        deal_data_1(data, _type);
                    }
                    auto_enter('#supplier_code');
                }
            },
            {
                text: '保存退出',
                elCls: 'button button-primary',
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
                text: '重置',
                elCls: 'button',
                handler: function () {
                    show_select('supplier');
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
        if (string_code == "") {
            string_code = supplier_code;
            $("#supplier_code").val(string_code);
        } else {
            string_code = string_code + ',' + supplier_code;
            $("#supplier_code").val(string_code);
        }
        if (string_name == "") {
            string_name = supplier_name;
            $("#supplier_code_select_multi .bui-select-input").val(string_name);
        } else {
            string_name = string_name + ',' + supplier_name;
            $("#supplier_code_select_multi .bui-select-input").val(string_name);
        }

    }
    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).trigger(e);
    }
</script>
