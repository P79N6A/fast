<style>
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #money_start{width: 65px;}
    #money_end{width: 65px;}
</style>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php
render_control('PageHead', 'head1', array('title' => '发货超时订单',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
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
    'show_row' => 3,
    'fields' => array(     
         array(
            'label' => '超时天数',
            'type' => 'group',
            'field' => 'days',
            'child' => array(
                    array('title' => 'start', 'type' => 'input', 'field' => 'days_start', 'class' => 'input-small'),
                    array('pre_title' => '~', 'type' => 'input', 'field' => 'days_end',  'class' => 'input-small'),
            ),
		),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '仓库',
            'type' => 'select_multi',
            'id' => 'store_code',
            'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
    )
));
?>
<div>
合计超时订单笔数：(<span id="deliver_overtime_count"></span>)
</div>
<?php
$current_time = date('Y-m-d H:i:s');
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
           array(
                'type' => 'text',
                'show' => 1,
                'title' => '超时天数',
                'field' => 'days',
                'width' => '100',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '120',                
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '计划发货时间',
                'field' => 'plan_send_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付时间',
                'field' => 'pay_time',
                'width' => '150',
                'align' => '',
            ),
             array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认时间',
                'field' => 'check_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '通知配货时间',
                'field' => 'is_notice_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '80',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_deliver_overtime_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'init' => 'nodata',
    'export' => array('id' => 'exprot_list', 'conf' => 'get_deliver_overtime_list', 'name' => '发货超时订单列表','export_type' => 'file'),//
    'params' => array('filter' => array('current_time' => $current_time)),
));
?>
<div>
    <span style="color:red">超时发货订单定义：计划发货时间小于当前时间且未发货的订单。</span>
</div>
<script>
    $(document).ready(function() {
     searchFormFormListeners['beforesubmit'].push(function(ev) {
        var obj = searchFormForm.serializeToObject();
        count_all(obj);
     });

});
    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
        openPage(window.btoa(url),url,'发货超时订单详情');
    }

 function count_all(obj) {
        obj.current_time = '<?php echo $current_time;?>';
         $.post("?app_act=oms/sell_record/deliver_overtime_count",obj, function(data) {
         $("#deliver_overtime_count").html(data.data);
     }, "json");   
 }
</script>

