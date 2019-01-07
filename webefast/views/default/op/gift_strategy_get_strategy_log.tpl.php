<style type="text/css">
    #keyword_type{
        width: 90px!important;
    }
</style>
<?php
render_control('PageHead', 'head1', array('title' => '赠品策略匹配结果日志查询', 'ref_table' => 'table'));
?>
<?php
//交易类型
$keyword_type = array(
    'deal_code'=>'平台交易号',
    'sell_record_code'=>'订单号',
);
$keyword_type = array_from_dict($keyword_type);
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        )
    ),
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        )
    ),
));
?>
<input type="hidden" name="search_id" value="">
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台交易号',
                'field' => 'deal_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品类别数',
                'field' => 'sku_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品数量',
                'field' => 'goods_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单应付款',
                'field' => 'payable_money',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'express_money',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '添加赠品是否成功',
                'field' => 'is_success',
                'width' => '120',
                'align' => '',
                'format_js' => array('type' => 'map', 'value' => array('0' => '失败', '1' => '成功'))
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '使用的赠品规则/失败的原因',
                'field' => 'strategy_content',
                'width' => '300',
                'align' => ''
            ),
        ),
    ),
    'dataset' => 'op/StrategyLogModel::get_by_record_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'api_order_list', 'name' => '平台订单', 'export_type' => 'file'),
    'idField' => 'tid',
    'ColumnResize' => true,
    'CheckSelection' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '200', 'field' => 'goods_name'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '150', 'field' => 'barcode'),
            array('title' => '数量', 'type' => 'text', 'width' => '80', 'field' => 'num'),
            array('title' => '金额', 'type' => 'text', 'width' => '80', 'field' => 'avg_money'),
            array('title' => '赠品', 'type' => 'text', 'width' => '80', 'field' => 'is_gift' ,'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'),//查询展开详情的方法
            'detail_param' => 'tid',//查询展开详情的使用的参数
        ),
    ),
    'params'=>array('filter'=>array('search_type'=>'test_gift_stategy')),
    'init' => 'nodata',
    'events' => array(
        'rowdblclick' => 'close_window',
    )
));
?>
