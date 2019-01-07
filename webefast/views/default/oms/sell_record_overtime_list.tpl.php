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
render_control('PageHead', 'head1', array('title' => '签收超时列表',
    'links' => array(
    ),
    'ref_table' => 'table'
));
?>
<?php
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_ext_list')) {
    $buttons[] = array(
        'label' => '导出',
        'id' => 'exprot_list',
    );
}
render_control('SearchForm', 'searchForm', array(
    'buttons' => $buttons,
    'show_row' => 2,
    'fields' => array(
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
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
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '200',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{deal_code_list}</a>',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
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
                'title' => '发货时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '地址',
                'field' => 'receiver_address',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '揽件时间',
                'field' => 'embrace_time',
                'width' => '150',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_overtime_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
//    'customFieldTable' => 'oms/sell_record_combine_ex_list',
    'export' => array('id' => 'exprot_list', 'conf' => 'overtime_record_list', 'name' => '超时订单列表', 'export_type' => 'file'),
//    'CellEditing' => true,
//    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
//      'cookie_page_size'=>'sell_record_exlist_page_size',
));
?>
<span >
    <a href="javascript:view_1()" style="color:red">注：需要开启快递鸟服务，仅支持有快递跟踪记录的交易（快递揽件成功的包裹）</a>
</span>

<script>
    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
        openPage(window.btoa(url),url,'签收超时订单详情');
    }
    function view_1() {
    var url = '?app_act=sys/params/do_list&page_no=app';
    openPage(window.btoa(url),url,'系统参数设置');
   }
</script>

