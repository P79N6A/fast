<?php
render_control('PageHead', 'head1', array('title' => '结转配置信息',
    'links' => array(
        array('url' => 'sys/carry/add_db&app_scene=add', 'title' => '添加结转库', 'is_pop' => false, 'pop_size' => '500,400'),
        array('url' => 'sys/carry/add_db_kh&app_scene=add', 'title' => '设置结转客户库', 'is_pop' => false, 'pop_size' => '500,400'),
    ),
    'ref_table' => 'table'  //ref_table 表示是否刷新父页面
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
            'label' => '客户名称',
            'title' => '客户名称',
            'type' => 'input',
            'id' => 'kh_name'
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
                'title' => '客户名称',
                'field' => 'kh_name',
                'width' => '200',
                'align' => ''
            ),
            array(
                'type' => '',
                'show' => 1,
                'title' => '结转库标记名',
                'field' => 'carry_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => '',
                'show' => 1,
                'title' => '结转库名',
                'field' => 'db_name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => '',
                'show' => 1,
                'title' => 'RDS名称',
                'field' => 'rds_dbname',
                'width' => '200',
                'align' => '',
            ),
            array(
                'type' => '',
                'show' => 1,
                'title' => 'RDS备注',
                'field' => 'rds_notes',
                'width' => '300',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/SysCarryModel::get_by_page',
    'queryBy' => 'searchForm',
    'idField' => 'id',
        //'RowNumber'=>true,
        // 'CheckSelection'=>true,
));
?>
