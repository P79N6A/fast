<?php
render_control('PageHead', 'head1', array('title' => '仓库库位管理',
    'links' => array(
        array('url' => 'base/shelf/scan&app_scene=add', 'title' => '扫描绑定库位', 'is_pop' => true, 'pop_size' => '800,200'),
        array('url' => 'base/shelf/import&app_scene=add', 'title' => '仓库库位导入', 'is_pop' => true, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'
));
?>


<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '仓库',
            'title' => '仓库名称/代码',
            'type' => 'input',
            'id' => 'code_name',
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
                'width' => '170',
                'align' => '',
                'buttons' => array(
                    array(
                        'id' => 'view',
                        'title' => '仓库库位列表',
                        'callback' => 'do_view'
                    ),
                    array(
                        'id' => 'export_list',
                        'title' => '导出',
                        'callback' => 'report_excel'
                    ),
                    array(
                        'id' => 'delete',
                        'title' => '删除',
                        'callback' => 'do_delete',
                        'confirm' => '确认要删除此仓库下的库位吗？',
                        'show_cond' => 'obj.is_del == 1'
                    ),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库代码',
                'field' => 'store_code',
                'width' => '100',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '仓库名称',
                'field' => 'store_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '库位数量',
                'field' => 'num',
                'width' => '100',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'base/ShelfModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'store_id',
    //'RowNumber'=>true,
    // 'CheckSelection'=>true,
    'events' => array(
        'rowdblclick' => 'showDetail',
    ),
));
?>
<?php echo load_js("pur.js", true); ?>
<script type="text/javascript">
    function do_delete(_index,row){
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/shelf/do_delete_store'); ?>', data: {store: row.store_code},
            success: function (ret) {
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
    function do_view(_index, row) {
        location.href = "?app_act=base/shelf/view&store_code=" + row.store_code + "&store_name=" + row.store_name;
    }
    function report_excel(_index, row) {
        var url = '?app_act=sys/export_csv/export_show'; //暂时不是框架级别
            params.ctl_dataset = 'base/ShelfModel::get_by_page_detail';
            params.ctl_type = 'export';
            params.ctl_export_conf = 'shelf_list';
            params.ctl_export_name = '库位导出';
            params.store_code = row.store_code;
           <?php echo   create_export_token_js('base/ShelfModel::get_by_page_detail');?>
            for (var key in params) {
                url += "&" + key + "=" + params[key];
            }
            //params.ctl_type = 'view';
            //window.location.href = url;
            window.open(url);
//        var param = "";
//        param = param + "&id=" + row.store_id + "&store_code=" + row.store_code +"&type=view_export&app_fmt=json";
//        url = "?app_act=base/shelf/exprot_detail" + param;
//
//        window.location.href = url;
    }
    /**
     * 查看仓库库位列表
     * @param _index
     * @param row
     */
    function do_view(_index, row) {
        detail(_index, row);
    }

    //数据行双击打开新页面显示仓库库位列表
    function showDetail(_index, row) {
        detail(_index, row);
    }

    function detail(_index, row) {
        openPage('<?php echo base64_encode('?app_act=base/shelf/view&store_code=') ?>' + row.store_code + "&store_name=" + row.store_name, '?app_act=base/shelf/view&store_code=' + row.store_code + "&store_name=" + row.store_name, '仓库库位列表');
    }

</script>

