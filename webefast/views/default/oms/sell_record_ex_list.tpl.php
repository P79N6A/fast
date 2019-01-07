<style>
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #money_start{width: 65px;}
    #money_end{width: 65px;}
    .bui-menu-item {
        padding: 70px 30px 0px 40px;
    }
    .btn_more_record{
        position: fixed;
        z-index:1001;
    }
    .like_link{
        text-decoration:underline;
        color:#428bca; 
        cursor:pointer;
    }
    #sort {
        width: 250px;
    }
    #tool2{ height:30px;}
    #tool2 input{ vertical-align:middle;}
    #tool2 label{ vertical-align:middle; margin-right:5px;}
</style>
<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php echo load_js('Launch_wangwang.js', true)?>
<?php
$links = array(
    array('url' => 'oms/sell_record/add', 'title' => '新增订单', 'is_pop' => false, 'pop_size' => '500,400'),
);
if ($response['priv']['inspect_priv'] == 1) {
    $links[] = array('url' => 'oms/sell_record/inspect_record', 'title' => '快速审单', 'is_pop' => false, 'pop_size' => '500,400');
}
render_control('PageHead', 'head1', array('title' => '订单列表',
    'links' => $links,
    'ref_table' => 'table'
));
?>
<?php
$order_tag = ds_get_select('order_label');
array_push($order_tag, array('order_label_code' => 'no_label_code', 'order_label_name' => '无标签'));
$keyword_type = array();
$keyword_type['deal_code_list'] = '交易号';
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['buyer_name'] = '买家昵称';
$keyword_type['receiver_mobile'] = '手机号码';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['is_lock_person'] = '锁定人';
$keyword_type['fenxiao_name'] = '分销商';
$keyword_type = array_from_dict($keyword_type);
$is_buyer_remark = array();
$is_buyer_remark['all'] = '买家留言';
$is_buyer_remark[1] = '有买家留言';
$is_buyer_remark[0] = '无买家留言';
$is_buyer_remark = array_from_dict($is_buyer_remark);
$is_seller_remark = array();
$is_seller_remark['all'] = '商家留言';
$is_seller_remark[1] = '有商家留言';
$is_seller_remark[0] = '无商家留言';
$is_seller_remark = array_from_dict($is_seller_remark);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
if ($response['priv']['export_ext_list'] == 1) {
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
            'help' => '支持多交易号、订单号查询，用逗号隔开(字符长度限制20000)，模糊查询需要开启参数；
以下字段支持模糊查询：商品条形码、商品编码',
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
        array(
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
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
        array(
            'label' => '卖家旗帜',
            'type' => 'select_multi',
            'id' => 'seller_flag',
            'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
        ),
        array(
            'label' => '省份',
            'type' => 'select',
            'id' => 'province',
            'data' => array(),
        ),
        array(
            'label' => '城市',
            'type' => 'select',
            'id' => 'city',
            'data' => array(),
        ),
        array(
            'label' => '地区',
            'type' => 'select',
            'id' => 'district',
            'data' => array(),
        ),
        array(
            'label' => '详细地址',
            'type' => 'input',
            'id' => 'receiver_addr',
            'title' => '支持以逗号分隔的模糊查询'
        ),
        array(
            'label' => '国家',
            'type' => 'select',
            'id' => 'country',
            'data' => ds_get_select('country', 2),
        ),
        array(
            'label' => '省份（多选）',
            'type' => 'select_multi',
            'id' => 'province_multi',
            'data' => array_map(function ($item) {
                        $return[] = $item['id'];
                        $return[] = $item['name'];
                        return $return;
                    }, load_model('base/TaobaoAreaModel')->get_area('1')),
        ),
        array(
            'label' => '支付方式',
            'type' => 'select_multi',
            'id' => 'pay_type',
            'data' => ds_get_select('pay_type'),
        ),
        array(
            'label' => array('id' => 'is_buyer_remark', 'type' => 'select', 'data' => $is_buyer_remark),
            'type' => 'input',
            'id' => 'buyer_remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => array('id' => 'is_seller_remark', 'type' => 'select', 'data' => $is_seller_remark),
            'type' => 'input',
            'id' => 'seller_remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => '仓库留言',
            'type' => 'input',
            'id' => 'store_remark',
            'title' => '支持模糊查询'
        ),
        array(
            'label' => '有无发票',
            'type' => 'select',
            'id' => 'is_invoice',
            'data' => ds_get_select_by_field('havestatus', 2),
        ),
        array(
            'label' => '下单时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '支付时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
                array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
            )
        ),
        array(
            'label' => '计划发货时间',
            'type' => 'group',
            'field' => 'daterange4',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'plan_send_time_start'),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'plan_send_time_end', 'remark' => ''),
            ),
        ),
        array(
            'label' => '订单价格',
            'type' => 'group',
            'field' => 'money',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'money_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'money_end', 'class' => 'input-small', 'remark' => '<input type="checkbox" id="contain_express_money">含运费'),
            ),
        ),
        array(
            'label' => '订单理论重量',
            'type' => 'group',
            'field' => 'weight',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'weight_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'weight_end', 'class' => 'input-small', 'remark' => ''),
            ),
        ),
        array(
            'label' => '商品数量',
            'type' => 'group',
            'field' => 'num',
            'child' => array(
                array('title' => 'start', 'type' => 'input', 'field' => 'num_start', 'class' => 'input-small'),
                array('pre_title' => '~', 'type' => 'input', 'field' => 'num_end', 'class' => 'input-small', 'remark' => ''),
            ),
        ),
        array(
            'label' => '订单性质',
            'type' => 'select_multi',
            'id' => 'sell_record_attr',
            'data' => load_model('FormSelectSourceModel')->sell_record_attr(),
        ),
        array(
            'label' => '订单标签',
            'type' => 'select_multi',
            'id' => 'order_tag',
            'data' => $order_tag,
        ),
        array(
            'label' => '编码/条形码',
            'type' => 'input',
            'id' => 'exact_code',
            'title' => '仅支持精确查询',
            'help' => '支持多商品编码、多商品条形码查询，用逗号隔开'
        ),
        array(
            'label' => '过滤条形码',
            'type' => 'input',
            'id' => 'no_barcode',
            'title' => '仅支持精确查询',
        ),
        array(
            'label' => '有无赠品',
            'type' => 'select',
            'id' => 'is_gift',
            'data' => array(array('' , '全部'), array('0' , '无'), array('1' , '有'))
        ),
        array(
            'label' => '是否预售单',
            'type' => 'select',
            'id' => 'sale_mode',
            'data' => array(array('' , '全部'), array('presale' , '是'), array('stock' , '否'))
        ),
    )
));
?>

<?php
$service_custom = load_model('common/ServiceModel')->check_is_auth_by_value('CDKZ');
$settlement_order = array();
if($service_custom == true) {
    $settlement_order = array('title' => '待结算', 'active' => false, 'id' => 'tabs_settlement');
}
render_control('TabPage', 'TabPage1', array(
    'tabs' => array(
        array('title' => '全部', 'active' => false, 'id' => 'tabs_all'),
        array('title' => '待付款', 'active' => false, 'id' => 'tabs_pay'),
        array('title' => '待确认', 'active' => true, 'id' => 'tabs_confirm'), // 默认选中active=true的页签
        array('title' => '待通知配货', 'active' => false, 'id' => 'tabs_notice_shipping'),
        array('title' => '待发货', 'active' => false, 'id' => 'tabs_send'),
        $settlement_order,
    ),
    'for' => 'TabPage1Contents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
));
?>
<ul id="tool2" class="toolbar" style="margin-top: 10px;">
    <?php $request['is_normal'] = isset($request['is_normal']) ? $request['is_normal'] : ''; ?>
    <li>
        <input type="radio" name="is_normal" <?php if ($request['is_normal'] == '') echo 'checked="checked"'; ?>    value=""/><label>全部</label>
        <input type="radio" name="is_normal"  <?php if ($request['is_normal'] == '1') echo 'checked="checked"'; ?>   value="1"/><label>正常</label>
        <input type="radio" name="is_normal"  <?php if ($request['is_normal'] == '2') echo 'checked="checked"'; ?>   value="2"/><label>非正常</label>
        <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="非正常订单包括：缺货,设问,挂起"/>&nbsp;
        <span>非正常的订单不允许批量确认</span>
    </li>
    <li style="float:right;margin-right: 30px;">
        <label>排序类型：</label>
        <select id="sort" name="sort" onchange="sort()">
            <option value="" >默认(付款时间升序+计划发货时间降序)</option>
            <option value="plan_send_time_asc">计划发货时间升序</option>
            <option value="plan_send_time_desc">计划发货时间降序</option>
            <option value="paid_money_asc">已付款金额升序</option>
            <option value="paid_money_desc">已付款金额降序</option>
            <option value="record_time_asc">下单时间升序</option>
            <option value="record_time_desc">下单时间降序</option>
            <option value="pay_time_asc">付款时间升序</option>
            <option value="pay_time_desc">付款时间降序</option>
        </select>
<!--        <button type="button" class="button button-small" id="sort_btn" onclick = "sort()">排序</button>-->
        <img src="assets/images/tip.png" alt="123" width="25" height="25" title ="排序所有页签"/>

    </li>
</ul>
<?php
$option_conf_list = array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单图标',
                'field' => 'status_text',
                'width' => '100',
                'align' => ''
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
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '150',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{deal_code_list}</a>',
                ),
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '下单时间',
                'field' => 'record_time',
                'width' => '100',
                'align' => '',
               // 'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '支付时间',
                'field' => 'pay_time',
                'width' => '100',
                'align' => '',
                //'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '计划发货时间',
                'field' => 'plan_send_time',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单状态',
                'field' => 'status',
                'width' => '155',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家昵称',
                'field' => 'buyer_name',
                'width' => '100',
                'align' => '',
                'sortable' => true,
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'set_wangwang_html')
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '100',
                'align' => '',
                 'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_name',
		),
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'receiver_mobile',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_tel',
		),
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '省',
                'field' => 'receiver_province_txt',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '市',
                'field' => 'receiver_city_txt',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '区',
                'field' => 'receiver_district_txt',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '200',
                'align' => '',
                 'format_js' => array(
                    'type' => 'function',
                    'value' => 'check_address',
		),
                
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库',
                'field' => 'store_name',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库留言',
                'field' => 'store_remark',
                'width' => '120',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '快递单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype : 'text'}",
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'express_money',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已付款',
                'field' => 'paid_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商家留言',
                'field' => 'seller_remark',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '买家留言',
                'field' => 'buyer_remark',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '数量',
                'field' => 'goods_num',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单备注',
                'field' => 'order_remark',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单标签',
                'field' => 'order_tag',
                'width' => '80',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单理论重量(kg)',
                'field' => 'goods_weigh',
                'width' => '120',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '应付款',
                'field' => 'payable_money',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
				'type' => 'text',
				'show' => 1,
				'title' => '是否开票',
				'field' => 'invoice_status',
				'width' => '80',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '发票类型',
				'field' => 'invoice_type',
				'width' => '80',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '发票抬头类型',
				'field' => 'is_company',
				'width' => '100',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '发票抬头',
				'field' => 'invoice_title',
				'width' => '80',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '发票内容',
				'field' => 'invoice_content',
				'width' => '80',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '企业税号',
				'field' => 'taxpayers_code',
				'width' => '100',
				'align' => '',
			),
                        array(
				'type' => 'text',
				'show' => 1,
				'title' => '分销结算运费',
				'field' => 'fx_express_money',
				'width' => '80',
				'align' => '',
			),
        );
if ($response['priv']['ex_update_detail']){
    $option_conf_list[] = array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '60',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '修改', 'act' => 'pop:oms/sell_record/ex_update_detail&sell_record_code={sell_record_code}', 'show_name' => '修改订单信息（{sell_record_code}）', 'pop_size' => '1200,403'),
                ),
            );
}
render_control('DataTable', 'table', array(
    'conf' => array('list' => $option_conf_list),
    'dataset' => 'oms/SellRecordModel::ex_list_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'customFieldTable' => 'oms/sell_record_combine_ex_list',
    'export' => array('id' => 'exprot_list', 'conf' => 'new_ex_record_list', 'name' => '订单列表', 'export_type' => 'file'),
    'CellEditing' => true,
    'CascadeTable' => array(
        'list' => array(
            array('title' => '商品图片', 'type' => 'text', 'width' => '100', 'field' => 'pic_path'),
            array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_name'),
            array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
            array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
            array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_name'),
            array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_name'),
            array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)',)),
            array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
            array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
            array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode', 'format_js' => array('type' => 'map', 'value' => array('stock' => '否', 'presale' => '是'))),
            array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map', 'value' => array('0' => '否', '1' => '是'))),
            array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'plan_send_time'),
        ),
        'page_size' => 10,
        'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
        'params' => 'sell_record_code',
        'ExpandCascadeDetail' => array(
            'detail_url' => get_app_url('oms/sell_record/get_ex_list_cascade_data'), //查询展开详情的方法
            'detail_param' => 'sell_record_code', //查询展开详情的使用的参数
        ),
    ),
    'ColumnResize' => true,
    'CheckSelection' => true,
    'init' => 'nodata',
    'init_note_nodata' => '请稍等，正在获取数据…',
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
    'cookie_page_size' => 'sell_record_exlist_page_size',
));
?>
<div id="TabPage1Contents">
    <!--全部-->
<div>
        <ul id="ToolBar1" class="toolbar frontool">
	 <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_intercept')) {?>
	<li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_intercept',obj_name:'订单',ids_params_name:'sell_record_code'}">批量订单拦截</button></li>
	<?php }?>
	<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_pending')) {?>
        <li class="li_btns"><button class="button button-primary btn_opt_pending ">批量挂起</button></li>
        <?php }?>
        <li class="li_btns"><button class="button button-primary btn_cancel">批量作废</button></li>
        <li class="li_btns"><button class="button button-primary btn_opt_label ">批量打标</button></li>
    <div class="front_close">&lt;</div>
</ul>
        
<script>
    
$(function(){
	function tools(){
            $(".frontool").css({left:'0px'});
            $(".front_close").click(function(){
                if($(this).html()=="&lt;"){
                    $(".frontool").animate({left:'-100%'},1000);
                    $(this).html(">");
	            $(this).addClass("close_02").animate({right:'-10px'},1000);
                }else{
                    $(".frontool").animate({left:'0px'},1);
                    $(this).html("<");
		    $(this).removeClass("close_02").animate({right:'0'},1000);
                }
            });
        }
        
	tools();
});

</script>

<script>   
    //查询添加逗号分隔
//    $('#keyword').attr('maxlength','20001');//输入框限制20000个字符长度
    $('#keyword').blur(function(){
        var str = $.trim($(this).val().replace((/[\r\n\"]/g),''));//去除换行双引号和前后空格
        new_str = str.replace((/\s+/g),',');
        $(this).val(new_str);
        var str_len = new_str.length;
        if ( str_len > 20000 ) {
            new_str = new_str.substring(0,20000);
            new_str = new_str.substring(0,new_str.lastIndexOf(','));
            $(this).val(new_str);
            var n = (new_str.split(',')).length;
            BUI.Message.Alert('文本框字符长度限制为20000，当前有效交易号/订单号个数为' + n);
        }
    }); 
//批量作废

$(".btn_cancel").click(function(){
    get_checked($(this), function(ids){
        var params = {"sell_record_id_list": ids};
        $.post("?app_act=oms/sell_record/cancel_all", params, function(data){
            if(data.status == '1'){
                //刷新数据
                BUI.Message.Alert(data.message, 'success');
                tableStore.load()
            } else {
                BUI.Message.Alert(data.message, 'error');
            }
        }, "json");

    })
})

//批量挂起

$(".btn_opt_pending").click(function(){
	get_checked($(this), function(ids) {
        new ESUI.PopWindow("?app_act=oms/sell_record/pending&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
            title: "批量挂起",
            width: 550,
            height:480,
            onBeforeClosed: function() {
            },
            onClosed: function() {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
});

//批量打标

$(".btn_opt_label").click(function(){
	get_checked($(this), function(ids) {
        new ESUI.PopWindow("?app_act=oms/sell_record/label&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
            title: "批量打标签",
            width: 500,
            height:300,
            onBeforeClosed: function() {
            },
            onClosed: function() {
                //刷新数据
                tableStore.load()
            }
        }).show()
    })
});

</script>
        
        
</div>
    <!--待付款-->
    <div>
        <ul  class="toolbar frontool"  id="ToolBar2">
            <li class="li_btns"><button class="button button-primary btn_opt_pay ">批量付款</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var default_opts = ['opt_pay'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar2", f);
                }
            });
        </script>
    </div>
    <!--待确认-->
    <div>
        <ul  class="toolbar frontool"  id="ToolBar3">
            <?php if ($response['priv']['pl_opt_confirm'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_confirm ">批量确认</button></li>
            <?php endif; ?>
            <?php if ($response['priv']['opt_alter_detail'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_alter_detail ">批量改商品</button></li>
            <?php endif; ?>
            <?php if ($response['priv']['opt_change_detail'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_change_detail ">批量替换商品</button></li>
            <?php endif; ?>
            <?php if ($response['priv']['opt_edit_store_code'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_store_code ">批量修改发货仓库</button></li>
            <?php endif; ?>
            <?php if ($response['priv']['opt_edit_express_code'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_edit_express_code ">批量修改配送方式</button></li>
            <?php endif; ?>
            <li class="li_btns"><button class="button button-primary btn_opt_split_order">批量拆单</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_label">批量打标</button></li>
            <?php if ($response['priv']['opt_cancel'] == 1) : ?>
                <li class="li_btns"><button class="button button-primary btn_opt_cancel">批量作废</button></li>
            <?php endif; ?>
            <li class="li_btns">
                <button class="button button-primary btn_opt_record_selected">更多批量操作<span class="x-caret x-caret-up"></span></button>
            </li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var custom_opts = $.parseJSON('[{"id":"opt_confirm","custom":"btn_init_opt_confirm"},{"id":"opt_alter_detail","custom":"btn_init_alter_detail"},{"id":"opt_change_detail","custom":"btn_init_change_detail"},{"id":"opt_edit_store_code","custom":"btn_init_edit_store_code"},{"id":"opt_edit_express_code","custom":"btn_init_edit_express_code"},{"id":"opt_split_order","custom":"btn_init_split_order"},{"id":"opt_label","custom":"btn_init_label"},{"id":"opt_cancel","custom":"btn_inti_cancel"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar3 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
    <!--待通知配货-->
    <div>
        <ul  class="toolbar frontool"  id="ToolBar4">
            <li class="li_btns"><button class="button button-primary btn_opt_notice_shipping ">批量通知配货</button></li>
            <li class="li_btns"><button class="button button-primary btn_opt_label">批量打标</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function () {
                var default_opts = ['opt_notice_shipping'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar4", f);
                }
                var custom_opts = $.parseJSON('[{"id":"opt_label","custom":"btn_init_label"}]');
                for (var j in custom_opts) {
                    var g = custom_opts[j];
                    $("#ToolBar4 .btn_" + g['id']).click(eval(g['custom']));
                }
            });
        </script>
    </div>
    <!--待发货-->
    <div></div>
    <!--待结算-->
    <div>
        <ul  class="toolbar frontool"  id="ToolBar6">
                <li class="li_btns"><button class="button button-primary btn_opt_settlement">批量结算</button></li>
            <li class="front_close">&lt;</li>
        </ul>
        <script>
            $(function() {
                var default_opts = ['opt_settlement'];
                for (var i in default_opts) {
                    var f = default_opts[i];
                    btn_init_opt("ToolBar6", f);
                }
            });
        </script>
    </div>
</div>

<div id="msg_id"></div>
<script type="text/javascript">
    var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(function () {
        tools();

        //TAB选项卡
        $("#TabPage1 a").click(function () {
            tableStore.load();
        });
        $("input[name='is_normal']").change(function () {
            tableStore.load();
        });
        tableStore.on('beforeload', function (e) {
            e.params.ex_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
            e.params.contain_express_money = $("#contain_express_money").attr('checked') == 'checked' ? '1' : '0';
            e.params.is_normal = $("input[name='is_normal']:checked").val();
            var sort_e = $("#sort  option:selected");
            //前端排序
            var sort_params={};
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val();
                switch (e.params.is_sort) {
                    case 'record_time_asc':
                        sort_params.field = 'record_time';
                        sort_params.direction = 'ASC';
                        break;
                    case 'record_time_desc':
                        sort_params.field = 'record_time';
                        sort_params.direction = 'DESC';
                        break;
                    case 'pay_time_asc':
                        sort_params.field = 'pay_time';
                        sort_params.direction = 'ASC';
                        break;
                    case 'pay_time_desc':
                        sort_params.field = 'pay_time';
                        sort_params.direction = 'DESC';
                        break;
                    case 'paid_money_asc':
                        sort_params.field = 'paid_money';
                        sort_params.direction = 'ASC';
                        break;
                    case 'paid_money_desc':
                        sort_params.field = 'paid_money';
                        sort_params.direction = 'DESC';
                        break;
                }
                tableStore.set('sortInfo',sort_params);
            }
            tableStore.set("params", e.params);
        });

        $('#country').change(function () {
            var parent_id = $(this).val();
            if (parent_id === '') {
                parent_id = 1;
            }
            areaChange(parent_id, 0, url);
        });
        $('#province').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 1, url);
        });
        $('#city').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 2, url);
        });
        $('#district').change(function () {
            var parent_id = $(this).val();
            areaChange(parent_id, 3, url);
        });
        $('#country').change();
        
        tableStore.load({}, function(){
            $(".nodata").text('');//清除加载提示
        });

        $(".btn_opt_record_selected").click(function () {
            $(".bui-pop-menu").css("top", "");
            $(".bui-pop-menu").css("bottom", "35px");
        });
    });

    function tools() {
        $(".frontool").css({left: '0px'});
        $(".front_close").click(function () {
            if ($(this).html() == "&lt;") {
                $(".frontool").animate({left: '-100%'}, 1000);
                $(this).html(">");
                $(this).addClass("close_02").animate({right: '-10px'}, 1000);
            } else {
                $(".frontool").animate({left: '0px'}, 1);
                $(this).html("<");
                $(this).removeClass("close_02").animate({right: '0'}, 1000);
            }
        });
    }

    function sort() {
        tableStore.load();
    }

    //读取已选中项
    function get_checked(obj, func, type) {
        var ids = new Array();
        var idss = new Array();
        var rows = tableGrid.getSelection();
        if (rows.length == 0) {
            BUI.Message.Alert("请选择订单", 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        ids.join(',');
        var opt_arr = ['批量修改仓库留言','批量修改配送方式','批量挂起','批量备注','批量替换商品','批量改商品','批量拆单','批量打标'];
        var opt_text = obj.text();
        if (opt_text == '批量修改发货仓库') {
            for (var j in rows) {
                var row = rows[j];
                idss.push(row.sell_record_id);
            }
            func.apply(null, [idss]);
        } else if ($.inArray(opt_text,opt_arr) != -1) {
            func.apply(null, [ids]);
        } else {
            BUI.Message.Show({
                title: '批量操作',
                msg: '是否执行订单' + opt_text + '?',
                icon: 'question',
                buttons: [
                    {
                        text: '是',
                        elCls: 'button button-primary',
                        handler: function () {
                            func.apply(null, [ids]);
                            this.close();
                        }
                    },
                    {
                        text: '否',
                        elCls: 'button',
                        handler: function () {
                            this.close();
                        }
                    }
                ]
            });
        }
    }

    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function () {
            get_checked($(this), function (ids) {
                if(id == 'opt_settlement'){
                    $.post('?app_act=fx/sell_record/have_out_goods',{ids:ids},function(data){
                        if(data.status == 2){
                            BUI.Message.Confirm(data.message,function(){
                                do_settlement(ids,id,1)
                            });
                        }else{
                            do_settlement(ids,id,0)
                        }
                    },'json')
                }else{
                    var params = {"sell_record_code_list": ids, "type": id, "batch": "批量操作"};
                    $.post("?app_act=oms/sell_record/opt_batch", params, function (data) {
                        if (data.status == 1) {
                            BUI.Message.Alert(data.message, 'info');
                            //刷新
                            tableStore.load();
                        } else {
                            BUI.Message.Alert(data.message, 'error');
                        }
                    }, "json");
                }
            });
        });
    }
    function do_settlement(ids,id,allow_out=0){
        task_do_settlement('app_act=oms/sell_record/opt&type=opt_settlement&batch=自动确认&action_name=批量结算&allow_out='+allow_out,'批量结算','订单', 'sell_record_code');
    }
    function task_do_settlement(act, task_name, obj_name, ids_params_name, submit_all_ids_flag, process_batch_task_ids, task_name_tips, btn_id, task){
        var ids = new Array();
        var sell_record_codes = new Array();//订单号
        var rows = tableGrid.getSelection();//读取选中列表
        if (rows.length == 0) {
            BUI.Message.Alert("请选择" + obj_name, 'error');
            return;
        }
        for (var i in rows) {
            var row = rows[i];
            ids.push(row.sell_record_code);
        }
        $("body").data("process_batch_task_ids", ids.join(','));
        $("#process_batch_task_tips").remove();
        show_process_batch_task_plan(task_name, '<div id="process_batch_task_tips" style="height:300px;overflow-y:scroll;"><div>处理中，请稍等......</div></div>');
        //console.log('==11');
        process_batch_task_act(act, ids_params_name, submit_all_ids_flag, btn_id, task, sell_record_codes);
    }

    function get_sell_record_code_list(ids) {
        var sell_record_code_list = '';
        for (var key in ids) {
            sell_record_code_list += ids[key] + ",";
        }
        return  sell_record_code_list.substring(0, sell_record_code_list.length - 1);
    }

    BUI.use('bui/menu', function (Menu) {
        var dropMenu1 = new Menu.PopMenu({
                trigger : '.btn_opt_record_selected',
                autoRender : true,
                width : 140,
                elCls : 'btn_more_record',
                children : [
                    {id: 'order_remark', content: "批量备注"},
                    <?php if ($response['priv']['opt_pending'] == 1) : ?>
                        {id: 'pending', content: "批量挂起"},
                    <?php endif; ?>
                    <?php if ($response['priv']['set_rush'] == 1) : ?>
                        {id: '_sys_batch_task', content: "批量加急"},
                    <?php endif; ?>
                    <?php if ($response['priv']['opt_unpay'] == 1) : ?>
                        {id: 'unpay', content: "批量取消付款"},
                    <?php endif; ?>
                    <?php if ($response['priv']['opt_edit_store_remark'] == 1) : ?>
                        {id: 'store_remark', content: "批量修改仓库留言"}
                    <?php endif; ?>
                ]
        });

        $(".bui-pop-menu").find("[data-id=_sys_batch_task]").attr('task_info', "{act:'app_act=oms/sell_record/set_rush',obj_name:'批量急单',ids_params_name:'sell_record_code'}");

        dropMenu1.on('itemclick', function () {
            if (dropMenu1.getSelectedValue() == "order_remark") {
                init_menu_opt('order_remark','btn_init_edit_order_remark');
            } else if (dropMenu1.getSelectedValue() == "pending") {
                init_menu_opt('pending','btn_init_pending');
            } else if (dropMenu1.getSelectedValue() == "_sys_batch_task") {
                $(".bui-pop-menu").find("[data-id=_sys_batch_task]").click(function () {
                    var task_info = eval('(' + $(this).attr('task_info') + ')');
                    var task_name = $(this).text();
                    process_batch_task(task_info['act'], task_name, task_info['obj_name'], task_info['ids_params_name'], task_info['submit_all_ids_flag']);
                });
            } else if (dropMenu1.getSelectedValue() == "store_remark") {
                init_menu_opt('store_remark','btn_init_edit_store_remark');
            } else if (dropMenu1.getSelectedValue() == "unpay") {
                init_menu_opt('unpay','btn_init_unpay');
            }
        });
    });
    
    //加载二级菜单操作
    function init_menu_opt(_id, _act){
        $(".bui-pop-menu").find("[data-id=" + _id + "]").click(eval(_act));
    }
    
    //批量确认
    function btn_init_opt_confirm() {
        get_checked($(this), function (ids) {
            var sell_record_code_list = get_sell_record_code_list(ids);
            $.post("?app_act=oms/sell_record/opt_confirm", {sell_record_code_list:sell_record_code_list}, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'info');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        });
    }
    
    //批量取消付款
    function btn_init_unpay(){
        get_checked($(this), function (ids) {
            var id = 'opt_unpay';
            var params = {"sell_record_code_list": ids, "type": id, "batch": "批量操作"};
            $.post("?app_act=oms/sell_record/opt_batch", params, function (data) {
                if (data.status == 1) {
                    BUI.Message.Alert(data.message, 'info');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        }, '批量取消付款');
    }
    
    //批量修改配送方式
    function btn_init_edit_express_code() {              
        get_checked($(this), function (ids) {
            $.ajax({type: 'POST', 
                    dataType: 'json',
                    url: '<?php echo get_app_url('oms/sell_record/fx_account'); ?>', 
                    data: {'sell_record_code': ids},
                    success: function(data) {
                        if (data.status == -1) {
                            BUI.Message.Confirm(data.message,function(){
                                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                                    title: "批量修改配送方式",
                                    width: 500,
                                    height: 250,
                                    onBeforeClosed: function () {
                                    },
                                    onClosed: function () {
                                        tableStore.load();
                                    }
                                }).show();
                            },'question');                     
                        }else{
                            new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_code&sell_record_code_list=" + ids.toString(), {
                                title: "批量修改配送方式",
                                width: 500,
                                height: 250,
                                onBeforeClosed: function () {
                                },
                                onClosed: function () {
                                    tableStore.load();
                                }
                            }).show();
                        }
                    }
            }); 
        });
    }

    //批量修改发货仓库
    function btn_init_edit_store_code() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_code&sell_record_id_list=" + ids.toString(), {
                title: "批量修改发货仓库",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    }

    //批量修改仓库留言
    function btn_init_edit_store_remark() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_store_remark&sell_record_code_list=" + ids.toString(), {
                title: "批量修改仓库留言",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        }, '批量修改仓库留言');
    }
    
    //批量挂起
    function btn_init_pending() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/pending&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
                title: "批量挂起",
                width: 550,
                height: 480,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        }, '批量挂起');
    }

    //批量备注
    function btn_init_edit_order_remark() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/edit_order_remark&sell_record_code_list=" + ids.toString(), {
                title: "批量备注",
                width: 500,
                height: 250,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        }, '批量备注');
    }
    
    //批量替换商品
    function btn_init_change_detail() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/change_detail&sell_record_code_list=" + ids.toString(), {
                title: "批量换商品",
                width: 750,
                height: 500,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    }
    //批量改商品
    function btn_init_alter_detail() {
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/alter_detail&sell_record_code_list=" + ids.toString(), {
                title: "批量改商品",
                width: 680,
                height: 500,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    }
    
    //自动匹配物流单号
    function btn_init_edit_express_no() {
        $(".btn_edit_express_no").click(function () {
            get_checked($(this), function (ids) {
                new ESUI.PopWindow("?app_act=oms/sell_record/edit_express_no&sell_record_id_list=" + ids.toString(), {
                    title: "自动匹配物流单号",
                    width: 800,
                    height: 600,
                    onBeforeClosed: function () {
                    },
                    onClosed: function () {
                        tableStore.load();
                    }
                }).show();
            });
        });
    }

    //打印发货单
    function btn_init_opt_print_send() {
        $(".btn_opt_print_send").click(function () {
            get_checked($(this), function (ids) {
                //TODO:打印
                var ids = ids.toString();
                var url = '?app_act=oms/sell_record/mark_sell_record_print';
                var params = {};
                params.record_ids = ids;
                $.post(url, params, function (data) {
                });
                var window_is_block = window.open('?app_act=sys/danju_print/do_print_record&app_page=null&print_data_type=order_sell_record&record_ids=' + ids);
                if (null == window_is_block) {
                    alert("您的浏览器阻止了打印发货单的新窗口,请在浏览器的阻止提示处选择允许弹出新窗口");
                }
            });
        });
    }

    //打印快递单
    function btn_init_opt_print_express() {
        $(".btn_opt_print_express").click(function () {
            get_checked($(this), function (ids) {
                //TODO:打印
                var ids = ids.toString();
                print_express.print_express(ids);
            });
        });
    }
    
    //批量拆单
    function btn_init_split_order(){
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/split_order_batch&sell_record_code_list=" + ids.toString(), {
                title: "批量拆单",
                width: 480,
                height: 350,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    }

    //批量打标
    function btn_init_label(){
        get_checked($(this), function (ids) {
            new ESUI.PopWindow("?app_act=oms/sell_record/label&batch=<?php echo urlencode("批量操作"); ?>&sell_record_code_list=" + ids.toString(), {
                title: "批量打标签",
                width: 500,
                height: 300,
                onBeforeClosed: function () {
                },
                onClosed: function () {
                    tableStore.load();
                }
            }).show();
        });
    }

    //批量作废
    function btn_inti_cancel() {
        get_checked($(this), function (ids) {
            var params = {"sell_record_id_list": ids};
            $.post("?app_act=oms/sell_record/cancel_all", params, function (data) {
                if (data.status == '1') {
                    BUI.Message.Alert(data.message, 'success');
                    tableStore.load();
                } else {
                    BUI.Message.Alert(data.message, 'error');
                }
            }, "json");
        });
    }

    function view(sell_record_code) {
        var url = '?app_act=oms/sell_record/view&sell_record_code=' + sell_record_code;
        openPage(window.btoa(url), url, '订单详情');
    }

    function showDetail(index, row) {
        openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code') ?>' + row.sell_record_id, '?app_act=oms/sell_record/view&ref=ex&sell_record_code=' + row.sell_record_code, '订单详情');
    }

    tableCellEditing.on('accept', function (record) {
        var params = {
            "sell_record_code": record.record.sell_record_code,
            "express_code": record.record.express_code,
            "express_no": record.record.express_no.trim()
        };
        var str = params.express_no;
        if (str != '') {
            var reg = new RegExp(/^[0-9A-Za-z]+$/);
            if (!reg.test(str)) {
                BUI.Message.Alert("快递单号必须为数字或者字母", 'error');
                return false;
            }
        }
        $.post("?app_act=oms/deliver_record/edit_express", params, function (data) {
            if (data.status < 0) {
                BUI.Message.Tip(data.message, 'error');
            } else if (data.status == 1) {
                BUI.Message.Tip(data.message, 'success');
            }
        }, "json");
    });

    function a_key_confirm() {
        $.get("?app_act=oms/sell_record/a_key_confirm&app_fmt=json", function (data) {
            if (data.status < 1) {
                BUI.Message.Alert(data.message, 'error');
            } else {
                get_log(data.data, 0);
            }
            tableStore.load();
        }, "json");
    }

    function get_log(task_id, log_file_offset) {
        var request_data = {
            'task_id': task_id,
            'log_file_offset': log_file_offset,
            'timestamp': new Date().getTime()
        };
        //才页面功能已经实现，也可以跟进自己页面进行自行增加
        var ajax_url = '?app_act=sys/sys_schedule/get_task_log&app_fmt=json';
        $.post(ajax_url, request_data, function (data) {
            var result = eval('(' + data + ')');
            if (result == '') {
                return;
            }
            // msg_id 为存储信息的页面DOM ID
            $('#msg_id').prepend(result.msg);
            if (result.code == 0) {
                //2秒获取1次信息
                setTimeout(function () {
                    get_log(result.task_id, result.log_file_offset);
                }, 2000);
            }
        });
    }

    
    function set_wangwang_html(value, row, index){
  
        if(row.sale_channel_code == 'taobao') {
            return template('<span class="like_link" onclick =\"show_info(this,\''+row.sell_record_code+'\',\'buyer_name\');\"> {buyer_name}</span><span class="wangwang"><span class="ww-light ww-small"><a href="javascript:launch_ww(\'{sell_record_code}\')" class="ww-inline ww-online" title="点此可以直接和买家交流。"><span>旺旺在线</span></a></span></span>', row);
        } else {
           return template('<span>{buyer_name}</span>', row);
        }
    }
    function check_tel(value, row, index){
    if(value.indexOf('***')>-1){
            return set_show_text(row,value,'receiver_mobile');
    }else{
        return value;
    }
}
function check_address(value, row, index){
    if(value.indexOf('***')>-1){
            return set_show_text(row,value,'receiver_address');
    }else{
        return value;
    }
}
function check_name(value, row, index){
    if(value.indexOf('***')>-1){
            return set_show_text(row,value,'receiver_name');
    }else{
        return value;
    }
}
function check_buyer_name(value, row, index){
    if(value.indexOf('***')>-1){
            return set_show_text(row,value,'buyer_name');
    }else{
        return value;
    }
}
function set_show_text(row,value,type){
    return '<span class="like_link" onclick =\"show_info(this,\''+row.sell_record_code+'\',\''+type+'\');\">'+value+'</span>';
}
function show_info(obj,sell_record_code,key){
       var url = "?app_act=oms/sell_record/get_record_key_data&app_fmt=json";
        $.post(url,{'sell_record_code':sell_record_code,key:key},function(ret){
            if(ret[key]==null){
                 BUI.Message.Tip('解密出现异常！', 'error');
                return ;
            }
            $(obj).html(ret[key]);
            $(obj).attr('onclick','');
            
            $(obj).removeClass('like_link');
       },'json');
}
  function launch_ww(record_code){
        var url = "?app_act=oms/sell_record/link_wangwang&record_code="+record_code;
        window.open(url);
    }
</script>
<?php echo load_js('task.js', true); ?>
<!-- 打印快递单公共文件 -->
<?php include_once (get_tpl_path('process_batch_task')); ?>


