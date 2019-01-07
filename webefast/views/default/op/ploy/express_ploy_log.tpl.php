<?php

render_control('DataTable', 'table_log', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作者',
                'field' => 'user_name',
                'width' => '100',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作名称',
                'field' => 'action_name',
                'width' => '120',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作时间',
                'field' => 'action_time',
                'width' => '150',
                'align' => ''
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '备注',
                'field' => 'action_desc',
                'width' => '370',
                'align' => ''
            ),
        )
    ),
    'dataset' => 'op/ploy/ExpressPloyLogModel::get_log_by_page',
    'idField' => 'log_id',
    'HeaderFix' => FALSE,
    'params' => array('filter' => array('ploy_code' => $request['ploy_code'])),
));
?>