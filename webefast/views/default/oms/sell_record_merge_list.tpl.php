<?php
render_control('PageHead', 'head1', array('title' => '合并订单列表',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '订单号',
            'type' => 'input',
            'id' => 'record_code'
        ),
        array(
            'label' => '交易号',
            'type' => 'input',
            'id' => 'deal_code'
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' =>load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '收货人',
            'type' => 'input',
            'id' => 'receiver_name'
        ),
        array(
            'label' => '收货地址',
            'type' => 'input',
            'id' => 'receiver_address'
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_code',
            'data' => ds_get_select('pay_type'),
        ),
    )
));
?>
<ul class="toolbar" style="margin-top: 10px;margin-left:20px">
    <li><button class="button button-primary">批量合并订单</button></li>
    <li><button class="button button-primary">一键合并订单</button></li>
</ul>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'record_code',
                'width' => '200',
                'align' => '',
                'format_js' => array(
                    'type' => 'html', 
                    'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}">{sell_record_code}</a>',
                )
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_code',
                'width' => '150',
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
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '200',
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
                'title' => '已付金额',
                'field' => 'paid_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellRecordModel::get_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'CheckSelection' => true,
    'params' => array(
        'filter' => array("merge_status"=>'1'),
    ),
));
?>

