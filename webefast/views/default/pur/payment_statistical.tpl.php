<style type="text/css">
    #supplier_name{
        width:185px;
    }
    #pay_time_start,#pay_time_end{
        width: 100px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '付款统计',
    'links' => array(
//        array('url' => 'pur/planned_record/multi_import', 'title' => '多采购单批量导入'),
//        array('url' => 'pur/planned_record/detail&app_scene=add', 'title' => '添加采购订单', 'is_pop' => true, 'pop_size' => '500,580'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$payment_status_arr = array(
    array('1', '已全部付款'),
    array('2', '未全部付款'),
);
$supplier = load_model('base/SupplierModel')->get_purview_supplier();
$order_supplier = load_model('base/CustomModel')->array_order($supplier, 'supplier_name');
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
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier_code',
            'child' => array(
                array('type' => 'select_multi', 'field' => 'supplier_code', 'data' => $order_supplier, 'readonly' => 1, 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            ),
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '单据编号',
            'type' => 'input',
            'id' => 'record_code',
        ),
        array(
            'label' => '付款状态',
            'type' => 'select_multi',
            'id' => 'is_payment',
            'data' => $payment_status_arr,
        ),
    )
));
?>
<div id="data_count" style="margin-left:20%;">

</div>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
//            array(
//                'type' => 'button',
//                'show' => 1,
//                'title' => '操作',
//                'field' => '_operate',
//                'width' => '100',
//                'align' => '',
//                'buttons' => array(
//                    array(
//                        'id' => 'cancellation',
//                        'title' => '查看明细',
//                        'callback' => 'do_view_payment',
//                    ),
//                ),
//            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商名称',
                'field' => 'supplier_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '应付总金额<img height="23" width="23" data-align="top-right" class="tip" src="assets/images/tip.png" title="应付总金额=订单总金额-已付总金额" />',
                'field' => 'money_sum_payable',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单总金额',
                'field' => 'record_sum_money',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付总金额',
                'field' => 'pay_sum_money',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '时间段内付款总金额<img height="23" width="23" data-align="top-right" class="tip" src="assets/images/tip.png" title="为付款时间在查询条件“付款时间”所设定的时间段内、付给指定供应商的付款总金额。若查询条件“付款时间”为空，则此字段值应与已付总金额相等" />',
                'field' => 'pay_sum_paytime_money',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'pur/PaymentModel::payment_statistics',
    'queryBy' => 'searchForm',
    'idField' => 'supplier_code',
    'init' => 'nodata',
//    'export' => array('id' => 'exprot_list', 'conf' => 'planned_record_list', 'name' => '采购计划单', 'export_type' => 'file'),
//    'params' => array('filter' => array('user_id' => $response['user_id'])),
//    'CheckSelection' => true,
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    $(document).ready(function () {
        searchFormFormListeners['beforesubmit'].push(function (ev) {
            var obj = searchFormForm.serializeToObject();
            load_count(obj);
        });
    });
    function load_count(obj) {
        $.post("?app_act=pur/payment/payment_count", obj, function (data) {
            $("#data_count").html(data.data);
        },'json');
    }
    function do_cancellation(_index, row) {
        var url = "?app_act=pur/payment/do_cancellation";
        $.post(url, {serial_number: row.serial_number}, function (ret) {
            if (ret.status < 0) {
                BUI.Message.Alert(ret.message, 'error');
            } else {
                BUI.Message.Alert(ret.message, 'success');
            }
            tableStore.load();
        }, 'json');
    }
    function do_delete(_index, row) {
        var url = "?app_act=pur/payment/do_delete";
        $.post(url, {serial_number: row.serial_number}, function (ret) {
            if (ret.status < 0) {
                BUI.Message.Alert(ret.message, 'error');
            } else {
                BUI.Message.Alert(ret.message, 'success');
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
    function do_view_payment(_index,row) {
        var url = '?app_act=pur/payment/do_list';
        openPage(window.btoa(url), url, '付款明细');
    }
</script>
