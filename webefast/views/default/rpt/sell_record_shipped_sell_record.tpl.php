<?php
$list = array(
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '订单号',
        'field' => 'sell_record_code',
        'width' => '150',
        'align' => '',
        'format_js' => array(
            'type' => 'html',
            'value' => '<a href=javascript:view({sell_record_code})>{sell_record_code}</a>',
        ),
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '发货时间',
        'field' => 'delivery_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '下单时间',
        'field' => 'record_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '付款时间',
        'field' => 'pay_time',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '支付方式',
        'field' => 'pay_name', // pay_code
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '支付宝交易号',
        'field' => 'alipay_no', // pay_code
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '配送方式',
        'field' => 'express_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '物流单号',
        'field' => 'express_no',
        'width' => '150',
        'align' => '',
        'editor' => "{xtype : 'text'}",
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '交易号',
        'field' => 'deal_code_list',
        'width' => '150',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '会员昵称',
        'field' => 'buyer_name', // customer_code
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '收货人',
        'field' => 'receiver_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '手机',
        'field' => 'receiver_mobile',
        'width' => '120',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '收货地址',
        'field' => 'receiver_address',
        'width' => '160',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '收货邮编',
        'field' => 'receiver_zip_code',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '销售平台',
        'field' => 'sale_channel_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '店铺',
        'field' => 'shop_name',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '邮费',
        'field' => 'express_money',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '数量',
        'field' => 'goods_count',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '商品应收总额',
        'field' => 'avg_money_all',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '已付金额',
        'field' => 'paid_money',
        'width' => '100',
        'align' => ''
    ),
    array(
        'type' => 'text',
        'show' => 1,
        'title' => '订单理论重量(Kg)',
        'field' => 'goods_weigh',
        'width' => '120',
        'align' => ''
    ),
);
if(!empty($response['proprety'])) {
        foreach($response['proprety'] as $val) {
            $list[] = array('title' => $val['property_val_title'],
                'show' => 1,
                'type' => 'text',
                'width' => '80',
                'field' => $val['property_val']);
        }
    } 
    //2017-12-7  task#1909 新增自定义列
    render_control('DataTable', 'sell_record_table', array(
    'conf' => array(
        'list' => $list
    ),
    'dataset' => 'rpt/SellRecordReportModel::shipped_sell_record',
    'idField' => 'sell_record_code',
    'customFieldTable' => 'rpt/sell_record_table',
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'export'=> array('id'=>'exprot_shipped_sell_record','conf'=>'sell_record_shipped_sell_record','name'=>'订单发货数据分析','export_type'=>'file'),
));

?>
<script type = "text/javascript">
    function showDetail(_index, row) {
        view(row.sell_record_code);
    }
    function view(sell_record_code) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>' + sell_record_code, '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code, '订单详情');
    }
</script>