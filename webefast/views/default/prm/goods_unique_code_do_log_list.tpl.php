<style type="text/css">
    #action_time_start{width:100px;}
    #action_time_end{width:100px;}
</style>
<?php render_control('PageHead', 'head1',
		array('title'=>'唯一码跟踪',
//                         'links' => array(
// 		 array(
// 		 //'url' => 'prm/goods_unique_code/import', 'title' => '导入商品唯一码', 'is_pop' => true, 'pop_size' => '500,350'
// 		 ),
// ),
				'ref_table'=>'table'
));?>

<?php
$keyword_type = array();
$keyword_type['unique_code'] = '唯一码';
$keyword_type['record_code'] = '单据编号';
$keyword_type['goods_code'] = '商品编码';
$keyword_type['barcode'] = '商品条形码';
$keyword_type['goods_name'] = '商品名称';
$keyword_type['buyer_name'] = '会员昵称';
$keyword_type['receiver_mobile'] = '手机号';
$keyword_type['receiver_name'] = '收货人';
$keyword_type['deal_code_list'] = '交易号';
$keyword_type = array_from_dict($keyword_type);
$buttons = array(
    array(
        'label' => '查询',
        'id' => 'btn-search',
        'type' => 'submit'
    ),
);
if (load_model('sys/PrivilegeModel')->check_priv('prm/goods_unique_code/export')) {
    array_push($buttons, array('label' => '导出', 'id' => 'exprot_list',));
}
render_control ( 'SearchForm', 'searchForm', array (
    'buttons' => $buttons,
    'fields' => array (
        array(
           'label' => array('id'=>'keyword_type','type'=>'select','data'=>$keyword_type),
           'type' => 'input',
           'title'=>'',
           'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '操作时间',
            'type' => 'group',
            'field' => 'daterange2',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'action_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'action_time_end', 'remark' => ''),
            )
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
                    'title' => '唯一码',
                    'field' => 'unique_code',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品条形码',
                	'field' => 'barcode',
                	'width' => '150',
                	'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据类型',
                    'field' => 'record_type_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '单据编号',
                    'field' => 'record_code',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '交易号',
                    'field' => 'deal_code_list',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '操作名称',
                	'field' => 'action_name_name',
                	'width' => '100',
                	'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '操作时间',
                	'field' => 'action_time',
                	'width' => '100',
                	'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '店铺',
                	'field' => 'shop_code_name',
                	'width' => '100',
                	'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品编码',
                	'field' => 'goods_code',
                	'width' => '100',
                	'align' => ''
                ),
                array (
                	'type' => 'text',
                	'show' => 1,
                	'title' => '商品名称',
                	'field' => 'goods_name',
                	'width' => '100',
                	'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格1',
                    'field' => 'spec1_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格2',
                    'field' => 'spec2_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统SKU码',
                    'field' => 'sku',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '会员昵称',
                    'field' => 'buyer_name',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收货人',
                    'field' => 'receiver_name',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '手机号',
                    'field' => 'receiver_mobile',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收货地址',
                    'field' => 'receiver_address',
                    'width' => '150',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '收发货仓库',
                    'field' => 'store_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '配送方式',
                    'field' => 'express_name',
                    'width' => '100',
                    'align' => ''
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '快递单号',
                    'field' => 'express_no',
                    'width' => '100',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/GoodsUniqueCodeLogModel::get_log_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'unique_id',
        'customFieldTable' => 'goods_unique_code_do_log_list/table',
        'export' => array('id' => 'exprot_list', 'conf' => 'unique_code_log_list', 'name' => '唯一码跟踪', 'export_type' => 'file'),
        //'RowNumber'=>true,
		//'CheckSelection'=>true,
    ) );
?>


<script type="text/javascript">

$(document).ready(function() {
// 	$("#TabPage1 a").click(function() {
//         tableStore.load();
//     });
    
//     tableStore.on('beforeload', function(e) {
//     	e.params.do_list_tab = $("#TabPage1").find(".active").find("a").attr("id");
//     	tableStore.set("params", e.params);
//     });

})
</script>

