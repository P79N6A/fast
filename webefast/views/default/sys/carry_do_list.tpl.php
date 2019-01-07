<?php

render_control('PageHead', 'head1', array('title' => '结转记录查询',
//    'links' => array(
//        array('url' => 'sys/carry/set_carry', 'title' => '结转设置'),
//    ),
    'ref_table' => 'table'
));
?>
<?php

render_control('PageHead', 'head1', array('title' => '结转查询',
    'ref_table' => 'table'
));
?>

<?php

render_control('DataTable', 'table', array('conf' => array('list' => array(
            array('type' => 'text',
                'show' => 1,
                'title' => '结转任务号',
                'field' => 'task_sn',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '结转开始时间',
                'field' => 'start_date',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '结转结束时间',
                'field' => 'end_date',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '结转订单笔数',
                'field' => 'record_num',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '结转退单笔数',
                'field' => 'return_num',
                'width' => '150',
                'align' => '',
            ),
            array('type' => 'text',
                'show' => 1,
                'title' => '结转状态',
                'field' => 'state_name',
                'width' => '200',
                'align' => '',
            ),
        )
    ),
    'dataset' => 'sys/carry/SysCarryModel::get_by_page',
    //  'queryBy' => 'searchForm',
    'idField' => 'task_sn',
));
?>

