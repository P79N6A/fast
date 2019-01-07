<?php

render_control('PageHead', 'head1', array('title' => isset($app['title']) ? $app['title'] : '添加推送相关信息',
));
?>

<?php

render_control('Form', 'form1', array(
    'conf' => array(
        'fields' => array(
            array('title' => '客户ID', 'type' => 'input', 'field' => 'kh_id'),
            array('title' => '推送设置RDS_ID', 'type' => 'input', 'field' => 'rds_id'),
            array('title' => '店铺昵称 ', 'type' => 'input', 'field' => 'nick',),
        ),
    ),
    'buttons' => array(
        array('label' => '提交', 'type' => 'submit'),
        array('label' => '重置', 'type' => 'reset'),
    ),
    'col' => 2,
    'act_add' => 'products/jdpdb/do_add',
        //'callback'=>'submitCall'
));
?>
