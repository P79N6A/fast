<?php
render_control('PageHead', 'head1', array(
    'title' => '导入模版列表',
    'links' => array(),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array('label' => '模版名称',
            'title' => '模版名称',
            'type' => 'input',
            'id' => 'danju_name',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => 'ID',
                'field' => 'excel_id',
                'width' => '50',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '模版名称',
                'field' => 'danju_name',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => 'file',
                'show' => 1,
                'title' => '上传模版',
                'text' => '请选择',
                'width' => '200',
                'field' => 'danju_path',
                //'rules' => array('ext' => '.xls,.xlsx', 'max' => 1, 'minSize' => 1, 'maxSize' => 4096),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '文件路径',
                'field' => 'danju_path',
                'width' => '400',
                'align' => '',
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '150',
                'align' => '',
                'buttons' => array(
                    //array('id' => 'download', 'title' => '下载', 'act' => '',),
                    array('id' => 'config', 'title' => '配置', 'act' => 'pop:sys/excel_import/config&app_scene=edit', 'show_name' => '配置'),
                    //array('id' => 'default', 'title' => '默认', 'callback' => '__do_set_default'),
                ),
            )
        )
    ),
    'dataset' => 'sys/ExcelImportModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'excel_id',
));
?>

<script type="text/javascript">
    /**
<!--
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sms_supplier/do_delete'); ?>',
            data: {supplier_id: row.supplier_id},
            success: function (ret) {
                var type = ret.status === 1 ? 'success' : 'error';
                if (type === 'success') {
                    BUI.Message.Alert('删除成功', type);
                    tableStore.load();
                } else {
                    BUI.Message.Alert(ret.message, type);
                }
            }
        });
    }

    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_default(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sms_supplier/update_active'); ?>',
            data: {supplier_id: row.supplier_id, type: active},
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
//-->
*/
</script>