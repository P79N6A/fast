<style type="text/css">
    #order_time_start,#order_time_end{
        width: 100px;
    }
</style>
<?php
$keyword_type = array();
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['barcode'] = '商品条形码';

$keyword_type = array_from_dict($keyword_type);
render_control('PageHead', 'head1', array('title' => '经销采购订单列表',
    'links' => array(
        array('url' => 'fx/purchase_record/detail&app_scene=add', 'title' => '新增经销采购单', 'is_pop' => true, 'pop_size' => '500,550'),
    ),
    'ref_table' => 'table'
));
?>
<?php
$login_type = CTX()->get_session('login_type');
$fields = array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '以下字段支持模糊查询：单据编号、商品编码、商品名称、商品条形码',
        ),
        array(
            'label' => '仓库',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '单据状态',
            'title' => '',
            'type' => 'select',
            'id' => 'is_deliver',
            'data' => array(
                array('', '请选择'), array('0', '未出库'), array('1', '已出库'), array('2', '部分出库')
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
            'label' => '业务日期',
            'type' => 'group',
            'field' => 'record_time',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
    );
if ($login_type != 2) {
    $fields[] = 
        array(
            'label' => '分销商',
            'title' => '',
            'type' => 'select_multi',
            'id' => 'custom_code',
//            'data' => ds_get_select('custom'),
            'data' => load_model('base/CustomModel')->get_purview_custom_select('pt_fx'),
        );
}
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
//        array(
//            'label' => '导出明细',
//            'id' => 'exprot_detail',
//        ),
    ),
    'fields' => $fields
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
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'priv' => 'fx/purchase_record/do_delete',
                        'show_cond' => 'obj.is_check != 1',
                        'confirm' => '确认要删除此信息吗？'
                    ),
                ),
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
                'title' => '下单时间',
                'field' => 'order_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '分销商',
                'field' => 'custom_name',
                'width' => '150',
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
                'title' => '计划采购数',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际到货数',
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
                'title' => '状态',
                'field' => 'is_deliver_name',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'fx/PurchaseRecordModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'purchaser_record_id',
    'export' => array('id' => 'exprot_detail', 'conf' => 'fx_purchase_record_detail', 'name' => '经销采购订单明细','export_type' => 'file'),
    //'RowNumber'=>true,
    //'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    var login_type = "<?php echo $response['login_type']?>";
    //导出明细
    $(function() {
        if(login_type == 2){
            $("#custom_code_select_multi .bui-select").unbind();
        }
        
//      $('#exprot_detail').click(function(){
//        var url = tableStore.get('url');   
//        params = tableStore.get('params');
//       
//        params.ctl_type = 'export';
//        params.ctl_export_conf = 'purchase_record_list_detail';
//        params.ctl_export_name =  '采购入库单明细';
//        var obj = searchFormForm.serializeToObject();
//          for(var key in obj){
//                 params[key] =  obj[key];
//	  } 
//     
//          for(var key in params){
//                url +="&"+key+"="+params[key];
//	  }
//          params.ctl_type = 'view';
//          window.open(url); 
//       // window.location.href = url;
//    });
    });
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('fx/purchase_record/do_delete'); ?>',
            data: {purchaser_record_id: row.purchaser_record_id},
            success: function(ret) {
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
    parent.do_detail = function(id) {
        view(id);
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
    //入库
    function do_checkin(_index, row) {
        url = '?app_act=pur/purchase_record/do_checkin';
        data = {record_code: row.record_code};
        _do_operate(url, data, 'table');
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
        var url = '?app_act=fx/purchase_record/view&purchaser_record_id=' + purchaser_record_id
        openPage(window.btoa(url), url, '经销采购单详情');
    }
</script>