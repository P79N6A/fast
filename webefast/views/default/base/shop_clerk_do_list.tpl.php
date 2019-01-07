<?php
render_control('PageHead', 'head1', array('title' => '店员列表',
    'links' => array(
        array('url' => 'base/shop_clerk/detail&app_scene=add', 'title' => '新增店员', 'is_pop' => true, 'pop_size' => '500,500',),
    ),
    'ref_table' => 'table'
));
?>
<?php
$keyword_type = array();
$keyword_type['user_name'] = '店员姓名';
$keyword_type['user_code'] = '店员代码';
$keyword_type['phone'] = '手机号码';
$keyword_type = array_from_dict($keyword_type);
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
            'label' => array('id' => 'keyword_type', 'type' => 'select', 'data' => $keyword_type),
            'type' => 'input',
            'title' => '',
            'data' => $keyword_type,
            'id' => 'keyword',
        ),
        array(
            'label' => '启用状态',
            'title' => '是否启用',
            'type' => 'select',
            'id' => 'status',
            'value' => 1,
            'data' => ds_get_select_by_field('clerkstatus', 1)
        ),
    )
));
?>
<?php
render_control('DataTable', 'table', array('conf' => array(
        'list' => array(
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '200',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'pop:base/shop_clerk/detail&app_scene=edit', 'show_name' => '编辑用户',
                        'show_cond' => 'obj.is_buildin != 1', 'pop_size' => '500,500'),
                    array('id' => 'reset_password', 'title' => '重设密码', 'callback' => 'do_reset_pwd',
                        'show_cond' => "obj.user_code != 'admin'", 'confirm' => '确认要重置<b>[{user_name}]</b>的密码吗？'),
                    array('id' => 'enable', 'title' => '启用',
                        'callback' => 'do_enable', 'show_cond' => 'obj.status != 1', 'confirm' => '确认要启用店员 <b>[{user_name}]</b> 吗？'),
                    array('id' => 'disable', 'title' => '停用', 'callback' => 'do_disable', 'show_cond' => 'obj.status == 1',
                        'confirm' => '确认要停用店员 <b>[{user_name}]</b> 吗？'),
                    array('id' => 'delete', 'title' => '删除', 'callback' => 'do_delete', 'show_cond' => 'obj.status != 1',
                        'confirm' => '确认要删除店员 <b>[{user_name}]</b> 吗？'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店员代码',
                'field' => 'user_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '店员姓名',
                'field' => 'user_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属门店',
                'field' => 'shop_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '职位',
                'field' => 'type',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机号码',
                'field' => 'phone',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '性别',
                'field' => 'sex',
                'width' => '100',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'sys/UserModel::get_clerk_by_page',
    'queryBy' => 'searchForm',
    'export' => array('id' => 'exprot_list', 'conf' => 'cherk_list', 'name' => '店员列表', 'export_type' => 'file'),
    'idField' => 'user_id',
        //'RowNumber'=>true,
//'CheckSelection'=>true,
));
?>
<script type="text/javascript">
    function do_reset_pwd(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('base/shop_clerk/reset_pwd'); ?>', data: {user_id: row.user_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('重设密码为：' + ret.data, type);
                } else {
                    if (ret.status == -10) {
                        BUI.Message.Alert(ret.message, function () {
                            window.open(ret.data);
                        }, type);
                    } else {
                        BUI.Message.Alert(ret.message, type);
                    }

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
            url: '<?php echo get_app_url('base/shop_clerk/update_active'); ?>',
            data: {user_id: row.user_id, status: active},
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
    function do_delete(_index, row) {
        $.ajax({
            type: 'POST', dataType: 'json',
            url: "<?php echo (get_app_url('base/shop_clerk/do_delete')); ?>",
            data: {user_id: row.user_id},
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
</script>