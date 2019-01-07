<?php
render_control('PageHead', 'head1', array('title' => '零售结算单详情',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
));
?>
<style>
    .panel-body{
        padding:0px;
    }
</style>
<div class="panel_wrap">
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">发货单</h3>
        </div>
        <div class="panel-body" id="panel_record">
            <?php
            render_control('DataTable', 'table1', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '订单号',
                            'field' => 'sell_record_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '交易号',
                            'field' => 'deal_code',
                            'width' => '140',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '支付宝交易号',
                            'field' => 'alipay_no',
                            'width' => '140',
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
                            'title' => '平台',
                            'field' => 'sale_channel_name',
                            'width' => '80',
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
                            'title' => '商品均摊总金额',
                            'field' => 'je',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '积分抵用金额',
                            'field' => 'point_fee',
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
                    )
                ),
                'dataset' => 'oms/SellSettlementModel::get_list_by_deal_code',
                'queryBy' => 'searchForm',
                'params' => array(
                    'filter'=>array(
                        'deal_code' => $response['deal_code'],
                        'order_attr' => '1'
                    ),
                ),
                'idField' => 'id',
                'CellEditing' => true,
                'CascadeTable' => array(
                    'list' => array(
                        array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
//                         array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_code_name'),
//                         array('title' => '系统规格', 'type' => 'text', 'width' => '200', 'field' => 'spec', 'format_js' => array('type' => 'html', 'value' => $response['goods_spec1_rename'] . ':{spec1_code},' . $response['goods_spec2_rename'] . ':{spec2_code}',)),
                        array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
                        array('title' => '系统规格', 'type' => 'text', 'width' => '200', 'field' => 'spec',),
                        array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
                        array('title' => '商品数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
                        array('title' => '收款金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
                    ),
                    'page_size' => 10,
                    'url' => get_app_url('acc/retail_settlement_detail/get_detail_list_by_deal_code&app_fmt=json'),
                    'params' => 'deal_code,order_attr,settle_type,sell_record_code'
                ),
                'events' => array(
                    'rowdblclick' => 'showDetail',
                ),
            ));
            ?>
        </div>
    </div>
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">退货单</h3>
        </div>
        <div class="panel-body" id="panel_return">
            <?php
            render_control('DataTable', 'table2', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '退单号',
                            'field' => 'sell_record_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '交易号',
                            'field' => 'deal_code',
                            'width' => '140',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '支付宝交易号',
                            'field' => 'alipay_no',
                            'width' => '140',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺',
                            'field' => 'shop_code',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '平台',
                            'field' => 'sale_channel_code',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品退货数量',
                            'field' => 'num',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '商品退货金额',
                            'field' => 'je',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '额外赔付金额',
                            'field' => 'compensate_money',
                            'width' => '100',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'oms/SellSettlementModel::get_list_by_deal_code',
                'queryBy' => 'searchForm',
                'idField' => 'id',
                'CellEditing' => true,
                'params' => array(
                    'filter'=>array(
                        'deal_code' => $response['deal_code'],
                        'order_attr' => '2'
                    ),
                ),
                'CascadeTable' => array(
                    'list' => array(
                        array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
                        array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_code_name'),
                        array('title' => '系统规格', 'type' => 'text', 'width' => '200', 'field' => 'spec',),
                        array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
                        array('title' => '商品数量', 'type' => 'text', 'width' => '100', 'field' => 'num'),
                        array('title' => '收款金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
                    ),
                    'page_size' => 10,
                    'url' => get_app_url('acc/retail_settlement_detail/get_detail_list_by_deal_code&app_fmt=json'),
                    'params' => 'deal_code,order_attr,settle_type,sell_record_code'
                ),
                'events' => array(
                    'rowdblclick' => 'showDetail',
                ),
            ));
            ?>
        </div>
    </div>
    
    <div class="panel">
        <div class="panel-header clearfix">
            <h3 class="pull-left">收支流水</h3>
        </div>
        <div class="panel-body" id="panel_alipay">
            <?php
            render_control('DataTable', 'table3', array(
                'conf' => array(
                    'list' => array(
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '交易号',
                            'field' => 'deal_code',
                            'width' => '120',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '支付宝交易号',
                            'field' => 'alipay_order_no',
                            'width' => '140',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '店铺',
                            'field' => 'shop_name',
                            'width' => '140',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '业务类型',
                            'field' => 'api_type',
                            'width' => '150',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '收入金额',
                            'field' => 'in_amount',
                            'width' => '80',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '支出金额',
                            'field' => 'out_amount',
                            'width' => '100',
                            'align' => ''
                        ),
                        array(
                            'type' => 'text',
                            'show' => 1,
                            'title' => '创建时间',
                            'field' => 'first_insert_time',
                            'width' => '100',
                            'align' => ''
                        ),
                    )
                ),
                'dataset' => 'acc/ApiTaobaoAlipayModel::get_list_by_deal_code',
                'queryBy' => 'searchForm',
                'params' => array(
                    'filter'=>array(
                        'deal_code' => $response['deal_code'],
                    ),
                ),
                'idField' => 'aid',
                'CellEditing' => true,
            ));
            ?>
        </div>
    </div>
    
</div>

