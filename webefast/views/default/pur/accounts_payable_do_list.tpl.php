<?php echo load_js('comm_util.js')?>
<style type="text/css">
    #supplier_name{
        width:185px;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '待付款列表',
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
$payment_status_arr = array(
    array('0', '未付款'),
    array('2', '部分付款'),
);
$keyword_type = array();
$keyword_type['pur_record_code'] = '入库单编号';
$keyword_type['planned_record_code'] = '采购订单编号';
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
            'label' => '创建时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'create_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'create_time_end', 'remark' => ''),
            )
        ),
//        array(
//            'label' => '单据编号',
//            'type' => 'input',
//            'id' => 'record_code',
//        ),
        array(
            'label' => '付款状态',
            'type' => 'select_multi',
            'id' => 'is_payment',
            'data' => $payment_status_arr,
        ),
        array(
            'label' => '供应商',
            'type' => 'group',
            'field' => 'supplier_code',
            'child' => array(
                array('type' => 'select_multi', 'field' => 'supplier_code', 'data' => $order_supplier, 'readonly' => 1, 'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            ),
        ),
        array(
            'label' => '备注',
            'type' => 'input',
            'id' => 'remark',
            'data' => '',
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
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看付款记录',
                        'callback' => 'do_payment_detail'
                    ),
                    array(
                        'id' => 'add',
                        'title' => '添加付款记录',
                        'callback' => 'add_payment_detail',
                        //'priv' => 'pur/accounts/add_payment_detail',
                        'show_cond' => 'obj.is_payment != 1',
                        //'confirm' => '确认要完成吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购入库单编号',
                'field' => 'purchaser_record_code',
                'width' => '120',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href="javascript:purchaser_view({purchaser_record_id})">{purchaser_record_code}</a>',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '采购订单编号',
                'field' => 'planned_record_code',
                'width' => '130',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href=javascript:planned_view({planned_record_id})>{planned_record_code}</a>',
//                ),
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
                'title' => '入库数量',
                'field' => 'finish_num',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单金额',
                'field' => 'record_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付金额',
                'field' => 'payment_money',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '待付金额',
                'field' => 'diff_money',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款状态',
                'field' => 'is_payment_str',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '创建时间',
                'field' => 'create_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'pur/AccountsPayableModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'planned_record_code',
    'export' => array('id' => 'exprot_list', 'conf' => 'accounts_payable_do_list', 'name' => '待付款列表', /*'export_type' => 'file'*/),
//    'params' => array('filter' => array('user_id' => $response['user_id'])),
    'CheckSelection' => true,
));
?>

<div id="TabPage1Contents">
    <div>
        <ul  class="toolbar frontool"  id="ToolBar1">
            <li class="li_btns"><button class="button button-primary btn_opt_custom_money" onclick="add_payment_money()">批量添加收款记录</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_edit_remark">批量备注</button></li>
            <div class="front_close">&lt;</div>
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
    </div>
</div>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
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
    function add_payment_money() {
        get_checked($(this), function (ids) {
            var ids = ids.toString();
            add_payment_info(ids);
        });
    }
    function add_payment_info(ids) {
        var url = '?app_act=pur/accounts_payable/add_payment_money&params=' + ids
        openPage(window.btoa(url), url, '添加付款记录');
    }
    function add_payment_detail(_index, row) {
        var record_code = '';
        if(row.record_type == 'purchaser') {
            record_code = row.purchaser_record_code;
        } else if(row.record_type == 'planned') {
            record_code = row.planned_record_code;
        }
        add_payment_info(record_code);
    }
    //读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择单据", 'error');
            return false;
        }      
        var supplier_code = '';
        for (var i in rows) {
            var row = rows[i];
            if(supplier_code == '') {
                supplier_code = row.supplier_code;
            } else {
                if(supplier_code !== row.supplier_code) {
                    BUI.Message.Alert("请选择相同供应商的单据", 'error');
                    return false;
                }
            }
            if(row.record_type == 'purchaser') {
                ids.push(row.purchaser_record_code);
            } else if(row.record_type == 'planned') {
                ids.push(row.planned_record_code);
            }
        }
        ids.join(',');        
        func.apply(null, [ids]);
    }
    
    //读取已选中项 验证批量备注
    function get_checked_remark(obj, func) {
        var ids = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择单据", 'error');
            return false;
        }      
        var supplier_code = '';
        for (var i in rows) {
            var row = rows[i];           
            if(row.record_type == 'purchaser') {
                ids.push(row.record_type +'_'+row.purchaser_record_code);
            } else if(row.record_type == 'planned') {
                ids.push(row.record_type +'_'+row.planned_record_code);
            }
        }
        ids.join(',');        
        func.apply(null, [ids]);
    }
  
    function planned_view(planned_record_id) {
        openPage('<?php echo base64_encode('?app_act=pur/planned_record/view&planned_record_id') ?>' + planned_record_id, '?app_act=pur/planned_record/view&planned_record_id=' + planned_record_id, '采购订单详情');
    }
    function purchaser_view(purchaser_record_id) {
        var url = '?app_act=pur/purchase_record/view&purchaser_record_id=' + purchaser_record_id
        openPage(window.btoa(url), url, '入库单详情');
    }
    function do_payment_detail(_index, row) {
        var record_code = '';
        if(row.record_type == 'purchaser') {
            record_code = row.purchaser_record_code;
        } else if(row.record_type == 'planned') {
            record_code = row.planned_record_code;
        }
        new ESUI.PopWindow("?app_act=pur/payment/do_view_payment&record_code=" + record_code + "&record_type=" + row.record_type, {
            title: "付款记录",
            width: 800,
            height: 550,
            buttons:[
              {
                text:'关闭',
                id:'money_check',
                elCls : 'button',
                handler : function(){
                  this.close();
                }
              }
            ],
            onBeforeClosed: function () {
            },
            onClosed: function () {
            }
        }).show();
    }
    parent.load_table = function () {
        tableStore.load();
    }
    parent.view_add_payment = function (record_code) {
        add_payment_info(record_code);
    }       
    //批量备注
    $(".btn_opt_edit_remark").click(function(){
        get_checked_remark($(this), function (ids) {
            new ESUI.PopWindow("?app_act=pur/accounts_payable/edit_remark&purchaser_record_code_list=" + ids.toString(), {
                title: "批量备注",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    //刷新数据
                    tableStore.load();
                }
            }).show();
        });
    });
</script>
