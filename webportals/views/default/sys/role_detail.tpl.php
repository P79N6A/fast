<?php
render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '编辑角色',
    'links' => array(
        array('url' => 'sys/role/do_list', 'title' => '角色列表')
    ),
));
?>

<?php
render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '代码', 'type' => 'input', 'field' => 'role_code', 'validation' => array('maxlen' => 30,), 'edit_scene' => 'add'),
            array('title' => '名称', 'type' => 'input', 'field' => 'role_name', 'validation' => array('type' => 'required', 'maxlen' => 150,)),
            array('title' => '描述', 'type' => 'textarea', 'field' => 'role_desc', 'validation' => array('maxlen' => 255,)),
        ),
        'hidden_fields' => array(array('field' => 'role_id'), array('field' => 'role_code'), array('field' => 'opt')),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'act_edit' => 'sys/role/do_edit',
    'act_add' => 'sys/role/do_add',
    'data' => $response['data'],
    'rules' => array(
        array('role_code', 'require'),
        array('role_name', 'require'))
));
?>
<?php
if ($app['scene'] != "add" && $app['scene'] != "edit") {
    render_control('TabPage', 'TabPage1', array(
        'tabs' => array(
            array('title' => '角色用户', 'active' => true), // 默认选中active=true的页签
        ),
        'for' => 'TabPageContents' // 指定页签内容的父容器，上面配置页签标题的顺序要和页签容器中的div的顺序一一对应
    ));
}
?>
<?php if ($app['scene'] != "add" && $app['scene'] != "edit") { ?>
    <div id="TabPageContents">
        <div class="panel">
            <div class="panel-body">
                <?php
                render_control('DataTable', 'tablemd', array(
                    'conf' => array(
                        'list' => array(
                            array(
                                'type' => '',
                                'show' => 1,
                                'title' => '用户代码',
                                'field' => 'user_id_code',
                                'width' => '150',
                                'align' => ''
                            ),
                            array(
                                'type' => 'text',
                                'show' => 1,
                                'title' => '用户名称',
                                'field' => 'user_id_name',
                                'width' => '150',
                                'align' => ''
                            ),
                        )
                    ),
                    'dataset' => 'sys/RoleModel::get_by_roleuser',
                    'params' => array('filter' => array('role_id' => $request['_id'])),
                    'idField' => 'user_role_id',
                    'CheckSelection' => false,
                ));
                ?>
            </div>
        </div>
    </div>
<?php } ?>
