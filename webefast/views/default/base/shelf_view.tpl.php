<?php
render_control('PageHead', 'head1', array('title' => '仓库库位列表[' . $response['store_name'] . ']',
    'links' => array(
        array('url' => "base/shelf/detail&app_scene=add&store_code={$response['store_code']}", 'title' => '新增库位', 'is_pop' => true, 'pop_size' => '500,400'),
        array('url' => 'base/shelf/do_list', 'is_pop' => false, 'title' => '库位管理列表'),
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
        )
    ),
    'fields' => array(
        array(
            'label' => '库位',
            'title' => '库位名称/代码',
            'type' => 'input',
            'id' => 'code_name',
        ),
    ),
    'hidden_fields' => array(array('field' => 'store_code', 'value' => $response['store_code'])) // 配置隐藏字段
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
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'pop:base/shelf/detail&app_scene=edit', 'show_name' => '编辑',
                        'show_cond' => 'obj.is_buildin != 1'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'confirm' => '确认要删除此信息吗？'),
                    array(
                        'id' => 'view',
                        'title' => '查看库位商品',
                        'show_name'=>'库位商品列表',
                        //'callback' => 'do_view'
                        'act' => "base/shelf/goods_list&store_code={store_code}&shelf_code={shelf_code}&store_name={store_name}",
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位代码',
                'field' => 'shelf_code',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位名称',
                'field' => 'shelf_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属仓库',
                'field' => 'store_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用',
                'field' => 'status',
                'width' => '50',
                'align' => '',
                'format_js' => array('type' => 'map_checked')
            ),
        )
    ),
    'dataset' => 'base/ShelfModel::get_by_page_detail',
    'queryBy' => 'searchForm',
    'idField' => 'shelf_id',
    'export'=> array('id'=>'exprot_list','conf'=>'shelf_list','name'=>'库位导出','export_type' => 'file'),
    'params' => array('filter' => array('store_code' => $response['store_code'])),
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/shelf/do_delete'); ?>', data: {shelf_id: row.shelf_id},
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
    function do_view(_index, row) {
        location.href = "?app_act=base/shelf/goods_list&shelf_code=" + row.shelf_code + "&store_code=" + row.store_code;
    }


</script>

