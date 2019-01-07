<style>
   .bui-grid .grid_header_fix {position:static;}
</style>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '名称/代码',
            'type' => 'input',
            'id' => 'code_name'
        ),
    )
));
?>

<?php
$expressList = oms_opts2_by_tb('base_express', 'express_code', 'express_name', array('status' => 1), 2);
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '80',
                'align' => '',
                'buttons' => array(
                    array('id' => 'confirm', 'title' => '确定', 'callback' => 'confirm'),
                ),
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
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'html',
//                    'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
//                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺名称',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
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
                'title' => '手机（电话）',
                'field' => 'receiver_mobile',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '200',
                'align' => ''
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code',
                'format_js'=> array('type'=>'map', 'value'=>$expressList),
                'width' => '80',
                'align' => '',
//                'editor'=>(1==$response['edit_express_status'] or 1==$response['edit_express_status_new'])?"{xtype : 'select', items: ".json_encode($expressList)."}":""
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流单号',
                'field' => 'express_no',
                'width' => '120',
                'align' => '',
//                'editor' =>(1==$response['edit_express_status'] or 1==$response['edit_express_status_new'])?"{xtype : 'text'}":"",
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'order_id',
    'params' => array('filter' => array('shipping_status'=>'4')),
        //'RowNumber'=>true,
        //'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function confirm(_index, row) {
        parent.add_c(row);
        ui_closePopWindow("<?php echo $request['ES_frmId'] ?>");
    }
</script>