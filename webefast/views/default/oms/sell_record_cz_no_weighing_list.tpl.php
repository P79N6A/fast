<?php echo load_js('jquery.cookie.js') ?>
<?php echo load_js('comm_util.js') ?>
<?php
$links =  array(
		array('url' => 'oms/sell_record_cz/view','title' => '订单称重')
);
render_control('PageHead', 'head1', array('title' => '待称重订单列表',
   'links'=>$links,
    'ref_table' => 'table'
));
?>

<?php
$keyword_type = array();
$keyword_type['sell_record_code'] = '订单号';
$keyword_type['deal_code'] = '交易号';
$keyword_type['express_no'] = '物流单号';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['receiver_mobile'] = '手机';

$keyword_type = array_from_dict($keyword_type);

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
            'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
            'type' => 'input',
            'title'=>'',
            'data'=>$keyword_type,
            'id' => 'keyword',
        ),
        array(
        		'label' => '仓库',
        		'type' => 'select_multi',
        		'id' => 'store_code',
        		'data' => load_model('base/StoreModel')->get_purview_store(),
        ),
        array(
            'label' => '配送方式',
            'type' => 'select_multi',
            'id' => 'express_code',
            'data' => ds_get_select('express'),
        ),
        array(
            'label' => '发货时间',
            'type' => 'group',
            'field' => 'daterange1',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'delivery_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'delivery_time_end', 'remark' => ''),
            )
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
                'title' => '订单号',
                'field' => 'sell_record_code',
                'width' => '120',
                'align' => '',
                'format_js' => array(
                    'type' => 'html',
                    'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                ),
                'sortable' => true
            ),
            array (
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
                'title' => '配送方式',
                'field' => 'express_name',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '物流单号',
                'field' => 'express_no',
                'width' => '100',
                'align' => '',
//                'editor' => "{xtype : 'text'}",
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '发货时间',
                'field' => 'delivery_time',
                'width' => '150',
                'align' => '',
                'sortable' => true
            ),
            array (
                'type' => 'text',
                'show' => 1,
                'title' => '交易号',
                'field' => 'deal_code_list',
                'width' => '120',
                'format_js' => array(
                                'type' => 'html',
                                'value' => '<a href="javascript:view({sell_record_code})">{deal_code_list}</a>',
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
                'title' => '收货人',
                'field' => 'receiver_name',
                'width' => '80',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'receiver_mobile',
                'width' => '100',
                'align' => '',
                'sortable' => true
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '收货地址',
                'field' => 'receiver_address',
                'width' => '250',
                'align' => '',
                'sortable' => true
            ),
        )
    ),
    'dataset' => 'oms/SellRecordCzModel::no_weighing_page',
    'queryBy' => 'searchForm',
    'idField' => 'sell_record_id',
    'export'=> array('id'=>'exprot_list','conf'=>'sell_record_cz','name'=>'未称重订单列表'),
//    'CellEditing' => true,
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<script type="text/javascript">
	function view(sell_record_code) {
	    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
	    openPage(window.btoa(url),url,'订单详情');
    }
</script>

