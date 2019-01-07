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
                    'field' => 'deal_code',
                    'width' => '200',
                    'format_js' => array(
                    'type' => 'html',
                        'value' => '<a href="javascript:view({sell_return_code})">{deal_code}</a>',
                    ),
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '退单号',
                    'field' => 'sell_return_code',
                    'width' => '200',
                    'align' => '',
                    'format_js' => array(
                    'type' => 'html',
                        //'value' => '<a href="' . get_app_url('oms/sell_record/view') . '&sell_record_code={sell_record_code}" >{sell_record_code}</a>',
                        'value' => '<a href="javascript:view({sell_return_code})">{sell_return_code}</a>',
                    ),
                ),                
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '退单商品金额',
                    'field' => 'return_avg_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '卖家承担运费',
                    'field' => 'seller_express_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '赔付金额',
                    'field' => 'compensate_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '手工调整',
                    'field' => 'adjust_money',
                    'width' => '100',
                ),
                array (
                    'type' => 'text',
                    'show' => 1,
                    'title' => '退款总金额',
                    'field' => 'refund_total_fee',
                    'width' => '100',
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
                        array('id' => 'repair', 'title' => '修复', 'callback' => 'do_repair', 'confirm' => '确认要将退单信息加入到零信结算单吗？')
                   ),
               )
            )
        ),
        'dataset' => 'oms/SellSettlementRepairModel::do_return_list_by_page',
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
function view(sell_return_code) {
    var url = '?app_act=oms/sell_return/after_service_detail&sell_return_code=' +sell_return_code
    openPage(window.btoa(url),url,'售后服务单详情');
}
function do_repair(_index, row) {
	$.ajax(
    {
        type: 'POST', dataType: 'json',
        url: '<?php echo get_app_url('oms/sell_repair/do_return_repair');?>', data: {sell_return_code:row.sell_return_code},
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