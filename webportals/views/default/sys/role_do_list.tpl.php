<?php

render_control('PageHead', 'head1', array('title' => '角色列表',
    'links' => array(
        array('url' => 'sys/role/detail&app_scene=add', 'title' => '新增角色', 'is_pop' => true, 'pop_size' => '800,400'),
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
        )
    ),
    'fields' => array(
        array(
            'label' => '关键字',
            'title' => '代码/名称',
            'type' => 'input',
            'id' => 'keyword'
        ),
        array(
            'label' => '内置角色',
            'type' => 'select',
            'id' => 'is_buildin',
            'data' => array_from_dict(array(
                '' => '全部',
                '1' => '是',
                '0' => '否'
                    )
            )
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
                'title' => '代码',
                'field' => 'role_code',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '名称',
                'field' => 'role_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '描述',
                'field' => 'role_desc',
                'width' => '200',
                'align' => '',
                'format' => array(
                    'type' => 'truncate',
                    'value' => 20
                )
            ),
            array(
                'type' => 'checkbox',
                'show' => 1,
                'title' => '内置',
                'field' => 'is_buildin',
                'width' => '50',
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
                    array('id' => 'view', 'title' => '查看', 'act' => 'sys/role/detail&app_scene=view', 'show_name' => '查看角色'),
                    array('id' => 'edit', 'title' => '编辑', 'act' => 'pop:sys/role/detail&app_scene=edit', 'show_name' => '编辑角色', 'show_cond' => 'obj.is_buildin != 1'),
                    array('id' => 'allot', 'title' => '分配权限', 'act' => 'sys/role/allot&app_scene=edit', 'show_name' => '分配权限[{role_code}-{role_name}]', 'pop_size' => '800,500', 'show_cond' => 'obj.is_buildin != 1'),
                ),
            )
        )
    ),
    'dataset' => 'sys/RoleModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'role_id',
    'CheckSelection' => FALSE,
));
?>