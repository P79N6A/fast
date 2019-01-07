<?php
render_control('PageHead', 'head1', array('title' => '历史发货订单查询',
    'links' => array(
    //array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table',
));
?>
<?php
$keyword_type = array();
$keyword_type['deal_code'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_mobile'] = '手机号码';

$keyword_type['express_no'] = '发货运单号';

$keyword_type = array_from_dict($keyword_type);

render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit',
        ),
    ),
    'show_row' => 1,
    'fields' => array(
        array(
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '精确查询，不支持模糊查询',
        ),
    ),
));
?>



<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_code',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '平台',
                'field' => 'sale_channel_name',
                'width' => '60',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '180',
//				'format_js' => array(
//					'type' => 'html',
//					'value' => '<a href="javascript:view({sell_record_code})">{deal_code_list}</a>',
//				),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '130',
                'align' => '',
//				'format_js' => array(
//					'type' => 'html',
////                    'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}" >{sell_record_code}</a>',
//					'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
//				),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'status',
                'width' => '155',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'function',
//                    'value' => 'check_buyer_name',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '70',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'function',
//                    'value' => 'check_name',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'receiver_mobile',
                'width' => '120',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'function',
//                    'value' => 'check_tel',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '250',
                'align' => '',
//                'format_js' => array(
//                    'type' => 'function',
//                    'value' => 'check_address',
//                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_code',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '120',
                'align' => '',
                'editor' => "{xtype : 'text'}",
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递费',
                'field' => 'express_money',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付款',
                'field' => 'paid_money',
                'width' => '70',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '付款时间',
                'field' => 'pay_time',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '确认人',
                'field' => 'confirm_person',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'goods_num',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单备注',
                'field' => 'order_remark',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单标签',
                'field' => 'order_tag',
                'width' => '80',
                'align' => '',
            ),
        ),
    ),
    'dataset' => 'sys/CarrySeachModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_code',
    'params' => array('filter' => array('record_type' => 'oms_sell_record')),
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)')),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode', 'format_js' => array('type' => 'map', 'value' => array('stock' => '否', 'presale' => '是'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'shipping_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('sys/carry_data/get_record_detail&app_fmt=json'),
        'params' => 'sell_record_code',
    ),
    'customFieldTable' => 'carry_sell_record_list/table',
    'init' => 'nodata',
));
?>


<script>

    function check_address(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'receiver_address');
        } else {
            return value;
        }
    }
    function check_name(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'receiver_name');
        } else {
            return value;
        }
    }
    function check_tel(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'receiver_mobile');
        } else {
            return value;
        }
    }
    function check_buyer_name(value, row, index) {
        if (value.indexOf('***') > -1) {
            return set_show_text(row, value, 'buyer_name');
        } else {
            return value;
        }
    }
    function set_show_text(row, value, type) {
        return '<span class="like_link" onclick =\"show_info(this,\'' + row.sell_record_code + '\',\'' + type + '\');\">' + value + '</span>';
    }
    function show_info(obj, sell_record_code, key) {
        var url = "?app_act=sys/carry_data/get_record_key_data&app_fmt=json";
        $.post(url, {'sell_record_code': sell_record_code, key: key}, function (ret) {
            if (ret[key] == null) {
                BUI.Message.Tip('解密出现异常！', 'error');
                return;
            }
            $(obj).html(ret[key]);
            $(obj).attr('onclick', '');

            $(obj).removeClass('like_link');
        }, 'json');
    }

</script>