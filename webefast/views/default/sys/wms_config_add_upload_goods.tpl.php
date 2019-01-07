<?php

render_control('PageHead', 'head1', array('title' => 'WMS下发商品配置',
    'links' => array(array('url' => 'sys/wms_config/import_goods&wms_config_id=' . $request['_id'], 'title' => '商品导入', 'is_pop' => true, 'pop_size' => '440,260')),
    'ref_table' => 'table'
));

render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => '商品条形码',
            'title' => '',
            'type' => 'input',
            'id' => 'barcode',
        ),
    )
));

render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '100',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确定要删除这条数据吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品条形码',
                'field' => 'barcode',
                'width' => '200',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品编码',
                'field' => 'goods_code',
                'width' => '200',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '商品名称',
                'field' => 'goods_name',
                'width' => '200',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '更新时间',
                'field' => 'lastchanged',
                'width' => '200',
                'align' => 'center',
            ),
        )
    ),
    'dataset' => 'sys/WmsConfigModel::get_goods_config',
    'queryBy' => 'searchForm',
    'params'=> array('filter' => array('wms_config_id'=>$request['_id'])),
    'idField' => 'id',
));
?>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', 
            dataType: 'json',
            url: '<?php echo get_app_url('sys/wms_config/do_delete_goods');?>', 
            data: {wms_config_id: row.wms_config_id, barcode: row.barcode},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }
</script>