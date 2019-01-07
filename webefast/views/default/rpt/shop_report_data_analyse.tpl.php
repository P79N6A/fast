<?php echo load_js('comm_util.js') ?>
<?php render_control('PageHead', 'head1',array('title'=>'店铺运营数据分析','ref_table'=>'table'));?>

<?php
$pay_time_start = date("Y-m-d",strtotime('-30 day'));
$pay_time_end = date("Y-m-d");

render_control('SearchForm', 'searchForm', array(
    'buttons' =>array(
    		array(
    				'label' => '查询',
    				'id' => 'btn-search',
    				'type'=>'submit'
    		),
    		array(
    				'label' => '导出',
    				'id' => 'exprot_list',
    		),
    ) ,
    'fields' => array(
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
            'label' => '销售平台',
            'type' => 'select_multi',
            'id' => 'sale_channel_code',
            //'data' => load_model('base/SaleChannelModel')->get_select()
            'data' => load_model('base/SaleChannelModel')->get_my_select()
        ),
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop(),
        ),
       
        
        
    )
));
?>

<?php
render_control ( 'DataTable', 'table', array (
    'conf' => array (
        'list' => array (
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '日期',
                'field' => 'biz_date',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '销售平台',
                'field' => 'sale_channel_name',
                'width' => '100',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店铺',
                'field' => 'shop_name',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单成交笔数',
                'field' => 'order_sale_count',
                'width' => '80',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品成交数量',
                'field' => 'goods_sale_count',
                'width' => '80',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '订单成交金额',
                'field' => 'order_sale_money',
                'width' => '95',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '运费',
                'field' => 'order_sale_express_money',
                'width' => '65',
                'align' => 'center',
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '已发货订单量',
                'field' => 'order_shipping_count',
                'width' => '105',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已发货商品数量',
                'field' => 'order_shipping_goods_count',
                'width' => '90',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '已发货订单金额',
                'field' => 'order_shipping_money',
                'width' => '115',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未发货订单量',
                'field' => 'order_un_shipping_count',
                'width' => '105',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未发货商品量',
                'field' => 'order_un_shipping_goods_count',
                'width' => '90',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '未发货总销售金额',
                'field' => 'order_un_shipping_money',
                'width' => '112',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单申请笔数',
                'field' => 'refund_apply_count',
                'width' => '112',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退单申请退款金额',
                'field' => 'refund_apply_money',
                'width' => '112',
                'align' => 'center'
            ),

            array(
                'type' => 'text',
                'show' => 1,
                'title' => '退货订单量',
                'field' => 'refund_return_goods_order_count',
                'width' => '95',
                'align' => 'center'
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '退货商品量',
            	'field' => 'refund_return_goods_count',
            	'width' => '80',
            	'align' => 'center'
            ),
            array(
            	'type' => 'text',
            	'show' => 1,
            	'title' => '退货总销售金额',
            	'field' => 'refund_actual_money',
            	'width' => '100',
            	'align' => 'center'
            ),
        )
    ),
    'dataset' => 'rpt/ReportBaseOrderCollectModel::shop_report_data_analyse',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_code',
    'export'=> array('id'=>'exprot_list','conf'=>'shop_report_data_analyse','name'=>'店铺运营数据分析'),
    //'RowNumber'=>true,
    //'CascadeTable' => array(),
    //'CheckSelection'=>true,
    'customFieldTable'=>'shop_report/table',
    'init' => 'nodata',
    //'init_note_nodata' => '点击查询显示数据',
    'events' => array(
        //'rowdblclick' => 'showDetail',
    ),
) );
?>



<script type="text/javascript">
	$(document).ready(function(){  
		$("#pay_time_start").val("<?php echo $pay_time_start ?>");
		$("#pay_time_end").val("<?php echo $pay_time_end ?>");  
	});  
	
</script>

<script type="text/javascript">
    BUI.use('bui/toolbar',function(Toolbar){
        //可勾选
        var g1 = new Toolbar.Bar({
            elCls : 'button-group',
            itemStatusCls  : {
                selected : 'active' //选中时应用的样式
            },
            defaultChildCfg : {
                elCls : 'button button-small',
                selectable : true //允许选中
            },
            children : [
                {content : '日报',id:'day_report',selected : true},
                {content : '周报',id:'week_report'},
                {content : '月报',id:'year_report'}
            ],
            render : '#b1'
        });

        g1.render();
        g1.on('itemclick',function(ev){
            //$('#l1').text(ev.item.get('id') + ':' + ev.item.get('content'));
        });
    });
</script>