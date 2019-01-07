<style type="text/css">

</style>
<?php
render_control('PageHead', 'head1', array('title' => '商品子条形码管理',
    'links' => array(
//        array('url' => 'prm/goods_barcode_child/import', 'title' => '导入商品子条形码', 'is_pop' => true, 'pop_size' => '500,350'),
    ),
    'ref_table' => 'table'
));
?>

<?php
render_control('SearchForm', 'searchForm', array(
    'buttons' => array(
        array(
            'label' => '查询',
            'id' => 'btn-search',
            'type' => 'submit'
        ),
        array(
            'label' => '导出',
            'id' => 'exprot_list',
        ),
    ),
    'fields' => array(
        array(
            'label' => '商品编码',
            'type' => 'input',
            'id' => 'goods_code',
        ),
        array(
            'label' => '商品条形码',
            'type' => 'input',
            'id' => 'p_barcode',
        ),
        array(
            'label' => '商品子条形码',
            'type' => 'input',
            'id' => 'barcode',
        ),
    )
));
?>
<button class="button button-primary " onclick="do_multi_delete()">批量删除</button>
<div style="margin-bottom: 80px;margin-top: 5px;">
    <?php
    render_control('DataTable', 'table', array(
        'conf' => array(
            'list' => array(
                array(
                    'type' => 'button',
                    'show' => 1,
                    'title' => '删除',
                    'field' => '_operate',
                    'width' => '80',
                    'align' => '',
                    'buttons' => array(
                        array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认删除数据？'),
                    ),
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
                    'title' => '商品编码',
                    'field' => 'goods_code',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格1',
                    'field' => 'spec1_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '规格2',
                    'field' => 'spec2_name',
                    'width' => '80',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '系统SKU码',
                    'field' => 'sku',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品条形码',
                    'field' => 'p_barcode',
                    'width' => '150',
                    'align' => ''
                ),
                array(
                    'type' => 'text',
                    'show' => 1,
                    'title' => '商品子条形码',
                    'field' => 'barcode',
                    'width' => '150',
                    'align' => ''
                ),
            )
        ),
        'dataset' => 'prm/GoodsBarcodeChildModel::get_by_page',
        'queryBy' => 'searchForm',
        'idField' => 'barcode_id',
        'export' => array('id' => 'exprot_list', 'conf' => 'goods_barcode_child_list', 'name' => '商品子条码', 'export_type' => 'file'),
        //'RowNumber'=>true,
        'CheckSelection' => true,
    ));
    ?>
</div>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('prm/goods_barcode_child/do_delete'); ?>', data: {barcode_id: row.barcode_id},
            success: function (ret) {
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

//读取已选中项
    function get_checked(func) {
        var ids = []
        var selecteds = tableGrid.getSelection();
        for (var i in selecteds) {
            ids.push(selecteds[i].barcode_id)
        }

        if (ids.length == 0) {
            BUI.Message.Alert("请选择子条码", 'error');
            return
        }

        func.apply(null, [ids])
    }

    function do_multi_delete() {
        get_checked(function (ids) {
            $.ajax({type: 'POST', dataType: 'json',
                url: '<?php echo get_app_url('prm/goods_barcode_child/do_delete'); ?>', data: {ids: ids.toString()},
                success: function (ret) {
                    var type = ret.status == 1 ? 'success' : 'error';
                    if (type == 'success') {
                        BUI.Message.Alert('删除成功：', type);
                        tableStore.load();
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }
                }
            });

        })

    }

</script>



