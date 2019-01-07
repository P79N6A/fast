<style type="text/css">
    #order_time_start,#order_time_end{
        width: 100px;
    }
</style>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['relation_code'] = '通知单号';
$keyword_type['planned_code'] = '采购单号';
$keyword_type['code_name'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
//$keyword_type['init_code'] = '原单号';

$keyword_type = array_from_dict($keyword_type);
render_control('PageHead', 'head1', array('title' => '采购入库单列表',
    'links' => array(
        array('url' => 'pur/purchase_record/detail&app_scene=add', 'title' => '添加采购入库单', 'is_pop' => true, 'pop_size' => '500,550'),
    // array('url' => 'pur/purchase_record/import&app_scene=add', 'title' => '导出', 'is_pop' => true, 'pop_size' => '500,300'),
    ),
    'ref_table' => 'table'
));
?>
<?php
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
            'label' => '导出',
            'id' => 'exprot_list',
        ),
        array(
            'label' => '导出明细',
            'id' => 'exprot_detail',
        ),
    ),
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '以下字段支持查询：单据编号、通知单号、采购单号、商品编码、商品条形码',
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
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '入库类型',
            'title' => '',
            'type' => 'select',
            'id' => 'record_type_code',
            'data' => ds_get_select('record_type', 2, array('record_type_property' => 0))
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select',
            'id' => 'is_check_and_accept',
            'data' => array(
                array('0', '未验收'), array('1', '已验收'), array('', '请选择')
            )),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'bill_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'bill_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '验收日期',
            'type' => 'group',
            'field' => 'enter_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'enter_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'enter_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'order_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '差异款',
            'type' => 'select',
            'id' => 'difference_models',
            'data' => ds_get_select_by_field('is_rush'),
        ),
        array(
            'label' => '备注',
            'title' => '',
            'type' => 'input',
            'id' => 'remark',
        )
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
                'width' => '210',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '验收',
                        'callback' => 'do_checkin',
                        'priv' => 'pur/purchase_record/do_checkin',
                        'show_cond' => 'obj.is_check_and_accept != 1'
                    ),
                    array(
                        'id' => 'accept_record_date',
                        'title' => '按业务日期验收',
                        'callback' => 'do_checkin_by_record_date',
                        'priv' => 'pur/purchase_record/do_checkin_time',
                        'show_cond' => 'obj.is_check_and_accept != 1'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'pur/purchase_record/do_delete',
                        'show_cond' => 'obj.is_check_and_accept != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '验收',
                'field' => 'is_check_and_accept',
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
//                    'value' => '<a href="' . get_app_url('pur/purchase_record/view') . '&purchaser_record_id={purchaser_record_id}">{record_code}</a>',
                    'value' => '<a href="javascript:view({purchaser_record_id})">{record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '通知单号',
                'field' => 'relation_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'order_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '业务日期',
                'field' => 'record_time',
                'width' => '100',
                'align' => ''
            ),
            array(
            'type' => 'text',
            'show' => 1,
            'title' => '验收日期',
            'field' => 'enter_time',
            'width' => '180',
            'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库类型',
                'field' => 'record_type_name',
                'width' => '120',
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
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库数',
                'field' => 'finish_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总差异数',
                'field' => 'diff_num',
                'width' => '60',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总金额',
                'field' => 'sum_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'remark',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '入库期限',
                'field' => 'in_time',
                'width' => '100'
            ),
        )
    ),
    'dataset' => 'pur/PurchaseRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'purchaser_record_id',
    'customFieldTable' => 'purchase_record_do_list/table',
    'export' => array('id' => 'exprot_list', 'conf' => 'purchase_record_list', 'name' => '采购入库单','export_type'=>'file'),
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
    //导出明细
$(function(){
      $('#exprot_detail').click(function(){
        var url =  '?app_act=sys/export_csv/export_show';
//          var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');

        params.ctl_type = 'export';
        params.ctl_export_conf = 'purchase_record_list_detail';
       <?php echo   create_export_token_js('pur/PurchaseRecordModel::get_by_page');?>
        params.ctl_export_name =  '采购入库单明细';
        var obj = searchFormForm.serializeToObject();
          for(var key in obj){
                 params[key] =  obj[key];
	  }

          for(var key in params){
                url +="&"+key+"="+params[key];
	  }
          window.open(url);
       // window.location.href = url;
});
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
            url: '<?php echo get_app_url('pur/purchase_record/do_delete'); ?>',
            data: {purchaser_record_id: row.purchaser_record_id},
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

    function  do_re_check(_index, row) {
        url = '?app_act=pur/purchase_record/do_check';
        data = {id: row.purchaser_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_check(_index, row) {
        url = '?app_act=pur/purchase_record/do_check';
        data = {id: row.purchaser_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    //差异校验
    function check_diff_num (row,type) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '?app_act=pur/purchase_record/check_diff_num',
            data: {record_code: row.record_code},
            success: function (ret) {
                var sta = ret.status;
                if (sta == 1) {
                    BUI.Message.Confirm('是否确认验收？ ', function(){
                        if (type === 'normal') {
                            url = '?app_act=pur/purchase_record/do_checkin';
                            data = {record_code: row.record_code};
                            _do_operate(url, data, 'table');
                        } else {
                            url = '?app_act=pur/purchase_record/do_checkin_by_record_date';
                            data = {record_code: row.record_code};
                            _do_operate(url, data, 'table');
                        }
                    }, 'question');
                    tableStore.load();
                } else if (sta == 2) {
                    BUI.Message.Confirm(ret.message, function(){
                        if (type === 'normal') {
                            url = '?app_act=pur/purchase_record/do_checkin';
                            data = {record_code: row.record_code};
                            _do_operate(url, data, 'table');
                        } else {
                            url = '?app_act=pur/purchase_record/do_checkin_by_record_date';
                            data = {record_code: row.record_code};
                            _do_operate(url, data, 'table');
                        }
                    }, 'question');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, 'error');
                }
            }
        });
    }
    
    //入库
    function do_checkin(_index, row) {
        check_diff_num(row,'normal');
    }
    //按业务日期验收
    function do_checkin_by_record_date(_index, row) {
        check_diff_num(row,'date');
    }

    /**
     * 查看入库单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.purchaser_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.purchaser_record_id);
    }

    function view(purchaser_record_id) {
        var url = '?app_act=pur/purchase_record/view&purchaser_record_id=' + purchaser_record_id
        openPage(window.btoa(url), url, '入库单详情');
    }
</script>