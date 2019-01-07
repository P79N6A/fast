<?php

render_control('PageHead', 'head1', array('title' => '门店会员列表',
    'links' => array(
        array('url' => 'crm/client/detail&app_scene=add', 'title' => '添加会员', 'is_pop' => true, 'pop_size' => '600,590'),
    ),
    'ref_table' => 'table'
));
?>


<?php

$keyword_type = array();
$keyword_type['client_code'] = '会员代码';
$keyword_type['client_name'] = '会员名称';
$keyword_type['client_tel'] = '手机号码';
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
                'width' => '100',
                'align' => '',
                'buttons' => array(
                    array('id' => 'edit', 'title' => '编辑',
                        'act' => 'pop:crm/client/detail&app_scene=edit', 'show_name' => '编辑', 'pop_size' => '600,590'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员代码',
                'field' => 'client_code',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '会员名称',
                'field' => 'client_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '性别',
                'field' => 'client_sex',
                'width' => '80',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '生日',
                'field' => 'birthday',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '邮箱',
                'field' => 'email',
                'width' => '250',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '手机',
                'field' => 'client_tel',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '积分',
                'field' => 'client_integral',
                'width' => '120',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'crm/ClientModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'client_id',
    'export' => array('id' => 'exprot_list', 'conf' => 'client_list', 'name' => '会员列表'),
));
?>