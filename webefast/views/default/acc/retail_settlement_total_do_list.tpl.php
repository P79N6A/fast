<?php
render_control('PageHead', 'head1', array('title' => '网络交易综合查询',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
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
            'label' => '交易号',
            'type' => 'input',
            'id' => 'deal_code'
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            'data' => load_model('base/SaleChannelModel')->get_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '支付宝流水号',
            'type' => 'input',
            'id' => 'alipay_no'
        ),
        array(
            'label' => '交易有无收入',
            'type' => 'select',
            'id' => 'is_ali_in_amount',
            'data' => ds_get_select_by_field("havestatus")
        ),
        array(
            'label' => '更新时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'lastchanged_min',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'lastchanged_max', 'remark' => ''),
            )
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'record_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '付款时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'pay_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '总应收款',
            'type' => 'group',
            'field' => 'numrange1',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'total_fee_min', 'class' => 'input-small',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'total_fee_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
        array(
            'label' => '实际收入',
            'type' => 'group',
            'field' => 'numrange2',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'ali_in_amount_min', 'class' => 'input-small',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'ali_in_amount_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
        array(
            'label' => '实际支出',
            'type' => 'group',
            'field' => 'numrange3',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'ali_out_amount_min', 'class' => 'input-small',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'ali_out_amount_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
        array(
            'label' => '商品均摊总金额',
            'type' => 'group',
            'field' => 'numrange4',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'sell_record_avg_money_min', 'class' => 'input-small',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'sell_record_avg_money_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
        array(
            'label' => '商品退货金额',
            'type' => 'group',
            'field' => 'numrange5',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'sell_return_avg_money_min', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'sell_return_avg_money_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
        array(
            'label' => '补差金额',
            'type' => 'group',
            'field' => 'numrange6',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'compensate_money_min', 'class' => 'input-small',),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'compensate_money_max', 'class' => 'input-small', 'remark' => ''),
            )
        ),
    )
));
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '60',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '查看',
                        'callback' => 'do_view',
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付宝交易号',
                'field' => 'alipay_no',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '应收款<img src="assets/images/tip.png" title="应收款=商品发货金额+运费+调整金额-商品退货金额-补差金额" />',
                'field' => 'total_fee',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际收入',
                'field' => 'ali_in_amount',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '实际支出',
                'field' => 'ali_out_amount',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '有无换货',
                'field' => 'is_change',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品发货数量',
                'field' => 'num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品发货金额',
                'field' => 'sell_record_avg_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'express_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易佣金',
                'field' => 'commission_fee',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '积分抵用金额',
                'field' => 'real_point_fee',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品退货数量',
                'field' => 'return_num',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品退货金额',
                'field' => 'sell_return_avg_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '补差金额',
                'field' => 'compensate_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '调整金额',
                'field' => 'adjust_money',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '更新时间',
                'field' => 'lastchanged',
                'width' => '150',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'oms/SellSettlementModel::get_total_list_by_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'retail_settlement_total', 'name' => '零售结算汇总', 'export_type' => 'file'),
    'idField' => 'id',
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'customFieldTable' => 'retail_settlement_total_do_list/table',
));
?>
<script>
    function do_view(_index, row) {
        detail(_index, row);
    }
    function detail(_index, row) {
        openPage("<?php urldecode("?app_act=acc/retail_settlement_total/detail") ?>", "?app_act=acc/retail_settlement_total/detail&deal_code=" + row.deal_code, "零售结算单详情");
    }
</script>

