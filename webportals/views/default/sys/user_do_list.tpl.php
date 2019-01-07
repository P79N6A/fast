<?php
render_control('PageHead', 'head1', array('title' => '用户列表',
    'links' => array(
    array('url'=>'sys/user/detail&app_scene=add', 'title'=>'新增用户', 'is_pop'=>false, 'pop_size'=>'500,400'),
    ),
    'ref_table' => 'table'
));
?>
<?php
render_control('SearchForm', 'searchForm', array(
    'cmd' => array(
        'label' => '查询',
        'label' => '查询',
        'id' => 'btn-search'
    ),
    'fields' => array(
        array(
            'label' => '关键字',
            'title' => '登录名/真实名',
            'type' => 'input',
            'id' => 'keyword'
        ),
        array(
            'label' => '有效',
            'title' => '是否有效',
            'type' => 'select',
            'id' => 'user_active',
            'data' => ds_get_select_by_field('boolstatus')
        ),
        array(
            'label' => '性别',
            'title' => '性别',
            'type' => 'select',
            'id' => 'user_sex',
            'data' => ds_get_select_by_field('sex')
        )
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
                'title' => '登录名',
                'field' => 'user_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '真实名',
                'field' => 'user_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '所属机构',
                'field' => 'user_org_code_name',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '直属上级',
                'field' => 'user_highedrup_name',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '是否有效',
                'field' => 'user_active',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮箱',
                'field' => 'user_email',
                'width' => '160',
                'align' => ''
            ),
            array(
                'type' => 'button',
                'show' => 1,
                'title' => '操作',
                'field' => '_operate',
                'width' => '200',
                'align' => '',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看',
                        'act' => 'sys/user/detail&app_scene=view', 'show_name' => '查看用户'),
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'sys/user/detail&app_scene=edit', 'show_name' => '编辑用户',
                        'show_cond' => 'obj.is_buildin != 1'),
                    array('id' => 'reset_password', 'title' => '重置密码', 'callback' => 'do_reset_pwd',
                        'show_cond' => "obj.user_code != 'admin'", 'confirm' => '确认要重置<b>[{user_name}]</b>的密码吗？'),
                    array('id' => 'list_row', 'title' => '角色列表',
                        'act' => 'pop:sys/user/role_list&user_id={user_id}', 'show_name' => '<i>[{user_name}]</i>关联的角色',
                        'pop_size' => '800,500'),
                ),
            )
        )
    ),
    'dataset' => 'sys/UserModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'user_id',
    //'RowNumber'=>true,
//    'CheckSelection' => true,
));
?>
<script type="text/javascript">
    function do_reset_pwd(_index, row) {
        $.ajax({type: 'POST', dataType: 'json',
            url: '<?php echo get_app_url('sys/user/reset_pwd'); ?>', data: {user_id: row.user_id},
            success: function (ret) {
                var type = ret.status == 1 ? 'success' : 'error';
                if (type == 'success') {
                    BUI.Message.Alert('新密码：' + ret.data, type);
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
            url: '<?php echo get_app_url('sys/user/update_active'); ?>',
            data: {user_id: row.user_id, type: active},
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