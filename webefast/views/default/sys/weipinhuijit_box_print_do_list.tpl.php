<?php
render_control('PageHead', 'head1', array('title' => '快递单模板列表',
    'links' => array(
    // array('url' => 'sys/weipinhuijit_box_print/add', 'title' => '新增箱唛打印模板'),
    ),
    'ref_table' => 'table'
));
?>


<?php
render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '300',
                'align' => '',
                'buttons' => array(
                    array('id' => 'enable', 'title' => '预览',
                        'callback' => 'do_enable'),
                    array('id' => 'edit', 'title' => '编辑模版', 'act' => 'sys/weipinhuijit_box_print/edit_express&print_templates_code={print_templates_code}', 'show_name' => '编辑'),
                    array('id' => 'delete', 'title' => '删除',
                        'callback' => 'do_delete',
                        'show_cond' => 'obj.is_buildin == 0',
                        'confirm' => '确定要删除这条辛辛苦苦录入的数据吗？'),
                ),
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '模版名称',
                'field' => 'print_templates_name',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '打印机',
                'field' => 'printer',
                'width' => '200',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '类型',
                'field' => 'is_buildin_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/WeipinhuijitBoxRecordModel::get_by_page',
//     'queryBy' => 'searchForm',
    'idField' => 'print_templates_id',
));
?>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/express_tpl/do_delete');
?>', data: {id: row.print_templates_id, is_buildin: row.is_buildin},
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


    function do_enable(_index, row) {
        _do_set_active(_index, row, 'enable');
    }
    function do_disable(_index, row) {
        _do_set_active(_index, row, 'disable');
    }
    function _do_set_active(_index, row, active) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/sms_tpl/update_active');
?>',
            data: {id: row.id, type: active},
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


    $(function () {
        $(".control-label").css("width", "110px");
    })

</script>