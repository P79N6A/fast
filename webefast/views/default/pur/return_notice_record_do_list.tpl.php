<style type="text/css">
    .well {
        min-height: 70px;
    }
    #order_time_start{
        width:100px;
    }
    #order_time_end{
        width:100px;
    }
</style>
<div class="page-header1" style="width:98%; display: block; clear: both; position: fixed; top:0px; left:0px; background-color: #FFF; padding: 4px 1%; z-index: 9999; box-shadow:0px 0px 5px #ccc">
    <span class="page-title">
        <h2>采购退货通知单</h2>
    </span>
    <span class="page-link">
        <?php if (load_model('sys/PrivilegeModel')->check_priv('pur/return_notice_record/detail&app_scene=add')) { ?>
            <span class="action-link">
                <a class="button button-primary" href="javascript:PageHead_show_dialog('?app_act=pur/return_notice_record/detail&app_scene=add&app_show_mode=pop', '添加采购退货通知单', {w:500,h:550})"> 添加采购退货通知单</a>
            </span>
        <?php } ?>
        <button class="button button-primary" onclick="javascript:location.reload();">
            <i class="icon-refresh icon-white"></i>
            刷新
        </button>
    </span>
</div>
<div class="clear" style="margin-top: 40px; "></div>
<script type="text/javascript">

    var ES_PAGE_ID = 'pur/return_notice_record/do_list';

    function PageHead_show_dialog(_url, _title, _opts) {

        new ESUI.PopWindow(_url, {
            title: _title,
            width: _opts.w,
            height: _opts.h,
            onBeforeClosed: function () {
                tableStore.load();
                if (typeof _opts.callback == 'function')
                    _opts.callback();
            }
        }).show();
    }
</script>
<?php
/*
  render_control('PageHead', 'head1', array('title' => '采购退货通知单',
  'links' => array(
  array('url' => 'pur/return_notice_record/detail&app_scene=add', 'title' => '添加采购退货通知单', 'is_pop' => true, 'pop_size' => '500,550'),
  ),
  'ref_table' => 'table'
  )); */
?>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
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
            'label' => '供应商',
            'title' => '',
            'type' => 'group',
            'id' => 'supplier',
        //    'data' => $order_supplier,
            'child' => array(
                array('type' => 'select_multi','field'=>'supplier_code','data' => $order_supplier,'readonly'=>1,'remark' => "<a href='#' id = 'base_supplier'><img src='assets/img/search.png'></a>"),
            ),
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store()
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'is_stop',
            'data' => array(
                array('0', '未终止'), array('1', '已终止')
            )),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'order_time_start',),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'order_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
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
                        'callback' => 'do_sure',
                        'priv' => 'pur/return_notice_record/do_sure',
                        'show_cond' => 'obj.is_sure == 0'
                    ),
                    array(
                        'id' => 'check2',
                        'title' => '取消确认',
                        'callback' => 'do_re_sure',
                        'priv' => 'pur/return_notice_record/do_sure',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_finish == 0 && obj.is_execute == 0 '
                    ),
                    array(
                        'id' => 'enable',
                        'title' => '生成退货单',
                        'callback' => 'do_execute',
                        'priv' => 'pur/return_notice_record/do_execute',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_wms != 1 && obj.is_finish == 0'
                    ),
                    array(
                        'id' => 'stop',
                        'title' => '终止',
                        'callback' => 'do_stop',
                        'priv' => 'pur/return_notice_record/do_stop',
                        'show_cond' => 'obj.is_stop == 0 && obj.is_sure == 1 && obj.is_finish == 0 && obj.is_wms != 1 '
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'pur/return_notice_record/do_delete',
                        'show_cond' => 'obj.is_sure != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认',
                'field' => 'is_sure',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生成退货单',
                'field' => 'is_execute',
                'width' => '80',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '终止',
                'field' => 'is_stop',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '单据编号',
                'field' => 'record_code',
                'width' => '160',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href=javascript:view({return_notice_record_id})>{record_code}</a>',
                ),
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
                'align' => '',
                'format' => array('type' => 'date')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '供应商',
                'field' => 'supplier_code_name',
                'width' => '120',
                'align' => '',
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
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '完成数量',
                'field' => 'finish_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '总差异数',
                'field' => 'diff_num',
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
        )
    ),
    'dataset' => 'pur/ReturnNoticeRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'return_notice_record_id',
    'params' => array('filter' => array('user_id' => $response['user_id'])),
    'export' => array('id' => 'exprot_detail', 'conf' => 'pur_return_notice_record', 'name' => '采购退货通知单', 'export_type' => 'file'),
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

    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/return_notice_record/do_delete'); ?>',
            data: {return_notice_record_id: row.return_notice_record_id},
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


    function  do_re_sure(_index, row) {
        url = '?app_act=pur/return_notice_record/do_sure';
        data = {id: row.return_notice_record_id, type: 'disable'};
        _do_operate(url, data, 'table');
    }
    function  do_sure(_index, row) {

        url = '?app_act=pur/return_notice_record/do_sure';
        data = {id: row.return_notice_record_id, type: 'enable'};
        _do_operate(url, data, 'table');
    }
    //终止
    function do_stop(_index, row) {

        url = '?app_act=pur/return_notice_record/do_stop';
        data = {id: row.return_notice_record_id};

        _do_operate(url, data, 'table');
    }
    //生成销货单
    function do_execute(_index, row) {
        //判断是否有未入库销货单
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('pur/return_notice_record/out_relation'); ?>',
            data: {id: row.return_notice_record_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    url = "?app_act=pur/return_notice_record/execute&return_notice_record_id=" + row.return_notice_record_id.toString();
                    _do_execute(url, 'table');

                } else {

                    if (ret.status == '-1') {
                        if (confirm("存在未出库的退货通知单，是否继续？")) {
                            url = "?app_act=pur/return_notice_record/execute&return_notice_record_id=" + row.return_notice_record_id.toString();
                            _do_execute(url, 'table');
                        }
                    }

                    // BUI.Message.Alert(ret.message, type);
                }
            }
        });



    }
    /*
     function do_enable(_index, row) {
     _do_set_check(_index, row, 'enable');
     }
     */


    /**
     * 查看退货通知单详情
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        view(row.return_notice_record_id);
    }

    //数据行双击打开新页面显示详情
    function showDetail(_index, row) {
        view(row.return_notice_record_id);
    }

    function view(return_notice_record_id) {
        openPage('<?php echo base64_encode('?app_act=pur/return_notice_record/view&return_notice_record_id') ?>' + return_notice_record_id, '?app_act=pur/return_notice_record/view&return_notice_record_id=' + return_notice_record_id, '退货通知单详情');
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

    function deal_data_1(obj, _type) {
        var supplier_name = new Array();
        var supplier_code = new Array();
        var string_code = $("#supplier_code").val();
        var string_name = $("#supplier_code_select_multi .bui-select-input").val();
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
        }else{
            string_name = string_name + ','+ supplier_name;
            $("#supplier_code_select_multi .bui-select-input").val(string_name);
        }
    }

    function auto_enter(_id) {
        var e = jQuery.Event("keyup");//模拟一个键盘事件
        e.keyCode = 13;//keyCode=13是回车
        $(_id).trigger(e);
    }


    function reset_supplier(){
        $("#supplier_code").attr("value","");
        $("#supplier_code_select_multi .bui-select-input").attr("value","");
    }
</script>


