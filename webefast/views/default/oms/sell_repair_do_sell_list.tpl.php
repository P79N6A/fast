<?php echo load_js('comm_util.js') ?>
<?php
render_control (
    'DataTable', 
    'table', 
    array (
        'conf' => array (
            'list' => array (
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '交易号',
                    'field' => 'deal_code_list',
                    'width' => '200',
                    'format_js' => array(
                    'type' => 'html',
                        'value' => '<a href="javascript:view({sell_record_code})">{deal_code_list}</a>',
                    ),
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单号',
                    'field' => 'sell_record_code',
                    'width' => '200',
                    'align' => '',
                    'format_js' => array(
                    'type' => 'html',
                        //'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}" >{sell_record_code}</a>',
                        'value' => '<a href="javascript:view({sell_record_code})">{sell_record_code}</a>',
                    ),
                ),                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品金额',
                    'field' => 'goods_money',
                    'width' => '100',
                ), 
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '邮费',
                    'field' => 'express_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单总金额',
                    'field' => 'order_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单应付款',
                    'field' => 'payable_money',
                    'width' => '100',
                ),                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '订单已付款',
                    'field' => 'paid_money',
                    'width' => '180',
                ),                
               array (
                   'type' => 'button',
                   'show' => 1,
                   'title' => '操作',
                   'field' => '_operate',
                   'width' => '130',
                   'align' => '',
                   'buttons' => array (
                        //array('id'=>'edit', 'title' => '编辑', 'act'=>'pop:prm/brand/detail&app_scene=edit', 'show_name'=>'编辑', 'show_cond'=>'obj.is_buildin != 1'),    
                        array('id' => 'repair', 'title' => '修复', 'callback' => 'do_repair', 'confirm' => '确认要将订单信息加入到零信结算单吗？')
                   ),
               )
            )
        ),
        'dataset' => 'oms/SellSettlementRepairModel::do_sell_list_by_page',
        /*'queryBy' => 'searchForm',*/
        'idField' => 'sell_record_code',
        //'RowNumber'=>true,
        
        //当前表格是否显示ajax异步加载详细信息
        /*'CascadeTable' => array(
            'list' => array(
                array('title' => '商品名称', 'type' => 'text', 'width' => '100', 'field' => 'goods_code_name'),
                array('title' => '商品编码', 'type' => 'text', 'width' => '100', 'field' => 'goods_code'),
                array('title' => '商品条形码', 'type' => 'text', 'width' => '100', 'field' => 'barcode'),
                array('title' => $response['goods_spec1_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec1_code_name'),
                array('title' => $response['goods_spec2_rename'], 'type' => 'text', 'width' => '100', 'field' => 'spec2_code_name'),
                array('title' => '数量（实物锁定数）', 'type' => 'text', 'width' => '100', 'field' => 'num', 'format_js' => array('type' => 'html', 'value' => '{num}(<span style="color:red">{lock_num}</span>)',)),
                array('title' => '标准价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
                array('title' => '单价', 'type' => 'text', 'width' => '100', 'field' => 'goods_price'),
                array('title' => '均摊金额', 'type' => 'text', 'width' => '100', 'field' => 'avg_money'),
                array('title' => '预售', 'type' => 'text', 'width' => '100', 'field' => 'sale_mode','format_js' => array('type' => 'map','value'=>array('stock'=>'否','presale'=>'是'))),
                array('title' => '礼品', 'type' => 'text', 'width' => '100', 'field' => 'is_gift', 'format_js' => array('type' => 'map_checked')),
                array('title' => '计划发货时间', 'type' => 'text', 'width' => '100', 'field' => 'shipping_time'),
            ),
            'page_size' => 10,
            'url' => get_app_url('oms/sell_record/get_detail_list_by_sell_record_code&app_fmt=json'),
            'params' => 'sell_record_code'
        ),*/
        //设置是否显示checkbox框
        //'CheckSelection'=>true,
        //设置自定义表头
        //'customFieldTable'=>'sell_record_do_list/table',
        // 'width'=>800, // 宽度
        // 'height'=>500, // 高度，如果分页数据实际高度，操作此高度时会产生滚动条
        //设置初始化页面是否显示数据[data|nodata]
        'init' => 'data',
        //'init_note_nodata' => '点击查询显示数据',
        /*'events' => array(
            'rowdblclick' => 'showDetail',
        ),*/
    )
);
?>
<script type="text/javascript">
function view(sell_record_code) {
    var url = '?app_act=oms/sell_record/view&sell_record_code=' +sell_record_code
    openPage(window.btoa(url),url,'订单详情');
}
function do_repair(_index, row) {
	$.ajax(
    {
        type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('oms/sell_repair/do_sell_repair');?>', data: {sell_record_code:row.sell_record_code},
        success: function(ret) {
            var type = ret.status == 1 ? 'success' : 'error';
            if (type == 'success') {
                BUI.Message.Alert(ret.message, type);
                tableStore.load();
            } else {
                BUI.Message.Alert(ret.message, type);
            }
        }
	});
}
</script>