<?php echo load_js('comm_util.js') ?>
<style>
    #money_start{width:50px;}
    #money_end{width:50px;}
    #pay_time_start{width:100px;}
    #pay_time_end{width:100px;}
    #record_time_start{width:100px;}
    #record_time_end{width:100px;}
    #check_time_start,
    #check_time_end,
    #is_notice_time_start,
    #is_notice_time_end,
    #delivery_time_start,
    #delivery_time_end,
    #cancel_time_start,
    #cancel_time_end{
        width:100px;
    }
    
</style>
<?php render_control('PageHead', 'head1',
	array('title' => '订单查询',
		'links' => array(
			//array('url'=>'oms/sell_record/add', 'title'=>'新增订单', 'is_pop'=>false, 'pop_size'=>'500,400'),
		),
		'ref_table' => 'table',
	));?>
<?php
render_control('SearchSelectButton', 'select_button', array(
	'fields' => array(
		array('id' => 'pay_status', 'title' => '付款状态', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '未付款', 'id' => '0'),
			array('content' => '已付款', 'id' => '2'),
		)),
		array('id' => 'order_status', 'title' => '确认状态', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '未确认', 'id' => '0'),
			array('content' => '已确认', 'id' => '1'),
		)),
		array('id' => 'notice_flag', 'title' => '通知配货状态', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '未通知', 'id' => '0'),
			array('content' => '已通知', 'id' => '1'),
		)),
		array('id' => 'shipping_flag', 'title' => '发货状态', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '未发货', 'id' => '0'),
			array('content' => '已发货', 'id' => '1'),
		)),
		array('id' => 'invoice_status', 'title' => '订单开票', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '不需要', 'id' => '0'),
			array('content' => '需要', 'id' => '1'),
		)),
		array('id' => 'cancel_flag', 'title' => '作废状态', 'children' => array(
			array('content' => '全部', 'id' => 'all', 'selected' => true),
			array('content' => '未作废', 'id' => '0'),
			array('content' => '已作废', 'id' => '1'),
		)),
	),
	'for' => 'searchForm',
	'style' => 'width:192px;',
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
$keyword_type['barcode'] = '商品条形码';
$keyword_type['combo_barcode'] = '套餐条形码';
$keyword_type['is_lock_person'] = '锁定人';
$keyword_type['confirm_person'] = '确认人';
$keyword_type['notice_person'] = '通知配货人';
$keyword_type['express_no'] = '快递单号';
$keyword_type['delivery_person'] = '验货员';
if ($response['unique_status'] == 1) {
	$keyword_type['unique_code'] = '唯一码';
}
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
		'type' => 'submit',
	),
);
if (load_model('sys/PrivilegeModel')->check_priv('oms/sell_record/export_list')) {
	$buttons[] = array(
			'label' => '导出',
			'id' => 'exprot_list',
	);
	$buttons[] = array(
		'label' => '导出明细',
		'id' => 'exprot_detail',
	);
}
$fields =  array(
		array(
			'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
			'type' => 'input',
			'title' => '',
			'data' => $keyword_type,
			'id' => 'keyword',
			'help' => '支持多交易号、订单号查询，用逗号隔开(字符长度限制20000);
交易号、订单号、手机号、买家昵称、快递单号查询模糊查询需要开启参数；
以下字段支持模糊查询：商品条形码、商品编码',
		),

		array(
			'label' => '配送方式',
			'type' => 'select_multi',
			'id' => 'express_code',
			'data' => ds_get_select('express'),
		),

		array(
			'label' => '卖家旗帜',
			'type' => 'select_multi',
			'id' => 'seller_flag',
			'data' => load_model('util/FormSelectSourceModel')->get_seller_flag(),
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

		array(
			'label' => '销售平台',
			'type' => 'select_multi',
			'id' => 'sale_channel_code',
			//'data' => load_model('base/SaleChannelModel')->get_select(),
            'data' => load_model('base/SaleChannelModel')->get_my_select(),
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
			'title' => '支持以逗号分隔的模糊查询',
		),
		array(
			'label' => '国家',
			'type' => 'select',
			'id' => 'country',
			'data' => ds_get_select('country', 2),
		),
		array(
			'label' => '支付方式',
			'type' => 'select_multi',
			'id' => 'pay_type',
			'data' => ds_get_select('pay_type'),
		),
        array(
            'label' => '有无赠品',
            'type' => 'select',
            'id' => 'is_gift',
            'data' => ds_get_select_by_field('havestatus', 1),
        ),
		array(
			'label' => '订单性质',
			'type' => 'select_multi',
			'id' => 'sell_record_attr',
			'data' => load_model('FormSelectSourceModel')->sell_record_attr(),
		),
		array(
			'label' => '发票抬头',
			'type' => 'input',
			'id' => 'invoice_title',
			'title' => '支持模糊查询',
		),
		array(
			'label' => '外部仓库订单',
			'type' => 'select',
			'id' => 'store_code_outside',
			'data' => ds_get_select_by_field('boolstatus', 2),
		),
		array(
			'label' => array('id' => 'is_buyer_remark', 'type' => 'select', 'data' => $is_buyer_remark),
			'type' => 'input',
			'id' => 'buyer_remark',
			'title' => '支持模糊查询',
		),
		array(
			'label' => array('id' => 'is_seller_remark', 'type' => 'select', 'data' => $is_seller_remark),
			'type' => 'input',
			'id' => 'seller_remark',
			'title' => '支持模糊查询',
		),
		array(
			'label' => '订单备注',
			'type' => 'input',
			'id' => 'order_remark',
			'title' => '支持模糊查询',
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
			'label' => '订单重量',
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
			'label' => '下单时间',
			'type' => 'group',
			'field' => 'daterange1',
			'child' => array(
				array('title' => 'start', 'type' => 'time', 'field' => 'record_time_start','value'=> $response['record_time_start']),
				array('pre_title' => '~', 'type' => 'time', 'field' => 'record_time_end', 'remark' => ''),
			),
		),
		array(
			'label' => '付款时间',
			'type' => 'group',
			'field' => 'daterange2',
			'child' => array(
				array('title' => 'start', 'type' => 'time', 'field' => 'pay_time_start'),
				array('pre_title' => '~', 'type' => 'time', 'field' => 'pay_time_end', 'remark' => ''),
			),
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
			'label' => '确认时间',
			'type' => 'group',
			'field' => 'daterange3',
			'child' => array(
				array('title' => 'start', 'type' => 'time', 'field' => 'check_time_start'),
				array('pre_title' => '~', 'type' => 'time', 'field' => 'check_time_end', 'remark' => ''),
			),
		),
		array(
			'label' => '通知配货时间',
			'type' => 'group',
			'field' => 'daterange4',
			'child' => array(
				array('title' => 'start', 'type' => 'time', 'field' => 'is_notice_time_start'),
				array('pre_title' => '~', 'type' => 'time', 'field' => 'is_notice_time_end', 'remark' => ''),
			),
		),
		array(
			'label' => '发货时间',
			'type' => 'group',
			'field' => 'daterange4',
			'child' => array(
				array('title' => 'start', 'type' => 'time', 'field' => 'delivery_time_start'),
				array('pre_title' => '~', 'type' => 'time', 'field' => 'delivery_time_end', 'remark' => ''),
			),
		),
    array(
        'label' => '作废时间',
        'type' => 'group',
        'field' => 'daterange4',
        'child' => array(
            array('title' => 'start', 'type' => 'time', 'field' => 'cancel_time_start'),
            array('pre_title' => '~', 'type' => 'time', 'field' => 'cancel_time_end', 'remark' => ''),
        ),
    ),
	);
 if ($response['auth_print_invoice']) {
        $fields[]=	array(
			'label' => '发票打印',
			'type' => 'select',
			'id' => 'is_print_invoice',
			'data' => ds_get_select_by_field('boolstatus'),
		);
 }
$fields[]=		array(
			'label' => '订单标签',
			'type' => 'select_multi',
			'id' => 'order_tag',
			'data' => $order_tag,
		);
   $fields[]=             array(
                        'label' => '编码/条形码',
                        'type' => 'input',
                        'id' => 'exact_code',
                        'title' => '仅支持精确查询',
                        'help' => '支持多商品编码、多商品条形码查询，用逗号隔开'
                );
   $fields[]=             array(
                        'label' => '过滤条形码',
                        'type' => 'input',
                        'id' => 'no_barcode',
                        'title' => '仅支持精确查询',
                );
   $fields[]=             array(
                        'label' => '发票号',
                        'type' => 'input',
                        'id' => 'invoice_number',
                        'title' => '仅支持精确查询',
                );

render_control('SearchForm', 'searchForm', array(
	'buttons' => $buttons,
	'show_row' => 2,
	'fields' =>$fields,
));
?>

 <ul id="ToolBar1" class="toolbar frontool">
	 <?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_intercept')) {?>
	<li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_intercept',obj_name:'订单',ids_params_name:'sell_record_code'}">批量订单拦截</button></li>
	<?php }
?>
	 <?php #if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_lock')) { ?>
	<!-- <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_lock',obj_name:'订单',ids_params_name:'sell_record_code'}">批量锁定</button></li> -->
	 <?php #} ?>
	 <?php #if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_unlock')) { ?>
	<!-- <li class="li_btns"><button class="button button-primary _sys_batch_task_btn" task_info="{act:'app_act=oms/sell_record/opt&type=opt_unlock',obj_name:'订单',ids_params_name:'sell_record_code'}">批量解锁</button></li> -->
	<?php #} ?>
	<?php if (load_model('sys/PrivilegeModel')->check_priv('oms/order_opt/opt_pending')) {?>
        <li class="li_btns"><button class="button button-primary btn_opt_pending ">批量挂起</button></li>
    <?php }
?>
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
    $(function(){
        var default_opts = ['opt_lock','opt_unlock','opt_intercept'];
                for(var i in default_opts){
	    var f = default_opts[i];
	    btn_init_opt("ToolBar1",f);
	}
                var custom_opts = $.parseJSON('');
        for(var j in custom_opts){
            var g = custom_opts[j];
            $("#ToolBar1 .btn_"+g['id']).click(eval(g['custom']));
        }
    });
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
</script>
<ul id="tool2" class="toolbar" style="margin-top: 10px;">
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
				'width' => '80',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '店铺',
				'field' => 'shop_name',
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
				'format_js' => array(
					'type' => 'html',
					'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{deal_code_list}</a>',
				),
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
				'format_js' => array(
					'type' => 'html',
//                    'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}" >{sell_record_code}</a>',
					'value' => '<a href="javascript:view(\\\'{sell_record_code}\\\')">{sell_record_code}</a>',
				),
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
                                'format_js' => array(
                                        'type' => 'function',
                                        'value' => 'set_wangwang_html')
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '收货人',
				'field' => 'receiver_name',
				'width' => '70',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '手机',
				'field' => 'receiver_mobile',
				'width' => '120',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '收货地址',
				'field' => 'receiver_address',
				'width' => '250',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '仓库',
				'field' => 'store_name',
				'width' => '100',
				'align' => '',
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '配送方式',
				'field' => 'express_name',
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
				'title' => '运费',
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
                               // 'sortable' => true,
			),
			array(
				'type' => 'text',
				'show' => 1,
				'title' => '付款时间',
				'field' => 'pay_time',
				'width' => '150',
				'align' => '',
                               // 'sortable' => true,
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
				'title' => '发票号',
				'field' => 'invoice_number',
				'width' => '100',
				'align' => '',
			),
//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '付款时间',
			//            		'field' => 'is_pay_time',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '计划发货时间',
			//            		'field' => 'is_plan_send_time',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '买家留言',
			//            		'field' => 'buyer_remark',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '商家留言',
			//            		'field' => 'seller_remark',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '金额',
			//            		'field' => 'order_money',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '收货人',
			//            		'field' => 'receiver_name',
			//            		'width' => '100',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '收货地址',
			//            		'field' => 'receiver_address',
			//            		'width' => '300',
			//            		'align' => ''
			//            ),
			//            array (
			//            		'type' => 'text',
			//            		'show' => 1,
			//            		'title' => '快递（打印模版）',
			//            		'field' => '',
			//            		'width' => '150',
			//            		'align' => ''
			//            ),
			//            array (
			//                'type' => 'button',
			//                'show' => 1,
			//                'title' => '操作',
			//                'field' => '_operate',
			//                'width' => '150',
			//                'align' => '',
			//                'buttons' => array (
			//                	/*
			//                	array('id'=>'edit', 'title' => '编辑',
			//                		'act'=>'pop:prm/brand/detail&app_scene=edit', 'show_name'=>'编辑',
			//                		'show_cond'=>'obj.is_buildin != 1'),
			//
			//                   array('id'=>'delete', 'title' => '删除', 'callback'=>'do_delete','confirm'=>'确认要删除此信息吗？'),
			//                	*/
			//                ),
			//            )
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
                                array('id' => 'edit', 'title' => '修改', 'act' => 'pop:oms/sell_record/ex_update_detail&sell_record_code={sell_record_code}',
                                        'show_name' => '修改订单信息（{sell_record_code}）', 'pop_size' => '1200,403','show_cond' => 'obj.order_status == 0'),
                        ),
                );
}
render_control('DataTable', 'table', array(
	'conf' => array('list' => $option_conf_list),
	'dataset' => 'oms/SellRecordModel::do_list_by_page',
	'queryBy' => 'searchForm',
	'idField' => 'sell_record_code',
	'export' => array('id' => 'exprot_detail', 'conf' => 'search_record_detail', 'name' => '订单查询明细','export_type' => 'file'),
	//'RowNumber'=>true,
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
			array('title' => '赠品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map','value' => array('0' => '否', '1' => '是'))),
		),
		'page_size' => 10,
		'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
		'params' => 'sell_record_code',
	),
	'CheckSelection' => true,
	'customFieldTable' => 'sell_record_do_list/table',
	// 'width'=>800, // 宽度
	// 'height'=>500, // 高度，如果分页数据实际高度，操作此高度时会产生滚动条
	'init' => 'nodata',
//    'init_note_nodata' => '点击查询显示数据',
	'events' => array(
		'rowdblclick' => 'showDetail',
	),
        'cookie_page_size'=>'sell_record_list_page_size',
));
?>

<script type="text/javascript">
$(function(){
      $('#exprot_detail').parent().append('<span style="color:red;vertical-align: middle;margin-left:20px;">默认查询近3个月订单</span>');
    
      $('#exprot_list').click(function(){
        var url = '?app_act=sys/export_csv/export_show', //暂时不是框架级别
        //var url = '?app_act=ctl/index/do_index&app_ctl=DataTable/do_get_data';
        params = tableStore.get('params');

        params.ctl_type = 'export';
        params.ctl_export_conf = 'search_record_list';
        params.ctl_export_name =  '订单查询';
        <?php echo   create_export_token_js('oms/SellRecordModel::do_list_by_page');?>
        var obj = searchFormForm.serializeToObject();
        for(var key in obj){
            params[key] =  obj[key];
	}

        for(var key in params){
            url +="&"+key+"="+params[key];
        }
        params.ctl_type = 'view';
        //window.location.href = url;
        window.open(url);

    });
});

function view(sell_record_code) {
    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
    openPage(window.btoa(url),url,'订单详情');
}

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

function do_delete (_index, row) {
	$.ajax({ type: 'POST', dataType: 'json',
    url: '<?php echo get_app_url('prm/brand/do_delete'); ?>', data: {brand_id: row.brand_id},
    success: function(ret) {
    	var type = ret.status == 1 ? 'success' : 'error';
    	if (type == 'success') {
        BUI.Message.Alert('删除成功：', type);
        tableStore.load();
    	} else {
        BUI.Message.Alert(ret.message, type);
    	}
    }
	});
}
//排序
    function sort() {
        tableStore.load();
    }    

var url = '<?php echo get_app_url('base/store/get_area'); ?>';
    $(document).ready(function() {
        tableStore.on('beforeload', function(e) {
            e.params.contain_express_money = $("#contain_express_money").attr('checked')=='checked'?'1':'0';
            e.params.pay_status = $("#pay_status").find(".active").attr("id");
            e.params.order_status = $("#order_status").find(".active").attr("id");
            e.params.notice_flag = $("#notice_flag").find(".active").attr("id");
            e.params.shipping_flag = $("#shipping_flag").find(".active").attr("id");
            e.params.cancel_flag = $("#cancel_flag").find(".active").attr("id");
            e.params.is_invoice=$("#invoice_status").find(".active").attr("id");
            var sort_e = $("#sort  option:selected");
            if (sort_e.length > 0) {
                e.params.is_sort = $("#sort  option:selected").val();
            }
            
             tableStore.set("params", e.params);
        });
        $('#country').change(function(){
            var parent_id = $(this).val();
		if(parent_id===''){
                    parent_id=1;
                }
            areaChange(parent_id,0,url);
        });
        $('#province').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,1,url);
        });
        $('#city').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,2,url);
        });
        $('#district').change(function(){
            var parent_id = $(this).val();
            areaChange(parent_id,3,url);
        });
//        $('#country').val('1');
        $('#country').change();

//        $('#btn-csv').click(function(){
//        	report_excel();
//        });
    })
//读取已选中项
    function get_checked(obj, func) {
        var ids = new Array();
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
        if(obj.text()=='批量修改发货仓库'||obj.text()=='批量修改仓库留言'||obj.text()=='批量修改配送方式'||obj.text()=='批量挂起'){
            func.apply(null, [ids]);
        }else{
	        BUI.Message.Show({
	            title: '自定义提示框',
	            msg: '是否执行订单' + obj.text() + '?',
	            icon: 'question',
	            buttons: [
	                {
	                    text: '是',
	                    elCls: 'button button-primary',
	                    handler: function() {
	                        func.apply(null, [ids]);
	                        this.close();
	                    }
	                },
	                {
	                    text: '否',
	                    elCls: 'button',
	                    handler: function() {
	                        this.close();
	                    }
	                }
	            ]
	        });
        }
    }



    //初始化批量操作按钮
    function btn_init_opt(tab_id, id) {
        $("#" + tab_id + " .btn_" + id).click(function() {
            get_checked($(this), function(ids) {
                var params = {"sell_record_code_list": ids, "type": id};
                $.post("?app_act=oms/sell_record/opt_batch", params, function(data) {
                    if (data.status == 1) {
                        BUI.Message.Alert(data.message, 'info')
                        //刷新
                        tableStore.load()
                    } else {
                        BUI.Message.Alert(data.message, 'error')
                    }
                }, "json");
            })
        });
    }


function showDetail(index, row) {
    openPage('<?php echo base64_encode('?app_act=oms/sell_record/view&sell_record_code=') ?>'+row.sell_record_code,'?app_act=oms/sell_record/view&ref=do&sell_record_code='+row.sell_record_code,'订单详情');
}

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


function report_excel()
{
	var searchForm = document.forms['searchForm'].elements;
	var param="";

//    param=param+"&contain_express_money="+$("#contain_express_money").attr('checked')=='checked'?'1':'0';
    param=param+"&pay_status="+$("#pay_status").find(".active").attr("id");
    param=param+"&order_status="+$("#order_status").find(".active").attr("id");
    param=param+"&notice_flag="+$("#notice_flag").find(".active").attr("id");
    param=param+"&shipping_flag="+$("#shipping_flag").find(".active").attr("id");
    param=param+"&cancel_flag="+$("#cancel_flag").find(".active").attr("id");

	param=param+"&deal_code_list="+searchForm['deal_code_list'].value;
	param=param+"&pay_type="+searchForm['pay_type'].value;
	param=param+"&sale_channel_code="+searchForm['sale_channel_code'].value;
	param=param+"&shop_code="+searchForm['shop_code'].value;
	param=param+"&store_code="+searchForm['store_code'].value;
	param=param+"&alipay_no="+searchForm['alipay_no'].value;
	param=param+"&buyer_name="+searchForm['buyer_name'].value;
	param=param+"&goods_code="+searchForm['goods_code'].value;
	param=param+"&barcode="+searchForm['barcode'].value;

	param=param+"&is_change_record="+searchForm['is_change_record'].value;
	param=param+"&seller_remark="+searchForm['seller_remark'].value;
	param=param+"&buyer_remark="+searchForm['buyer_remark'].value;

	param=param+"&order_remark="+searchForm['order_remark'].value;
	param=param+"&receiver_name="+searchForm['receiver_name'].value;
	param=param+"&receiver_mobile="+searchForm['receiver_mobile'].value;

	param=param+"&express_code="+searchForm['express_code'].value;
	param=param+"&express_no="+searchForm['express_no'].value;
	param=param+"&country="+searchForm['country'].value;

	param=param+"&province="+searchForm['province'].value;
	param=param+"&city="+searchForm['city'].value;
	param=param+"&district="+searchForm['district'].value;

	param=param+"&receiver_addr="+searchForm['receiver_addr'].value;
	param=param+"&is_stock_out="+searchForm['is_stock_out'].value;
	param=param+"&sale_mode="+searchForm['sale_mode'].value;

	param=param+"&is_lock_person="+searchForm['is_lock_person'].value;
	param=param+"&is_lock="+searchForm['is_lock'].value;
	param=param+"&is_pending="+searchForm['is_pending'].value;

	param=param+"&is_problem="+searchForm['is_problem'].value;
	param=param+"&is_handwork="+searchForm['is_handwork'].value;
	param=param+"&is_combine="+searchForm['is_combine'].value;

	param=param+"&is_split="+searchForm['is_split'].value;
	param=param+"&is_copy="+searchForm['is_copy'].value;
	param=param+"&num_start="+searchForm['num_start'].value;

	param=param+"&num_end="+searchForm['num_end'].value;
//	param=param+"&is_invoice="+searchForm['is_invoice'].value;
	param=param+"&invoice_title="+searchForm['invoice_title'].value;

	param=param+"&contain_express_money="+searchForm['contain_express_money'].value;
	param=param+"&money_start="+searchForm['money_start'].value;
	param=param+"&money_end="+searchForm['money_end'].value;

	param=param+"&record_time_start="+searchForm['record_time_start'].value;
	param=param+"&record_time_end="+searchForm['record_time_end'].value;
	param=param+"&pay_time_start="+searchForm['pay_time_start'].value;

	param=param+"&pay_time_end="+searchForm['pay_time_end'].value;
	param=param+"&send_time_start="+searchForm['send_time_start'].value;
	param=param+"&send_time_end="+searchForm['send_time_end'].value;

	param=param+"&plan_send_time_start="+searchForm['plan_send_time_start'].value;
	param=param+"&plan_send_time_end="+searchForm['plan_send_time_end'].value;
	param=param+"&weight_start="+searchForm['weight_start'].value;
	param=param+"&weight_end="+searchForm['weight_end'].value;
	param=param+"&action_type=export_csv&app_fmt=json";
	url="?app_act=oms/sell_record/export_csv_list"+param;
	window.location.href=url;
}

    //旺旺
    function set_wangwang_html(value, row, index){
        if(row.sale_channel_code == 'taobao') {
            return template('<span> {buyer_name}</span><span class="wangwang"><span class="ww-light ww-small"><a href="javascript:launch_ww(\'{sell_record_code}\')" class="ww-inline ww-online" title="点此可以直接和买家交流。"><span>旺旺在线</span></a></span></span>', row);
        } else {
           return template('<span>{buyer_name}</span>', row);
        }
    }

  function launch_ww(record_code){
        var url = "?app_act=oms/sell_record/link_wangwang&record_code="+record_code;
        window.open(url);
    }
</script>

<?php include_once get_tpl_path('process_batch_task');?>
