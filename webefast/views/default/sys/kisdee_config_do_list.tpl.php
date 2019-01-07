<?php
render_control('PageHead', 'head1', array('title' => '金蝶配置列表',
    'links' => array(
        array('url' => 'sys/kisdee_config/detail&app_scene=add', 'title' => '新增配置'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array('cmd' => array('label' => '查询',
        'id' => 'btn-search',
    ),
    'fields' => array(
        array(
            'label' => '配置名称',
            'title' => '',
            'type' => 'input',
            'id' => 'kis_config_name',
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array('type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '15%',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看', 'act' => 'sys/kisdee_config/detail&app_scene=view', 'show_name' => '查看'),
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'sys/kisdee_config/detail&app_scene=edit', 'show_cond' => 'obj.config_status != 1', 'show_name' => '编辑'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'show_cond' => 'obj.config_status != 1', 'confirm' => '确定要删除吗？'),
                    array('id' => 'enable', 'title' => '启用', 'callback' => 'do_enable', 'show_cond' => 'obj.config_status != 1'),
                    array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.config_status == 1', 'confirm' => '确认要停用吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '启用状态',
                'field' => 'config_status',
                'width' => '10%',
                'align' => 'center',
                'format_js' => array('type' => 'map_checked')
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '配置名称',
                'field' => 'config_name',
                'width' => '30%',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '应用上线日期',
                'field' => 'online_time',
                'width' => '45%',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/KisdeeConfigModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'config_id',
));
?>

<script type="text/javascript">
    function do_delete(_index, row) {
        $.ajax({
            url: '<?php echo get_app_url('sys/kisdee_config/do_delete'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {id: row.config_id},
            success: function (ret) {
                alert_msg(ret.status, ret.message);
                if (ret.status == 1) {
                    tableStore.load();
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
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: '<?php echo get_app_url('sys/kisdee_config/update_active'); ?>',
            data: {id: row.config_id, type: active},
            success: function (ret) {
                alert_msg(ret.status, ret.message);
                if (ret.status == 1) {
                    tableStore.load();
                }
            }
        });
    }

    function alert_msg(_status, _msg) {
        var _type = _status == 1 ? 'success' : 'error';
        BUI.Message.Show({
            msg: _msg,
            icon: _type,
            buttons: [],
            autoHide: true
        });
    }
</script>