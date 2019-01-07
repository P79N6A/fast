<?php

render_control('PageHead', 'head1', array('title' => '产品建议提单列表',
    'links' => array(),
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
            'label' => '提单编号',
            'title' => '编号模糊查询',
            'type' => 'input',
            'id' => 'xqsue_number'
        ),
        array(
            'label' => '提单标题',
            'title' => '标题模糊查询',
            'type' => 'input',
            'id' => 'xqsue_title'
        ),
        array(
            'label' => '提单人',
            'title' => '提单人模糊查询',
            'type' => 'input',
            'id' => 'xqsue_user',
        ),
        array(
            'label' => '状态',
            'type' => 'select_multi',
            'field' => 'xqsue_status',
            'data' => ds_get_select_by_field('xqissue_type', 0)
        ),
        array(
            'label' => '需求类型',
            'type' => 'select_multi',
            'field' => 'xqsue_xqtype',
            'data' => ds_get_select_by_field('xqsuetype', 0)
        ),
        array(
            'label' => '客户名称',
            'title' => '客户模糊查询',
            'type' => 'input',
            'id' => 'kh_name'
        ),
        array(
            'label' => '审批意见',
            'title' => '审批意见模糊查询',
            'type' => 'input',
            'id' => 'xqsue_idea'
        ),
        array(
            'label' => '预返日期',
            'type' => 'group',
            'field' => 'daterange4',
            'child' => array(
                array('title' => 'start', 'type' => 'date', 'field' => 'xqsue_return_time_start',),
                array('pre_title' => '~', 'type' => 'date', 'field' => 'xqsue_return_time_end',),
            )
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
                'width' => '60',
                'align' => 'center',
                'buttons' => array(
                    array('id' => 'view', 'title' => '查看',
                        'act' => 'service/product_suggest/detail&app_scene=view', 'show_name' => '查看需求提单'),
                ),
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '提单编号',
                'field' => 'xqsue_number',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '问题标题',
                'field' => 'xqsue_title',
                'width' => '160',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '客户名称',
                'field' => 'xqsue_kh_id_name',
                'width' => '200',
                'align' => 'center',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '提单人',
                'field' => 'xqsue_user_name',
                'width' => '120',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '提单时间',
                'field' => 'xqsue_submit_time',
                'width' => '150',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '预返日期',
                'field' => 'xqsue_return_time',
                'width' => '110',
                'align' => 'center'
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '状态',
                'field' => 'xqsue_status',
                'width' => '120',
                'align' => 'center',
                'format_js' => array('type' => 'map', 'value' => ds_get_field('xqissue_type'))
            )
        )
    ),
    'dataset' => 'service/ProductSuggestModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'xqsue_number',
    'CheckSelection' => true,
    'events' => array(
        'rowdblclick' => array('ref_button' => 'view')),
));
?>