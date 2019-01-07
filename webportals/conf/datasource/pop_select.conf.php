<?php
return array(
    // 选择窗体数据源标识 => array(url: 对应的页面,title: 对应页面选择数据的`名称`字段)
    'sys/user' => array('url' => get_app_url('common/select/user'), 'title' => '选择用户'),
    'sys/org' => array('url' => get_app_url('common/select/org'), 'title' => '选择组织机构'),
    'sys/orguser' => array('url' => get_app_url('common/select/orguser'), 'title' => '选择组织机构用户'),
);