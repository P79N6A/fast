<style type="text/css">
    .well {
        min-height: 30px;
    }
</style>

<div class="page-header1" style="margin-top: 4px;">
    <span class="page-title">
        <h2>活动商品设置</h2>
    </span>
    <?php render_control('PageHead', 'head1',
    array('title' => '活动商品设置',
	    'links' => array(
			array('url' => 'op/op_activity_goods/rule_goods_import', 'title' => '导入活动商品', 'is_pop' => true),
	    ),
        'ref_table' => 'table'
    ));
    ?>
</div>
<div class="clear"></div>
<hr>

<?php
render_control('SearchForm', 'searchForm', array(
   'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'show_row' => 1,
    'fields' => array(
        array(
            'label' => '店铺',
            'type' => 'select_multi',
            'id' => 'shop_code',
            'data' => load_model('base/ShopModel')->get_purview_shop_tianmao(),
        ),
    )
));
?>

<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'delete', 'title' => '移除','priv' => 'op/op_activity_goods/do_delete',
                        'callback' => 'do_delete', 'confirm' => '确认要移除吗？'),
                ),
            ),
            array(
                'title' => '店铺',
                'show' => 1,
                'type' => 'text',
                'width' => '150',
                'field' => 'shop_name',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_barcode',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品规格',
                'field' => 'spec',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动库存',
                'field' => 'inv_num',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype:'number'}"
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '活动售价',
                'field' => 'sale_price',
                'width' => '100',
                'align' => '',
                'editor' => "{xtype:'number'}"
            ),
        )
    ),
    'dataset' => 'op/activity/ActivityGoodsModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
    'export' => array('id' => 'exprot_list', 'conf' => 'goods_record_list', 'name' => '活动商品列表'),
    'CellEditing' => (1 == $response['data']['id']) ? false : true,
));
?>
<script type="text/javascript">
    /*
    * 商品删除
    */
    function do_delete(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('op/op_activity_goods/do_delete');?>',
            data: {id: row.id},
            success:function(ret) {
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
    if (typeof tableCellEditing != "undefined") {
        //数量、价格修改回调操作
        tableCellEditing.on('accept', function (record, editor) {
            if (record.record.inv_num < 0 || record.record.sale_price < 0) {
                BUI.Message.Alert('不能为负数', 'error');
                tableStore.load();
                return;
            }
            var _record = record.record;
            $.post('?app_act=op/op_activity_goods/do_edit_detail',
                    {id: _record.id, shop_code: _record.shop_code, sku: _record.sku, barcode: _record.barcode, inv_num: _record.inv_num, sale_price: _record.sale_price},
                    function (result) {
                        BUI.Message.Alert('修改成功');
                        window.location.reload();
                    }, 'json');
        });
    }
</script>


