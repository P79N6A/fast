<?php

render_control('DataTable', 'table', array(
    'conf' => array(
        'list' => array(
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '接口名称',
                'field' => 'name',
                'width' => '150',
                'align' => '',
            ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '操作',
                'field' => 'status_html',
                'width' => '200',
                'align' => '',
            ),
           
           array(
               'type' => 'text',
               'show' => 1,
               'title' => '最后一次运行时间',
               'field' => 'last_time',
               'width' => '150',
               'align' => '',
           ),
           array(
           		'type' => 'text',
           		'show' => 1,
           		'title' => '下一次预计执行时间',
           		'field' => 'next_execute_time',
           		'width' => '150',
           		'align' => '',
           ),
           array(
            'type' => 'text',
            'show' => 1,
            'title' => '运行间隔时间',
            'field' => 'loop_time',
            'width' => '150',
            'align' => '',

           ),
//            array(
//                'type' => 'text',
//                'show' => 1,
//                'title' => '最后一次运行状态报告',
//                'field' => '',
//                'width' => '200',
//                'align' => '',
//            ),
//        	    array('type' => 'button',
// 		        'show' => 1,
// 		        'title' => '最后一次运行状态报告',
// 		        'field' => '_operate',
// 		        'width' => '150',
// 		        'align' => '',
// 		        'buttons' => array(
// 		            array('id' => 'enable', 'title' => '查看'),
// 		        ),
// 		    ),
            array(
                'type' => 'text',
                'show' => 1,
                'title' => '说明',
                'field' => 'desc',
                'width' => '400',
                'align' => '',
            ),
//            array('type' => 'button',
//		        'show' => 1,
//		        'title' => '操作',
//		        'field' => '_operate',
//		        'width' => '150',
//		        'align' => '',
//		        'buttons' => array(
//		            array('id' => 'enable', 'title' => '高级配置'),
//		        ),
//		    ),
        )
    ),
    'dataset' => 'sys/ScheduleModel::get_by_page',
//    'queryBy' => 'searchForm',
    'idField' => 'id',
    'params' => array('filter' => array('type' => $response['type'])),
        //'RowNumber'=>true,
        //'CheckSelection' => true,
));
?>